<?php
/**
 * @file class.skin.php
 * @brief Contiene la classe Skin
 * 
 * @copyright 2005 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */

/**
 * @brief Libreria per la gestione delle skin
 * 
 * @copyright 2005 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class Skin extends Model {

	protected $_tbl_data;
	private static $_tbl_skin = 'sys_layout_skin';
	private $_home, $_interface;

	/**
	 * Costruttore
	 * 
	 * @param integer $id valore ID del record
	 * @return void
	 */
	function __construct($id) {

		$this->_tbl_data = self::$_tbl_skin;

		parent::__construct($id);

		$this->_home = 'index.php';
		$this->_interface = 'layout';
	}
	
	/**
	 * Elenco delle skin in formato object
	 * 
	 * @param string $order per quale campo ordinare i risultati
	 * @return array
	 */
	public static function getAll($order='priority') {

		$db = db::instance();
		$res = array();
		$rows = $db->select('id', self::$_tbl_skin, null, array('order' => $order));
		if($rows and count($rows)) {
			foreach($rows as $row) {
				$res[] = new skin($row['id']);
			}
		}

		return $res;
	}

	/**
	 * Skin getter
	 * 
	 * @param array $options
	 *   array associativo di opzioni
	 *   - @b order (string): per quale campo ordinare i risultati
	 * @return array
	 */
	public static function get($options = array()) {
		
		$where = gOpt('where', $options, null);
		$order = gOpt('order', $options, null);
		$limit = gOpt('limit', $options, null);

		$db = db::instance();
		$res = array();
		$rows = $db->select('id', self::$_tbl_skin, $where, array('order' => $order, 'limit' => $limit));
		if($rows and count($rows)) {
			foreach($rows as $row) {
				$res[] = new skin($row['id']);
			}
		}

		return $res;
	}

	/**
	 * Ricerca la corrispondenza dell'url con una skin
	 * 
	 * @param string $relativeUrl percorso relativo
	 * @return object o false
	 */
	public static function getSkin($relativeUrl) {

    $registry = registry::instance();
		$db = $registry->db;
		$session = $registry->session;
		$plink = new Link();

		$query = "SELECT id, session, rexp, urls, auth FROM ".self::$_tbl_skin." ORDER BY priority ASC";	
		$a = $db->selectquery($query);
		if(sizeof($a)>0) {

			foreach($a as $b) {

				$session_array = explode("=", trim($b['session']));

				if(count($session_array)==2) {

					if(isset($session->$session_array[0]) && $session->$session_array[0] == $session_array[1]) {
						
						$urls = explode(",", $b['urls']);

						foreach($urls as $url) 
						{	
							if(!preg_match('#\?(evt|pt)\[#', $url))
							$url = $plink->convertLink($url, array('pToLink'=>true, 'basename'=>true));
					
							if($url == $relativeUrl) { 
								if($b['auth']=='' || ($registry->user->id && $b['auth']=='yes') || (!$registry->user->id && $b['auth']=='no'))
									return new skin($b['id']);
							}
						}

						if(!empty($b['rexp']))
						{
							$p_relativeUrl = $plink->convertLink($relativeUrl, array('pToLink'=>false));
					
							if(preg_match($b['rexp'], $relativeUrl) || preg_match($b['rexp'], $p_relativeUrl))
							{
								if($b['auth']=='' || ($registry->user->id && $b['auth']=='yes') || (!$registry->user->id && $b['auth']=='no'))
									return new skin($b['id']);
							}
						}
					}
				}
			}

			foreach($a as $b) {

				if(!$b['session']) {
					$urls = explode(",", $b['urls']);

					foreach($urls as $url) 
					{	
						if(!preg_match('#\?(evt|pt)\[#', $url))
							$url = $plink->convertLink($url, array('pToLink'=>true, 'basename'=>true));
					
						if($url == $relativeUrl) { 
							if($b['auth']=='' || ($registry->user->id && $b['auth']=='yes') || (!$registry->user->id && $b['auth']=='no'))
								return new skin($b['id']);
						}
					}
				}
			}
			foreach($a as $b) {

				if(!$b['session'] && !empty($b['rexp']))
				{
					$p_relativeUrl = $plink->convertLink($relativeUrl, array('pToLink'=>false));
					
					if(preg_match($b['rexp'], $relativeUrl) || preg_match($b['rexp'], $p_relativeUrl))
					{
						if($b['auth']=='' || ($registry->user->id && $b['auth']=='yes') || (!$registry->user->id && $b['auth']=='no'))
							return new skin($b['id']);
					}
				}
			}
			return false;
		}
		else return false;
	}

	/**
	 * Elimina dalla tabella delle skin il riferimento a uno specifico file css
	 * 
	 * @param integer $id valore ID del css associato alla skin
	 * @return boolean
	 */
	public static function removeCss($id) {

		$db = db::instance();
    $res = $db->update(array(
      'css' => 0
    ), self::$_tbl_skin, "css='$id'");

		return $res;
	}
	
	/**
	 * Elimina dalla tabella delle skin il riferimento a uno specifico template
	 * 
	 * @param integer $id valore ID del template associato alla skin
	 * @return boolean
	 */
	public static function removeTemplate($id) {
		
		$db = db::instance();
    $res = $db->update(array(
      'template' => 0
    ), self::$_tbl_skin, "template='$id'");

		return $res;
	}

	/**
	 * Riporta la priorità di una nuova skin
	 * 
	 * @return integer
	 */
	public static function newSkinPriority() {

		$db = db::instance();
		$query = "SELECT MAX(priority) as m FROM ".self::$_tbl_skin;
		$a = $db->selectquery($query);
		if(sizeof($a)>0) {
			return ($a[0]['m']+1);
		}
		return 1;
	}

  public function sortUp() {

    $priority = $this->priority;
    $before_skins = self::get(array('where' => "priority<'".$priority."'", "order" => "priority DESC", "limit" => array(0, 1)));
    $before_skin = $before_skins[0];
    $this->priority = $before_skin->priority;
    $this->updateDbData();

    $before_skin->priority = $priority;
    $before_skin->updateDbData();
  }

	/**
	 * Form per la creazione e la modifica di una skin
	 * 
	 * @return string
	 */
	public function formSkin() {

		$gform = Loader::load('Form', array('gform', 'post', true));
		$gform->load('dataform');

		$title = ($this->id)? _("Modifica")." ".htmlChars($this->label):_("Nuova skin");
		
		$required = 'template';
		$buffer = $gform->open($this->_home."?evt[".$this->_interface."-actionSkin]", '', $required);
		$buffer .= $gform->hidden('id', $this->id);

		$buffer .= $gform->cinput('label', 'text', $gform->retvar('label', htmlInput($this->label)), _("Etichetta"), array("required"=>true, "size"=>40, "maxlength"=>200, "trnsl"=>true, "trnsl_table"=>$this->_tbl_data, "field"=>"label", "trnsl_id"=>$this->id));
		$buffer .= $gform->cinput('session', 'text', $gform->retvar('session', $this->session), array(_("Variabile di sessione"), _("esempi").":<br />mobile=1"), array("size"=>40, "maxlength"=>200));
		$buffer .= $gform->cinput('rexp', 'text', $gform->retvar('rexp', $this->rexp), array(_("Espressione regolare"), _("esempi").":<br />#\?evt\[news-(.*)\]#<br />#^news/(.*)#"), array("size"=>40, "maxlength"=>200));
		$buffer .= $gform->cinput('urls', 'text', $gform->retvar('urls', htmlInput($this->urls)), array(_("Urls"), _("Indicare uno o più indirizzi separati da virgole; esempi").":<br />index.php?evt[news-viewList]<br />news/viewList"), array("size"=>40, "maxlength"=>200));
		$css_list = array();
		foreach(css::getAll() as $css) {
			$css_list[$css->id] = htmlInput($css->label);
		}	
		$buffer .= $gform->cselect('css', $gform->retvar('css', $this->css), $css_list, _("Css"));
		$tpl_list = array();
		foreach(template::getAll() as $tpl) {
			$tpl_list[$tpl->id] = htmlInput($tpl->label);
		}	
		$buffer .= $gform->cselect('template', $gform->retvar('template', $this->template), $tpl_list, _("Template"), array("required"=>true));
		$buffer .= $gform->cradio('auth', $gform->retvar('auth', $this->auth), array(""=>"si & no", "yes"=>_("si"),"no"=>_("no")), '', array(_("Autenticazione"), _('<b>si</b>: la skin viene considerata solo se l\'utente è autenticato.<br /><b>no</b>: viceversa.<br /><b>si & no</b>: la skin viene sempre considerata.')), array("required"=>true));
		$buffer .= $gform->cinput('cache', 'text', $gform->retvar('cache', $this->cache), array(_("Tempo di caching dei contenuti (s)"), _("Se non si vogliono tenere in cache o non se ne conosce il significato lasciare vuoto o settare a 0")), array("size"=>6, "maxlength"=>16, "pattern"=>"^\d*$"));

		$buffer .= $gform->cinput('submit_action', 'submit', (($this->id)?_("modifica"):_("inserisci")), '', array("classField"=>"submit"));

		$buffer .= $gform->close();

		$view = new view();
		$view->setViewTpl('section');
		$dict = array(
			'title' => $title,
			'class' => 'admin',
			'content' => $buffer
		);

		return $view->render($dict);
	}

	/**
	 * Inserimento e modifica di una skin
	 */
	public function actionSkin() {

		$gform = Loader::load('Form', array('gform', 'post', false));
		$gform->save('dataform');
		$req_error = $gform->arequired();

		$action = ($this->id) ? 'modify' : 'insert';

		$link_error = $this->_home."?evt[$this->_interface-manageLayout]&block=skin&id=$this->id&action=$action";

		if($req_error > 0) 
			exit(error::errorMessage(array('error'=>1), $link_error));

    $this->label = cleanVar($_POST, 'label', 'string', null);
    $this->session = cleanVar($_POST, 'session', 'string', null);
    $this->rexp = cleanVar($_POST, 'rexp', 'string', null);
    $this->urls = cleanVar($_POST, 'urls', 'string', null);
    $this->template = cleanVar($_POST, 'template', 'int', null);
    $this->css = cleanVar($_POST, 'css', 'int', null);
    $this->auth = cleanVar($_POST, 'auth', 'string', null);
    $this->cache = cleanVar($_POST, 'cache', 'int', null);

		if(!$this->id) $this->priority = skin::newSkinPriority();
		$this->updateDbData();

		header("Location: $this->_home?evt[$this->_interface-manageLayout]&block=skin");
		exit();
	}
	
	/**
	 * Form per l'eliminazione di una skin
	 * 
	 * @return string
	 */
	public function formDelSkin() {
	
    $gform = Loader::load('Form', array('gform', 'post', false));
    $gform->load('dataform');

    $title = sprintf(_('Elimina skin "%s"'), $this->label);

    $buffer = "<p class=\"backoffice-info\">"._('Attenzione! L\'eliminazione è definitiva')."</p>";
    $required = '';
    $buffer .= $gform->open($this->_home."?evt[$this->_interface-actionDelSkin]", '', $required);
    $buffer .= $gform->hidden('id', $this->id);
    $buffer .= $gform->cinput('submit_action', 'submit', _("elimina"), _('Sicuro di voler procedere?'), array("classField"=>"submit"));
    $buffer .= $gform->close();

    $view = new view();
    $view->setViewTpl('section');
    $dict = array(
      'title' => $title,
      'class' => 'admin',
      'content' => $buffer
    );

    return $view->render($dict);
  }
	
	/**
	 * Eliminazione di una skin
	 */
	public function actionDelSkin() {
		
		$this->_registry->trd->deleteTranslations($this->_tbl_data, $this->id);
		$this->deleteDbData();
		
		header("Location: $this->_home?evt[$this->_interface-manageLayout]&block=skin");
		exit();
	}

	/**
	 * Descrizione della procedura
	 * 
	 * @return string
	 */
	public static function layoutInfo() {
	
		$buffer = "<h2>"._("Skin")."</h2>\n";
		$buffer .= "<p>"._("In questa sezione si definiscono le skin che comprendono un file css (opzionale) ed un template e che possono essere associate a")."</p>";
		$buffer .= "<ul>
		<li>"._("un url")."</li>
		<li>"._("una serie di url")."</li>
		<li>"._("una classe di url")."</li>
		</ul>";
		$buffer .= "<p>"._("Questi metodi possono essere abbinati o meno ad una variabile di sessione.")."</p>";
		$buffer .= "<h3>"._("Funzionamento")."</h3>\n";
		$buffer .= "<p>"._("La ricerca di una corrispondenza pagina richiesta/skin avviene in base a dei principi di priorità secondo i quali vengono controllati prima gli url/classi di url appartenenti a skin che hanno un valore di variabile di sessione; successivamente vengono controllati quelli appartenenti a skin che non hanno un valore di variabile di sessione.")."</p>";
		$buffer .= "<p>"._("L'ordine di priorità delle skin è definito dall'ordine in cui compaiono nell'elenco a sinistra e modificabile per trascinamento.")."</p>";
		$buffer .= "<p>"._("Nel campo <b>Variabile di sessione</b> che compare nel form di modifica o inserimento si può inserire il valore di una variabile di sessione nel formato \"nome_variabile=valore\", per il quale verranno applicate le regole di matching di url e classi.<br />Nel campo <b>Urls</b> si può inserire un indirizzo o più indirizzi separati da virgola ai quali associare la skin. Tali indirizzi hanno la <b>priorità</b> rispetto alle classi di url nel momento in cui viene cercata la skin da associare al documento richiesto.
		<br />Le classi di url, definite mediante il campo <b>Espressione regolare</b>, nel formato PCRE permettono di fare il matching con tutti gli url che soddisfano l'espressione regolare inserita.")."</p>\n";

		$buffer .= "<h3>"._("Regole di matching url/classi")."</h3>\n";
		$buffer .= "<p>"._("Quando viene richiesta una pagina (url) il sistema inizia a controllare il matching tra la pagina richiesta e gli indirizzi associati alle skin.
		<br />Se il matching non viene trovato, la ricerca continua utilizzando le espressioni regolari.")."</p>\n";

		$buffer .= "<p>"._("Nei campi 'Espressione regolare' e 'Urls' possono essere inseriti valori nel formato permalink o in quello nativo di gino.")."</p>";
		
		return $buffer;
	}
}

?>
