<?php
/**
 * @file class.Skin.php
 * @brief Contiene la definizione ed implementazione della classe Gino.Skin
 * 
 * @copyright 2005-2018 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
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
 * @copyright 2005-2018 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
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
     * @return void
     */
    function __construct($id) {

    	$this->_tbl_data = self::$table;
    	
    	parent::__construct($id);
    	
    	$this->_interface = 'layout';
    }
    
    /**
     * @brief Rappresentazione a stringa dell'oggetto
     * @return string
     */
    function __toString() {
        
        return (string) $this->ml('label');
    }
    
    /**
     * 
     * @return array
     */
    public static function columns() {
        
        $registry = Registry::instance();
    	
    	$columns['id'] = new \Gino\IntegerField(array(
    		'name' => 'id',
    		'primary_key' => true,
    		'auto_increment' => true,
    		'max_lenght' => 11
    	));
    	$columns['label'] = new \Gino\CharField(array(
    		'name' => 'label',
    	    'label' => _("Etichetta"),
    		'required' => true,
    		'max_lenght' => 200
    	));
    	$columns['session'] = new \Gino\CharField(array(
    		'name' => 'session',
    	    'label' => array(_("Variabile di sessione"), sprintf(_("impostare le regole di matching di url e classi; come esempio:%s%s"), "<br />", "mobile=1")),
    		'max_lenght' => 128
    	));
    	$columns['rexp'] = new \Gino\CharField(array(
    		'name' => 'rexp',
    	    'label' => array(_("Espressione regolare"), sprintf(_("esempi:%s%s"), "<br />", $registry->router->exampleUrl('regexp'))),
    		'max_lenght' => 200
    	));
    	$columns['urls'] = new \Gino\CharField(array(
    		'name' => 'urls',
    	    'label' => array(_("Indirizzi"), sprintf(_("Indicare uno o più indirizzi separati da virgole; esempi:%s%s"), "<br />", $registry->router->exampleUrl('url'))),
    		'max_lenght' => 200
    	));
    	$columns['highest'] = new \Gino\BooleanField(array(
    		'name' => 'highest',
    		'label' => array(_("Priorità massima"), _("da utilizzare per bypassare le skin con variabile di sessione")),
    		'required' => true,
    		'default' => 0
    	));
    	$columns['template'] = new \Gino\ForeignKeyField(array(
    	    'name' => 'template',
    	    'label' => _("Template"),
    	    'required' => true,
    	    'foreign' => '\Gino\Template',
    	    'foreign_order' => 'label ASC',
    	    'add_related' => false,
    	    'max_lenght' => 11,
    	));
    	$columns['css'] = new \Gino\ForeignKeyField(array(
    	    'name' => 'css',
    	    'label' => _("Css"),
    	    'required' => false,
    	    'foreign' => '\Gino\Css',
    	    'foreign_order' => 'label ASC',
    	    'add_related' => false,
    	    'max_lenght' => 11,
    	));
    	
    	$columns['priority'] = new \Gino\IntegerField(array(
    		'name' => 'priority',
    		'required' => true,
    		'max_lenght' => 11
    	));
    	$columns['auth'] = new \Gino\EnumField(array(
    		'name' => 'auth',
    	    'label' => array(_("Autenticazione"), _('<b>si</b>: la skin viene considerata solo se l\'utente è autenticato.<br /><b>no</b>: viceversa.<br /><b>si & no</b>: la skin viene sempre considerata.')),
    		'required' => true,
    		'choice' => array("" => _("si & no"), "yes" => _("si"), "no" => _("no")),
    		'value_type' => 'string'
    	));
    	$columns['cache'] = new \Gino\IntegerField(array(
    		'name' => 'cache',
    	    'label' => array(_("Tempo di caching dei contenuti (s)"), _("Se non si vogliono tenere in cache o non se ne conosce il significato lasciare vuoto o settare a 0")),
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
    	
    	// Ricerca skin con impostazione highest
    	$bs = array();
    	$rows = $registry->db->select('id, session, rexp, urls, auth', self::$table, "highest='1'", array('order' => 'priority ASC'));
    	if($rows && count($rows)) {
    		
    		foreach($rows as $row) {
	    		if(!empty($row['urls'])) {
	    			$value = self::skinValue($row, $request, 'urls');
	    			if(!is_null($value)) {
	    				return $value;
	    			}
	    		}
	    		
	    		if(!empty($row['rexp'])) {
	    			$value = self::skinValue($row, $request, 'rexp');
	    			if(!is_null($value)) {
	    				return $value;
	    			}
	    		}
    		}
    	}
    	
    	$rows = $registry->db->select('id, session, rexp, urls, auth', self::$table, "highest='0'", array('order' => 'priority ASC'));
    	if($rows and count($rows)) {
    		
    	    // Ricerca skin con valori di sessione (campo @a session)
    	    foreach($rows as $row) {
    			
    			$session_array = explode("=", trim($row['session']));
    			if(count($session_array) == 2) {
    				
    				$param_session = $session_array[0];
    				$value_session = $session_array[1];
    				
    				if(isset($session->$param_session) && $session->$param_session == $value_session) {
    
    					if(!empty($row['urls'])) {
    						$value = self::skinValue($row, $request, 'urls');
    						if(!is_null($value)) {
    							return $value;
    						}
    					}
    
    					if(!empty($row['rexp'])) {
    						$value = self::skinValue($row, $request, 'rexp');
    						if(!is_null($value)) {
    							return $value;
    						}
    					}
    				}
    			}
    		}

    		// Ricerca skin con valori nel campo @a urls
    		foreach($rows as $row) {
    
    			if(!$row['session'] && !empty($row['urls'])) {
    				$value = self::skinValue($row, $request, 'urls');
    				if(!is_null($value)) {
    					return $value;
    				}
    			}
    		}

    		// Ricerca skin con valori nel campo @a rexp
    		foreach($rows as $row) {
    
    			if(!$row['session'] && !empty($row['rexp'])) {
    				$value = self::skinValue($row, $request, 'rexp');
    				if(!is_null($value)) {
    					return $value;
    				}
    			}
    		}
    		return FALSE;
    	}
    
    	return FALSE;
    }
    
    /**
     * @brief Ritorna una skin se verifica la corrispondenza con l'indirizzo richiesto
     * 
     * @param array $item alcuni valori di una skin (id|session|rexp|urls|auth)
     * @param object $request oggetto Gino.Http.Request
     * @param string $field nome di alcuni campi della tabella @a sys_layout_skin (urls|rexp)
     * @return \Gino\Skin|NULL
     */
    private static function skinValue($item, $request, $field) {
    	
    	if($field == 'urls') {
    		
    		$urls = explode(",", $item['urls']);
    		foreach($urls as $url)
    		{
    			// url esatto nella forma abbellita o espansa
    			if($url == $request->url or $url == $request->path) {
    				if($item['auth'] == '' or ($request->user->id && $item['auth'] == 'yes') or (!$request->user->id and $item['auth'] == 'no'))
    					return new Skin($item['id']);
    			}
    		}
    	}
    	elseif($field == 'rexp') {
    		
    		if(preg_match($item['rexp'], $request->url) or preg_match($item['rexp'], $request->path))
    		{
    			if($item['auth'] == '' or ($request->user->id and $item['auth'] == 'yes') or (!$request->user->id and $item['auth'] == 'no'))
    				return new Skin($item['id']);
    		}
    	}
    	
    	return null;
    }

    /**
     * @brief Elimina dalla tabella delle skin il riferimento a uno specifico file css
     *
     * @param integer $id valore ID del css associato alla skin
     * @return bool, update result
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
     * @return bool, update result
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
     * @return integer, priority value
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
     * @return null
     */
    public function sortUp() {

        $priority = $this->priority;
        $before_skins = self::objects(null, array('where' => "priority<'".$priority."'", "order" => "priority DESC", "limit" => array(0, 1)));
        $before_skin = $before_skins[0];
        $this->priority = $before_skin->priority;
        $this->save(array('only_update' => 'priority'));

        $before_skin->priority = $priority;
        $before_skin->save(array('only_update'=>'priority'));
        
        return null;
    }

    /**
     * @brief Form per la creazione e la modifica di una skin
     * @return string, html form
     */
    public function formSkin() {

        Loader::import('class', array('\Gino\Css', '\Gino\Template'));

        $title = ($this->id)? _("Modifica")." ".htmlChars($this->label):_("Nuova skin");
        
        $mform = \Gino\Loader::load('ModelForm', array($this, array(
            'form_id' => 'gform',
        )));
        
        $buffer = $mform->view(
            array(
                'session_value' => 'dataform',
                'show_save_and_continue' => false,
                'view_title' => false,
                'f_action' => $this->_registry->router->link($this->_interface, 'actionSkin'),
                's_value' => (($this->id) ? _("modifica") : _("inserisci")),
                'removeFields' => ['priority']
            ),
            array(
                'description' => array("cols" => 45, "rows" => 4, "trnsl" => false),
                'cache' => ["pattern" => "^\d*$"]
            )
        );

        $view = new View();
        $view->setViewTpl('section');
        $dict = array(
            'title' => $title,
            'class' => null,
            'content' => $buffer
        );

        return $view->render($dict);
    }

    /**
     * @brief Processa il form di inserimento e modifica di una skin
     * @see self::formSkin()
     * @param \Gino\Http\Request $request istanza di Gino.Request
     * @return Gino.Http.Redirect
     */
    public function actionSkin(\Gino\Http\Request $request) {

        $gform = Loader::load('Form', array());
        //$gform->setValidation(false);
        $gform->saveSession('dataform');
        $req_error = $gform->checkRequired();

        $action = ($this->id) ? 'modify' : 'insert';

        $link_error = $this->_registry->router->link($this->_interface, 'manageLayout', array(), "block=skin&id=$this->id&action=$action");

        if($req_error > 0) {
        	return Error::errorMessage(array('error'=>1), $link_error);
        }
        $this->label = cleanVar($request->POST, 'label', 'string', null);
        $this->session = cleanVar($request->POST, 'session', 'string', null);
        $this->rexp = cleanVar($request->POST, 'rexp', 'string', null);
        $this->urls = cleanVar($request->POST, 'urls', 'string', null);
        $this->highest = cleanVar($request->POST, 'highest', 'int', null);
        $this->template = cleanVar($request->POST, 'template', 'int', null);
        $this->css = cleanVar($request->POST, 'css', 'int', null);
        $this->auth = cleanVar($request->POST, 'auth', 'string', null);
        $this->cache = cleanVar($request->POST, 'cache', 'int', null);

        if(!$this->id) {
        	$this->priority = skin::newSkinPriority();
        }
        
        $this->save();
        
        return new Redirect($this->_registry->router->link($this->_interface, 'manageLayout', array(), array('block' => 'skin')));
    }

    /**
     * @brief Form per l'eliminazione di una skin
     *
     * @return string, html form
     */
    public function formDelSkin() {

        $gform = Loader::load('Form', array());
        $gform->load('dataform');

        $title = sprintf(_('Elimina skin "%s"'), $this->label);

        $buffer = "<p class=\"backoffice-info\">"._('Attenzione! L\'eliminazione è definitiva')."</p>";
        
        $formaction = $this->_registry->router->link($this->_interface, 'actionDelSkin');

        $buffer .= $gform->open($formaction, '', '', array('form_id'=>'gform', 'validation'=>false));
        $buffer .= \Gino\Input::hidden('id', $this->id);
        
        $submit = \Gino\Input::submit('submit_action', _("elimina"));
        $buffer .= \Gino\Input::placeholderRow( _('Sicuro di voler procedere?'), $submit);
        $buffer .= $gform->close();

        $view = new View();
        $view->setViewTpl('section');
        $dict = array(
            'title' => $title,
            'class' => null,
            'content' => $buffer
        );

        return $view->render($dict);
    }

    /**
     * @bief Processa il form di eliminazione di una skin
     * @see self::formDelSkin()
     * @param \Gino\Http\Request $request istanza di Gino.Request
     * @return Gino.Http.Redirect
     */
    public function actionDelSkin(\Gino\Http\Request $request) {

        $this->_registry->trd->deleteTranslations($this->_tbl_data, $this->id);
        $this->deleteDbData();

        return new Redirect($this->_registry->router->link($this->_interface, 'manageLayout', array(), array('block' => 'skin')));
    }

    /**
     * @brief Descrizione della procedura
     *
     * @return string, html info
     */
    public static function layoutInfo() {

        $buffer = "<h2>"._("Skin")."</h2>\n";
        $buffer .= "<p>".sprintf(_("In questa sezione si definiscono le skin che richiamano uno specifico template 
e che opzionalmente possono richiamare un file css.%s
Ogni skin viene associata agli indirizzi specificati nei campi <i>Espressione regolare</i> e <i>Indirizzi</i>. 
Inoltre la skin può essere abbinata o meno ad una variabile di sessione e può essere impostata con <i>Priorità massima</i>."), "<br />")."</p>";

        $buffer .= "<h3>"._("Funzionamento")."</h3>\n";
        $buffer .= "<p>".sprintf(_("La ricerca di una corrispondenza tra la pagina richiesta e la skin avviene in base a dei principi 
di priorità in base ai quali vengono controllati prima gli indirizzi appartenenti a skin con priorità massima e successivamente 
quelli appartenenti a skin con una variabile di sessione.%s
A seguire vengono controllati gli indirizzi delle skin senza priorità massima e senza variabile di sessione definiti 
nel campo 'urls' e successivamente le classi di indirizzi definite nel campo 'rexp'."), "<br />")."</p>";

        $buffer .= "<p>"._("Gli indirizzi definiti nel campo 'urls' hanno sempre la precedenza sulle classi di indirizzi 
definite nel campo 'rexp', anche nel caso di skin con priorità massima e di skin con variabile di sessione. 
Ad ogni condizione di confronto (priorità massima, variabile di sessione, indirizzi, classi di indirizzi) l'ultimo fattore 
che dirime la priorità di precedenza di una skin è infine dato dal campo 'priority', identificato dall'ordine 
nel quale le skin compaiono nell'elenco (in alto ci sono le skin con priorità maggiore).")."</p>";

        $buffer .= "<h3>"._("Dettagli campi del form")."</h3>\n";
        $buffer .= "<p>".sprintf(_("Nel campo <i>Variabile di sessione</i> si può inserire il valore di una variabile di sessione 
nel formato 'sessionname=sessionvalue'. Nelle skin con variabile di sessione deve essere impostato anche il campo 
<i>Indirizzi</i> (urls) o il campo <i>Espressione regolare</i> (rexp), ricordando che il primo predomina sul secondo.%s
Nel caso in cui non si debba abbinare alla variabile di sessione un particolare indirizzo o espressione regolare, dandole quindi 
una priorità su tutti gli indirizzi, è necessario impostare il campo rexp col valore <i>#.*#</i>."), "<br />")."</p>\n";

        $buffer .= "<p>".sprintf(_("Nel campo <i>Indirizzi</i> si può inserire un indirizzo o più indirizzi separati da virgola 
ai quali associare la skin. Tali indirizzi hanno la priorità rispetto alle classi di indirizzi nel momento in cui 
viene cercata la skin da associare al documento richiesto.%s
Le classi di indirizzi, definite mediante il campo <i>Espressione regolare</i> nel formato PCRE, permettono di fare il matching 
con tutti gli indirizzi che soddisfano l'espressione regolare inserita."), "<br />")."</p>";
        $buffer .= "<p>"._("Nei campi <i>Espressione regolare</i> e <i>Indirizzi</i> possono essere inseriti valori nel formato permalink 
o in quello nativo di gino.")."</p>";

        $buffer .= "<h3>"._("Sintesi regole di matching indirizzi/classi")."</h3>\n";
        $buffer .= "<p>".sprintf(_("Quando viene richiesta una pagina (url) il sistema inizia a controllare il matching 
tra la pagina richiesta e gli indirizzi associati alle skin a partire dalla skin col valore del campo priority 
più basso (priorità maggiore) continuando a salire.%s
Se la corrispondenza non viene trovata, la ricerca continua utilizzando le espressioni regolari associate alle skin, 
sempre a partire dalla skin col valore del campo priority più basso."), "<br />")."</p>";
        $buffer .= "<p>"._("L'ordine di priorità delle skin può essere aggirato utilizzando 
il campo <i>Priorità massima</i> e il campo <i>Variabile di sessione</i>. Le skin con queste impostazioni vengono infatti 
controllate prima delle altre.")."</p>";

        return $buffer;
    }
}

Skin::$columns=Skin::columns();