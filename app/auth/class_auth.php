<?php
/**
 * @file class_auth.php
 * @brief Contiene la classe auth
 * 
 * @copyright 2013 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
namespace Gino\App\Auth;

use \Gino\Loader;
use \Gino\View;
use \Gino\Http\Response;
use \Gino\Http\ResponseView;
use \Gino\Http\ResponseAjax;
use \Gino\Http\Redirect;

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
class auth extends \Gino\Controller {

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
            "pwd_numeric_number"=>_("Caratteri numerici password")
        );
    }

    /**
     * @brief Elenco dei metodi che possono essere richiamati dal menu e dal template
     * @return array
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
     * @return path directory
     */
    public function getAddPath($id) {

        if(!$id) $id = $this->_db->autoIncValue(User::$table);

        $directory = $id.OS;

        return $directory;
    }

    /**
     * @brief Interfaccia di amministrazione modulo
     * @param \Gino\Http\Request $request
     * @return \Gino\Http\Response
     */
    public function manageAuth(\Gino\Http\Request $request) {

        $this->requirePerm('can_admin');

        $block = \Gino\cleanVar($request->GET, 'block', 'string', null);
        $op = \Gino\cleanVar($request->GET, 'op', 'string', null);

        $link_frontend = "<a href=\"".$this->_home."?evt[$this->_class_name-manageAuth]&block=frontend\">"._("Frontend")."</a>";
        $link_options = "<a href=\"".$this->_home."?evt[$this->_class_name-manageAuth]&block=options\">"._("Opzioni")."</a>";
        $link_group = "<a href=\"".$this->_home."?evt[$this->_class_name-manageAuth]&block=group\">"._("Gruppi")."</a>";
        $link_perm = "<a href=\"".$this->_home."?evt[$this->_class_name-manageAuth]&block=perm\">"._("Permessi")."</a>";
        $link_dft = "<a href=\"".$this->_home."?evt[".$this->_class_name."-manageAuth]\">"._("Utenti")."</a>";
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
            $backend = $this->changePassword();
            $sel_link = $link_dft;
        }
        else {

            if($op == 'jug')
                $backend = $this->joinUserGroup($request);
            elseif($op == 'jup')
                $backend = $this->joinUserPermission();
            else
                $backend = $this->manageUser($request);
        }

        if(is_a($backend, '\Gino\HttpResponse')) {
            return $backend;
        }

        $dict = array(
            'title' => _('Utenti di sistema'),
            'links' => array($link_frontend, $link_options, $link_perm, $link_group, $link_dft),
            'selected_link' => $sel_link,
            'content' => $backend
        );

        $view = new \Gino\View(null, 'tab');
        $view->setViewTpl('tab');

        return new ResponseView($view, $dict);
    }

    /**
     * @brief Interfaccia di amministrazione utenti
     * @param \Gino\Http\Request $request
     * @see AdminTable_AuthUser::backoffice()
     * @return html || \Gino\Http\Redirect
     */
    private function manageUser(\Gino\Http\Request $request) {

        //Loader::import('class', '\Gino\AdminTable');

        $info = _("Elenco degli utenti del sistema.");
        $link_button = $this->_home."?evt[".$this->_class_name."-manageAuth]&block=user";

        $opts = array(
            'list_display' => array('id', 'firstname', 'lastname', 'email', 'active'),
            'list_description' => $info, 
            'add_buttons' => array(
                array('label'=>\Gino\icon('group', array('scale' => 1)), 'link'=>$link_button."&op=jug", 'param_id'=>'ref'), 
                array('label'=>\Gino\icon('permission', array('scale' => 1)), 'link'=>$link_button."&op=jup", 'param_id'=>'ref'), 
                array('label'=>\Gino\icon('password', array('scale' => 1)), 'link'=>$this->_home."?evt[".$this->_class_name."-manageAuth]&block=password", 'param_id'=>'ref')
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

        $id = \Gino\cleanVar($request->GET, 'id', 'int', '');
        $edit = \Gino\cleanVar($request->GET, 'edit', 'int', '');

        if($id && $edit)    // modify
        {
            $removeFields = array('username', 'userpwd');

            if($this->_username_as_email) $removeFields[] = 'email';

            $addCell = null;
        }
        else
        {
            $url = "$this->_home?evt[".$this->_class_name."-checkUsername]";
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
            'pwd_numeric_number' => $this->_pwd_numeric_number
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
            )
        );

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
     * @brief Controlla se uno username è disponibile
     *
     * @param \Gino\Http\Request $request
     * @return \Gino\Http\Response
     */
    public function checkUsername(\Gino\Http\Request $request) {

        Loader::import('class/http', '\Gino\Http\ResponseAjax');

        $username = \Gino\cleanVar($request->POST, 'username', 'string', '');

        if(!$username) {
            return new ResponseAjax("<strong>"._("Inserire uno username!")."</strong>");
        }

        $check = $this->_db->getFieldFromId(User::$table, 'id', 'username', $username);
        $content = $check ? _("Username non disponibile!") : _("Username disponibile!");

        return new ResponseAjax("<strong>".$content."</strong>");
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
    private function changePassword() {

        // PERM ??

        if(isset($_POST['submit_action']))
        {
            $user_id = \Gino\cleanVar($_POST, 'id', 'int', '');
            $obj_user = new User($user_id);

            $action_result = $obj_user->savePassword(array(
                'pwd_length_min' => $this->_pwd_length_min, 
                'pwd_length_max' => $this->_pwd_length_max, 
                'pwd_numeric_number' => $this->_pwd_numeric_number
            ));

            if($action_result === true) {
                header("Location: ".$this->_home."?evt[".$this->_class_name."-manageAuth]");
                exit();
            }
            else {
                exit(error::errorMessage($action_result, $this->_home."?evt[".$this->_class_name."-manageAuth]&block=password&ref=$user_id"));
            }
        }

        $user_id = \Gino\cleanVar($_GET, 'ref', 'int', '');
        $change = \Gino\cleanVar($_GET, 'c', 'int', '');

        $obj_user = new User($user_id);

        $content = $obj_user->formPassword(array(
            'form_action'=>'', 
            'rules'=>$this->passwordRules($user_id), 
            'maxlength'=>$this->_pwd_length_max)
        );

        $title = sprintf(_('Modifica password "%s"'), $obj_user);

        $dict = array(
            'title' => $title,
            'content' => $content
        );

        $view = new \Gino\View();
        $view->setViewTpl('section');

        return $view->render($dict);
    }

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
     * Reindirizza le operazione di join tra utenti/gruppi/permessi
     * 
     * @param string $block
     * @param string $option
     * @param integer $ref_id valore ID del riferimento (utente o gruppo)
     * @return redirect
     */
    private function returnJoinLink($block, $option, $ref_id) {

        $link_interface = $this->_home."?evt[".$this->_class_name."-manageAuth]&block=$block&op=$option&ref=$ref_id";
        return new Redirect("http://".$_SERVER['HTTP_HOST'].$link_interface);
    }

    /**
     * Associazione utente-permessi
     * 
     * @see User::getPermissions()
     * @see formPermission()
     * @return string
     * 
     * Parametri GET: \n
     *   - ref (integer), valore ID dell'utente
     */
    private function joinUserPermission() {

        // PERM

        $id = \Gino\cleanVar($_GET, 'ref', 'int', '');
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

        $view = new \Gino\View();
        $view->setViewTpl('section');

        return $view->render($dict);
    }

    /**
     * Gestisce l'action dell'associazione degli utenti ai permessi
     * 
     * @see User::getPermissions()
     * @see User::getMergeValue()
     * @see returnJoinLink()
     * @return redirect
     * 
     * Parametri POST: \n
     *   - id (integer), valore ID dell'utente
     *   - perm (array), permessi selezionati
     */
    public function actionJoinUserPermission() {

        // PERM

        $id = \Gino\cleanVar($_POST, 'id', 'integer', '');
        if(!$id) return null;

        $perm = $_POST['perm'];

        $obj_user = new User($id);
        $existing_perms = $obj_user->getPermissions();

        if(is_array($perm) && count($perm))
        {
            $array_delete = array_diff($existing_perms, $perm);

            // Valori da eliminare
            if(count($array_delete))
            {
                foreach($array_delete AS $value)
                {
                    $split = User::getMergeValue($value);

                    $permission_id = $split[0];
                    $instance_id = $split[1];

                    $this->_db->delete(Permission::$table_perm_user, "user_id='$id' AND instance='$instance_id' AND perm_id='$permission_id'");
                }
            }

            // Valori da aggiungere
            foreach($perm AS $value)
            {
                if(!in_array($value, $existing_perms))
                {
                    $split = User::getMergeValue($value);

                    $permission_id = $split[0];
                    $instance_id = $split[1];

                    $this->_db->insert(array('instance'=>$instance_id, 'user_id'=>$id, 'perm_id'=>$permission_id), Permission::$table_perm_user);
                }
            }
        }
        else    // elimina tutto
        {
            $this->_db->delete(Permission::$table_perm_user, "user_id='$id'");
        }

        $this->returnJoinLink('user', 'jup', $id);
    }

    /**
     * Associazione gruppo-permessi
     * 
     * @see Group::getPermissions()
     * @see formPermission()
     * @return string
     * 
     * Parametri GET: \n
     *   - ref (integer), valore ID del gruppo
     */
    private function joinGroupPermission() {

        // PERM

        $id = \Gino\cleanVar($_GET, 'ref', 'int', '');
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
     * Gestisce l'action dell'associazione dei gruppi ai permessi
     * 
     * @see Group::getPermissions()
     * @see Group::getMergeValue()
     * @see returnJoinLink()
     * @return redirect
     * 
     * Parametri POST: \n
     *   - id (integer), valore ID del gruppo
     *   - perm (array), permessi selezionati
     */
    public function actionJoinGroupPermission() {

        // PERM

        $id = \Gino\cleanVar($_POST, 'id', 'integer', '');
        if(!$id) return null;

        $perm = $_POST['perm'];

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
     * @brief Associazione utente-gruppi
     * 
     * @param \Gino\Http\Request $request
     * @see User::getGroups()
     * @see formGroup()
     * @return string
     * 
     * Parametri GET: \n
     *   - ref (integer), valore ID dell'utente
     */
    private function joinUserGroup(\Gino\Http\Request $request) {

        // PERM

        $id = \Gino\cleanVar($request->GET, 'ref', 'int', '');
        if(!$id) return null;

        $obj_user = new User($id);
        $checked = $obj_user->getGroups();

        $gform = Loader::load('Form', array('j_usergroup', 'post', false));

        $form_action = $this->_home.'?evt['.$this->_class_name.'-actionJoinUserGroup]';

        $content = $gform->open($form_action, false, '');
        $content .= $gform->hidden('id', $obj_user->id);
        $content .= $this->formGroup($gform, $checked);

        $content .= $gform->input('submit', 'submit', _("associa"), null);
        $content .= $gform->close();

        $dict = array(
            'title' => sprintf(_('Utente "%s" - gruppi'), $obj_user),
            'content' => $content
        );

        $view = new View();
        $view->setViewTpl('section');

        return $view->render($dict);
    }

    /**
     * Gestisce l'action dell'associazione degli utenti ai gruppi
     * 
     * @see User::getGroups()
     * @see returnJoinLink()
     * @return redirect
     * 
     * Parametri POST: \n
     *   - id (integer), valore ID dell'utente
     *   - group (array), gruppi selezionati
     */
    public function actionJoinUserGroup() {

        // PERM

        $id = \Gino\cleanVar($_POST, 'id', 'integer', '');
        if(!$id) return null;

        $group = $_POST['group'];

        $obj_user = new User($id);
        $existing_groups = $obj_user->getGroups();

        if(is_array($group) && count($group))
        {
            $array_delete = array_diff($existing_groups, $group);

            // Valori da eliminare
            if(count($array_delete))
            {
                foreach($array_delete AS $value)
                {
                    $this->_db->delete(Group::$table_group_user, "user_id='$id' AND group_id='$value'");
                }
            }

            // Valori da aggiungere
            foreach($group AS $value)
            {
                if(!in_array($value, $existing_groups))
                {
                    $this->_db->insert(array('group_id'=>$value, 'user_id'=>$id), Group::$table_group_user);
                }
            }
        }
        else    // elimina tutto
        {
            $this->_db->delete(Group::$table_group_user, "user_id='$id'");
        }

        return $this->returnJoinLink('user', 'jug', $id);
    }

    /**
     * Imposta il multicheckbox sui permessi
     * 
     * @see Permission::getList()
     * @see User::setMergeValue()
     * @see Form::multipleCheckbox()
     * @param object $obj_form
     * @param array $checked
     * @return string
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
     * Imposta il multicheckbox sui gruppi
     * 
     * @see Group::getList()
     * @see Form::multipleCheckbox()
     * @param object $obj_form
     * @param array $checked
     * @return string
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
     * Pagina di autenticazione
     * 
     * @see Access::Authentication()
     * @return string
     */
    public function login(\Gino\Http\Request $request){

        $link_interface = $_SERVER['REQUEST_URI'];    // /git/gino/index.php?evt[auth-login]

        $link_interface = $this->_plink->convertLink($link_interface, array('vserver'=>'REQUEST_URI', 'pToLink'=>true, 'basename'=>true));

        $referer = isset($this->_registry->session->auth_redirect)
            ? $this->_registry->session->auth_redirect
            : ((isset($_SERVER['HTTP_REFERER']) and $_SERVER['HTTP_REFERER'])
                ? $_SERVER['HTTP_REFERER']
                : $this->_home);
        $request->session->auth_redirect = $referer;

        if(isset($_POST['submit_login']))
        {
            if($this->_access->Authentication()) exit();
            else exit(error::errorMessage(array('error'=>_("Username o password non valida")), $link_interface));
        }

        $gform = \Gino\Loader::load('Form', array('login', 'post', true));

        $form = $gform->open($link_interface, false, '');
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
        return new \Gino\Http\Response($view->render($dict));
    }
}
