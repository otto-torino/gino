<?php
/**
 * @file class.Router.php
 * @brief Contiene la definizione ed implementazione della class Gino.Router
 *
 * @copyright 2014-2017 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */

namespace Gino;

use \Gino\Loader;
use \Gino\Singleton;
use \Gino\Registry;
use \Gino\Exception\Exception404;
use \Gino\Http\Response;
use \Gino\App\SysClass\ModuleApp;
use \Gino\App\Module\ModuleInstance;

/**
 * @brief Gestisce il routing di una request HTTP, chiamando la classe e metodo che devono fornire risposta
 * 
 * @copyright 2014-2017 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 * 
 * ##Definizione di alias route
 * Nel file lib/route.php è possibile definire degli alias agli indirizzi delle risorse. 
 * L'unica accortezza da adottare è quella di non utilizzare il carattere della costante URL_SEPARATOR nei nomi degli alias.
 * 
 * @code
 * $route->setInstanceAlias('instancename', 'instancealias');
 * $route->instancename = array(
 *   'methodname' => 'methodalias'
 * );
 * @endcode
 */
class Router extends Singleton {

	const EVT_NAME = 'evt';

	/**
	 * @brief Oggetto Gino.Registry
	 * @var object
	 */
	private $_registry;
	
	/**
	 * @brief Oggetto Gino.Request
	 * @var object
	 */
	private $_request;

	private $_url_class,
            $_url_instance,
            $_url_method,
            $_controller_view; // callable
    
	/**
	 * @brief Elenco degli output di una istanza
	 * @var array nel formato array([instance_name] => array([method_name] => [method_alias]))
	 */
	private $_methods_alias;
	
	/**
	 * @brief Elenco degli alias delle istanze
	 * @var array nel formato array([instance_name] => [instance_alias])
	 */
	private $_instances_alias;

    /**
     * @brief Costruttore
     * @description Esegue l'url rewriting quando si utilizzano pretty urls e setta le variabili che 
     *              contengono le informazioni della classe e metodo chiamati da url
     */
    protected function __construct() {

        $this->_registry = Registry::instance();
        $this->_request = $this->_registry->request;
        $this->urlRewrite();
        
        $this->_methods_alias = array();
        $this->_instances_alias = array();
    }
    
    /**
     * @brief Imposta gli alias degli output di una istanza
     * @description Non si può utilizzare il carattere della costante URL_SEPARATOR.
     *
     * Esempio
     * @code
     * $instance->foo = 'bar';
     * @endcode
     *
     * @param string $name nome dell'istanza
     * @param array $value nomi degli output nel formato array([true_name] => [alias_name])
     * @return void
     */
    public function __set($name , $value) {
    
    	$this->_methods_alias[$name] = $value;
    }
    
    /**
     * @brief Ritorna gli alias degli output di una istanza
     *
     * Esempio
     * @code
     * echo $instance->foo;
     * @endcode
     *
     * @param string $name nome dell'istanza
     * @return array o null
     */
    public function __get($name) {
    
    	return isset($this->_methods_alias[$name]) ? $this->_methods_alias[$name] : null;
    }
    
    /**
     * @brief Imposta l'alias di una istanza
     * @description Non si può utilizzare il carattere -
     * 
     * @param string $name nome dell'istanza
     * @param string $value nome dell'alias
     */
    public function setInstanceAlias($name, $value) {
    	
    	$this->_instances_alias[$name] = $value;
    }

    /**
     * @brief Url rewriting
     * @description Se l'url non è nella forma pretty riscrive le proprietà GET e REQUEST dell'oggetto
     *              @ref Gino.Http.Request parserizzando l'url. Chiama Gino.Http.Request per fare un update
     *              della proprietà url.
     * @return void
     */
    private function urlRewrite() {

        // pretty url
        if(!preg_match("#^/(index.php\??.*)?$#is", $this->_registry->request->path)) {
            // ripuliamo da schifezze
            $this->_request->GET = array();
            $query_string = '';
            $path_info = preg_replace_callback("#\?(.*)$#", function($matches) use(&$query_string) { $query_string = $matches[1]; return ''; }, $this->_request->path);

            if($path_info !== '/') {
                $this->rewritePathInfo(array_values(array_filter(explode('/', $path_info), function($v) { return $v !== ''; })));
            }

            if($query_string !== '') {
                $this->rewriteQueryString(explode('&', $query_string));
            }

            $this->_request->REQUEST = array_merge($this->_request->POST, $this->_request->GET);
        }

        $this->_request->updateUrl();
    }

    /**
     * @brief Riscrittura URL path info
     * @param array $paths parti del path info
     * @return TRUE
     */
    private function rewritePathInfo(array $paths) {

        $tot = count($paths);

        /**
         * http://example.com/admin
         */
        if($tot === 1) {
            // admin porta alla home di amministrazione
            if($paths[0] === 'admin') {
                $this->_request->GET[self::EVT_NAME] = array(sprintf('index%sadmin_page', URL_SEPARATOR) => '');
            }
            // il path viene interpretato come <nome-istanza>/index
            elseif($paths[0] !== 'home') {
                $this->_request->GET[self::EVT_NAME] = array(sprintf('%s%sindex', $paths[0], URL_SEPARATOR) => '');
            }
            return TRUE;
        }

        // esistono due o più path, i primi due sono nome istanza e metodo
        $this->_request->GET[self::EVT_NAME] = array(sprintf('%s%s%s', $paths[0], URL_SEPARATOR, $paths[1]) => '');

        // ulteriori path sono normali coppie chiave/valore da inserire nella proprietà GET
        if($tot > 2) {
            // numero dispari di path, il terzo è un id
            if($tot % 2 !== 0) {
                $this->_request->GET['id'] = urldecode($paths[2]);
                // e lo rimuovo
                unset($paths[2]);
                // e rimetto a posto le chiavi
                $paths = array_values($paths);
            }

            // devo ricontare i paths
            for($i = 2, $tot = count($paths); $i < $tot; $i += 2) {
                $this->_request->GET[$paths[$i]] = isset($paths[$i + 1]) ? urldecode($paths[$i + 1]) : '';
            }
        }

        return TRUE;
    }

    /**
     * @brief Riscrittura URL della query_string
     * @param array $pairs coppie chiave-valore
     * @return void
     */
    private function rewriteQueryString(array $pairs) {

        foreach($pairs as $pair) {
            $pair_parts = explode('=', $pair);
            $this->_request->GET[$pair_parts[0]] = isset($pair_parts[1]) ? $pair_parts[1] : '';
        }
    }

    /**
     * @brief Imposta le proprietà che contengono le informazioni della classe e metodo chiamati da url
     * @description Se i parametri ricavati dall'url tentano di chiamare una callable (classe + metodo) non chiamabile
     *              per qualunque motivo, viene generata una @ref Gino.Exception.Exception404
     * @return TRUE
     */
    private function setUrlParams() {

        $evt_key = (isset($this->_registry->request->GET[self::EVT_NAME]) and is_array($this->_registry->request->GET[self::EVT_NAME]))
            ? key($this->_registry->request->GET[self::EVT_NAME])
            : false;

        if($evt_key === FALSE or preg_match('#^[^a-zA-Z0-9_-]+?#', $evt_key)) {
            $this->_url_class = null;
            $this->_url_method = null;
            $this->_controller_view = null;
        }
        else {
            list($mdl, $method) = explode(URL_SEPARATOR, $evt_key);

            // Search alias route
            require_once LIB_DIR.OS.'route.php';
            
            $found_mdl = array_search($mdl, $this->_instances_alias);
            if($found_mdl) {
            	$mdl = $found_mdl;
            }
            
            $mdl_route = $this->$mdl;	// call self::__get()
            if($mdl_route) {
            	
            	$found_key = array_search($method, $mdl_route);
            	if($found_key) {
            		$method = $found_key;
            	}
            }
            // /Search
            
            Loader::import('module', 'ModuleInstance');
            Loader::import('sysClass', 'ModuleApp');
            $module_app = ModuleApp::getFromName($mdl);
            $module = ModuleInstance::getFromName($mdl);

            // se da url non viene chiamato un modulo né un'istanza restituiamo un 404
            if(is_null($module_app) and is_null($module)) {
                throw new Exception404();
            }

            if(is_dir(APP_DIR.OS.$mdl) and class_exists(get_app_name_class_ns($mdl)) and $module_app and !$module_app->instantiable) {
                $class = $module_app->classNameNs();
                $class_name = $module_app->className();
                $module_instance = new $class();
            }
            elseif(is_object($module) && class_exists($module->classNameNs())) {
                $mdl_id = $module->id;
                $class = $module->classNameNs();
                $class_name = $module->className();
                $module_instance = new $class($mdl_id);
            }
            else {
                throw new Exception404();
            }

            $method_check = parse_ini_file(APP_DIR.OS.$class_name.OS.$class_name.".ini", true);
            $public_method = @$method_check['PUBLIC_METHODS'][$method];

            if(isset($public_method)) {
                $this->_url_class = $class_name;
                $this->_url_instance = $mdl;
                $this->_url_method = $method;
                $this->_controller_view = array($module_instance, $this->_url_method);
            }
            else {
                throw new Exception404();
            }
        }
    }

    /**
     * @brief Controlla se la callable fornita è quella chiamata da url
     * @param callable $callable
     * @return bool
     */
    public function isRoute($callable) {
        return !!$this->_controller_view === $callable;
    }

    /**
     * @brief Esegue il route della request HTTP
     * @description Passa la @ref Gino.Http.Request alla callable che deve gestirla e ritornare una Gino.Http.Response.
     *              Se non è definita una callable, ritorna una Gino.Http.Response con contenuto vuoto
     * @return Gino.Http.Response
     */
    public function route() {
        $this->setUrlParams();
        if(!is_null($this->_controller_view)) {
            return call_user_func($this->_controller_view, $this->_request);
        }
        else {
            $document = new Document('');
            return $document();
        }
    }

    /**
     * @brief Url che linka un metodo di una istanza di controller con parametri dati
     * @param string|\Gino\Controller $instance_name nome istanza o istanza del @ref Gino.Controller
     * @param string $method nome metodo
     * @param array $params parametri da aggiungere come path info nel caso di pretty url
     * @param array|string $query_string parametri aggiuntivi da trattare come query_string in entrambi i casi (pretty, espanso)
     * @param array $kwargs array associativo
     *                      - pretty: bool, default TRUE. Creare un pretty url o un url espanso
     *                      - abs: bool, default FALSE. Se TRUE viene ritornato un url assoluto
     * @return string
     */
    public function link($instance_name, $method, array $params = array(), $query_string = '', array $kwargs = array()) {

        if(is_a($instance_name, '\Gino\Controller')) {
            $instance_name = get_name_class($instance_name);
        }

        $pretty = isset($kwargs['pretty']) ? $kwargs['pretty'] : TRUE;
        $abs = isset($kwargs['abs']) ? $kwargs['abs'] : FALSE;
        // query string puo' essere array o stringa, normalizzo a stringa
        $query_string = is_array($query_string)
            ? implode('&', array_map(function($k, $v) { return $v === '' ? $k :sprintf('%s=%s', $k, $v); }, array_keys($query_string), array_values($query_string)))
            : $query_string;

        $tot_params = count($params);

        // pretty url
        if($pretty) {
            $url = sprintf('%s/%s/', $instance_name, $method);
            // parametro id tattato diversamente, come 3 elemento
            if(isset($params['id'])) {
                $url .= sprintf('%s/', $params['id']);
            }

            foreach($params as $k => $v) {
                if($k !== 'id') $url .= sprintf('%s/%s/', $k, $v);
            }

            if($query_string) $url .= '?' . $query_string;

            return $abs ? $this->_request->root_absolute_url . $url : $url;
        }

        // url espansi
        $url = $this->_request->META['SCRIPT_NAME']."?".self::EVT_NAME."[".$instance_name.URL_SEPARATOR.$method."]";
        
        if($tot_params) $query_string = implode('&', array_map(function($k, $v) { return sprintf('%s=%s', $k, $v); }, array_keys($params), array_values($params))) . ($query_string ? '&' . $query_string : '');
        if($query_string) $url .= '?' . $query_string;

        return $abs ? $this->_request->root_absolute_url . $url : $url;
    }

    /**
     * @brief Trasformazione di un path con aggiunta o rimozione di parametri dalla query string
     * @param array $add parametri da aggiungere nella forma parametro => valore
     * @param array $remove parametri da rimuovere
     * @return string, path trasformato
     */
    public function transformPathQueryString(array $add = array(), array $remove = array()) {

        $url = $this->_request->path;

        if(count($remove)) {
            foreach($remove as $param) {
                $url = preg_replace("#(\?|&)".preg_quote($param)."(?:=[^&]*)#", '', $url);
            }
        }

        if(count($add)) {
            foreach($add as $param => $value) {
                // se presente va riscritto
                if(preg_match("#\b".preg_quote($param)."(=[^&]*)#", $url, $matches)) {
                    $url = preg_replace("#\b".preg_quote($param)."(?:=[^&]*)#", $param.'='.$value, $url);
                }
                else {
                    $url .= ( strpos($url, '?') ? '&' : '?' ) . sprintf('%s=%s', $param, $value);
                }
            }
        }

        return substr($url, 0, 1) === '/' ? substr($url, 1) : $url;
    }

    /**
     * @brief Visualizzazione di esempi di indirizzi url
     * 
     * @param string $view tipo di esempio da visualizzare; sono validi i seguenti valori:
     *   - @a url
     *   - @a regexp
     * @return string|NULL
     */
    public function exampleUrl($view='url') {
    	
    	$e_regexp = "#\?".self::EVT_NAME."\[news".URL_SEPARATOR."(.*)\]#<br />#^news/(.*)#";
    	$e_url = "index.php?".self::EVT_NAME."[news".URL_SEPARATOR."viewList]<br />news/viewList";
    	
    	if($view == 'url') {
    		return $e_url;
    	}
    	elseif($view == 'regexp') {
    		return $e_regexp;
    	}
    	else {
    		return null;
    	}
    }
}
