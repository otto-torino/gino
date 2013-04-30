<?php
/**
 * @file class.pageEntryAdminTable.php
 * Contiene la definizione ed implementazione della classe pageEntryAdminTable.
 *
 * @version 0.1
 * @copyright 2012 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 * @authors Marco Guidotti guidottim@gmail.com
 * @authors abidibo abidibo@gmail.com
 */

/**
 * \ingroup page
 * Classe per la gestione del backoffice delle pagine (estensione della classe adminTable del core di gino).
 *
 * @version 0.1
 * @copyright 2012 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 * @authors Marco Guidotti guidottim@gmail.com
 * @authors abidibo abidibo@gmail.com
 */
class pageEntryAdminTable extends adminTable {

	public function modelForm($model, $options=array(), $inputs=array()) {
		
		// Valori di default di form e sessione
		$default_formid = 'form'.$model->getTable().$model->id;
		$default_session = 'dataform'.$model->getTable().$model->id;
		
		// Opzioni generali form
		$formId = array_key_exists('formId', $options) ? $options['formId'] : $default_formid;
		$method = array_key_exists('method', $options) ? $options['method'] : 'post';
		$validation = array_key_exists('validation', $options) ? $options['validation'] : true;
		$trnsl_table = array_key_exists('trnsl_table', $options) ? $options['trnsl_table'] : $model->getTable();
		$trnsl_id = array_key_exists('trnsl_id', $options) ? $options['trnsl_id'] : $model->id;
		$verifyToken = array_key_exists('verifyToken', $options) ? $options['verifyToken'] : false;
		$tblLayout = array_key_exists('tblLayout', $options) ? $options['tblLayout'] : true;
		$form_label_width = array_key_exists('form_label_width', $options) ? $options['form_label_width'] : null;
		$form_field_width = array_key_exists('form_field_width', $options) ? $options['form_field_width'] : null;
		
		$session_value = array_key_exists('session_value', $options) ? $options['session_value'] : $default_session;
		
		$gform = new Form($formId, $method, $validation, 
			array(
				"trnsl_table"=>$trnsl_table,
				"trnsl_id"=>$trnsl_id,
				"verifyToken"=>$verifyToken,
				"tblLayout"=>$tblLayout,
				"form_label_width"=>$form_label_width,
				"form_field_width"=>$form_field_width
			)
		);
		$gform->load($session_value);
		
		// Opzioni per la modifica della struttura del form
		$removeFields = gOpt('removeFields', $options, null);
		$viewFields = gOpt('viewFields', $options, null);
		$addCell = array_key_exists('addCell', $options) ? $options['addCell'] : null;
		
		$structure = '';
		$form_upload = false;
		$form_required = array();
		foreach($model->structure($model->id) as $field=>$object) {
			
			if($addCell)
			{
				foreach($addCell AS $ref_key=>$addvalue)
				{
					if($ref_key == $field)
					{
						$structure .= $addvalue;
					}
				}
			}
			
			if($this->permission($options, $field) &&
			(
				($removeFields && !in_array($field, $removeFields)) || 
				($viewFields && in_array($field, $viewFields)) || 
				(!$viewFields && !$removeFields)
			))
			{
				if(isset($inputs[$field]))
					$options_input = $inputs[$field];
				else 
					$options_input = array();
				
				if($field == 'instance')
				{
					$object->setWidget(null);
					$object->setRequired(false);
				}
				
				$structure .= $object->formElement($gform, $options_input);
				
				$name_class = get_class($object);
				
				if($object instanceof fileField || $object instanceof imageField)
					$form_upload = true;
				
				if($object->getRequired() == true && $object->getWidget() != 'hidden')
					$form_required[] = $field;
			}
		}
		if(sizeof($form_required) > 0)
			$form_required = implode(',', $form_required);
		
		if($model->id) {
			
			$submit = _("modifica");
		}
		else {
			
			$submit = _("inserisci");
		}
		
		$f_action = array_key_exists('f_action', $options) ? $options['f_action'] : '';
		$f_upload = array_key_exists('f_upload', $options) ? $options['f_upload'] : $form_upload;
		$f_required = array_key_exists('f_required', $options) ? $options['f_required'] : $form_required;
		$f_func_confirm = array_key_exists('f_func_confirm', $options) ? $options['f_func_confirm'] : '';
		$f_text_confirm = array_key_exists('f_text_confirm', $options) ? $options['f_text_confirm'] : '';
		$f_generateToken = array_key_exists('f_generateToken', $options) ? $options['f_generateToken'] : false;
		
		$s_name = array_key_exists('s_name', $options) ? $options['s_name'] : 'submit';
		$s_value = array_key_exists('s_value', $options) ? $options['s_value'] : $submit;
		$s_classField = array_key_exists('s_classField', $options) ? $options['s_classField'] : 'submit';
		
		$buffer = '';
		
		$buffer .= $gform->form($f_action, $f_upload, $f_required, 
			array(
				'func_confirm'=>$f_func_confirm,
				'text_confirm'=>$f_text_confirm,
				'generateToken'=>$f_generateToken
			)
		);
		
		if(sizeof($this->_hidden) > 0)
		{
			foreach($this->_hidden AS $key=>$value)
			{
				if(is_array($value))
				{
					$h_value = array_key_exists('value', $options) ? $options['value'] : '';
					$h_id = array_key_exists('id', $options) ? $options['id'] : '';
					$buffer .= $gform->hidden($key, $h_value, array('id'=>$h_id));
				}
				else $buffer .= $gform->hidden($key, $value);
			}
		}
		
		$buffer .= $structure;
		
		$buffer .= $gform->cinput($s_name, 'submit', $s_value, '', array("classField"=>$s_classField));
		$buffer .= $gform->cform();
		
		return $buffer;
	}
	
	/**
	 * Metodo chiamato al salvataggio di una pagina 
	 * 
	 * @param object $model istanza di @ref pageEntry
	 * @param array $options opzioni del form
	 * @param array $options_element opzioni dei campi
	 * @access public
	 * @return void
	 */
	public function modelAction($model, $options=array(), $options_element=array()) {

		$result = parent::modelAction($model, $options, $options_element);
		
		if(is_array($result) && isset($result['error'])) {
			return $result;
		}
		
		$session = session::instance();
		$model->author = $session->userId;
		$model->updateDbData();

		$model_tags = array();

		if($model->published) {
			foreach(explode(',', $model->tags) as $tag) {
				$tag_id = pageTag::saveTag($this->_controller->getInstance(), $tag);
				if($tag_id) {
					$model_tags[] = $tag_id;
				}
			}
		}

		return $model->saveTags($model_tags);
	}
}
?>
