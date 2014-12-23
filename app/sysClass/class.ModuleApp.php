<?php
/**
 * @file class.ModuleApp.php
 * @brief Contiene la definizione ed implementazione della classe Gino.App.SysClass.ModuleApp
 *
 * @copyright 2005-2014 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */

namespace Gino\App\SysClass;

/**
 * @brief Classe di tipo Gino.Model che rappresenta un modulo di sistema
 *
 * @copyright 2005-2014 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class ModuleApp extends \Gino\Model {

  public static $table = TBL_MODULE_APP;

    /**
     * @brief Costruttore
     * @param int $id
     * @return istanza di Gino.App.SysClass.ModuleApp
     */
    function __construct($id) {

        $this->_tbl_data = self::$table;
        parent::__construct($id);

    }

    /**
     * @brief Rappresentazione a stringa dell'oggetto
     * @return label modulo
     */
    function __toString() {
        return $this->label;
    }

    /**
     * @brief Nome della classe di sistema
     * @return nome classe (senza namespace)
     */
    public function className() {

        return $this->name;
    }

    /**
     * @brief Nome della classe di sistema con namespace completo
     * @return nome classe (con namespace)
     */
    public function classNameNs() {

        return get_app_name_class_ns($this->name);
    }

    /**
     * @brief Recupera l'oggetto dato il nome della classe di sistema
     * @param string $name nome classe senza namespace
     * @return istanza di Gino.App.SysClass.ModuleApp
     */
    public static function getFromName($name) {

        $db = \Gino\db::instance();
        $rows = $db->select('id', self::$table, "name='$name'");
        if($rows and count($rows)) {
            return new ModuleApp($rows[0]['id']);
        }

        return null;
    }
}
