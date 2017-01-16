<?php
/**
 * @file class.MenuVoice.php
 * @brief Contiene la definizione ed implementazione della classe Gino.App.Menu.MenuVoice
 * 
 * @copyright 2005-2017 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
namespace Gino\App\Menu;

use \Gino\View;

/**
 * @brief Classe di tipo Gino.Model che rappresenta una voce di menu
 * 
 * @copyright 2005-2017 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class MenuVoice extends \Gino\Model {

    public static $tbl_voices = "sys_menu_voices";
    public static $columns;
    
    private static $_tbl_user_role = "user_role";
    private $_gform;

    /**
     * @brief Costruttore
     * 
     * @param integer $id valore ID della voce di menu
     * @return istanza di Gino.App.Menu.MenuVoice
     */
    function __construct($id) {

        $this->_tbl_data = self::$tbl_voices;
        parent::__construct($id);
    }

    /**
     * @brief Elimina le voci di menu di una istanza (nella procedura di eliminazione di una istanza)
     * 
     * @param integer $instance valore id dell'istanza
     * @return TRUE
     */
    public static function deleteInstanceVoices($instance) {

        $db = \Gino\db::instance();

        $items = $db->select("id", self::$tbl_voices, "instance='$instance' AND parent='0'");
        if($items && count($items)) {

            foreach($items as $item) {
                \Gino\Translation::deleteTranslations(self::$tbl_voices, $item['id']);
                $mv = new MenuVoice($item['id']);
                $mv->deleteVoice();
            }
        }

        return TRUE;
    }

    /**
     * @brief Recupera la prima posizione disponibile per l'ordinamento
     * @return numero posizione ordinamento
     */
    public function initOrderList() {

        $res = $this->_db->select("max(order_list) AS last", self::$tbl_voices, "instance='{$this->_p['instance']}' AND parent='{$this->_p['parent']}'");
        if($res && count($res)) {
            $last = $res[0]['last'];
        }
        else $last = 0;

        $this->order_list = $last+1;
    }

    /**
     * @brief Query di aggiornamento della sequenza delle voci di menu
     * @return risultato query, bool
     */
    public function updateOrderList() {

        $result = $this->_db->update(array('order_list'=>array('sql'=>"order_list-1")), self::$tbl_voices, "order_list>'".$this->order_list."' AND parent='".$this->parent."'", true);
        return $result;
    }

    /**
     * @brief Elimina una voce di menu e tutte le sue discendenti (ricorsivo)
     * @return void
     */
    public function deleteVoice() {

        foreach($this->getChildren() as $child) $child->deleteVoice();
        $this->deleteDbData();
    }

    /**
     * @brief Figli di primo livello della voce di menu
     * @return array id => Gino.App.Menu.MenuVoice
     */
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

    /**
     * @brief Recupera oggetti di tipo Gino.App.Menu.MenuVoice
     * @param array $options array associativo di opzioni:
     *              - where: where clause
     *              - order: ordinamento
     */
    public static function get($options=null) {

        $res = array();
        $where = \Gino\gOpt('where', $options, null);
        $order = \Gino\gOpt('order', $options, null);
        $db = \Gino\Db::instance();
        $rows = $db->select('id', self::$tbl_voices, $where, array('order' => $order));
        if($rows and count($rows)) {
            foreach($rows as $row) {
                $res[] = new MenuVoice($row['id']);
            }
        }
        return $res;
    }

    /**
     * @brief Controlla se l'utente può vedere la voce di menu
     * @return TRUE se la può vedere, FALSE altrimenti
     */
    public function userCanSee() {

        $request = \Gino\Http\Request::instance();
        if(!$this->perms) {
            return TRUE;
        }
        \Gino\Loader::import('auth', 'Permission');

        foreach(explode(';', $this->perms) as $p) {
            $values = explode(',', $p);
            $perm = new \Gino\App\Auth\Permission($values[0]);
            $instance = $values[1];
            if($request->user->hasPerm($perm->class, $perm->code, $instance)) {
                return TRUE;
            }
        }

        return FALSE;
    }

    /**
     * @brief Form di inserimento e modifica di una voce di menu
     *
     * @param string $formaction indirizzo dell'azione
     * @param integer $parent valore ID della voce parent
     * @return html, form
     */
    public function formVoice($formaction, $parent) {

        \Gino\Loader::import('auth', array('Permission'));

        $gform = \Gino\Loader::load('Form', array());
        $gform->load('dataform');
        
        if(!$parent) $parent = 0;

        $parentVoice = new MenuVoice($parent);

        if($this->_p['id']) {$title = _("Modifica voce"); $submit = _("modifica");$action='modify';}
        else {
            $title = ($parent) ? _("Nuova voce sotto ")."\"".\Gino\htmlChars($parentVoice->label)."\"":_("Nuova voce principale");
            $submit = _("inserisci");
            $action='insert';
        }
        $title = $title."<a name=\"top\"> </a>";

        $buffer = $gform->open($formaction, '', '', array('form_id'=>'gform'));	//$required = 'label,type,voice';
        $buffer .= \Gino\Input::hidden('action', $action);
        $buffer .= \Gino\Input::hidden('parent', $parent);
        if($this->_p['id']) {
        	$buffer .= \Gino\Input::hidden('id', $this->_p['id']);
        }

        $buffer .= \Gino\Input::input_label('label', 'text', $gform->retvar('label', \Gino\htmlInput($this->_p['label'])), _("Voce"), array("required"=>true, "size"=>40, "maxlength"=>200, "trnsl"=>true, "trnsl_table"=>self::$tbl_voices, "field"=>"label", "trnsl_id"=>$this->_p['id']));

        $buffer .= \Gino\Input::input_label('url', 'text', $gform->retvar('url', \Gino\htmlInput($this->_p['url'])), _("url"), array("size"=>40, "maxlength"=>200, "id"=>"url"));

        $buffer .= \Gino\Input::radio_label('type', $gform->retvar('type', \Gino\htmlInput($this->_p['type'])), array('int'=>_("interno (utilizzare la ricerca viste)"), 'ext'=>_("esterno (http://www.otto.to.it)")), 'int', _("Tipo di link"), array("required"=>true, "aspect"=>"v"));

        $buffer .= \Gino\Input::multipleCheckbox('perm[]', explode(';', $this->perms), \Gino\App\Auth\Permission::getForMulticheck(), array(_('Permessi'), _('Se si intende mostrare la voce di menu a tutti gli utenti non selezionare alcun permesso')), null);

        $buffer .= \Gino\Input::input_label('submit_action', 'submit', $submit, '', array("classField"=>"submit"));
        $buffer .= $gform->close();

        $view = new View(null, 'section');
        $dict = array(
            'title' => $title,
            'class' => 'admin',
            'content' => $buffer
        );

        return $view->render($dict);
    }

    /**
     * @brief Ricava la voce di menu corrispondente all'url corrente
     * 
     * @param integer $instance valore ID dell'istanza
     * @return id voce selezionata o null
     */
    public static function getSelectedVoice($instance) {

        $request = \Gino\Http\Request::instance();
        $db = \Gino\Db::instance();

        $result_link = null;
        $result = null;

        // @TODO not HC
        if($request->path == '' || $request->path == '/' || $request->path == 'index.php') {
            return 52;
        }
        
    	$url = urldecode($request->path);
        $url = addslashes($url);
        
        if(substr($url, 0, 1) == '/') {
        	$url1 = substr($url, 1);
        	$url2 = $url1;
        }
        else {
        	$url1 = $url;
        	$url2 = $url1."#";
        }
        $where = "(url='".$url1."' OR url='".$url2."') AND instance='".$instance."'";
        $rows = $db->select('id, url', self::$tbl_voices, $where);
        if($rows and count($rows)) {
          return $rows[0]['id'];
        }
        return null;
    }
    
    /**
     * Struttura dei campi della tabella di un modello
     *
     * @return array
     */
    public static function columns() {
    	 
    	$columns['id'] = new \Gino\IntegerField(array(
    		'name'=>'id',
    		'primary_key'=>true,
    		'auto_increment'=>true,
    	));
    	$columns['instance'] = new \Gino\IntegerField(array(
    		'name'=>'instance',
    		'required'=>true,
    	));
    	$columns['parent'] = new \Gino\IntegerField(array(
    		'name'=>'parent',
    		'required'=>true,
    	));
    	$columns['label'] = new \Gino\CharField(array(
    		'name'=>'label',
    		'label' => _("Voce"),
    		'required'=>true,
    		'max_lenght'=>200,
    	));
    	$columns['url'] = new \Gino\CharField(array(
    		'name'=>'url',
    		'required'=>true,
    		'max_lenght'=>200,
    	));
    	$columns['type'] = new \Gino\EnumField(array(
    		'name' => 'type',
    		'label' => _("Tipo di link"), 
    		'required' => true,
    		'choice' => array('int'=>_("interno"), 'ext'=>_("esterno"))
    	));
    	$columns['order_list'] = new \Gino\IntegerField(array(
    		'name' => 'order_list',
    		'required' => true,
    	));
    	$columns['perms'] = new \Gino\CharField(array(
    		'name'=>'perms',
    		'label' => _("Permessi"),
    		'required'=>true,
    		'max_lenght'=>255,
    	));
    	return $columns;
    }
}

MenuVoice::$columns=MenuVoice::columns();
