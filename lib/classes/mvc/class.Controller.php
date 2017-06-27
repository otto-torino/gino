<?php
/**
 * @file class.Controller.php
 * @brief Contiene la definizione ed implementazione della classe Gino.Controller
 * 
 * @copyright 2013-2017 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
namespace Gino;

/**
 * @brief Classe astratta primitiva di tipo Controller (MVC), dalla quale tutti i controller delle singole app discendono
 *
 * @copyright 2013-2017 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
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
     * @brief Inizializza il controller
     * @param int $instance_id id modulo, se diverso da zero il modulo è un'istanza di una classe, altrimenti è la classe di sistema
     * @return istanza di Gino.Controller
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
      }

    /**
     * @brief Funzione chiamata quando si cerca di chiamare un metodo inaccessibile
     * @param string $name nome metodo
     * @param array $arguments argomenti
     * @return Exception
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
        $this->_class_www = SITE_APP.'/'.$this->_class_name;
        $this->_class_img = $this->_class_www.'/img';
        $this->_home = HOME_FILE;
        $this->_data_dir = CONTENT_DIR.OS.$this->_class_name . ($this->_instance ? OS . $this->_instance_name : '');
        $this->_data_www = CONTENT_WWW.'/'.$this->_class_name . ($this->_instance ? '/' . $this->_instance_name : '');
        $this->_view_dir = APP_DIR.OS.$this->_class_name.OS.'views';
    }

    /**
     * @brief Restituisce alcune proprietà della classe
     *
     * @description Le informazioni vengono utilizzate per creare o eliminare istanze. Questo metodo dev'essere sovrascritto da tutte le classi figlie.
     * @return lista delle proprietà utilizzate per la creazione di istanze di tipo pagina
     */
    public static function getClassElements() {
        return array();
    }

    /**
     * @brief Espone l'id valore dell'istanza
     * @return id
     */
    public function getInstance() {
        return $this->_instance;
    }

    /**
     * @brief Espone il nome dell'istanza
     * @return nome istanza
     */
    public function getInstanceName() {
        return $this->_instance_name;
    }

    /**
     * @brief Espone il nome della classe
     * @return nome classe
     */
    public function getClassName() {
        return $this->_class_name;
    }

    /**
     * @brief Percorso assoluto alla cartella dei contenuti
     * @return percorso assoluto
     */
    public function getBaseAbsPath() {
        return $this->_data_dir;
    }

    /**
     * @brief Percorso relativo alla cartella dei contenuti
     * @return percorso relativo
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
     * @return interfaccia di amministrazione opzioni
     */
    public function manageOptions() {
        $options = new \Gino\Options($this);
        return $options->manageDoc();
    }

    /**
     * @brief Interfaccia per la gestione delle viste e css (frontend)
     * @see Frontend::manageFrontend()
     * @return interfaccia di amministrazione frontend (viste, css...)
     */
    public function manageFrontend() {
        $frontend = Loader::load('Frontend', array($this));
        return $frontend->manageFrontend();
    }
    
    /**
     * @brief Interfaccia per la gestione dei file delle traduzioni
     * @see Locale::manageLocale()
     * @return interfaccia di amministrazione traduzioni
     */
    public function manageLocale() {
    	$locale = locale::instance_to_class($this->_class_name);
    	return $locale->manageLocale($this);
    }

    /**
     * @brief Eliminazione istanza del modulo
     * @description Questo metodo deve essere sovrascritto dalle classi istanziabili per permettere l'eliminazione delle istanze.
     *              Se non sovrascritto viene chiamato e getta una Exception
     */
    public function deleteInstance() {
        throw new \Exception(sprintf(_('La classe %s non implementa il metodo deleteInstance'), get_class($this)));
    }

}
