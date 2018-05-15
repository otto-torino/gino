<?php
/**
 * @file class.ModuleApp.php
 * @brief Contiene la definizione ed implementazione della classe Gino.App.SysClass.ModuleApp
 *
 * @copyright 2005-2017 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */

namespace Gino\App\SysClass;

/**
 * @brief Classe di tipo Gino.Model che rappresenta un modulo di sistema
 *
 * @copyright 2005-2017 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class ModuleApp extends \Gino\Model {

	public static $table = TBL_MODULE_APP;
	public static $columns;
	
	/**
     * @brief Costruttore
     * @param int $id
     * @return void, istanza di Gino.App.SysClass.ModuleApp
     */
    function __construct($id) {

        $this->_tbl_data = self::$table;
        
        parent::__construct($id);
    }

    /**
     * @brief Rappresentazione a stringa dell'oggetto
     * @return string, label modulo
     */
    function __toString() {
        return $this->label;
    }

    /**
     * @brief Nome della classe di sistema
     * @return string, nome classe (senza namespace)
     */
    public function className() {

        return $this->name;
    }

    /**
     * @brief Nome della classe di sistema con namespace completo
     * @return string, nome classe (con namespace)
     */
    public function classNameNs() {

        return get_app_name_class_ns($this->name);
    }

    /**
     * @brief Recupera l'oggetto dato il nome della classe di sistema
     * @param string $name nome classe senza namespace
     * @return Gino.App.SysClass.ModuleApp
     */
    public static function getFromName($name) {

        $db = \Gino\Db::instance();
        $rows = $db->select('id', self::$table, "name='$name'");
        if($rows and count($rows)) {
            return new ModuleApp($rows[0]['id']);
        }
        else {
        	return null;
        }
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
    	$columns['label'] = new \Gino\CharField(array(
    		'name'=>'label',
    		'required'=>true,
     		'max_lenght'=>100,
    	));
    	$columns['name'] = new \Gino\CharField(array(
    		'name'=>'name',
    		'required'=>true,
     		'max_lenght'=>100,
    	));
    	$columns['active'] = new \Gino\BooleanField(array(
    		'name'=>'active',
    		'required'=>true,
    		'default'=>1,
    	));
    	$columns['tbl_name'] = new \Gino\CharField(array(
    		'name'=>'tbl_name',
    		'required'=>true,
     		'max_lenght'=>30,
    	));
    	$columns['instantiable'] = new \Gino\BooleanField(array(
    		'name'=>'instantiable',
    		'required'=>true,
    	));
    	$columns['description'] = new \Gino\TextField(array(
    		'name'=>'description',
    		'required'=>true
    	));
    	$columns['removable'] = new \Gino\BooleanField(array(
    		'name'=>'removable',
    		'required'=>true,
    	));
    	$columns['class_version'] = new \Gino\CharField(array(
    		'name'=>'class_version',
    		'required'=>true,
     		'max_lenght'=>200,
    	));
    	return $columns;
    }
    
    /**
     * @brief Elenco dei moduli installati in gino
     * @return array[], id => name
     */
    public static function getModuleList() {
    	
    	$db = \Gino\Db::instance();
    	$array = array();
    	
    	$rows = $db->select('id, name', self::$table, null);
    	if($rows and count($rows)) {
    		foreach ($rows AS $row) {
    			$array[$row['id']] = $row['name'];
    		}
    	}
    	return $array;
    }
}

ModuleApp::$columns=ModuleApp::columns();
