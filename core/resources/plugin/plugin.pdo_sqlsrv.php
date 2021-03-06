<?php
/**
 * @file plugin.pdo_sqlsrv.php
 * @brief Contiene la classe pdo_sqlsrv
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
use Gino\SqlParse;

/**
 * @brief Driver specifico per la connessione a un database SQL Server attraverso la libreria PDO
 * 
 * @copyright 2015-2018 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class pdo_sqlsrv extends pdo {

	/**
	 * @see Gino.Plugin.pdo::setDSN()
	 * 
	 * Example of configuration parameters
	 * @code
	 * define("DB_HOST", "(local)\SQLEXPRESS");	// (local): host name
	 * define("DB_PORT", "1433");
	 * @endcode
	 */
	protected function setDSN() {
	
		$dsn = $this->_dbms.":Server=".$this->_db_host.";Database=".$this->_db_name;
		
		return $dsn;
	}
	
	/**
	 * @see Gino.Plugin.pdo::setAttribute()
	 */
	protected function setAttribute() {
	
		return array(
			//\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION
		);
	}
	
	/**
	 * @see \Gino\Plugin\pdo::checkRowsFromSelect()
	 */
	protected function checkRowsFromSelect($result) {
	
		$rows = $result ? 1 : 0;
		return $rows;
	}
	
	/**
	 * @see Gino.Plugin.pdo::multiActionQuery()
	 * @see Gino.SqlParse::getQueries()
	 */
	public function multiActionQuery($file_content) {
	
		$result = true;
		$queries = \Gino\SqlParse::getQueries(array('content_schema'=>$file_content));
		if(count($queries))
		{
			$debug = false;
			
			foreach($queries AS $query)
			{
				$res = $this->execCustomQuery($query, array('statement'=>'action'));
				if(!$res) $result = false;
				
				if($debug)
				{
					printf("%s\n", $query);
					printf("-----------------\n");
				}
			}
		}
		
		return $this->_result ? true : false;
	}

	/**
	 * @see Gino.Plugin.pdo::autoIncValue()
	 */
	public function autoIncValue($table) {

		$query = "SELECT IDENT_CURRENT('$table') AS NextId";
		$a = $this->select(null, null, null, array('custom_query'=>$query, 'cache'=>false));
		if($a && isset($a[0]))
		{
			$auto_increment = $a[0]['NextId'];
		}
		else $auto_increment = 0;
		
		$auto_increment++;
		
		return $auto_increment;
	}
	
	/**
	 * @see Gino.Plugin.pdo::tableexists()
	 */
	public function tableexists($table){
		
		$query = "SELECT * FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA='".DB_SCHEMA."' AND TABLE_TYPE='BASE TABLE' AND TABLE_NAME='$table'";
		$a = $this->select(null, null, null, array('custom_query'=>$query));
		if($a)
			return true;
		else
			return false;
	}
	
	/**
	 * @see Gino.Plugin.pdo::limit()
	 * 
	 * Examples
	 * @code
	 * //Returning the first 100 rows from a table called employee:
	 * select top 100 * from employee
	 * //Returning the top 20% of rows from a table called employee:
	 * select top 20 percent * from employee 
	 * @endcode
	 */
	public function limit($range, $offset=0) {
		
		$limit = "TOP $range";
		
		return $limit;
	}
	
	/**
	 * @see Gino.Plugin.pdo::distinct()
	 */
	public function distinct($fields, $options=array()) {
		
		$alias = \Gino\gOpt('alias', $options, null);
		$remove_table = \Gino\gOpt('remove_table', $options, true);
		
		if(!$fields) return null;
		
		if($remove_table)
		{
			$a_fields = explode(',', $fields);
			$a_data = array();
			foreach($a_fields AS $field)
			{
				$a_value = explode('.', trim($field));
				$a_data[] = $a_value[count($a_value)-1];
			}
			$fields = implode(', ', $a_data);
		}
		
		$data = "DISTINCT ($fields)";
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
				/*$sequence2 = array();
				 foreach($sequence AS $value)
				 {
				 if(is_int($value))
				 {
				 $len = strlen($value);
				 $value = "CAST($value AS VARCHAR($len))";
				 }
				 	
				 $sequence2[] = $value;
				 }*/
				$concat = implode(' + ', $sequence);
			}
			else $concat = $sequence[0];
		}
		else $concat = $sequence;
		
		return $concat;
	}

	/**
	 * @see Gino.Plugin.pdo::dumpDatabase()
	 * @see listTables()
	 */
	public function dumpDatabase($file) {

		$tables = $this->listTables();
		
		while ($td = $this->fetch($tables, array('mode'=>'NUM'))) {
		
			$table = $td[0];
				
			$r = $this->queryResults("
				SELECT * FROM INFORMATION_SCHEMA.COLUMNS 
				WHERE TABLE_CATALOG='".$this->_db_name."' AND TABLE_NAME='$table' 
				ORDER BY ORDINAL_POSITION
			");
			if($r)
			{
				$insert_sql = "";
				$d = $this->fetch($r, array('mode'=>'NUM'));
				$d[1] .= ";";
				$SQL[] = str_replace("\n", "", $d[1]);
				
				$table_query = $this->queryResults("SELECT * FROM $table");
				
				$num_fields = $this->getNumberCols();
				while ($fetch_row = $this->fetch($table_query, array('mode'=>'NUM'))) {
					$insert_sql .= "INSERT INTO $table VALUES (";
					for ($n=1; $n<=$num_fields; $n++) {
						$m = $n - 1;
						$insert_sql .= "'".$this->escapeString($fetch_row[$m]).($n==$num_fields ? "" : "', ");
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
	
	private function listTables() {
	
		$query = "SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_CATALOG='".$this->_db_name."' AND TABLE_TYPE='BASE_TABLE'";
		$res = $this->queryResults($query);
		
		return $res;
	}
	
	/**
	 * @see Gino.Plugin.pdo::changeFieldType()
	 */
	public function changeFieldType($data_type, $value) {
		
		if($data_type == 'IntegerField') {
			settype($value, 'int');
		}
		return $value;
	}
	
	/**
	 * @see Gino.Plugin.pdo::restore()
	 */
	public function restore($table, $filename, $options=array()) {      
	
		$delimiter = \Gino\gOpt('delim', $options, ',');
		$lineend = \Gino\gOpt('lineend', $options, '\\r\\n');
		
		$query = "BULK INSERT ".$table." FROM '".$filename."'
		WITH (
			FIELDTERMINATOR = '".$delimiter."',
			ROWTERMINATOR = '".$lineend."'
		)";
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
		$limit = \Gino\gOpt('limit', $options, null);	// top command
		$debug = \Gino\gOpt('debug', $options, false);
	
		if($statement == 'select')
		{
			if(!$fields OR !$tables) return null;
				
			if(is_array($limit) && count($limit))	// pagination
			{
				return $this->setQueryUsingPaging($fields, $tables, $where, $options);
			}
			
			$top = is_string($limit) ? $limit : '';
				
			$query = "SELECT ";
			if($top) $query .= $top.' ';
			$query .= "$fields FROM $tables";
			if($where) $query .= ' '.$where;
			if($group_by) $query .= ' '.$group_by;
			if($having) $query .= ' HAVING '.$having;
			if($order) $query .= ' '.$order;
		}
	
		return $query;
	}
	
	/**
	 * @see Gino.Plugin.pdo::conformFieldType()
	 */
	public function conformFieldType($meta) {
	
		$type = strtolower($meta['sqlsrv:decl_type']);
		if(preg_match("#\s#", $type))
		{
			$a = explode(" ", $type);
			$type = $a[0];
		}
		
		switch ($type) {
			case 'tinyint':
				return 'bool';
			case 'smallint':
				return 'int';
			case 'null':
				return null;
			case 'varchar':
			case 'nvarchar':
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
		
		return "[$field]";
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
		
		$fields = "[".implode('],[', $a_fields)."]";
		return $fields;
	}
	
	/**
	 * @see Gino.Plugin.pdo::SQLForFieldInformations()
	 */
	protected function SQLForFieldInformations($table) {
	
		return "SELECT TOP 1 * FROM ".$table;
	}
	
	/**
	 * @see Gino.Plugin.pdo::SQLForDump()
	 */
	protected function SQLForDump($table, $path_to_file, $delimiter, $enclosed, $where) {
	
		return "SELECT * INTO OUTFILE '".$path_to_file."' 
		FIELDS TERMINATED BY '".$delimiter."' 
		$enclosed
		LINES TERMINATED BY '\r\n' 
		FROM ".$table.$where;
	}
	
	/**
	 * Imposta una query con paginazione
	 * 
	 * @param string|array $fields
	 * @param string|array $tables
	 * @param string $where
	 * @param array $options
	 *   - @b order (string)
	 *   - @b limit (array)
	 *   - @b debug (boolean)
	 *   - @b distinct (string)
	 * @return string
	 */
	private function setQueryUsingPaging($fields, $tables, $where=null, $options=array()) {
	
		$order = \Gino\gOpt('order', $options, null);
		$limit = \Gino\gOpt('limit', $options, null);
		$debug = \Gino\gOpt('debug', $options, false);
		$distinct = \Gino\gOpt('distinct', $options, null);
		
		$qtables = is_array($tables) ? implode(",", $tables) : $tables;
		if(!$where) $where = '';
		
		$offset = $limit[0];
		$range = $limit[1];
		settype($offset, 'int');
		
		if(is_string($fields)) $fields = explode(',', $fields);
		
		$clean_fields = array();	// solo i nomi dei campi
		$func_fields = array();		// nomi dei campi comprensivi delle eventuali funzioni (ad es. distinct)
		
		foreach($fields AS $f)
		{
			$field = trim($f);
			preg_match("#^([a-zA-Z ]+)\(([a-zA-Z0-9_]+)\)$#", $field, $matches);
			if(isset($matches[2]) && $matches[2])
			{
				$clean_fields[] = $matches[2];
			}
			else
			{
				$clean_fields[] = $field;
			}
				
			$func_fields[] = $field;
		}
	
		if($order)
		{
			$order_field = array();
			$split_order_field = explode(',', $order);	// per gestire i casi tipo: name ASC, descr DESC
			
			foreach($split_order_field AS $s)
			{
				$a_order = array();
				$split_field = explode(' ', $s);
				foreach($split_field AS $f)
				{
					if(preg_match("#\.#", $f))	// ricerco la ricorrenza [nome_tabella].[nome_campo]
					{
						$a_field = explode('.', $f);
						$field_name = trim($a_field[1]);
						$a_order[] = $field_name;
	
						// verifico se il campo di ordinamento è presente nell'elenco dei campi del select
						// in caso negativo lo aggiungo all'elenco dei nomi comprensivi delle eventuali funzioni (subquery)
						if(!in_array($field_name, $clean_fields))
							$func_fields[] = $field_name;
					}
					else $a_order[] = trim($f);
				}
				$order_field[] = implode(' ', $a_order);
			}
			
			$clean_order = implode(', ', $order_field);
			$qorder = $order;
			
			if(!preg_match("#ORDER BY#i", $clean_order))
			{
				$clean_order = "ORDER BY ".$clean_order;
				$qorder = "ORDER BY ".$qorder;
			}
		}
		else
		{
			$clean_order = $qorder = "ORDER BY id";
		}
	
		$clean_fields = implode(', ', $clean_fields);
		$func_fields = implode(', ', $func_fields);
		
		$query = "SELECT $clean_fields FROM (
			SELECT $func_fields, row_number () over ($qorder) - 1 as rn
			FROM $qtables $where) rn_subquery
		WHERE rn between $offset and ($offset+$range)-1 $clean_order";
		
		if($debug) echo $query;
	
		return $query;
	}
}

?>
