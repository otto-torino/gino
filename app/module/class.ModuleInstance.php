<?php
/**
 * @file class.ModuleInstance.php
 * @brief Contiene la definizione ed implementazione della classe Gino.App.Module.ModuleInstance
 *
 * @copyright 2005-2018 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */

namespace Gino\App\Module;
use \Gino\App\SysClass\ModuleApp;

/**
 * @brief Classe di tipo Gino.Model che rappresenta un'istanza di un modulo di sistema
 *
 * @copyright 2005-2018 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class ModuleInstance extends \Gino\Model {

    public static $table = TBL_MODULE;
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
	 * Nome della classe
	 * 
	 * @return string
	 */
	public function className() {
		
		$module_app = $this->moduleApp();
		return $module_app->name;
	}

    /**
	 * Nome della classe con namespace completo
	 * 
	 * @return string
	 */
	public function classNameNs($ns=true) {
		$module_app = $this->moduleApp();
		return get_app_name_class_ns($module_app->name);
	}

    /**
     * @brief Recupera l'oggetto dato il nome dell'istanza
     * @param string $name nome istanza senza namespace
     * @return Gino.App.Module.ModuleInstance
     */
    public static function getFromName($name) {

        $db = \Gino\db::instance();
        $rows = $db->select('id', self::$table, "name='$name'");
        if($rows and count($rows)) {
            return new ModuleInstance($rows[0]['id']);
        }

        return null;
    }

    /**
     * @brief Recupera le istanze dato l'id classe di sistema
     * @param int $module_app_id
     * @return array di istanze di Gino.App.Module.ModuleInstance
     */
    public static function getFromModuleApp($module_app_id) {

        $res = array();

        $db = \Gino\db::instance();
        $rows = $db->select('id', self::$table, "module_app='$module_app_id'");
        if($rows and count($rows)) {
            foreach($rows as $row) {
                $res[] = new ModuleInstance($row['id']);
            }
        }

        return $res;
    }

    /**
     * @brief Modulo di sitema dell'istanza
     * @return Gino.App.SysClass.ModuleApp
     */
	public function moduleApp() {
		
		\Gino\Loader::import('sysClass', 'ModuleApp');
		return new ModuleApp($this->module_app);
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
			'required'=>true,
     		'max_lenght'=>100,
		));
		$columns['name'] = new \Gino\CharField(array(
			'name'=>'name',
		    'label' => _("Nome"),
			'required'=>true,
     		'max_lenght'=>100,
		));
		$columns['module_app'] = new \Gino\IntegerField(array(
			'name'=>'module_app',
		    'label' => _("Modulo"),
			'required'=>true
		));
		$columns['active'] = new \Gino\BooleanField(array(
			'name'=>'active',
		    'label' => _("Attivo"),
			'required'=>true,
		));
		$columns['description'] = new \Gino\TextField(array(
			'name'=>'description',
		    'label' => _("Descrizione"),
			'required'=>false
		));
		
		return $columns;
	}
}
ModuleInstance::$columns=ModuleInstance::columns();
