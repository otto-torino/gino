<?php
/**
 * @file class.ModelFormUser.php
 * @brief Contiene la definizione ed implementazione della classe Gino.App.Auth.ModelFormUser
 *
 * @copyright 2015 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
namespace Gino\App\Auth;

/**
 * @brief Sovrascrive la classe Gino.ModelForm
 * 
 * @copyright 2015 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class ModelFormUser extends \Gino\ModelForm {

    /**
	 * @see Gino.ModelForm::save()
	 * 
	 * Customizations: \n
	 *   - @b username_as_email
	 *   - @b user_more_info
	 *   - @b aut_password
	 *   - @b aut_password_length
	 *   - @b pwd_length_min
	 *   - @b pwd_length_max
	 *   - @b pwd_numeric_number
	 *   - @b ldap_auth
	 *   - @b ldap_auth_password
	 *
	 * @see User::checkEmail()
	 * @see User::checkPassword()
	 * @see User::checkUsername()
	 * @see User::setPassword()
	 * @see User::setMoreInfo()
	 */
	public function save($options=array(), $options_field=array()) {
	
		// Opzioni per selezionare gli elementi da recuperare dal form
		$removeFields = array_key_exists('removeFields', $options) ? $options['removeFields'] : null;
		$viewFields = array_key_exists('viewFields', $options) ? $options['viewFields'] : null;
		
		// Opzioni per l'ìmportazione di un file
		$import = false;
		if(isset($options['import_file']) && is_array($options['import_file']))
		{
			$field_import = array_key_exists('field_import', $options['import_file']) ? $options['import_file']['field_import'] : null;
			$field_verify = array_key_exists('field_verify', $options['import_file']) ? $options['import_file']['field_verify'] : array();
			$field_log = array_key_exists('field_log', $options['import_file']) ? $options['import_file']['field_log'] : null;
			$dump = array_key_exists('dump', $options['import_file']) ? $options['import_file']['dump'] : false;
			$dump_path = array_key_exists('dump_path', $options['import_file']) ? $options['import_file']['dump_path'] : null;
	
			if($field_import) $import = TRUE;
		}
		
		$this->saveSession();
		$req_error = $this->checkRequired();
	
		// CUSTOM - si controlla il campo ldap per le interazioni col campo password (ldap true => password non obbligatoria)
		$password = \Gino\cleanVar($this->_request->POST, 'userpwd', 'string', '');
		$user_ldap = \Gino\cleanVar($this->_request->POST, 'ldap', 'int', '');
		if($req_error == 1 && $user_ldap && !$password) $req_error = 0;
		// /CUSTOM
		
		if($req_error > 0) {
			return array('error'=>1);
		}
		
		// CUSTOM
		$insert = $this->_model->id ? false : true;
		
		$check_email = User::checkEmail($this->_model->id);
		if(is_array($check_email)) return $check_email;
		
		if($insert)
		{
			if(!$user_ldap)
			{
				$check_password = User::checkPassword($options);
				if(is_array($check_password)) return $check_password;
			}
			
			$check_username = User::checkUsername($options);
			if(is_array($check_username)) return $check_username;
		}
		// /CUSTOM
	
		$controller = $this->_model->getController();
		$m2mt = array();
		$builds = array();
	
		foreach($this->_model->getStructure() as $field=>$object) {
	
			if($this->permission($options, $field) && (
				($removeFields && !in_array($field, $removeFields)) ||
				($viewFields && in_array($field, $viewFields)) ||
				(!$viewFields && !$removeFields)
			))
			{
				if(isset($options_field[$field])) {
					$opt_element = $options_field[$field];
				}
				else {
					$opt_element = array();
				}
				 
				if($field == 'instance' && is_null($this->_model->instance))
				{
					$this->_model->instance = $controller->getInstance();
				}
				elseif(is_a($object, '\Gino\ManyToManyThroughField'))
				{
					$m2mt[] = array(
							'field' => $field,
							'object' => $object,
					);
				}
				else
				{
					$build = $this->_model->build($object);
						
					$value = $build->clean($opt_element, $this->_model->id);
					// imposta il valore; @see Gino.Model::__set()
					$this->_model->{$field} = $value;
	
					if($import)
					{
						if($field == $field_import)
							$path_to_file = $object->getPath();
					}
				}
			}
		}
	
		if($import)
		{
			$result = $this->readFile($this->_model, $path_to_file, array('field_verify'=>$field_verify, 'dump'=>$dump, 'dump_path'=>$dump_path));
			if($field_log) {
				$this->_model->{$field_log} = $result;
			}
		}
	
		// CUSTOM
		$username_as_email = \Gino\gOpt('username_as_email', $options, false);
		$user_more_info = \Gino\gOpt('user_more_info', $options, false);
		
		if($username_as_email) $this->_model->username = $this->_model->email;
		
		if($insert)
		{
			$ldap_auth = \Gino\gOpt('ldap_auth', $options, false);
			$ldap_auth_password = \Gino\gOpt('ldap_auth_password', $options, null);
			
			if($ldap_auth)
			{
				if($this->_model->ldap && $ldap_auth_password)
					$this->_model->userpwd = $ldap_auth_password;
			}
			else {
				$this->_model->ldap = 0;
			}
			
			$this->_model->userpwd = User::setPassword($this->_model->userpwd, $options);
		}
		// /CUSTOM
		
		$result = $this->_model->save();
		
		// CUSTOM
		if($result && $user_more_info) {
			return User::setMoreInfo($this->_model->id);
		}
		// /CUSTOM
		
		// error
		if(is_array($result)) {
			return $result;
		}
	
		foreach($m2mt as $data) {
			$result = $this->m2mThroughAction($data['field'], $data['object'], $this->_model, $options);
			 
			// error
			if(is_array($result)) {
				return $result;
			}
		}
		 
		return $result;
	}
}
