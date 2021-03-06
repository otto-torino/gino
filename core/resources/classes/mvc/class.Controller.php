<?php
/**
 * @file class.Controller.php
 * @brief Contiene la definizione ed implementazione della classe Gino.Controller
 */
namespace Gino;

/**
 * @brief Classe astratta primitiva di tipo Controller (MVC), dalla quale tutti i controller delle singole app discendono
 */
abstract class Controller {

    protected $_registry,
              $_db,
              $_access,
              $_session,
              $_plink,
              $_trd,
              $_locale,
              $_permissions,
              $_class_name,
              $_instance,
              $_instance_name,
              $_instance_label,
              $_class_www,
              $_class_img,
              $_data_dir,
              $_data_www,
              $_view_dir,
              $_home;

	/**
	 * @brief Valore del campo tbl_name della tabella TBL_MODULE_APP
	 * @var string
	 */
	private $_tbl_name;
	
	/**
	 * @brief Nome della tabella delle opzioni
	 * @var string
	 */
	protected $_tbl_options;
	
	/**
	 * @brief Valori dei cammpi delle opzioni
	 * @var array, nel formato (string) fieldname => (mixed) value
	 */
	protected $_value_options;
	
	/**
	 * @brief Impostazioni dei campi delle opzioni del controller
	 * @description Richiamato in Gino.Options
	 * @see Gino.Options
	 * @var array, nel formato (string) fieldname => (array) 
	 *     ['label' => mixed, 'value' => mixed, 'section' => boolean, 'section_title' => string, ...]
	 */
	public $_optionsLabels;
	
    /**
     * @brief Inizializza il controller
     * @param int $instance_id id modulo, se diverso da zero il modulo è un'istanza di una classe, altrimenti è la classe di sistema
     * @return void, istanza di Gino.Controller
     */
    function __construct($instance_id = 0) {

        $this->_registry = registry::instance();
        
        if(is_null($this->_registry->session)) {
        	$this->_registry->session = \Gino\Session::instance();
        }

        // alias
        $this->_db = $this->_registry->db;
        $this->_access = $this->_registry->access;
        $this->_session = $this->_registry->session;
        $this->_plink = $this->_registry->plink;
        $this->_trd = $this->_registry->trd;

        $this->_class_name = get_name_class($this);
        $this->setInstanceProperties($instance_id);
        $this->_tbl_name = null;

        $this->_locale = locale::instance_to_class($this->_class_name);

        $this->setPaths();
        
        // Options table/values/properties
        Loader::import('sysClass', 'ModuleApp');
        $module_app = \Gino\App\SysClass\ModuleApp::getFromName(get_name_class($this->_class_name));
        $class_prefix = $module_app->tbl_name;
        
        $this->_tbl_options = $class_prefix.'_opt';
        $this->_value_options = $this->appOptions();
        
        $this->setPropertyOptions();
    }
    
    /**
     * @brief Imposta le opzioni del controller come proprietà
     * @description Nel caso in cui le proprietà vengano dicharate nella classe controller,
     * queste devono essere dichiarate @a protected o @a public.
     * @return null
     */
    protected function setPropertyOptions() {
        
        if(count($this->_value_options)) {
            foreach($this->_value_options as $key => $value) {
                $key = '_'.$key;
                $this->$key = $value;
            }
        }
        return null;
    }
    
    /**
     * @brief Funzione chiamata quando si cerca di chiamare un metodo inaccessibile
     * @param string $name nome metodo
     * @param array $arguments argomenti
     * @return \Exception
     */
    function __call($name, $arguments) {
        throw new \Exception(sprintf(_('"%s" is not a method of "%s" class'), $name, get_class($this)));
    }

    /**
     * @brief Setta le proprietà legate all'istanza
     * @param int $id id dell'istanza
     * @return void
     */
    private function setInstanceProperties($instance_id) {

        if(!$instance_id) {
            $this->_instance = 0;
            $this->_instance_name = $this->_class_name;
            $this->_instance_label = $this->_class_name;
        }
        else {
            $this->_instance = $instance_id;
            $this->_instance_name = $this->_db->getFieldFromId(TBL_MODULE, 'name', 'id', $this->_instance);
            $this->_instance_label = $this->_db->getFieldFromId(TBL_MODULE, 'label', 'id', $this->_instance);
        }
    }

    /**
     *  @brief Setta i percorsi di base dell'app
     *  @return void
     */
    protected function setPaths() {
        
        $this->_class_www = get_app_dir($this->_class_name, true);
        $this->_class_img = $this->_class_www.'/img';
        $this->_home = HOME_FILE;
        $this->_data_dir = CONTENT_DIR.OS.$this->_class_name . ($this->_instance ? OS . $this->_instance_name : '');
        $this->_data_www = CONTENT_WWW.'/'.$this->_class_name . ($this->_instance ? '/' . $this->_instance_name : '');
        $this->_view_dir = get_app_dir($this->_class_name).OS.'views';
    }

    /**
     * @brief Restituisce alcune proprietà della classe
     *
     * @description Le informazioni vengono utilizzate per creare o eliminare istanze. Questo metodo dev'essere sovrascritto da tutte le classi figlie.
     * @return array, lista delle proprietà utilizzate per la creazione di istanze di tipo pagina
     */
    public static function getClassElements() {
        return array();
    }

    /**
     * @brief Espone l'id valore dell'istanza
     * @return integer, id
     */
    public function getInstance() {
        return $this->_instance;
    }

    /**
     * @brief Espone il nome dell'istanza
     * @return string, nome istanza
     */
    public function getInstanceName() {
        return $this->_instance_name;
    }

    /**
     * @brief Espone il nome della classe
     * @return string, nome classe
     */
    public function getClassName() {
        return $this->_class_name;
    }

    /**
     * @brief Percorso assoluto alla cartella dei contenuti
     * @return string, percorso assoluto
     */
    public function getBaseAbsPath() {
        return $this->_data_dir;
    }

    /**
     * @brief Percorso relativo alla cartella dei contenuti
     * @return string, percorso relativo
     */
    public function getBasePath() {
        return $this->_data_www;
    }

    /**
     * @brief Richiama il metodo ononimo di Access passando in automatico classe e istanza
     * @see Access:requirePerm
     * @return void
     */
    public function requirePerm($perm) {
        $this->_access->requirePerm($this->_class_name, $perm, $this->_instance);
    }

    /**
     * @brief Richiama il metodo ononimo di User passando in automatico classe e istanza
     * @see User:hasPerm
     * @return bool
     */
    public function userHasPerm($perm) {
        return $this->_registry->request->user->hasPerm($this->_class_name, $perm, $this->_instance);
    }

    /**
     * @brief Shortcut link per classi di tipo Gino.Controller
     * @see Gino.Router::link
     */
    public function link($instance_name, $method, array $params = array(), $query_string = '', array $kwargs = array()) {
        return $this->_registry->router->link($instance_name, $method, $params, $query_string, $kwargs);
    }

    /**
     * @brief Shortcut link area amministrativa per classi di tipo Gino.Controller
     * @see Gino.Router::link
     */
    public function linkAdmin(array $params = array(), $query_string = '', array $kwargs = array()) {

        $method = $this->_instance ? 'manageDoc' : 'manage' . ucfirst($this->_instance_name);

        return $this->_registry->router->link($this->_instance_name, $method, $params, $query_string, $kwargs);
    }

    /**
     * @brief Opzioni di classe
     *
     * @param string $option nome del campo dell'opzione di classe
     * @param mixed $options default FALSE.
     *   - (array): chiavi value (valore di default), translation (traduzione)
     *   - (boolean): indica se è prevista la traduzione (compatibilità con precedenti versioni di gino)
     * @return mixed
     */
	protected function setOption($option, $options = FALSE) {
		
		if(is_null($this->_tbl_name)) {
			$tbl_name = $this->_db->getFieldFromId(TBL_MODULE_APP, 'tbl_name', 'name', $this->_class_name);
			$this->_tbl_name = $tbl_name."_opt";
		}
		
		$records = $this->_db->select("id, $option", $this->_tbl_name, "instance='".$this->_instance."'");
		if($records and count($records))
		{
			foreach($records AS $r)
			{
				if(is_bool($options)) {
					$trsl = $options; // for compatibility with old versions
				}
				elseif(is_array($options) AND array_key_exists('translation', $options)) {
					$trsl = $options['translation'];
				}
				else {
					$trsl = FALSE;
				}
		
				if($trsl && $this->_registry->sysconf->multi_language) {
					$value = $this->_trd->selectTXT($this->_tbl_name, $option, $r['id']);
				}
				else {
					$value = $r[$option];
				}
			}
		}
		else
		{
			if(is_array($options) AND array_key_exists('value', $options)) {
				$value = $options['value'];
			}
			else {
				$value = null;
			}
		}
		
		return $value;
    }

    /**
     * @brief Interfaccia per la gestione delle opzioni dei moduli
     *
     * @see Gino.Options::manageDoc()
     * @return string, interfaccia di amministrazione opzioni
     */
    public function manageOptions() {
        $options = new \Gino\Options($this);
        return $options->manageDoc();
    }

    /**
     * @brief Interfaccia per la gestione delle viste e css (frontend)
     * @see Frontend::manageFrontend()
     * @return string, interfaccia di amministrazione frontend (viste, css...)
     */
    public function manageFrontend() {
        $frontend = Loader::load('Frontend', array($this));
        return $frontend->manageFrontend();
    }
    
    /**
     * @brief Interfaccia per la gestione dei file delle traduzioni
     * @see Locale::manageLocale()
     * @return string, interfaccia di amministrazione traduzioni
     */
    public function manageLocale() {
    	$locale = locale::instance_to_class($this->_class_name);
    	return $locale->manageLocale($this);
    }

    /**
     * @brief Eliminazione istanza del modulo
     * @description Questo metodo deve essere sovrascritto dalle classi istanziabili per permettere l'eliminazione delle istanze.
     *              Se non sovrascritto viene chiamato e lancia una Exception
     */
    public function deleteInstance() {
        throw new \Exception(sprintf(_('La classe %s non implementa il metodo deleteInstance'), get_class($this)));
    }

    /**
     * @brief Imposta i parametri per SEO per una specifica pagina
     * 
     * @param array $options array associativo di opzioni
     *   - @b title (string): titolo
     *   - @b description (string): descrizione
     *   - @b keywords (string): elenco delle parole chiave 
     *   - @b url (string): indirizzo completo della pagina (@example $this->link($this->_instance_name, 'detail', ['id' => $item->slug], '', ['abs' => true]))
     *   - @b image (string): url dell'immagine
     *   - @b type (string): tipologia di pagina (es. @a article)
     *   - @b open_graph (boolean): visualizzazione dei meta tag semantici Open Graph dedicati alle pagine Facebook (default true)
     *   - @b twitter (boolean): visualizzazione dei meta tag semantici dedicati ai profili Twitter (default true)
     *   - @b customs (array): meta tag aggiuntivi nel formato [property => content]
     * @return void
     */
    public function setSEOSettings($options=[]) {
        
        $title = gOpt('title', $options, null);
        $description = gOpt('description', $options, null);
        $keywords = gOpt('keywords', $options, null);
        $url = gOpt('url', $options, null);
        $image = gOpt('image', $options, null);
        $type = gOpt('type', $options, null);
        $open_graph = gOpt('open_graph', $options, true);
        $twitter = gOpt('twitter', $options, true);
        $customs = gOpt('customs', $options, []);
        
        // Def strings
        if($title) {
            $title = \strip_tags($title);
            $title = \htmlspecialchars($title, ENT_COMPAT);
        }
        if($description) {
            $description = \strip_tags($description);
            $description = \htmlspecialchars($description, ENT_COMPAT);
        }
        
        if($title) {
            $this->_registry->title = $this->_registry->sysconf->head_title . ' | '.$title;
        }
        if($description) {
            $this->_registry->description = \Gino\cutHtmlText($description, 150, '...', true, false, true, '');
        }
        if($keywords) {
            $this->_registry->keywords = $keywords;
        }
        
        if($open_graph) {
            if($url) {
                $this->_registry->addMeta(array(
                    'property' => 'og:url',
                    'content' => $url
                ));
            }
            if($title) {
                $this->_registry->addMeta(array(
                    'property' => 'og:title',
                    'content' => $title
                ));
            }
            if($description) {
                $this->_registry->addMeta(array(
                    'property' => 'og:description',
                    'content' => $this->_registry->description
                ));
            }
            if($image) {
                $this->_registry->addMeta(array(
                    'property' => 'og:image',
                    'content' => $image
                ));
            }
            if($type) {
                $this->_registry->addMeta(array(
                    'property' => 'og:type',
                    'content' => $type
                ));
            }
        }
        
        if($twitter) {
            if($url) {
                $this->_registry->addMeta(array(
                    'property' => 'twitter:url',
                    'content' => $url
                ));
            }
            if($title) {
                $this->_registry->addMeta(array(
                    'property' => 'twitter:title',
                    'content' => $title
                ));
            }
            if($description) {
                $this->_registry->addMeta(array(
                    'property' => 'twitter:description',
                    'content' => $this->_registry->description
                ));
            }
            if($image) {
                $this->_registry->addMeta(array(
                    'property' => 'twitter:image',
                    'content' => $image
                ));
            }
        }
        
        if(is_array($customs) and count($customs)) {
            foreach ($customs as $key => $value) {
                $this->_registry->addMeta(array(
                    'property' => $key,
                    'content' => $value
                ));
            }
        }
    }
    
    /**
     * @brief Web Service Restful
     * 
     * @see \Gino\Http\ResponseJson
     * @param string|array $data contenuto della risposta (se diverso da stringa viene codificato in json)
     * @param array $options opzioni del costruttore di Gino.Http.ResponseJson
     * @return \Gino\Http\ResponseJson
     */
    public function REST($data, $options=[]) {
        
        if(!is_array($data) and !is_string($data)) {
            $data = [];
        }
        
        \Gino\Loader::import('class/http', '\Gino\Http\ResponseJson');
        return new \Gino\Http\ResponseJson($data);
    }
    
    /**
     * @brief Impostazione delle opzioni del controller
     * @description I valori delle opzioni vengono caricate nel registro
     *
     * @return mixed[]|NULL[] nel formato: (string) fieldname => (mixed) value
     */
    protected function appOptions() {
        
        $app_dir = get_app_dir($this->_class_name);
        $options_file = $app_dir.OS.'options.php';
        
        if(file_exists($options_file)) {
            include $options_file;
        }
        else {
            $options = [];
        }
        
        \Gino\Loader::load('Options', array($this));
        $this->_optionsLabels = [];
        
        $array = [];
        $fields = [];
        
        if(count($options)) {
            
            foreach ($options as $field => $data) {
                
                if(!$this->_registry->apps->instanceExists($this->_instance_name)) {
                    
                    $value = $this->setOption($field, ['value' => $data['default']]);
                    $array[$field] = $value;
                }
                else {
                    $value = $this->_registry->apps->{$this->_instance_name}[$field];
                }
                
                $field_input = ['label' => $data['label'], 'value' => $value];
                
                if(array_key_exists('required', $data) && is_bool($data['required'])) {
                    $field_input['required'] = $data['required'];
                }
                if(array_key_exists('trnsl', $data) && is_bool($data['trnsl'])) {
                    $field_input['trnsl'] = $data['trnsl'];
                }
                if(array_key_exists('editor', $data) && is_bool($data['editor'])) {
                    $field_input['editor'] = $data['editor'];
                }
                if(array_key_exists('footnote', $data) && $data['footnote']) {
                    $field_input['footnote'] = $data['footnote'];
                }
                
                if(array_key_exists('section', $data) && is_bool($data['section']) && $data['section']) {
                    $field_input['section'] = $data['section'];
                }
                if(array_key_exists('section_title', $data) && $data['section_title']) {
                    $field_input['section_title'] = $data['section_title'];
                }
                if(array_key_exists('section_description', $data) && $data['section_description']) {
                    $field_input['section_description'] = $data['section_description'];
                }
                
                $this->_optionsLabels[$field] = $field_input;
                $fields[$field] = $value;
            }
            
            if(count($array)) {
                $this->_registry->apps->{$this->_instance_name} = $array;
            }
        }
        return $fields;
    }
    
    /**
     * @brief Imposta i parametri della ricerca in una vista
     * @description Impostare i campi ri ricerca nella variabile @a $search_fields del file @a utils.php
     * 
     * @see Gino.SearchInterface
     * @param array $query_params parametri in arrivo da una request
     * @return \Gino\SearchInterface|NULL
     */
    protected function setSearchParams($query_params=[]) {
        
        $app_dir = get_app_dir($this->_class_name);
        $utils_file = $app_dir.OS.'utils.php';
        
        if(file_exists($utils_file)) {
            include $utils_file;
        }
        else {
            $search_fields = [];
        }
        
        if(count($search_fields)) {
            
            Loader::import('class', array('\Gino\SearchInterface'));
            $obj = new \Gino\SearchInterface($search_fields, array(
                'identifier' => $this->_class_name.'Search'.$this->_instance,
                'param_values' => $query_params
            ));
            
            $obj->sessionSearch();
            
            return $obj;
        }
        else {
            return null;
        }
    }
}
