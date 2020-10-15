<?php
/**
 * @file sqlcode.php
 * @brief Contiene la classe sqlcode
 */

namespace Gino;

/**
 * @brief Contiene query e codice sql personalizzato
 * 
 * Ogni query e ogni porzione di codice sql deve essere inserita in un metodo il cui nome, per praticità, riporta le sue coordinate ed è così composto: \n
 * nome_classe + _ + nome_metodo
 * 
 * Esempio
 * @code
 * include_once(RESOURCES_DIR.OS."sqlcode.php");
 * $obj = new sqlcode();
 * $call = get_class().'_methodName';
 * $query = $obj->$call($param1, $param2, $param3);
 * @endcode
 */
class sqlcode {

	private $_dbms;
	
	function __construct() {
		
		$this->_dbms = DBMS;
	}
	
	public function admin_actionUser($table1, $table2, $value1, $value2, $value3, $value4) {
		
		if($this->_dbms == 'mysql')
		{
			$query = "DELETE FROM gu USING $table1 AS gu INNER JOIN $table2 AS u WHERE gu.group_id='$value1' AND u.user_id=gu.user_id AND u.role<='$value2' AND u.role>'$value3' AND gu.instance='$value4'";
		}
		else $query = '';
		
		return $query;
	}
}

?>