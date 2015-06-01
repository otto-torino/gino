<?php
/**
 * @file class.Db.php
 * @brief Contiene l'interfaccia Gino.DbManager e le classi Gino.Db e Gino.SqlParse
 *
 * @copyright 2005-2015 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */

namespace Gino;

/**
 * @brief Interfaccia per le librerie di connessione al database
 *
 * Definisce i metodi che le librerie di connessione al database devono implementare.
 *
 * @copyright 2005-2015 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
interface DbManager {

    /**
     * @brief Costruttore
     * @param array $params parametri di connessione al db
     */
    function __construct($params);

    /**
     * @brief Restituisce informazioni sull'esecuzione delle query
     * @return informazioni
     */
    public function getInfoQuery();

    /**
     * @brief Apre una connessione al database
     * @return TRUE in caso di successo, genera un eccezione altrimenti
     */
    public function openConnection();

    /**
     * @brief Chiude la connessione al database
     * @return void
     */
    public function closeConnection();

    /**
     * @brief Istruzione begin
     * @return bool, risultato istruzione
     */
    public function begin();

    /**
     * @brief Istruzione rollback
     * @return bool, risultato istruzione
     */
    public function rollback();

    /**
     * @brief Istruzione commit
     * @return bool risultato istruzione
     */
    public function commit();

    /**
     * @brief Esegue una o più query concatenate dal punto e virgola
     *
     * @description Il metodo viene utilizzato per l'installazione dei pacchetti.
     * @param string $file_content contenuto del file sql
     * @return bool, risultato
     */
    public function multiActionQuery($file_content);

    /**
     * @brief Libera tutta la memoria utilizzata dal set di risultati
     *
     * @param array $result risultato della query
     * @return bool, risultato
     */
    public function freeresult($result);

    /**
     * @brief Valore dell'ultimo ID generato da una colonna Auto Increment a seguito di una istruzione INSERT o UPDATE
     * 
     * @param string $table nome della tabella
     * @return ultimo id generato
     */
    public function getLastId($table);

    /**
     * @brief Valore di Auto Increment
     * 
     * @param string $table nome della tabella
     * @return auto increment
     */
    public function autoIncValue($table);

    /**
     * @brief Ricava il valore di un campo a una data condizione
     *
     * @param string $table nome della tabella
     * @param string $field nome del campo del quale occorre ricavare il valore
     * @param string $field_id nome del campo condizione (where)
     * @param mixed $id valore del campo condizione (where)
     * @param array $options
     * @return valore del campo
     */
    public function getFieldFromId($table, $field, $field_id, $id, $options);

    /**
     * @brief Verifica l'esistenza di una tabella
     *
     * @param string $table nome della tabella
     * @return bool
     */
    public function tableexists($table);

    /**
     * @brief Recupera le informazioni sui campi di una tabella
     *
     * @description Utilizzato dalla classe Gino.Options per costruire il form delle opzioni di una classe.
     *
     * @param string $table nome della tabella
     * @return informazioni in array
     */
    public function fieldInformations($table);

    /**
     * @brief Uniforma i tipi di dato dei campi
     *
     * @description Elenco dei tipi di dato validi nella definizione di un campo
     *              - @a char, input form
     *              - @a text, textarea form
     *              - @a int, input form (se length>1) o radio button (length=1)
     *              - @a bool, radio button
     *              - @a date, input form di tipo data
     * 
     * @param mixed $type tipo di dato come definito dalle singole librerie
     * @return string (char|text|int|bool|date)
     */
    public function conformFieldType($type);
	
    /**
     * @brief Istruzione per limitare i risultati di una query (LIMIT)
     *
     * Esempio
     * @code
     * $this->_db->limit(1, 0);
     * @endcode
     *
     * @param integer $range numero di elementi da mostrare
     * @param integer $offset record di partenza (key)
     * @return LIMIT condition
     */
    public function limit($range, $offset);

    /**
     * @brief Distinct keyword
     *
     * @param string $fields nome/nomi dei campi separati da virgola
     * @param array $options array associativo di opzioni
     *   - @b alias (string): nome dell'alias del distinct
     *   - @b remove_table (boolean): rimuove il nome della tabella dalla definizione del campo
     * @return DISTINCT statement
     */
    public function distinct($fields, $options);

    /**
     * @brief Istruzione per concatenare i campi
     *
     * Esempi:
     * @code
     * $this->_db->concat(array("label", "' ('", "server", "')'"));
     * $this->_db->concat(array("lastname", "' '", "firstname"))
     * @endcode
     *
     * @param mixed $sequence elenco di campi da concatenare
     * @return CONCAT statement
     */
    public function concat($sequence);

    /**
     * @brief Effettua il dump del database
     *
     * @param string $file percorso completo del file da scrivere
     * @return risultato operazione, bool
     */
    public function dumpDatabase($file);

    /**
     * @brief Informazioni sulla struttura di una tabella del database
     *
     * L'array deve essere così strutturato: \n
     * @code
     * array(
     *   'primary_key' => string 'primary_key_name'
     *   'keys' => 
     *     array (key_name[, ...])
     *   'fields' => 
     *     array (field_1=>array (size=10), field_2=>array (size=10)[, ...])
     * )
     * @endcode
     *
     * Per ogni campo vengono definite le chiavi:
     *   - @b order (string): the ordinal position
     *   - @b default (null or mixed): the default value
     *   - @b null (string): whether the field is nullable or not ('NO' or 'YES')
     *   - @b type (string): the field type (varchar, int, text, ...); must return a compatible value with those defined in Model::dataType()
     *   - @b max_length (null or string): the field max length (es. '200' in varchar field)
     *   - @b n_int (string): the number of int digits
     *   - @b n_precision (integer): the number of decimal digits
     *   - @b key (string): the field key if set (ex. 'PRI' for primary key, 'UNI' for unique key)
     *   - @b extra (string): extra information (ex. auto_increment for an auto-increment field)
     *   - @b enum (null or string): valori di un campo enumerazione (es. ''yes','no'')
     *
     * @param string $table nome della tabella
     * @return array di informazioni
     */
    public function getTableStructure($table);
    
    /**
     * Reimposta il corretto tipo di dato di un campo quando il valore recuperato da una istruzione select è di un tipo non corrispondente (vedi PDO_SQLSRV)
     * 
     * @param string $data_type tipo di dato come definito da Gino.Model::dataType()
     * @param mixed $value valore del campo di un record (Gino.Model::$_p)
     * @return mixed
     */
    public function changeFieldType($data_type, $value);

    /**
     * @brief Recupera il nome dei campi di una tabella
     *
     * @param string $table nome della tabella
     * @return array con i nomi dei campi
     */
    public function getFieldsName($table);

    /**
     * @brief Numero di record interessati da una query di selezione
     *
     * @param string $table nome della tabella
     * @param string $where condizione della query
     * @param string $field nome del campo di selezione per il conteggio dei record
     * @param array $options opzioni del metodo select()
     * @return numero di record
     */
    public function getNumRecords($table, $where, $field, $options);
    
    /**
     * @brief Costruisce una query di selezione
     *
     * @param mixed $fields elenco dei campi
     * @param mixed $tables elenco delle tabelle
     * @param string $where condizione della query
     * @param array $options array associativo di opzioni
     *   - @b order (string): ordinamento
     *   - @b group_by (string): elenco dei campi da raggruppare (istruzione GROUP BY)
     *   - @b distinct (string): nome/nomi dei campi sui quali applicare la keyword DISTINCT
     *   - @b limit (mixed): limitazione degli elementi
     *     - string, condizione di limitazione degli elementi
     *     - array, valori per il range di limitazione (array(offset, range))
     *   - @b debug (boolean): se vero stampa a video la query
     * @return query
     */
    public function query($fields, $tables, $where, $options);

    /**
     * @brief Esegue una query di selezione passando direttamente la query
     * 
     * @param string $query istruzione sql da eeguire
     * @param array $options array associativo di opzioni
     *   - @b statement (string): tipologia di query
	 *     - @a select (default)
	 *     - @a action
	 *   - opzioni del metodo select()
	 * @return array (select statement) or boolean
     */
    public function execCustomQuery($query, $options);
    
    /**
     * @brief Esegue una query di selezione passando i parametri di costruzione della query
     * 
     * @see query()
     * @param mixed $fields elenco dei campi
     * @param mixed $tables elenco delle tabelle
     * @param string $where condizione della query
     * @param array $options array associativo di opzioni
     *   - @b custom_query (string): query completa
     *   - @b cache (boolean): indica se salvare in cache (se abilitata) i risultati della query (default true)
     *   - @b identity_keyword (string): codice identificativo dei dati in cache
     *   - @b time_caching (integer): tempo di durata della cache
     *   - opzioni del metodo query()
     * @return array di risultati
     */
    public function select($fields, $tables, $where, $options);

    /**
     * @brief Costruisce ed esegue una query di inserimento
     *
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
     * @return risultato operazione, bool
     */
    public function insert($fields, $table, $debug);

    /**
     * @brief Costruisce ed esegue una query di aggiornamento
     *
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
     * @return risultato operazione, bool
     */
    public function update($fields, $table, $where, $debug);

    /**
     * @brief Costruisce ed esegue una query di eliminazione
     *
     * @param string $table nome della tabella
     * @param string $where condizione della query
     * @param boolean $debug se vero stampa a video la query
     * @return risultato operazione, bool
     */
    public function delete($table, $where, $debug);

   /**
     * @brief Eliminazione di una tabella
     *
     * @param string $table nome della tabella
     * @return risultato dell'operazione, bool
     */
    public function drop($table);

    /**
     * @brief Controlla che il valore del campo non sia gia presente in tabella
     *
     * @param string $table nome della tabella
     * @param string $field campo da cercare
     * @param string $value valore da confrontare
     * @param array $options
     *   array associativo di opzioni
     *   - except_id (integer): valore ID del record per il quale non effettuare il controllo
     * @return TRUE se presente, FALSE altrimenti
     */
  public function columnHasValue($table, $field, $value, $options=array());

    /**
     * @brief Definizione delle Join
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
     * @return join clause
     */
    public function join($table, $condition, $option);

    /**
     * @brief Permette di combinare delle istruzioni SELECT
     *
     * Le regole di base per la combinazione dei set di risultati di più query tramite l'istruzione UNION: \n
     * - Tutte le query devono includere lo stesso numero di colonne nello stesso ordine. \n
     * - I tipi di dati devono essere compatibili.
     *
     * @see select()
     * @param array $queries query da unire (viene seguito l'ordine nell'array)
     * @param array $options
     *   array associativo di opzioni
     *   - @b debug (boolean):  se vero stampa a video la query
     *   - @b instruction (string): istruzione (default UNION)
     *   - @b cache (boolean): indica se salvare in cache (se abilitata) i risultati della query (default true)
     * @return array di risultati
     */
    public function union($queries, $options=array());

    /**
     * @brief Restore di un file (ad es. di un backup)
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
     *   - @b hasheader (boolean): indica se il file comincia con una riga contenente i nomi dei campi
     * @return risultato dell'operazione, bool
     */
    public function restore($table, $filename, $options=array());

    /**
     * @brief Dump di una tabella
     *
     * @param string $table nome della tabella
     * @param string $path_to_file nome del file completo di percorso
     * @param array $options
     *   array associativo di opzioni
     *   - @b where (string): condizioni della query
     *   - @b delim (string): stringa che viene usata per separare tra loro i valori dei campi
     *   - @b enclosed (string): carattere utilizzato per racchiudere i valori di tipo stringa
     * @return stringa (nome del file di dump)
     */
    public function dump($table, $path_to_file, $options=array());

    /**
     * @brief Aggiunge le sequenze di escape ai caratteri speciali in una stringa per l'uso in una istruzione SQL, tenendo conto dell'attuale set di caratteri della connessione
     *
     * @param mixed $string
     * @return stringa
     */
    public function escapeString($string);
}

/**
 * @brief Classe Factory e Singleton usata per creare oggetti che si interfacciano al database
 * 
 * Le librerie di connessione al database sono sottoclassi di questa (che funziona come "scheletro") e vengono instanziate nel metodo instance()
 * 
 * @copyright 2005-2015 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
abstract class Db extends singleton {

    /* DB Configuration Paramethers */
	private static $_dbms = DBMS;
    private static $_db_host = DB_HOST;
    private static $_db_user = DB_USER;
    private static $_db_pass = DB_PASSWORD;
    private static $_db_dbname = DB_DBNAME;
    private static $_db_charset = DB_CHARSET;
    private static $_db_schema = DB_SCHEMA;

    /**
     * @brief Istanzia la classe che si occupa della connessione al database
     * @description Garantisce che tutti gli oggetti ricevano sempre la stessa istanza Singleton.
     * @return object
     */
    public static function instance() {

        $class = get_class();

        // singleton, return always the same instance
        if(array_key_exists($class, self::$_instances) === false) {

        	if(DBMS == 'mysql' || DBMS == 'sqlsrv')
            {
                $lib_class = USE_PDO ? 'pdo' : DBMS;
                $lib_file = PLUGIN_DIR.OS."plugin.".$lib_class.".php";
                
                $lib_driver = $lib_class."_".DBMS;
                $lib_driver_file = PLUGIN_DIR.OS."plugin.".$lib_driver.".php";

                if(file_exists($lib_file))
                {
                    include_once($lib_file);
                    
                    if(USE_PDO && file_exists($lib_driver_file))
                    {
                    	include_once($lib_driver_file);
                    	$lib_class = '\Gino\Plugin\\'.$lib_driver;
                    }
                    else
                    {
                    	$lib_class = '\Gino\Plugin\\'.$lib_class;
                    }

                    self::$_instances[$class] = new $lib_class(
                        array(
                        	"connect"=>true,
                       		"dbms"=>self::$_dbms,
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

/**
 * @brief Classe per gestire il parser dei file sql (Code freely adapted from phpBB Group)
 *
 * @copyright 2015 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class SqlParse {
	
	/**
	 * Strip the sql comment lines out of an uploaded sql file specifically for mssql and postgres type files in the install
	 * 
	 * @param string $output
	 * @return string
	 */
	public static function remove_comments(&$output) {
		
		$lines = explode("\n", $output);
		$output = "";
	
		// try to keep mem. use down
		$linecount = count($lines);
	
		$in_comment = false;
		for($i = 0; $i < $linecount; $i++)
		{
			if( preg_match("/^\/\*/", preg_quote($lines[$i])) )
			{
				$in_comment = true;
			}
	
			if( !$in_comment )
			{
				$output .= $lines[$i] . "\n";
			}
	
			if( preg_match("/\*\/$/", preg_quote($lines[$i])) )
			{
				$in_comment = false;
			}
		}
		
		unset($lines);
		return $output;
	}
	
	/**
	 * Strip the sql comment lines out of an uploaded sql file
	 *
	 * @param string $sql file contents
	 * @return array
	 */
	public static function remove_remarks($sql) {
		
		$lines = explode("\n", $sql);
		
		// try to keep mem. use down
		$sql = "";
	
		$linecount = count($lines);
		$output = "";
	
		for ($i = 0; $i < $linecount; $i++)
		{
			if (($i != ($linecount - 1)) || (strlen($lines[$i]) > 0))
			{
				if (isset($lines[$i][0]) && $lines[$i][0] != "#")
				{
					$output .= $lines[$i] . "\n";
				}
				else
				{
					$output .= "\n";
				}
				// Trading a bit of speed for lower mem. use here.
				$lines[$i] = "";
			}
		}
		return $output;
	}
	
	/**
	 * Split an uploaded sql file into single sql statements
	 * 
	 * Note: expects trim() to have already been run on $sql.
	 * 
	 * @param string $sql file contents
	 * @param string $delimiter delimiter
	 * @return array
	 */
	public static function split_sql_file($sql, $delimiter) {
		
		// Split up our string into "possible" SQL statements.
		$tokens = explode($delimiter, $sql);
	
		// try to save mem.
		$sql = "";
		$output = array();
	
		// we don't actually care about the matches preg gives us.
		$matches = array();
	
		// this is faster than calling count($oktens) every time thru the loop.
		$token_count = count($tokens);
		for ($i = 0; $i < $token_count; $i++)
		{
			// Don't wanna add an empty string as the last thing in the array.
			if (($i != ($token_count - 1)) || (strlen($tokens[$i] > 0)))
			{
				// This is the total number of single quotes in the token.
				$total_quotes = preg_match_all("/'/", $tokens[$i], $matches);
				// Counts single quotes that are preceded by an odd number of backslashes,
				// which means they're escaped quotes.
				$escaped_quotes = preg_match_all("/(?<!\\\\)(\\\\\\\\)*\\\\'/", $tokens[$i], $matches);
	
				$unescaped_quotes = $total_quotes - $escaped_quotes;
	
				// If the number of unescaped quotes is even, then the delimiter did NOT occur inside a string literal.
				if (($unescaped_quotes % 2) == 0)
         		{
					// It's a complete sql statement.
					$output[] = $tokens[$i];
					// save memory.
					$tokens[$i] = "";
				}
				else
				{
					// incomplete sql statement. keep adding tokens until we have a complete one.
					// $temp will hold what we have so far.
					$temp = $tokens[$i] . $delimiter;
					// save memory..
					$tokens[$i] = "";
	
					// Do we have a complete statement yet?
					$complete_stmt = false;
	
					for ($j = $i + 1; (!$complete_stmt && ($j < $token_count)); $j++)
					{
						// This is the total number of single quotes in the token.
						$total_quotes = preg_match_all("/'/", $tokens[$j], $matches);
						// Counts single quotes that are preceded by an odd number of backslashes,
						// which means they're escaped quotes.
						$escaped_quotes = preg_match_all("/(?<!\\\\)(\\\\\\\\)*\\\\'/", $tokens[$j], $matches);
	
						$unescaped_quotes = $total_quotes - $escaped_quotes;
	
						if (($unescaped_quotes % 2) == 1)
						{
							// odd number of unescaped quotes. In combination with the previous incomplete
							// statement(s), we now have a complete statement. (2 odds always make an even)
							$output[] = $temp . $tokens[$j];
	
							// save memory.
							$tokens[$j] = "";
							$temp = "";
	
							// exit the loop.
                  			$complete_stmt = true;
                  			// make sure the outer loop continues at the right point.
                  			$i = $j;
						}
						else
						{
							// even number of unescaped quotes. We still don't have a complete statement.
							// (1 odd and 1 even always make an odd)
							$temp .= $tokens[$j] . $delimiter;
							// save memory.
							$tokens[$j] = "";
						}
					} // for..
				} // else
			}
		}
		
		return $output;
	}
	
	/**
	 * Elenco delle singole query presenti in un file sql
	 * 
	 * @param string $options array associativo di opzioni
	 *   - @b file_schema (string): percorso al file sql
	 *   - @b content_schema (string): contenuto del file sql
	 * @return array (query)
	 */
	public static function getQueries($options=array()) {
		
		$file_schema = gOpt('file_schema', $options, false);
		$content_schema = gOpt('content_schema', $options, null);
		
		if(!$file_schema && !$content_schema) return array();
		
		if($file_schema)
		{
			$sql_query = @fread(@fopen($file_schema, 'r'), @filesize($file_schema)) or die('problem ');
			//$sql_query = file_get_contents($file_schema);
		}
		else $sql_query = $content_schema;
		
		$sql_query = self::remove_remarks($sql_query);
		$sql_query = self::split_sql_file($sql_query, ';');
		
		return $sql_query;
	}
}
