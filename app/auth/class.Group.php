<?php
/**
 * \file class.Group.php
 * Contiene la definizione ed implementazione della classe Group.
 * 
 * @version 1.0
 * @copyright 2013 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 * @authors Marco Guidotti guidottim@gmail.com
 * @authors abidibo abidibo@gmail.com
 */
namespace Gino\App\Auth;

/**
 * \ingroup auth
 * Classe tipo model che rappresenta un gruppo.
 *
 * @version 1.0
 * @copyright 2013 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 * @authors Marco Guidotti guidottim@gmail.com
 * @authors abidibo abidibo@gmail.com
 */
class Group extends \Gino\Model {

	public static $table = TBL_GROUP;
	public static $table_group_user = TBL_GROUP_USER;
	public static $table_group_perm = TBL_GROUP_PERMISSION;
	
	/**
	 * Costruttore
	 * 
	 * @param integer $id valore ID del record
	 * @param object $instance istanza del controller
	 */
	function __construct($id) {

		$this->_fields_label = array(
			'name' => _('Nome'), 
			'description' => _('Descrizione')
		);

		$this->_tbl_data = self::$table;
		parent::__construct($id);
	}

	function __toString() {
		return $this->name;
	}
	
	public function getModelLabel() {
		return _('gruppo');
	}

	/**
	 * Elenco dei gruppi
	 * 
	 * @return array array(id, name, description)
	 */
	public static function getList() {
		
		$db = \Gino\db::instance();
		
		$items = array();
		
		$records = $db->select('id, name, description', self::$table, null, array('order'=>'name ASC'));
		if($records && count($records))
		{
			foreach($records AS $r)
			{
				$items[] = array(
					'id'=>$r['id'], 
					'name'=>$r['name'], 
					'description'=>$r['description']
				);
			}
		}
		return $items;
	}
	
	/**
	 * Valore che raggruppa permesso e istanza
	 * 
	 * @param integer $permission_id valore ID del permesso
	 * @param integer $instance_id valore ID dell'istanza
	 * @return string
	 */
	public static function setMergeValue($permission_id, $instance_id) {
		
		return $permission_id.'_'.$instance_id;
	}
	
	/**
	 * Splitta i valori di permesso e istanza
	 * 
	 * @param string $value valore da splittare
	 * @return array array(permission_id, instance_id)
	 */
	public static function getMergeValue($value) {
		
		$split = explode('_', $value);
		$permission_id = $split[0];
		$instance_id = $split[1];
		
		return array($permission_id, $instance_id);
	}
	
	/**
	 * Elenco dei permessi di un gruppo
	 * 
	 * @param integer $id valore ID del gruppo
	 * @return array
	 */
	public function getPermissions() {
		
		$items = array();
		
		$records = $this->_db->select('instance, perm_id', self::$table_group_perm, "group_id='".$this->id."'");
		if($records && count($records))
		{
			foreach($records AS $r)
			{
				$items[] = self::setMergeValue($r['perm_id'], $r['instance']);
			}
		}
		return $items;
	}
}
