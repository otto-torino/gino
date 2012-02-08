<?php
/*
Form:

$sort = new sort(array('table'=>$table, 'instance'=>$this->_instance));
$htmlList = new htmlList(array("numItems"=>count($items), "separator"=>false, "id"=>'priorityList'));
...[ciclo]...
$link_sort = $sort->link();
$GINO .= $htmlList->item($name, array($link_sort), '', true, $content, "id$item->id", "sortable");
...[/ciclo]...
$GINO .= $sort->jsLib($this->_home."?pt[{$this->_className}-actionUpdateOrder]");

Action:

	public function actionUpdateOrder() {
	
		$this->accessGroup('');
		
		$order = cleanVar($_POST, 'order', 'string', '');
		$items = explode(",", $order);
		$i=1;
		foreach($items as $item) {
			$sort = new sort(array('id'=>$item, 'instance'=>$this->_instance, 'table'=>$table, 'field_id'=>'reference'));
			$sort->priority = $i;
			$sort->updateDbData();
			$i++;
		}
	}

Se la classe principale prevede inoltre le selezioni occorre gestire i due fattori in modo unitario:

		...
		
		$sels = new calendarSelection(0);
		$sels->instance = $this->_instance;
		if($this->_block == 'list')
		{
			if($this->_manageSortSel) $sels->sortNumber(1); else $sels->sortNumber(0);
			$sels->updateListDbData();
		}
		else
		{
			if($this->_manageSortSel)
			{
				$sort = new sort(array('table'=>calendarEvent::$_tbl_selection, 'instance'=>$this->_instance));
				$new = $sort->newPriority();
			}
			else $new = 0;
			$sels->sortNumber($new);
			$sels->updateDbData();
		}
*/

class sort {

	private $_p = array(
			'id'=>null,
			'field_id'=>'id',
			'field_sort'=>'priority',
			'table'=>null,
			'instance'=>null,
			'aggregator'=>null,
			'field_aggregator'=>'aggregator',
			'ul_id'=>'priorityList',
			'link_class'=>'orderPriority',
			'link_style'=>"float:left;width:20px;height:20px;background:url('img/ico_sort.gif');cursor:move;margin-right:3px;"
		);
	private $_priority;

	/**
	 * 
	 * @param array $data
	 * 		id					integer		valore ID del record
	 * 		field_id			string		nome del campo ID
	 * 		field_sort			string		nome del campo che contiene i valori di ordinamento
	 * 		table				string		nome della tabella
	 * 		instance			integer		valore ID dell'istanza
	 * 		aggregator			integer		valore del campo sul quale viene effettuato l'ordinamento 
	 * 										nel caso in cui l'ordinamento sia relativo a un sottoinsieme di elementi
	 * 		field_aggregator	string		nome del campo del sottoinsieme di ordinamento
	 * 		ul_id				string		div ID
	 * 		link_class			string		classe del div che rappresenta il collegamento all'icona di ordinamento
	 * 		link_style			string		stile del div che rappresenta il collegamento all'icona di ordinamento
	 * return void
	 */
	function __construct($data = array()) {
	
		foreach($data as $k=>$v) {
			if(array_key_exists($k, $this->_p)) $this->_p[$k] = $v;
		}
	}
	
	public function __get($pName) {
	
		if(method_exists($this, 'get'.$pName)) return $this->{'get'.$pName}();
		else return $this->_p[$pName];
	}
	
	public function __set($pName, $value) {

		if(method_exists($this, 'set'.$pName)) return $this->{'set'.$pName}($value);
		else $this->_p[$pName] = $value;
	}
	
	public function setPriority($value) {
		
		$this->_priority = $value;
		return true;
	}
	
	public function link() {
		
		return "<div class=\"".$this->_p['link_class']."\" style=\"".$this->_p['link_style']."\"></div>";
	}
	
	public function jsLib($path_action, $options=array()){
		
		$reference = array_key_exists('reference', $options) ? "&ref=".$options['reference'] : '';
		
		$GINO = "<script>";
		$GINO .= "function message() { alert('"._("Ordinamento effettuato con successo")."')}";
		$GINO .= "var prioritySortables = new Sortables($('{$this->_p['ul_id']}'), {
					constrain: false,
					clone: true,
					handle: '.{$this->_p['link_class']}',
					onComplete: function() {
						var order = this.serialize(1, function(element, index) {
							return element.getProperty('id').replace('id', '');
						}).join(',');
						ajaxRequest('post', '$path_action', 'order='+order+'$reference&tbl={$this->_p['table']}', null, {'callback':message});
       					}

			})";
			$GINO .= "</script>";
		return $GINO;
	}
	
	public function newPriority() {

		$db = new db();
		
		$where = $this->_p['instance'] ? " WHERE instance='{$this->_p['instance']}'" : '';
		if($where AND $this->_p['aggregator'])
			$where .= " AND {$this->_p['field_aggregator']}='{$this->_p['aggregator']}'";
		
		$query = "SELECT MAX({$this->_p['field_sort']}) as m FROM ".$this->_p['table'].$where;
		$a = $db->selectquery($query);
		if(sizeof($a)>0) {
			return ($a[0]['m']+1);
		}
		return 1;
	}
	
	public function updateDbData() {
	
		if($this->_p['id']) { 
			$query = "UPDATE {$this->_p['table']} SET {$this->_p['field_sort']}=".$this->_priority." WHERE {$this->_p['field_id']}='{$this->_p['id']}'";
		}
		else {
			if(!empty($this->_p['instance'])) $instance = $this->_p['instance']; else $instance = 0;
			$this->_priority = $this->newPriority();
			
			$query = "INSERT INTO {$this->_p['table']} ('{$this->_p['field_id']}', '{$this->_p['field_sort']}') VALUES ({$this->_p['id']}, $instance, {$this->_priority})";
		}
		
		$db = new db();
		$result = $db->actionquery($query);

		return $result;
	}
}
?>