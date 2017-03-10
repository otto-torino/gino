<?php
/**
 * @file class.Group.php
 * Contiene la definizione ed implementazione della classe Gino.App.Auth.Group.
 * 
 * @copyright 2013-2017 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 * @authors Marco Guidotti guidottim@gmail.com
 * @authors abidibo abidibo@gmail.com
 */
namespace Gino\App\Auth;

/**
 * @brief Classe tipo Gino.Model che rappresenta un gruppo di utenti
 *
 * @copyright 2013-2017 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 * @authors Marco Guidotti guidottim@gmail.com
 * @authors abidibo abidibo@gmail.com
 */
class Group extends \Gino\Model {

    public static $table = TBL_GROUP;
    public static $table_group_perm = TBL_GROUP_PERMISSION;
    public static $columns;

    /**
     * @brief Costruttore
     * 
     * @param integer $id valore ID del record
     * @return istanza di Gino.App.Auth.Group
     */
    function __construct($id) {

        $this->_model_label = _('Gruppo');
        $this->_tbl_data = self::$table;
        parent::__construct($id);
    }

    /**
     * @brief Rapprensentazione a stringa dell'oggetto
     * @return nome gruppo
     */
    function __toString() {
        return $this->name;
    }
    
    /**
     * Struttura dei campi della tabella di un modello
     *
     * @return array
     */
    public static function columns() {
    	 
    	$columns['id'] = new \Gino\IntegerField(array(
    		'name'=>'id',
    		'primary_key'=>true,
    		'auto_increment'=>true,
    	));
    	$columns['name'] = new \Gino\CharField(array(
    		'name'=>'name',
    		'label' => _("Nome"),
    		'required'=>true,
    		'max_lenght'=>128,
    	));
    	$columns['description'] = new \Gino\TextField(array(
    		'name'=>'description',
    		'label' => _("Descrizione"),
    		'required'=>false
    	));
    
    	return $columns;
    }

    /**
     * @brief Elenco dei gruppi
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
     *@brief  Valore che raggruppa permesso e istanza
     *
     * @param integer $permission_id valore ID del permesso
     * @param integer $instance_id valore ID dell'istanza
     * @return string
     */
    public static function setMergeValue($permission_id, $instance_id) {

        return $permission_id.'_'.$instance_id;
    }

    /**
     * @brief Splitta i valori di permesso e istanza
     *
     * @param string $value valore da splittare
     * @return array array(permission_id, instance_id)
     */
    public static function getMergeValue($value) {

        return explode('_', $value);
    }

    /**
     * @brief Elenco dei permessi di un gruppo
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
    
    /**
     * @brief Elenco dei permessi associati al gruppo
     * @return string
     */
    public function printPermissions() {
    	
    	$buffer = '';
    	
    	$records = $this->_db->select('instance, perm_id', self::$table_group_perm, "group_id='".$this->id."'");
    	if($records && count($records))
    	{
    		foreach($records AS $r)
    		{
    			$perm = Permission::getDataPermission($r['perm_id'], $r['instance']);
    			$buffer .= $perm."<br />";
    		}
    	}
    	return $buffer;
    }
}

Group::$columns=Group::columns();
