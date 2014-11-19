<?php
/**
 * @file class_menuVoice.php
 * @brief Contiene la classe MenuVoice
 * 
 * @copyright 2005 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
namespace Gino\App\Menu;

/**
 * @brief Fornisce gli strumenti alla classe menu per la gestione amministrativa
 * 
 * @copyright 2005 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class MenuVoice extends \Gino\Model {
	
	public static $tbl_voices = "sys_menu_voices";
	private static $_tbl_user_role = "user_role";
	private $_gform;

	/**
	 * Costruttore
	 * 
	 * @param integer $id valore ID della voce di menu
	 * @return void
	 */
	function __construct($id) {
	
		$this->_tbl_data = self::$tbl_voices;
		parent::__construct($id);
	}
	
	/**
	 * Elimina le voci di menu di una istanza (nella procedura di eliminazione di una istanza)
	 * 
	 * @param integer $instance valore ID dell'istanza
	 * @return boolean
	 */
	public static function deleteInstanceVoices($instance) {

		$db = \Gino\db::instance();
		$query = "SELECT id FROM ".self::$tbl_voices." WHERE instance='$instance' AND parent='0'";
		$a = $db->selectquery($query);
		if(sizeof($a)>0) {
			foreach($a as $b) {
				\Gino\App\Language\Language::deleteTranslations(self::$tbl_voices, $b['id']);
				$mv = new MenuVoice($b['id']);
				$mv->deleteVoice();
			}
		}

		return true;
	}

	public function initOrderList() {

		$query = "SELECT max(order_list) AS last FROM ".self::$tbl_voices." WHERE instance='{$this->_p['instance']}' AND parent='{$this->_p['parent']}'";
		$a = $this->_db->selectquery($query);
		if(sizeof($a)>0) {
			$last = $a[0]['last'];
		}
		else $last = 0;

		$this->order_list = $last+1;
	}

	/**
	 * Query di aggiornamento della sequenza delle voci di menu
	 * 
	 * @return boolean
	 */
	public function updateOrderList() {

		$result = $this->_db->update(array('order_list'=>array('sql'=>"order_list-1")), self::$tbl_voices, "order_list>'".$this->order_list."' AND parent='".$this->parent."'", true);
		return $result;
	}

	/**
	 * Elimina le voci di menu che hanno in comune la stessa voce parent
	 */
	public function deleteVoice() {

		foreach($this->getChildren() as $child) $child->deleteVoice();
		$this->deleteDbData();
	}

	private function getChildren() {

		$children = array();

		$rows = $this->_db->select('id', self::$tbl_voices, "parent='{$this->_p['id']}'", array('order' => "order_list"));
		if($rows and count($rows)) {
			foreach($rows as $row) {
				$children[$row['id']] = new MenuVoice($row['id']);
			}
		}

		return $children;
	}

  public static function get($options=null) {
    
  	$res = array();
    $where = \Gino\gOpt('where', $options, null);
    $order = \Gino\gOpt('order', $options, null);
    $db = \Gino\db::instance();
    $rows = $db->select('id', self::$tbl_voices, $where, array('order' => $order));
    if($rows and count($rows)) {
      foreach($rows as $row) {
        $res[] = new MenuVoice($row['id']);
      }
    }
    return $res;
  }

  public function userCanSee() {

    $request = \Gino\Http\Request::instance();
    if(!$this->perms) {
      return true;
    }
    \Gino\Loader::import('auth', 'Permission');

    foreach(explode(';', $this->perms) as $p) {
      $values = explode(',', $p);
      $perm = new \Gino\App\Auth\Permission($values[0]);
      $instance = $values[1];
      if($request->user->hasPerm($perm->class, $perm->code, $instance)) {
        return true;
      }
    }

    return false;
  }

	/**
	 * Form di inserimento e modifica di una voce di menu
	 * 
	 * @param string $formaction indirizzo dell'azione
	 * @param integer $parent valore ID della voce parent
	 * @return string
	 */
	public function formVoice($formaction, $parent) {

		\Gino\Loader::import('auth', array('Permission'));
	
		$gform = \Gino\Loader::load('Form', array('gform', 'post', true));
		$gform->load('dataform');

		$parentVoice = new MenuVoice($parent);

		if($this->_p['id']) {$title = _("Modifica voce"); $submit = _("modifica");$action='modify';}
		else {
			$title = ($parent)? _("Nuova voce sotto ")."\"".\Gino\htmlChars($parentVoice->label)."\"":_("Nuova voce principale");
			$submit = _("inserisci");
			$action='insert';
		}
		$title = $title."<a name=\"top\"> </a>";

		$pub = new \Gino\Pub;
		
		$required = 'label,type,voice';	

		$required = '';
		$buffer = $gform->open($formaction, '', $required);
		$buffer .= $gform->hidden('action', $action);
		$buffer .= $gform->hidden('parent', $parent);
		if($this->_p['id'])
			$buffer .= $gform->hidden('id', $this->_p['id']);

		$buffer .= $gform->cinput('label', 'text', $gform->retvar('label', \Gino\htmlInput($this->_p['label'])), _("Voce"), array("required"=>true, "size"=>40, "maxlength"=>200, "trnsl"=>true, "trnsl_table"=>self::$tbl_voices, "field"=>"label", "trnsl_id"=>$this->_p['id']));

		$buffer .= $gform->cinput('url', 'text', $gform->retvar('url', \Gino\htmlInput($this->_p['url'])), _("url"), array("size"=>40, "maxlength"=>200, "id"=>"url"));

		$buffer .= $gform->cradio('type', $gform->retvar('type', \Gino\htmlInput($this->_p['type'])), array('int'=>_("interno (utilizzare la ricerca viste)"), 'ext'=>_("esterno (http://www.otto.to.it)")), 'int', _("Tipo di link"), array("required"=>true, "aspect"=>"v"));

		$buffer .= $gform->multipleCheckbox('perm[]', explode(';', $this->perms), \Gino\App\Auth\Permission::getForMulticheck(), array(_('Permessi'), _('Se si intende mostrare la voce di menu a tutti gli utenti non selezionare alcun permesso')), null);

		$buffer .= $gform->cinput('submit_action', 'submit', $submit, '', array("classField"=>"submit"));
		$buffer .= $gform->close();

		$view = new \Gino\View(null, 'section');
		$dict = array(
			'title' => $title,
			'class' => 'admin',
			'content' => $buffer
		);

		return $view->render($dict);
	}
	
	/**
	 * Ricava la voce di menu corrispondente alla pagina caricata
	 * 
	 * @param integer $instance valore ID dell'istanza
	 * @return mixed (integer->valore ID della voce di menu, string->home|admin)
	 */
	public static function getSelectedVoice($instance) {
	
		$db = \Gino\db::instance();
		$query_string = urldecode($_SERVER['QUERY_STRING']);	// "evt[page-displayItem]&id=5"
		$result_link = null;
		$result = null;

    if(preg_match("/\[(.+)\]/is", $query_string, $matches)) {
      $result = '';
      $result_link = '';
      $rows = $db->select('id, url', self::$tbl_voices, "url LIKE '%".$matches[0]."%' AND instance='$instance'");
      if($rows and count($rows)) {
        foreach($rows as $row) {
          if(preg_match("#".preg_quote(stristr($row['url'], '?'))."(&.*)?$#", "?".$query_string) && strlen($result_link)<strlen($row['url']) )
          {
            $result = $row['id'];
            $result_link=$row['url'];
            return $result;
          }
        }
      }
    }

    /*
    L'indirizzo di base è nel formato di gino, ovvero ad esempio: 
    urldecode($_SERVER['QUERY_STRING']) => string(26) "evt[page-displayItem]&id=5" 
    in quanto l'eventuale permalink è già stato convertito (class Document).
    Nella tabella del menu i link sono registrati nel formato permalink.
    */
    $obj = new \Gino\Link();
    $plink = $obj->convertLink($query_string);	// => page/displayItem/5
    $search_link = $obj->alternativeLink($plink);
    
    $query = "SELECT id, url FROM ".self::$tbl_voices." WHERE $search_link AND instance='$instance'";
    $a = $db->selectquery($query);
    if(sizeof($a)>0) {
      foreach($a as $b) {
        
        $mlink = $b['url'];
        $mlink = $obj->convertLink($mlink, array('pToLink'=>true));	// => evt[page-displayItem]&id=5
        
        if(preg_match("#".preg_quote(stristr($mlink, '?'))."(&.*)?$#", "?".$query_string) 
        && strlen($result_link)<strlen($mlink))
        {
          $result = $b['id'];
          $result_link = $mlink;
        }
      }
    }
		return $result;
	}

}
?>
