<?php
/**
 * \file class.User.php
 * Contiene la definizione ed implementazione della classe User.
 * 
 * @version 1.0
 * @copyright 2013 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 * @authors Marco Guidotti guidottim@gmail.com
 * @authors abidibo abidibo@gmail.com
 */

/**
 * \ingroup auth
 * Classe tipo model che rappresenta un utente.
 *
 * @version 1.0
 * @copyright 2013 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 * @authors Marco Guidotti guidottim@gmail.com
 * @authors abidibo abidibo@gmail.com
 */
class User extends Model {

	public static $table = TBL_USER;
	public static $table_more = TBL_USER;
	
	private $_controller;
	private static $extension_media;
	
	/**
	 * Costruttore
	 * 
	 * @param integer $id valore ID del record
	 * @param object $instance istanza del controller
	 */
	function __construct($id) {

		$this->_fields_label = array(
			'firstname' => _('Nome'), 
			'lastname' => _('Cognome'), 
			'company' => _('Società'), 
			'phone' => _("Telefono"), 
			'email' => _("Email"), 
			'userpwd' => _('Password'), 
			'is_admin' => _('Super-amministratore'),
			'is_staff' => _("Accesso all'area amministrativa"),
			'address' => _("Indirizzo"), 
			'city' => _("Città"), 
			'nation' => _('Nazione'), 
			'text' => _("Informazioni"), 
			'photo' => _("Foto"), 
			'publication' => _('Pubblicazione dati'), 
			'active' => _('Attivo')
		);
		
		$this->_tbl_data = self::$table;
		
		require_once 'class_auth.php';
		//loader::import('auth', 'auth');
		$controller = new auth();
		$this->_controller = $controller;
		
		$registry = registry::instance();
		self::$extension_media = !$registry->pub->enabledPng() ? array('jpg') : array('png', 'jpg');
		
		parent::__construct($id);
	}

	function __toString() {
		return $this->lastname.' '.$this->firstname;
	}
	
	public function getModelLabel() {
		return _('utente');
	}

	/*
	 * Sovrascrive la struttura di default
	 * 
	 * @see Model::structure()
	 * @param integer $id
	 * @return array
	 */
	 public function structure($id) {

		$structure = parent::structure($id);

		$structure['firstname'] = new CharField(array(
			'name'=>'firstname', 
			'required'=>true,
			'label'=>$this->_fields_label['firstname'], 
			'value'=>$this->firstname, 
			'trnsl'=>false,
			'table'=>$this->_tbl_data
		));
		
		$structure['lastname'] = new CharField(array(
			'name'=>'lastname', 
			'required'=>true,
			'label'=>$this->_fields_label['lastname'], 
			'value'=>$this->lastname, 
			'trnsl'=>false,
			'table'=>$this->_tbl_data
		));
		
		$structure['email'] = new EmailField(array(
			'name'=>'email', 
			'required'=>true,
			'label'=>$this->_fields_label['email'], 
			'value'=>$this->email, 
			'trnsl'=>false,
			'table'=>$this->_tbl_data
		));
		
		$structure['is_admin'] = new BooleanField(array(
			'name'=>'is_admin', 
			'required'=>true,
			'label'=>$this->_fields_label['is_admin'], 
			'enum'=>array(1 => _('si'), 0 => _('no')), 
			'default'=>0,
			'value'=>$this->is_admin, 
			'table'=>$this->_tbl_data
		));
		
		$structure['is_staff'] = new BooleanField(array(
			'name'=>'is_staff', 
			'required'=>true, 
			'label'=>$this->_fields_label['is_staff'], 
			'enum'=>array(1=>_('si'), 0=>_('no')), 
			'default'=>0,
			'value'=>$this->is_staff, 
			'table'=>$this->_tbl_data
		));
		
		$structure['nation'] = new foreignKeyField(array(
			'name'=>'nation', 
			'value'=>$this->nation, 
			'label'=>$this->_fields_label['nation'], 
			'lenght'=>4, 
			'fkey_table'=>TBL_NATION, 
			'fkey_id'=>'id', 
			'fkey_field'=>$this->_lng_nav, 
			'fkey_where'=>'', 
			'fkey_order'=>$this->_lng_nav.' ASC'
		));
		
		$base_path = $this->_controller->getBasePath();
		$add_path = $this->_controller->getAddPath($this->id);
		
		$structure['photo'] = new ImageField(array(
			'name'=>'photo', 
			'required'=>false, 
			'label'=>$this->_fields_label['photo'], 
			'extensions'=>self::$extension_media, 
			'path'=>$base_path, 
			//'add_path'=>$add_path, 
			'value'=>$this->photo
		));

		$structure['publication'] = new BooleanField(array(
			'name'=>'publication', 
			'required'=>false, 
			'label'=>$this->_fields_label['publication'], 
			'enum'=>array(1=>_('si'), 0=>_('no')), 
			'default'=>0,
			'value'=>$this->publication, 
			'table'=>$this->_tbl_data
		));
		
		$structure['active'] = new BooleanField(array(
			'name'=>'active', 
			'required'=>true, 
			'label'=>$this->_fields_label['active'], 
			'enum'=>array(1=>_('si'), 0=>_('no')), 
			'default'=>0,
			'value'=>$this->active, 
			'table'=>$this->_tbl_data
		));
		
		return $structure;
	 }
	 
	 /**
	  * Form per cambiare la password
	  * 
	  * @param array $options
	  *   array associativo di opzioni
	  *   - @b form_action (string): indirizzo del form action
	  *   - @b rules (string): descrizione delle regole alle quali è sottoposta la password
	  *   - @b maxlength (integer): numero massimo di caratteri
	  * @return string
	  */
	 public function formPassword($options=array()) {
	 	
	 	$form_action = gOpt('form_action', $options, null);
	 	$rules = gOpt('rules', $options, null);
	 	$maxlength = gOpt('maxlength', $options, null);
	 	
	 	$gform = Loader::load('Form', array('pwdform', 'post', true));
	 	
		$gform = new Form('pwdform', 'post', true);
		$gform->load('pwdform');

		$required = 'userpwd,check_userpwd';
		
		$buffer = $gform->open($form_action, '', $required);
		$buffer .= $gform->hidden('id', $this->id);

		$buffer .= $gform->cinput('userpwd', 'password', '', array(_("Password"), $rules), array("required"=>true, "size"=>40, "maxlength"=>$maxlength));
		$buffer .= $gform->cinput('check_userpwd', 'password', '', _("Verifica password"), array("required"=>true, "size"=>40, "maxlength"=>$maxlength, "other"=>"autocomplete=\"off\""));

		$buffer .= $gform->cinput('submit_action', 'submit', _("procedi"), '', array("classField"=>"submit"));

		$buffer .= $gform->close();
		
		return $buffer;
	}
	
	/**
	 * Salva la nuova password
	 * 
	 * @see checkPassword()
	 * @see setPassword()
	 * @param array $options
	 *   array associativo di opzioni (per il metodo checkPassword())
	 *   - @b pwd_length_min (integer): numero minimo di caratteri della password
	 *   - @b pwd_length_max (integer): numero massimo di caratteri della password
	 *   - @b pwd_numeric_number (integer): numero di caratteri numerici da inserire nella password
	 * @return boolean
	 */
	public function savePassword($options=array()) {
	 	
	 	$gform = Loader::load('Form', array('pwdform', 'post', true));
	 	
		$gform = new Form('pwdform', 'post', true);
		$gform->save('pwdform');
		$req_error = $gform->arequired();
		
		if($req_error > 0) 
			return array('error'=>1);

		$password = cleanVar($_POST, 'userpwd', 'string', '');
	 	$options['password'] = $password;
	 	
		$check_password = self::checkPassword($options);
		
		if(is_array($check_password))
			return $check_password;
		
		$password = self::setPassword($password);
		
		$result = $this->_db->update(array('userpwd'=>$password), self::$table, "id='".$this->id."'");
		
		return $result;
	 }
	 
	 /**
	  * Imposta la password (dal form di inserimento utente)
	  * 
	  * @see generatePassword()
	  * @see pub::cryptMethod()
	  * @param string $password
	  * @param array $options
	  *   array associativo di opzioni
	  *   - @b aut_password (boolean): la password è generata dal sistema (default false)
	  * @return string (password) or array (error)
	  */
	 public static function setPassword($password, $options=array()) {
	 	
	 	$aut_password = gOpt('aut_password', $options, false);
		
		if(is_null($password) && $aut_password)
		{
			$password = self::generatePassword($options);
		}
		else
		{
			$registry = registry::instance();
    		$crypt_method = $registry->pub->getConf('password_crypt');
    		
    		$password = $crypt_method ? $registry->pub->cryptMethod($password, $crypt_method) : $password;
		}
		return $password;
	 }
	 
	/**
	 * Verifica la conformità di una password
	 * 
	 * @param array $options
	 *   array associativo di opzioni
	 *   - @b password (string): valore della password (se non indicato viene recuperato il valore dal POST)
	 *   - @b check_password (string): valore di controllo della password (se non indicato viene recuperato il valore dal POST)
	 *   - @b pwd_length_min (integer): numero minimo di caratteri della password
	 *   - @b pwd_length_max (integer): numero massimo di caratteri della password
	 *   - @b pwd_numeric_number (integer): numero di caratteri numerici da inserire nella password
	 * @return array (error) or true
	 * 
	 * Parametri POST: \n
	 *   - userpwd (string)
	 *   - check_userpwd (string)
	 * 
	 * Se è presente l'input form @a check_userpwd viene controllata la corrispondenza con l'input form @a userpwd.
	 */
	public static function checkPassword($options=array()){
		
		$password = gOpt('password', $options, null);
		$password_check = gOpt('password_check', $options, null);
		$pwd_length_min = gOpt('pwd_length_min', $options, null);
		$pwd_length_max = gOpt('pwd_length_max', $options, null);
		$pwd_numeric_number = gOpt('pwd_numeric_number', $options, null);
		
		if(is_null($password)) $password = cleanVar($_POST, 'userpwd', 'string', '');
		if(is_null($password_check)) $password_check = cleanVar($_POST, 'check_userpwd', 'string', '');
		
		if($password_check && $password != $password_check)
			return array('error'=>6);
		
		$regex = '';
		$base = "[0-9]{1}.*";
		if($pwd_numeric_number > 0)
		{
			$regex .= '/^.*';
			
			for($i=0; $i<$pwd_numeric_number; $i++)
			{
				$regex .= $base;
			}
			$regex .= '$/';
			
			$check = true;
		}
		else $check = false;
		
		if($check)
		{
			if((strlen($password) < $pwd_length_min OR strlen($password) > $pwd_length_max) || !preg_match($regex, $password))
				return array('error'=>19);
		}
		else
		{
			if(strlen($password) < $pwd_length_min OR strlen($password) > $pwd_length_max)
				return array('error'=>19);
		}
		
		return true;
	}
	
	/**
	 * Genera una password (random)
	 * 
	 * @param array $options
	 *   array associativo di opzioni
	 *   - @b aut_password_length (integer): numero di caratteri della password automatica
	 * @return string
	 */
	public static function generatePassword($options=array()){

		$password_length = gOpt('aut_password_length', $options, null);
		
		if(!$password_length) return null;
		
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

		for($i = 0; $i < $password_length; $i++)
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
	
	/**
	 * Verifica la conformità e validità di un indirizzo email
	 * 
	 * @see function checkEmail()
	 * @param integer $id valore ID del record sul quale non si effettua il controllo (per le operazioni di modifica)
	 * @return array (error) or true
	 * 
	 * Parametri POST: \n
	 *   - email (string)
	 *   - check_email (string)
	 */
	public static function checkEmail($id=null){
		
		$db = db::instance();
		
		$email = cleanVar($_POST, 'email', 'string', '');
		$check_email = cleanVar($_POST, 'check_email', 'string', '');
		
		if($email && !checkEmail($email, true))
			return array('error'=>7);
		
		if($db->columnHasValue(self::$table, 'email', $email, array('except_id'=>$id)))
			return array('error'=>20);
		
		if($check_email && $email != $check_email)
			return array('error'=>25);
		
		return true;
	}
	
	/**
	 * Verifica l'unicità dello username
	 * 
	 * @param array $options
	 *   array associativo di opzioni
	 *   - @b username_as_email (boolean): indica se come username viene utilizzata l'email (default false)
	 * @return array (error) or true
	 * 
	 * Parametri POST: \n
	 *   - username (string)
	 *   - email (string)
	 */
	public static function checkUsername($options) {
		
		$username_as_email = gOpt('username_as_email', $options, false);
		
		$db = db::instance();
		
		$input_name = $username_as_email ? 'email' : 'username';
		
		$username = cleanVar($_POST, $input_name, 'string', '');
		
		if($db->columnHasValue(self::$table, 'username', $username))
			return array('error'=>8);
		
		return true;
	}

  /**
   * Restituisce l'utente dati i valori username e password
   * 
   * @param string $username lo username
   * @param string $password la password
   * @return mixed l'oggetto utente ricavato oppure null
   */
  public static function getFromUserPwd($username, $password) {

    $db = db::instance();

    $user = null;

    $registry = registry::instance();
    $crypt_method = $registry->pub->getConf('password_crypt');
    
    $password = $crypt_method ? $registry->pub->cryptMethod($password, $crypt_method) : $pwd;

    $rows = $db->select('id', TBL_USER, "username='$username' AND userpwd='$password' AND active='1'");
    if($rows and count($rows) == 1) {
      $user = new User($rows[0]['id']);
    }
    return $user;

  }

  /**
   * Verifica se l'utente ha uno dei permessi della classe
   * @param string $class la classe
   * @param int|array $perms id o array di id dei permessi da verificare
   * @param int $instance istanza della classe (0 per classi non istanziabili)
   * @return boolean
   */
  public function hasPerm($class, $perms, $instance = 0) {

    if(!$this->id) {
      return false;
    }
    elseif($this->is_admin) {
      return true;
    }

    if(!is_array($perms)) {
      $perms = array($perms);
    }

    foreach($perms as $perm_code) {
      // check user permission
      $rows = $this->_db->select(TBL_USER_PERMISSION.'.perm_id', array(TBL_USER_PERMISSION, TBL_PERMISSION), array(
        TBL_USER_PERMISSION.'.perm_id' => TBL_PERMISSION.'.id',
        TBL_USER_PERMISSION.'.user_id' => $this->id,
        TBL_PERMISSION.'.code' => $perm_code
      ));
      if($rows and count($rows)) {
        return true;
      }
      // check user group permission
      $rows = $this->_db->select(TBL_GROUP_PERMISSION.'.perm_id', array(TBL_GROUP_PERMISSION, TBL_GROUP_USER, TBL_PERMISSION), array(
        TBL_GROUP_PERMISSION.'.perm_id' => TBL_PERMISSION.'.id',
        TBL_PERMISSION.'.code' => $perm_code,
        TBL_GROUP_PERMISSION.'.instance' => $instance,
        TBL_GROUP_PERMISSION.'.group_id' => TBL_GROUP_USER.'.group_id',
        TBL_GROUP_USER.'.user_id' => $this->id
      ));
      if($rows and count($rows)) {
        return true;
      }
    }

    return false;
  }

  /**
   * Verifica se l'utente ha uno dei permessi amministrativi della classe
   * @param string $class la classe
   * @param int $instance istanza della classe (0 per classi non istanziabili)
   * @return boolean
   */
  public function hasAdminPerm($class, $instance = 0) {

    $perms = array();
    $rows = $this->_db->select('code', TBL_PERMISSION, "admin='1'");
    if($rows and count($rows)) {
      foreach($rows as $row) {
        $perms[] = $row['code'];
      }
    }
    if($this->hasPerm($class, $instance, $perms)) {
      return true;
    }

    return false;
  }

	/**
	 * @see Model::delete()
	 */
	/*public function delete() {

		$pathToDel = $this->_controller->getBasePath().$this->_controller->getAddPath($this->id);
		
		$parent = parent::delete();
		if($parent !== true) return $parent;
		
		if($pathToDel)
		{
			$registry = registry::instance();
			$registry->pub->deleteFileDir($pathToDel);
		}

		return true;
	}*/

}

