<?php
/**
 * @file class.db.php
 * @brief Contiene l'interfaccia DbManager e la classe db
 * 
 * @copyright 2005 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */

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
	 * Libera tutta la memoria utilizzata dal Result Set
	 * 
	 * @param array $result risultato della query
	 * @return boolean
	 */
	public function freeresult($result);
	
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
	 * Utilizzato dalla classe options per costruire il form delle opzioni di una classe.
	 * 
	 * @param string $table nome della tabella
	 * @return array
	 */
	public function fieldInformations($table);
	
	/**
	 * Uniforma i tipi di dato dei campi
	 * 
	 * @param mixed $type tipo di dato come definito dalle singole librerie
	 * @return string
	 * 
	 * Il tipo di dato di un campo deve essere uno dei seguenti:
	 *   - @a char, input form
	 *   - @a text, textarea form
	 *   - @a int, input form (se length>1) o radio button (length=1)
	 *   - @a bool, radio button
	 *   - @a date, input form di tipo data
	 */
	public function conformType($type);
	
	/**
	 * Istruzione per limitare i risultati di una query (LIMIT)
	 *
	 * @param integer $range numero di elementi da mostrare
	 * @param integer $offset record di partenza (key)
	 * @return string (condizione limit)
	 * 
	 * Esempio
	 * @code
	 * $this->_db->limit(1, 0);
	 * @endcode
	 */
	public function limit($range, $offset);
	
	/**
	 * Distinct keyword
	 *
	 * @param string $fields nome/nomi dei campi separati da virgola
	 * @param array $options array associativo di opzioni
	 *   - @b alias (string)
	 * @return string
	 */
	public function distinct($fields, $options);
	
	/**
	 * Istruzione per concatenare i campi
	 *
	 * @param mixed $sequence elenco di campi da concatenare
	 * @return string
	 * 
	 * Esempi:
	 * @code
	 * $this->_db->concat(array("label", "' ('", "server", "')'"));
	 * $this->_db->concat(array("lastname", "' '", "firstname"))
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
	 * L'array deve essere così strutturato: \n
	 * @code
	 * 'primary_key' => string 'primary_key_name'
	 * 'keys' => 
	 *   array (key_name[, ...])
	 * 'fields' => 
	 *   array (field_1=>array (size=10), field_2=>array (size=10)[, ...])
	 * @endcode
	 * 
	 * Per ogni campo vengono definite le chiavi:
	 *   - @b order (string): the ordinal position
	 *   - @b deafult (null or mixed): the default value
	 *   - @b null (string): whether the field is nullable or not ('NO' or 'YES')
	 *   - @b type (string): the field type (varchar, int, text, ...); deve ritornare un valore compatibile con quelli definiti in propertyObject::dataType()
	 *   - @b max_length (null or string): the field max length (es. '200' in un campo varchar)
	 *   - @b n_int (string): the number of int digits
	 *   - @b n_precision (integer): the number of decimal digits
	 *   - @b key (string): the field key if set (ex. 'PRI' for primary key, 'UNI' for unique key)
	 *   - @b extra (string): extra information (ex. auto_increment for an auto-increment field)
	 *   - @b enum (null or string): valori di un campo enumerazione (es. ''yes','no'')
	 * 
	 * In MySQL:
	 * @code
	 * field_float			'n_int' => int 0		'n_precision' => int 0
	 * field_float(10,2)	'n_int' => string '10'	'n_precision' => string '2'
	 * field_decimal(10,2)	'n_int' => string '10'	'n_precision' => string '2'
	 * @endcode
	 */
	public function getTableStructure($table);
	
	/**
	 * Recupera il nome dei campi di una tabella
	 * 
	 * @param string $table nome della tabella
	 * @return array
	 */
	public function getFieldsName($table);
	
	/**
	 * Numero di record interessati da una query di selezione
	 * 
	 * @param string $table nome della tabella
	 * @param string $where condizione della query
	 * @param string $field nome del campo di selezione per il conteggio dei record
	 * @return integer
	 */
	public function getNumRecords($table, $where, $field);
	
	/**
	 * Costruisce una query di selezione
	 * 
	 * @see limit()
	 * @param mixed $fields elenco dei campi
	 * @param mixed $tables elenco delle tabelle
	 * @param string $where condizione della query
	 * @param array $options array associativo di opzioni
	 *   - @b order (string): ordinamento
	 *   - @b distinct (string): nome/nomi dei campisui quali quali applicare la keyword DISTINCT
	 *   - @b limit (mixed): limitazione degli elementi
	 *     - string, condizione di limitazione degli elementi
	 *     - array, valori per il range di limitazione (array(offset, range))
	 *   - @b debug (boolean): se vero stampa a video la query
	 * @return string
	 */
	public function query($fields, $tables, $where, $options);
	
	/**
	 * Costruisce ed esegue una query di selezione
	 * 
	 * @see query()
	 * @see selectquery()
	 * @param mixed $fields elenco dei campi
	 * @param mixed $tables elenco delle tabelle
	 * @param string $where condizione della query
	 * @param array $options array associativo di opzioni (vedi le opzioni del metodo query())
	 * @return array
	 */
	public function select($fields, $tables, $where, $options);
	
	/**
	 * Costruisce ed esegue una query di inserimento
	 * 
	 * @see actionquery()
	 * @param array $fields elenco dei campi con i loro valori; se il valore di un campo è un array, con la chiave @a sql è possibile passare una istruzione SQL
	 *   - array(field1=>value1, field2=>value2), esempio:
	 *     @code
	 *     array('name'=>$name, 'description'=>$description, 'room'=>$room)
	 *     @endcode
	 *   - array(field1=>array('sql'=>sql_command), field2=>value2), esempio:
	 *     @code
	 *     array('label'=>$label, 'orderList'=>array('sql'=>"orderList-1"))
	 *     @endcode
	 * @param string $table nome della tabella
	 * @param boolean $debug se vero stampa a video la query
	 * @return boolean
	 */
	public function insert($fields, $table, $debug);
	
	/**
	 * Costruisce ed esegue una query di aggiornamento
	 * 
	 * @see actionquery()
	 * @param array $fields elenco dei campi con i loro valori; se il valore di un campo è un array, con la chiave @a sql è possibile passare una istruzione SQL
	 *   - array(field1=>value1, field2=>value2), esempio:
	 *     @code
	 *     array('name'=>$name, 'description'=>$description, 'room'=>$room)
	 *     @endcode
	 *   - array(field1=>array('sql'=>sql_command), field2=>value2), esempio:
	 *     @code
	 *     array('label'=>$label, 'orderList'=>array('sql'=>"orderList-1"))
	 *     @endcode
	 * @param string $table nome della tabella
	 * @param string $where condizione della query
	 * @param boolean $debug se vero stampa a video la query
	 * @return boolean
	 */
	public function update($fields, $table, $where, $debug);
	
	/**
	 * Costruisce ed esegue una query di eliminazione
	 * 
	 * @see actionquery()
	 * @param string $table nome della tabella
	 * @param string $where condizione della query
	 * @param boolean $debug se vero stampa a video la query
	 * @return boolean
	 */
	public function delete($table, $where, $debug);

  /**
	 * Eliminazione di una tabella
	 * 
	 * @param string $table nome della tabella
	 * @return boolean
	 */
	public function drop($table);

	/**
	 * Controlla che il valore del campo non sia gia presente in tabella
	 * 
	 * @param string $table nome della tabella
	 * @param string $field campo da cercare
	 * @param string $value valore da confrontare
	 * @param array $options
	 *   array associativo di opzioni
	 *   - except_id (integer): valore ID del record per il quale non effettuare il controllo
	 * @return boolean
	 */
  public function columnHasValue($table, $field, $value, $options=array());
	
	/**
	 * Definizione delle Join
	 * 
	 * @param string $table nome della tabella
	 * @param string $condition condizione della join
	 * @param string $option opzione della join
	 *   - left
	 *   - right
	 *   - outer
	 *   - inner
	 *   - left outer
	 *   - right outer
	 * @return string
	 */
	public function join($table, $condition, $option);
	
	/**
	 * Permette di combinare delle istruzioni SELECT
	 * 
	 * @see selectquery()
	 * @param array $queries query da unire (viene seguito l'ordine nell'array)
	 * @param array $options
	 *   array associativo di opzioni
	 *   - @b debug (boolean):  se vero stampa a video la query
	 *   - @b instruction (string): istruzione (default UNION)
	 * @return array
	 * 
	 * Le regole di base per la combinazione dei set di risultati di più query tramite l'istruzione UNION: \n
	 * - Tutte le query devono includere lo stesso numero di colonne nello stesso ordine. \n
	 * - I tipi di dati devono essere compatibili.
	 */
	public function union($queries, $options=array());
	
	/**
	 * Restore di un file (ad es. di un backup)
	 * 
	 * @param string $table nome della tabella
	 * @param string $filename nome del file da importare
	 * @param array $options
	 *   array associativo di opzioni
	 *   - @b fields (array): nomi dei campi
	 *   - @b delim (string): stringa che viene usata per separare tra loro i valori dei campi
	 *   - @b enclosed (string): stringa utilizzata per racchiudere i valori di tipo stringa
	 *   - @b escaped (string): carattere di escape, cioè quello utilizzato prima dei caratteri speciali
	 *   - @b lineend (string): stringa utilizzata come separatore tra i record
	 *   - @b hasheader (boolean): se il file comincia con una riga contenente i nomi dei campi
	 * @return boolean
	 */
	public function restore($table, $filename, $options=array());
	
	/**
	 * Dump di una tabella
	 * 
	 * @param string $table nome della tabella
	 * @param string $filename nome del file completo di percorso
	 * @param array $options
	 *   array associativo di opzioni
	 *   - @b delim (string): stringa che viene usata per separare tra loro i valori dei campi
	 *   - @b enclosed (string): stringa utilizzata per racchiudere i valori di tipo stringa
	 * @return string (nome del file di dump)
	 */
	public function dump($table, $filename, $options=array());
	
	/**
	 * Aggiunge le sequenze di escape ai caratteri speciali in una stringa per l'uso in una istruzione SQL, tenendo conto dell'attuale set di caratteri della connessione
	 * 
	 * @param mixed $string 
	 * @return mixed
	 */
	public function escapeString($string);
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

			if(DBMS == 'mysql' || DBMS == 'mssql' || DBMS == 'sqlsrv' || DBMS == 'odbc')
			{
				$lib_class = DBMS;
				$lib_file = PLUGIN_DIR.OS."plugin.".$lib_class.".php";
				
				if(file_exists($lib_file))
				{
					include_once($lib_file);
					
					self::$_instances[$class] = new $lib_class(
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
		}

		return self::$_instances[$class];
	}
}
?>
