<?php
/**
 * @file class.Skin.php
 * @brief Contiene la definizione ed implementazione della classe Gino.Skin
 * 
 * @copyright 2005-2014 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */

namespace Gino;

use Gino\Http\Redirect;

/**
 * @brief Libreria per la gestione delle Skin
 *
 * Le Skin sono l'unione di un template, un css (opzionale), e delle rules che permettono di associarle ad un url.
 * Dato un url il sistema ricava la skin associata ed utilizza il template per generare il documento html completo.
 * @see Gino.App.Layout
 * @copyright 2005-2014 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */

class Skin extends Model {

	public static $table = 'sys_layout_skin';
	public static $columns;
	
    private $_interface;

    /**
     * @brief Costruttore
     *
     * @param integer $id valore ID del record
     * @return istanza di Gino.Skin
     */
    function __construct($id) {

    	$this->_tbl_data = self::$table;
    	
    	parent::__construct($id);
    	
    	$this->_interface = 'layout';
    }
    
    public static function columns() {
    	
    	$columns['id'] = new \Gino\IntegerField(array(
    		'name'=>'id',
    		'primary_key'=>true,
    		'auto_increment'=>true,
    	));
    	$columns['label'] = new \Gino\CharField(array(
    		'name' => 'label',
    		'required' => true,
    		'max_lenght' => 200
    	));
    	$columns['session'] = new \Gino\CharField(array(
    		'name' => 'session',
    		'required' => false,
    		'max_lenght' => 128
    	));
    	$columns['rexp'] = new \Gino\CharField(array(
    		'name' => 'rexp',
    		'required' => false,
    		'max_lenght' => 200
    	));
    	$columns['urls'] = new \Gino\CharField(array(
    		'name' => 'urls',
    		'required' => false,
    		'max_lenght' => 200
    	));
    	$columns['template'] = new \Gino\CharField(array(
    		'name' => 'template',
    		'required' => true,
    		'max_lenght' => 200
    	));
    	$columns['css'] = new \Gino\IntegerField(array(
    		'name' => 'css',
    		'required' => true,
    	));
    	$columns['priority'] = new \Gino\IntegerField(array(
    		'name' => 'priority',
    		'required' => true,
    	));
    	$columns['auth'] = new \Gino\EnumField(array(
    		'name' => 'auth',
    		'required' => true,
    		'enum' => array('yes', 'no')
    	));
    	$columns['cache'] = new \Gino\IntegerField(array(
    		'name' => 'cache',
    		'required' => true,
    		'default' => 0
    	));
    	return $columns;
    }

    /**
     * @brief Recupera la skin corrispondente all'url della request
     *
     * @param \Gino\Http\Request $request
     * @return skin trovata oppure FALSE
     */
    public static function getSkin(\Gino\Http\Request $request) {

        $registry = Registry::instance();
        $session = $request->session;

        $rows = $registry->db->select('id, session, rexp, urls, auth', self::$table, null, array('order' => 'priority ASC'));
        if($rows and count($rows)) {
            /**
             * Variabile di sessione -> urls -> rexp
             */
            foreach($rows as $row) {
                $session_array = explode("=", trim($row['session']));
                if(count($session_array) == 2) {
                    if(isset($session->$session_array[0]) && $session->$session_array[0] == $session_array[1]) {
                        $urls = explode(",", $row['urls']);
                        // url esatto nella forma abbellita o espansa
                        foreach($urls as $url)
                        {
                            if($url == $request->url or $url == $request->path) {
                                if($row['auth'] == '' or ($request->user->id and $row['auth']=='yes') or (!$request->user->id and $row['auth'] == 'no'))
                                    return new Skin($row['id']);
                            }
                        }

                        if(!empty($row['rexp']))
                        {
                            if(preg_match($row['rexp'], $request->url) || preg_match($row['rexp'], $request->path))
                            {
                                if($row['auth'] == '' or ($request->user->id and $row['auth'] == 'yes') or (!$request->user->id and $row['auth'] == 'no'))
                                    return new Skin($row['id']);
                            }
                        }
                    }
                }
            }

            /**
             * Urls
             */
            foreach($rows as $row) {

                if(!$row['session']) {
                    $urls = explode(",", $row['urls']);
                    foreach($urls as $url) 
                    {
                        if($url == $request->url or $url == $request->path) { 
                            if($row['auth'] == '' or ($request->user->id && $row['auth'] == 'yes') or (!$request->user->id and $row['auth'] == 'no'))
                                return new Skin($row['id']);
                        }
                    }
                }
            }

            /**
             * Rexp
             */
            foreach($rows as $row) {

                if(!$row['session'] && !empty($row['rexp']))
                {
                    if(preg_match($row['rexp'], $request->url) or preg_match($row['rexp'], $request->path))
                    {
                        if($row['auth'] == '' or ($request->user->id and $row['auth'] == 'yes') or (!$request->user->id and $row['auth'] == 'no'))
                            return new Skin($row['id']);
                    }
                }
            }
            return FALSE;
        }

        return FALSE;
    }

    /**
     * @brief Elimina dalla tabella delle skin il riferimento a uno specifico file css
     *
     * @param integer $id valore ID del css associato alla skin
     * @return risultato operazione, bool
     */
    public static function removeCss($id) {

        $db = Db::instance();
        $res = $db->update(array(
            'css' => 0
        ), self::$table, "css='$id'");

        return $res;
    }

    /**
     * @brief Elimina dalla tabella delle skin il riferimento a uno specifico template
     *
     * @param integer $id valore ID del template associato alla skin
     * @return risultato operazione, bool
     */
    public static function removeTemplate($id) {

        $db = Db::instance();
        $res = $db->update(array(
            'template' => 0
        ), self::$table, "template='$id'");

        return $res;
    }

    /**
     * @brief Priorità di una nuova skin
     *
     * @return priorità
     */
    public static function newSkinPriority() {

        $db = Db::instance();
        $rows = $db->select('MAX(priority) as m', self::$table);
        if($rows and count($rows)) {
            return ($rows[0]['m'] + 1);
        }
        return 1;
    }

    /**
     * @brief Aumenta di 1 la priorità della skin e scala le altre
     * @return risultato operazione, bool
     */
    public function sortUp() {

        $priority = $this->priority;
        $before_skins = self::objects(null, array('where' => "priority<'".$priority."'", "order" => "priority DESC", "limit" => array(0, 1)));
        $before_skin = $before_skins[0];
        $this->priority = $before_skin->priority;
        $this->save();

        $before_skin->priority = $priority;
        $before_skin->save();
    }

    /**
     * @brief Form per la creazione e la modifica di una skin
     * @return codice html form
     */
    public function formSkin() {

        Loader::import('class', array('\Gino\Css', '\Gino\Template'));

        $gform = Loader::load('Form', array('gform', 'post', true));
        $gform->load('dataform');

        $title = ($this->id)? _("Modifica")." ".htmlChars($this->label):_("Nuova skin");
        
        $formaction = $this->_registry->router->link($this->_interface, 'actionSkin');

        $required = 'template';
        $buffer = $gform->open($formaction, '', $required);
        $buffer .= $gform->hidden('id', $this->id);
        
        $buffer .= $gform->cinput('label', 'text', $gform->retvar('label', htmlInput($this->label)), _("Etichetta"), array("required"=>true, "size"=>40, "maxlength"=>200, "trnsl"=>true, "trnsl_table"=>$this->_tbl_data, "field"=>"label", "trnsl_id"=>$this->id));
        $buffer .= $gform->cinput('session', 'text', $gform->retvar('session', $this->session), array(_("Variabile di sessione"), _("esempi").":<br />mobile=1"), array("size"=>40, "maxlength"=>200));
        $buffer .= $gform->cinput('rexp', 'text', $gform->retvar('rexp', $this->rexp), array(_("Espressione regolare"), _("esempi").":<br />#\?evt\[news-(.*)\]#<br />#^news/(.*)#"), array("size"=>40, "maxlength"=>200));
        $buffer .= $gform->cinput('urls', 'text', $gform->retvar('urls', htmlInput($this->urls)), array(_("Urls"), _("Indicare uno o più indirizzi separati da virgole; esempi").":<br />index.php?evt[news-viewList]<br />news/viewList"), array("size"=>40, "maxlength"=>200));
        $css_list = array();
        
        foreach(Css::getAll() as $css) {
            $css_list[$css->id] = htmlInput($css->label);
        }
        $buffer .= $gform->cselect('css', $gform->retvar('css', $this->css), $css_list, _("Css"));
        
        $tpl_list = array();
        foreach(Template::objects(null, array('order' => 'label')) as $tpl) {
            $tpl_list[$tpl->id] = htmlInput($tpl->label);
        }
        $buffer .= $gform->cselect('template', $gform->retvar('template', $this->template), $tpl_list, _("Template"), array("required"=>true));
        $buffer .= $gform->cradio('auth', $gform->retvar('auth', $this->auth), array(""=>"si & no", "yes"=>_("si"),"no"=>_("no")), '', array(_("Autenticazione"), _('<b>si</b>: la skin viene considerata solo se l\'utente è autenticato.<br /><b>no</b>: viceversa.<br /><b>si & no</b>: la skin viene sempre considerata.')), array("required"=>true));
        $buffer .= $gform->cinput('cache', 'text', $gform->retvar('cache', $this->cache), array(_("Tempo di caching dei contenuti (s)"), _("Se non si vogliono tenere in cache o non se ne conosce il significato lasciare vuoto o settare a 0")), array("size"=>6, "maxlength"=>16, "pattern"=>"^\d*$"));

        $buffer .= $gform->cinput('submit_action', 'submit', (($this->id)?_("modifica"):_("inserisci")), '', array("classField"=>"submit"));

        $buffer .= $gform->close();

        $view = new View();
        $view->setViewTpl('section');
        $dict = array(
            'title' => $title,
            'class' => 'admin',
            'content' => $buffer
        );

        return $view->render($dict);
    }

    /**
     * @brief Processa il form di inserimento e modifica di una skin
     * @see self::formSkin()
     * @param \Gino\Http\Request $request istanza di Gino.Request
     * @return Gino.Http.Response
     */
    public function actionSkin(\Gino\Http\Request $request) {

        $gform = Loader::load('Form', array('gform', 'post', false));
        $gform->save('dataform');
        $req_error = $gform->arequired();

        $action = ($this->id) ? 'modify' : 'insert';

        $link_error = $this->_registry->router->link($this->_interface, 'manageLayout', array(), "block=skin&id=$this->id&action=$action");

        if($req_error > 0)
            return error::errorMessage(array('error'=>1), $link_error);

        $this->label = cleanVar($request->POST, 'label', 'string', null);
        $this->session = cleanVar($request->POST, 'session', 'string', null);
        $this->rexp = cleanVar($request->POST, 'rexp', 'string', null);
        $this->urls = cleanVar($request->POST, 'urls', 'string', null);
        $this->template = cleanVar($request->POST, 'template', 'int', null);
        $this->css = cleanVar($request->POST, 'css', 'int', null);
        $this->auth = cleanVar($request->POST, 'auth', 'string', null);
        $this->cache = cleanVar($request->POST, 'cache', 'int', null);

        if(!$this->id) $this->priority = skin::newSkinPriority();
        $this->save();

        return new Redirect($this->_registry->router->link($this->_interface, 'manageLayout', array(), array('block' => 'skin')));
    }

    /**
     * @brief Form per l'eliminazione di una skin
     *
     * @return codice html form
     */
    public function formDelSkin() {

        $gform = Loader::load('Form', array('gform', 'post', false));
        $gform->load('dataform');

        $title = sprintf(_('Elimina skin "%s"'), $this->label);

        $buffer = "<p class=\"backoffice-info\">"._('Attenzione! L\'eliminazione è definitiva')."</p>";
        
        $formaction = $this->_registry->router->link($this->_interface, 'actionDelSkin');
        $required = '';
        $buffer .= $gform->open($formaction, '', $required);
        $buffer .= $gform->hidden('id', $this->id);
        $buffer .= $gform->cinput('submit_action', 'submit', _("elimina"), _('Sicuro di voler procedere?'), array("classField"=>"submit"));
        $buffer .= $gform->close();

        $view = new View();
        $view->setViewTpl('section');
        $dict = array(
          'title' => $title,
          'class' => 'admin',
          'content' => $buffer
        );

        return $view->render($dict);
    }

    /**
     * @bief Processa il form di eliminazione di una skin
     * @see self::formDelSkin()
     * @param \Gino\Http\Request $request istanza di Gino.Request
     * @return Gino.Http.Response
     */
    public function actionDelSkin(\Gino\Http\Request $request) {

        $this->_registry->trd->deleteTranslations($this->_tbl_data, $this->id);
        $this->deleteDbData();

        return new Redirect($this->_registry->router->link($this->_interface, 'manageLayout', array(), array('block' => 'skin')));
    }

    /**
     * @brief Descrizione della procedura
     *
     * @return informazioni, codice html
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

Skin::$columns=Skin::columns();