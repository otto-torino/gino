<?php
/**
 * @file plugin.pdo_mysql.php
 * @brief Contiene la classe pdo_mysql
 * 
 * @copyright 2015-2018 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */

/**
 * @namespace Gino.Plugin
 * @description Namespace che comprende classi di tipo plugin
 */
namespace Gino\Plugin;

/**
 * @brief Driver specifico per la connessione a un database MYSQL attraverso la libreria PDO
 * 
 * @copyright 2015-2018 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 * 
 * NOTE
 * ---------------
 * ###Metodo dump()
 * Per poter effettuare il dump di un tabase MySQL occorre: \n
 *   - assegnare il permesso FILE all'utente del database: GRANT FILE ON *.* TO 'dbuser'@'localhost';
 *   - la directory di salvataggio deve avere i permessi read/write/execute, oppure deve avere come proprietario l'utente di sistema del database
 */
class pdo_mysql extends pdo {

	/**
	 * @see Gino.Plugin.pdo::setDSN()
	 * 
	 * Example of configuration parameters
	 * @code
	 * define("DB_HOST", "localhost");
	 * define("DB_PORT", "3306");
	 * @endcode
	 */
	protected function setDSN() {
		
		$dsn = $this->_dbms.":host=".$this->_db_host.";dbname=".$this->_db_name;
		
		if($this->_db_charset && in_array($this->_db_charset, pdo::getCharset())) {
			$dsn .= ";charset=".$this->_db_charset;
		}
		
		return $dsn;
	}
	
	/**
	 * @see Gino.Plugin.pdo::setAttribute()
	 */
	protected function setAttribute() {
		
		return array(
			\PDO::ATTR_EMULATE_PREPARES => false,
			\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION, 
			// \PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'")
		);
	}
	
	/**
	 * @see Gino.Plugin.pdo::setCharacterSet()
	 */
	protected function setCharacterSet() {
		
		if($this->_db_charset && in_array($this->_db_charset, pdo::getCharset()))
		{
			$this->_pdo->exec("SET NAMES ".$this->_db_charset);
		}
	}
	
	/*
	private function setUtf8() {
		
		$query = "SHOW VARIABLES LIKE 'character_set_database'";
		$res_db_charset = $this->queryResults($query);
		$charset_row = $this->fetch($res_db_charset);
		
		$this->queryResults("SET NAMES '".$charset_row['Value']."'");
		
		unset($res_db_charset, $charset_row);
	}
	*/

	/**
	 * @see Gino.Plugin.pdo::multiActionQuery()
	 */
	public function multiActionQuery($file_content) {
	
		$link = mysqli_connect($this->_db_host, $this->_db_user, $this->_db_password, $this->_db_name);
		$this->_result = mysqli_multi_query($link, $file_content);
		
		$debug = false;
		
		if($this->_result && $debug) {
			do {
				// store first result set
				if ($result = mysqli_store_result($link)) {
					while ($row = mysqli_fetch_row($result)) {
						printf("%s\n", $row[0]);
					}
					mysqli_free_result($result);
				}
				// print divider
				if (mysqli_more_results($link)) {
					printf("-----------------\n");
				}
			} while (mysqli_next_result($link));
		}
		
		mysqli_close($link);
		
		return $this->_result ? true : false;
	}

	/**
	 * @see Gino.Plugin.pdo::autoIncValue()
	 */
	public function autoIncValue($table) {

		$query = "SHOW TABLE STATUS LIKE '$table'";
		$a = $this->select(null, null, null, array('custom_query'=>$query, 'cache'=>false));
		if(sizeof($a) > 0)
		{
			foreach ($a AS $b)
			{
				$auto_increment = $b['Auto_increment'];
			}
		}
		else $auto_increment = 0;
		
		return $auto_increment;
	}
	
	/**
	 * @see Gino.Plugin.pdo::tableexists()
	 */
	public function tableexists($table){
		
		$query = "SHOW TABLES FROM `".$this->_db_name."`";
		$res = $this->queryResults($query);
		
		while($row = $this->fetch($res, array('mode'=>'NUM'))) {
			
			if($row[0] == $table) return true;
		}
		return false;
	}
	
	/**
	 * @see Gino.Plugin.pdo::limit()
	 */
	public function limit($range, $offset) {
		
		$limit = "LIMIT $offset, $range";
		return $limit;
	}
	
	/**
	 * @see Gino.Plugin.pdo::distinct()
	 */
	public function distinct($fields, $options=array()) {
		
		$alias = \Gino\gOpt('alias', $options, null);
		
		if(!$fields) return null;
		
		$data = "DISTINCT($fields)";
		if($alias) $data .= " AS $alias";
		
		return $data;
	}
	
	/**
	 * @see Gino.Plugin.pdo::concat()
	 */
	public function concat($sequence) {
		
		if(is_array($sequence))
		{
			if(sizeof($sequence) > 1)
			{
				$string = implode(',', $sequence);
				$concat = "CONCAT($string)";
			}
			else $concat = $sequence[0];
		}
		else $concat = $sequence;
		
		return $concat;
	}

	/**
	 * @see Gino.Plugin.pdo::dumpDatabase()
	 */
	public function dumpDatabase($file) {

		$query = "SHOW TABLES FROM ".$this->_db_name;
		$tables = $this->queryResults($query);
		
		while ($td = $this->fetch($tables, array('mode'=>'NUM'))) {

			$table = $td[0];
			
			$r = $this->queryResults("SHOW CREATE TABLE `$table`");
			if($r)
			{
				$insert_sql = "";
				$d = $this->fetch($r, array('mode'=>'NUM'));
				$d[1] .= ";";
				$SQL[] = str_replace("\n", "", $d[1]);
				
				$table_query = $this->queryResults("SELECT * FROM `$table`");
				
				$num_fields = $this->getNumberCols();
				while ($fetch_row = $this->fetch($table_query, array('mode'=>'NUM'))) {
					$insert_sql .= "INSERT INTO $table VALUES (";
					for ($n=1; $n<=$num_fields; $n++) {
						$m = $n - 1;
						$insert_sql .= "'".$this->escapeString($fetch_row[$m]).($n==$num_fields ? "" : "', ");	// $this->_pdo->quote
					}
					$insert_sql .= ");\n";
				}
				if ($insert_sql!= "") {
					$SQL[] = $insert_sql;
				}
			}
		}

		if(!($fo = fopen($file, 'wb'))) return false;
		if(!fwrite($fo, implode("\r", $SQL))) return false;
		fclose($fo);

		return true;
	}
	
	/**
	 * @see Gino.Plugin.pdo::restore()
	 */
	public function restore($table, $filename, $options=array()) {      
	
		$fields = \Gino\gOpt('fields', $options, null);
		$delimiter = \Gino\gOpt('delim', $options, ',');
		$enclosed = \Gino\gOpt('enclosed', $options, '"');
		$escaped = \Gino\gOpt('escaped', $options, '\\');
		$lineend = \Gino\gOpt('lineend', $options, '\\r\\n');
		$hasheader = \Gino\gOpt('hasheader', $options, false);
		
		$ignore = $hasheader ? "IGNORE 1 LINES " : "";
		if($fields) $fields = "(".implode(',', $fields).")";
		
		$query = 
		"LOAD DATA INFILE '".$filename."' INTO TABLE ".$table." ".
		"FIELDS TERMINATED BY '".$delimiter."' ENCLOSED BY '".$enclosed."' ".
		"ESCAPED BY '".$escaped."' ".
		"LINES TERMINATED BY '".$lineend."' ".$ignore.$fields;
		
		$this->_result = $this->queryResults($query, array('statement'=>'action'));
		if($this->_result)
			return true;
		else
			return false;
	}
	
	/**
	 * @see Gino.Plugin.pdo::buildQuery()
	 */
	protected function buildQuery($options=array()) {
	
		$statement = \Gino\gOpt('statement', $options, 'select');
		$fields = \Gino\gOpt('fields', $options, null);
		$tables = \Gino\gOpt('tables', $options, null);
		$where = \Gino\gOpt('where', $options, null);
		$group_by = \Gino\gOpt('group_by', $options, null);
		$having = \Gino\gOpt('having', $options, null);
		$order = \Gino\gOpt('order', $options, null);
		$limit = \Gino\gOpt('limit', $options, null);
		$debug = \Gino\gOpt('debug', $options, false);
	
		if($statement == 'select')
		{
			if(!$fields OR !$tables) return null;
				
			if(is_array($limit) && count($limit))
			{
				$qlimit = $this->limit($limit[1],$limit[0]);
			}
			elseif(is_string($limit))
			{
				$qlimit = $limit;
			}
			else $qlimit = null;
				
			$query = "SELECT $fields FROM $tables";
			if($where) $query .= ' '.$where;
			if($group_by) $query .= ' '.$group_by;
			if($having) $query .= ' HAVING '.$having;
			if($order) $query .= ' '.$order;
			if($qlimit) $query .= ' '.$qlimit;
		}
	
		return $query;
	}
	
	/**
	 * @see Gino.Plugin.pdo::conformFieldType()
	 */
	public function conformFieldType($meta) {
		
		$type = strtolower($meta['native_type']);
		
		switch ($type) {
			case 'tiny':
			case 'short':
			case 'long':
			case 'longlong';
			case 'int24':
				return 'int';
			case 'null':
				return null;
			case 'varchar':
			case 'var_string':
			case 'string':
				return 'char';
			case 'blob':
			case 'tiny_blob':
			case 'long_blob':
				return 'text';
			default:
				return $type;
		}
	}
	
	/**
	 * @see Gino.Plugin.pdo::setFieldName()
	 */
	protected function setFieldName($field) {
		
		return "`$field`";
	}
	
	/**
	 * @see Gino.Plugin.pdo::setFieldValue()
	 */
	protected function setFieldValue($value) {
		
		return "'$value'";
	}
	
	/**
	 * @see Gino.Plugin.pdo::arrayFieldsToString()
	 */
	protected function arrayFieldsToString($a_fields) {
		
		$fields = "`".implode('`,`', $a_fields)."`";
		return $fields;
	}
	
	/**
	 * @see Gino.Plugin.pdo::SQLForFieldInformations()
	 */
	protected function SQLForFieldInformations($table) {
	
		return "SELECT * FROM ".$table." LIMIT 0,1";
	}
	
	/**
	 * @see Gino.Plugin.pdo::SQLForDump()
	 */
	protected function SQLForDump($table, $path_to_file, $delimiter, $enclosed, $where) {
	
		return "SELECT * INTO OUTFILE '".$path_to_file."' 
		FIELDS TERMINATED BY '".$delimiter."' 
		$enclosed
		FROM ".$table.$where;
	}
}

?>
