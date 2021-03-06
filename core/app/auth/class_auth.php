<?php
/**
 * @file class_auth.php
 * @brief Contiene la definizione ed implementazione della classe Gino.App.Auth.auth
 *
 * @copyright 2013-2019 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */

/**
 * @namespace Gino.App.Auth
 * @description Namespace dell'applicazione Auth, per la gestione di utenti, gruppi e permessi
 */
namespace Gino\App\Auth;

use \Gino\Loader;
use \Gino\Document;
use \Gino\View;
use \Gino\Error;
use \Gino\Http\Response;
use \Gino\Http\Redirect;

require_once('class.User.php');
require_once('class.Group.php');
require_once('class.Permission.php');
require_once('class.RegistrationProfile.php');

require_once(CLASSES_DIR.OS.'class.ModelForm.php');
require_once('class.ModelFormUser.php');

/**
 * @brief Classe di tipo Gino.Controller per la gestione degli utenti, gruppi e permessi
 *
 * @copyright 2013-2019 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 * 
 * #Descrizione
 * I permessi delle applicazioni sono definiti nella tabella @a auth_permission. Il campo @a admin indica se il permesso necessita dell'accesso all'area amministrativa. \n
 * Ogni utente può essere associato a un permesso definito nella tabella @a auth_permission, e tale associazione viene registrata nella tabella @a auth_user_perm. \n
 * La tabella @a auth_user_perm registra il valore id dell'utente, del permesso e dell'istanza relativa all'applicazione del permesso. \n
 * Questo implica che nell'interfaccia di associazione utente/permessi occorre mostrare i permessi relativi a ogni applicazione (classe) per tutte le istanze presenti.
 *
 * I gruppi sono definiti nella tabella @a auth_group. I gruppi possono essere associati ai permessi e alle istanze (auth_group_perm) e gli utenti ai gruppi (auth_group_user).
 * 
 * #JWT
 * I metodi:
 * - getJsonData=getJsonData
 * - whoami=whoami
 * - deleteToken=deleteToken
 * - refreshToken=refreshToken
 * 
 * implementano JWT e possono essere utilizzati per l'autenticazione di applicazioni javascript.
 */
class auth extends \Gino\Controller {

    private $_options;
    public $_optionsLabels;

    private $_users_for_page;
    private $_user_more, $_user_view;
    private $_username_as_email;
    private $_aut_pwd, $_aut_pwd_length, $_pwd_length_min, $_pwd_length_max, $_pwd_numeric_number;
    private $_ldap_auth, $_ldap_auth_only, $_ldap_single_user, $_ldap_auth_password;

    public $_other_field1, $_other_field2, $_other_field3;
    private $_label_field1, $_label_field2, $_label_field3;

    /**
     * @brief Costruttore
     * @return void, istanza di Gino.App.Auth.auth
     */
    function __construct(){

        parent::__construct();

        $this->_instance = 0;
        $this->_instanceName = $this->_class_name;
        
        $this->setAppOptions();
    }
    
    private function setAppOptions() {
        
        $this->_optionsValue = array(
            
        );
        
        if(!$this->_registry->apps->instanceExists($this->_instance_name)) {
            
            $this->_users_for_page = $this->setOption('users_for_page', []);
            $this->_user_more = $this->setOption('user_more_info', []);
            $this->_user_view = $this->setOption('user_card_view', []);
            $this->_username_as_email = $this->setOption('username_as_email', []);
            $this->_aut_pwd = $this->setOption('aut_pwd', []);
            $this->_aut_pwd_length = $this->setOption('aut_pwd_length', []);
            $this->_pwd_length_min = $this->setOption('pwd_min_length', []);
            $this->_pwd_length_max = $this->setOption('pwd_max_length', []);
            $this->_pwd_numeric_number = $this->setOption('pwd_numeric_number', []);
            $this->_ldap_auth = $this->setOption('ldap_auth', []);
            $this->_ldap_auth_only = $this->setOption('ldap_auth_only', []);
            $this->_ldap_single_user = $this->setOption('ldap_single_user', []);
            $this->_ldap_auth_password = $this->setOption('ldap_auth_password', []);
            
            $this->_registry->apps->{$this->_instance_name} = array(
                'users_for_page' => $this->_users_for_page,
                'user_more_info' => $this->_user_more,
                'user_card_view' => $this->_user_view,
                'username_as_email' => $this->_username_as_email,
                'aut_pwd' => $this->_aut_pwd,
                'aut_pwd_length' => $this->_aut_pwd_length,
                'pwd_min_length' => $this->_pwd_length_min,
                'pwd_max_length' => $this->_pwd_length_max,
                'pwd_numeric_number' => $this->_pwd_numeric_number,
                'ldap_auth' => $this->_ldap_auth,
                'ldap_auth_only' => $this->_ldap_auth_only,
                'ldap_single_user' => $this->_ldap_single_user,
                'ldap_auth_password' => $this->_ldap_auth_password
            );
        }
        else {
            $this->_users_for_page = $this->_registry->apps->{$this->_instance_name}['users_for_page'];
            $this->_user_more = $this->_registry->apps->{$this->_instance_name}['user_more_info'];
            $this->_user_view = $this->_registry->apps->{$this->_instance_name}['user_card_view'];
            $this->_username_as_email = $this->_registry->apps->{$this->_instance_name}['username_as_email'];
            $this->_aut_pwd = $this->_registry->apps->{$this->_instance_name}['aut_pwd'];
            $this->_aut_pwd_length = $this->_registry->apps->{$this->_instance_name}['aut_pwd_length'];
            $this->_pwd_length_min = $this->_registry->apps->{$this->_instance_name}['pwd_min_length'];
            $this->_pwd_length_max = $this->_registry->apps->{$this->_instance_name}['pwd_max_length'];
            $this->_pwd_numeric_number = $this->_registry->apps->{$this->_instance_name}['pwd_numeric_number'];
            $this->_ldap_auth = $this->_registry->apps->{$this->_instance_name}['ldap_auth'];
            $this->_ldap_auth_only = $this->_registry->apps->{$this->_instance_name}['ldap_auth_only'];
            $this->_ldap_single_user = $this->_registry->apps->{$this->_instance_name}['ldap_single_user'];
            $this->_ldap_auth_password = $this->_registry->apps->{$this->_instance_name}['ldap_auth_password'];
        }
        
        $this->_options = \Gino\Loader::load('Options', array($this));
        $this->_optionsLabels = array(
            "users_for_page" => array(
                'label' => _("Utenti per pagina"),
                'value' => null,
            ),
            "user_more_info" => array(
                'label' => _("Informazioni aggiuntive utenti"),
                'value' => null
            ),
            "user_card_view" => array(
                'label' => _("Schede utenti visibili"),
                'value' => null
            ),
            "username_as_email" => array(
                'label' => _("Utilizzo email come username"),
                'value' => null,
                'section' => true,
                'section_title' => _('Impostazioni username')
            ),
            "aut_pwd" => array(
                'label' => _("Generazione automatica password"),
                'value' => null,
                'section' => true,
                'section_title' => _('Caratteristiche password')
            ),
            "aut_pwd_length" => array(
                'label' => _("Caratteri della password automatica"),
                'value' => null
            ),
            "pwd_min_length" => array(
                'label' => _("Minimo caratteri password"),
                'value' => null
            ),
            "pwd_max_length" => array(
                'label'=>_("Massimo caratteri password"),
                'value' => null
            ),
            "pwd_numeric_number"=>array(
                'label' => _("Caratteri numerici password"),
                'value' => null
            ),
            "ldap_auth" => array(
                'label' =>_("Attivazione Ldap"),
                'value' => null,
                'required' => true,
                'section' => true,
                'section_title' =>_('Ldap'),
                'section_description' => "<p>"._("Opzioni per la procedura di autenticazione Ldap; per impostare i parametri di connessione al database editare il file config.ldap.php.")."</p>",
            ),
            "ldap_auth_only" => array(
                'label' => array(_("Autenticazione esclusiva Ldap"), _("se l'autenticazione non è esclusiva viene verificata prima la validità dell'utente nel database Ldap e in caso negativo in quello di gino")),
                'value' => null,
            ),
            "ldap_single_user" => array(
                'label'=>_("Utente unico di gino per tutti gli utenti ldap"), 
                'trnsl'=>false, 
                'required'=>false
            ),
            "ldap_auth_password" => array(
                'label' => _("Password dell'utente/utenti di gino abbinati agli utenti ldap"),
                'trnsl'=>false,
                'required'=>false
            ),
        );
    }

    /**
     * @brief Definizione dei metodi pubblici che forniscono un output per il front-end
     *
     * Questo metodo viene letto dal motore di generazione dei layout (prende i metodi non presenti nel file ini) e dal motore di generazione di 
     * voci di menu (presenti nel file ini) per presentare una lista di output associati all'istanza di classe.
     *
     * @return array associativo metodi pubblici metodo => array('label' => label, 'permissions' => permissions)
     */
    public static function outputFunctions() {

        $list = array(
            'login' => array('label'=>_("Box di login"), 'permissions'=>array())
        );

        return $list;
    }

    /**
     * @brief Restituisce alcune proprietà della classe
     * @return array associativo contenente le tabelle, viste e struttura directory contenuti
     */
    public static function getClassElements() {

        return array(
            "tables"=>array(
                'auth_group', 
                'auth_group_perm', 
                'auth_group_user', 
                'auth_opt', 
                'auth_permission', 
                'auth_user', 
                'auth_user_add',
                'auth_user_email',
                'auth_user_perm',
                'auth_registration_profile',
                'auth_registration_profile_group',
                'auth_registration_request',
            ),
            'views' => array(
                'login.php' => _('Login area privata/amministrativa'),
                'registration.php' => _('Pagina di registrazione'),
                'registration_email_object.php' => _('Oggetto della mail inviata a seguito di registrazione'),
                'registration_email_message.php' => _('Messaggio della mail inviata a seguito di registrazione'),
                'registration_result.php' => _('Risultato registrazione'),
                'confirmation_result.php' => _('Risultato conferma indirizzo email'),
                'activation_email_object.php' => _('Oggetto della mail inviata per conferma attivazione'),
                'activation_email_message.php' => _('Messaggio della mail inviata per conferma attivazione'),
                'profile.php' => _('Profilo utente'),
                'activate_profile.php' => _('Attivazione profilo per utente già registrato'),
                'data_recovery_request.php' => _('Richiesta di recupero credenziali'),
                'data_recovery_request_processed.php' => _('Processing richiesta di recupero credenziali'),
                'data_recovery_success.php' => _('Successo richiesta di recupero credenziali'),
            ),
            "folderStructure"=>array (
                CONTENT_DIR.OS.'user'=> null
            )
        );
    }
    
	/**
     * @brief Percorso base della directory dei contenuti
     *
     * @param string $path tipo di percorso (default abs)
     *   - abs, assoluto
     *   - rel, relativo
     * @return string
     */
    public function getBasePath($path = 'abs'){

        $directory = '';

        if($path == 'abs') {
            $directory = $this->_data_dir.OS;
        }
        elseif($path == 'rel') {
            $directory = $this->_data_www.'/';
        }

        return $directory;
    }

    /**
     * @brief Percorso della directory dei contenuti (una directory per ogni utente)
     * @param integer $id valore ID dell'utente
     * @return string, path directory
     */
    public function getAddPath($id) {

        if(!$id) $id = $this->_db->autoIncValue(User::$table);

        $directory = $id.OS;

        return $directory;
    }
    
    /**
     * @brief Verifica utente/password nel processo di autenticazione
     * 
     * @see Ldap::getCheckUser()
     * @param string $username
     * @param string $password
     * @return user object
     */
    public function checkAuthenticationUser($username, $password) {
    	
    	if($this->_ldap_auth)
		{
			Loader::import('auth', 'Ldap');
			
			$ldap = new Ldap($username, $password);
			
			if($ldap->authentication())
			{
				return $this->getCheckUser($username, array('auth_ldap'=>true));
			}
			elseif(!$this->_ldap_auth_only)
			{
				return $this->getCheckUser($username, array('auth_ldap'=>false, 'user_pwd'=>$password));
			}
			else return null;
		}
		else
		{
			return $this->getCheckUser($username, array('auth_ldap'=>false, 'user_pwd'=>$password));
		}
    	
		return null;
    }
    
	/**
	 * @brief Verifica la validità di un utente
	 * 
	 * @see User::getFromUserPwd()
	 * @param string $username
	 * @param array $options
	 *   array associativo di opzioni
	 *   - @b user_pwd (string): password per autenticazione di gino
	 *   - @b auth_ldap (boolean): indica se è stata effettuata l'autenticazione ldap
	 * @return user object
	 */
	private function getCheckUser($username, $options=array()) {

		$password = \Gino\gOpt('user_pwd', $options, null);
		$auth_ldap = \Gino\gOpt('auth_ldap', $options, false);
		
		if($auth_ldap)
		{
			if($this->_ldap_single_user) $username = $this->_ldap_single_user;
			
			$password = $this->_ldap_auth_password;
		}
		
		$registry = \Gino\Registry::instance();
		
		$user = User::getFromUserPwd($username, $password, $auth_ldap);
		return $user;
	}

    /**
     * Attivazione di altri profili per utente già registrato ed attivo
     * 
     * @param \Gino\Http\Request $request
     * @return Gino.Http.Response
     */
    public function activateProfile(\Gino\Http\Request $request)
    {
        if(!$request->user->id or !$request->user->active) {
            throw new \Gino\Exception\Exception404();
        }

        if($request->method == 'POST') {

            $formobj = Loader::load('Form', array());
            $formobj->saveSession('authactivateprofiledata');

            $profile_id = \Gino\cleanVar($request->POST, 'profile', 'int');
            $profile = new RegistrationProfile($profile_id);
            $error_redirect = $this->_registry->router->link($this->_class_name, 'activateProfile', array('id' => $profile->id));

            // check terms
            if($profile->terms and !\Gino\cleanVar($request->POST, 'terms', 'int')) {
                return \Gino\Error::errorMessage(array('error' => _('Devi accettare i termini e le condizioni del servizio')), $error_redirect);
            }

            // add_information validation
            if($profile->add_information) {
                $app = $profile->informationApp();
                $result = $app->actionAuthRegistration($request, $formobj);
                if($result !== TRUE) {
                    return \Gino\Error::errorMessage(array('error' => $result), $error_redirect);
                }
            }

            // data were valid
            Loader::import('auth', 'RegistrationRequest');
            $registration_request = new RegistrationRequest(null);
            $registration_request->registration_profile = $profile->id;
            $registration_request->date = date('Y-m-d H:i:s');
            $registration_request->firstname = $request->user->firstname;
            $registration_request->lastname = $request->user->lastname;
            $registration_request->username = $request->user->username;
            $registration_request->password = $request->user->userpwd;
            $registration_request->email = $request->user->email;
            $registration_request->user = $request->user->id;
            $registration_request->confirmed = 1;

            $registration_request->save();

            if(!$registration_request->id) {
                throw new \Exception(_('Salvataggio richiesta di registrazione fallito'));
            }

            // now id is available
            $registration_request->code = md5($registration_request->id.$registration_request->email);
            $registration_request->save();

            $request->user->groups = array_merge($request->user->groups, $profile->groups);
            $request->user->save();

            return new \Gino\Http\Redirect($this->_registry->router->link($this->_class_name, 'profile'));
        }

        $profile_id = \Gino\cleanVar($request->GET, 'id', 'int');
        $profile = new RegistrationProfile($profile_id);

        if(!$profile->id) {
            throw new \Gino\Exception\Exception404();
        }

        /* form */
        $formobj = Loader::load('Form', array());
        $formobj->load('authactivateprofiledata');

        $form = $formobj->open('', TRUE, '', array('form_id'=>'auth_activate_profile'));
        $form .= \Gino\Input::hidden('profile', $profile->id);

        // add_information
        if($profile->add_information) {
            $app = $profile->informationApp();
            $form .= $app->formAuthRegistration($formobj);
        }

        // terms
        if($profile->terms) {
           $form .= \Gino\htmlChars($profile->terms);
           $form .= \Gino\Input::checkbox_label('terms', FALSE, 1, _('Termini e condizioni'), array('id' => 'terms', 'required' => TRUE, 'text_add' => _('Ho letto ed accetto i termini e le condizioni del servizio')));
        }

        $form .= \Gino\Input::input_label('submit_auth_activate_profile', 'submit', _('invia'), '', array());
        $form .= $formobj->close();

        $view = new \Gino\View($this->_view_dir, 'activate_profile');
        $dict = array(
            'profile' => $profile,
            'form' => $form
        );
        $document = new \Gino\Document($view->render($dict));
        return $document();
    }

    /**
     * Registrazione utente
     * 
     * @param \Gino\Http\Request $request istanza di Gino.Http.Request
     * @return \Gino\Http\Response
     */
    public function registration(\Gino\Http\Request $request)
    {
        // form submission
        if($request->method == 'POST') {
            
            // validazione dati classe auth
            $profile_id = \Gino\cleanVar($request->POST, 'profile', 'int');
            $profile = new RegistrationProfile($profile_id);

            $formobj = Loader::load('Form', array());
            $formobj->saveSession('authregistrationdata');
            
            $error_redirect = $this->_registry->router->link($this->_class_name, 'registration', array('id' => $profile->id));
            // captcha
            if(!$formobj->checkCaptcha()) {
                return \Gino\Error::errorMessage(array('error' => _('Il codice inserito non è corretto.')), $error_redirect);
            }
            // campi obbligatori
            if($formobj->checkRequired()) {
                return \Gino\Error::errorMessage(array('error' => 1), $error_redirect);
            }

            $firstname = \Gino\cleanVar($request->POST, 'firstname', 'string');
            $lastname = \Gino\cleanVar($request->POST, 'lastname', 'string');
            if(!$this->_username_as_email) {
                $username = \Gino\cleanVar($request->POST, 'username', 'string');
            }
            $password = \Gino\cleanVar($request->POST, 'password', 'string');
            $password_check = \Gino\cleanVar($request->POST, 'check_password', 'string');
            $email = \Gino\cleanVar($request->POST, 'email', 'string');
            $email_check = \Gino\cleanVar($request->POST, 'check_email', 'string');

            // check email
            $check_email = User::checkEmail();
            if($check_email !== TRUE) {
                return \Gino\Error::errorMessage($check_email, $error_redirect);
            }
            // check username
            $check_username = User::checkUsername(array('username_as_email' => $this->_username_as_email));
            if($check_username !== TRUE) {
                return \Gino\Error::errorMessage($check_username, $error_redirect);
            }
            // check passowrd
            $check_password = User::checkPassword(array(
                'password' => $password,
                'password_check' => $password_check,
                'pwd_length_min' => $this->_pwd_length_min,
                'pwd_length_max' => $this->_pwd_length_max,
                'pwd_numeric_number' => $this->_pwd_numeric_number
            ));
            if($check_password !== TRUE) {
                return \Gino\Error::errorMessage($check_password, $error_redirect);
            }
            // check terms
            if($profile->terms and !\Gino\cleanVar($request->POST, 'terms', 'int')) {
                return \Gino\Error::errorMessage(array('error' => _('Devi accettare i termini e le condizioni del servizio')), $error_redirect);
            }

            // add_information validation
            if($profile->add_information) {
                $app = $profile->informationApp();
                $result = $app->actionAuthRegistration($request, $formobj);
                if($result !== TRUE) {
                    return \Gino\Error::errorMessage(array('error' => $result), $error_redirect);
                }
            }

            // data were valid
            Loader::import('auth', 'RegistrationRequest');
            $registration_request = new RegistrationRequest(null);
            $registration_request->registration_profile = $profile->id;
            $registration_request->date = date('Y-m-d H:i:s');
            $registration_request->firstname = $firstname;
            $registration_request->lastname = $lastname;
            $registration_request->username = $this->_username_as_email ? $email : $username;
            $registration_request->password = User::setPassword($password);
            $registration_request->email = $email;
            $registration_request->confirmed = 0;

            $registration_request->save();

            if(!$registration_request->id) {
                throw new \Exception(_('Salvataggio richiesta di registrazione fallito'));
            }

            // now id is available
            $registration_request->code = md5($registration_request->id.$registration_request->email);
            $registration_request->save();

            // send mail
            $view = new \Gino\View($this->_view_dir, 'registration_email_object');
            $email_object = $view->render(array( 'profile' => $profile ));

            $view = new \Gino\View($this->_view_dir, 'registration_email_message');
            $dict = array(
                'profile' => $profile,
                'request' => $registration_request,
                'confirmation_url' => $this->_registry->router->link($this->_class_name, 'confirmRegistration', array('code' => $registration_request->code, 'id' => $registration_request->id), '', array('abs' => TRUE))
            );
            $email_message = $view->render($dict);
            $headers = "From: ".$this->_registry->sysconf->email_from_app . "\n";
            $headers .= 'MIME-Version: 1.0'."\n";
            $headers .= 'Content-type: text/plain; charset=utf-8'."\n";

            if(!mail($email, $email_object, $email_message, $headers)) {
                return new \Gino\Http\Redirect($this->_registry->router->link($this->_class_name, 'registrationResult', array('id' => $registration_request->id), array('e' => 'mail')));
            }

            return new \Gino\Http\Redirect($this->_registry->router->link($this->_class_name, 'registrationResult', array('id' => $registration_request->id)));
        }
        // show form
        else {
            $profile_id = \Gino\cleanVar($request->GET, 'id', 'int');
            $profile = new RegistrationProfile($profile_id);

            if(!$profile->id) {
                throw new \Gino\Exception\Exception404();
            }

            if($request->user->id) {
                return new \Gino\Http\Redirect($this->_registry->router->link('auth', 'profile'));
            }

            /* form */
            $formobj = Loader::load('Form', array());
            $formobj->load('authregistrationdata');
            $required = $this->_username_as_email
                ? 'firstname,lastname,password,check_password,email,check_email'
                : 'firstname,lastname,username,password,check_password,email,check_email';

            $form = $formobj->open('', TRUE, $required, array('form_id'=>'auth_registration'));
            $form .= \Gino\Input::hidden('profile', $profile->id);
            $form .= \Gino\Input::input_label('firstname', 'text', $formobj->retvar('firstname'), _('Nome'), array('required' => TRUE));
            $form .= \Gino\Input::input_label('lastname', 'text', $formobj->retvar('lastname'), _('Cognome'), array('required' => TRUE));
            if(!$this->_username_as_email) {
                // onblur check username availability
                $js = "
                onblur=\"var self=this; gino.jsonRequest(
                    'post', 
                    '".$this->_registry->router->link($this->_class_name, 'checkUsernameJson')."',
                    'username=' + $(this).get('value'),
                    function(response) { 
                        if(!response.result) if(!$(self).hasClass('invalid')) $(self).addClass('invalid');
                        else $(self).removeClass('invalid');
                        $('username-check-result').set('text', response.text); 
                    });\"
                onfocus=\"var self=this; $('username-check-result').set('text', ''); $(self).removeClass('invalid');\"";
                $username_check_result = '<span id="username-check-result"></span>';
                $form .= \Gino\Input::input_label('username', 'text', '', _('Username'), array('required' => TRUE, 'js'=>$js, 'text_add' => $username_check_result));
            }

            $form .= \Gino\Input::input_label('email', 'email', $formobj->retvar('email'), _('Email'), array('required' => TRUE));
            $form .= \Gino\Input::input_label('check_email', 'email', '', _('Ripeti email'), array('required' => TRUE, 'other' => 'autocomplete="off"'));

            // onblur validate password and check strength
            $js = "
            onblur=\"var self=this; gino.jsonRequest(
                'post', 
                '".$this->_registry->router->link($this->_class_name, 'checkPasswordJson')."',
                'password=' + $(this).get('value'),
                function(response) { 
                    if(!response.result) if(!$(self).hasClass('invalid')) $(self).addClass('invalid');
                    else $(self).removeClass('invalid');
                    $('password-check-result').set('text', response.text); 
                });\"
            onfocus=\"var self=this; $('password-check-result').set('text', ''); $(self).removeClass('invalid');\"";
            $password_check_result = '<span id="password-check-result"></span>';
            $form .= \Gino\Input::input_label('password', 'password', '', array(_('Password'), $this->passwordRules()), array('required' => TRUE, 'js'=>$js, 'text_add' => $password_check_result));
            $form .= \Gino\Input::input_label('check_password', 'password', '', _('Ripeti password'), array('required' => TRUE));

            // add_information
            if($profile->add_information) {
                $app = $profile->informationApp();
                $form .= $app->formAuthRegistration($formobj);
            }

            $form .= $formobj->captcha();
            // terms
            if($profile->terms) {
               $form .= \Gino\htmlChars($profile->terms);
               $form .= \Gino\Input::checkbox_label('terms', FALSE, 1, _('Termini e condizioni'), array('id' => 'terms', 'required' => TRUE, 'text_add' => _('Ho letto ed accetto i termini e le condizioni del servizio')));
            }

            $form .= \Gino\Input::input_label('submit_auth_registration', 'submit', _('invia'), '', array());
            $form .= $formobj->close();

            $view = new \Gino\View($this->_view_dir, 'registration');
            $dict = array(
                'profile' => $profile,
                'form' => $form
            );
            $document = new \Gino\Document($view->render($dict));
            return $document();
        }
    }

    /**
     * Vista risultato registrazione
     *
     * @param \Gino\Http\Request $request istanza di Gino.Http.Request
     * @return \Gino\Http\Response
     */
    public function registrationResult(\Gino\Http\Request $request) {
    	
        Loader::import('auth', 'RegistrationRequest');
        $id = \Gino\cleanVar($request->GET, 'id', 'int');
        $registration_request = new RegistrationRequest($id);

        if(!$registration_request->id) {
            throw new \Gino\Exception\Exception404();
        }

        $error = FALSE;
        $e = \Gino\cleanVar($request->GET, 'e', 'string');
        if($e == 'mail') {
            $error = sprintf(_('Si è verificato un errore nell\'invio dell\'email di conferma registrazione, scrivere al seguente indirizzo: %s'), '<a href="mailto:'.$this->_registry->sysconf->email_admin.'">'.$this->_registry->sysconf->email_admin.'</a>');
        }

        $view = new \Gino\View($this->_view_dir, 'registration_result');
        $dict = array(
            'error' => $error,
            'request' => $registration_request,
            'profile' => new RegistrationProfile($registration_request->registration_profile)
        );
        $document = new Document($view->render($dict));

        return $document();
    }

    /**
     * Conferma indirizzo email registrazione
     *
     * @param \Gino\Http\Request $request
     * @return Gino.Http.Response
     */
    public function confirmRegistration(\Gino\Http\Request $request) {
		
    	Loader::import('auth', 'RegistrationRequest');

        $id = \Gino\cleanVar($request->GET, 'id', 'int');
        $code = \Gino\cleanVar($request->GET, 'code', 'string');

        $registration_request = new RegistrationRequest($id);

        if(!$registration_request->id) {
            throw new \Gino\Exception\Exception404();
        }

        // gia confermato, vai al login
        if($registration_request->confirmed) {
            $request->session->auth_redirect = $this->_registry->router->link($this->_class_name, 'login');
            return new \Gino\Http\Redirect($request->session->auth_redirect);
        }

        $headers = "From: ".$this->_registry->sysconf->email_from_app . "\n";
        $headers .= 'MIME-Version: 1.0'."\n";
        $headers .= 'Content-type: text/plain; charset=utf-8'."\n";

        // confirmed
        if($code == md5($registration_request->id.$registration_request->email)) {
            // conferma
            $registration_request->confirmed = 1;
            $registration_request->save();
            $profile = new RegistrationProfile($registration_request->registration_profile);

            if($profile->auto_enable) {
                // creazione utente
                $result = $this->createAndActivateUser($registration_request);

                if(!$result) {
                    return Error::errorMessage(array('error' => sprintf(_('Si è verificato un errore nell\'attivazione dell\'utenza. Scrivere a %s'), $this->_registry->sysconf->email_admin)), $this->_home);
                }

                // mail amministratore
                $mail_object = sprintf(_('Registrazione nuovo utente | %s'), $this->_registry->sysconf->head_title);
                $mail_message = sprintf(_('L\'utente %s %s si è appena registrato ed è stato attivato.'), $registration_request->firstname, $registration_request->lastname);
                mail($this->_registry->sysconf->email_admin, $mail_object, $mail_message, $headers);

            }
            else {
                // mail amministratore
                $mail_object = sprintf(_('Registrazione nuovo utente in attesa di attivazione | %s'), $this->_registry->sysconf->head_title);
                $mail_message = sprintf(_('L\'utente %s %s si è appena registrato ed è in attesa di attivazione.'), $registration_request->firstname, $registration_request->lastname);
                mail($this->_registry->sysconf->email_admin, $mail_object, $mail_message, $headers);
            }
        }
        // not confirmed
        else {
            throw new \Gino\Exception\Exception404();
        }

        $request->session->auth_redirect = 'home/';
        $view = new View($this->_view_dir, 'confirmation_result');
        $dict = array(
            'registration_request' => $registration_request,
            'profile' => $profile
        );
        $document = new \Gino\Document($view->render($dict));
        return $document();
    }

    /**
     * Creazione ed attivazione di un utente a seguito di rischiesta di registrazione
     *
     * @param \Gino\Http\Request $request
     * @return Gino.Http.Redirect
     */
    public function activateRegistrationUser(\Gino\Http\Request $request) {

        Loader::import('auth', 'RegistrationRequest');

        $this->requirePerm('can_admin');
        $id = \Gino\cleanVar($request->GET, 'id', 'int');
        $registration_request = new RegistrationRequest($id);

        if(!$registration_request->id) {
            throw new \Exception(sprintf(_('Richiesta di registrazione con id %s inesistente'), $registration_request->id));
        }

        $result = $this->createAndActivateUser($registration_request);

        if($result) {
            return new \Gino\Http\Redirect($this->_registry->router->link($this->_class_name, 'manageAuth', array(), array('block'=>'request')));
        }
        else {
            throw new \Exception(sprintf(_('Impossibile creare l\'utente per la richiesta di registrazione con id %s'), $registration_request->id));
        }
    }

    /**
     * Crea ed attiva un utente, invia una mail di attivazione
     *
     * @param \Gino\App\Auth\RegistrationRequest $registration_request richiesta di registrazione
     * @return bool, risultato
     */
    private function createAndActivateUser(\Gino\App\Auth\RegistrationRequest $registration_request) {

        $profile = new RegistrationProfile($registration_request->registration_profile);

        $user = new User(null);
        $user->firstname = $registration_request->firstname;
        $user->lastname = $registration_request->lastname;
        $user->email = $registration_request->email;
        $user->username = $registration_request->username;
        $user->userpwd = $registration_request->password;
        $user->is_admin = 0;
        $user->publication = 0;
        $user->date = date('Y-m-d H:i:s');
        $user->ldap = 0;
        $user->active = 1;
        $user->groups = $profile->groups;
        $user->save();

        if(!$user->id) {
            return FALSE;
        }

        $registration_request->user = $user->id;
        $registration_request->save();

        $headers = "From: ".$this->_registry->sysconf->email_from_app . "\n";
        $headers .= 'MIME-Version: 1.0'."\n";
        $headers .= 'Content-type: text/plain; charset=utf-8'."\n";

        $view = new View($this->_view_dir, 'activation_email_object');
        $email_object = $view->render(array( 'profile' => $profile ));
        $view = new View($this->_view_dir, 'activation_email_message');
        $dict = array(
            'profile' => $profile,
            'user' => $user,
            'login_url' => $this->_registry->router->link($this->_class_name, 'login', array(), '', array('abs' => TRUE)),
            'profile_url' => $this->_registry->router->link($this->_class_name, 'profile', array(), '', array('abs' => TRUE))
        );
        $email_message = $view->render($dict);

        return mail($user->email, $email_object, $email_message, $headers);
    }

    /**
     * Vista profilo utente
     *
     * Tutti gli utenti hanno un profilo dove poter modificare la password ed alcune informazioni personali.
     * Solamente gli utenti creati a seguito di procedura registrazione possono associarsi ad altri profili
     * di registrazione
     *
     * @param \Gino\Http\Request $request
     * @return Gino.Http.Response
     */
    public function profile(\Gino\Http\Request $request) {

        Loader::import('auth', 'RegistrationRequest');

        if(!$request->user->id or !$request->user->active) {
            throw new \Gino\Exception\Exception404();
        }

        // change password
        if($request->method == 'POST' and isset($request->POST['submit_auth_profile_chg_password']))
        {
            $obj_user = $request->user;

            $action_result = $obj_user->savePassword(array(
                'pwd_length_min' => $this->_pwd_length_min, 
                'pwd_length_max' => $this->_pwd_length_max, 
                'pwd_numeric_number' => $this->_pwd_numeric_number
            ));

            if($action_result === true) {
                $headers = "From: ".$this->_registry->sysconf->email_from_app . "\n";
                $headers .= 'MIME-Version: 1.0'."\n";
                $headers .= 'Content-type: text/plain; charset=utf-8'."\n";

                $object = sprintf(_('Modifica password | %s'), $this->_registry->sysconf->head_title);
                $message = sprintf(_("Hai modificato la password di accesso.\nLa nuova password è: %s."), \Gino\cleanVar($request->POST, 'userpwd', 'string'));
                mail($request->user->email, $object, $message, $headers);
                return new Redirect($this->_registry->router->link($this->_class_name, 'profile', array(), array('pwd' => 1)));
            }
            else {
                return Error::errorMessage($action_result, $this->_registry->router->link($this->_class_name, 'profile'));
            }
        }

        // show profile
        $view = new \Gino\View($this->_view_dir, 'profile');

        // change password
        $pwd_updated = \Gino\cleanVar($request->GET, 'pwd', 'int');
        
        $formobj = Loader::load('Form', array());
        $form_password = $formobj->open('', FALSE, 'userpwd,check_userpwd', array('form_id'=>'auth_profile_chg_password'));

        // onblur validate password and check strength
        $js = "
        onblur=\"var self=this; gino.jsonRequest(
            'post', 
            '".$this->_registry->router->link($this->_class_name, 'checkPasswordJson')."',
            'password=' + $(this).get('value'),
            function(response) { 
                if(!response.result) if(!$(self).hasClass('invalid')) $(self).addClass('invalid');
                else $(self).removeClass('invalid');
                $('password-check-result').set('text', response.text); 
            });\"
        onfocus=\"var self=this; $('password-check-result').set('text', ''); $(self).removeClass('invalid');\"";
        $password_check_result = '<span id="password-check-result"></span>';
        $form_password .= \Gino\Input::input_label('userpwd', 'password', '', array(_('Password'), $this->passwordRules()), array('required' => TRUE, 'js'=>$js, 'text_add' => $password_check_result));
        $form_password .= \Gino\Input::input_label('check_userpwd', 'password', '', _('Ripeti password'), array('required' => TRUE));

        $form_password .= \Gino\Input::input_label('submit_auth_profile_chg_password', 'submit', _('modifica password'), '', array());
        $form_password .= $formobj->close();

        // add information data
        $data = array();

        $user_profiles = array();
        $registration_requests = RegistrationRequest::objects(null, array('where' => "user='".$request->user->id."' AND confirmed='1'"));
        foreach($registration_requests as $r) {
            $profile = new RegistrationProfile($r->registration_profile);
            $user_profiles[] = $profile->id;
            if($profile->add_information) {
                $app = $profile->informationApp();
                if(method_exists($app, 'authProfile')) {
                    $data[] = array(
                        'description' => $profile->ml('description'),
                        'content' => $app->authProfile(),
                        'update_url' => method_exists($app, 'updateAuthProfile') ? $this->_registry->router->link($app->getInstanceName(), 'updateAuthProfile') : null
                    );
                }
            }
        }

        // other profiles
        $profiles_data = array();
        $profiles = RegistrationProfile::objects(null, array());
        foreach($profiles as $profile) {
            if(!in_array($profile->id, $user_profiles)) {
                $profiles_data[] = array(
                    'profile' => $profile,
                    'activation_url' => $this->_registry->router->link($this->_class_name, 'activateProfile', array('id' => $profile->id))
                );
            }
        }

        $dict = array(
            'user' => $request->user,
            'form_password' => $form_password,
            'pwd_updated' => $pwd_updated,
            'delete_account_url' => $this->_registry->router->link($this->_class_name, 'deleteAccount'),
            'data' => $data,
            'profiles_data' => $profiles_data
        );

        $document = new Document($view->render($dict));
        return $document();
    }

    /**
     * Eliminazione account
     *
     * @param \Gino\Http\Request $request
     * @return Gino.Http.Redirect
     */
    public function deleteAccount(\Gino\Http\Request $request) {
        
    	if(!$request->user->id or !$request->user->active) {
            throw new \Gino\Exception\Exception404();
        }

        Loader::import('auth', 'RegistrationRequest');

        $registration_requests = RegistrationRequest::objects(null, array('where' => "user='".$request->user->id."' AND confirmed='1'"));
        foreach($registration_requests as $r) {
            $profile = new RegistrationProfile($r->registration_profile);
            if($profile->add_information) {
                $app = $profile->informationApp();
                if(method_exists($app, 'deleteAuthAccount')) {
                    $app->deleteAuthAccount($request);
                }
            }
            $r->delete();
        }

        $request->user->delete();

        return new \Gino\Http\Redirect($this->_home.'?action=logout');
    }

    /**
     * @brief Interfaccia di amministrazione modulo
     * @param \Gino\Http\Request istanza di Gino.Http.Request
     * @return Gino.Http.Response
     */
    public function manageAuth(\Gino\Http\Request $request) {

        $this->requirePerm('can_admin');

        $block = \Gino\cleanVar($request->GET, 'block', 'string', null);
        $op = \Gino\cleanVar($request->GET, 'op', 'string', null);

        $link_frontend = ['link' => $this->linkAdmin(array(), 'block=frontend'), 'label' => _('Frontend')];
        $link_options = ['link' => $this->linkAdmin(array(), 'block=options'), 'label' => _('Opzioni')];
        $link_group = ['link' => $this->linkAdmin(array(), 'block=group'), 'label' => _('Gruppi')];
        $link_perm = ['link' => $this->linkAdmin(array(), 'block=perm'), 'label' => _('Permessi')];
        $link_profile = ['link' => $this->linkAdmin(array(), 'block=profile'), 'label' => _('Profili registrazione')];
        $link_request = ['link' => $this->linkAdmin(array(), 'block=request'), 'label' => _('Richieste registrazione')];
        $link_dft = ['link' => $this->linkAdmin(), 'label' => _('Utenti')];
        $sel_link = $link_dft;

        if($block == 'frontend') {
            $backend = $this->manageFrontend();
            $sel_link = $link_frontend;
        }
        elseif($block=='options') {
            $backend = $this->manageOptions();
            $sel_link = $link_options;
        }
        elseif($block=='group') {

            if($op == 'jgp') {
                $backend = $this->joinGroupPermission($request);
            } else {
                $backend = $this->manageGroup();
            }

            $sel_link = $link_group;
        }
        elseif($block=='perm') {
            $backend = $this->managePermission();
            $sel_link = $link_perm;
        }
        elseif($block=='profile') {
            $backend = $this->manageRegistrationProfile();
            $sel_link = $link_profile;
        }
        elseif($block=='request') {
            $backend = $this->manageRegistrationRequest();
            $sel_link = $link_request;
        }
        elseif($block=='password') {
            $backend = $this->changePassword($request);
            $sel_link = $link_dft;
        }
        else {
            if($op == 'jup') {
                $backend = $this->joinUserPermission($request);
            } else {
                $backend = $this->manageUser($request);
            }
        }

        if(is_a($backend, '\Gino\Http\Response')) {
            return $backend;
        }

        $dict = array(
            'title' => _('Autenticazione'),
            'links' => array($link_frontend, $link_options, $link_request, $link_profile, $link_perm, $link_group, $link_dft),
            'selected_link' => $sel_link,
            'content' => $backend
        );

        $view = new View(null, 'tabs');

        $document = new Document($view->render($dict));
        return $document();
    }

    /**
     * Interfaccia di amministrazione richieste di registrazione
     *
     * @return string or \Gino\Http\Redirect
     */
    private function manageRegistrationRequest() {
    	
        Loader::import('auth', 'RegistrationRequest');

        $info = _("Elenco delle richieste di registrazione.");

        $opts = array(
            'list_display' => array('id', 'date', 'firstname', 'lastname', 'email', 'confirmed', array('label' => _('Utente'), 'member' => 'getOrActivateUser'), ),
            'list_description' => $info
        );

        $admin_table = Loader::load('AdminTable', array(
            $this,
            array('allow_insertion' => FALSE)
        ));

        return $admin_table->backoffice('RegistrationRequest', $opts, array(), array());
    }

    /**
     * Interfaccia di amministrazione profili di registrazione
     *
     * @return string or \Gino\Http\Redirect
     */
    private function manageRegistrationProfile() {
    	
        $info = _("Elenco dei profili di registrazione automatica al sistema.");

        $fieldsets = array(
            _('Meta') => array('id', 'description'),
            _('Vista form registrazione') => array('title', 'text', 'terms'),
            _('Opzioni') => array('auto_enable', 'groups'),
            _('Informazioni aggiuntive') => array('add_information', 'add_information_module_type', 'add_information_module_id')
        );

        $opts = array(
            'list_display' => array('id', 'description', 'auto_enable', 'add_information', 'groups', array('label' => _('Url'), 'member'=>'getUrl')),
            'list_description' => $info
        );

        $opts_fields = array(
            'text' => array(
                'widget' => 'editor',
                'notes' => FALSE,
                'img_preview' => TRUE,
            ),
            'terms' => array(
                'widget' => 'editor',
                'notes' => FALSE,
                'img_preview' => FALSE,
            ),
            'add_information_module_id' => array('trnsl' => false)
        );

        $admin_table = Loader::load('AdminTable', array(
            $this
        ));

        return $admin_table->backoffice('RegistrationProfile', $opts, array('fieldsets' => $fieldsets), $opts_fields);
    }

    /**
     * @brief Interfaccia di amministrazione utenti
     * @param \Gino\Http\Request istanza di Gino.Http.Request
     * @return string or \Gino\Http\Redirect
     * 
     * Nell'inserimento di un nuovo utente vengono effettuati i controlli con User::checkPassword() e User::checkUsername(). \n
     * In inserimento e modifica vengono effettuati i controlli con User::checkEmail().
     * 
     * Lo username e l'email devono essere unici. \n
     * Se si imposta come username l'email (proprietà $_username_as_email), il campo username non viene mostrato nell'inserimento e il campo email nella modifica. \n
     * Nell'inserimento viene chiesto di riscrivere l'email come controllo. \n
     * Se viene impostata la generazione automatica della password, nell'inserimento non viene mostrato il campo password.
     */
    private function manageUser(\Gino\Http\Request $request) {

        $info = _("Elenco degli utenti del sistema.");
        
        $list_display = $this->_ldap_auth ? array('id', 'firstname', 'lastname', 'username', 'email', 'active', 'ldap', 'groups') : array('id', 'firstname', 'lastname', 'username', 'email', 'active', 'groups');

        $opts = array(
            'list_display' => $list_display,
            'list_description' => $info, 
            'add_buttons' => array(
                array('label'=>\Gino\icon('permission', array('scale' => 1)), 'link'=>$this->linkAdmin(array(), 'block=user&op=jup'), 'param_id'=>'ref'),
                array('label'=>\Gino\icon('password', array('scale' => 1)), 'link'=>$this->linkAdmin(array(), 'block=password'), 'param_id'=>'ref')
            )
        );

        $id = \Gino\cleanVar($request->GET, 'id', 'int', '');
        $edit = \Gino\cleanVar($request->GET, 'edit', 'int', '');

        if($this->_username_as_email) {
            $fieldsets = array(
                _('Anagrafica') => array('id', 'firstname', 'lastname', 'company', 'phone', 'address', 'cap', 'city', 'nation'),
                _('Utenza') => $this->_ldap_auth ? array('email', 'check_email', 'userpwd', 'ldap', 'active') : array('email', 'check_email', 'userpwd', 'active'),
                _('Informazioni') => array('text', 'photo', 'publication'),
                _('Privilegi') => array('is_admin', 'groups')
            );
        }
        else {
            $fieldsets = array(
                _('Anagrafica') => array('id', 'firstname', 'lastname', 'company', 'phone', 'email', 'check_email', 'address', 'cap', 'city', 'nation'),
                _('Utenza') => $this->_ldap_auth ? array('username', 'check_username', 'userpwd', 'ldap', 'active') : array('username', 'check_username', 'userpwd', 'active'),
                _('Informazioni') => array('text', 'photo', 'publication'),
                _('Privilegi') => array('is_admin', 'groups')
            );
        }
        
        $buffer = '';

        if($id && $edit)    // modify
        {
            $removeFields = array('username', 'userpwd');
            if($this->_username_as_email) $removeFields[] = 'email';
            $addCell = null;
        }
        else
        {
            $url = $this->link($this->_class_name, 'checkUsername');
            $div_id = 'check_user';
            $onclick = "gino.ajaxRequest('post', '$url', 'username='+$('username').getProperty('value'), '$div_id')";
            $check_message = "<div id=\"$div_id\" style=\"display:inline; margin-left:10px; font-weight:bold;color:#ff0000;\"></div>\n";
            
            $check_username = \Gino\Input::submit('check_username', _("controlla"), ['onclick' => $onclick, 'type' => 'button']);
            $check_username .= $check_message;
            
            $check_username = \Gino\Input::placeholderRow(_("Disponibilità username"), $check_username);
            
            $check_email = \Gino\Input::input_label('check_email', 'text', '', _("Controllo email"), array("required"=>true, "size"=>40, "maxlength"=>100, "other"=>"autocomplete=\"off\""));

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
            
            $buffer .= "
        	<script type=\"text/javascript\">

			function getRadioVal(form, name) {
				
				var radios = document.getElementById(form).elements[name];
				var val = 0;
				
				for (var i=0; i<radios.length; i++) {
					if (radios[i].checked) {
						val = radios[i].value;
						break;
        			}
        		}
        		if (val == 1) {
        			document.getElementById('userpwd').parentNode.style.display = 'none';
        			document.getElementById('userpwd').removeAttribute('required');
        		}
        		else if (val == 0) {
        			document.getElementById('userpwd').parentNode.style.display = 'block';
        			document.getElementById('userpwd').setAttribute('required', '');
        		}
    		}
    		</script>";
        }
        
        if(!$this->_ldap_auth) $removeFields[] = 'ldap';
        
        $opts_form = array(
            'removeFields' => $removeFields, 
            'addCell' => $addCell, 
            // Custom options
            'username_as_email' => $this->_username_as_email, 
            'user_more_info' => $this->_user_more, 
            'aut_password' => $this->_aut_pwd, 
            'aut_password_length' => $this->_aut_pwd_length, 
            'pwd_length_min' => $this->_pwd_length_min, 
            'pwd_length_max' => $this->_pwd_length_max, 
            'pwd_numeric_number' => $this->_pwd_numeric_number,
        	'ldap_auth' => $this->_ldap_auth, 
        	'ldap_auth_password' => $this->_ldap_auth_password, 
            'fieldsets' => $fieldsets
        );

        $opts_input = array(
            'email' => array(
                'size'=>40, 
                'trnsl'=>false
            ),
            'username' => array(
                'id'=>'username'
            ),
            'userpwd' => array(
                'id'=>'userpwd', 
            	'text_add'=>$this->passwordRules($id), 
                'widget'=>'password'
            ),
            'firstname' => array(
                'trnsl'=>false
            ),
            'lastname' => array(
                'trnsl'=>false
            ),
            'company' => array(
                'trnsl'=>false
            ),
            'phone' => array(
                'trnsl'=>false
            ),
            'fax' => array(
                'trnsl'=>false
            ),
            'address' => array(
                'trnsl'=>false
            ),
            'cap' => array(
                'size'=>5
            ),
            'city' => array(
                'trnsl'=>false
            ), 
            'ldap' => array(
				'id'=>'ldap', 
            	'js'=>"onclick=\"javascript:getRadioVal('formauth_user', 'ldap');\""
            )
        );

        $admin_table = \Gino\Loader::load('AdminTable', array($this));
        $admin_table->setModelForm('\Gino\App\Auth\ModelFormUser');

        $backend = $admin_table->backoffice('User', $opts, $opts_form, $opts_input);
        
        return (is_a($backend, '\Gino\Http\Response')) ? $backend : $buffer.$backend;
    }

    /**
     * @brief Descrizione delle regole alle quali è sottoposta la password
     * 
     * @param integer $id valore ID dell'utente
     * @return string, regole password
     */
    private function passwordRules($id = null) {

        $text = '';

        if($id || !$this->_aut_pwd)
        {
            $text = sprintf(_("La password deve contenere un numero di caratteri da %s a %s."), $this->_pwd_length_min, $this->_pwd_length_max);

            if($this->_pwd_numeric_number) $text .= ' '.sprintf(_("Tra questi, %s devono essere numerici."), $this->_pwd_numeric_number);
        }

        return $text;
    }

    /**
     * @brief Controlla se lo username è disponibile
     *
     * @param \Gino\Http\Request $request
     * @return Gino.Http.Response
     */
    public function checkUsername(\Gino\Http\Request $request) {

        Loader::import('class/http', '\Gino\Http\Response');

        $username = \Gino\cleanVar($request->POST, 'username', 'string', '');

        if(!$username) {
            return new \Gino\Http\Response("<strong>"._("Inserire uno username!")."</strong>");
        }

        $check = $this->_db->getFieldFromId(User::$table, 'id', 'username', $username);
        $content = $check ? _("Username non disponibile!") : _("Username disponibile!");

        return new Response("<strong>".$content."</strong>");
    }

    /**
     * @brief Controlla se lo username è disponibile e ritorna un json
     *
     * @param \Gino\Http\Request $request
     * @return Gino.Http.ResponseJson
     */
    public function checkUsernameJson(\Gino\Http\Request $request) {

        Loader::import('class/http', '\Gino\Http\ResponseJson');

        $username = \Gino\cleanVar($request->POST, 'username', 'string');

        if(!$username) {
            $response = array('result' =>FALSE, 'text' => _('Inserire uno username!'));
        }
        else {
            $check = $this->_db->getFieldFromId(User::$table, 'id', 'username', $username);
            $response = $check ? array('result' =>FALSE, 'text' => _('Username non disponibile!')) : array('result' =>TRUE, 'text' => _('Username disponibile!'));
        }

        return new \Gino\Http\ResponseJson($response);
    }

    /**
     * @brief Controlla che la password abbia le caratteristiche richieste
     *
     * @param \Gino\Http\Request $request
     * @return Gino.Http.ResponseJson
     */
    public function checkPasswordJson(\Gino\Http\Request $request) {

        Loader::import('class/http', '\Gino\Http\ResponseJson');

        $password = \Gino\cleanVar($request->POST, 'password', 'string');

        if(!$password) {
            $response = array('result' =>FALSE, 'text' => _('Inserire una password!'));
        }
        else {
            // password length
            if(strlen($password) < $this->_pwd_length_min or strlen($password) > $this->_pwd_length_max) {
                $response = array('result' =>FALSE, 'text' => sprintf(_('La password deve contenere almeno %s caratteri e non più di %s'), $this->_pwd_length_min, $this->_pwd_length_max));
            }
            else {
                // password digit chars
                preg_match_all("#\d#", $password, $matches);
                if(!$matches[0] or count($matches[0]) < $this->_pwd_numeric_number) {
                    $response = array('result' =>FALSE, 'text' => sprintf(_('La password deve contenere almeno %s caratteri numerici'), $this->_pwd_numeric_number));
                }
                else {
                    // valida
                    $strength = $this->passwordStrength($password);
                    $response = array(
                        'result' => TRUE,
                        'strength' => $strength,
                        'text' => sprintf(_('Sicurezza: %s/10'), $strength)
                    );
                }
            }
        }

        return new \Gino\Http\ResponseJson($response);
    }

    /**
     * Clacola la robustezza di una password, da 0 a 10
     *
     * @param string $password
     * @return int, robustezza password da 0 a 10
     */
    private function passwordStrength($password)
    {
        if(strlen($password) < 10) {
            $strength = max(0, strlen($password) - 3);
            $strength = min($strength, 6);
        }
        else {
            $strength = 6;
        }

        // numbers alpha (uppercase and not) and special chars
        if(preg_match("#.*(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*\W)#", $password)) {
            $strength += 3;
        }
        // numbers alpha (uppercase and not)
        elseif(preg_match("#.*(?=.*[a-z])(?=.*[A-Z])(?=.*\d)#", $password)) {
            $strength += 2;
        }
        elseif(preg_match("#.*(?=.*[a-z])(?=.*[A-Z])#", $password) or preg_match("#.*(?=.*\w)(?=.*\d)#", $password)) {
            $strength += 1;
        }

        return $strength;
    }

    /**
     * @brief Interfaccia di sostituzione della password
     *
     * Parametri GET (per il form): \n
     *   - ref (integer), valore ID dell'utente
     *   - c (integer), riporta se la password è stata correttamente aggiornata
     *
     * Parametri POST (per l'action del form): \n
     *   - id (integer), valore ID dell'utente
     *
     * @see User::savePassword()
     * @see User::formPassword()
     * @see passwordRules()
     * @param \Gino\Http\Request $request
     * @return string
     */
    private function changePassword(\Gino\Http\Request $request) {

        if($request->method == 'POST')
        {
            $user_id = \Gino\cleanVar($request->POST, 'id', 'int', '');
            $obj_user = new User($user_id);

            $action_result = $obj_user->savePassword(array(
                'pwd_length_min' => $this->_pwd_length_min, 
                'pwd_length_max' => $this->_pwd_length_max, 
                'pwd_numeric_number' => $this->_pwd_numeric_number
            ));

            if($action_result === true) {
                return new Redirect($this->_registry->router->link($this->_class_name, 'manageAuth'));
            }
            else {
                return Error::errorMessage($action_result, $this->_registry->router->link($this->_class_name, 'manageAuth', array(), "block=password&ref=$user_id"));
            }
        }

        $user_id = \Gino\cleanVar($request->GET, 'ref', 'int', '');
        $change = \Gino\cleanVar($request->GET, 'c', 'int', '');

        $obj_user = new User($user_id);

        if($obj_user->ldap)
        {
        	$content = "<p>"._("Non è possibile modificare la password di un utente Ldap")."</p>";
        }
        else
        {
        	$content = $obj_user->formPassword(array(
            	'form_action'=>'', 
            	'rules'=>$this->passwordRules($user_id), 
            	'maxlength'=>$this->_pwd_length_max)
        	);
        }

        $title = sprintf(_('Modifica password "%s"'), $obj_user);

        $dict = array(
            'title' => $title,
            'content' => $content
        );

        $view = new \Gino\View();
        $view->setViewTpl('section');

        return $view->render($dict);
    }

    /**
     * @brief Interfaccia di amministrazione dei gruppi
     * @return Gino.Http.Redirect oppure html
     */
    private function manageGroup() {

    	$opts = array(
            'list_display' => array('id', 'name', 'description', array('member' => 'printPermissions', 'label' => _("Permessi associati"))),
            'list_description' => _("Elenco dei gruppi del sistema."), 
            'add_buttons' => array(
                array('label'=>\Gino\icon('permission', array('scale' => 1)), 'link'=>$this->linkAdmin(array(), 'block=group&op=jgp'), 'param_id'=>'ref'),
            )
        );

        $admin_table = Loader::load('AdminTable', array($this));

        return $admin_table->backoffice('Group', $opts, array());
    }

    /**
     * @brief Interfaccia di amministrazione dei permessi
     * @return Gino.Http.Redirect oppure html
     */
    private function managePermission() {

        $info = _("Elenco dei permessi.");

		$opts = array(
            'list_display' => array('id', 'class', 'code', 'label', 'admin'),
            'list_description' => $info
        );

        $admin_table = \Gino\Loader::load('AdminTable', array(
            $this,
            array('allow_insertion' => false, 'edit_deny' => 'all', 'delete_deny' => 'all')
        ));

        return $admin_table->backoffice('Permission', $opts);
    }

    /**
     * @brief Reindirizza le operazione di join tra utenti/gruppi/permessi
     * 
     * @param string $block
     * @param string $option
     * @param integer $ref_id valore ID del riferimento (utente o gruppo)
     * @return redirect
     */
    private function returnJoinLink($block, $option, $ref_id) {

        $url = $this->linkAdmin(array(), "block=$block&op=$option&ref=$ref_id", array('abs' => TRUE));
        return new Redirect($url);
    }

    /**
     * @brief Interfaccia di associazione utente-permessi
     *
     * Parametri GET: \n
     *   - ref (integer), valore ID dell'utente
     * @param \Gino\Http\Request $request
     * @return string
     */
    private function joinUserPermission(\Gino\Http\Request $request) {

        $id = \Gino\cleanVar($request->GET, 'ref', 'int', '');
        if(!$id) return null;

        $obj_user = new User($id);
        $checked = $obj_user->getPermissions();

        $gform = \Gino\Loader::load('Form', array());

        $form_action = $this->link($this->_class_name, 'actionJoinUserPermission');

        $content = $gform->open($form_action, false, '', array('form_id'=>'j_userperm', 'validation'=>false));
        $content .= \Gino\Input::hidden('id', $obj_user->id);
        $content .= $this->formPermission($checked);

        $content .= \Gino\Input::submit('submit', _("associa"));
        $content .= $gform->close();
        
        $description = _("IMPORTANTE: per permettere a un utente l'accesso a funzionalità amministrative di qualsiasi applicazione non è sufficiente assegnargli i permessi relativi, ma occorre anche assegnargli il permesso di accedere all'area amministrativa (Modulo: core -> appartenenza allo staff).");

        $dict = array(
            'title' => sprintf(_("Utente \"%s\" - permessi"), $obj_user),
            'content' => $content, 
        	'post_header' => $description
        );

        $view = new View(null, 'section');
        return $view->render($dict);
    }

    /**
     * @brief Processa il form di associazione degli utenti ai permessi
     *
     * parametri post: \n
     *   - id (integer), valore id dell'utente
     *   - perm (array), permessi selezionati
     *
     * @param \Gino\Http\Request $request
     * @return Gino.Http.Redirect
     */
    public function actionJoinUserPermission(\Gino\Http\Request $request) {

        $this->requirePerm('can_admin');

        $id = \Gino\cleanVar($request->POST, 'id', 'int', '');

        if(!$id) {
            throw new \Gino\Exception\Exception404();
        }

        $perm = array_key_exists('perm', $request->POST) ? $request->POST['perm'] : array();

        $obj_user = new User($id);
        $existing_perms = $obj_user->getPermissions();

        if(is_array($perm) && count($perm))
        {
            $array_delete = array_diff($existing_perms, $perm);

            // valori da eliminare
            if(count($array_delete))
            {
                foreach($array_delete as $value)
                {
                    $split = User::getMergeValue($value);

                    $permission_id = $split[0];
                    $instance_id = $split[1];

                    $this->_db->delete(Permission::$table_perm_user, "user_id='$id' and instance='$instance_id' and perm_id='$permission_id'");
                }
            }

            // valori da aggiungere
            foreach($perm as $value)
            {
                if(!in_array($value, $existing_perms))
                {
                    $split = User::getMergeValue($value);

                    $permission_id = $split[0];
                    $instance_id = $split[1];

                    $this->_db->insert(array('instance'=>$instance_id, 'user_id'=>$id, 'perm_id'=>$permission_id), permission::$table_perm_user);
                }
            }
        }
        else    // elimina tutto
        {
            $this->_db->delete(permission::$table_perm_user, "user_id='$id'");
        }

        return $this->returnJoinLink('user', 'jup', $id);
    }

    /**
     * @brief Form di associazione gruppo-permessi
     * 
     * parametri get: \n
     *   - ref (integer), valore id del gruppo
     * 
     * @param \Gino\Http\Request $request
     * @return string
     */
    private function joinGroupPermission($request) {

        $id = \Gino\cleanVar($request->GET, 'ref', 'int', '');
        if(!$id) return null;

        $obj_group = new Group($id);
        $checked = $obj_group->getPermissions();

        $gform = \Gino\Loader::load('Form', array());

        $form_action = $this->link($this->_class_name, 'actionJoinGroupPermission');

        $content = $gform->open($form_action, false, '', array('form_id'=>'j_groupperm', 'validation'=>false));
        $content .= \Gino\Input::hidden('id', $obj_group->id);
        $content .= $this->formPermission($checked);

        $content .= \Gino\Input::input('submit', 'submit', _("associa"));
        $content .= $gform->close();

        $dict = array(
            'title' => sprintf(_("Gruppo \"%s\" - permessi"), $obj_group),
            'content' => $content
        );

        $view = new \Gino\View();
        $view->setViewTpl('section');

        return $view->render($dict);
    }

    /**
     * @brief Processa il form di associazione gruppo-permessi
     * 
     * Parametri POST: \n
     *   - id (integer), valore ID del gruppo
     *   - perm (array), permessi selezionati
     *
     * @param \Gino\Http\Request $request
     * @return Gino.Http.Redirect
     */
    public function actionJoinGroupPermission(\Gino\Http\Request $request) {

        
    	$this->requirePerm('can_admin');
    	
    	$id = \Gino\cleanVar($request->POST, 'id', 'int', '');
    	
    	if(!$id) {
    		throw new \Gino\Exception\Exception404();
    	}

        $perm = array_key_exists('perm', $request->POST) ? $request->POST['perm'] : array();

        $obj_group = new Group($id);
        $existing_perms = $obj_group->getPermissions();

        if(is_array($perm) && count($perm))
        {
            $array_delete = array_diff($existing_perms, $perm);

            // Valori da eliminare
            if(count($array_delete))
            {
                foreach($array_delete AS $value)
                {
                    $split = Group::getMergeValue($value);

                    $permission_id = $split[0];
                    $instance_id = $split[1];

                    $this->_db->delete(Group::$table_group_perm, "group_id='$id' AND instance='$instance_id' AND perm_id='$permission_id'");
                }
            }

            // Valori da aggiungere
            foreach($perm AS $value)
            {
                if(!in_array($value, $existing_perms))
                {
                    $split = Group::getMergeValue($value);

                    $permission_id = $split[0];
                    $instance_id = $split[1];

                    $this->_db->insert(array('instance'=>$instance_id, 'group_id'=>$id, 'perm_id'=>$permission_id), Group::$table_group_perm);
                }
            }
        }
        else    // elimina tutto
        {
            $this->_db->delete(Group::$table_group_perm, "group_id='$id'");
        }

        return $this->returnJoinLink('group', 'jgp', $id);
    }

    /**
     * @brief Imposta il multicheckbox sui permessi
     * 
     * @param array $checked
     * @return string, multicheck input
     */
    private function formPermission($checked=array()) {

        $perm = Permission::getList();

        $a_checked = array();

        if(count($perm))
        {
            $items = array();

            foreach ($perm AS $p)
            {
                $perm_id = $p['perm_id'];
                $perm_label = $p['perm_label'];
                $perm_descr = $p['perm_descr'];
                $mod_name = $p['mod_name'];
                $mod_label = $p['mod_label'];
                $inst_id = (int) $p['inst_id'];

                $merge = User::setMergeValue($perm_id, $inst_id);
                if(in_array($merge, $checked))
                    $a_checked[] = $merge;

                $description = _("Modulo").": $mod_name";
                if($mod_label) $description .= " ($mod_label)";

                $description .= "<br />$perm_label ($perm_descr)";

                $items[$merge] = $description;
            }
        }

        $content = \Gino\Input::multipleCheckbox('perm[]', $a_checked, $items, '', null);

        return $content;
    }

    /**
     * @brief Imposta il multicheckbox sui gruppi
     * 
     * @see Gino.App.Auth.Group::getList()
     * @see Gino.Form::multipleCheckbox()
     * @param \Gino\Form $obj_form istanza di Gino.Form
     * @param array $checked array di id di permessi selezionati
     * @return string, multicheck input
     */
    private function formGroup($obj_form, $checked=array()) {

        $group = Group::getList();
        $items = array();

        $a_checked = array();

        if(count($group))
        {
            foreach($group AS $g)
            {
                $group_id = $g['id'];
                $group_name = $g['name'];
                $group_description = $g['description'];

                if(in_array($group_id, $checked))
                    $a_checked[] = $group_id;

                $description = $group_name;
                if($group_description) $description .= " ($group_description)";

                $items[$group_id] = $description;
            }
        }

        $content = $obj_form->multipleCheckbox('group[]', $a_checked, $items, _('Gruppi'), null);

        return $content;
    }

    /**
     * @brief Pagina di login
     * @see Gino.Access::Authentication()
     * @return Gino.Http.Response
     */
    public function login(\Gino\Http\Request $request){

    	// Ridefinizione dell'indirizzo di reindirizzamento (a seguito del login) quando questo indirizzo corrisponde 
    	// all'area amministrativa e l'utente non ha i permessi per accedervi
    	if(isset($request->session->auth_redirect) &&
    		$request->user->id &&
    		!$request->user->hasPerm('core', 'is_staff') &&
    		$request->session->auth_redirect == $this->link('index', 'admin_page')) {
    			$request->session->auth_redirect = $request->root_absolute_url;
    	}
    	
    	$referer = isset($request->session->auth_redirect)
            ? $request->session->auth_redirect
            : ((isset($request->META['HTTP_REFERER']) and $request->META['HTTP_REFERER'])
                ? $request->META['HTTP_REFERER']
                : $this->_home);
        $request->session->auth_redirect = $referer;

        if($request->user->id) {
            $redirect = $request->session->auth_redirect != $this->_registry->router->link($this->_class_name, 'login')
                ? $request->session->auth_redirect
                : $this->_home;

            return new \Gino\Http\Redirect($redirect);
        }

        if(isset($_POST['submit_login']))
        {
            if($this->_access->Authentication()) exit();
            else return Error::errorMessage(array('error'=>_("Username o password non valida")), $link_interface);
        }
		
        $mform = \Gino\Loader::load('ModelForm', array(new User(null), array('fields'=>array('username', 'userpwd'))));
        
        $form = $mform->view(
        	array(
        		'form_id' => 'login',
        	    'form_class' => 'myform',
        		'show_save_and_continue' => false,
        		'view_info' => false,
        		'view_title' => false,
        		'addCell' => array('username'=>array('name'=>'action', 'field'=>\Gino\Input::hidden('action', 'auth'))),
        		's_name' => 'submit_login',
        		's_value' => _("login")
        	),
        	array('username'=>array('trnsl'=>false), 'userpwd'=>array('trnsl'=>false))
        );
        
        $view = new \Gino\View($this->_view_dir, 'login');
        $dict = array(
            'form' => $form,
            'title' => _('Login')
        );

        $document = new \Gino\Document($view->render($dict));
        return $document();
    }

    /**
     * Pagina di recupero username e password
     *
     * @param \Gino\Http\Request $request
     * @return Gino.Http.Response
     */
    public function dataRecovery(\Gino\Http\Request $request)
    {
        $code = \Gino\cleanVar($request->GET, 'code', 'string');
        $email = \Gino\cleanVar($request->GET, 'email', 'string');

        $headers = "From: ".$this->_registry->sysconf->email_from_app . "\n";
        $headers .= 'MIME-Version: 1.0'."\n";
        $headers .= 'Content-type: text/plain; charset=utf-8'."\n";

        // recupero password
        if($code and $email) {
            $user = User::getFromEmail($email);
            $check_code = $code === md5($user->id . $user->username . $user->email . $user->date . date('Ym'));
            if($check_code and $user) {
                $password = User::generatePassword(array('aut_password_length' => 8));
                $user->userpwd = User::setPassword($password);
                $user->save();
                $mail_object = sprintf(_('Recupero credenziali di accesso | %s'), $this->_registry->sysconf->head_title);
                $mail_message = sprintf(_("Le tue nuove credenziali di accesso al sito %s sono:\nusername: %s\npassword:%s\nTi ricordiamo che potrai modificare la password generata automaticamente nella tua pagina del profilo:\n%s"), $this->_registry->sysconf->head_title, $user->username, $password, $this->_registry->router->link($this->_class_name, 'profile', array(), '', array('abs' => TRUE)));
                mail($email, $mail_object, $mail_message, $headers);

                return new \Gino\Http\Redirect($this->_registry->router->link($this->_class_name, 'dataRecoverySuccess'));
            }
            else {
                throw new \Gino\Exception\Exception404();
            }
        }
        // invio mail per richiesta
        else {

            if($request->method == 'POST') {
                $formobj = Loader::load('Form', array());	// array('form_id'=>'data_recovery')
                if($formobj->checkRequired()) {
                    return \Gino\Error::errorMessage(array('error' => 1), $this->_registry->router->link($this->_class_name, 'dataRecovery'));
                }

                $email = \Gino\cleanVar($request->POST, 'email', 'string');
                $user = User::getFromEmail($email);

                if($user) {
                    $error = FALSE;
                    $code = md5($user->id . $user->username . $user->email . $user->date . date('Ym'));

                    $mail_object = sprintf(_('Recupero credenziali di accesso | %s'), $this->_registry->sysconf->head_title);
                    $mail_message = sprintf(_("Hai ricevuto questa email perché hai richiesto la procedura di recupero credenziali di accesso al sito %s.\nSe non fossi stato tu ignorala, altrimenti per procedere al recupero, segui il link qui sotto entro il mese corrente:\n%s"), $this->_registry->sysconf->head_title, $this->_registry->router->link($this->_class_name, 'dataRecovery', array('email' => $email, 'code' => $code), '', array('abs' => TRUE)));
                    mail($email, $mail_object, $mail_message, $headers);
                }
                else {
                    $error = TRUE;
                }

                $view = new \Gino\View($this->_view_dir, 'data_recovery_request_processed');

                $dict = array(
                    'error' => $error
                );
                $document = new Document($view->render($dict));
                return $document();

            }
            else {
                $view = new \Gino\View($this->_view_dir, 'data_recovery_request');
                
                $formobj = Loader::load('Form', array());
                $form = $formobj->open('', TRUE, 'email', array('form_id'=>'data_recovery'));
                $form .= \Gino\Input::input_label('email', 'email', '', _('Indirizzo email'), array('required' => TRUE));
                $form .= \Gino\Input::input_label('submit_data_recovery', 'submit', _('invia'), '', array());
                $form .= $formobj->close();

                $dict = array(
                    'form' => $form
                );
                $document = new Document($view->render($dict));
                return $document();
           }
        }

    }

    public function dataRecoverySuccess(\Gino\Http\Request $request) {

        $view = new \Gino\View($this->_view_dir, 'data_recovery_success');
        $dict = array(
            'profile_url' => $this->_registry->router->link($this->_class_name, 'profile')
        );
        $document = new Document($view->render($dict));
        return $document();
    }
    
    /**
     * @brief Json of login credentials
     * @description Create the jwt payload
     *
     * @param \Gino\Http\Redirect $redirect istanza di Gino.Http.Redirect
     * @return Gino.Http.ResponseJson
     */
    public function getJsonData(\Gino\Http\Request $request) {
        
        // Verifica se le credenziali inserite nel login sono corrette
        // Richiama Gino.Access::AuthenticationMethod() che imposta le variabili di sessione in gino
        $check = $this->_access->CheckLogin($request);
        
        \Gino\Loader::import('class/http', '\Gino\Http\ResponseJson');
        
        if($check) {
            $user = new User($this->_session->user_id);
            
            require_once(PLUGIN_DIR.OS.'plugin.jwt.php');
            $plugin_jwt = new \Gino\Plugin\plugin_jwt();
            $jwt_token = $plugin_jwt->JWT_encode($user);
            
            $data = [
                'userData' => [
                    'userId' => $user->id,
                    'userName' => $user->firstname.' '.$user->lastname,
                    'userEmail' => $user->email,
                    'isSuperUser' => $user->is_admin
                ],
                'tokenData' => $jwt_token
            ];
            
            return new \Gino\Http\ResponseJson($data);
        }
        else {
            return new \Gino\Http\ResponseJson(
                ['error' => "login failed"],
                ['status' => ['code' => 400, 'text' => 'Bad Request: invalid credentials']]
            );
        }
    }
    
    /**
     * @brief Ritorna i dati dell'utente che effettua la richiesta
     *
     * @param \Gino\Http\Redirect $redirect istanza di Gino.Http.Redirect
     * @return Gino.Http.ResponseJson
     */
    public function whoami(\Gino\Http\Request $request) {
        
        $jwt = \Gino\cleanVar($request->GET, 'jwt', 'string');
        if($jwt == 'undefined' or $jwt=='null') {
            $jwt = '';
        }
        
        \Gino\Loader::import('class/http', '\Gino\Http\ResponseJson');
        $data = [];
        
        if($jwt) {
            
            require_once(PLUGIN_DIR.OS.'plugin.jwt.php');
            $plugin_jwt = new \Gino\Plugin\plugin_jwt();
            $token = $plugin_jwt->JWT_decode($jwt);
            
            if($plugin_jwt->verifyToken($token)) {
                $data = [
                    'userData' => [
                        'userId' => $token->data->userId,
                        'userName' => $token->data->userName,
                        'userEmail' => $token->data->userEmail,
                        'isSuperUser' => $token->data->isSuperUser
                    ],
                    'tokenData' => $jwt
                ];
            }
        }
        
        if(count($data)) {
            return new \Gino\Http\ResponseJson($data);
        }
        else {
            return new \Gino\Http\ResponseJson(
                ['error' => "invalid token"],
                ['status' => ['code' => 400, 'text' => 'Bad Request: invalid token']]
            );
        }
    }
    
    /**
     * @brief Elimina la sessione se è verificato il Token
     *
     * @param \Gino\Http\Redirect $redirect istanza di Gino.Http.Redirect
     * @return Gino.Http.ResponseJson
     */
    public function deleteToken(\Gino\Http\Request $request) {
        
        $jwt = \Gino\cleanVar($request->GET, 'jwt', 'string');
        
        \Gino\Loader::import('class/http', '\Gino\Http\ResponseJson');
        
        if($jwt && $jwt != 'undefined') {
            
            require_once(PLUGIN_DIR.OS.'plugin.jwt.php');
            $plugin_jwt = new \Gino\Plugin\plugin_jwt();
            $token = $plugin_jwt->JWT_decode($jwt);
            
            if($plugin_jwt->verifyToken($token)) {
                
                $this->_session->destroy();
                if(isset($this->_session->user_id) && $this->_session->user_id) {
                    $delete = false;
                }
                else {
                    $delete = true;
                }
            }
            else {
                $delete = false;
            }
            
            $data = [
                'deleteSession' => (int)$delete
            ];
            
            return new \Gino\Http\ResponseJson($data);
        }
        else {
            return new \Gino\Http\ResponseJson(
                ['error' => "undefined token"],
                ['status' => ['code' => 400, 'text' => 'Bad Request: undefined token']]
            );
        }
    }
    
    /**
     * @brief Ritorna il Token
     *
     * @param \Gino\Http\Redirect $redirect istanza di Gino.Http.Redirect
     * @return Gino.Http.ResponseJson
     */
    public function refreshToken(\Gino\Http\Request $request) {
        
        $token = \Gino\cleanVar($request->POST, 'token', 'string');
        
        if($token && $token != 'undefined') {
            $data = ['tokenData' => $token];
        }
        else {
            $data = [];
        }
        
        \Gino\Loader::import('class/http', '\Gino\Http\ResponseJson');
        return new \Gino\Http\ResponseJson($data);
    }
}
