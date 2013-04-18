<?php
/**
 * \file class.pageCategory.php
 * Contiene la definizione ed implementazione della classe pageCategory.
 * 
 * @version 0.1
 * @copyright 2012 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 * @authors Marco Guidotti guidottim@gmail.com
 * @authors abidibo abidibo@gmail.com
 */

/**
 * \ingroup page
 * Classe tipo model che rappresenta la categoria di una pagina.
 *
 * @version 0.1
 * @copyright 2012 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 * @authors Marco Guidotti guidottim@gmail.com
 * @authors abidibo abidibo@gmail.com
 */
class pageCategory extends propertyObject {

	private $_controller;
	public static $_tbl_item = "page_category";
	
	/**
	 * Costruttore
	 * 
	 * @param integer $id valore ID del record
	 * @param object $instance oggetto dell'istanza
	 */
	function __construct($id, $instance) {
		
		$this->_controller = $instance;
		$this->_tbl_data = self::$_tbl_item;
		
		$this->_fields_label = array(
			'name'=>_("Nome"), 
			'parent'=>_('Categoria superiore'),
			'description'=>_('Descrizione')
		);
		
		parent::__construct($id);
		
		$this->_model_label = $this->id ? $this->name : null;
	}
	
	/**
	 * Rappresentazione testuale del modello 
	 * 
	 * @return string
	 */
	function __toString() {
		
		return $this->_model_label;
	}
	
	/**
	 * Sovrascrive la struttura di default
	 * 
	 * @see propertyObject::structure()
	 * @param integer $id
	 * @return array
	 */
	public function structure($id) {
		
		$structure = parent::structure($id);

		$ref_id = cleanVar($_GET, 'ref', 'int', '');
		$insert = cleanVar($_GET, 'insert', 'int', '');
		
		if($ref_id && $insert)
		{
			$structure['name'] = new charField(array(
				'name'=>'name', 
				'required'=>true, 
				'value'=>'', 
				'label'=>$this->_fields_label['name']
			));
			
			$structure['parent'] = new constantField(array(
				'name'=>'parent', 
				'value'=>$ref_id, 
				'label'=>$this->_fields_label['parent'], 
				'const_table'=>$this->_tbl_data, 
				'const_field'=>'name'
			));
		}
		else
		{
			$structure['parent'] = new foreignKeyField(array(
				'name'=>'parent', 
				'required'=>false, 
				'value'=>$this->parent, 
				'label'=>$this->_fields_label['parent'], 
				'fkey_table'=>$this->_tbl_data, 
				'fkey_field'=>'name', 
				'fkey_order'=>'name'
			));
		}

		return $structure;
	}
}

?>
