<?php
/**
 * @file class.ModuleApp.php
 * @brief Contiene la definizione ed implementazione della classe Gino.App.SysClass.ModuleApp
 *
 * @copyright 2005-2018 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */

namespace Gino\App\SysClass;

/**
 * @brief Classe di tipo Gino.Model che rappresenta un modulo di sistema
 *
 * @copyright 2005-2018 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class ModuleApp extends \Gino\Model {

	public static $table = TBL_MODULE_APP;
	public static $columns;
	
	/**
     * @brief Costruttore
     * @param int $id
     * @return void
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
     * @brief Nome della classe di sistema senza namespace
     * @return string
     */
    public function className() {

        return $this->name;
    }

    /**
     * @brief Nome della classe di sistema con namespace completo
     * @return string
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
    	    'label' => _("Etichetta"),
    		'required' => true,
     		'max_lenght'=>100,
    	));
    	$columns['name'] = new \Gino\CharField(array(
    		'name'=>'name',
    	    'label' => _("Nome classe"),
    		'required' => true,
     		'max_lenght'=>100,
    	));
    	$columns['active'] = new \Gino\BooleanField(array(
    		'name'=>'active',
    	    'label' => _("Attivo"),
    		'required' => true,
    		'default'=>1,
    	));
    	$columns['tbl_name'] = new \Gino\CharField(array(
    		'name'=>'tbl_name',
    	    'label' => _("Prefisso tabelle"),
    		'required' => true,
     		'max_lenght'=>30,
    	));
    	$columns['instantiable'] = new \Gino\BooleanField(array(
    		'name'=>'instantiable',
    	    'label' => _("Tipo di classe"),
    		'required' => true,
    	    'choice' => [1 => _('istanziabile'), 0 => _('non istanziabile')]
    	));
    	$columns['description'] = new \Gino\TextField(array(
    		'name'=>'description',
    	    'label' => _("Descrizione"),
    		'required'=>true
    	));
    	$columns['removable'] = new \Gino\BooleanField(array(
    		'name' => 'removable',
    	    'label' => _("Rimovibile"),
    		'required' => true,
    	));
    	$columns['class_version'] = new \Gino\CharField(array(
    		'name' => 'class_version',
    	    'label' => _("Versione"),
    		'required' => true,
     		'max_lenght' => 200,
    	));
    	return $columns;
    }
    
    /**
     * @brief Elenco dei moduli installati in gino
     * @return array[], id(int) => name(string)
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
