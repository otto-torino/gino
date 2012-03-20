<?php

include_once(PLUGIN_DIR.OS."plugin.mysql.php");

interface DbManager {

	function __construct($params);

	public function openConnection();
	public function closeConnection();
	public function begin();
	public function rollback();
	public function commit();
	public function actionquery($query);
	public function multiActionquery($query);
	public function selectquery($query);
	public function resultselect($query);	// numero di record risultanti da un select
	public function affected();				// numero di record interessati da una istruzione INSERT, UPDATE o DELETE
	public function getlastid($table);		// valore dell'ultimo ID dopo una istruzione INSERT o UPDATE
	public function autoIncValue($table);	// valore di Autoincrement
	public function getFieldFromId($table, $field, $field_id, $id);
	public function tableexists($table);
	public function fieldInformations($table);
	public function limit($range, $offset);	// istruzione per limitare i risultati di una query
	public function concat($sequence);		// istruzione per concatenare i campi
	public function dumpDatabase($file);

}

/*
* Factory Class which creates concrete db objects
*/
abstract class db extends singleton {

	/* DB Configuration Paramethers */
	private static $_db_host = DB_HOST;
	private static $_db_user = DB_USER;
	private static $_db_pass = DB_PASSWORD;
	private static $_db_dbname = DB_DBNAME;
	private static $_db_charset = DB_CHARSET;
	private static $_db_schema = DB_SCHEMA;
	
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
