<?php
/**
 * @file class.access.php
 * @brief Contiene la classe Access
 *
 * @copyright 2005 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */

/**
 * @brief Classe per la gestione dell'autenticazione ed accesso alla funzionalità
 * 
 * La classe gestisce il processo di autenticazione e l'accesso al sito e alle sue funzionalità
 * 
 * @copyright 2005 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class Access {
  
  public $default_role;
  
  protected $_home;
  protected $_log_access, $_username_email;
  protected $_crypt;
  protected $_db, $session;
  protected $_session_user, $_session_role, $_access_admin;

  private $_block_page;

  /**
   * Costruttore
   */
  function __construct(){

    $this->_db = db::instance();
    $this->session = session::instance();

    $this->_home = HOME_FILE;
    $this->_crypt = pub::getConf('password_crypt');
    
    $this->_log_access = pub::getConf('log_access');
    
    $this->_block_page = $this->_home."?evt[index-auth_page]";
  }

  /**
   * Autenticazione all'applicazione
   * 
   * @see AuthenticationMethod()
   * @see loginSuccess()
   * @see loginError()
   * @return void
   * 
   * Parametri POST: \n
   * - action (string)
   * - user (string)
   * - pwd (string)
   */
  public function Authentication(){

    loader::import('auth', 'User');
    
    if((isset($_POST['action']) && $_POST['action']=='auth')) {
      $user = cleanVar($_POST, 'user', 'string', '');
      $password = cleanVar($_POST, 'pwd', 'string', '');
      $this->AuthenticationMethod($user, $password) ? $this->loginSuccess() : $this->loginError(_("autenticazione errata"));
    }
    elseif((isset($_GET['action']) && $_GET['action']=='logout')) {
      
      $this->session->destroy();
      header("Location: ".$this->_home."?logout");
    }
    else {
      $registry = registry::instance();
      if(isset($this->session->user_id)) {
        loader::import('auth', 'User');
        $registry->user = new User($this->session->user_id);
      }
      else {
        $registry->user = new User(null);
      }
    }
  }
  
  private function loginError($message) {

    $self = $_SERVER['PHP_SELF'].($_SERVER['QUERY_STRING'] ? "?".$_SERVER['QUERY_STRING']:'');

    exit(error::errorMessage(array('error'=>$message), $self));
  }

  private function loginSuccess() {

    $self = $_SERVER['PHP_SELF'].($_SERVER['QUERY_STRING'] ? "?".$_SERVER['QUERY_STRING']:'');
    $referer = $_SERVER['HTTP_REFERER'] ? $_SERVER['HTTP_REFERER'] : $this->_home;

    $redirect = isset($this->session->auth_redirect)
        ? $this->session->auth_redirect
        : $referer;

    header("Location: ".$redirect);
  }

  /**
   * Verifica utente/password
   * 
   * Imposta le variabili di sessione user_id, userName, userRole e richiama il metodo logAccess()
   * 
   * @see pub::cryptMethod()
   * @see verifyRole()
   * @see logAccess()
   * @param string $user
   * @param string $pwd
   * @return boolean
   */
  private function AuthenticationMethod($user, $pwd){

    $user = User::getFromUserPwd($user, $pwd);
    if($user) {
      $this->session->user_id = $user->id;
      $this->session->user_name = htmlChars($user->firstname.' '.$user->lastname);
      if($this->_log_access == 'yes') {
        $this->logAccess($user->id);
      }
      return true;
    }

    return false;

  }
  
  /**
   * Registra il log dell'accesso all'applicazione
   * 
   * @param integer $userid
   * @return boolean
   */
  private function logAccess($userid) {
    
    date_default_timezone_set('Europe/Rome');

    $date = date("Y-m-d H:i:s");
    
    $result = $this->_db->insert(array('user_id'=>$userid, 'date'=>$date), TBL_LOG_ACCESS);
    
    return $result;
  }


  /**
   * Accesso all'area amministrativa
   * 
   * @return boolean
   */
  public function getAccessAdmin() {

    $registry = registry::instance();
    // no logged user
    if(!$registry->user->id) {
      return false;
    }

    return $registry->user->is_admin or $registry->user->is_staff;

  }

  /**
   * Raise 403 se l'utente non è amministratore
   */
  public function requireAdmin() {

    $registry = registry::instance();
    if(!$registry->user->is_admin) {
      Error::raise403();
    }

  }

  /**
   * Raise 403 se l'utente non ha almeno uno dei permessi dati
   */
  public function requirePerm($class, $perm, $instance = 0) {

    $registry = registry::instance();
    if(!$registry->user->hasPerm($class, $perm, $instance)) {
      Error::raise403();
    }

  }
  
  /**
   * Controlla se il ruolo esiste
   * 
   * @param integer $role_id
   * @return boolean
   */
  private function verifyRole($role_id){
    
    $check = $this->_db->getFieldFromId(TBL_USER_ROLE, 'role_id', 'role_id', $role_id);
    if($check)
      return true;
    else
      return false;
  }
  
  
  /**
   * Form di autenticazione
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

  /**
   * Ricava il valore ID di un ruolo di una classe
   * 
   * @param string $class_name
   * @param string $field
   * @param integer $instance
   * @return integer
   */
  private function queryClassRole($class_name, $field, $instance){
    
    $value = '';
    
    if($instance)
      $records = $this->_db->select($field, TBL_MODULE, "id='$instance' AND class='$class_name'");
    else
      $records = $this->_db->select($field, TBL_MODULE_APP, "name='$class_name' AND type='class'");
    
    if(count($records))
    {
      foreach($records AS $r)
      {
        $value = $r[$field];
      }
    }
    
    return $value;
  }
  
  /**
   * Restituisce l'ID del ruolo di una data classe
   * 
   * @see queryClassRole()
   * @param string $class_name nome della classe
   * @param integer $instance valore ID dell'istanza
   * @param string $role riferimento per la ricerca del ruolo (user, user2, user3)
   * @return integer
   */
  public function classRole($class_name, $instance, $role){

    $value = '';
    
    if($role == 'user' || $role == 'user2' || $role == 'user3') {

      $field = $role=='user' ? "role1":($role=='user2'?"role2":"role3");
      $value = $this->queryClassRole($class_name, $field, $instance);
    }
    
    if(empty($value))
      exit(error::syserrorMessage("Auth", "classRole", _("impossibile leggere i privilegi di accesso per la classe ").$class_name._(" istanza ").$instance, __LINE__));
    
    return $value;
  }
  
  /**
   * Verifica del ruolo utente per l'accesso a una pagina di classe
   * 
   * Viene utilizzata nel metodo accessType()
   * 
   * @param integer $role valore ID del ruolo (es. proprietà @a _access_base)
   * @return boolean
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
   * @param integer $role valore ID del ruolo (es. proprietà @a _access_global)
   * @return boolean
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
   * Verifica del ruolo utente per l'accesso a una pagina
   * 
   * Con una risposta negativa redirige a una pagina di errore. Viene verificato il campo role1.
   * 
   * @see userRole()
   * @param integer $module_id valore ID del modulo
   * @return boolean o redirect
   */
  public function AccessVerifyPage($module_id){

    $records = $this->_db->select('role1', TBL_MODULE, "id='$module_id' AND type='page'");
    if(count($records))
    {
      foreach($records AS $r)
      {
        $role = $r['role1'];
      }
    }
    
    if(empty($role))
    {
      header("Location:http://".$this->_url_path_login."&err=ERROR: system error 1");
      exit();
    }
    
    /*if(!$role OR !$this->verifyRole($role))
    {
      header("Location:http://".$this->_url_path_login."&err=ERROR: no access 3");
      exit();
    }*/
    
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
  
  /**
   * Verifica del ruolo utente per l'accesso ai contenuti di una pagina
   * 
   * Con una risposta negativa non mostra i contenuti. Viene verificato il campo role1.
   * 
   * @see userRole()
   * @param integer $module_id valore ID del modulo
   * @return boolean
   */
  public function AccessVerifyPageIf($module_id){

    $records = $this->_db->select('role1', TBL_MODULE, "id='$module_id' AND type='page'");
    if(count($records))
    {
      foreach($records AS $r)
      {
        $role = $r['role1'];
      }
    }
    
    if(empty($role)) return false;
    
    if($role >= $this->userRole()) return true; else return false;
  }
  
  /*
    Gruppi
  */
  
  private function referenceTable($class_name){
    
    return TBL_MODULE_APP;
  }
  
  private function blockUser($message, $redirect, $options) {
  
    if(isset($options['logout']) && $options['logout']===true) {
      
      $this->session->destroy();
      $this->session->startSession(SESSION_NAME);
    }

    exit(error::errorMessage(array('error'=>$message), $redirect));
  }
  
  /**
   * Restituisce il gruppo amministratore di una classe
   *
   * @param string $class_name nome della classe
   * @return integer
   */
  public function adminGroup($class_name){
    
    $table = $this->referenceTable($class_name);
    
    $records = $this->_db->select('role_group', $table, "name='$class_name' AND type='class'");
    if(count($records))
    {
      foreach($records AS $r)
      {
        $role_group = $r['role_group'];
      }
    }
    else $role_group = '';
    
    return $role_group;
  }
  
  /**
   * Verifica se un utente è l'amministratore di una classe
   *
   * @param string $class_name nome della classe
   * @param integer $instance valore ID dell'istanza
   * @return boolean
   */
  public function AccessAdminClass($class_name, $instance){
    
    if(empty($class_name)) return false;
    
    $control = false;
    $table = $this->referenceTable($class_name);
    
    $records = $this->_db->select('role_group, tbl_name', $table, "name='$class_name' AND type='class'");
    if(count($records))
    {
      foreach($records AS $r)
      {
        $tbl_group = $r['tbl_name'].'_grp';
        $tbl_user = $r['tbl_name'].'_usr';
        $role_group = $r['role_group'];
      }
    }
    
    if($this->_db->tableexists($tbl_group) AND $this->_db->tableexists($tbl_user))
    {
      $records = $this->_db->select('user_id', $tbl_user, "group_id='$role_group' AND user_id='".$this->_session_user."' AND instance='$instance'");
      if(count($records))
        $control = true;
    }
    return $control;
  }
  
  /**
   * Elenco dei gruppi di una classe
   * 
   * @param string $class_name nome della classe
   * @return array
   */
  public function listGroup($class_name){
    
    $group = array();
    $table = $this->referenceTable($class_name);
    
    $records = $this->_db->select('tbl_name', $table, "name='$class_name' AND type='class'");
    if(count($records))
    {
      foreach($records AS $r)
      {
        $tbl_group = $r['tbl_name'].'_grp';
      }
    }
    
    if($this->_db->tableexists($tbl_group))
    {
      $records = $this->_db->select('id', $tbl_group, '', array('order'=>'id ASC'));
      if(count($records))
      {
        foreach($records AS $r)
        {
          $group[] = $r['id'];
        }
      }
    }
    return $group;
  }
  
  /**
   * Elenco dei gruppi di un utente in riferimento a una data classe
   *
   * @param string $class_name nome della classe
   * @param integer $instance valore ID dell'istanza
   * @param boolean $no_admin indica se il il gruppo ha funzionalità amministrative (true -> non ha funzionalità amministrative) 
   * @return array
   */
  public function userGroup($class_name, $instance=null, $no_admin = true){
    
    $group = array();
    $table = $this->referenceTable($class_name);
    
    $records = $this->_db->select('tbl_name', $table, "name='$class_name'");
    if(count($records))
    {
      foreach($records AS $r)
      {
        $tbl_group = $r['tbl_name'].'_grp';
        $tbl_user = $r['tbl_name'].'_usr';
      }
    }
    
    if($this->_db->tableexists($tbl_group) AND $this->_db->tableexists($tbl_user))
    {
      if($no_admin)
        $records = $this->_db->select('group_id', $tbl_user, "user_id='".$this->_session_user."' AND instance='$instance'");
      else
        $records = $this->_db->select('group_id', "$tbl_user AS u, $tbl_group AS g", "u.user_id='$this->_session_user' AND u.instance='$instance' AND u.group_id=g.id AND g.no_admin='no'");
      
      if(count($records))
      {
        foreach($records AS $r)
        {
          $group[] = $r['group_id'];
        }
      }
    }
    return $group;
  }
  
  /**
   * Verifica l'appartenenza di un utente ad almeno un gruppo di una data classe
   * 
   * @see metodo accessGroup(), classe AbstractEvtClass
   * @param string $class_name nome della classe
   * @param integer $instance valore ID dell'istanza
   * @param array $user_group gruppi ai quali può accedere l'utente
   * @param mixed $permission gruppi ai quali è concesso l'accesso a una determinata funzione
   * @return boolean
   * 
   * @code
   * $this->accessGroup($this->_group_1);	// accesso a gruppi selezionati
   * $this->accessGroup('ALL');	// accesso a tutti i gruppi della classe
   * $this->accessGroup('');		// amm. sito + amm. classe
   * @endcode
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
   * Controlla se un utente può accedere a porzioni di codice
   *
   * @param string $class_name nome della classe
   * @param integer $instance valore ID dell'istanza
   * @param array $user_group gruppi ai quali è associato l'utente
   * @param mixed $permission gruppi che possiedono i permessi
   * @return boolean
   * 
   * @code
   * $this->_access->AccessVerifyGroupIf($this->_className, $this->_instance, $this->_user_group, $this->_group_1)
   * $this->_access->AccessVerifyGroupIf($this->_className, $this->_instance, '', 'ALL')
   * $this->_access->AccessVerifyGroupIf($this->_className, $this->_instance, '', '')	// amm. sito + amm. classe
   * @endcode
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

  /**
   * Verifica dell'accesso
   * @return void
   */
  public function AccessVerify(){

    if(empty($this->_session_user))
    {
      header("Location:http://".$this->_url_path_login."&err=ERROR: no access 6");
      exit();
    }
  }

  /**
   * Verifica dell'accesso
   * @return boolean
   */
  public function AccessVerifyIf(){

    if(empty($this->_session_user)) return false; else return true;
  }


  /**
   * Elenco dei ruoli di gino
   * @return array
   */
  public function listRole(){

    $role_type = array(); $role_name = array();

    $records = $this->_db->select('role_id, name', TBL_USER_ROLE, '', array('order'=>'role_id'));
    if(count($records))
    {
      foreach($records AS $r)
      {
        $role_type[] = $r['role_id'];
        $role_name[] = $r['name'];
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
