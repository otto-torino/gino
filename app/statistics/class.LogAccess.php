<?php
/**
 * @file class.LogAccess.php
 * @brief Contiene la definizione ed implementazione della classe \Gino\App\Statistics\LogAccess
 * 
 * @copyright 2005-2014 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */

namespace Gino\App\Statistics;

/**
 * @brief Modello che rappresenta un login sul sistema
 * 
 * @copyright 2005-2014 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class LogAccess extends \Gino\Model {

    public static $table = TBL_LOG_ACCESS;
    public static $columns;

    /**
     * @brief Costruttore
     * @return istanza di Gino.App.Statistics.LogAccess
     */
    function __construct($id) {
    	
    	$this->_tbl_data = TBL_LOG_ACCESS;
    	parent::__construct($id);
    }
    
    /**
     * Struttura dei campi della tabella di un modello
     *
     * @return array
     */
    public static function columns() {
    
    	$columns['id'] = new \Gino\IntegerField(array(
    		'name' => 'id',
    		'primary_key' => true,
    		'auto_increment' => true,
    	));
    	$columns['user_id'] = new \Gino\ForeignKeyField(array(
    		'name' => 'user_id',
    		'label' => _("Utente"),
    		'required' => true,
    		'foreign' => '\Gino\App\Auth\User',
    		'foreign_order' => 'lastname ASC, firstname ASC',
    		'add_related' => false,
    	));
    	$columns['date'] = new \Gino\DatetimeField(array(
    		'name' => 'date',
    		'label' => _('Data'),
    		'required' => true,
    		'auto_now' => false,
    		'auto_now_add' => true,
    	));
    	
    	return $columns;
    }

    /**
     * @brief Totale di accessi utente
     * @param int $user_id id utente
     * @return numero di accessi
     */
    public static function getCountForUser($user_id) {

        $db = \Gino\Db::instance();
        return $db->getNumRecords(self::$table, "user_id='$user_id'");
    }

    /**
     * @brief Ultimo accesso di un utente
     * @param int $user_id id utente
     * @return Gino.App.Statistics.LogAccess dell'ultimo accesso o null
     */
    public static function getLastForUser($user_id) {

        $res = null;

        $db = \Gino\Db::instance();
        $rows = $db->select('id', self::$table, "user_id='$user_id'", array('order'=>'date DESC', 'limit' => array(1, 1)));
        if($rows and count($rows)) {
            $res = new LogAccess($rows[0]['id']);
        }

        return $res;
    }
}

LogAccess::$columns=LogAccess::columns();