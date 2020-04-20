<?php
/**
 * @file class.Document.php
 * @brief Contiene la definizione ed implementazione della class Gino.Document
 * 
 * @copyright 2005-2020 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
namespace Gino;

use \Gino\Registry;
use \Gino\Loader;
use \Gino\OutputCache;
use \Gino\App\SysClass\ModuleApp;
use \Gino\App\Module\ModuleInstance;
use \Gino\App\Page\page;

/**
 * @brief Crea il documento html da inviare come corpo della risposta HTTP
 * 
 * @copyright 2005-2020 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class Document {

    private $_registry,
            $_request,
            $_url_content;

	/**
	 * @brief Elenco delle istanze dei contenuti
	 * @var array
	 *   - array(string [classname _ moduleid] => object [new classname(moduleid)])
	 *   - array(page => new page)
	 */
	private $_instances;
	
	/**
	 * @brief Elenco dei contenuti
	 * @var array
	 *   - array(string [moduletype - moduleid - methodname - methodparam] => string [output])
	 *   - array(string [page - moduleid] => object [page->box(moduleid)])
	 */
	private $_outputs;
	
    /**
     * @brief Costruttore
     * @param string $url_content contenuto fornito dal metodo chiamato via url
     * @return void, istanza di Gino.Document
     */
    function __construct($url_content) {
    	
        $this->_registry = Registry::instance();
        $this->_request = $this->_registry->request;
        $this->_url_content = $url_content;
        
        $this->_outputs = array();
        $this->_instances = array();

        Loader::import('sysClass', 'ModuleApp');
        Loader::import('module', 'ModuleInstance');
    }

    /**
     * @brief Risposta HTTP con il documento
     * @return Gino.Http.Response il cui corpo è il documento
     */
    public function __invoke() {
        return new \Gino\Http\Response($this->render());
    }

    /**
     * @brief Crea il corpo della risposta HTTP
     * @return string, documento html
     */
    public function render() {

        Loader::import('class', 
            array(
                '\Gino\Skin',
                '\Gino\Template',
                '\Gino\Css',
                '\Gino\Javascript',
                '\Gino\Cache'
            )
        );

        $skin = $this->getSkin();

        // set dei meta, css, scripts
        $this->setHeadVariables($skin);

        $buffer = '';
        $cache = new OutputCache($buffer, $skin->cache ? true : false);
        if($cache->start('skin', $this->_request->path.$this->_request->session->lng.$skin->id, $skin->cache)) {

            $tpl = Loader::load('Template', array($skin->template));
            $template = TPL_DIR.OS.$tpl->filename;

            if($tpl->free) {
                /*
                 * Il template viene parserizzato 2 volte. La prima volta vengono eseguiti i metodi definiti nei tag {module} e 
                 * vengono letti i file definiti attraverso i tag {%block%}. 
                 * In questo modo vengono salvate eventuali modifiche al registry che viene utilizzato per includere js, css e meta 
                 * nell'head del documento. L'output viene quindi tenuto in memoria, mentre il template non viene toccato.
                 * La seconda volta viene parserizzato per sostituire effettivamente i segnaposto dei moduli con l'output precedentemente 
                 * salvato nella prima parserizzazione.
                 * Non si possono sostituire gli output già alla prima parserizzazione, e poi fare un eval del template 
                 * perché altrimenti eventuali contenuti degli output potrebbero causare errori di interpretazione dell'eval: 
                 * è sufficiente una stringa '<?' a far fallire l'eval.
                 * parse modules first time to update registry
                 */
            	$tpl_content = file_get_contents($template);
            	$regexp = array("#{% block '(.*?)' %}#", "#{% block (.*?) %}#", "#{module(.*?)}#");
            	
            	preg_replace_callback($regexp, array($this, 'parseTpl'), $tpl_content);
            	
            	ob_start();
            	include($template);
            	$tpl_content = ob_get_contents();
            	ob_clean();
            	// parse second time to replace codes
            	$cache->stop(preg_replace_callback($regexp, array($this, 'parseTpl'), $tpl_content));
            }
        }

        return $buffer;
    }

    /**
     * @brief Parserizza un placeholder del template ritornando le corrispondenti porzioni di template oppure l'output di pagine o applicazioni.
     * 
     * @param array $m placeholder
     * @return string, contenuto corrispondente
     * 
     * La variabile $m assume valori simili ai seguenti:
     * @code
     * array (size=2)
	 * 0 => string '{% block 'footer.php' %}' (length=24)
	 * 1 => string 'footer.php' (length=10)
	 * 
	 * array (size=2)
	 * 0 => string '{module classid=4 func=render}' (length=30)
	 * 1 => string ' classid=4 func=render' (length=22)
	 * @endcode
     */
    private function parseTpl($m) {
    	
    	$regex_marker = $m[0];
    	$regex_result = $m[1];
    	
    	if(preg_match("#{% block '(.*?)' %}#", $regex_marker)) {
    		
    	    // case: appname/filename
    	    if(preg_match("#/#", $regex_result)) {
    	        $a = explode('/', $regex_result);
    	        $filename = APP_DIR.OS.$a[0].OS.'templates'.OS.$a[1];
    	    }
    	    else {
    	        $filename = TPL_DIR.OS.$regex_result;
    	    }
    	    
    		if(file_exists($filename) && is_file($filename)) {
    			
    			$content = file_get_contents($filename);
    			
    			ob_start();
    			require $filename;
    			return ob_get_clean();
    		}
    		else {
    			return null;
    		}
    	}
    	elseif(preg_match("#{% block (.*?) %}#", $regex_marker)) {
    	    
    	    preg_match("#(\w+)\.(\w+)(\s*param=([0-9]+))?#", $regex_result, $matches);
    	    
    	    $instance = (!empty($matches[1])) ? $matches[1] : null;
    	    // method / slug
    	    $reference = (!empty($matches[2])) ? $matches[2] : null;
    	    $param = (isset($matches[4]) && !empty($matches[4])) ? $matches[4] : null;
    	    
    	    if($instance == 'page') {
    	        
    	        if(!is_int($reference)) {
    	            
    	            $db = Db::instance();
    	            $rows = $db->select('id', 'page_entry', "slug='$reference' AND published='1'");
    	            if($rows and count($rows)) {
    	                $reference = $rows[0]['id'];
    	            }
    	            else {
    	                $reference = null;
    	            }
    	        }
    	        
    	        $mdlContent = $this->modPage($reference);
    	    }
    	    elseif($instance and $reference) {
    	        
    	        try {
    	            $mdlContent = $this->modClass($instance, $reference, $param);
    	        }
    	        catch(\Exception $e) {
    	            Logger::manageException($e);
    	        }
    	    }
    	    elseif($instance == null && $reference == null) {
    	        $mdlContent = $this->_url_content;
    	    }
    	    else {
    	        return $matches[0];
    	    }
    	    
    	    return $mdlContent;
    	}
    	elseif(preg_match("#{module(.*?)}#", $regex_marker)) {
    		
    		preg_match("#\s(\w+)id=([0-9]+)\s*(\w+=(\w+))(\s*param=([0-9]+))?#", $regex_result, $matches);
    		
    		$mdlType = (!empty($matches[1])) ? $matches[1]:null;
    		$mdlId = (!empty($matches[2])) ? $matches[2]:null;
    		$mdlParam = (isset($matches[6]) && !empty($matches[6])) ? $matches[6] : null;
    		
    		if($mdlType=='page') {
    			$mdlContent = $this->modPage($mdlId);
    		}
    		elseif(($mdlType=='class' or $mdlType=='sysclass') and isset($matches[4])) {
    			$mdlFunc = $matches[4];
    			
    			try {
    			    $mdlContent = $this->modClassCompatibility($mdlId, $mdlFunc, $mdlType, $mdlParam);
    			}
    			catch(\Exception $e) {
    				Logger::manageException($e);
    			}
    		}
    		elseif($mdlType==null && $mdlId==null) {
    			$mdlContent = $this->_url_content;
    		}
    		else {
    			return $matches[0];
    		}
    		
    		return $mdlContent;
    	}
    }
    
    /**
     * @brief Recupera la skin da utilizzare per generare il documento
     * @description La scelta della skin dipende dall'url
     * 
     * @return Gino.Skin oppure una Exception se la skin non viene trovata
     */
    private function getSkin() {

        Loader::import('class', '\Gino\Skin');
        $skin = Skin::getSkin($this->_request);

        if($skin === FALSE or !$skin->id) {
            throw new \Exception(_('Skin inesistente'));
        }

        return $skin;
    }

    /**
     * @brief Imposta le variabili del Gino.Registry utilizzate all'interno del template
     *
     * @description Il registro contiene informazioni per i meta tag, css e javascript
     * @param \Gino\Skin $skin
     * @return void
     */
    private function setHeadVariables($skin) {

        // Meta
        $this->_registry->title = $this->_registry->title ? $this->_registry->title : $this->_registry->sysconf->head_title;
        $this->_registry->description = $this->_registry->description ? $this->_registry->description : $this->_registry->sysconf->head_description;
        $this->_registry->keywords = $this->_registry->keywords ? $this->_registry->keywords : $this->_registry->sysconf->head_keywords;
        $this->_registry->favicon = $this->_registry->favicon ? $this->_registry->favicon : SITE_WWW."/favicon.ico";
        
        require CONFIG_DIR.OS.'common.inc';
        
        $common_css = $PIPELINE['stylesheets'];
        $common_js = $PIPELINE['javascripts'];
        
        $opt_css = ['css', 'raw_css'];
        $opt_js = ['js', 'core_js', 'custom_js'];
        
        $stylesheets = ['css' => [], 'raw_css' => []];
        $javascripts = ['js' => [], 'core_js' => [], 'custom_js' => []];
        
        if(count($common_css)) {
            
            foreach ($common_css as $key => $value) {
                
                $loading = $value['loading'];
                
                if(count($value['source_filenames'])) {
                    foreach ($value['source_filenames'] as $css_file) {
                        $stylesheets[$loading][] = $css_file;
                    }
                }
            }
        }
        
        if($skin->css) {
            $css = Loader::load('Css', array('layout', array('id'=>$skin->css)));
            $stylesheets['css'][] = CSS_WWW."/".$css->filename;
        }
        
        foreach ($opt_css as $opt) {
            if(is_array($this->_registry->$opt)) {
                $this->_registry->$opt = array_merge($stylesheets[$opt], $this->_registry->$opt);
            }
            else {
                $this->_registry->$opt = $stylesheets[$opt];
            }
        }
        
        // Javascripts
        if(count($common_js)) {
            
            foreach ($common_js as $key => $value) {
                
                $loading = $value['loading'];
                
                if(count($value['source_filenames'])) {
                    foreach ($value['source_filenames'] as $js_file) {
                        $javascripts[$loading][] = $js_file;
                    }
                }
            }
        }
        
        if($skin->administrative_area) {
            
        }
        
        $browser = get_browser_info();
        if($browser['name'] == 'MSIE' and $browser['version'] < 9) {
            $javascripts['core_js'] = SITE_JS."/respond.js";
        }
        
        if($this->_registry->sysconf->captcha_public and $this->_registry->sysconf->captcha_private) {
            $javascripts['core_js'] = "https://www.google.com/recaptcha/api.js";
        }
        
        foreach ($opt_js as $opt) {
            if(count($javascripts[$opt])) {
                if(is_array($this->_registry->$opt)) {
                    $this->_registry->$opt = array_merge($javascripts[$opt], $this->_registry->$opt);
                }
                else {
                    $this->_registry->$opt = $javascripts[$opt];
                }
            }
        }
        
        return null;
    }

    /**
     * @brief Codice javascript che mostra, se presente, l'errore in sessione
     * @return string, codice javascript
     */
    private function errorMessages() {

        $buffer = '';
        $errorMsg = Error::getErrorMessage();
        if(!empty($errorMsg)) {
            $buffer .= "<script>window.addEvent('load', function() { new gino.layerWindow({title:'".jsVar(_('Errore!'))."', html: '".jsVar($errorMsg)."', 'width': 600}).display();});</script>";
        }
        return $buffer;
    }

    /**
     * @brief Contenuto dei moduli di tipo pagina
     *
     * @see Gino.App.Page.page::box()
     * @param int $mdlId valore ID della pagina
     * @return string, contenuto pagina
     */
    private function modPage($mdlId) {

        if(isset($this->_outputs['page-'.$mdlId])) {
            return $this->_outputs['page-'.$mdlId];
        }

        if(!isset($this->_instances['page']) or !is_object($this->_instances['page'])) {
            $this->_instances['page'] = new page();
        }

        $page = $this->_instances['page'];
        $this->_outputs['page-'.$mdlId] = $page->box($mdlId);

        return $this->_outputs['page-'.$mdlId];
    }

    /**
     * @brief Contenuto dei moduli di tipo classe (X COMPATIBILITÀ)
     * 
     * @param int $mdlId id istanza/classe
     * @param string $mdlFunc metodo
     * @param string $mdlType tipo modulo (sysclass|class)
     * @param string|int $mdlParam valore del parametro da passare al metodo da richiamare
     * @return string
     */
    private function modClassCompatibility($mdlId, $mdlFunc, $mdlType, $mdlParam=null){

        $db = Db::instance();

        if($mdlParam) {
        	$paramKey = '-'.$mdlParam;
        }
        else {
        	$paramKey = '';
        }
        
        if(isset($this->_outputs[$mdlType.'-'.$mdlId.'-'.$mdlFunc.$paramKey])) {
            return $this->_outputs[$mdlType.'-'.$mdlId.'-'.$mdlFunc.$paramKey];
        }

        $obj = $mdlType=='sysclass' ? new ModuleApp($mdlId) : new ModuleInstance($mdlId);

        $class = $obj->classNameNs();
        $class_name = $obj->className();

        if(!isset($this->_instances[$class_name."_".$mdlId]) || !is_object($this->_instances[$class_name."_".$mdlId])) {
            $this->_instances[$class_name."_".$mdlId] = new $class($mdlId);
        }

        $classObj = $this->_instances[$class_name."_".$mdlId];
        
        // Permessi
        $ofs = call_user_func(array($classObj, 'outputFunctions'));
        $ofp = isset($ofs[$mdlFunc]['permissions']) ? $ofs[$mdlFunc]['permissions'] : array();

        if($mdlType=='sysclass') {

            $module_app = new ModuleApp($mdlId);
            if(!$this->checkOutputFunctionPermissions($ofp, $module_app->name, 0)) {
                return '';
            }
        	
        	if($mdlParam) {
        		$buffer = $classObj->$mdlFunc($mdlParam);
        	}
        	else {
        		$buffer = $classObj->$mdlFunc();
        	}
        }
        elseif($mdlType=='class') {

            $module = new ModuleInstance($mdlId);
            $class_name = $module->className();
            if(!$this->checkOutputFunctionPermissions($ofp, $class_name, $mdlId)) {
                return '';
            }

            if($mdlParam) {
            	$buffer = $classObj->$mdlFunc($mdlParam);
            }
            else {
            	$buffer = $classObj->$mdlFunc();
            }
        }

        $this->_outputs[$mdlType.'-'.$mdlId.'-'.$mdlFunc.$paramKey] = $buffer;

        return $buffer;
    }
    
    /**
     * @brief Contenuto dei moduli di tipo classe
     *
     * @param string $instance nome dell'istanza/classe
     * @param string $mdlFunc metodo
     * @param string|int $mdlParam valore del parametro da passare al metodo da richiamare
     * @return string
     */
    private function modClass($instance, $mdlFunc, $mdlParam=null){
        
        $db = Db::instance();
        
        if($mdlParam) {
            $paramKey = '-'.$mdlParam;
        }
        else {
            $paramKey = '';
        }
        
        $instanceId = null;
        
        $db = Db::instance();
        $rows = $db->select('id', 'sys_module_app', "name='$instance' AND active='1' AND instantiable='0'");
        if($rows and count($rows)) {
            $instanceId = $rows[0]['id'];
            $mdlType = 'sysclass';
        }
        if($instanceId == null) {
            $rows = $db->select('id', 'sys_module', "name='$instance' AND active='1'");
            if($rows and count($rows)) {
                $instanceId = $rows[0]['id'];
                $mdlType = 'class';
            }
        }
        
        if(!$instanceId) {
            return null;
        }
        
        if(isset($this->_outputs[$mdlType.'-'.$instanceId.'-'.$mdlFunc.$paramKey])) {
            return $this->_outputs[$mdlType.'-'.$instanceId.'-'.$mdlFunc.$paramKey];
        }
        
        $obj = $mdlType=='sysclass' ? new ModuleApp($instanceId) : new ModuleInstance($instanceId);
        
        $class = $obj->classNameNs();
        $class_name = $obj->className();
        
        if(!isset($this->_instances[$class_name."_".$instanceId]) || !is_object($this->_instances[$class_name."_".$instanceId])) {
            $this->_instances[$class_name."_".$instanceId] = new $class($instanceId);
        }
        
        $classObj = $this->_instances[$class_name."_".$instanceId];
        
        // Permessi
        $ofs = call_user_func(array($classObj, 'outputFunctions'));
        $ofp = isset($ofs[$mdlFunc]['permissions']) ? $ofs[$mdlFunc]['permissions'] : array();
        
        if($mdlType=='sysclass') {
            
            $module_app = new ModuleApp($instanceId);
            if(!$this->checkOutputFunctionPermissions($ofp, $module_app->name, 0)) {
                return '';
            }
            
            if($mdlParam) {
                $buffer = $classObj->$mdlFunc($mdlParam);
            }
            else {
                $buffer = $classObj->$mdlFunc();
            }
        }
        elseif($mdlType=='class') {
            
            $module = new ModuleInstance($instanceId);
            $class_name = $module->className();
            if(!$this->checkOutputFunctionPermissions($ofp, $class_name, $instanceId)) {
                return '';
            }
            
            if($mdlParam) {
                $buffer = $classObj->$mdlFunc($mdlParam);
            }
            else {
                $buffer = $classObj->$mdlFunc();
            }
        }
        
        $this->_outputs[$mdlType.'-'.$instanceId.'-'.$mdlFunc.$paramKey] = $buffer;
        
        return $buffer;
    }

    /**
     * @brief Check dei permessi di accesso ad un metodo di tipo output
     * 
     * @see Gino.App.Auth.User::hasPerm()
     * @param array $perms elenco dei codici dei permessi da verificare, nei formati:
     *   @a sysclass_name.perm_name => cerca il permesso perm_name della classe sysclass_name con istanza 0 (compresa la sysclass fittizia 'core')
     *   @a perm_name => cerca il permesso perm_name della classe che definisce outputFunctions con istanza corrente (0 se si tratta di una sysclass)
     * @param string $class_name nome della classe
     * @param integer $instance istanza della classe (0 per classi non istanziabili)
     * @return boolean
     */
    private function checkOutputFunctionPermissions($perms, $class_name, $instance) {

        if(!count($perms)) {
            return TRUE;
        }
        
        $request = \Gino\Http\Request::instance();

        foreach($perms as $perm) {
            if(strpos($perm, '.') !== FALSE) {
                list($class_name_perm, $perm_name) = explode('.', $perm);
                if($request->user->hasPerm($class_name_perm, $perm_name, 0)) {
                    return TRUE;
                }
            }
            else {
                if($request->user->hasPerm($class_name, $perm, $instance)) {
                    return TRUE;
                }
            }
        }

        return FALSE;
    }

    /**
     * @brief Contenuto modulo/pagina richiamato da url
     * @return string
     */
    private function modUrl() {
        return $this->_url_content;
    }

    /**
     * @brief Codice google analytics
     * @return string, codice javascript
     */
    private function google_analytics(){

        $code = $this->_registry->sysconf->google_analytics;
        $buffer = "<script type=\"text/javascript\">";
        
        $buffer .= "(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
			(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
			m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
		})(window,document,'script','//www.google-analytics.com/analytics.js','ga');
        
		ga('create', '".$code."', 'auto');
		ga('set', 'anonymizeIp', true);
		ga('send', 'pageview');";
        
        $buffer .= "</script>";
        
        return $buffer;
    }
}
