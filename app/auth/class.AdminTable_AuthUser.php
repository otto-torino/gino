<?php
/**
 * @file class.AdminTable_AuthUser.php
 * @brief Contiene la classe AdminTable_AuthUser
 *
 * @copyright 2013 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
namespace Gino\App\Auth;

/**
 * @brief Sovrascrive la classe AdminTable
 * 
 * @copyright 2013 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class AdminTable_AuthUser extends \Gino\AdminTable {
	
	/**
	 * Gestisce l'azione del form
	 * 
	 * @see readFile()
	 * @see Model::updateDbData()
	 * @see field::clean()
	 * @param object $model
	 * @param array $options
	 *   - opzioni per il recupero dei dati dal form
	 *   - opzioni per selezionare gli elementi da recuperare dal form
	 *     - @b removeFields (array): elenco dei campi non presenti nel form
	 *     - @b viewFields (array): elenco dei campi presenti nel form
	 *   - @b import_file (array): attivare l'importazione di un file (richiama il metodo readFile())
	 *     - @a field_import (string): nome del campo del file di importazione
	 *     - @a field_verify (array): valori da verificare nel processo di importazione, nel formato array(nome_campo=>valore[, ])
	 *     - @a field_log (string): nome del campo del file di log
	 *     - @a dump (boolean): per eseguire il dump della tabella prima di importare il file
	 *     - @a dump_path (string): percorso del file di dump
	 * @param array $options_element opzioni per formattare uno o più elementi da inserire nel database
	 * @return void
	 * 
	 * Personalizzazioni: \n
	 *   - @b username_as_email
	 *   - @b user_more_info
	 *   - @b aut_password
	 *   - @b aut_password_length
	 *   - @b pwd_length_min
	 *   - @b pwd_length_max
	 *   - @b pwd_numeric_number
	 *   
	 * @see User::checkEmail()
	 * @see User::checkPassword()
	 * @see User::checkUsername()
	 * @see User::setPassword()
	 * @see User::setMoreInfo()
	 */
	public function modelAction($model, $options=array(), $options_element=array()) {
		
		// Importazione di un file
		$import = false;
		if(isset($options['import_file']) && is_array($options['import_file']))
		{
			$field_import = array_key_exists('field_import', $options['import_file']) ? $options['import_file']['field_import'] : null;
			$field_verify = array_key_exists('field_verify', $options['import_file']) ? $options['import_file']['field_verify'] : array();
			$field_log = array_key_exists('field_log', $options['import_file']) ? $options['import_file']['field_log'] : null;
			$dump = array_key_exists('dump', $options['import_file']) ? $options['import_file']['dump'] : false;
			$dump_path = array_key_exists('dump_path', $options['import_file']) ? $options['import_file']['dump_path'] : null;
			
			if($field_import) $import = true;
		}
		
		// Valori di default di form e sessione
		$default_formid = 'form'.$model->getTable().$model->id;
		$default_session = 'dataform'.$model->getTable().$model->id;
		
		// Opzioni generali per il recupero dei dati dal form
		$formId = array_key_exists('formId', $options) ? $options['formId'] : $default_formid;
		$method = array_key_exists('method', $options) ? $options['method'] : 'post';
		$validation = array_key_exists('validation', $options) ? $options['validation'] : true;
		$session_value = array_key_exists('session_value', $options) ? $options['session_value'] : $default_session;
		
		// Opzioni per selezionare gli elementi da recuperare dal form
		$removeFields = array_key_exists('removeFields', $options) ? $options['removeFields'] : null;
		$viewFields = array_key_exists('viewFields', $options) ? $options['viewFields'] : null;
		
		$gform = new \Gino\Form($formId, $method, $validation);
		$gform->save($session_value);
		$req_error = $gform->arequired();
		
		if($req_error > 0) 
			return array('error'=>1);
		
		/*
		 * Personalizzazioni
		 */
		$username_as_email = \Gino\gOpt('username_as_email', $options, false);
		$user_more_info = \Gino\gOpt('user_more_info', $options, false);
		
		$insert = $model->id ? false : true;
		
		$check_email = User::checkEmail($model->id);
		if(is_array($check_email)) return $check_email;
		
		if($insert)
		{
			$check_password = User::checkPassword($options);
			if(is_array($check_password)) return $check_password;

			$check_username = User::checkUsername($options);
			if(is_array($check_username)) return $check_username;
		}
		// End
		
		foreach($model->getStructure() as $field=>$object) {
			
			if($this->permission($options, $field) &&
			(
				($removeFields && !in_array($field, $removeFields)) || 
				($viewFields && in_array($field, $viewFields)) || 
				(!$viewFields && !$removeFields)
			))
			{
				if(isset($options_element[$field]))
					$opt_element = $options_element[$field];
				else 
					$opt_element = array();
				
				if($field == 'instance' && is_null($model->instance))
				{
					$model->instance = $this->_controller->getInstance();
				}
				else
				{
					$value = $object->clean($opt_element);
					$result = $object->validate($value);
					
					if($result === true) {
						$model->{$field} = $value;
					}
					else {
						return array('error'=>$result['error']);
					}
					
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
			$result = $this->readFile($model, $path_to_file, array('field_verify'=>$field_verify, 'dump'=>$dump, 'dump_path'=>$dump_path));
			if($field_log)
				$model->{$field_log} = $result;
		}
		
		/*
		 * Personalizzazioni
		 */
		if($username_as_email) $model->username = $model->email;
		
		if($insert) $model->userpwd = User::setPassword($model->userpwd, $options);
		
		$update_db = $model->updateDbData();
		
		if($update_db && $user_more_info)
		{
			return User::setMoreInfo($model->id);
		}
		else return $update_db;
		// End
	}
}
