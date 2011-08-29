<?php
/**
 *
 *  Class export
 *
 *  Properties:
 *
 *  (string) _s default ",": the field separator, default to comma
 *  (string) _table: the name of the table to export
 *  (mixed)  _fields: the fields to export:
 *                   *: all fields
 *                   * -(field1,field2): all fields except from field1 and field2
 *                   field1,field2: the fields field1 and field2
 *                   array("field1", "field2"): the fields field1 and field2
 *  (bool)   _head: whether or not to print fields' headings
 *  (mixed)  _rids: the records ids to export:
 *  		     *: all records
 *  		     1,3,5: the records with id=1, id=3 and id=5
 *  		     array(1,3,5): the records with id=1, id=3 and id=5 
 *  (string) _order: the field to order the query results by
 *  (array)  _data: competitive to _table: the array containing the data to export:
 *                   array(0=>array("head1", "head2", "head3"), 
 *                         1=>array("value1 record 1", "value 2 record 1", "value 3 record 1"), 
 *                         2=>array("value1 record 2", "value 2 record 2", "value 3 record 2")
 *                   )
 *
 *  Methods are provided to set all these properties:
 *
 *  -setTable
 *  -setSeparator
 *  -setFields
 *  -setHead
 *  -setRids
 *  -setOrder
 *  -setData
 *
 *  Output method:
 *
 *  exportData($filename, $extension, output) : (file)
 *    (string) filename: the name of the file written (the absolute path if the output is file)
 *    (string) extension: the file extension 
 *    (string) output: file|stream 
 *
 *
**/
class export {

	private $_s = ",";

	private $_table;
	private $_head = true;
	private $_fields = '*';
	private $_rids = '*';
	private $_order;

	private $_data;

	public function setTable($table) {
		$this->_table = $table;	
	}

	public function setSeparator($s) {
		$this->_s = $s;
	}

	public function setFields($fields) {
		$this->_fields = $fields;
	}

	public function setHead($head) {
		$this->_head = $head;
	}

	public function setRids($rids) {
		$this->_rids = $rids;
	}

	public function setOrder($order) {
		$this->_order = $order;
	}

	public function setData($data) {
		$this->_data = $data;
	}

	public function exportData($filename, $extension, $output='stream') {

		if($extension=='csv') return $this->exportCsv($filename, $output);
		// many other extensions in the future 
	} 

	private function exportCsv($filename, $output) {
		
		$data = $this->getData();

		$csv = '';
		foreach($data as $row) {
			$cell = array();
			foreach($row as $v) $cell[] = enclosedField($v);
			$csv .= implode($this->_s, $cell)."\r\n";
		}

		if($output=='stream') { 
			header("Content-Type: plain/text");
			header("Content-Disposition: Attachment; filename=$filename");

			header("Pragma: no-cache");
			echo $csv;
			exit;
		}
		elseif($output=='file') {
			$fo = fopen($filename, "w");
			fwrite($fo, $csv);
			fclose($fo);
		}

	}

	private function getData() {

		if($this->_data) return $this->_data;
		if(!$this->_table) return array();

		$data = array();
		$head_fields = $this->getHeadFields();
		if(count($head_fields) && $this->_head) $data[] = $head_fields;

		if($this->_rids=='*') $where = '';
		elseif(is_array($this->_rids) && count($this->_rids)) 
			$where = "WHERE id='".implode("' OR id='", $this->_rids)."'";
		elseif(is_string($this->_rids) && strlen($this->_rids)>0)	
			$where = "WHERE id='".implode("' OR id='", explode(",",$this->_rids))."'";

		$order = $this->_order ? " ORDER BY ".$this->_order:"";

		$query_data = "SELECT ".implode(",", $head_fields)." FROM ".$this->_table." $where $order";
		$res = mysql_query($query_data);
		while($row = mysql_fetch_array($res, MYSQL_NUM)) 
			$data[] = $row;

		return $data;

	}

	private function getHeadFields() {
		
		if($this->_head && is_string($this->_fields) && preg_match("#\*#", $this->_fields)) {
			preg_match("#\* -\((.*)\)#", $this->_fields, $matches);
			$excluded_fields = isset($matches[1]) ? explode(",",$matches[1]):array();
			$head_fields = array();
			$query = "SHOW COLUMNS FROM ".$this->_table;
			$res = mysql_query($query);
			while($row = mysql_fetch_assoc($res)) {
				$results[] = $row;
			}
			mysql_free_result($res);
			foreach($results as $r) 
				if(!in_array($r['Field'], $excluded_fields)) $head_fields[] = $r['Field'];
		
		}
		elseif(is_string($this->_fields)) $head_fields = explode(",",$this->_fields);
		elseif(is_array($this->_fields)) $head_fields = $this->_fields;

		return $head_fields;

	}

}

?>
