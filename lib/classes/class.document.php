<?php

class Document {

	private $_db, $_plink;
	private $_tbl_module_app, $_tbl_module;
	private $_instances;

	private $_precharge_mdl_url, $_mdl_url_content;

	private $_auth, $_session_role;

	function __construct() {
	
		$this->_db = new db();
		$this->_plink = new link();
		$this->_tbl_module_app = 'sys_module_app';
		$this->_tbl_module = 'sys_module';

		$this->_precharge_mdl_url = htmlChars(pub::variable('precharge_mdl_url'));

		$access = new access();
		$this->_session_role = $access->userRole();
		$this->_auth = (isset($_SESSION['userId']))? true:false;
	}

	/*
	 * Esempi:
	 * ["QUERY_STRING"]=> string(18) "articoli/viewList/"
	 * ["REQUEST_URI"]=> string(41) "/gino/articoli/viewList/?b3JkZXI9dGl0bGU="
	 * ["SCRIPT_NAME"]=> string(15) "/gino/index.php"
	 */
	public function render() {

		if(pub::variable('permalinks') == 'yes')
		{
			$query_string = $this->_plink->convertLink($_SERVER['REQUEST_URI'], array('setServerVar'=>true, 'pToLink'=>true, 'vserver'=>'REQUEST_URI'));	// index.php?evt[index-admin_page]
			
			$script = substr(preg_replace("#^".SITE_WWW."#", '', $_SERVER['SCRIPT_NAME']), 1);	// index.php
			$query_string = preg_replace("#^".preg_quote($script)."\??#", "", $query_string);	// evt[index-admin_page]
		}
		else
		{
			$query_string = $_SERVER['QUERY_STRING'];
		}
		$relativeUrl = preg_replace("#".SITE_WWW.OS."#", "", $_SERVER['SCRIPT_NAME']).((!empty($query_string))?"?$query_string":"");	//index.php?evt[index-admin_page]
		
		$this->_mdl_url_content = $this->_precharge_mdl_url!='no'? $this->modUrl():null;

		$skinObj = skin::getSkin(urldecode($relativeUrl));
		if($skinObj===false) exit(error::syserrorMessage("document", "render", _("skin inesistente"), __LINE__));

		$buffer = '';

		$cache = new outputCache($buffer, $skinObj->cache ? true : false);
		if($cache->start('skin', $query_string.$_SESSION['lng'].$skinObj->id, $skinObj->cache)) {
			$content = $this->headLine($skinObj);

			$tplObj = new template($skinObj->template);
			$template = TPL_DIR.OS.$tplObj->filename;

			$tplContent = file_get_contents($template);
			$regexp = "/(<div(?:.*?)(id=\"(nav_.*?)\")(?:.*?)>)\n?([^<>]*?)\n?(<\/div>)/";
			$content .= preg_replace_callback($regexp, array($this, 'renderNave'), $tplContent);

			$content .= $this->footLine();

			$cache->stop($content);
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

	private function headLine($skinObj) {
		
		$evt = $this->getEvent();
		$instance = is_null($evt) ? null : $evt[1];

		if(!is_null($instance) && method_exists($instance, 'getHeadlines')) {
			$params = $instance->getHeadlines($evt[2]);
			$title = isset($params['title']) ? $params['title'] : null;
			$description = isset($params['description']) ? $params['description'] : null;
			$meta_title = isset($params['meta_title']) ? $params['meta_title'] : null;
			$image_src = isset($params['image_src']) ? $params['image_src'] : null;
		}

		$description = (isset($description) && $description) ? $description : htmlChars(pub::variable('head_description'));
		$keywords = htmlChars(pub::variable('head_keywords'));
		$title = (isset($title) && $title) ? $title : htmlChars(pub::variable('head_title'));
		$image_src = (isset($image_src) && $image_src) ? $image_src : null;
	
		$copyright = "<!--
================================================================================
    Gino - a generic CMS framework
    Copyright (C) 2005  Otto Srl - written by Marco Guidotti and abidibo

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

   For additional information: <opensource@otto.to.it>
================================================================================
-->\n";

		$headline = "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Strict//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd\">\n";
		$headline .= $copyright;
		$headline .= "<html xmlns=\"http://www.w3.org/1999/xhtml\" lang=\"".LANG."\" xml:lang=\"".LANG."\">\n";
		$headline .= "<head>\n";
		$headline .= "<meta http-equiv=\"content-type\" content=\"text/html; charset=utf-8\" />\n";
		$pub = new pub();
		$headline .= "<base href=\"".$pub->getUrl('root').SITE_WWW."/\" />\n";
		
		if(isset($meta_title)) $headline .= "<meta name=\"title\" content=\"".$meta_title."\" />\n";
		if(!empty($description)) $headline .= "<meta name=\"description\" content=\"".$description."\" />\n";
		if(!empty($keywords)) $headline .= "<meta name=\"keywords\" content=\"".$keywords."\" />\n";
		if(pub::variable('mobile')=='yes' && isset($_SESSION['mobile'])) 
			$headline .= "<meta name=\"viewport\" content=\"width=504\" />\n";
		if($image_src) $headline .= "<link rel=\"image_src\" href=\"$image_src\" />\n";

		$headline .= "<title>".$title."</title>\n";

		$headline .= $this->css($skinObj);
		
		$headline .= "<link rel=\"shortcut icon\" href=\"".SITE_WWW."/favicon.ico\" />";
		
		$headline .= $this->javascript($skinObj);
		
		if(pub::variable('google_analytics')) $headline .= $this->google_analytics();
		$headline .= "</head>\n";
		$headline .= "<body>\n";
		
		return $headline;

	}

	private function css($skinObj) {
	
		// a seconda dell'autenticazione etc... 
		$css = css::mainCss();
		$css .= css::datePickerCss();
		$css .= css::slimboxCss();
		if($skinObj->css) {
			$cssObj = new css('layout', $skinObj->css);
			$css .= css::customCss($cssObj->filename);
		}

		return $css;
	}

	private function javascript($skinObj) {

		$javascript = javascript::mootoolsLib();
		$javascript .= javascript::fullGinoMinLib();
		if(pub::variable("captcha_public") && pub::variable("captcha_private"))
			$javascript .= javascript::captchaLib();
		$javascript .= javascript::onLoadFunction($skinObj);

		return $javascript;
	}

	private function footLine() {

		$footline = $this->errorMessages();
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
		elseif($mdlType=='' && $mdlId==0) $mdlContent = ($this->_precharge_mdl_url!='no')? $this->_mdl_url_content:$this->modUrl();
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
	
		if($_SESSION['userId']==1) {
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
