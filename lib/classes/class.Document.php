<?php
/**
 * @file class.Document.php
 * @brief Contiene la definizione ed implementazione della class Gino.Document
 * 
 * @copyright 2005-2018 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
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
 * @copyright 2005-2018 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
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
            	$regexp = array("#{% block '(.*?)' %}#", "#{module(.*?)}#");
            	preg_replace_callback($regexp, array($this, 'parseTpl'), $tpl_content);
            	
            	// for compatibility; instantiate the registry variable directly in the template file
            	$registry = $this->_registry;
            	
            	ob_start();
            	include($template);
            	$tpl_content = ob_get_contents();
            	ob_clean();
            	// parse second time to replace codes
            	$cache->stop(preg_replace_callback($regexp, array($this, 'parseTpl'), $tpl_content));
            }
            else {
                $tpl_content = file_get_contents($template);
                $regexp = "/(<div(?:.*?)(id=\"(nav_.*?)\")(?:.*?)>)\n?([^<>]*?)\n?(<\/div>)/";
                $content = preg_replace_callback($regexp, array($this, 'renderNave'), $tpl_content);

                $headline = $this->headLine($skin);
                $footline = $this->footLine();

                $cache->stop($headline.$content.$footline);
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
    				$mdlContent = $this->modClass($mdlId, $mdlFunc, $mdlType, $mdlParam);
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

        // meta
        $this->_registry->title = $this->_registry->title ? $this->_registry->title : $this->_registry->sysconf->head_title;
        $this->_registry->description = $this->_registry->description ? $this->_registry->description : $this->_registry->sysconf->head_description;
        $this->_registry->keywords = $this->_registry->keywords ? $this->_registry->keywords : $this->_registry->sysconf->head_keywords;
        $this->_registry->favicon = $this->_registry->favicon ? $this->_registry->favicon : SITE_WWW."/favicon.ico";

        // Css
        $stylesheets = array();
        
        // Bootstrap
        $stylesheets[] = SITE_JS."/bootstrap/css/bootstrap.min.css";
        
        // Custom styles
        $stylesheets[] = CSS_WWW."/styles.css";
        $stylesheets[] = CSS_WWW."/jquery-ui.min-1.12.1.css";
        $stylesheets[] = CSS_WWW."/jquery-ui.min-1.12.1-update.css";
        
        if($skin->css) {
            $css = Loader::load('Css', array('layout', array('id'=>$skin->css)));
            $stylesheets[] = CSS_WWW."/".$css->filename;
        }
        
        $this->_registry->css = array_merge($stylesheets, $this->_registry->css);

        // javascript core
        $scripts = array(
        	SITE_JS."/MooTools-More-1.6.0-compressed.js",
        	SITE_JS."/modernizr.js",
        	SITE_JS."/gino-min.js",
        	SITE_JS."/Modal.js",
        );
        $browser = get_browser_info();
        if($browser['name'] == 'MSIE' and $browser['version'] < 9) {
            $scripts[] = SITE_JS."/respond.js";
        }
        
        $this->_registry->core_js = $scripts;
        
        if($this->_registry->sysconf->captcha_public and $this->_registry->sysconf->captcha_private) {
        	$this->_registry->addCoreJs("https://www.google.com/recaptcha/api.js");
        }
        
        // jQuery and Bootstrap
        $this->_registry->addCoreJs(SITE_JS."/jquery/jquery-2.2.4.min.js");
        $this->_registry->addCoreJs(SITE_JS."/jquery/jquery-ui-1.12.1.js");
        $this->_registry->addCoreJs(SITE_JS."/jquery/jquery-noconflicts.js");
        $this->_registry->addCoreJs(SITE_JS."/jquery/core.js");
        $this->_registry->addCoreJs(SITE_JS."/bootstrap/js/bootstrap.min.js");
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
     * @brief Headline per template non free
     * @return string, headline
     */
    private function headLine($skin) {

        Loader::import('class', '\Gino\Javascript');

        $headline = "<!DOCTYPE html>\n";
        $headline .= "<html lang=\"".LANG."\">\n";
        $headline .= "<head>\n";
        $headline .= "<meta charset=\"utf-8\" />\n";
        $headline .= "<base href=\"".$this->_registry->request->root_absolute_url."\" />\n";

        $headline .= $this->_registry->variables('meta');

        if(!empty($this->_registry->description)) {
            $headline .= "<meta name=\"description\" content=\"".$this->_registry->description."\" />\n";
        }
        if(!empty($this->_registry->keywords)) {
            $headline .= "<meta name=\"keywords\" content=\"".$this->_registry->keywords."\" />\n";
        }
        if($this->_registry->sysconf->mobile && isset($this->_request->session->L_mobile)) {
            $headline .= "<meta name=\"viewport\" content=\"width=device-width; user-scalable=0; initial-scale=1.0; maximum-scale=1.0;\" />\n"; // iphone,android 
        }
        $headline .= $this->_registry->variables('head_links');
        $headline .= "<title>".$this->_registry->title."</title>\n";

        $headline .= $this->_registry->variables('css');
        $headline .= $this->_registry->variables('js');
        $headline .= javascript::vendor();
        $headline .= javascript::onLoadFunction($skin);

        $headline .= "<link rel=\"shortcut icon\" href=\"".$this->_registry->favicon."\" />";
        $headline .= "<link href='https://fonts.googleapis.com/css?family=Roboto:300,900,700,300italic' rel='stylesheet' type='text/css' />";

        if($this->_registry->sysconf->google_analytics) {
            $headline .= $this->google_analytics();
        }
        $headline .= "</head>\n";
        $headline .= "<body>\n";

        return $headline;
    }

    /**
     * @brief Footline per template non free
     * @return string, footline
     */
    private function footLine() {

        $footline = $this->errorMessages();
        $footline .= "</body>";
        $footline .= "</html>";

        return $footline;
    }

    /**
     * @brief Gestisce gli elementi del layout ricavati dal file di template non free
     * 
     * @see renderModule()
     * @param array $matches
     *     - @b $matches[0] complete matching 
     *     - @b $matches[1] match open tag, es. <div id="nav_1_1" style="float:left;width:200px">
     *     - @b $matches[3] match div id, es. nav_1_1
     *     - @b $matches[4] match div content, es. {module classid=20 func=blockList}
     *     - @b $matches[5] match close tag, es. </div>
     * @return string
     */
    private function renderNave($matches) {

        $navContent = $matches[1];

        if(preg_match("#module#", $matches[4])) {
            $mdlMarkers = explode("\n", $matches[4]);
            foreach($mdlMarkers as $mdlMarker) if(preg_match("#module#", $mdlMarker)) $navContent .= $this->renderModule($mdlMarker);
        }
        else $navContent .= "&#160;";

        $navContent .= $matches[5];

        return $navContent;
    }

    /**
     * @brief Gestisce il tipo di elemento da richiamare
     *
     * @see modPage()
     * @see modClass()
     * @see modUrl()
     * @param string $mdlMarker placeholder
     * @return string or Exception se il modulo non viene riconosciuto
     */
    private function renderModule($mdlMarker) {

        preg_match("#\s(\w+)id=([0-9]+)\s*(\w+=(\w+))?#", $mdlMarker, $matches);
        
        $mdlType = (!empty($matches[1]))? $matches[1]:null;
        $mdlId = (!empty($matches[2]))? $matches[2]:null;

        if($mdlType=='page') {
            $mdlContent = $this->modPage($mdlId);
        }
        elseif($mdlType=='class' || $mdlType=='sysclass') {
            $mdlFunc = $matches[4];
            try {
                $mdlContent = $this->modClass($mdlId, $mdlFunc, $mdlType);
            }
            catch(\Exception $e) {
                Logger::manageException($e);
            }
        }
        elseif($mdlType==null && $mdlId==null) $mdlContent = $this->_url_content;
        else {
            throw new \Exception('Tipo di modulo sconosciuto');
        }

        return $mdlContent;
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
     * @brief Contenuto dei moduli di tipo classe
     * 
     * @param int $mdlId id istanza/classe
     * @param string $mdlFunc metodo
     * @param string $mdlType tipo modulo (sysclass|class)
     * @param string|int $mdlParam valore del parametro da passare al metodo da richiamare
     * @return string
     */
    private function modClass($mdlId, $mdlFunc, $mdlType, $mdlParam=null){

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
