<?php

class Access extends pub {
	
	public $default_role;
	
	protected $_home;
	protected $_log_access, $_username_email;
	protected $_crypt;
	protected $_db;
	protected $_session_user, $_session_role, $_access_admin;

	private $_block_page;

	function __construct(){

		$this->_db = db::instance();

		$this->_home = HOME_FILE;
		$this->_crypt = pub::variable('password_crypt');
		
		$this->_log_access = pub::variable('log_access');
		
		$this->defaultRole();

		if(isset($_SESSION['userId'])) $this->_session_user = $_SESSION['userId']; else $this->_session_user = 0;
		$this->_session_role = $this->userRole();
		$this->_access_admin = pub::variable('admin_role');

		$this->_block_page = $this->_home."?evt[index-auth_page]";
	}
	
	private function defaultRole(){

		$query = "SELECT role_id FROM ".TBL_USER_ROLE." WHERE default_value='yes'";
		$a = $this->_db->selectquery($query);
		sizeof($a) > 0 ?$this->default_role = $a[0]['role_id']:exit(error::syserrorMessage("admin", "defaultRole", _("Ruolo di default non settato"), __LINE__));
	}
	
	private function verifyRole($role_id){
		
		$query = "SELECT role_id FROM ".TBL_USER_ROLE." WHERE role_id='$role_id'";
		$a = $this->_db->selectquery($query);
		return (sizeof($a) > 0);
	}
	
	private function AuthenticationMethod($user, $pwd){

		$pwd = pub::cryptMethod($pwd, $this->_crypt);

		$query = "SELECT user_id, firstname, lastname, role FROM ".TBL_USER."
		WHERE username='$user' AND userpwd='$pwd' AND valid='yes'";
		$a = $this->_db->selectquery($query);
		if(sizeof($a) == 1)
		{
			foreach($a AS $b)
			{
				if(!$this->verifyRole($b['role'])) return false;
				
				$user_id = $b["user_id"];
				$role = $b["role"];
				
				$_SESSION["userId"] = $user_id;
				$_SESSION["userName"] = htmlChars($b["firstname"].' '.$b["lastname"]);
				$_SESSION["userRole"] = $role;
				
				if($this->_log_access == 'yes') $this->logAccess($user_id);
			}
			return true;
		}
		else
		{
			return false;
		}
	}
	
	private function logAccess($userid) {
		
		date_default_timezone_set('Europe/Rome');

		$date = date("Y-m-d H:i:s");
		$query = "INSERT INTO ".TBL_LOG_ACCESS." (user_id, date) VALUES ($userid, '$date')";
		$result = $this->_db->actionquery($query);
		
		return $this;
	}
	
	/**
	 * Form di autenticazione
	 *
	 * @return string
	 */
	public function AccessForm(){

		$GINO = "<section class=\"auth_form\">\n";
		$GINO .= "<form action=\"\" method=\"post\" id=\"formauth\" name=\"formauth\">\n";
		$GINO .= "<input type=\"hidden\" id=\"action\" name=\"action\" value=\"auth\" />\n";
		$GINO .= "<label for=\"user\">".($this->_u_username_email?"Email":"Username")."</label><br />";
		$GINO .= "<input type=\"text\" id=\"user\" name=\"user\" size=\"25\" maxlength=\"50\" class=\"auth\" /><br />";
		$GINO .= "<label>"._("Password")."</label><br />";
		$GINO .= "<input type=\"password\" name=\"pwd\" size=\"25\" maxlength=\"15\" class=\"auth\" /><br /><br />";
		$GINO .= "<input type=\"submit\" class=\"submit\" name=\"login_user\" value=\"login\" />";
		$GINO .= "</form>\n";
		$GINO .= "</section>\n";
		
		return $GINO;
	}
	
	/*
	 * Funzione di Autenticazione
	 */
	public function Authentication(){
		if((isset($_POST['action']) && $_POST['action']=='auth')) {
			$user = cleanVar($_POST, 'user', 'string', '');
			$password = cleanVar($_POST, 'pwd', 'string', '');
			$this->AuthenticationMethod($user, $password)? $this->loginSuccess():$this->loginError(_("autenticazione errata"));
		}
		elseif((isset($_GET['action']) && $_GET['action']=='logout')) {
			
			$_SESSION = array();	// svuoto la sessione
			session_destroy();		// destroy session
			header("Location: ".$this->_home."?logout");
		}
	}
	
	private function loginError($message) {

		$self = $_SERVER['PHP_SELF'].($_SERVER['QUERY_STRING'] ? "?".$_SERVER['QUERY_STRING']:'');

		exit(error::errorMessage(array('error'=>$message), $self));
	}

	private function loginSuccess() {

		$self = $_SERVER['PHP_SELF'].($_SERVER['QUERY_STRING'] ? "?".$_SERVER['QUERY_STRING']:'');

		$redirect = isset($_SESSION['auth_redirect'])
				?$_SESSION['auth_redirect']
				:(isset($_SERVER['QUERY_STRING']) && $_SERVER['QUERY_STRING']=='evt[index-auth_page]'
					? $this->_home
					: $self);

		header("Location: ".$redirect);
	}

	/**
	 * Restituisce l'ID del ruolo dell'utente
	 *
	 * @return integer
	 */
	public function userRole(){

		if(empty($this->_session_user)) 
			$role = $this->default_role;
		else
		{
			$query = "SELECT role FROM ".TBL_USER." WHERE user_id='".$this->_session_user."'";
			$a = $this->_db->selectquery($query);
			if(sizeof($a) == 1) $role = $a[0]['role'];
			else
				exit(error::syserrorMessage("access", "userRole", _("impossibile associare un ruolo all'utente id:").$this->_session_user, __LINE__));
		}

		return $role;
	}

	private function queryClassRole($class_name, $field, $instance){
		
		$value = '';
		$query = $instance
			? "SELECT $field FROM ".TBL_MODULE." WHERE id='$instance' AND class='$class_name'"
			: "SELECT $field FROM ".TBL_MODULE_APP." WHERE name='$class_name' AND type='class'";
		$a = $this->_db->selectquery($query);
		if(sizeof($a) > 0) $value = $a[0][$field];

		return $value;
	}
	
	// ID del ruolo di una data classe
	public function classRole($class_name, $instance, $role){

		$value = '';
		
		if($role == 'user' || $role == 'user2' || $role == 'user3') {

			$field = $role=='user' ? "role1":($role=='user2'?"role2":"role3");
			$value = $this->queryClassRole($class_name, $field, $instance);
			
		}
		
		if(empty($value))
			exit(error::syserrorMessage("access", "classRole", _("impossibile leggere i privilegi di accesso per la classe ").$class_name._(" istanza ").$instance, __LINE__));
		
		return $value;
	}
	
	/**
	 * Verifica del ruolo utente per l'accesso a una pagina di classe.
	 * Viene utilizzata nella funzione accessType($role).
	 *
	 * @param integer $role		ID del ruolo
	 * @return boolean
	 * 
	 * @example $this->_access->accessType($this->_access_base)
	 */
	public function AccessVerifyRoleID($role){

		if(!$role OR !$this->verifyRole($role))
			$this->blockUser(_("Permessi insufficienti per visualizzare i contenuti richiesti"), $this->_block_page, array("logout"=>true));
		else
		{
			if($role >= $this->userRole())
				return true;
			else
				$this->blockUser(_("Permessi insufficienti per visualizzare i contenuti richiesti"), $this->_block_page, array("logout"=>true));
		}
	}
	
	/**
	 * Verifica la possibilità di accedere a specifiche funzionalità in riferimento al proprio ruolo
	 *
	 * @param integer $role		ID del ruolo
	 * @return boolean
	 * 
	 * @example $this->_access->AccessVerifyRoleIDIf($this->_access_global)
	 */
	public function AccessVerifyRoleIDIf($role){

		if(!$role OR !$this->verifyRole($role))
		{
			return false;
		}
		else
		{
			if($role >= $this->userRole()) return true;
			else return false;
		}
	}
	
	/**
	 * Verifica del ruolo utente per l'accesso a una pagina.
	 *
	 * @param integer $module_id
	 * @return boolean
	 */
	public function AccessVerifyPage($module_id){

		$query = "SELECT role1 FROM ".TBL_MODULE." WHERE id='$module_id' AND type='page'";
		$a = $this->_db->selectquery($query);
		if(sizeof($a) > 0)
		{
			foreach($a AS $b)
			{
				$role = $b['role1'];
			}
		}
		
		if(empty($role))
		{
			header("Location:http://".$this->_url_path_login."&err=ERROR: system error 1");
			exit();
		}
		
		/*
		if(!$role OR !$this->verifyRole($role))
		{
			header("Location:http://".$this->_url_path_login."&err=ERROR: no access 3");
			exit();
		}
		*/
		
		if($role >= $this->userRole())
		{
			return true;
		}
		else
		{
			header("Location:http://".$this->_url_path_login."&err=ERROR: no access 4");
			exit();
		}
	}
	
	/*
		Gruppi
	*/
	
	private function referenceTable($class_name){
		
		return TBL_MODULE_APP;
		/*
		$query = "SELECT id FROM sys_module WHERE name='$class_name' AND type='class'";
		$a = $this->_db->selectquery($query);
		if(sizeof($a) > 0)
		{
			$table = 'sys_module';
		}
		else
		{
			$query = "SELECT id FROM sys_module_app WHERE name='$class_name' AND type='class'";
			$a = $this->_db->selectquery($query);
			if(sizeof($a) > 0)
				$table = 'sys_module_app';
		}
		return $table;
		 */
	}
	
	private function blockUser($message, $redirect, $options) {
	
		if(isset($options['logout']) && $options['logout']===true) {
			unset($_SESSION);
			session_destroy();
			session_name(SESSION_NAME);
			session_start();
		}

		exit(error::errorMessage(array('error'=>$message), $redirect));
	}
	/**
	 * ID Gruppo amministratore della classe
	 *
	 * @param string $class_name
	 * @return integer
	 */
	public function adminGroup($class_name){
		
		$table = $this->referenceTable($class_name);
		
		$query = "SELECT role_group FROM $table WHERE name='$class_name' AND type='class'";
		$a = $this->_db->selectquery($query);
		if(sizeof($a) > 0)
		{
			foreach($a AS $b)
			{
				$role_group = $b['role_group'];
			}
		}
		else $role_group = '';
		
		return $role_group;
	}
	
	/**
	 * Verifica se un utente è l'amministratore di una classe
	 *
	 * @param string $class_name
	 * @return boolean
	 */
	public function AccessAdminClass($class_name, $instance){
		
		if(empty($class_name)) return false;
		
		$control = false;
		$table = $this->referenceTable($class_name);
		
		$query = "SELECT role_group, tbl_name FROM $table WHERE name='$class_name' AND type='class'";
		$a = $this->_db->selectquery($query);
		if(sizeof($a) > 0)
		{
			foreach($a AS $b)
			{
				$tbl_group = $b['tbl_name'].'_grp';
				$tbl_user = $b['tbl_name'].'_usr';
				$role_group = $b['role_group'];
			}
		}
		
		if($this->_db->tableexists($tbl_group) AND $this->_db->tableexists($tbl_user))
		{
			$query = "SELECT user_id FROM $tbl_user WHERE group_id='$role_group' AND user_id='".$this->_session_user."' AND instance='$instance'";
			$a = $this->_db->selectquery($query);
			if(sizeof($a) > 0)
			{
				$control = true;
			}
		}
		return $control;
	}
	
	// Elenco dei gruppi della classe
	public function listGroup($class_name){
		
		$group = array();
		$table = $this->referenceTable($class_name);
		
		$query = "SELECT tbl_name FROM $table WHERE name='$class_name' AND type='class'";
		$a = $this->_db->selectquery($query);
		if(sizeof($a) > 0)
		{
			foreach($a AS $b)
			{
				$tbl_group = $b['tbl_name'].'_grp';
			}
		}
		
		if($this->_db->tableexists($tbl_group))
		{
			$query = "SELECT id FROM $tbl_group ORDER BY id ASC";
			$a = $this->_db->selectquery($query);
			if(sizeof($a) > 0)
			{
				foreach ($a AS $b)
				{
					$group[] = $b['id'];
				}
			}
		}
		return $group;
	}
	
	/**
	 * Elenco dei gruppi di un utente in riferimento a una data classe
	 *
	 * @param string $class_name
	 * @return array
	 */
	public function userGroup($class_name, $instance=null, $no_admin = true){
		
		$group = array();
		$table = $this->referenceTable($class_name);
		
		$query = "SELECT tbl_name FROM $table WHERE name='$class_name'";
		$a = $this->_db->selectquery($query);
		if(sizeof($a) > 0)
		{
			foreach($a AS $b)
			{
				$tbl_group = $b['tbl_name'].'_grp';
				$tbl_user = $b['tbl_name'].'_usr';
			}
		}
		
		if($this->_db->tableexists($tbl_group) AND $this->_db->tableexists($tbl_user))
		{
			$query = $no_admin
				? "SELECT group_id FROM $tbl_user WHERE user_id='".$this->_session_user."' AND instance='$instance'"
				: "SELECT group_id FROM $tbl_user AS u, $tbl_group AS g WHERE u.user_id='$this->_session_user' AND u.instance='$instance' AND u.group_id=g.id AND g.no_admin='no'";
			$a = $this->_db->selectquery($query);
			if(sizeof($a) > 0)
			{
				foreach ($a AS $b)
				{
					$group[] = $b['group_id'];
				}
			}
		}
		return $group;
	}
	
	/**
	 * Verifica l'appartenenza ad almeno un gruppo della classe
	 *
	 * @param string $class_name		nome della classe
	 * @param array $user_group			gruppi cui può accedere l'utente
	 * @param array|string $permission	gruppi cui è concesso l'accesso a una determinata funzione
	 * @return boolean
	 * 
	 * @example all'interno della classe -> $this->accessGroup()
	 * 
	 * $this->accessGroup($this->_group_1);	// accesso a gruppi selezionati
	 * $this->accessGroup('ALL');	// accesso a tutti i gruppi della classe
	 * $this->accessGroup('');		// amm. sito + amm. classe
	 */
	public function AccessVerifyGroup($class_name, $instance, $user_group, $permission){

		if($this->AccessVerifyRoleIDIf($this->_access_admin))	// amm sito
		{
			return true;
		}
		elseif($this->AccessAdminClass($class_name, $instance))	// amm classe
		{
			return true;
		}
		elseif(is_array($user_group) AND is_array($permission))
		{
			if(sizeof($user_group) > 0 AND sizeof($permission) > 0)
			{
				foreach($permission AS $value)
				{
					if(in_array($value, $user_group)) return true;
				}
			}
		}
		elseif(is_string($permission) AND $permission == 'ALL')	// htmlcode
		{
			if(!is_array($user_group)) $user_group = $this->userGroup($class_name, $instance, false);
			
			if(sizeof($user_group) > 0) return true;
		}
		
		$this->blockUser(_("Permessi insufficienti per visualizzare i contenuti richiesti"), $this->_block_page, array("logout"=>true));
	}
	
	/**
	 * Controlla se un utente può accedere a porzioni di codice.
	 *
	 * @param string $class_name		nome della classe
	 * @param array $user_group			gruppi cui è associato l'utente
	 * @param array|string $permission	gruppi che possiedono i permessi
	 * @return boolean
	 * 
	 * @example
	 * $this->_access->AccessVerifyGroupIf($this->_className, $this->_user_group, $this->_group_1)
	 * $this->_access->AccessVerifyGroupIf($this->_className, '', 'ALL')
	 * $this->_access->AccessVerifyGroupIf($this->_className, '', '')	// amm. sito + amm. classe
	 */
	public function AccessVerifyGroupIf($class_name, $instance, $user_group, $permission){

		$control = false;
		if($this->AccessVerifyRoleIDIf($this->_access_admin))	// amm sito
		{
			$control = true;
		}
		elseif($this->AccessAdminClass($class_name, $instance))	// amm classe | istanza
		{
			$control = true;
		}
		elseif(is_array($user_group) AND is_array($permission))
		{
			if(sizeof($user_group) > 0 AND sizeof($permission) > 0)
			{
				foreach($permission AS $value)
				{
					if(in_array($value, $user_group)) $control = true;
				}
			}
		}
		elseif(is_string($permission) AND $permission == 'ALL')	// htmlcode
		{
			$user_group = $this->userGroup($class_name, $instance);
			if(sizeof($user_group) > 0) $control = true;
		}
		else $control = false;
		
		return $control;
	}

	/*
		Verifica dell'accesso
	*/
	public function AccessVerify(){

		if(empty($this->_session_user))
		{
			header("Location:http://".$this->_url_path_login."&err=ERROR: no access 6");
			exit();
		}
	}

	public function AccessVerifyIf(){

		if(empty($this->_session_user)) return false; else return true;
	}
	
	/**
	 * Metodo di accesso all'area amministrativa
	 *
	 * @return boolean
	 */
	public function getAccessAdmin() {
		
		if(empty($this->_session_user)) return false;
		if(!$this->verifyRole($this->_session_role)) return false;
		
		if($this->_session_role <= $this->_access_admin) return true;
		
		$query = "SELECT sm.tbl_name 
			  FROM ".TBL_MODULE_APP." AS sm, ".TBL_MODULE." AS m
			  WHERE sm.type='class' AND 
			        ((sm.masquerade='no' AND sm.instance='no') OR (sm.masquerade='no' AND sm.instance='yes' AND m.class=sm.name AND m.masquerade='no'))";
		$a = $this->_db->selectquery($query);
		if(sizeof($a) > 0)
		{
			foreach ($a AS $b)
			{
				$tblusrname = $b['tbl_name']."_usr";
				$tblgrp = $b['tbl_name']."_grp";
				
				if($this->_db->tableexists($tblgrp) AND $this->_db->tableexists($tblusrname)) {
					$query2 = "SELECT u.group_id FROM ".$tblusrname." AS u, ".$tblgrp." AS g
					WHERE u.user_id='".$this->_session_user."' AND u.group_id=g.id AND g.no_admin='no'";
					$a2 = $this->_db->selectquery($query2);
					if(is_array($a2) && sizeof($a2) > 0) return true;
				}
			}
		}
		return false;
	}
	
	public function listRole(){

		$role_type = array(); $role_name = array();

		$query = "SELECT role_id, name FROM ".TBL_USER_ROLE." ORDER BY role_id";
		$a = $this->_db->selectquery($query);
		if(sizeof($a) > 0)
		{
			foreach($a AS $b)
			{
				$role_type[] = $b['role_id'];
				$role_name[] = $b['name'];
			}
		}

		return array_combine($role_type, $role_name);
	}

	private function textCleanup($value)
	{
		$value = trim($value);
		$value = strip_tags($value, '');

		if(!get_magic_quotes_gpc()) $value = addslashes($value);
		
		return $value;
	}
}
?>
