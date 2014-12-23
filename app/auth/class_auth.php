<?php
/**
 * @file class_auth.php
 * @brief Contiene la definizione ed implementazione della classe Gino.App.Auth.auth
 *
 * @copyright 2013-2014 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
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

require_once(CLASSES_DIR.OS.'class.AdminTable.php');
require_once('class.AdminTable_AuthUser.php');

/**
 * @brief Classe di tipo Gino.Controller per la gestione degli utenti, gruppi e permessi
 *
 * I permessi delle applicazioni sono definiti nella tabella @a auth_permission. Il campo @a admin indica se il permesso necessita dell'accesso all'area amministrativa. \n
 * Ogni utente può essere associato a un permesso definito nella tabella @a auth_permission, e tale associazione viene registrata nella tabella @a auth_user_perm. \n
 * La tabella @a auth_user_perm registra il valore ID dell'utente, del permesso e dell'istanza relativa all'applicazione del permesso. \n
 * Questo implica che nell'interfaccia di associazione utente/permessi occorre mostrare i permessi relativi a ogni applicazione (classe) per tutte le istanze presenti.
 *
 * I gruppi sono definiti nella tabella @a auth_group. I gruppi possono essere associati ai permessi e alle istanze (auth_group_perm) e gli utenti ai gruppi (auth_group_user).
 * @copyright 2013-2014 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class auth extends \Gino\Controller {

    private $_options;
    public $_optionsLabels;

    private $_title;
    private $_users_for_page;
    private $_user_more, $_user_view;
    private $_self_registration, $_self_registration_active;
    private $_username_as_email;
    private $_aut_pwd, $_aut_pwd_length, $_pwd_length_min, $_pwd_length_max, $_pwd_numeric_number;
    private $_ldap_auth, $_ldap_auth_only, $_ldap_single_user, $_ldap_auth_password;

    public $_other_field1, $_other_field2, $_other_field3;
    private $_label_field1, $_label_field2, $_label_field3;

    /**
     * @brief Costruttore
     * @return istanza di Gino.App.Auth.auth
     */
    function __construct(){

        parent::__construct();

        $this->_instance = 0;
        $this->_instanceName = $this->_class_name;

        $this->_title = \Gino\htmlChars($this->setOption('title', true));
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
        
        $this->_ldap_auth = $this->setOption('ldap_auth');
        $this->_ldap_auth_only = $this->setOption('ldap_auth_only');
        $this->_ldap_single_user = $this->setOption('ldap_single_user');
        $this->_ldap_auth_password = $this->setOption('ldap_auth_password');

        $this->_options = \Gino\Loader::load('Options', array($this));
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
            "pwd_numeric_number"=>_("Caratteri numerici password"), 
        	"ldap_auth"=>array(
        		'label'=>_("Attivazione Ldap"), 
        		'required'=>true, 
        		'section'=>true,
                'section_title'=>_('Ldap'),
                'section_description'=> "<p>"._("Opzioni per la procedura di autenticazione Ldap; per impostare i parametri di connessione al database editare il file config.ldap.php.")."</p>",
        	),
            "ldap_auth_only"=>array(_("Autenticazione esclusiva Ldap"), _("se l'autenticazione non è esclusiva viene verificata prima la validità dell'utente nel database Ldap e in caso negativo in quello di gino")),
            "ldap_single_user"=>array('label'=>_("Utente unico di gino per tutti gli utenti ldap"), 'trnsl'=>false, 'required'=>false),
            "ldap_auth_password"=>array('label'=>_("Password dell'utente/utenti di gino abbinati agli utenti ldap"), 'trnsl'=>false, 'required'=>false), 
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
                'auth_user_registration'
            ),
            'views' => array(
                'login.php' => _('Login area privata/amministrativa')
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
     * @return percorso
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
     * @return path directory
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
     * @brief Interfaccia di amministrazione modulo
     * @param \Gino\Http\Request istanza di Gino.Http.Request
     * @return Gino.Http.Response
     */
    public function manageAuth(\Gino\Http\Request $request) {

        $this->requirePerm('can_admin');

        $block = \Gino\cleanVar($request->GET, 'block', 'string', null);
        $op = \Gino\cleanVar($request->GET, 'op', 'string', null);

        $link_frontend = sprintf('<a href="%s">%s</a>', $this->linkAdmin(array(), 'block=frontend'), _('Frontend'));
        $link_options = sprintf('<a href="%s">%s</a>', $this->linkAdmin(array(), 'block=options'), _('Opzioni'));
        $link_group = sprintf('<a href="%s">%s</a>', $this->linkAdmin(array(), 'block=group'), _('Gruppi'));
        $link_perm = sprintf('<a href="%s">%s</a>', $this->linkAdmin(array(), 'block=perm'), _('Permessi'));
        $link_dft = sprintf('<a href="%s">%s</a>', $this->linkAdmin(), _('Utenti'));
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

            if($op == 'jgp')
                $backend = $this->joinGroupPermission();
            else
                $backend = $this->manageGroup();

            $sel_link = $link_group;
        }
        elseif($block=='perm') {
            $backend = $this->managePermission();
            $sel_link = $link_perm;
        }
        elseif($block=='password') {
            $backend = $this->changePassword($request);
            $sel_link = $link_dft;
        }
        else {
            if($op == 'jup')
                $backend = $this->joinUserPermission($request);
            else
                $backend = $this->manageUser($request);
        }

        if(is_a($backend, '\Gino\Http\Response')) {
            return $backend;
        }

        $dict = array(
            'title' => _('Utenti di sistema'),
            'links' => array($link_frontend, $link_options, $link_perm, $link_group, $link_dft),
            'selected_link' => $sel_link,
            'content' => $backend
        );

        $view = new View(null, 'tab');
        $view->setViewTpl('tab');

        $document = new Document($view->render($dict));
        return $document();
    }

    /**
     * @brief Interfaccia di amministrazione utenti
     * @param \Gino\Http\Request istanza di Gino.Http.Request
     * @see AdminTable_AuthUser::backoffice()
     * @return html oppure \Gino\Http\Redirect
     * 
     * Nell'inserimento di un nuovo utente vengono effettuati i controlli con User::checkPassword() e User::checkUsername(). \n
     * In inserimento e modifica vengono effettuati i controlli con User::checkEmail() in AdminTable_AuthUser::modelAction().
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
            $url = $this->_home."?evt[".$this->_class_name."-checkUsername]";
            $onclick = "onclick=\"gino.ajaxRequest('post', '$url', 'username='+$('username').getProperty('value'), 'check')\"";
            $check = "<div id=\"check\" style=\"color:#ff0000;\"></div>\n";
            
            $gform = \Gino\Loader::load('Form', array('', '', ''));
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

        $admin_table = new AdminTable_AuthUser($this);

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
     * @return html
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
                return new Redirect($this->_registry->router->link($this->_class_name, 'manageAuth')
                );
            }
            else {
                return Error::errorMessage($action_result, $this->_registry->router->link($this->_class_name, 'manageAuth', "block=password&ref=$user_id"));
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

        $info = _("Elenco dei gruppi del sistema.");
        $link_button = $this->_home."?evt[".$this->_class_name."-manageAuth]&block=group";

        $opts = array(
            'list_display' => array('id', 'name', 'description'),
            'list_description' => $info, 
            'add_buttons' => array(
                array('label'=>\Gino\icon('permission', array('scale' => 1)), 'link'=>$link_button."&op=jgp", 'param_id'=>'ref')
            )
        );

        $admin_table = Loader::load('AdminTable', array(
            $this
        ));

        return $admin_table->backoffice('Group', $opts);
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
     * @return html
     */
    private function joinUserPermission(\Gino\Http\Request $request) {

        $id = \Gino\cleanVar($request->GET, 'ref', 'int', '');
        if(!$id) return null;

        $obj_user = new User($id);
        $checked = $obj_user->getPermissions();

        $gform = \Gino\Loader::load('Form', array('j_userperm', 'post', false));

        $form_action = $this->_home.'?evt['.$this->_class_name.'-actionJoinUserPermission]';

        $content = $gform->open($form_action, false, '');
        $content .= $gform->hidden('id', $obj_user->id);
        $content .= $this->formPermission($gform, $checked);

        $content .= $gform->input('submit', 'submit', _("associa"), null);
        $content .= $gform->close();

        $dict = array(
            'title' => sprintf(_("Utente \"%s\" - permessi"), $obj_user),
            'content' => $content
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

        $id = \gino\cleanvar($request->POST, 'id', 'int', '');

        if(!$id) {
            throw new \Gino\Exception\Exception404();
        }

        $perm = $request->POST['perm'];

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
                    $split = user::getmergevalue($value);

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
                    $split = user::getmergevalue($value);

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

        return $this->returnjoinlink('user', 'jup', $id);
    }

    /**
     * @brief Form di associazione gruppo-permessi
     * 
     * parametri get: \n
     *   - ref (integer), valore id del gruppo
     *
     * @return html, form
     */
    private function joingrouppermission() {

        // perm

        $id = \gino\cleanvar($_GET, 'ref', 'int', '');
        if(!$id) return null;

        $obj_group = new Group($id);
        $checked = $obj_group->getPermissions();

        $gform = \Gino\Loader::load('Form', array('j_groupperm', 'post', false));

        $form_action = $this->_home.'?evt['.$this->_class_name.'-actionJoinGroupPermission]';

        $content = $gform->open($form_action, false, '');
        $content .= $gform->hidden('id', $obj_group->id);
        $content .= $this->formPermission($gform, $checked);

        $content .= $gform->input('submit', 'submit', _("associa"), null);
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

        $id = \Gino\cleanVar($request->POST, 'id', 'integer', '');
        if(!$id) return null;

        $perm = $request->POST['perm'];

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

        $this->returnJoinLink('group', 'jgp', $id);
    }

    /**
     * @brief Imposta il multicheckbox sui permessi
     * @return html, multicheck
     */
    private function formPermission($obj_form, $checked=array()) {

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

        $content = $obj_form->multipleCheckbox('perm[]', $a_checked, $items, '', null);

        return $content;
    }

    /**
     * @brief Imposta il multicheckbox sui gruppi
     * 
     * @see Gino.App.Auth.Group::getList()
     * @see Gino.Form::multipleCheckbox()
     * @param \Gino\Form $obj_form istanza di Gino.Form
     * @param array $checked array di id di permessi selezionati
     * @return html, multicheck
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

        $referer = isset($request->session->auth_redirect)
            ? $request->session->auth_redirect
            : ((isset($request->META['HTTP_REFERER']) and $request->META['HTTP_REFERER'])
                ? $request->META['HTTP_REFERER']
                : $this->_home);
        $request->session->auth_redirect = $referer;

        if(isset($_POST['submit_login']))
        {
            if($this->_access->Authentication()) exit();
            else return error::errorMessage(array('error'=>_("Username o password non valida")), $link_interface);
        }

        $gform = \Gino\Loader::load('Form', array('login', 'post', true));

        $form = $gform->open('', FALSE, '');
        $form .= $gform->hidden('action', 'auth');

        $form .= $gform->cinput('user', 'text', '', _("Username"), array('size'=>30));
        $form .= $gform->cinput('pwd', 'password', '', _("Password"), array('size'=>30));

        $form .= $gform->cinput('submit_login', 'submit', _("login"), '', null);
        $form .= $gform->close();

        $view = new \Gino\View($this->_view_dir, 'login');
        $dict = array(
            'form' => $form,
            'title' => _('Login')
        );

        $document = new \Gino\Document($view->render($dict));
        return $document();
    }
}
