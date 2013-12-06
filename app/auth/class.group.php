<?php
/**
 * \file class.Group.php
 * Contiene la definizione ed implementazione della classe User.
 * 
 * @version 1.0
 * @copyright 2013 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 * @authors Marco Guidotti guidottim@gmail.com
 * @authors abidibo abidibo@gmail.com
 */

/**
 * \ingroup auth
 * Classe tipo model che rappresenta un gruppo.
 *
 * @version 1.0
 * @copyright 2013 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 * @authors Marco Guidotti guidottim@gmail.com
 * @authors abidibo abidibo@gmail.com
 */
class Group extends Model {

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
		
		$db = db::instance();
		
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
	 * Elenco dei gruppi di un utente
	 * 
	 * @param integer $user_id valore ID dell'utente
	 * @return array
	 */
	public static function userGroup($user_id) {
		
		$db = db::instance();
		
		$items = array();
		
		$records = $db->select('group_id', self::$table_group_user, "user_id='$user_id'");
		if($records && count($records))
		{
			foreach($records AS $r)
			{
				$items[] = $r['group_id'];
			}
		}
		return $items;
	}
}
