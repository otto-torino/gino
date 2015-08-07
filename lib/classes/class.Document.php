<?php
/**
 * @file class.Document.php
 * @brief Contiene la definizione ed implementazione della class Gino.Document
 * 
 * @copyright 2005-2014 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
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
 * @copyright 2005-2014 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class Document {

    private $_registry,
            $_request,
            $_url_content;

    /**
     * @brief Costruttore
     * @param string $url_content contenuto fornito dal metodo chiamato via url
     * @return istanza di Gino.Document
     */
    function __construct($url_content) {
        $this->_registry = Registry::instance();
        $this->_request = $this->_registry->request;
        $this->_url_content = $url_content;

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
     * @return documento html
     */
    public function render() {

        Loader::import('class', 
            array(
                '\Gino\Skin',
                '\Gino\Template',
                '\Gino\Css',
                '\Gino\Javascript',
                '\Gino\Cache'	// aggiunto 20150610
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
                // Il template viene parserizzato 2 volte. La prima volta vengono eseguiti i metodi (definiti nei tag {module...}), 
                // in questo modo vengono salvate eventuali modifiche al registry che viene utilizzato per includere js e css e meta nell'head del documento.
                // L'output viene quindi tenuto in memoria, mentre il template non viene toccato.
                // La seconda volta viene parserizzato per sostituire effettivamente i segnaposto dei moduli con l'output precedentemente salvato nella prima
                // parserizzazione.
                // Non si possono sostituire gli output già alla prima parserizzazione, e poi fare un eval del template perché altrimenti eventuali contenuti
                // degli output potrebbero causare errori di interpretazione dell'eval, è sufficiente una stringa '<?' a far fallire l'eval.
                // parse modules first time to update registry
                $tpl_content = file_get_contents($template);
                $regexp = "#{module(.*?)}#";
                preg_replace_callback($regexp, array($this, 'parseModules'), $tpl_content);
                $registry = $this->_registry;
                ob_start();
                include($template);
                $tpl_content = ob_get_contents();
                ob_clean();
                // parse second time to replace codes
                $cache->stop(preg_replace_callback($regexp, array($this, 'parseModules'), $tpl_content));
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
     * @brief Recupera la skin da utilizzare per generare il documento
     *
     * @description La scelta della skin dipende dall'url
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

        // css
        $stylesheets = array(
            CSS_WWW."/styles.css",
            CSS_WWW."/datepicker_jqui.css",
            CSS_WWW."/slimbox.css",
        );

        if($skin->css) {
            $css = Loader::load('Css', array('layout', array('id'=>$skin->css)));
            $stylesheets[] = CSS_WWW."/".$css->filename;
        }

        $this->_registry->css = array_merge($stylesheets, $this->_registry->css);

        // js
        $scripts = array(
            SITE_JS."/mootools-1.4.0-yc.js",
            SITE_JS."/modernizr.js",
            SITE_JS."/gino-min.js",
        );
        $browser = get_browser_info();
        if($browser['name'] == 'MSIE' and $browser['version'] < 9) {
            $scripts[] = SITE_JS."/respond.js";
        }
        if($this->_registry->sysconf->captcha_public and $this->_registry->sysconf->captcha_private) {
            $this->_registry->addCustomJs("http://www.google.com/recaptcha/api/js/recaptcha_ajax.js", array('compress'=>false, 'minify'=>false));
        }

        $this->_registry->js = array_merge($scripts, $this->_registry->js);
    }

    /**
     * @brief Parserizza un placeholder del template ritornando la pagina o istanza/metodo corrispondenti
     * @param string $m placeholder
     * @return contenuto corrispondente
     */
    private function parseModules($m) {
        $mdlMarker = $m[0];
        preg_match("#\s(\w+)id=([0-9]+)\s*(\w+=(\w+))?#", $mdlMarker, $matches);
        $mdlType = (!empty($matches[1]))? $matches[1]:null;
        $mdlId = (!empty($matches[2]))? $matches[2]:null;

        if($mdlType=='page') {
            $mdlContent = $this->modPage($mdlId);
        }
        elseif(($mdlType=='class' or $mdlType=='sysclass') and isset($matches[4])) {
            $mdlFunc = $matches[4];
            try {
                $mdlContent = $this->modClass($mdlId, $mdlFunc, $mdlType);
            }
            catch(Exception $e) {
                    Logger::manageException($e);
            }
        }
        elseif($mdlType==null && $mdlId==null) $mdlContent = $this->_url_content;
        else return $matches[0];

        return $mdlContent;
    }

    /**
     * @brief Codice javascript che mostra, se presente, l'errore in sessione
     * @return codice javascript
     */
    private function errorMessages() {

        $buffer = '';
        $errorMsg = error::getErrorMessage();
        if(!empty($errorMsg)) {
            $buffer .= "<script>window.addEvent('load', function() { new gino.layerWindow({title:'".jsVar(_('Errore!'))."', html: '".jsVar($errorMsg)."', 'width': 600}).display();});</script>";
        }
        return $buffer;
    }

    /**
     * @brief Headline per template non free
     * @return headline
     */
    private function headLine($skin) {

        Loader::import('class', '\Gino\Javascript');

        $headline = "<!DOCTYPE html>\n";
        $headline .= "<html lang=\"".LANG."\">\n";
        $headline .= "<head>\n";
        $headline .= "<meta charset=\"utf-8\" />\n";
        $headline .= "<base href=\"".$this->_registry->request->root_absolute_url."\" />\n";

        $headline .= $this->_registry->variables('meta');

        if(!empty($this->_registry->description)) $headline .= "<meta name=\"description\" content=\"".$this->_registry->description."\" />\n";
        if(!empty($this->_registry->keywords)) $headline .= "<meta name=\"keywords\" content=\"".$this->_registry->keywords."\" />\n";
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
        $headline .= "<link href='http://fonts.googleapis.com/css?family=Roboto:300,900,700,300italic' rel='stylesheet' type='text/css' />";

        if($this->_registry->sysconf->google_analytics) $headline .= $this->google_analytics();
        $headline .= "</head>\n";
        $headline .= "<body>\n";

        return $headline;
    }

    /**
     * @brief Footline per template non free
     * @return footline
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
     
     * @see modPage()
     * @see modClass()
     * @see modUrl()
     * @param string $mdlMarker placeholder
     * @return contenuto o Exception se il modulo non viene riconosciuto
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
            catch(Exception $e) {
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
     * @brief Contenuto modulo di tipo pagina
     *
     * @see Gino.App.Page.page::box()
     * @param int $mdlId valore ID della pagina
     * @return contenuto pagina
     */
    private function modPage($mdlId){

        if(isset($this->_outputs['page-'.$mdlId])) {
            return $this->_outputs['page-'.$mdlId];
        }

        if(!isset($this->_instances['page']) or !is_object($this->_instances['page'])) 
        {
            $this->_instances['page'] = new page();
        }

        $page = $this->_instances['page'];
        $this->_outputs['page-'.$mdlId] = $page->box($mdlId);

        return $this->_outputs['page-'.$mdlId];
    }

    /**
     * @brief Contenuto modulo di tipo classe
     * @param int $mdlId id istanza/classe
     * @param string $mdlFunc metodo
     * @param string $mdlType tipo modulo (sysclass|class)
     * @return contenuto
     */
    private function modClass($mdlId, $mdlFunc, $mdlType){

        $db = Db::instance();

        if(isset($this->_outputs[$mdlType.'-'.$mdlId.'-'.$mdlFunc])) {
            return $this->_outputs[$mdlType.'-'.$mdlId.'-'.$mdlFunc];
        }

        $obj = $mdlType=='sysclass' ? new ModuleApp($mdlId) : new ModuleInstance($mdlId);

        $class = $obj->classNameNs();
        $class_name = $obj->className();

        if(!isset($this->_instances[$class_name."_".$mdlId]) || !is_object($this->_instances[$class_name."_".$mdlId])) {
            $this->_instances[$class_name."_".$mdlId] = new $class($mdlId);
        }

        $classObj = $this->_instances[$class_name."_".$mdlId];
        $ofs = call_user_func(array($classObj, 'outputFunctions'));
        $ofp = isset($ofs[$mdlFunc]['permissions'])? $ofs[$mdlFunc]['permissions']:array();

        if($mdlType=='sysclass') {

            $module_app = new ModuleApp($mdlId);
            if(!$this->checkOutputFunctionPermissions($ofp, $module_app->name, 0)) {
                return '';
            }
            $buffer = $classObj->$mdlFunc();
        }
        elseif($mdlType=='class') {

            $module = new ModuleInstance($mdlId);
            $class_name = $module->className();
            if(!$this->checkOutputFunctionPermissions($ofp, $class_name, $mdlId)) {
                return '';
            }

            $buffer = $classObj->$mdlFunc();
        }

        $this->_outputs[$mdlType.'-'.$mdlId.'-'.$mdlFunc] = $buffer;

        return $buffer;
    }

    /**
     * @brief Check dei permessi di accesso ad un metodo di tipo output
     *
     * formati:
     *     sysclass_name.perm_name => cerca il permesso perm_name della classe sysclass_name con istanza 0 (compresa la sysclass fittizia 'core')
     *     perm_name => cerca il permesso perm_name della classe che definisce outputFunctions con istanza corrente (0 se si tratta di una sysclass)
     * @return bool
     */
    private function checkOutputFunctionPermissions($perms, $class_name, $instance) {

        if(!count($perms)) {
            return TRUE;
        }

        foreach($perms as $perm) {
            if(strpos($perm, '.') !== FALSE) {
                list($class_name_perm, $perm_name) = explode('.', $perm);
                if($this->_registry->user->hasPerm($class_name_perm, $perm_name, 0)) {
                    return TRUE;
                }
            }
            else {
                if($this->_registry->user->hasPerm($class_name, $perm, $instance)) {
                    return TRUE;
                }
            }
        }

        return FALSE;

    }

    /**
     * @brief Contenuto modulo/pagina richiamato da url
     * @return contenuto
     */
    private function modUrl() {
        return $this->_url_content;
    }

    /**
     * @brief Codice google analytics
     * @return codice javascript
     */
    private function google_analytics(){

        $code = $this->_registry->sysconf->google_analytics;
        $buffer = "<script type=\"text/javascript\">";
            $buffer .= "var _gaq = _gaq || [];";
        $buffer .= "_gaq.push(['_setAccount', '".$code."']);";
            $buffer .= "_gaq.push(['_trackPageview']);";
        $buffer .= "(function() {
                        var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
                        ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
                        var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
                })();";
        $buffer .= "</script>";

        return $buffer;
    }

}
