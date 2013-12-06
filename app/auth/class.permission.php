<?php
/**
 * \file class.Permission.php
 * Contiene la definizione ed implementazione della classe User.
 * 
 * @version 1.0
 * @copyright 2013 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 * @authors Marco Guidotti guidottim@gmail.com
 * @authors abidibo abidibo@gmail.com
 */

/**
 * \ingroup auth
 * Classe tipo model che rappresenta un permesso.
 *
 * @version 1.0
 * @copyright 2013 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 * @authors Marco Guidotti guidottim@gmail.com
 * @authors abidibo abidibo@gmail.com
 */
class Permission extends Model {

	public static $table = TBL_PERMISSION;
	
	/**
	 * Costruttore
	 * 
	 * @param integer $id valore ID del record
	 * @param object $instance istanza del controller
	 */
	function __construct($id) {

		$this->_fields_label = array(
			'class' => _('Nome della classe'), 
			'code' => _('Codice del permesso'), 
			'label' => _('Label'), 
			'description' => _('Descrizione'), 
			'admin' => _('Permesso da amministratore'), 
		);

		$this->_tbl_data = self::$table;
		parent::__construct($id);
	}

	function __toString() {
		return $this->label;
	}
	
	public function getModelLabel() {
		return _('permesso');
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

		$structure['admin'] = new BooleanField(array(
			'name'=>'admin', 
			'required'=>true,
			'label'=>$this->_fields_label['admin'], 
			'enum'=>array(1 => _('si'), 0 => _('no')), 
			'default'=>0,
			'value'=>$this->admin
		));
		
		return $structure;
	}
	 
	public static function getList() {
		
		$db = db::instance();
		
		$items = array();
		
		$perm = $db->select('*', 'auth_permission', '', array('order'=>'class ASC'));
		if($perm && count($perm))
		{
			foreach($perm AS $p)
			{
				$p_id = $p['id'];
				$p_class = $p['class'];
				$p_code = $p['code'];
				$p_label = $p['label'];
				
				// la classe è istanziabile?
				$class_instance = $db->getFieldFromId(TBL_MODULE_APP, 'instance', 'name', $p_class);
				
				if($class_instance == 'yes')
				{
					$list_instance = $db->select('id, label, name', TBL_MODULE, "class='$p_class'", array('order'=>'label ASC'));
					if($list_instance && count($list_instance))
					{
						foreach($list_instance AS $i)
						{
							$i_id = $i['id'];
							$i_name = $i['name'];
							$i_label = $i['label'];
							
							$items[] = array(
								'perm_id'=>$p_id, 
								'class'=>$p_class, 
								'code'=>$p_code, 
								'label'=>$p_label, 
								'instance_id'=>$i_id, 
								'instance_name'=>$i_name, 
								'instance_label'=>$i_label
							);
						}
					}
				}
				else
				{
					$items[] = array(
						'perm_id'=>$p_id, 
						'class'=>$p_class, 
						'code'=>$p_code, 
						'label'=>$p_label, 
						'instance_id'=>null, 
						'instance_name'=>null, 
						'instance_label'=>null
					);
				}
			}
		}
		
		return $items;
	}

}
