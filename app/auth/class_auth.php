<?php
/**
 * @file class_auth.php
 * @brief Contiene la classe auth
 * 
 * @copyright 2013 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */

require_once('class.User.php');
require_once('class.Group.php');
require_once('class.Permission.php');

require_once(CLASSES_DIR.OS.'class.AdminTable.php');
require_once('class.AdminTable_AuthUser.php');

/**
 * @brief Gestione degli utenti
 * 
 * @copyright 2013 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 * 
 * 
 * I permessi delle applicazioni sono definiti nella tabella @a auth_permission. Il campo @a admin indica se il permesso necessita dell'accesso all'area amministrativa. \n
 * Ogni utente può essere associato a un permesso definito nella tabella @a auth_permission, e tale associazione viene registrata nella tabella @a auth_user_perm. \n
 * La tabella @a auth_user_perm registra il valore ID dell'utente, del permesso e dell'istanza relativa all'applicazione del permesso. \n
 * Questo implica che nell'interfaccia di associazione utente/permessi occorre mostrare i permessi relativi a ogni applicazione (classe) per tutte le istanze presenti.
 * 
 * I gruppi sono definiti nella tabella @a auth_group. I gruppi possono essere associati ai permessi e alle istanze (auth_group_perm) e gli utenti ai gruppi (auth_group_user).
 * 
 * Ogni volta che si installa una applicazione bisogna creare i record in auth_group_perm ?
 * 
 * 
 * 
 */
class auth extends Controller {
	
	private $_options;
	public $_optionsLabels;
	
	private $_title;
	private $_users_for_page;
	private $_user_more, $_user_view;
	private $_self_registration, $_self_registration_active;
	private $_username_as_email;
	private $_aut_pwd, $_aut_pwd_length, $_pwd_length_min, $_pwd_length_max, $_pwd_numeric_number;
	
	public $_other_field1, $_other_field2, $_other_field3;
	private $_label_field1, $_label_field2, $_label_field3;

	function __construct(){

		parent::__construct();

		$this->_instance = 0;
		$this->_instanceName = $this->_class_name;

		$this->_title = htmlChars($this->setOption('title', true));
		$this->_users_for_page = $this->setOption('users_for_page');
		$this->_user_more = $this->setOption('user_more_info');
		$this->_user_view = $this->setOption('user_card_view');
		$this->_self_registration = $this->setOption('self_registration');
		$this->_self_registration_active = $this->setOption('self_registration_active');
		
		$this->_username_as_email = $this->setOption('username_as_email');
		$this->_aut_pwd = $this->setOption('aut_pwd');
		$this->_aut_pwd_length = $this->setOption('aut_pwd_length');
		$this->_pwd_length_min = $this->setOption('pwd_min_length');
		$this->_pwd_length_max = $this->setOption('pwd_max_length');
		$this->_pwd_numeric_number = $this->setOption('pwd_numeric_number');
		
		$this->_options = loader::load('Options', array($this->_class_name, $this->_instance));
		$this->_optionsLabels = array(
			"title"=>_("Titolo"), 
			"users_for_page"=>_("Utenti per pagina"),
			"user_more_info"=>_("Informazioni aggiuntive utenti"), 
			"user_card_view"=>_("Schede utenti visibili"),
			"self_registration"=>_("Registrazione autonoma"),
			"self_registration_active"=>_("Utenti attivi automaticamente"),
			"username_as_email"=>_("Utilizzo email come username"),
			"aut_pwd"=>_("Generazione automatica password"),
			"aut_pwd_length"=>_("Caratteri della password automatica"),
			"pwd_min_length"=>_("Minimo caratteri password"),
			"pwd_max_length"=>_("Massimo caratteri password"),
			"pwd_numeric_number"=>_("Caratteri numerici password")
		);
	}
	
	/*
	$obj = new $class($module->id);
            if($permissions_code and count($permissions_code)) {
              foreach($permissions_code as $permission_code) {
                $permissions[] = $obj->permissions()[$permission_code];
              }
            }
            */
	public function permissions() {
		
		return array(
			'can_admin' => _('utenti amministratori del modulo')
		);
	}
	
	/**
	 * Elenco dei metodi che possono essere richiamati dal menu e dal template
	 * 
	 * @return array
	 */
	public static function outputFunctions() {

		$list = array(
			/*"blockList" => array("label"=>_("Elenco utenti"), "role"=>'1'),
			"viewList" => array("label"=>_("Elenco utenti e schede informazioni"), "role"=>'1'),
			"userCard" => array("label"=>_("Scheda utente connesso"), "role"=>'3'),
			"registration" => array("label"=>_("Form di registrazione autonoma"), "role"=>'2'),
			"personal" => array("label"=>_("Modifica dati personali"), "role"=>'3')*/
		);

		return $list;
	}
	
	/**
	 * Percorso base della directory dei contenuti
	 *
	 * @param string $path tipo di percorso (default abs)
	 *   - abs, assoluto
	 *   - rel, relativo
	 * @return string
	 */
	public function getBasePath($path='abs'){
	
		$directory = '';
		
		if($path == 'abs')
			$directory = $this->_data_dir.OS;
		elseif($path == 'rel')
			$directory = $this->_data_www.'/';
		
		return $directory;
	}
	
	/**
	 * Percorso della directory dei contenuti (una directory per ogni utente)
	 * 
	 * @param integer $id valore ID dell'utente
	 * @return string
	 */
	public function getAddPath($id) {
		
		if(!$id) $id = $this->_db->autoIncValue(User::$table);
		
		$directory = $id.OS;
		
		return $directory;
	}
	
	public function manageAuth() {

		$this->requirePerm('can_admin');

		$block = cleanVar($_GET, 'block', 'string', null);

		$link_options = "<a href=\"".$this->_home."?evt[$this->_class_name-manageAuth]&block=options\">"._("Opzioni")."</a>";
		$link_group = "<a href=\"".$this->_home."?evt[$this->_class_name-manageAuth]&block=group\">"._("Gruppi")."</a>";
		$link_perm = "<a href=\"".$this->_home."?evt[$this->_class_name-manageAuth]&block=perm\">"._("Permessi")."</a>";
		$link_dft = "<a href=\"".$this->_home."?evt[".$this->_class_name."-manageAuth]\">"._("Gestione")."</a>";
		$sel_link = $link_dft;

		if($block=='options') {
			$content = $this->manageOptions();
			$sel_link = $link_options;
		}
		elseif($block=='group') {
			$content = $this->manageGroup();
			$sel_link = $link_group;
		}
		elseif($block=='perm') {
			$content = $this->managePermission();
			$sel_link = $link_perm;
		}
		else {
			$content = $this->manageUser();
		}

		$dict = array(
			'title' => _('Utenti di sistema'),
			'links' => array($link_options, $link_perm, $link_group, $link_dft),
			'selected_link' => $sel_link,
			'content' => $content
		);

		$view = new view(null, 'tab');
		$view->setViewTpl('tab');

		return $view->render($dict);
	}
	
	/**
	 * Gestione dell'utente
	 * 
	 * @return string
	 */
	private function manageUser() {
		
		//Loader::import('class', 'AdminTable');
		
		$info = "<div class=\"backoffice-info\">";
		$info .= "<p>"._("Elenco degli utenti del sistema.")."</p>";
		$info .= "</div>";
		
		
		$opts = array(
			'list_display' => array('id', 'firstname', 'lastname', 'email', 'active'),
			'list_description' => $info, 
			'add_buttons' => array(
				array('label'=>pub::icon('group', array('scale' => 1)), 'link'=>$this->_home."?evt[".$this->_class_name."-joinUserGroup]", 'param_id'=>'ref'), 
				array('label'=>pub::icon('permission', array('scale' => 1)), 'link'=>$this->_home."?evt[".$this->_class_name."-joinUserPermission]", 'param_id'=>'ref'), 
				array('label'=>pub::icon('password', array('scale' => 1)), 'link'=>$this->_home."?evt[".$this->_class_name."-changePassword]", 'param_id'=>'ref')
			)
		);
		
		/*
		Lo username e l'email devono essere unici. IMPOSTARE UNIQUE KEY ?
		
		Se come username si imposta l'email (proprietà $_username_as_email), il campo username non viene mostrato nell'inserimento e il campo email nella modifica
		
		Nell'inserimento viene chiesto di riscrivere l'email come controllo.
		Se viene impostata la generazione automatica della password, nell'inserimento non viene mostrato il campo userpwd
		
		
		Nell'inserimento di un nuovo utente vengono effettuati i controlli con i metodi: User::checkPassword(), User::checkUsername()
		In inserimento e modifica vengono effettuati i controlli con il metodo: User::checkEmail()
		
		 */
		
		$id = cleanVar($_GET, 'id', 'int', '');
		$edit = cleanVar($_GET, 'edit', 'int', '');
		
		if($id && $edit)	// modify
		{
			$removeFields = array('username', 'userpwd');
			
			if($this->_username_as_email) $removeFields[] = 'email';
			
			$addCell = null;
		}
		else
		{
			$url = "$this->_home?pt[".$this->_class_name."-checkUsername]";
			$onclick = "onclick=\"gino.ajaxRequest('post', '$url', 'username='+$('username').getProperty('value'), 'check')\"";
			$check = "<div id=\"check\" style=\"color:#ff0000;\"></div>\n";

			$gform = Loader::load('Form', array('', '', ''));
			$check_username = $gform->cinput('check_username', 'button', _("controlla"), _("Disponibilità username"), array('js'=>$onclick, "text_add"=>$check));
			$check_email = $gform->cinput('check_email', 'text', '', _("Controllo email"), array("required"=>true, "size"=>40, "maxlength"=>100, "other"=>"autocomplete=\"off\""));
			
			$removeFields = array();
			
			if($this->_username_as_email) $removeFields[] = 'username';
			if($this->_aut_pwd) $removeFields[] = 'userpwd';
			
			$addCell = array(
				'userpwd' => array(
					'name' => 'check_username', 
					'field' => $check_username
				), 
				'username' => array(
					'name' => 'check_email', 
					'field' => $check_email
				)
			);
		}
		
		$opts_form = array(
			'removeFields' => $removeFields, 
			'addCell' => $addCell, 
			// NEW
			'username_as_email' => $this->_username_as_email, 
			'user_more_info' => $this->_user_more, 
			'aut_password' => $this->_aut_pwd, 
			'aut_password_length' => $this->_aut_pwd_length, 
			'pwd_length_min' => $this->_pwd_length_min, 
			'pwd_length_max' => $this->_pwd_length_max, 
			'pwd_numeric_number' => $this->_pwd_numeric_number
		);
		
		$opts_input = array(
			'email' => array(
				'size'=>40
			), 
			'username' => array(
				'id'=>'username'
			), 
			'userpwd' => array(
				'text_add'=>$this->passwordRules($id)
			)
		);

		/*$admin_table = loader::load('AdminTable', array(
			$this
		));*/
		
		$admin_table = new AdminTable_AuthUser($this);
		

		return $admin_table->backoffice('User', $opts, $opts_form, $opts_input);
	}
	
	/**
	 * Descrizione delle regole alle quali è sottoposta la password
	 * 
	 * @param integer $id valore ID dell'utente
	 * @return string
	 */
	private function passwordRules($id=null) {
		
		$text = '';
		
		if($id || (!$id && !$this->_aut_pwd))
		{
			$text = sprintf(_("La password deve contenere un numero di caratteri da %s a %s."), $this->_pwd_length_min, $this->_pwd_length_max);
			
			if($this->_pwd_numeric_number) $text .= ' '.sprintf(_("Tra questi, %s devono essere numerici."), $this->_pwd_numeric_number);
		}
		
		return $text;
	}
	
	/**
	 * Controlla se uno username è disponibile
	 * 
	 * @return string
	 */
	public function checkUsername() {

		$username = cleanVar($_POST, 'username', 'string', '');

		if(empty($username)) {echo "<span style=\"font-weight:bold\">"._("Inserire uno username!")."</span>"; exit();}

		$check = $this->_db->getFieldFromId(User::$table, 'id', 'username', $username);
		if($check)
		{
			echo "<span style=\"font-weight:bold\">"._("Username non disponibile!")."</span>";
			exit();
		}
		else
		{
			echo "<span style=\"font-weight:bold\">"._("Username disponibile!")."</span>";
			exit();
		}
	}
	
	/**
	 * Interfaccia di sostituzione della password
	 * 
	 * @see User::savePassword()
	 * @see User::formPassword()
	 * @see passwordRules()
	 * @return string
	 * 
	 * Parametri GET (per il form): \n
	 *   - ref (integer), valore ID dell'utente
	 *   - c (integer), riporta se la password è stata correttamente aggiornata
	 * 
	 * Parametri POST (per l'action del form): \n
	 *   - id (integer), valore ID dell'utente
	 */
	public function changePassword() {
		
		// PERM ??
		
		$link_interface = $this->_home."?evt[".$this->_class_name."-changePassword]";
		
		if(isset($_POST['submit_action']))
		{
			$user_id = cleanVar($_POST, 'id', 'int', '');
			$obj_user = new User($user_id);
			
			$action_result = $obj_user->savePassword(array(
				'pwd_length_min' => $this->_pwd_length_min, 
				'pwd_length_max' => $this->_pwd_length_max, 
				'pwd_numeric_number' => $this->_pwd_numeric_number
			));
			
			$link_interface .= "&ref=$user_id";
			
			if($action_result === true) {
				
				$link_interface .= "&c=1";
				
				header("Location: "."http://".$_SERVER['HTTP_HOST'].$link_interface);
				exit();
			}
			exit(error::errorMessage($action_result, $link_interface));
		}
		
		$user_id = cleanVar($_GET, 'ref', 'int', '');
		$change = cleanVar($_GET, 'c', 'int', '');
		
		$obj_user = new User($user_id);
		
		$content = $obj_user->formPassword(array(
			'form_action'=>$link_interface, 
			'rules'=>$this->passwordRules($user_id), 
			'maxlength'=>$this->_pwd_length_max)
		);
		
		$title = _('Impostazione password');
		if($change == 1) $title .= " - "._("modifica effettuata");
		
		$dict = array(
			'title' => $title,
			'content' => $content
		);

		$view = new view();
		$view->setViewTpl('section');

		return $view->render($dict);
	}
	
	private function manageGroup() {
		
		$info = "<div class=\"backoffice-info\">";
		$info .= "<p>"._("Elenco dei gruppi del sistema.</p>");
		$info .= "</div>";
		
		
		$opts = array(
			'list_display' => array('id', 'name', 'description'),
			'list_description' => $info, 
			'add_buttons' => array(
				array('label'=>pub::icon('permission', array('scale' => 1)), 'link'=>$this->_home."?evt[".$this->_class_name."-joinGroupPermission]", 'param_id'=>'ref')
			)
		);
		
		$admin_table = loader::load('AdminTable', array(
			$this
		));

		return $admin_table->backoffice('Group', $opts);
	}
	
	private function managePermission() {
		
		$info = "<div class=\"backoffice-info\">";
		$info .= "<p>"._("Elenco dei permessi.</p>");
		$info .= "</div>";
		
		
		$opts = array(
			'list_display' => array('id', 'class', 'code', 'label', 'admin'),
			'list_description' => $info
		);
		
		$admin_table = loader::load('AdminTable', array(
			$this
		));

		return $admin_table->backoffice('Permission', $opts);
	}
	
	/**
	 * Associazione utente-permessi
	 * 
	 * Parametri GET: \n
	 *   - ref (integer), valore ID dell'utente
	 */
	public function joinUserPermission() {
		
		// PERM ??
		
		$id = cleanVar($_GET, 'ref', 'int', '');
		
		$obj_user = new User($id);
		
		$gform = loader::load('Form', array('j_userperm', 'post', false));
		
		$content = $gform->open('', false, '');
		$content .= $gform->hidden('id', $obj_user->id);
		$content .= $this->formPermission();
		
		$content .= $gform->input('submit', 'submit', _("associa"), null);
		$content .= $gform->close();
		
		$dict = array(
			'title' => _('Associazione Utente - Permessi'),
			'pre_header' => $obj_user, 
			'content' => $content
		);

		$view = new view();
		$view->setViewTpl('section');

		return $view->render($dict);
	}
	
	/**
	 * Associazione gruppo-permessi
	 * 
	 * Parametri GET: \n
	 *   - ref (integer), valore ID del gruppo
	 */
	public function joinGroupPermission() {
		
		// PERM ??
		
		$id = cleanVar($_GET, 'ref', 'int', '');
		
		$obj_group = new Group($id);
		
		$gform = loader::load('Form', array('j_groupperm', 'post', false));
		
		$content = $gform->open('', false, '');
		$content .= $gform->hidden('id', $obj_group->id);
		$content .= $this->formPermission();
		
		$content .= $gform->input('submit', 'submit', _("associa"), null);
		$content .= $gform->close();
		
		$dict = array(
			'title' => _('Associazione Gruppo - Permessi'),
			'pre_header' => $obj_group, 
			'content' => $content
		);

		$view = new view();
		$view->setViewTpl('section');

		return $view->render($dict);
	}
	
	// SPOSTARE IN class.User.php ?????
	private function formPermission($checked=array()) {
		
		$perm = Permission::getList();
		
		$content = '';
		
		if(count($perm))
		{
			$content .= "<table>";
			
			$content .= "<tr>";
			$content .= "<th>"._("Nome classe")."</th>";
			$content .= "<th>"._("Codice permesso")."</th>";
			$content .= "<th>"._("Label")."</th>";
			$content .= "<th>"._("Nome istanza")."</th>";
			$content .= "<th>"._("Label istanza")."</th>";
			$content .= "</tr>";
			
			foreach($perm AS $p)
			{
				$p_id = $p['perm_id'];
				$p_class = $p['class'];
				$p_code = $p['code'];
				$p_label = $p['label'];
				$p_inst_id = $p['instance_id'];
				$p_inst_name = $p['instance_name'];
				$p_inst_label = $p['instance_label'];
				
				$p_inst_id = (int) $p_inst_id;
				$value = $p_id.'_'.$p_inst_id;
				
				$checkbox = "<input type=\"checkbox\" name=\"perm[]\" value=\"$value\" />";
				
				$content .= "<tr>";
				$content .= "<td>$checkbox $p_class</td>";
				$content .= "<td>$p_code</td>";
				$content .= "<td>$p_label</td>";
				$content .= "<td>$p_inst_name</td>";
				$content .= "<td>$p_inst_label</td>";
				$content .= "</tr>";
			}
			$content .= "</table>";
		}
		
		return $content;
	}
	
	// SPOSTARE IN class.Group.php ?????
	private function formGroup($checked=array()) {
		
		$group = Group::getList();
		
		$content = '';
		
		if(count($group))
		{
			$content .= "<table>";
			
			$content .= "<tr>";
			$content .= "<th>"._("Nome gruppo")."</th>";
			$content .= "<th>"._("Descrizione")."</th>";
			$content .= "</tr>";
			
			foreach($group AS $g)
			{
				$g_id = $g['id'];
				$g_name = $g['name'];
				$g_description = $g['description'];
				
				$checkbox = "<input type=\"checkbox\" name=\"group[]\" value=\"$g_id\" />";
				
				$content .= "<tr>";
				$content .= "<td>$checkbox $g_name</td>";
				$content .= "<td>$g_description</td>";
				$content .= "</tr>";
			}
			$content .= "</table>";
		}
		
		return $content;
	}
	
	/**
	 * Associazione utente-gruppi
	 * 
	 * Parametri GET: \n
	 *   - ref (integer), valore ID dell'utente
	 */
	public function joinUserGroup() {
		
		// PERM ??
		
		$id = cleanVar($_GET, 'ref', 'int', '');
		
		$obj_user = new User($id);
		
		$gform = Loader::load('Form', array('', '', ''));
		
		$gform = loader::load('Form', array('j_usergroup', 'post', false));
		
		$content = $gform->open($this->_home.'?evt['.$this->_class_name.'-actionJoinUserGroup]', false, '');
		$content .= $gform->hidden('id', $obj_user->id);
		$content .= $this->formGroup();
		
		$content .= $gform->input('submit', 'submit', _("associa"), null);
		$content .= $gform->close();
		
		$dict = array(
			'title' => _('Associazione Utenti - Gruppi'), 
			'pre_header' => $obj_user, 
			'content' => $content
		);

		$view = new view();
		$view->setViewTpl('section');

		return $view->render($dict);
	}
	
	/**
	 * Box di login
	 * 
	 * @see access::AccessForm()
	 * @see account::linkRegistration()
	 * @param boolean $bool mostra il collegamento alla registrazione autonoma di un utente
	 * @param string $classname nome della classe che fornisce i metodi per le interfacce
	 * @return string
	 */
	public function login(){	// $bool=false, $classname='user'

		$GINO = "<div class=\"auth\">\n";
		$GINO .= "<div class=\"auth_title\">"._("login:")."</div>";
		$GINO .= "<div class=\"auth_content\">"; 
		$GINO .= $this->_access->AccessForm();
		
		//$registration = new account($classname);
		//$GINO .= $registration->linkRegistration($bool);
		
		$GINO .= "</div>\n";
		$GINO .= "</div>\n";
		
		return $GINO;
	}
	
	/**
	 * Box di login in tabella
	 * 
	 * @param boolean $bool mostra il collegamento alla registrazione autonoma di un utente
	 * @param string $classname nome della classe che fornisce i metodi per le interfacce
	 * @return string
	 */
	public function tableLogin($bool=false, $classname='index'){

		$GINO = "<form action=\"\" method=\"post\" id=\"formauth\" name=\"formauth\">\n";
		$GINO .= "<input type=\"hidden\" id=\"action\" name=\"action\" value=\"auth\" />\n";
		$GINO .= "<table>";
		$GINO .= "<tr class=\"authTitle\">";
		$GINO .= "<td></td><td>"._("Area riservata")."</td>";
		$GINO .= "</tr>";
		$GINO .= "<tr class=\"authForm\">";
		$GINO .= "<td class=\"afLabel\">".($this->_u_username_email?"email":"user")."</td>";
		$GINO .= "<td class=\"afField\"><input type=\"text\" id=\"user\" name=\"user\" size=\"25\" maxlength=\"50\" class=\"auth\" /></td>";
		$GINO .= "</tr>";
		$GINO .= "<tr class=\"authForm\">";
		$GINO .= "<td class=\"afLabel\">"._("password")."</td>";
		$GINO .= "<td class=\"afField\"><input type=\"password\" name=\"pwd\" size=\"25\" maxlength=\"15\" class=\"auth\" /></td>";
		$GINO .= "</tr>";
		$GINO .= "<tr class=\"authForm\">";
		$GINO .= "<td class=\"afLabel\"></td>";
		$GINO .= "<td class=\"afField\"><input type=\"submit\" class=\"generic\" name=\"login_user\" value=\""._("login")."\" /></td>";
		$GINO .= "</tr>";
		if($this->_u_aut_registration OR $bool) {
			$class = $classname=='index' ? 'user':$classname;
			$GINO .= "<tr class=\"authRegTitle\">";
			$GINO .= "<td></td><td>"._("Registrazione")."</td>";
			$GINO .= "</tr>";
			$GINO .= "<tr class=\"authRegForm\">";
			$GINO .= "<td class=\"arfLabel\"></td>";
			$GINO .= "<td class=\"arfField\"><input onclick=\"location.href='".$this->_home."?evt[$class-registration]'\" type=\"button\" class=\"generic\" name=\"login_user\" value=\""._("sign up")."\" /></td>";
			$GINO .= "</tr>";
		}
		$GINO .= "</table>";
		$GINO .= "</form>";
		
		return $GINO;
	}
}
