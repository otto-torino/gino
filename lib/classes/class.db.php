<?php
/**
 * @file class.db.php
 * @brief Contiene l'interfaccia DbManager e la classe db
 * 
 * @copyright 2005 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */

// Include il file di connessione al database plugin.mysql.php
include_once(PLUGIN_DIR.OS."plugin.mysql.php");

/**
 * @brief Interfaccia per le librerie di connessione al database
 * 
 * Definiscono una serie di metodi che le librerie di connessione al database dovranno implementare.
 * 
 * @copyright 2005 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
interface DbManager {

	function __construct($params);

	/**
	 * Apre una connessione al database
	 * 
	 * @return boolean
	 */
	public function openConnection();
	
	/**
	 * Chiude la connessione
	 */
	public function closeConnection();
	
	/**
	 * Istruzione begin
	 * 
	 * @return boolean
	 */
	public function begin();
	
	/**
	 * Istruzione rollback
	 * 
	 * @return boolean
	 */
	public function rollback();
	
	/**
	 * Istruzione commit
	 * 
	 * @return boolean
	 */
	public function commit();
	
	/**
	 * Esecuzione della query (istruzioni insert, update, delete)
	 * 
	 * @param string $query query
	 * @return boolean
	 */
	public function actionquery($query);
	
	/**
	 * Esegue una o più query concatenate dal punto e virgola
	 * 
	 * @param string $query query
	 * @return boolean
	 */
	public function multiActionquery($query);
	
	/**
	 * Esecuzione della query (istruzione select)
	 * 
	 * @param string $query query
	 * @return array
	 */
	public function selectquery($query);
	
	/**
	 * Numero di record risultanti da una istruzione SELECT
	 * 
	 * @param string $query	query
	 * @return integer
	 */
	public function resultselect($query);
	
	/**
	 * Numero di record interessati da una istruzione INSERT, UPDATE o DELETE
	 * 
	 * @return integer
	 */
	public function affected();
	
	/**
	 * Valore dell'ultimo ID generato da una colonna Auto Increment a seguito di una istruzione INSERT o UPDATE
	 * 
	 * @param string $table nome della tabella
	 * @return integer
	 */
	public function getlastid($table);
	
	/**
	 * Valore di Auto Increment
	 * 
	 * @param string $table nome della tabella
	 * @return integer
	 */
	public function autoIncValue($table);
	
	/**
	 * Ricava il valore di un campo a una data condizione
	 * 
	 * @param string $table nome della tabella
	 * @param string $field nome del campo del quale occorre ricavare il valore
	 * @param string $field_id nome del campo condizione (where)
	 * @param mixed $id valore del campo condizione (where)
	 * @return mixed
	 */
	public function getFieldFromId($table, $field, $field_id, $id);
	
	/**
	 * Verifica se una tabella esiste
	 * 
	 * @param string $table nome della tabella
	 * @return boolean
	 */
	public function tableexists($table);
	
	/**
	 * Recupera le informazioni sui campi di una tabella
	 * 
	 * @param string $table nome della tabella
	 * @return array
	 */
	public function fieldInformations($table);
	
	/**
	 * Istruzione per limitare i risultati di una query (LIMIT)
	 *
	 * @param integer $range
	 * @param integer $offset
	 * @return string
	 * 
	 * @code
	 * $this->_db->limit(1, 0);
	 * @endcode
	 * 
	 * sintassi SQL con PostgreSQL:
	 * @code
	 * $string = "LIMIT $range OFFSET $offset";
	 * @endcode
	 */
	public function limit($range, $offset);
	
	/**
	 * Istruzione per concatenare i campi
	 *
	 * @param mixed $sequence elenco di campi da concatenare
	 * @return string
	 * 
	 * @code
	 * $this->_db->concat(array("label", "' ('", "server", "')'"));
	 * @endcode
	 * 
	 * sintassi SQL con MySQL:
	 * @code
	 * concat(array("lastname", "' '", "firstname"))
	 * @endcode
	 * sintassi SQL con PostgreSQL:
	 * @code
	 * $string = implode(' || ', $sequence);
	 * @endcode
	 * sintassi SQL con SQL Server:
	 * @code
	 * $string = implode(' + ', $sequence);
	 * $concat = $string;
	 * @endcode
	 */
	public function concat($sequence);
	
	/**
	 * Effettua il dump del database
	 * 
	 * @param string $file percorso completo del file da scrivere
	 * @return boolean
	 */
	public function dumpDatabase($file);
	
	/**
	 * Informazioni sulla struttura di una tabella del database
	 * 
	 * @param string $table nome della tabella
	 * @return array
	 * 
	 * In MySQL l'array è nella forma \n
	 * @code
	 * array(
	 *   'primary_key'=>'primary_key_name', 
	 *   'keys'=>array('keyname1', 'keyname2'), 
	 *   'fields'=>array(
	 *     'fieldname1'=>array(
	 *       'property1" => "value1', 
	 *       'property2" => "value2', 
	 *       [...]
	 *     ), 
	 *     'fieldname2'=>array(
	 *       'property1" => "value1', 
	 *       'property2" => "value2', 
	 *       [...]
	 *     ), 
	 *     [...]
	 *   )
	 * )
	 * @endcode
	 * 
	 * Per ogni campo si recuperano le seguenti proprietà:
	 * - @b order: the ordinal position
	 * - @b deafult: the default value
	 * - @b null: whether the field is nullable or not
	 * - @b type: the field type (varchar, int, text, ...)
	 * - @b max_length: the field max length
	 * - @b n_int: the number of int digits
	 * - @b n_precision: the number of decimal digits
	 * - @b key: the field key if set
	 * - @b extra: extra information
	 * - @b enum: valori di un campo enumerazione
	 */
	public function getTableStructure($table);
}

/**
 * @brief Classe dalla quale vengono creati gli oggetti che si interfacciano al database
 * 
 * Le librerie di connessione al database sono sottoclassi di questa (che funziona come "scheletro") e vengono instanziate nel metodo instance()
 * 
 * @copyright 2005 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
abstract class db extends singleton {

	/* DB Configuration Paramethers */
	private static $_db_host = DB_HOST;
	private static $_db_user = DB_USER;
	private static $_db_pass = DB_PASSWORD;
	private static $_db_dbname = DB_DBNAME;
	private static $_db_charset = DB_CHARSET;
	private static $_db_schema = DB_SCHEMA;
	
	/**
	 * Istanzia la classe che si occupa della connessione al database
	 * @return object
	 */
	public static function instance() {

		$class = get_class();

		// singleton, return always the same instance
		if(array_key_exists($class, self::$_instances) === false) {

			if(DBMS=='mysql') {
				self::$_instances[$class] = new mysql(
					array(
					"connect"=>true,
					"host"=>self::$_db_host,
					"user"=>self::$_db_user,
					"password"=>self::$_db_pass,
					"db_name"=>self::$_db_dbname,
					"charset"=>self::$_db_charset
					)
				);
			}
		}

		return self::$_instances[$class];
	}
}
?>
