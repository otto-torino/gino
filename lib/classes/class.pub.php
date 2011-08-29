<?php
class pub extends EvtHandler{
	
	public static $delimiter = ';;';
	protected $_className;
	protected $_access, $_db, $_trd, $_list, $_plink;
	protected $_session_user, $_session_user_name, $_session_role, $_access_admin, $_access_user;
	protected $_module_type, $_module_text;
	protected $_crypt;
	protected $_url_path, $_url_path_login, $_url_root;
	protected $_home;
	protected $_admin_page;
	protected $_multi_language, $_delimiter;
	
	protected $_os, $_site_www, $_app_www, $_img_www, $_extra_www, $_content_www, $_graphics_www;
	protected $_tmp_dir, $_app_dir, $_extra_dir, $_content_dir;
	protected $_class_www, $_class_img, $_data_www, $_data_dir;
	protected $_max_role, $_min_role, $_max_file_size;
	protected $_mobile;
	
	protected $_u_more_info, $_u_media_info, $_u_aut_validation, $_u_aut_registration, $_u_personalized_email, $_u_username_email, $_u_pwd_automatic, $_u_pwd_length, $_u_pwd_length_min, $_u_pwd_length_max, $_u_pwd_number;
	
	protected $_type_media, $_type_media_value;
	
	protected $_tbl_language, $_lng_dft, $_lng_dft_name, $_lng_nav, $_lng_nav_name;
	protected $_tbl_sysconf, $_tbl_module, $_tbl_module_app, $_tbl_position;
	protected $_tbl_menu, $_tbl_menu_tree;
	protected $_tbl_page, $_tbl_page_block;
	protected $_tbl_nation, $_tbl_user, $_tbl_user_add, $_tbl_user_reg, $_tbl_user_role, $_tbl_user_email;
	protected $_tbl_translation;
	
	protected $_doc_insert, $_doc_modify, $_doc_delete, $_doc_list, $_doc_return, $_doc_language, $_doc_link, $_doc_content, $_doc_view, $_doc_email, $_doc_check, $_doc_user, $_doc_permission, $_doc_password, $_doc_search, $_doc_sort, $_doc_new, $_doc_help, $_doc_config, $_doc_back, $_doc_export, $_doc_pdf, $_doc_cart, $_doc_minimize;
	protected $_doc_home, $_doc_admin;
	
	protected $_act_insert, $_act_modify, $_act_delete, $_act_insert_first, $_act_insert_before, $_act_insert_after, $_act_insert_single, $_act_modify_single, $_act_active, $_act_view, $_act_search;
	
	protected $_link_home, $_link_admin, $_link_return;
	
	protected $_log_access, $_email_send, $_email_from;
	
	function __construct(){
		
		$this->_className = get_class($this);	// name of current class
		
		$this->_access = new Access;
		$this->_db = new DB;
		$this->_plink = new Link;

		if(isset($_SESSION['userId'])) $this->_session_user = $_SESSION['userId']; else $this->_session_user = 0;
		if(isset($_SESSION['userName'])) $this->_session_user_name = $_SESSION['userName']; else $this->_session_user_name = '';
		
		$this->_session_role = $this->_access->userRole();
		$this->_access_admin = $this->variable('admin_role');
		$this->_access_user = $this->variable('user_role');
		
		$this->_module_type = array('page','class','func');
		$this->_module_text = array(_("pagine"),_("classi"),_("funzioni"));
		
		$this->_crypt = $this->variable('password_crypt');
		
		$this->_multi_language = $this->variable('multi_language');
		$this->_delimiter = ';;';		// funzioni menuFunctionsList() e class menu
		
		$this->_max_role = 1;
		$this->_min_role = 5;
		$this->setMaxFileSize(MAX_FILE_SIZE);
		
		$this->_type_media = array('img','flash','video');
		$this->_type_media_value = array(_("immagine"),_("swf"),_("video"));
		
		$this->setLanguage();
		$this->setURL();
		$this->setHome();
		$this->setEvent();
		$this->setPath();
		$this->setSysTable();
		$this->setImgIcon();
		$this->setAction();
		$this->setLink();
		$this->setConfig();
		$this->setOptionUser();
	}
	
	private function setURL(){
		
		$this->_url_path = $_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME'];
		$this->_url_path_login = $this->_url_path."?evt[index-auth_page]";
		$this->_url_root = "http://".$_SERVER['HTTP_HOST'];
	}
	
	private function setHome(){
		
		$this->_home = HOME_FILE;
	}
	
	// Indica  se si è all'interno di una pagina amministrativa
	private function setEvent(){
		
		$request = $_SERVER['REQUEST_URI'];
		$result = preg_match('(\[[a-zA-Z0-9]+\-[a-zA-Z0-9\_]+\])', $request, $matches);
		
		if($result)
		{
			$class_event = trim($matches[0]);
			$class_event = substr($class_event, 0, -1);	// toglie ]
			$class_event = substr($class_event, 1);		// toglie [
			
			list($class, $event) = explode("-", $class_event, 2);
		}
		else
		{
			$class = '';
			$event = '';
		}
		
		if($event == 'admin_page' OR preg_match('/^manage/', $event) > 0)
		{
			$this->_admin_page = true;
		}
		else $this->_admin_page = false;
	}
	
	private function setLanguage(){
		
		$this->_tbl_language = 'language';
		$this->_lng_dft = $_SESSION['lngDft'];
		$this->_lng_nav = $_SESSION['lng'];
		$this->setLngDftName();
		$this->setLngNavName();
		$this->_trd = new translation($this->_lng_nav, $this->_lng_dft);
	}
	
	private function setLngDftName(){
		
		$query = "SELECT language FROM ".$this->_tbl_language." WHERE code='".$this->_lng_dft."'";
		$a = $this->_db->selectquery($query);
		if(sizeof($a) > 0)
		{
			foreach($a AS $b)
			{
				$language = htmlChars($b['language']);
			}
		}
		else $language = '';
		
		$this->_lng_dft_name = $language;
	}
	
	private function setLngNavName(){
		
		$query = "SELECT language FROM ".$this->_tbl_language." WHERE code='".$this->_lng_nav."'";
		$a = $this->_db->selectquery($query);
		if(sizeof($a) > 0)
		{
			foreach($a AS $b)
			{
				$language = htmlChars($b['language']);
			}
		}
		else $language = '';
		
		$this->_lng_nav_name = $language;
	}
	
	private function getMaxFileSize() {
		return $this->_max_file_size;
	}
	
	private function setMaxFileSize($req_var) {
	
		if(is_int($req_var) AND $req_var > 0) $this->_max_file_size = $req_var;
		else $this->_max_file_size = 2048000;
	}
	
	private function setPath(){
		
		$this->_os = OS;
		
		$this->_site_www = SITE_WWW;
		$this->_app_www = SITE_APP;
		$this->_img_www = SITE_IMG;
		$this->_extra_www = SITE_EXTRA;
		$this->_content_www = CONTENT_WWW;
		$this->_graphics_www = SITE_GRAPHICS;
		
		$this->_tmp_dir = TMP_DIR.$this->_os;
		$this->_app_dir = APP_DIR;
		$this->_extra_dir = EXTRA_DIR;
		$this->_content_dir = CONTENT_DIR;
		
		// Application Class
		$this->pathApp();
		
		// Contents Class
		$this->_data_www = $this->pathData('rel');
		$this->_data_dir = $this->pathData('abs');
	}
	
	private function setSysTable(){
		
		$this->_tbl_sysconf = 'sys_conf';
		$this->_tbl_module = 'sys_module';
		$this->_tbl_module_app = 'sys_module_app';
		$this->_tbl_position = 'sys_position';
		$this->_tbl_menu = 'sys_menu';
		$this->_tbl_menu_tree = 'sys_menu_tree';
		$this->_tbl_page = 'page';
		$this->_tbl_page_block = 'page_block';
		$this->_tbl_nation = 'nation';
		$this->_tbl_user = 'user_app';
		$this->_tbl_user_add = 'user_add';
		$this->_tbl_user_reg = 'user_registration';
		$this->_tbl_user_role = 'user_role';
		$this->_tbl_user_email = 'user_email';
		$this->_tbl_translation = 'language_translation';
	}
	
	private function setImgIcon(){
		
		$this->_doc_insert = "<img src=\"".$this->_img_www."/ico_insert.gif\" alt=\""._("nuovo")."\" />";
		$this->_doc_modify = "<img src=\"".$this->_img_www."/ico_modify.gif\" alt=\""._("modifica")."\" />";
		$this->_doc_delete = "<img src=\"".$this->_img_www."/ico_delete.gif\" alt=\""._("elimina")."\" />";
		$this->_doc_list = "<img src=\"".$this->_img_www."/ico_list.gif\" alt=\""._("elenco")."\" />";
		$this->_doc_return = "<img src=\"".$this->_img_www."/ico_return.gif\" alt=\""._("indietro")."\" title=\""._("indietro")."\" />";
		$this->_doc_language = "<img src=\"".$this->_img_www."/ico_language.gif\" alt=\""._("traduzione")."\" title=\""._("traduzione")."\" />";
		$this->_doc_link = "<img src=\"".$this->_img_www."/ico_link.gif\" alt=\""._("link")."\" title=\""._("link")."\" />";
		$this->_doc_content = "<img src=\"".$this->_img_www."/ico_content.gif\" alt=\""._("contenuti")."\" />";
		$this->_doc_view = "<img src=\"".$this->_img_www."/ico_view.gif\" alt=\""._("visualizza")."\" title=\""._("visualizza")."\" />";
		$this->_doc_email = "<img src=\"".$this->_img_www."/ico_email.gif\" alt=\""._("email")."\" title=\""._("email")."\"/>";
		$this->_doc_check = "<img src=\"".$this->_img_www."/ico_check.gif\" alt=\""._("check")."\" />";
		$this->_doc_user = "<img src=\"".$this->_img_www."/ico_group.gif\" alt=\""._("utenti")."\" />";
		$this->_doc_permission = "<img src=\"".$this->_img_www."/ico_permission.gif\" alt=\""._("permessi")."\" title=\""._("permessi")."\" />";
		$this->_doc_password = "<img src=\"".$this->_img_www."/ico_password.gif\" alt=\""._("password")."\" />";
		$this->_doc_search = "<img src=\"".$this->_img_www."/ico_search.gif\" alt=\""._("ricerca")."\" title=\""._("ricerca")."\"/>";
		$this->_doc_sort = "<img src=\"".$this->_img_www."/ico_sort.gif\" alt=\""._("ordina")."\" title=\""._("ordina")."\" />";
		$this->_doc_new = "<img src=\"".$this->_img_www."/ico_new.gif\" alt=\""._("novità")."\" />";
		$this->_doc_color = "<img src=\"".$this->_img_www."/ico_palette.gif\" alt=\""._("palette colori")."\" title=\""._("palette colori")."\"/>";
		$this->_doc_help = "<img src=\"".$this->_img_www."/ico_help.gif\" alt=\""._("help in linea")."\" title=\""._("help")."\"/>";
		$this->_doc_config = "<img src=\"".$this->_img_www."/ico_config.gif\" alt=\""._("impostazioni")."\" title=\""._("impostazioni")."\"/>";
		$this->_doc_home = "<img src=\"".$this->_img_www."/ico_home.gif\" alt=\""._("home")."\" title=\""._("home")."\"/>";
		$this->_doc_admin = "<img src=\"".$this->_img_www."/ico_admin.gif\" alt=\""._("amministrazione")."\" title=\""._("amministrazione")."\"/>";
		$this->_doc_back = "<img src=\"".$this->_img_www."/ico_back.gif\" alt=\""._("inizio")."\" />";
		$this->_doc_export = "<img src=\"".$this->_img_www."/ico_export.gif\" alt=\""._("esporta")."\" />";
		$this->_doc_pdf = "<img src=\"".$this->_img_www."/ico_pdf.gif\" alt=\""._("pdf")."\" />";
		$this->_doc_cart = "<img src=\"".$this->_img_www."/ico_cart.gif\" alt=\""._("metti nel carrello")."\" />";
		$this->_doc_minimize = "<img src=\"".$this->_img_www."/ico_minimize.gif\" alt=\""._("riduci a icona")."\" />";
	
	}
	
	private function setAction(){
		
		$this->_act_insert = 'insert';
		$this->_act_modify = 'modify';
		$this->_act_delete = 'delete';
		$this->_act_insert_first = 'insert_first';
		$this->_act_insert_before = 'insert_before';
		$this->_act_insert_after = 'insert_after';
		$this->_act_insert_single = 'insert_single';
		$this->_act_modify_single = 'modify_single';
		$this->_act_active = 'active';
		$this->_act_view = 'view';
		$this->_act_search = 'search';
	}
	
	private function setLink(){
		
		$this->_link_return = "<a href=\"javascript:history.go(-1)\">".$this->icon('return')."</a>";
		$this->_link_home = "<a href=\"".$this->_home."\">".$this->icon('home')."</a>";
		$this->_link_admin = "<a href=\"".$this->_home."?evt[index-admin_page]\">".$this->icon('admin')."</a>";
	}
	
	private function setConfig(){
		
		$this->_log_access = $this->variable('log_access');
		//$this->_email_name = $this->variable('email_name');		// non utilizzato
		$this->_email_send = $this->variable('email_admin');	// non utilizzato (a chi viene inviata automaticamente l'email)
		$this->_email_from = $this->variable('email_from_app');	// no-reply (class email)
	}
	
	/**
	 * Opzioni di classe
	 *
	 * @param string $option
	 * @param boolean|array $options
	 * @return value
	 * 
	 * Opzioni:
	 * translation (boolean)
	 * value					valore di default
	 */
	protected function setOption($option, $options=false) {
		
		$tbl_name = $this->_db->getFieldFromId($this->_tbl_module_app,'tbl_name','name',$this->_className);
		$tbl_name = $tbl_name."_opt";

		$instance = (isset($this->_instance))?$this->_instance:0; // 0 means the class cannot have multi instances
		
		$query = "SELECT id, $option FROM ".$tbl_name." WHERE instance='".$instance."'";
		$a = $this->_db->selectquery($query);
		if(sizeof($a)>0) {
			foreach($a as $b)
			{
				if(is_bool($options)) $trsl = $options;	// for compatibility with old version
				elseif(is_array($options) AND key_exists('translation', $options)) $trsl = $options['translation'];
				else $trsl = false;
				
				if($trsl && $this->_multi_language=='yes') $value = $this->_trd->selectTXT($tbl_name, $option, $b['id']);
				else
				{
					$value = $b[$option];
					
					//if(empty($b[$option]) AND is_array($options) AND $options['value']) $value = $options['value'];
					//else $value = $b[$option];
				}
			}
		}
		else
		{
			if(is_array($options) AND $options['value']) $value = $options['value'];
			else $value = null;
		}
		
		return $value;
	}
	
	private function setOptionUser(){
		
		$this->_u_more_info = $this->userOption('more_info');
		$this->_u_media_info = $this->userOption('media_info');
		$this->_u_aut_validation = $this->userOption('aut_valid');
		$this->_u_aut_registration = $this->userOption('aut_registration');
		$this->_u_personalized_email = $this->userOption('mod_email');
		$this->_u_username_email = $this->userOption('username_email');
		$this->_u_pwd_automatic = $this->userOption('aut_pwd');
		$this->_u_pwd_length = $this->userOption('pwd_length');
		$this->_u_pwd_length_min = $this->userOption('pwd_min_length');
		$this->_u_pwd_length_max = $this->userOption('pwd_max_length');
		$this->_u_pwd_number = $this->userOption('pwd_number');
	}
	
	private function userOption($option){
		
		$query = "SELECT id, $option FROM user_opt WHERE instance=0";
		$a = $this->_db->selectquery($query);
		if(sizeof($a)>0) {
			foreach($a as $b) {
				$value = $b[$option];
			}
			return $value;
		}
		else return null;
	}

	public static function getMultiLanguage() {
		$db = new db();
		$query = "SELECT multi_language FROM ".TBL_SYS_CONF." WHERE id=1";
		$a = $db->selectquery($query);
		return $a[0]['multi_language'];
	}

	public static function getDftLanguage() {
		$db = new db();
		$query = "SELECT dft_language FROM ".TBL_SYS_CONF." WHERE id=1";
		$a = $db->selectquery($query);
		return $a[0]['dft_language'];
	}

	public static function variable($field){
		$db = new db();
		$trd = new translation($_SESSION['lng'], $_SESSION['lngDft']);

		return $trd->selectTXT("sys_conf", "$field", 1);

	}
	
	/**
	 * Percorsi delle directory dei contenuti
	 *
	 * @param string $type			abs|rel
	 * @param string $classname		if empty -> $this->_className
	 * @return string
	 * 
	 * abs-> percorso assoluto
	 * rel-> precorso relativo
	 */
	private function pathData($type, $classname=''){
		
		if(empty($classname)) $classname = $this->_className;
		
		if($type == 'abs') return $this->_content_dir.$this->_os.$classname;
		elseif ($type == 'rel') return $this->_content_www.'/'.$classname;
		else return '';
	}
	
	private function pathApp(){
		
		$this->_class_www = $this->_app_www.'/'.$this->_className;
		$this->_class_img = $this->_class_www.'/img';
	}
	
	// Serialize/Unserialize Operations
	
	protected function obj_serialize($instanceName, $object){
		
		$filename = $this->pathData('abs', $instanceName).$this->_os.'ser_'.$instanceName.'.txt';
		
		$file = fopen($filename, "w");
		$ser = serialize($object);
		fwrite($file, $ser);
		fclose($file);
	}
	
	protected function obj_unserialize($instanceName){
		
		$filename = $this->pathData('abs', $instanceName).$this->_os.'ser_'.$instanceName.'.txt';
		
		$file = fopen($filename, "r");
		$content = file_get_contents($filename);
		$object = unserialize($content);
		fclose($file);
		
		return $object;
	}
	
	// Encode/Decode url params
	
	protected function encode_params($params){
		
		if(!empty($params))
		{
			$params = preg_replace('/=/', ':', $params);
			$params = preg_replace('/&/', ';;', $params);
		}
		return $params;
	}
	
	protected function decode_params($params){
		
		if(!empty($params))
		{
			$params = preg_replace('/:/', '=', $params);
			$params = preg_replace('/;;/', '&', $params);
		}
		return $params;
	}
	
	/**
	 * Indirizzo per il redirect
	 *
	 * @param string $params		var1=1&var2=2
	 */
	protected function urlRedirect($params=''){
		
		// Return True
		if(isset($_SESSION['url_access']) AND !empty($_SESSION['url_access']))
		{
			$url = '?'.$_SESSION['url_access'];
		}
		else $url = '';
		
		if(!empty($params) AND !empty($url))
		{
			$url .= "&".$params;
		}
		elseif(!empty($params) AND empty($url))
		{
			$url .= "?".$params;
		}
		
		// Return False
		if(isset($_SESSION['url_error']) AND !empty($_SESSION['url_error']))
		{
			$url_error = $_SESSION['url_error'];
			
			if($url_error == 'auth') $url_error = $this->_url_path_login.'&';
			else $url_error = $this->_url_path.'?'.$url_error.'&';
		}
		else $url_error = $this->_url_path.'?';	// autenticazione dalla home page
		// End
		
		$url = "http://".$this->_url_path.$url;
		$url_error = "http://".$url_error;
		
		return array($url, $url_error);
	}
	
	protected function tblname($classname){
		
		$array = array();
		
		$query = "SELECT tbl_name FROM ".$this->_tbl_module." WHERE name='$classname' AND type='class'
		UNION SELECT tbl_name FROM ".$this->_tbl_module_app." WHERE name='$classname' AND type='class'";
		$a = $this->_db->selectquery($query);
		if(sizeof($a) > 0)
		{
			foreach ($a AS $b)
			{
				$array[] = $b['tbl_name']."_usr";
				$array[] = $b['tbl_name']."_grp";
			}
		}
		
		return $array;
	}

	/**
	 * Inclusione di file CSS e Javascript relativi a singole classi
	 *
	 * @param string $file		nome del file
	 * @param string $id
	 * @param string $type		(css|js)
	 * @param string $path		percorso relativo del file
	 * @param array  $opts		opzioni
	 * @return string
	 * 
	 * @example scriptAsset('catalogue.css', 'catCSS', 'css')
	 * 
	 * Se il percorso non è specificato i file devono essere inseriti nelle directory di classe ('app')
	 */
	public function scriptAsset($file, $id, $type, $path='', $opts=null) {
		
		if($type != 'css' AND $type != 'js') return '';
		
		if(empty($file))
		{
			$file = $this->_className;
			if($type == 'css') $file .= '.css';
			elseif($type == 'js') $file .= '.js';
		}
		
		if(empty($path))
		{
			$file = $this->_class_www.'/'.$file;
		}
		else
		{
			if(substr($path, -1) != '/') $path .= '/';
			$file = $path.$file;
		}
		$GINO = '';

		$GINO .= "<script type=\"text/javascript\">\n";
		
		if($type == 'css')
		{
			$GINO .= "if(!\$defined($$('link[id=$id]')[0])) new Asset.css('$file', {id: '".$id."'});";
		}
		elseif($type == 'js')
		{
			$onload = isset($opts['onload']) ? $opts['onload'] : '';
			$GINO .= "if(!\$defined($$('script[id=$id]')[0])) new Asset.javascript('$file', {id: '".$id."'".($onload ? ", 'onLoad': $onload" : "")."});";
		}
		
		$GINO .= "</script>";

		return $GINO;
	}
	
	public static function icon($name, $text='', $tiptype='base'){
		
		switch ($name) {
			
			// Ordine alfabetico
			case 'admin':
				$icon = 'ico_admin.gif';
				$title = _("amministrazione");
				break;
			case 'attach':
				$icon = 'ico_attach.gif';
				$title = _("allegati");
				break;
			case 'back':
				$icon = 'ico_back.gif';
				$title = _("inizio");
				break;
			case 'cart':
				$icon = 'ico_cart.gif';
				$title = _("metti nel carrello");
				break;
			case 'check':
				$icon = 'ico_check.gif';
				$title = _("check");
				break;
			case 'close':
				$icon = 'ico_close.gif';
				$title = _("chiudi");
				break;
			case 'config':
				$icon = 'ico_config.gif';
				$title = _("opzioni");
				break;
			case 'content':
				$icon = 'ico_content.gif';
				$title = _("contenuti");
				break;
			case 'css':
				$icon = 'ico_CSS.gif';
				$title = _("css");
				break;
			case 'delete':
				$icon = 'ico_trash.gif';
				$title = _("elimina");
				break;
			case 'detail':
				$icon = 'ico_detail.gif';
				$title = _("dettaglio");
				break;
			case 'download':
				$icon = 'ico_download.gif';
				$title = _("download");
				break;
			case 'email':
				$icon = 'ico_email.gif';
				$title = _("email");
				break;
			case 'export':
				$icon = 'ico_export.gif';
				$title = _("esporta");
				break;
			case 'feed':
				$icon = 'ico_feed.png';
				$title = _("feed rss");
				break;
			case 'group':
				$icon = 'ico_group.gif';
				$title = _("utenti");
				break;
			case 'help':
				$icon = 'ico_help.gif';
				$title = _("help in linea");
				break;
			case 'home':
				$icon = 'ico_home.gif';
				$title = _("home");
				break;
			case 'input':
				$icon = 'ico_input.gif';
				$title = _("input");
				break;
			case 'insert':
				$icon = 'ico_insert.gif';
				$title = _("nuovo");
				break;
			case 'language':
				$icon = 'ico_language.gif';
				$title = _("traduzione");
				break;
			case 'link':
				$icon = 'ico_link.gif';
				$title = _("link");
				break;
			case 'list':
				$icon = 'ico_list.gif';
				$title = _("elenco");
				break;
			case 'minimize':
				$icon = 'ico_minimize.gif';
				$title = _("riduci a icona");
				break;
			case 'modify':
				$icon = 'ico_modify.gif';
				$title = _("modifica");
				break;
			case 'new':
				$icon = 'ico_new.gif';
				$title = _("novità");
				break;
			case 'newpdf':
				$icon = 'ico_newPDF.gif';
				$title = _("crea PDF");
				break;
			case 'palette':
				$icon = 'ico_palette.gif';
				$title = _("palette colori");
				break;
			case 'password':
				$icon = 'ico_password.gif';
				$title = _("password");
				break;
			case 'pdf':
				$icon = 'ico_pdf.gif';
				$title = _("pdf");
				break;
			case 'permission':
				$icon = 'ico_permission.gif';
				$title = _("permessi");
				break;
			case 'print':
				$icon = 'ico_print.gif';
				$title = _("stampa");
				break;
			case 'return':
				$icon = 'ico_return.gif';
				$title = _("indietro");
				break;
			case 'revision':
				$icon = 'ico_revision.gif';
				$title = _("revisione");
				break;
			case 'search':
				$icon = 'ico_search.gif';
				$title = _("ricerca");
				break;
			case 'sort':
				$icon = 'ico_sort.gif';
				$title = _("ordina");
				break;
			case 'view':
				$icon = 'ico_view.gif';
				$title = _("visualizza");
				break;
			default:
				$icon = '';
				$title = '';
		}
		
		$GINO = '';
		
		if(!empty($icon))
		{
			if(!empty($text)) $alt_text = $text; else $alt_text = $title;
			$GINO .= "<img class=\"icon_tooltip".($tiptype=='full'?_("full"):"")."\" src=\"".SITE_IMG."/$icon\" title=\"$alt_text\" />";
		}
		
		return $GINO;
	}
	
	/**
	 * Elimina ricorsivamente i file e le directory
	 *
	 * @param string $dir			percorso assoluto alla directory
	 * @param boolean $delete_dir	per eliminare o meno le directory
	 */
	public function deleteFileDir($dir, $delete_dir=true){
	
		if(is_dir($dir))
		{
			if(substr($dir, -1) != '/') $dir .= $this->_os;	// Append slash if necessary
			
			if($dh = opendir($dir))
			{
				while(($file = readdir($dh)) !== false)
				{
					if($file == "." || $file == "..") continue;
					
					if(is_file($dir.$file)) @unlink($dir.$file);
					else $this->deleteFileDir($dir.$file, true);
				}
				
				if($delete_dir)
				{
					closedir($dh);
					@rmdir($dir);
				}
			}
		}
	}
	
	/**
	 * Elimina il file indicato
	 *
	 * @param string $path_to_file
	 * @param string $home				($this->_home)
	 * @param string $redirect			(class-function)
	 * @param string $param_link		(id=3&ref=12&)
	 * @return boolean
	 * 
	 * @example $this->deleteFile($directory.$file, $this->_home, $redirect, $link);
	 * 
	 * Metodo pubblico perché viene richiamato dalla classe mFile
	 */
	public function deleteFile($path_to_file, $home, $redirect, $param_link){
		
		if(is_file($path_to_file))
		{
			if(!@unlink($path_to_file))
			{
				if(!empty($redirect)) EvtHandler::HttpCall($home, $redirect, $param_link.'error=17');
				else return false;
			}
		}
		return true;
	}
	
	protected function dimensionFile($bytes){
	
		/*
		$a = explode(',', $bytes);
		$kb = $a[0];
		*/
		$kb = (int)($bytes);
		if($kb == 0) $kb = 1;
		
		return $kb;
	}
	
	/**
	 * Stringa dei formati di file accettati
	 *
	 * @param array $extensions		array("txt", "rtf", "doc")
	 * @return string
	 */
	public static function allowedFile($extensions){
	
		$GINO = '';
		if(sizeof($extensions) > 0)
		{
			foreach($extensions AS $value)
			{
				$GINO .= $value.', ';
			}
			$GINO = substr($GINO, 0, -2);
		}
		else
		{
			$GINO = _("non risultano formati permessi.");
		}
		return $GINO;
	}
	
	/**
	 * Nome dell'estensione
	 *
	 * @param string $filename
	 * @return string
	 */
	protected function extensionFile($filename){
		
		$extension = strtolower(str_replace('.','',strrchr($filename, '.')));
		// $extension = end(explode('.', $filename))
		return $extension;
	}
	
	/**
	 * Controlla se l'estensione è valida
	 *
	 * @param string $filename
	 * @param array $extensions
	 * @return boolean
	 */
	protected function verifyExtension($filename, $extensions){
		
		$ext = $this->extensionFile($filename);
		
		if(sizeof($extensions) > 0 AND !empty($ext))
		{
			if(in_array($ext, $extensions)) return true; else return false;
		}
		else return false;
	}
	
	protected function typeMedia($file){
		
		$media = '';
		
		if(!empty($file))
		{
			$ext = $this->extensionFile($file);
			
			if($ext == 'gif' OR $ext == 'jpg' OR $ext == 'png')
			{
				$media = $this->_type_media[0];
			}
			elseif($ext == 'swf')
			{
				$media = $this->_type_media[1];
			}
			elseif($ext == 'mov' OR $ext == 'avi')
			{
				$media = $this->_type_media[2];
			}
		}
		
		if(empty($media)) $media = $this->_type_media[0];
		
		return $media;
	}
	
	protected function enabledPng(){
		
		if (function_exists('gd_info'))
		{
			$array = gd_info();
			/*
			foreach($array as $key=>$val)
			{
				if($val===true) $val="Enabled";
				if($val===false) $val="Disabled";
				echo "$key: $val <br />\n";
			}
			*/
			
			return $array['PNG Support'];
		}
		else return false;
	}
	
	public function enabledZip(){
		
		if (class_exists('ZipArchive'))
			return true;
		else
			return false;
	}
	
	protected function cryptMethod($pwd, $crypt){

		if(empty($crypt)) $crypt = $this->_crypt;
		
		if(!empty($crypt) AND $crypt != 'none')
		{
			$method = $this->_crypt;
			$password = $method($pwd);
		}
		else $password = $pwd;
		
		return $password;
	}
	
	/**
	 * Browser version
	 * 
	 * @param string $field			Parent|Platform
	 * @return string
	 * 
	 * @example 
	 * Parent: "IE 6.0", "Firefox 1.5"
	 * Platform: Win2000, Linux, MacPPC
	 */
	public function detectBrowser($field=false){
		
		if(!$field) $field = 'Parent';
		
		if(ini_get("browscap")) {	// If available, use PHP native function
			$current_browser = get_browser(null, true);
		}
		else {
			$browscap_dir = LIB_DIR.$this->_os.'browscap';
			require_once($browscap_dir.$this->_os.'php-local-browscap.php');
			$current_browser = get_browser_local(null, false, LIB_DIR.OS.'browscap'.OS.'./browscap.ini');
		}
		
		if(is_array($current_browser) && array_key_exists($field, $current_browser))
			$value = $current_browser[$field]; else $value = '';
		
		return $value;
	}
	
	private function _detectBrowser_() {
		
		$user_agent = $_SERVER['HTTP_USER_AGENT'];
		
		$browsers = array(
			'Opera' => 'Opera',
			'Mozilla Firefox'=> '(Firebird)|(Firefox)',
			'Galeon' => 'Galeon',
			'Mozilla'=>'Gecko',
			'MyIE'=>'MyIE',
			'Lynx' => 'Lynx',
			'Netscape' =>   '(Mozilla/4\.75)|(Netscape6)|(Mozilla/4\.08)|(Mozilla/4\.5)|(Mozilla/4\.6)|(Mozilla/4\.79)',
			'Konqueror'=>'Konqueror',
			'SearchBot' => '(nuhk)|(Googlebot)|(Yammybot)|(Openbot)|(Slurp/cat)|(msnbot)|(ia_archiver)',
			'Internet Explorer 7' => '(MSIE 7\.[0-9]+)',
			'Internet Explorer 6' => '(MSIE 6\.[0-9]+)',
			'Internet Explorer 5' => '(MSIE 5\.[0-9]+)',
			'Internet Explorer 4' => '(MSIE 4\.[0-9]+)'
		);
		foreach($browsers as $browser=>$pattern)
		{
			if (eregi($pattern, $user_agent))
				return $browser;
		}
		
		return 'Unknown';
	}
	
	/**
	 * Return the user's name
	 *
	 * @param integer $user_id
	 * @param string $string_case		lower | upfirststring | upfirstword | upper
	 * @param string $field_first		firstname | lastname | company
	 * @param string $table
	 * @param string $field_id
	 * @return string
	 * 
	 * @example (case)
	 * lower: nome cognome
	 * upfirststring: Nome cognome
	 * upfirstword: Nome Cognome
	 * upper: NOME COGNOME
	 */
	protected function nameUser($user_id, $string_case, $field_first='', $table='', $field_id=''){
		
		if(empty($field_first)) $field_first = 'firstname';
		if(empty($table)) $table = $this->_tbl_user;
		if(empty($field_id)) $field_id = 'user_id';
		
		$query = "SELECT firstname, lastname, company FROM $table WHERE $field_id='$user_id'";
		$a = $this->_db->selectquery($query);
		if(sizeof($a) > 0)
		{
			foreach ($a AS $b)
			{
				$firstname = htmlChars($b['firstname']);
				$lastname = htmlChars($b['lastname']);
				$company = htmlChars($b['company']);
				
				if($field_first == 'firstname')
				{
					$name = $firstname.' '.$lastname;
				}
				elseif($field_first == 'lastname')
				{
					$name = $lastname.' '.$firstname;
				}
				else
				{
					$name = $company;
				}
				
				switch ($string_case)
				{
					case 'lower':
						$name = strtolower($name);
						break;
					case 'upfirststring':
						$name = ucfirst(strtolower($name));
						break;
					case 'upfirstword':
						$name = ucwords(strtolower($name));
						break;
					case 'upper':
						$name = strtoupper($name);
						break;
					default:
						$name = strtolower($name);
						break;
				}
				
				return $name;
			}
		}
	}
	
	// return true if value exist
	protected function valueExist($query, $field, $value_match){
		
		$a = $this->_db->selectquery($query);
		if(sizeof($a) > 0)
		{
			foreach($a AS $b)
			{
				if($b[$field] == $value_match) return true;
			}
		}
		return false;
	}
	
	protected function emailSend($to, $subject, $object, $from='', $type='text'){
		
		// type: text, html
		
		$m_to = $to;
		$m_subject = $subject;
		$m_object = $object;
		
		if(empty($from)) $from = $this->_email_from;
		$m_from = "From: ".$from;
		
		mail($m_to, $m_subject, $m_object, $m_from);
	}
	
	protected function emailPolicy(){
		
		// if(empty($email_admin)) $email_admin = '[inserire nelle impostazioni]';
		
		$GINO = "\n\n"._("Indirizzo web").": http://".$_SERVER['HTTP_HOST'].$this->_site_www."\n---------------------------------------------------------------\n"._("La presente email è stata inviata con procedura automatica. Si prega di non rispondere alla presente email.")."\n\n"._("Per problemi o segnalazioni potete scrivere a ").$this->_email_send;

		return $GINO;
	}
	
	/*
	 * Esportazione file
	 * 
	 * I valori da DB devono passare attraverso le funzioni:
	 * 
	 * $firstname = enclosedField(utf8_encode($b['firstname']));	-> TESTO
	 * $date = utf8_encode($b['date']);								-> DATA
	 * $number = $b['number'];										-> NUMERO
	 * 
	 * Creare il file sul filesystem:
	 * 
$filename = $this->_doc_dir.'/'.$filename;
if(file_exists($filename)) unlink($filename);
$this->writeFile($filename, $output, 'csv');
	 * 
	 * Effettuare il download del file:
	 * 
$filename = 'export.csv';
header("Content-type: application/csv \r \n");
header("Content-Disposition: inline; filename=$filename");
echo $output;
exit();
	 */
	 
	/**
	 * Crea un file con specifiche caratteristiche
	 *
	 * @param string $filename		absolute path to file
	 * @param string $content		file content
	 * @param string $type			utf8, iso8859, csv
	 * 
	 * @see 
	 * csv: utilizzare la funzione utf8_encode() sui valori da DB
	 */
	protected function writeFile($filename, $content, $type) {
		
		$dhandle = fopen($filename, "wb");
		
		if($type == 'utf8')
		{
			# Add byte order mark
			fwrite($dhandle, pack("CCC",0xef,0xbb,0xbf));
		}
		else 
		{
			if($type == 'iso8859')
			{
				# From UTF-8 to ISO-8859-1
				$content = mb_convert_encoding($content, "ISO-8859-1", "UTF-8");
			}
			elseif($type == 'csv')
			{
				# UTF-8 Unicode CSV file that opens properly in Excel
				$content = chr(255).chr(254).mb_convert_encoding( $content, 'UTF-16LE', 'UTF-8');
			}
		}
		
		fwrite($dhandle, $content);
		fclose($dhandle);
	}
	
	protected function removeBOM($str=''){
		
		if(substr($str, 0,3) == pack("CCC",0xef,0xbb,0xbf)) {
			$str = substr($str, 3);
		}
		return $str;
	}
}
?>
