<?php
/*================================================================================
Gino - a generic CMS framework
Copyright (C) 2005  Otto Srl - written by Marco Guidotti

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
================================================================================*/

/**
 * Funzionamento (classe che istanzia)
 * 
 * Proprietà:
	private $_account;
	public $_service;		// se si intende definire l'attivazione del 'servizio' al completamento della registrazione (boolean)
	public $_service_group;	// gruppo che accede al servizio
	private $_properties;
 * 
 * Nel costruttore:
	$this->_account = new account($this, $this->_className);
	$this->_properties = array(
		'a'=>'',
		'b'=>'',
		'c'=>''
	);
 *
 * Aggiungere il metodo:
 * 
 * 
	public function __get($property_name){
		
		if(isset($this->_properties[$property_name]))
		{
			return $this->_properties[$property_name];
		}
		else {
			return(null);
		}
	}
 * 
 */

class account extends pub {
	
	private $_call_obj, $_call_name;
	
	private $_app_aut_trained, $_app_email_contact, $_app_email_send;
	
	private $_user_dir, $_user_www;
	private $_extension_media, $_lng_tbl_nation;

	function __construct($object='', $class=''){
		
		parent::__construct();

		if(is_object($object))
		{
			$this->_call_obj = $object;
			$this->setOptionApp();
		}
		
		$this->_call_name = $class;
		
		$this->_user_dir = $this->_content_dir.$this->_os.'user'.$this->_os;
		$this->_user_www = $this->_content_www.'/user';
		
		$this->_extension_media = array('jpg', 'png');
		$this->_lng_tbl_nation = $this->_lng_nav;

	}
	
	private function setOptionApp(){
		
		//// NON PRESENTE
		$this->_app_aut_trained = $this->_call_obj->a;		// Abilitazione automatica alla registrazione di un nuovo utente (oppure tramite conferma da email)
		
		//// NON PRESENTE
		$this->_app_email_contact = $this->_call_obj->b;	// email per contatto
		$this->_app_email_send = $this->_call_obj->c;		// Invio email come promemoria di attivazione del servizio
	}
	
	private function accessAutRegistration(){
		
		if(!$this->_u_aut_registration)
		EvtHandler::HttpCall($this->_home, '', '');
	}
	
	// Metodo di accesso
	private function accessAccount($user='', $action=''){
		
		if(empty($action)) $action = $this->_act_insert;
		
		if($action == $this->_act_insert)
		{
			$this->accessAutRegistration();
		}
		elseif($action == $this->_act_modify)
		{
			$this->_access->accessVerify();
			
			if($this->_call_name != 'user' AND $user != $this->_session_user)
			EvtHandler::HttpCall($this->_home, '', '');
		}
	}
	
	/**
	 * Login Form
	 *
	 * @param string	$classname
	 * @return string
	 */
	public function login($classname=null) {
	
		$GINO = "<div style=\"margin-left:10px;width: 200px;\">";
		
		$GINO .= "<p><a name=\"comment\" class=\"subtitle\" style=\"text-decoration:none; cursor:default;\">"._("Per procedere è necessario autenticarsi.")."</a></p>";
		
		$func = new sysfunc();
		$GINO .= $func->Autenticazione($this->_u_aut_registration, $classname);
		
		$GINO .= "</div>\n";
		
		return $GINO;
	}
	
	public function linkRegistration($bool=false){

		$GINO = '';
		
		if($this->_u_aut_registration OR $bool)
		{
			if($this->_call_name == 'index') $class = 'user'; else $class = $this->_call_name;
			
			$GINO .= "<div class=\"auth_reg_title\"><a href=\"".$this->_home."?evt[$class-registration]\">"._("registrati")."</a>";
			$GINO .= "</div>\n";
		}
		return $GINO;
	}
	
	/*
		Gestione utenti
	*/
	
	public function formAccount($action='') {

		$this->accessAccount('', $action);
		
		if(empty($action)) $action = $this->_act_insert;
		$user = $this->_session_user;
		
		if($action == $this->_act_insert) $action_method = 'actionRegistration';
		elseif($action == $this->_act_modify) $action_method = 'actionPersonal';
		

		$GINO = '';
		if($action == $this->_act_insert AND !$this->_u_aut_validation)
		{
			$GINO .= "<p>";
			$GINO .= _("Compilare il seguente form di registrazione, in seguito verrà inviata una mail di conferma in cui si chiederà di confermare la registrazione.");
			$GINO .= "</p>";
		}
		
		$gform = new Form('gform', 'post', true);
		$gform->load('dataform');
		
		if($action == $this->_act_modify)
		{
			$query = "SELECT firstname, lastname, company, phone, fax, email, username, address, cap, city, nation, text, photo, date FROM ".$this->_tbl_user." WHERE user_id='$user'";
			$a = $this->_db->selectquery($query);
			if(sizeof($a) > 0)
			{
				foreach($a AS $b)
				{
					$firstname = htmlInput($b['firstname']);
					$lastname = htmlInput($b['lastname']);
					$company = htmlInput($b['company']);
					$phone = htmlInput($b['phone']);
					$fax = htmlInput($b['fax']);
					$email = htmlInput($b['email']);
					$address = htmlInput($b['address']);
					$cap = htmlInput($b['cap']);
					$city = htmlInput($b['city']);
					$nation = htmlInput($b['nation']);
					$text = htmlInput($b['text']);
					$photo = htmlInput($b['photo']);
					
					$username = htmlChars($b['username']);
					$date = htmlChars($b['date']);
				}
			}
			
			$registered = (dbDatetimeToDate($date, '/'))? " "._("registrato il '").dbDatetimeToDate($date, '/')."'":"";
			$title = _("Impostazioni ")."'".$username."'".$registered;
			
			$submit = _("modifica");
		}
		else
		{
			$firstname = $gform->retvar('firstname', '');
			$lastname = $gform->retvar('lastname', '');
			$company = $gform->retvar('company', '');
			$phone = $gform->retvar('phone', '');
			$fax = $gform->retvar('fax', '');
			$email = $gform->retvar('email', '');
			$address = $gform->retvar('address', '');
			$cap = $gform->retvar('cap', '');
			$city = $gform->retvar('city', '');
			$nation = $gform->retvar('nation', '');
			$text = $gform->retvar('text', '');
			$photo = '';
			$username = $gform->retvar('username', '');
			
			$title = _("Registrazione");
			$submit = _("registrati");
		}

		// Campi obbligatori
		if($action == $this->_act_modify)
		{
			if($this->_u_username_email) $required = "firstname,lastname";
			else $required = "firstname,lastname,email";
		}
		else
		{
			if($this->_u_username_email)
			{
				if($this->_u_pwd_automatic) $required = "firstname,lastname,email,email2,terms";
				else $required = "firstname,lastname,email,email2,pwd,pwd2,terms";
			}
			else
			{
				if($this->_u_pwd_automatic) $required = "username,firstname,lastname,email,terms";
				else $required = "username,firstname,lastname,email,pwd,pwd2,terms";
			}
		}
		// end
		
		$htmlsection = new htmlSection(array('class'=>'public', 'headerTag'=>'header', 'headerLabel'=>$title));

		// Text Character Password
		$pwd_min = $this->_u_pwd_length_min;
		$pwd_max = $this->_u_pwd_length_max;
		$pwd_number = $this->_u_pwd_number;
		
		if(!$this->_u_pwd_automatic)
		{
			if(!empty($pwd_number))
			{
				$text_number = ", "._("di cui almeno $pwd_number numerici").'.';
			}
			else $text_number = '.';
			
			$pwd_txt = _("Deve contenere minimo $pwd_min caratteri, massimo $pwd_max").$text_number;
		}
		// End
		
		if($action == $this->_act_modify)
			$GINO .= $this->formPwd($user, 'h', 'personal', $action, $pwd_txt, $pwd_max);

		$GINO .= $gform->form($this->_home."?evt[".$this->_call_name."-$action_method]", true, $required);
		$GINO .= $gform->hidden('user', $user);
		$GINO .= $gform->hidden('action', $action);
		
		if($action == $this->_act_insert)
		{
			if(!$this->_u_pwd_automatic)
			{
				$GINO .= $gform->cinput('pwd', 'password', '', array(_("Password"), $pwd_txt), array("required"=>true, "size"=>20, "maxlength"=>$pwd_max));
				$GINO .= $gform->cinput('pwd2', 'password', '', _("Verifica password"), array("required"=>true, "size"=>20, "maxlength"=>$pwd_max, "other"=>"autocomplete=\"off\""));
			}
			else
			{
				$GINO .= $gform->noinput(_("Password"), _("La password viene generata in automatico"));
			}
			
			if(!$this->_u_username_email)
			{
				$url = $this->_home."?pt[user-actionCheckUsername]";
				$onclick = "onclick=\"ajaxRequest('post', '$url', 'username='+$('username').getProperty('value'), 'check')\"";
				$check = "<div id=\"check\" style=\"color:#ff0000;\"></div>\n";

				$GINO .= $gform->cinput('username', 'text', $username, _("Username"), array("id"=>"username", "required"=>true, "size"=>40, "maxlength"=>"50"));
				$GINO .= $gform->cinput('check_username', 'button', _("controlla"), _("Disponibilità username"), array("js"=>$onclick, "text_add"=>$check, "classField"=>"generic"));
			}
		}
		
		$GINO .= $gform->cinput('company', 'text', $company, _("Ragione sociale"), array("size"=>40, "maxlength"=>"100"));
		$GINO .= $gform->cinput('firstname', 'text', $firstname, _("Nome"), array("required"=>true, "size"=>40, "maxlength"=>"50"));
		$GINO .= $gform->cinput('lastname', 'text', $lastname, _("Cognome"), array("required"=>true, "size"=>40, "maxlength"=>"50"));
		
		if($this->_u_username_email)
		{
			if($action == $this->_act_insert)
			{
				$GINO .= $gform->cinput('email', 'text', $email, _("Email"), array("required"=>true, "size"=>40, "maxlength"=>"100"));
				$GINO .= $gform->cinput('email2', 'text', '', _("Controllo email"), array("required"=>true, "size"=>40, "maxlength"=>"100", "other"=>"autocomplete=\"off\""));
			}
			else 
			{
				$GINO .= $gform->cinput('emailview', 'text', $email, _("Email"), array("required"=>true, "size"=>40, "maxlength"=>"100", "readonly"=>true));
			}
		}
		else
		{
			$GINO .= $gform->cinput('email', 'text', $email, _("Email"), array("required"=>true, "size"=>40, "maxlength"=>"100"));
		}
		
		$GINO .= $gform->cinput('address', 'text', $address, _("Indirizzo"), array("size"=>40, "maxlength"=>"200"));
		$GINO .= $gform->cinput('cap', 'text', $cap, _("Cap"), array("size"=>5, "maxlength"=>"5"));
		$GINO .= $gform->cinput('city', 'text', $city, _("Città"), array("size"=>40, "maxlength"=>"50"));

		$query = "SELECT id, ".$this->_lng_tbl_nation." FROM ".$this->_tbl_nation." ORDER BY ".$this->_lng_tbl_nation." ASC";
		$GINO .= $gform->cselect('nation', $nation, $query, _("Nazione"));

		$GINO .= $gform->cinput('phone', 'text', $phone, _("Telefono"), array("size"=>30, "maxlength"=>"30"));
		$GINO .= $gform->cinput('fax', 'text', $fax, _("Fax"), array("size"=>30, "maxlength"=>"30"));

		if($this->_u_media_info)
		{
			$GINO .= $gform->hidden('old_file', $photo);

			$GINO .= $gform->ctextarea('text', $text, _("Informazioni generali"), array("cols"=>"55", "rows"=>7));
			$GINO .= $gform->cfile('photo', $photo, _("Fotografia"), array("extensions"=>$this->_extension_media, "del_check"=>true));
		}
		
		if($action == $this->_act_insert)
		{
			$GINO .= $gform->captcha();
			
			$GINO .= $gform->cell($this->termCondition(), array("other"=>"style=\"border-width:1px;padding:5px;\""));

			$GINO .= $gform->ccheckbox('terms', false, 'ok', _("Termini e condizioni d'uso"), array("required"=>true, "text_add"=>"&#160;"._("Ho letto ed accetto i termini e le condizioni del servizio"), "classField"=>"centered"));
			//$GINO .= $gform->multipleCheckbox('terms', array(), array('ok'=>_("accetta")), _("Termini e condizioni d'uso"), 'req', '', '', '', '', '', '', 'left', 'none');
		}

		$GINO .= $gform->cinput('submit_action', 'submit', $submit, '', array("classField"=>"submit"));
		$GINO .= $gform->cform();
		
		$htmlsection->content = $GINO;
		
		return $htmlsection->render();
	}
	
	/**
	 * Action Form di Registrazione
	 *
	 * @param string $method		metodo sul quale si viene reindirizzati in caso di risposta positiva
	 * @param string $params		parametri da inviare in caso di risposta positiva
	 */
	public function actionAccount($method='', $params='', $link_error=null){

		$user = cleanVar($_POST, 'user', 'int', '');
		$action = cleanVar($_POST, 'action', 'string', '');
		
		$this->accessAccount($user, $action);
		
		$gform = new Form('gform', 'post', true);
		$gform->save('dataform');
		$req_error = $gform->arequired();

		$username = cleanVar($_POST, 'username', 'string', '');
		$pwd = cleanVar($_POST, 'pwd', 'string', '');
		$pwd2 = cleanVar($_POST, 'pwd2', 'string', '');
		$company = cleanVar($_POST, 'company', 'string', '');
		$firstname = cleanVar($_POST, 'firstname', 'string', '');
		$lastname = cleanVar($_POST, 'lastname', 'string', '');
		$email = cleanVar($_POST, 'email', 'string', '');
		$email2 = cleanVar($_POST, 'email2', 'string', '');

		$address = cleanVar($_POST, 'address', 'string', '');
		$cap = cleanVar($_POST, 'cap', 'int', '');
		$city = cleanVar($_POST, 'city', 'string', '');
		$nation = cleanVar($_POST, 'nation', 'int', '');
		$phone = cleanVar($_POST, 'phone', 'string', '');
		$fax = cleanVar($_POST, 'fax', 'string', '');

		$captcha = cleanVar($_POST, 'captcha_input', 'string', '');
		$terms = cleanVar($_POST, 'terms', 'string', '');
		$date = date("Y-m-d H:i:s");
		
		if(!empty($method))
			$return_method = $method;
		else {
			if($action == $this->_act_insert) $return_method = 'registration';
			elseif($action == $this->_act_modify) $return_method = 'personal';
		}
		
		$link = $params;
		$redirect = $this->_call_name.'-'.$return_method;
		$redirect_true = $this->_call_name.'-'.$return_method;
		if(!$link_error) $link_error = $this->_home."?evt[$redirect]".($link?"&$link":"");

		// Media
		$text = cleanVar($_POST, 'text', 'string', '');
		$file_tmp = $_FILES['photo']['tmp_name'];
		$old_file = cleanVar($_POST, 'old_file', 'string', '');
		$check_del_file = cleanVar($_POST, 'check_del_file', 'string', '');

		$directory = $this->_user_dir;

		/*
		controllo degli inserimenti
		*/

		if($req_error > 0) 
			exit(error::errorMessage(array('error'=>1), $link_error));

		if($action == $this->_act_insert)
		{
			if($terms != 'ok')
				exit(error::errorMessage(array('error'=>26), $link_error));
			else $privacy = 'yes';
			
			// Password
			if(!$this->_u_pwd_automatic)
			{
				if(!$this->verifyPassword($pwd))
					exit(error::errorMessage(array('error'=>19), $link_error));
				
				if($pwd != $pwd2)
					exit(error::errorMessage(array('error'=>6), $link_error));
			}
			else
			{
				$pwd = $this->generatePwd();
			}
			$password = $this->cryptMethod($pwd, $this->variable('password_crypt'));
			// End
			
			// Captcha
			if(!$gform->checkCaptcha())
				exit(error::errorMessage(array('error'=>24), $link_error));
		}

		// Email
		if(!empty($email) AND !email_control($email))
			exit(error::errorMessage(array('error'=>7), $link_error));
		
		if($this->_u_username_email)
		{
			if($action == $this->_act_insert)
			{
				if($email != $email2)
					exit(error::errorMessage(array('error'=>25), $link_error));
				
				$query = "SELECT email FROM ".$this->_tbl_user."";
				if($this->valueExist($query, 'email', $email))
					exit(error::errorMessage(array('error'=>20), $link_error));
			}
		}
		// End
		
		// Inserimento utente
		if($action == $this->_act_insert)
		{
			$role = $this->_access_user;
			if($this->_u_username_email) $username = $email;
			if($this->_u_aut_validation) $valid = 'yes'; else $valid = 'no';
			$publication = 'no';

			$query = "INSERT INTO ".$this->_tbl_user." (
			firstname, lastname, company, phone, fax, email, username, userpwd,
			address, cap, city, nation, text, pub, role, date, valid, privacy
			) VALUES (
			'$firstname', '$lastname', '$company', '$phone', '$fax', '$email', '$username', '$password',
			'$address', $cap, '$city', $nation, '$text', '$publication', $role, '$date', '$valid', '$privacy'
			)";
			$result = $this->_db->actionquery($query);

			$rid = $this->_db->getlastid($this->_tbl_user);

			if($result)
			{
				$user_id = $this->_db->getlastid($this->_tbl_user);
				
				if($this->_u_more_info)
				{
					$user = new user();
					
					$query2 = "INSERT INTO ".$this->_tbl_user_add." VALUES ($user_id, '".$user->_other_field1."', '".$user->_other_field2."', '".$user->_other_field3."')";
					$result2 = $this->_db->actionquery($query2);
				}
				
				$this->service($user_id);
				
				// Invio mail
				$session = session_id();
				$query2 = "INSERT INTO ".$this->_tbl_user_reg." VALUES (null, $user_id, '$session')";
				$result2 = $this->_db->actionquery($query2);
				$reg_id = $this->_db->getlastid($this->_tbl_user_reg);
				$reg_link = "index.php?evt[user-confirmRegistration]&sid=".$session."&id=".$reg_id;
					
				if($this->_u_personalized_email)
				{
					$this->mailNewRegistration($email, $firstname, $lastname, $username, $pwd, $reg_link, $this->_u_aut_validation?2:1);
				}
				else
				{
					$site = $this->_url_path;
					$subject = _("Nuova Registrazione").' - '.$this->variable('head_title');
					$object = _("Gentile Cliente,")."\r\n";
					$object .= _("Grazie per essersi registrato a $site.");
					if(!$this->_u_aut_validation)
						$object .= _("Per completare la Sua registrazione con successo deve confermare la Sua richiesta selezionando il link")." <a href=\"$reg_link\">$reg_link</a>\r\n\r\n";
					else
						$object .= _("Effetua il login all'indirizzo seguente ")." <a href=\"$this->_home\">Home page</a>\r\n\r\n";
					$object .= _("Cordiali saluti")."\r\n";
					$object .= _("Lo Staff");
						
					$this->emailSend($email, $subject, $object);
				}
			}
		}
		elseif($action == $this->_act_modify AND !empty($user))
		{
			$rid = $user;
			if(!empty($email))  $email_field = "email='$email',"; else $email_field = '';
			
			$query = "UPDATE ".$this->_tbl_user." SET
			firstname='$firstname', lastname='$lastname', company='$company',
			phone='$phone', fax='$fax', ".$email_field."
			address='$address', cap=$cap, city='$city', nation=$nation,
			text='$text'
			WHERE user_id='$user'";
			$result = $this->_db->actionquery($query);
		}
		
		$gform->manageFile('photo', $old_file, true, $this->_extension_media, $directory, $link_error, $this->_tbl_user, 'photo', 'user_id', $rid,
			array("prefix_file"=>user::$prefix_img, "prefix_thumb"=>user::$prefix_thumb, "width"=>user::$width_img, "thumb_width"=>user::$width_thumb));
		
		EvtHandler::HttpCall($this->_home, $redirect_true, $link);
	}
	
	private function mailNewRegistration($to, $name, $surname, $user, $password, $link, $email_id){

		$array = array($name, $surname, $user, $password, $link);
		$data = implode(';;', $array);
		
		$email = new email('user', null);
		$email->schemaSendEmail($to, $email_id, $data);
	}
	
	/**
	 * Change User Password
	 *
	 * @param integer $user		user id
	 * @param string $format	h: horizontal | v: vertical
	 * @param string $function	function name (return redirect)
	 * @param string $action
	 * @return string
	 */
	private function formPwd($user, $format, $function, $action, $pwd_txt, $pwd_max){

		$buffer = "<div>\n";
		
		$gform = new Form('pwdform', 'post', true);
		$gform->load('pwdform');

		$required = 'pwd,pwd2';
		$buffer .= $gform->form($this->_home."?pt[".$this->_className."-actionPwd]", '', $required);
		$buffer .= $gform->hidden('user', $user);
		$buffer .= $gform->hidden('action', $action);
		$buffer .= $gform->hidden('class', $this->_call_name);
		$buffer .= $gform->hidden('function', $function);
		$buffer .= $gform->cinput('pwd', 'password', '', array(_("Password"), $pwd_txt), array("required"=>true, "size"=>40, "maxlength"=>$pwd_max));
		$buffer .= $gform->cinput('pwd2', 'password', '', _("Verifica password"), array("required"=>true, "size"=>40, "maxlength"=>$pwd_max, "other"=>"autocomplete=\"off\""));
		$buffer .= $gform->cinput('submit_action', 'submit', _("modifica"), '', array("classField"=>"submit"));

		$buffer .= $gform->cform();
		$buffer .= "</div>\n";
		$buffer .= "<p class=\"line\"></p>";

		return $buffer;
	}

	public function actionPwd(){

		$user = cleanVar($_POST, 'user', 'int', '');
		$action = cleanVar($_POST, 'action', 'string', '');
		
		$this->accessAccount($user, $action);
		
		$class = cleanVar($_POST, 'class', 'string', '');
		$function = cleanVar($_POST, 'function', 'string', '');
		$pwd = cleanVar($_POST, 'pwd', 'string', '');
		$pwd2 = cleanVar($_POST, 'pwd2', 'string', '');
		
		$this->_gform = new GinoForm('gform','post', true);
		$this->_gform->save('pwdform');
		$req_error = $this->_gform->arequired();

		if($action == $this->_act_modify AND $function != 'personal')
		{
			$link = "user=$user";
			$link_error = "user=$user&";
		}
		else
		{
			$link = '';
			$link_error = '';
		}

		$redirect = $class.'-'.$function;

		if($req_error > 0)
		EvtHandler::HttpCall($this->_home, $redirect, $link_error."error=01");

		if(empty($user))
		EvtHandler::HttpCall($this->_home, $redirect, $link_error."error=09");

		$user_role = $this->_db->getFieldFromId($this->_tbl_user, 'role', 'user_id', $user);
		if($this->_session_role >= $user_role AND $user != $this->_session_user)
		EvtHandler::HttpCall($this->_home, $redirect, $link_error.'error=31');

		if(!$this->verifyPassword($pwd))
		EvtHandler::HttpCall($this->_home, $redirect, $link_error.'error=19');

		if($pwd != $pwd2)
		EvtHandler::HttpCall($this->_home, $redirect, $link_error.'error=06');

		$password = $this->cryptMethod($pwd, $this->_crypt);

		$query = "UPDATE ".$this->_tbl_user." SET userpwd='$password' WHERE user_id='$user'";
		$this->_db->actionquery($query);

		EvtHandler::HttpCall($this->_home, $redirect, $link);
	}
	
	/**
	 * Attivazione del servizio al completamento della registrazione
	 *
	 * @param integer $user_id
	 */
	private function service($user_id){
		
		$service = $this->_call_name;
		$class = new $service;
		if($class->_service)
		{
			if(!empty($class->_service_group))
			{
				$group_id = $class->_service_group;
				$tblname = $this->tblname($this->_call_name);
				
				if(sizeof($tblname) > 0)
				{
					$query = "INSERT INTO ".$tblname[0]." (group_id, user_id) VALUES ('$group_id', $user_id)";
					$result = $this->_db->selectquery($query);
				}
			}
		}
	}
	
	private function termCondition($check=''){

		//$GINO = "<p class=\"bold\">"._("Termini e condizioni del servizio")."</p>\n";
		
		/*
		$GINO .= "<p>"._("Proprietà dei dati")."</p>";
		
		$GINO .= "<p>"._("È assolutamente vietata la riproduzione, anche parziale, del materiale pubblicato. I loghi, la grafica, i suoni, le immagini e i testi di questo sito non possono essere copiati o ritrasmessi salvo espressa autorizzazione. L'utilizzo per qualsiasi fine non autorizzato è espressamente proibito dalla legge e comporta responsabilità sia civili sia penali.")."</p>";

		$GINO .= "<p>"._("Privacy")."</p>";
		*/

		$GINO = "<p>"._("Informativa ai sensi dell'ART. 13 del D. LGS n°196/2003 (codice in materia di protezione dei dati personali), i dati personali saranno trattati solo dall'Azienda o dai nostri partner per l'invio di materiale informativo/commerciale sui prodotti e i servizi offerti e non saranno in nessun caso comunicati o diffusi a terzi. Gli interessati potranno richiedere in ogni momento la cancellazione dei propri dati inviandoci un fax o una email.")."</p>";

		/*
		$GINO .= "<p>"._("Come aggiornare le vostre informazioni e preferenze personali")."</p>";
		
		$GINO .= "<p>"._("Potete avvisarci di eventuali cambi di nome, recapito postale, titolo, numero di telefono, indirizzo di posta elettronica o preferenze in merito. Se decidete di aggiornare le vostre informazioni o preferenze, o se preferite non ricevere più comunicati da noi o dai nostri partner, non esitate a comunicarcelo. Inviate gli aggiornamenti desiderati via fax.")."</p>";

		$GINO .= "<p>"._("Link")."</p>";
		
		$GINO .= "<p>"._("In merito agli eventuali link ad altri siti presenti nelle nostre pagine, l'Azienda non si assume alcuna responsabilità per il contenuto o le direttive sulla riservatezza ed i principi di tali siti web. Vi invitiamo pertanto a leggere le dichiarazioni sulla riservatezza di questi siti, in quanto potrebbero differire dalle nostre.")."</p>\n";
		*/
		
		$GINO .= $check;

		return $GINO;
	}
	
	public function confirmRegistration(){
		
		$this->accessAutRegistration();
		
		$session = cleanVar($_GET, 'sid', 'string', '');
		$id = cleanVar($_GET, 'id', 'int', '');

		if(!empty($session) && !empty($id))
		{
			$query = "SELECT user_id FROM ".$this->_tbl_user_reg." WHERE id='$id' AND session='$session'";
			$a = $this->_db->selectquery($query);
			if(sizeof($a) > 0)
			{
				foreach($a AS $b)
				{
					$user_id = $b['user_id'];
				}

				$query2 = "UPDATE ".$this->_tbl_user." SET valid='yes' WHERE user_id='".$this->_db->getFieldFromId($this->_tbl_user_reg, 'user_id', 'id', $id)."'";
				$result2 = $this->_db->actionquery($query2);

				$query3 = "DELETE FROM ".$this->_tbl_user_reg." WHERE id='$id'";
				$result3 = $this->_db->actionquery($query3);

				return true;
			}
		}
		
		return false;
	}
	
	/*
		Attivazione del servizio
	*/
	
	public function activation(){
		
		$GINO = '';
		
		$GINO .= "<p>"._("Attiva il servizio sul tuo account.")."</p>";
		
		$this->_gform = new GinoForm('gform', 'post', true);
		
		$GINO .= $this->_gform->form('', '', '');
		
		$url = "index.php?pt[".$this->_className."-actionActivation]";
		$data = "'active='+$('active').getProperty('value')+'&call=".$this->_call_name."'";
		$onclick = "onclick=\"sendPost('$url', $data, 'check')\"";
		
		$GINO .= $this->_gform->input('active', _("Attiva"), 'button', '', '', $onclick);
		
		$GINO .= "<div id=\"check\"></div>";
		
		$GINO .= $this->_gform->cform();
		
		return $GINO;
	}
	
	public function actionActivation() {

		$active = cleanVar($_POST, 'active', 'string', '');
		$classname = cleanVar($_POST, 'call', 'string', '');
		
		if(!$this->_session_user) exit();

		if(empty($active)) exit();
		
		$tblname = $this->tblname($classname);
		
		if(sizeof($tblname) > 0)
		{
			$group_id = $this->_access->serviceGroup($classname);
			
			if($group_id > 0)
			{
				if(!$this->_access->service($classname, $group_id))
				{
					$query = "INSERT INTO ".$tblname[0]." (group_id, user_id)
					VALUES ('$group_id', ".$this->_session_user.")";
					$result = $this->_db->actionquery($query);
					
					if($result)
					{
						$email = $this->_db->getFieldFromId($this->_tbl_user, 'email', 'user_id', $this->_session_user);
						
						if($this->_app_email_send AND !empty($email))
						{
							$subject = $this->variable('head_title').' - '._("Attivazione del servizio");
							$object = _("Gentile Cliente,")."\r\n";
							$object .= _("Grazie per essersi registrato al servizio.")."\r\n\r\n";
							$object .= _("Cordiali saluti")."\r\n";
							$object .= _("Lo Staff");
							
							$this->emailSend($email, $subject, $object);
						}
					}
				}
			}
		}
		
		$url = $this->urlRedirect();
		
		if($result) $link = $url[0]; else $link = $url[1];
		
		echo "
		<script type=\"text/javascript\">
		location.href = \"$link\";
		</script>
		";
		exit();
	}
	
	// End
	
	public function generatePwd(){

		//set alphabet arrays
		$char_alpha_lower = array("a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k", "l", "m", "n", "o", "p", "q", "r", "s", "t", "u", "v", "w", "x", "y", "z");
		$char_alpha_upper = array("A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R", "S", "T", "U", "V", "W", "X", "Y", "Z");
		$char_numeric  = array('1', '2', '3', '4', '5', '6', '7', '8', '9', '0');
		$char_special = array('_', '!', '?', '@');
		// end

		$alpha_pwd = array_merge($char_alpha_lower, $char_alpha_upper);
		$other_pwd = array_merge($char_numeric, $char_special);
		$name_pwd = array('alpha_pwd', 'other_pwd');

		$password = '';

		for($i = 0; $i < $this->_app_length_pwd; $i++)
		{
			// scelta random tra $alpha_pwd e $other_pwd (50%)
			$key_choice = mt_rand(0, sizeof($name_pwd)-1);
			$choice = $name_pwd[$key_choice];
			$choice_array = $$choice;

			$key_rand = mt_rand(0, sizeof($choice_array)-1);
			$char = $choice_array[$key_rand];

			$password .= $char;
		}

		return $password;
	}
	
	public function verifyPassword($pwd){
		
		$pwd_min = $this->_u_pwd_length_min;
		$pwd_max = $this->_u_pwd_length_max;
		$pwd_number = $this->_u_pwd_number;
		
		$regex = '';
		$base = "[0-9]{1}.*";
		if($pwd_number > 0)
		{
			$regex .= '/^.*';
			
			for($i=0; $i<$pwd_number; $i++)
			{
				$regex .= $base;
			}
			$regex .= '$/';
			
			$condition = true;
		}
		else $condition = false;
		
		if($condition)
		{
			if((strlen($pwd) < $pwd_min OR strlen($pwd) > $pwd_max) || !preg_match($regex, $pwd))
			return false;
		}
		else
		{
			if(strlen($pwd) < $pwd_min OR strlen($pwd) > $pwd_max)
			return false;
		}
		
		return true;
	}
}
?>
