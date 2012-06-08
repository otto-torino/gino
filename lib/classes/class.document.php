<?php
/**
 * @file class.document.php
 * @brief Contiene la classe Document
 * 
 * @copyright 2005 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */

/**
 * @brief Libreria che si preoccupa di costruire la pagina richiesta e di stamparla utilizzando il metodo render()
 * 
 * Procedura eseguita dalla libreria:
 *   - se sono attivi i permalink, converte l'indirizzo dal permalink recuperando la REQUEST_URI (metodo convertLink)
 *   - verifica la corrispondenza dell'url con una skin (skin::getSkin()) e recupera i riferimenti della skin dal record della tabella sys_layout_skin
 *   - verifica se la pagina è in cache (outputCache->start)
 *   - carica la parte iniziale della pagina con i file css e javascript (header html, head e avvio body). I valori di default indicati nella sezione head sono definiti in Impostazioni. E' tuttavia possibile personalizzare questi valori sovrascrivendoli nel registro
 *   - istanzia il template associato alla skin e recupera il nome del file del template
 *   - effettua il parser del file di template e sostituisce ai marcatori dei moduli di classe, pagina e funzione il contenuto associato
 *   - chiude la connessione al database
 *   - carica i tag di chiusura del file html (body, html)
 *   - stampa la pagina
 * 
 * @copyright 2005 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class Document {

	private $_registry, $_db, $session, $_plink;
	private $_tbl_module_app, $_tbl_module;
	private $_instances;

	private $_precharge_mdl_url, $_mdl_url_content;

	private $_auth, $_session_role;

	function __construct() {
	
		$this->_registry = registry::instance();
		$this->_db = db::instance();
		$this->session = session::instance();
		
		$this->_plink = new link();
		$this->_tbl_module_app = 'sys_module_app';
		$this->_tbl_module = 'sys_module';

		$this->_precharge_mdl_url = htmlChars(pub::variable('precharge_mdl_url'));

		$access = new access();
		$this->_session_role = $access->userRole();
		$this->_auth = isset($this->session->userId) ? true : false;
	}

	/**
	 * Crea il documento
	 * 
	 * @return string
	 * 
	 * Esempi di contenuti delle variabili $_SERVER:
	 * @code
	 * $_SERVER["QUERY_STRING"]=> string(18) "articoli/viewList/"
	 * $_SERVER["REQUEST_URI"]=> string(41) "/gino/articoli/viewList/?b3JkZXI9dGl0bGU="
	 * $_SERVER["SCRIPT_NAME"]=> string(15) "/gino/index.php"
	 * @endcode
	 */
	public function render() {

		if(pub::variable('permalinks') == 'yes')
		{
			$query_string = $this->_plink->convertLink($_SERVER['REQUEST_URI'], array('setServerVar'=>true, 'setDataVar'=>true, 'pToLink'=>true, 'vserver'=>'REQUEST_URI'));		// index.php?evt[index-admin_page]
			
			$script = substr(preg_replace("#^".preg_quote(SITE_WWW)."#", '', $_SERVER['SCRIPT_NAME']), 1);	// index.php
			$query_string = preg_replace("#^(".preg_quote($script).")?\??#", "", $query_string);	// evt[index-admin_page]
		}
		else
		{
			$query_string = $_SERVER['QUERY_STRING'];
		}
		$relativeUrl = preg_replace("#".preg_quote(SITE_WWW.'/')."#", "", $_SERVER['SCRIPT_NAME']).((!empty($query_string))?"?$query_string":"");	//index.php?evt[index-admin_page]
		
		$this->_mdl_url_content = $this->_precharge_mdl_url!='no'? $this->modUrl():null;

		$skinObj = skin::getSkin(urldecode($relativeUrl));
		if($skinObj===false) exit(error::syserrorMessage("document", "render", _("skin inesistente"), __LINE__));

		$buffer = '';

		$cache = new outputCache($buffer, $skinObj->cache ? true : false);
		if($cache->start('skin', $query_string.$this->session->lng.$skinObj->id, $skinObj->cache)) {

			$this->initHeadVariables($skinObj);
			
			$tplObj = new template($skinObj->template);
			$template = TPL_DIR.OS.$tplObj->filename;

			$tplContent = file_get_contents($template);
			$regexp = "/(<div(?:.*?)(id=\"(nav_.*?)\")(?:.*?)>)\n?([^<>]*?)\n?(<\/div>)/";
			$content = preg_replace_callback($regexp, array($this, 'renderNave'), $tplContent);
			if($content === null) exit("PCRE Error! Subject too large or complex.");

			$headline = $this->headLine($skinObj);
			$footline = $this->footLine();

			$cache->stop($headline.$content.$footline);
		}

		echo $buffer;
	}

	private function errorMessages() {

		$buffer = '';
		$errorMsg = error::getErrorMessage();
		if(!empty($errorMsg)) {
			$buffer .= "<script>alert('".$errorMsg."');</script>";
		}
		return $buffer;
	}

	/**
	 * Caricamento nel registro dei parametri base
	 * 
	 * @param object $skinObj
	 */
	private function initHeadVariables($skinObj) {

		$this->_registry->title = htmlChars(pub::variable('head_title'));
		$this->_registry->description = htmlChars(pub::variable('head_description'));
		$this->_registry->keywords = htmlChars(pub::variable('head_keywords'));
		$this->_registry->favicon = SITE_WWW."/favicon.ico";
		
		$this->_registry->addCss(CSS_WWW."/main.css");
		$this->_registry->addCss(CSS_WWW."/datepicker_jqui.css");
		$this->_registry->addCss(CSS_WWW."/slimbox.css");
		
		if($skinObj->css) {
			$cssObj = new css('layout', $skinObj->css);
			$this->_registry->addCss(CSS_WWW."/".$cssObj->filename);
		}
		
		$this->_registry->addJs(SITE_JS."/mootools-1.4.0-yc.js");
		$this->_registry->addJs(SITE_JS."/gino-min.js");
		
		if(pub::variable("captcha_public") && pub::variable("captcha_private"))
			$this->_registry->addJs("http://api.recaptcha.net/js/recaptcha_ajax.js");
	}

	private function headLine($skinObj) {
		
		$evt = $this->getEvent();
		$instance = is_null($evt) ? null : $evt[1];

		if(pub::variable('mobile')=='yes' && isset($this->session->L_mobile)) { 
			$headline = "<!DOCTYPE html PUBLIC \"-//WAPFORUM//DTD XHTML Mobile 1.2//EN\" \"http://www.wapforum.org/DTD/xhtml-mobile12.dtd\">\n";
		}
		else {
			$headline = "<!DOCTYPE html>\n";
		}
		$headline .= "<html lang=\"".LANG."\">\n";
		$headline .= "<head>\n";
		$headline .= "<meta charset=\"utf-8\" />\n";
		$pub = new pub();
		$headline .= "<base href=\"".$pub->getUrl('root').SITE_WWW."/\" />\n";
		
		$headline .= $this->_registry->variables('meta');
		
		if(!empty($this->_registry->description)) $headline .= "<meta name=\"description\" content=\"".$this->_registry->description."\" />\n";
		if(!empty($this->_registry->keywords)) $headline .= "<meta name=\"keywords\" content=\"".$this->_registry->keywords."\" />\n";
		if(pub::variable('mobile')=='yes' && isset($this->session->L_mobile)) {
			$headline .= "<meta name=\"viewport\" content=\"width=device-width; user-scalable=0; initial-scale=1.0; maximum-scale=1.0;\" />\n"; // iphone,android 
		}
		$headline .= $this->_registry->variables('head_links');
		$headline .= "<title>".$this->_registry->title."</title>\n";
		
		$headline .= $this->_registry->variables('css');
		$headline .= $this->_registry->variables('js');
		$headline .= javascript::onLoadFunction($skinObj);
		
		$headline .= "<link rel=\"shortcut icon\" href=\"".$this->_registry->favicon."\" />";
		
		if(pub::variable('google_analytics')) $headline .= $this->google_analytics();
		$headline .= "</head>\n";
		$headline .= "<body>\n";
		
		return $headline;
	}

	private function footLine() {

		$footline = $this->errorMessages();
		$this->_db->closeConnection();
		$footline .= "</body>";
		$footline .= "</html>";

		return $footline;
	}

	/*
	 * method renderModule() 
	 *
	 * $matches[0] complete matching 
	 * $matches[1] match open tag, es. <div id="nav_1_1" style="float:left;width:200px">
	 * $matches[3] match div id, es. nav_1_1
	 * $matches[4] match div content, es. {mod id=20}
	 * $matches[5] match close tag, es. </div>
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

	private function renderModule($mdlMarker) {

		preg_match("#\s(\w+)id=([0-9]+)\s*(\w+=(\w+))?#", $mdlMarker, $matches);
		$mdlType = (!empty($matches[1]))? $matches[1]:null;
		$mdlId = (!empty($matches[2]))? $matches[2]:null;

		if($mdlType=='page') {
			$mdlFunc = $matches[4];
			$mdlContent = $this->modPage($mdlId, $mdlFunc);
		}
		elseif($mdlType=='class' || $mdlType=='sysclass') {
			$mdlFunc = $matches[4];
			$mdlContent = $this->modClass($mdlId, $mdlFunc, $mdlType);
		}
		elseif($mdlType=='func') $mdlContent = $this->modFunc($mdlId);
		elseif($mdlType==null && $mdlId==null) $mdlContent = ($this->_precharge_mdl_url!='no')? $this->_mdl_url_content:$this->modUrl();
		else exit(error::syserrorMessage("document", "renderModule", "Tipo di modulo sconosciuto", __LINE__));

		return $mdlContent;
	}
	
	private function modPage($mdlId, $mdlFunc){

		if(!isset($this->_instances['page']) || !is_object($this->_instances['page'])) 
			$this->_instances['page'] = new page();

		$page = $this->_instances['page'];

		return ($page->checkReadPermission($mdlId))
			? ($mdlFunc=='block'?$page->blockItem($mdlId):$page->displayItem($mdlId))
			:"";
	}

	private function modClass($mdlId, $mdlFunc, $mdlType){

		$class_name = $mdlType=='sysclass'
			? $this->_db->getFieldFromId(TBL_MODULE_APP, 'name', 'id', $mdlId)
			: $this->_db->getFieldFromId(TBL_MODULE, 'class', 'id', $mdlId);

		if(!isset($this->_instances[$class_name."_".$mdlId]) || !is_object($this->_instances[$class_name."_".$mdlId])) 
			$this->_instances[$class_name."_".$mdlId] = new $class_name($mdlId);

		$classObj = $this->_instances[$class_name."_".$mdlId];		
		$ofs = call_user_func(array($classObj, 'outputFunctions'));
		$ofr = isset($ofs[$mdlFunc]['role'])? $ofs[$mdlFunc]['role']:null;

		if(!$ofr) return '';
		else $field = "role".$ofr;

		if($mdlType=='sysclass') {

			$query = "SELECT name, $field FROM $this->_tbl_module_app WHERE id='$mdlId'";
			$a = $this->_db->selectquery($query);
			if(sizeof($a) > 0) {
				$name = htmlChars($a[0]['name']);
				$role = htmlChars($a[0][$field]);
				if(!($this->_session_role <= $role)) return '';
			}
			else return '';

			$buffer = $classObj->$mdlFunc();
		}
		elseif($mdlType=='class') {
			$query = "SELECT class, $field FROM $this->_tbl_module WHERE id='$mdlId'";
			$a = $this->_db->selectquery($query);
			if(sizeof($a) > 0) {
				$class = htmlChars($a[0]['class']);
				$role = htmlChars($a[0][$field]);
				if(!($this->_session_role <= $role)) return '';
			}
			else return '';

			$buffer = $classObj->$mdlFunc();

		}

		return $buffer;
	}
	
	private function modFunc($id){

		$GINO = '';
		
		$query = "SELECT name, role1 FROM ".$this->_tbl_module." WHERE id='$id'";
		$a = $this->_db->selectquery($query);
		if(sizeof($a) > 0)
		{
			foreach($a AS $b)
			{
				$name = htmlChars($b['name']);
				$role = htmlChars($b['role1']);
			}
		}
		
		if($this->_session_role <= $role)
		{
			$func = new sysfunc;
			
			if(method_exists($func, $name))
			{
				$GINO .= "<section id=\"section_$name\">\n";
				$GINO .= $func->$name();
				$GINO .= "</section>\n";
			}
		}

		return $GINO;
	}
	
	private function modUrl() {

		$evt = $this->getEvent();
		if(is_null($evt)) return null;
		list($class, $instance, $function) = $evt;

		if(is_null($instance)) exit(error::syserrorMessage("document", "modUrl", "Modulo sconosciuto", __LINE__));

		$methodCheck = parse_ini_file(APP_DIR.OS.$class.OS.$class.".ini", true);
		$publicMethod = @$methodCheck['PUBLIC_METHODS'][$function];

		if(isset($publicMethod)) return $instance->$function();
		else header("Location: ".HOME_FILE);
	}

	private function getEvent() {

		$evtKey = isset($_GET[EVT_NAME])? is_array($_GET[EVT_NAME])? key($_GET[EVT_NAME]):false:false;
		
		if(!$evtKey) return null;
		if(preg_match('#^[^a-zA-Z0-9_-]+?#', $evtKey)) return null;
		
		list($mdl, $function) = explode("-", $evtKey);
		if(is_dir(APP_DIR.OS.$mdl) && class_exists($mdl) && $this->_db->getFieldFromId($this->_tbl_module_app, 'instance', 'name', $mdl)!='yes') {
			
			$mdlId = $this->_db->getFieldFromId($this->_tbl_module_app, 'id','name',$mdl);
			$class = $mdl;

			if(!isset($this->_instances[$class."_".$mdlId]) || !is_object($this->_instances[$class."_".$mdlId])) 
				$this->_instances[$class."_".$mdlId] = new $class($mdlId);

			$instance = $this->_instances[$class."_".$mdlId];
		}
		elseif(class_exists($this->_db->getFieldFromId($this->_tbl_module, 'class', 'name', $mdl))) {
			$mdlId = $this->_db->getFieldFromId($this->_tbl_module, 'id','name',$mdl);
			$class = $this->_db->getFieldFromId($this->_tbl_module, 'class', 'name', $mdl);
			
			if(!isset($this->_instances[$class."_".$mdlId]) || !is_object($this->_instances[$class."_".$mdlId])) 
				$this->_instances[$class."_".$mdlId] = new $class($mdlId);

			$instance = $this->_instances[$class."_".$mdlId];
		}
		else { $class=null; $instance=null; }

		return array($class, $instance, $function);
	}

	private function proxy() {
	
		if($this->session->userId == 1) {
			$_SERVER['REQUEST_URI'] = '/index.php?evt[realtime-map]';
			$_SERVER['QUERY_STRING'] = 'evt[realtime-map]';
			$_SERVER['SCRIPT_FILENAME'] = dirname(__FILE__).'index.php';
			$_SERVER['SCRIPT_NAME'] = 'index.php';
			$_SERVER['PHP_SELF'] = '/index.php';
		}
	}

	private function google_analytics(){
		
		$code = pub::variable('google_analytics');
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
?>
