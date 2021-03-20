<?php
/**
 * @file class.Router.php
 * @brief Contiene la definizione ed implementazione della class Gino.Router
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
 * #DEFINIZIONE DI ALIAS AGLI INDIRIZZI
 * Nei file app/[app_name]/urls.php è possibile definire gli alias agli indirizzi delle risorse. 
 * Nei nomi degli alias non è possibile utilizzare il carattere della costante URL_SEPARATOR.
 * 
 * È poi necessario richiamare nel file settings/urls.php la variabile che comprende gli alias.
 * @see settings/urls.php
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
	 * @brief Elenco degli alias degli indirizzi di tipo istanza/metodo
	 * @var array nel formato array([instance/method] => [instance-alias/method-alias])
	 */
	private $_urls_instance;
	
	/**
	 * @brief Elenco degli alias di indirizzi di tipo istanza/metodo/(id|slug)
	 * @var array nel formato array([istance/method/id] => [url-alias])
	 */
	private $_urls_alias;
	
	/**
	 * @brief Elenco degli indirizzi interni da ridefinire
	 * @var array
	 */
	private $_urls_pattern;

    /**
     * @brief Costruttore
     * @description Esegue l'url rewriting quando si utilizzano permalink (pretty url) e imposta le variabili che 
     *              contengono le informazioni di classe e metodo chiamati da url
     */
    protected function __construct() {

        $this->_registry = Registry::instance();
        $this->_request = $this->_registry->request;
        
        require_once SETTINGS_DIR.OS.'urls.php';
        
        $this->setUrlsPattern($rewritten_urls);
        
        $this->urlRewrite();
    }
    
    // $urls è un array (applicazioni) di array (indirizzi dell'applicazione)
    private function setUrlsPattern($urls) {
        
        $urls_instance = $urls_pattern = $urls_alias = [];
        
        if(count($urls)) {
            foreach ($urls as $app) {
                if(is_array($app) and count($app)){
                    
                    foreach ($app as $url) {
                        
                        $array = $url;
                        if(count($array) == 3 and $array[0] == 'instance') {
                            $urls_instance[$array[1]] = $array[2];
                        }
                        elseif(count($array) == 3 and $array[0] == 'regexp') {
                            $urls_pattern[$array[1]] = $array[2];
                        }
                        elseif(count($array) == 3 and $array[0] == 'alias') {
                            $urls_alias[$array[1]] = $array[2];
                        }
                    }
                }
            }
        }
        
        $this->_urls_instance = $urls_instance;
        $this->_urls_pattern = $urls_pattern;
        $this->_urls_alias = $urls_alias;
    }
    
    /**
     * @brief Url rewriting
     * @description Se l'url non è nella forma pretty (permalink) riscrive le proprietà GET e REQUEST dell'oggetto
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
     * @brief Riscrittura URL PathInfo quando l'indirizzo è nel formato permalink
     * 
     * @see config/route.inc
     * @param array $paths parti del PathInfo (@see urlRewrite())
     * @return TRUE
     */
    private function rewritePathInfo(array $paths) {

        // 1. URL ALIAS
        // ex. maps/view/turismo-rifugi' => 'osm/view/rifugi
        if(is_array($this->_urls_alias) && count($this->_urls_alias)) {
            $check_value = implode('/', $paths);
            
            if (array_key_exists($check_value, $this->_urls_alias)) {
                $url_rewrite = $this->_urls_alias[$check_value];
            }
            elseif (array_key_exists($check_value.'/', $this->_urls_alias)) {
                $url_rewrite = $this->_urls_alias[$check_value.'/'];
            }
            else {
                $url_rewrite = null;
            }
            
            // Rewrite $paths
            if($url_rewrite) {
                $paths = explode('/', $url_rewrite);
            }
        }
        
        $tot = count($paths);
        
        /**
         * http://example.com/admin
         */
        if($tot === 1) {
            // admin porta alla home page amministrativa
            if($paths[0] === 'admin') {
                $this->_request->GET[self::EVT_NAME] = array(sprintf('index%sadmin_page', URL_SEPARATOR) => '');
            }
            // il path viene controllato con gli alias URL e, in caso di non corrispondenza, interpretato come <nome-istanza>/index
            elseif($paths[0] !== 'home') {
                
                $check_alias = false;
                if(is_array($this->_urls_alias) and count($this->_urls_alias)) {
                    $url_alias = $this->_urls_alias;
                    
                    $found_url = array_search($paths[0], $url_alias);
                    if($found_url) {
                        $u = explode("/", $found_url);
                        if(end($u) == '') {
                            array_pop($u);
                        }
                        $u_mdl = $u[0];
                        $u_method = $u[1];
                        
                        $this->_request->GET[self::EVT_NAME] = array(sprintf('%s%s%s', $u_mdl, URL_SEPARATOR, $u_method) => '');
                        if(count($u) == 3) {
                            $this->_request->GET['id'] = $u[2];
                        }
                        $check_alias = true;
                    }
                }
                
                if(!$check_alias) {
                    $this->_request->GET[self::EVT_NAME] = array(sprintf('%s%sindex', $paths[0], URL_SEPARATOR) => '');
                }
            }
        }
        elseif($tot === 2) {
            
            $check_alias = false;
            if(is_array($this->_urls_instance) and count($this->_urls_instance)) {
                $instances_alias = $this->_urls_instance;
                
                $found_url = array_search(implode('/', $paths), $instances_alias);
                if($found_url) {
                    $u = explode("/", $found_url);
                    $u_mdl = $u[0];
                    $u_method = $u[1];
                    
                    $this->_request->GET[self::EVT_NAME] = array(sprintf('%s%s%s', $u_mdl, URL_SEPARATOR, $u_method) => '');
                    $check_alias = true;
                }
            }
            
            if(!$check_alias) {
                $this->_request->GET[self::EVT_NAME] = array(sprintf('%s%s%s', $paths[0], URL_SEPARATOR, $paths[1]) => '');
            }
        }
        // istanza/metodo/[slug|id]
        elseif($tot === 3) {
            
            $this->originalUrlPattern($paths, $tot);
        }
        // I path oltre i primi due (nome istanza e metodo) sono normali coppie chiave/valore da inserire nella proprietà GET
        elseif($tot > 3) {
            
            $this->originalUrl();
        }
        
        return true;
    }
    
    private function originalUrl($paths, $number_items) {
        
        $this->_request->GET[self::EVT_NAME] = array(sprintf('%s%s%s', $paths[0], URL_SEPARATOR, $paths[1]) => '');
        
        // se il numero di elementi è dispari, il terzo elemento è un id
        if($number_items % 2 !== 0) {
            $this->_request->GET['id'] = urldecode($paths[2]);
            // quindi lo rimuovo
            unset($paths[2]);
            // e rimetto a posto le chiavi
            $paths = array_values($paths);
        }
        
        // devo ricontare i paths
        for($i = 2, $tot = count($paths); $i < $tot; $i += 2) {
            $this->_request->GET[$paths[$i]] = isset($paths[$i + 1]) ? urldecode($paths[$i + 1]) : '';
        }
        return null;
    }
    
    /**
     * @brief Returns the original url of an urls_pattern with slug
     * 
     * @param array $paths
     * @return NULL
     * 
     * @example article/detail/06-02-2020-news-2/ => articolo/dettaglio/06-02-2020-news-2/
     */
    private function originalUrlPattern($paths, $number_items) {
        
        $items = 3;
        $check_alias = false;
        
        if(is_array($this->_urls_pattern) and count($this->_urls_pattern)) {
            // Elenco degli urls pattern (@see settings/urls.py)
            $urls_pattern = $this->_urls_pattern;
            
            $address = $_SERVER['REQUEST_URI'];
            // /gino-test/articolo/dettaglio/06-02-2020-news-2/
            
            $aa = explode('/', $address);
            $bb = [];
            foreach ($aa as $b) {
                if($b) {
                    $bb[] = $b;
                }
            }
            // var_dump($bb) -> array (size=3)
            // 0 => string 'gino-test', 1 => string 'articolo', 2 => string 'dettaglio'
            $address_last = array_pop($bb);
            $count = count($bb);
            $address_method = $bb[$count-1];
            $address_instance = $bb[$count-2];
            
            $address_check = $address_instance.'/'.$address_method;
            
            // var_dump($paths) -> array (size=3)
            // 0 => string 'articolo', 1 => string 'dettaglio', 2 => string '06-02-2020-news-2'
            
            $urls_pattern_to_check = [];
            foreach ($urls_pattern as $original_url => $new_url) {
                // 'article/detail/<slug>/' => 'articolo/dettaglio/<slug>/'
                
                // Bisogna tradurre <slug> con il valore ricavato da REQUEST_URI
                
                // new
                $cc = explode('/', $new_url);
                $dd = [];
                foreach ($cc as $b) {
                    if($b) {
                        $dd[] = $b;
                    }
                }
                $new_url_last = $dd[2];
                $new_url_method = $dd[1];
                $new_url_instance = $dd[0];
                
                // original
                $cc = explode('/', $original_url);
                $dd = [];
                foreach ($cc as $b) {
                    if($b) {
                        $dd[] = $b;
                    }
                }
                $original_url_last = $dd[2];
                $original_url_method = $dd[1];
                $original_url_instance = $dd[0];
                
                $original_path = $original_url_instance.'/'.$original_url_method.'/'.$address_last;
                
                // ricostruisco l'url col terzo elemento preso da REQUEST_URI
                $urls_pattern_to_check[] = $new_url_instance.'/'.$new_url_method.'/'.$address_last;
                $urls_pattern_to_check[] = $original_path;
                // due volte perché devo validare sia il path originale che quello tradotto
                $urls_pattern_original[] = $original_path;
                $urls_pattern_original[] = $original_path;
            }
            // array_search — Searches the array for a given value and returns
            // the first corresponding key if successful (return int value)
            // search 'articolo/dettaglio/06-02-2020-news-2' into $urls_pattern_to_check
            $found_key_url = array_search(implode('/', $paths), $urls_pattern_to_check);    // returns 1 if found
            
            if(is_int($found_key_url)) {
                $found_url = $urls_pattern_original[$found_key_url];    // 'article/detail/06-02-2020-news-2'
            }
            else {
                $found_url = 0;
            }
            
            if($found_url) {
                $u = explode("/", $found_url);
                $u_mdl = $u[0];     // article
                $u_method = $u[1];  // detail
                $u_pattern = $u[2]; // 06-02-2020-news-2
                
                $this->_request->GET[self::EVT_NAME] = array(sprintf('%s%s%s', $u_mdl, URL_SEPARATOR, $u_method) => '');
                $check_alias = true;
                
                // se il numero di elementi è dispari, il terzo elemento è un id
                if($items % 2 !== 0) {
                    $this->_request->GET['id'] = urldecode($u_pattern);
                    // quindi lo rimuovo
                    unset($paths[2]);
                    // e rimetto a posto le chiavi
                    $paths = array_values($paths);
                }
                
                // devo ricontare i paths
                for($i = 2, $tot = count($paths); $i < $tot; $i += 2) {
                    $this->_request->GET[$paths[$i]] = isset($paths[$i + 1]) ? urldecode($paths[$i + 1]) : '';
                }
            }
        }
        
        if(!$check_alias) {
            //$this->_request->GET[self::EVT_NAME] = array(sprintf('%s%s%s', $paths[0], URL_SEPARATOR, $paths[1]) => '');
            
            $this->originalUrl($paths, $number_items);
        }
        return null;
    }
    
    /**
     * @brief Check if the instance and method of the request are included in the urls_pattern (@see settings/urls.py)
     * @param string $instance
     * @param string $method
     * @return mixed[]|string[]|NULL
     */
    private function findMatchesUrlPattern($instance, $method) {
        
        if(is_array($this->_urls_pattern) and count($this->_urls_pattern)) {
            
            $urls_pattern = $this->_urls_pattern;
            
            foreach ($urls_pattern as $original_url => $new_url) {
                // 'article/detail/<slug>/' => 'articolo/dettaglio/<slug>/'
                
                // new
                $cc = explode('/', $new_url);
                $dd = [];
                foreach ($cc as $b) {
                    if($b) {
                        $dd[] = $b;
                    }
                }
                $new_url_last = $dd[2];
                $new_url_method = $dd[1];
                $new_url_instance = $dd[0];
                
                // original
                $cc = explode('/', $original_url);
                $dd = [];
                foreach ($cc as $b) {
                    if($b) {
                        $dd[] = $b;
                    }
                }
                $original_url_last = $dd[2];
                $original_url_method = $dd[1];
                $original_url_instance = $dd[0];
                
                if($original_url_instance == $instance and $original_url_method == $method) {
                    
                    return ['instance' => $new_url_instance, 'method' => $new_url_method];
                }
            }
            
        }
        
        return null;
    }
    
    /**
     * @brief Check if the instance and method of the request are included in the urls_instance (@see settings/urls.py)
     * @param string $instance
     * @param string $method
     * @return mixed[]|string[]|NULL
     */
    private function findMatchesUrlInstance($instance, $method) {
        
        if(is_array($this->_urls_instance) and count($this->_urls_instance)) {
            
            $urls_instance = $this->_urls_instance;
            $url = $instance.'/'.$method;
            
            foreach ($urls_instance as $original_url => $new_url) {
                // 'article/archive' => 'articoli/elenco'
                
                $original_url = rtrim($original_url, '/');
                
                if($original_url == $url) {
                    $u = explode('/', $new_url);
                    return ['instance' => $u[0], 'method' => $u[1]];
                }
            }
        }
        
        return null;
    }

    /**
     * @brief Riscrittura URL della query_string quando l'indirizzo è nel formato permalink
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

            Loader::import('module', 'ModuleInstance');
            Loader::import('sysClass', 'ModuleApp');
            $module_app = ModuleApp::getFromName($mdl);
            $module = ModuleInstance::getFromName($mdl);

            // se da url non viene chiamato un modulo né un'istanza restituiamo un 404
            if(is_null($module_app) and is_null($module)) {
                throw new Exception404();
            }
            
            $mdl_dir = get_app_dir($mdl);
            
            if(is_dir($mdl_dir) and class_exists(get_app_name_class_ns($mdl)) and $module_app and !$module_app->instantiable) {
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
            
            $app_dir = get_app_dir($class_name);

            $method_check = parse_ini_file($app_dir.OS.$class_name.".ini", true);
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
     * @param array $params parametri da aggiungere come path info nel caso di pretty url (permalink)
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
            // Ricerca urls pattern
            
            $find_matches = $this->findMatchesUrlPattern($instance_name, $method);
            if(is_array($find_matches) and count($find_matches)) {
                $instance_name = $find_matches['instance'];
                $method = $find_matches['method'];
            }
            else {
                $find_matches = $this->findMatchesUrlInstance($instance_name, $method);
                if(is_array($find_matches) and count($find_matches)) {
                    $instance_name = $find_matches['instance'];
                    $method = $find_matches['method'];
                }
            }
            
            $url = sprintf('%s/%s/', $instance_name, $method);
            
            // parametro id trattato diversamente, come terzo elemento
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
     * 
     * @see self::changeUrlQueryString()
     * @param array $add parametri da aggiungere nel formato [param1 => value1, param2 => value2[,]]
     * @param array $remove parametri da rimuovere nel formato [param1, param2[,]]
     * @return string
     */
    public function transformPathQueryString(array $add = array(), array $remove = array()) {

        return $this->changeUrlQueryString($this->_request->path, ['add_params' => $add, 'remove_params' => $remove]);
    }
    
    /**
     * @brief Modifica un indirizzo con aggiunta o rimozione di parametri dalla query string
     * 
     * @param string $url indirizzo da modificare
     * @param array $options array associativo di opzioni
     *   - @b add_params (array): parametri da aggiungere nel formato [param1 => value1, param2 => value2[,]]
     *   - @b remove_params (array): parametri da rimuovere nel formato [param1, param2[,]]
     * @return string
     */
    public function changeUrlQueryString($url, array $options = []) {
        
        $add_params = gOpt('add_params', $options, []);
        $remove_params = gOpt('remove_params', $options, []);
        
        if(is_array($remove_params) && count($remove_params)) {
            foreach($remove_params as $param) {
                $url = preg_replace("#(\?|&)".preg_quote($param)."(?:=[^&]*)#", '', $url);
            }
        }
        
        if(is_array($add_params) && count($add_params)) {
            foreach($add_params as $param => $value) {
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
    	
    	$e_regexp = "#\?".self::EVT_NAME."\[article".URL_SEPARATOR."(.*)\]#<br />#^article/(.*)#";
    	$e_url = "index.php?".self::EVT_NAME."[article".URL_SEPARATOR."viewList]<br />article/viewList";
    	
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
