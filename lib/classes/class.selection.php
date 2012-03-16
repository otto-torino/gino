<?php
/*
CREATE TABLE `main_selection` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `instance` int(11) NOT NULL,
  `reference` int(11) NOT NULL,
  `aggregator` int(11) NOT NULL,
  `priority` smallint(2) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

ATTENZIONE:
Negli elenchi a livello di formattazione è a volte preferibile utilizzare la classe 'Form' senza tabelle:
$gform = new Form('gform', 'post', true, array('tblLayout'=>false));

----------------------------------------------
UTILIZZO TIPO NEWSLETTER
scelta di eventi (reference) di una newsletter (aggregator)
----------------------------------------------
- reference:	id del record che è stato selezionato (es. un evento o una news)
- aggregator:	id del contenitore (es. una newsletter)

----------------------------------------------
UTILIZZO CLASSICO
scelta di file (reference) associati o meno a un documento (aggregator)
----------------------------------------------
- reference: 	id di un file
- aggregator:	id di qualcosa cui è collegato il file (ad es. un progetto)

Esempio:
(1) Form

$sels = new selection(0, $id, $this->_instance, $this->_tbl_selection, array('input_name'=>'s_check'));
$check = $sels->fCheck($gform, array('where'=>"aggregator='$reference'"));
						
$itemContent .= $htmlListFile->item($name.$date, array($check), '', false, '', '', 'checkbox');

(2) Action

// Lista non paginata
$sels = new selection(0, 0, $this->_instance, $this->_tbl_selection, array('aggregator'=>$reference, 'input_name'=>'s_check'));
$sels->instance = $this->_instance;
$sels->sortNumber(0);
$sels->updateListDbData();
*/

class selection extends propertyObject {

	protected $_tbl_data;
	private $_gform;
	private $_ref_id, $_instance;
	private $_sort_number;
	
	private $_aggregator, $_input_name;
	
	/*
	@param options	array
		aggregator	integer
		input_name	string
	*/
	function __construct($id, $reference, $instance, $table, $options=null) {
	
		$this->_tbl_data = $table;
		
		parent::__construct($this->initP($id));
		
		$this->_aggregator = (isset($options['aggregator']) AND $options['aggregator'] != 0) ? $options['aggregator'] : 0;
		$this->_input_name = (isset($options['input_name']) AND $options['input_name'] != '') ? $options['input_name'] : 'check';
		
		$this->_ref_id = $reference;
		$this->_instance = $instance;
	}
	
	private function initP($id) {
	
		$db = db::instance();
		$query = "SELECT * FROM ".$this->_tbl_data." WHERE id='$id'";
		$a = $db->selectquery($query);
		if(sizeof($a)>0) return $a[0];
		else return array('id'=>null, 'instance'=>null, 'reference'=>null, 'aggregator'=>null, 'priority'=>null);
	}
	
	public function setInstance($value) {
		
		if($this->_p['instance']!=$value && !in_array('instance', $this->_chgP)) $this->_chgP[] = 'instance';
		$this->_p['instance'] = $value;
		return true;
	}
	
	public function setPriority($postLabel) {
		
		$value = cleanVar($_POST, $postLabel, 'int', '');
		if($this->_p['priority']!=$value && !in_array('priority', $this->_chgP)) $this->_chgP[] = 'priority';
		$this->_p['priority'] = $value;
		return true;
	}
	
	public function sortNumber($value) {
		
		$this->_sort_number = $value;
		return true;
	}

	/*
	 * text				testo dopo il checkbox
	 * disabled			checkbox disabilitato
	 * view_disabled	visualizzare se l'elemento è selezionato anche se è disabilitato
	 * where			condizioni aggiuntive per la query di ricerca degli elementi selezionati
	 * 					(ad es. 'where'=>array("aggregator='$reference'"))
	 */
	public function fCheck($gform, $options=array()) {
		
		$text = array_key_exists('text', $options) ? $options['text'] : '';
		$disabled = array_key_exists('disabled', $options) ? $options['disabled'] : false;
		$view_disabled = array_key_exists('view_disabled', $options) ? $options['view_disabled'] : false;
		$where = array_key_exists('where', $options) ? $options['where'] : '';
		
		$options_check = array();
		
		if($disabled)
			$options_check['other'] = "disabled=\"disabled\"";
		
		if(!$view_disabled)
		{
			$checked = false;
		}
		else
		{
			if(is_array($where) AND sizeof($where) > 0)
				$where = "AND ".implode(" AND ", $where);
			else
				$where = '';
			
			$db = db::instance();
			$query = "SELECT reference FROM ".$this->_tbl_data." WHERE reference='{$this->_ref_id}' AND instance='{$this->_instance}' $where";
			$a = $db->selectquery($query);
			if(sizeof($a)) $checked = true; else $checked = false;
		}
		
		$input = $gform->checkbox("{$this->_input_name}[]", $checked, $this->_ref_id, $options_check);
		if($checked AND !$disabled)
			$input .= $gform->hidden("old{$this->_input_name}[]", $this->_ref_id);
		
		if(!empty($text))
			$input .= " $text";
		
		$input .= ' ';
		
		return $input;
	}
	
	// update di una lista completa, non paginata
	public function updateListDbData() {
		
		$check = isset($_POST[$this->_input_name]) ? $_POST[$this->_input_name] : array();
		$oldcheck = isset($_POST['old'.$this->_input_name]) ? $_POST['old'.$this->_input_name] : array();
		
		$count = $this->_sort_number;
		
		if(sizeof($oldcheck) > 0)
		{
			foreach ($oldcheck AS $value)
			{
				$query = "DELETE FROM ".$this->_tbl_data." WHERE reference='$value' AND instance='".$this->_instance."'";
				$this->_db->actionquery($query);
			}
		}
		
		if(sizeof($check) > 0)
		{
			foreach ($check AS $value)
			{
				$query = "INSERT INTO ".$this->_tbl_data." (instance, reference, aggregator, priority) 
				VALUES (".$this->_p['instance'].", $value, ".$this->_aggregator.", $count)";
				$this->_db->actionquery($query);
				$count = $this->updateCount($count);
			}
		}
		
		return null;
	}
	
	// update di una lista paginata: occorre confrontare il prima e il dopo
	public function updateDbData() {
		
		$check = isset($_POST[$this->_input_name]) ? $_POST[$this->_input_name] : array();
		$oldcheck = isset($_POST['old'.$this->_input_name]) ? $_POST['old'.$this->_input_name] : array();
		
		$count = $this->_sort_number;
		
		if(sizeof($oldcheck) > 0)	// esistevano
		{
			if(sizeof($check) > 0)	// esisteranno
			{
				foreach($check AS $value)
				{
					if(!in_array($value, $oldcheck))	// aggiungere nuovi elementi
					{
						$query = "INSERT INTO ".$this->_tbl_data." (instance, reference, aggregator, priority) 
						VALUES (".$this->_p['instance'].", $value, ".$this->_aggregator.", $count)";
						$this->_db->actionquery($query);
						$count = $this->updateCount($count);
					}
				}
				
				// Eliminazione elementi vecchi che non esistono più
				$diff = array_diff($oldcheck, $check);
				foreach($diff AS $value)
				{
					$query = "DELETE FROM ".$this->_tbl_data." WHERE reference='$value' AND instance='".$this->_instance."'";
					$this->_db->actionquery($query);
				}
			}
			else	// elimino tutti gli elementi perché non ne esisteranno
			{
				foreach ($oldcheck AS $value)
				{
					$query = "DELETE FROM ".$this->_tbl_data." WHERE reference='$value' AND instance='".$this->_instance."'";
					$this->_db->actionquery($query);
				}
			}
			return true;
		}
		elseif(sizeof($check) > 0)	// esisteranno
		{
			foreach($check AS $value)
			{
				$query = "INSERT INTO ".$this->_tbl_data." (instance, reference, aggregator, priority) 
				VALUES (".$this->_p['instance'].", $value, ".$this->_aggregator.", $count)";
				$this->_db->actionquery($query);
				$count = $this->updateCount($count);
			}
			return true;
		}
		else return null;
	}
	
	private function updateCount($value){
		
		if($value  > 0) $value++;
		return $value;
	}
	
	/**
	 * Elenco record selezionati
	 *
	 * @param array $options
	 * @return array
	 * 
	 * Opzioni
	 * --------------------
	 * sort 	boolean 	ordinamento (priority)
	 * class	string		nome della classe da istanziare per recuperare i valori di 'reference'
	 * method	string		nome di metodo di 'class' da richiamare:
	 * 						1. per richiamare una porzione di testo (ex. 'printForNewsletter')
	 * 						2. per recuperare i valori dei record collegati a 'reference';
	 * 						in questo caso ogni elemento dell'array di ritorno deve essere un array dei valori dell' elemento.
	 * font		string		famiglia di font
	 * view		string		list: presenta un elenco sintetico dei record
	 */
	public function getListItems($options=array()) {

		$sort = array_key_exists('sort', $options) ? $options['sort'] : true;
		$class = array_key_exists('class', $options) ? $options['class'] : '';
		$method = array_key_exists('method', $options) ? $options['method'] : '';
		$font_family = array_key_exists('font', $options) ? $options['font'] : '';
		$view = array_key_exists('view', $options) ? $options['view'] : '';
		
		if(!$class)
			$class = $this->referenceClass($this->_instance);
		
		$where = $this->_aggregator ? "AND aggregator='".$this->_aggregator."'" : '';
		if($sort) $order = "ORDER BY priority ASC"; else $order = '';
		
		$items = array();
		$db = db::instance();
		$query = "SELECT reference FROM ".$this->_tbl_data." WHERE instance='{$this->_instance}' $where $order";
		$a = $db->selectquery($query);
		if(sizeof($a))
		{
			foreach($a as $b)
			{
				/*
				 * ex: class -> news, method -> getData
				 * 
				 * $events = $sels->getListItems(array('class'=>'news', 'method'=>'getData'));
				 * for($i=0, $end=sizeof($events);$i<$end;$i++)
				 * ...
				 * $evt = $events[$i];
				 * $evt['id'], $evt['title'], ...
				 * 
				 * ex: method -> printForNewsletter, view -> list
				 */
				if($method)
				{
					$new = new $class($this->_instance);
					$items[] = $new->$method($b['reference'], array('view'=>$view, 'font'=>$font_family));
				}
				/*
				 * ex: class calendarEvent
				 * 
				 * $events = $sels->getListItems(array('class'=>'calendarEvent'));
				 * for($i=0, $end=sizeof($events);$i<$end;$i++)
				 * ...
				 * $evt = $events[$i];
				 * $evt->id, $evt->date, ...
				 */
				else
				{
					$items[] = new $class($b['reference']);
				}
			}
			return $items;
		}
		else return null;
	}
	
	private function referenceClass($instanceId){
		
		$query = "SELECT id, class as name, name as instance FROM sys_module WHERE id='$instanceId' AND type='class' AND masquerade='no'";
		$a = $this->_db->selectquery($query);
		if(sizeof($a) > 0)
		{
			foreach($a AS $b)
			{
				$class_name = htmlChars($b['name']);
				$instanceName = htmlChars($b['instance']);
				
				if(method_exists($class_name, 'newsletter'))
				{
					$array_class = array();
					$list = call_user_func(array($class_name, 'newsletter'));
					
					if(sizeof($list) > 0)
					{
						if(array_key_exists('include', $list) AND $list['include'] != '')
							include_once(APP_DIR.OS.$list['include']);
						
						if(array_key_exists('classData', $list) AND $list['classData'] != '')
							return $list['classData'];
					}
					else return null;
				}
			}
		}
		
		return null;
	}
}
?>