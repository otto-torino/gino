<?php
/**
 * @file class.sort.php
 * @brief Contiene la classe sort
 * 
 * @copyright 2005 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */

/**
 * @brief Gestisce l'ordinamento di un insieme di elementi
 * 
 * @copyright 2005 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 * 
 * La libreria deve essere inclusa all'inizio del file della classe che la deve utilizzare:
 * @code
 * require_once(CLASSES_DIR.OS.'class.sort.php');
 * @endcode
 * 
 * Esempio 1
 * ---------------
 * Form
 * 
 * @code
 * $sort = new sort(array('table'=>$table, 'instance'=>$this->_instance));
 * $htmlList = new htmlList(array("numItems"=>count($items), "separator"=>false, "id"=>'priorityList'));
 * ...[ciclo]...
 * $link_sort = $sort->link();
 * $GINO .= $htmlList->item($name, array($link_sort), '', true, $content, "id$item->id", "sortable");
 * ...[/ciclo]...
 * $GINO .= $sort->jsLib($this->_home."?pt[{$this->_instanceName}-actionUpdateOrder]");
 * @endcode
 *
 * Esempio 2
 * ---------------
 * Action
 * 
 * @code
 * public function actionUpdateOrder() {
 *   $this->accessGroup('');
 *   $order = cleanVar($_POST, 'order', 'string', '');
 *   $items = explode(",", $order);
 *   $i=1;
 *   foreach($items as $item) {
 *     $sort = new sort(array('id'=>$item, 'instance'=>$this->_instance, 'table'=>'tbl_data', 'field_id'=>'reference'));
 *     $sort->priority = $i;
 *     $sort->updateDbData();
 *     $i++;
 *   }
 * }
 * @endcode
 * 
 * Se la classe principale prevede inoltre le selezioni occorre gestire i due fattori in modo unitario:
 * @code
 * $sels = new calendarSelection(0);
 * $sels->instance = $this->_instance;
 * if($this->_block == 'list')
 * {
 *   if($this->_manageSortSel) $sels->sortNumber(1); else $sels->sortNumber(0);
 *   $sels->updateListDbData();
 * }
 * else
 * {
 *   if($this->_manageSortSel)
 *   {
 *     $sort = new sort(array('table'=>calendarEvent::$_tbl_selection, 'instance'=>$this->_instance));
 *     $new = $sort->newPriority();
 *   }
 *   else $new = 0;
 *   $sels->sortNumber($new);
 *   $sels->updateDbData();
 * }
 * @endcode
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
	 * Costruttore
	 * 
	 * @param array $data
	 *   - @b id (integer): valore ID del record
	 *   - @b field_id (string): nome del campo ID (@a reference se siamo in una tabella di selezione)
	 *   - @b field_sort (string): nome del campo che contiene i valori di ordinamento
	 *   - @b table (string): nome della tabella con gli elementi da ordinare (può anche essere una tabella di selezione)
	 *   - @b instance (integer): valore ID dell'istanza
	 *   - @b aggregator (integer): valore del campo sul quale viene effettuato l'ordinamento nel caso in cui l'ordinamento sia relativo a un sottoinsieme di elementi
	 *   - @b field_aggregator (string): nome del campo del sottoinsieme di ordinamento
	 *   - @b ul_id (string): valore ID del contenitore della lista degli elementi (UL)
	 *   - @b link_class (string): classe del div che rappresenta il collegamento all'icona di ordinamento
	 *   - @b link_style (string): stile del div che rappresenta il collegamento all'icona di ordinamento
	 * @return void
	 */
	function __construct($data = array()) {
	
		foreach($data as $k=>$v) {
			if(array_key_exists($k, $this->_p)) $this->_p[$k] = $v;
		}
	}
	
	/**
	 * Ritorna il valore della proprietà
	 * 
	 * Permette di modificare i valori delle proprietà direttamente dalle classi dopo avere istanziato la classe htmlSection
	 * 
	 * @param string $pName
	 * @return mixed
	 */
	public function __get($pName) {
	
		if(method_exists($this, 'get'.$pName)) return $this->{'get'.$pName}();
		else return $this->_p[$pName];
	}
	
	/**
	 * Imposta il valore della proprietà
	 * 
	 * Permette di modificare i valori delle proprietà direttamente dalle classi dopo avere istanziato la classe htmlSection
	 * 
	 * @param string $pName
	 * @param mixed $value
	 * @return void
	 */
	public function __set($pName, $value) {

		if(method_exists($this, 'set'.$pName)) return $this->{'set'.$pName}($value);
		else $this->_p[$pName] = $value;
	}
	
	/**
	 * Imposta il valore della priorità
	 * @param integer $value
	 * @return boolean
	 */
	public function setPriority($value) {
		
		$this->_priority = $value;
		return true;
	}
	
	/**
	 * Mostra il DIV da utilizzare per l'ordinamento
	 * 
	 * @return string
	 */
	public function link() {
		
		return "<div class=\"".$this->_p['link_class']."\" style=\"".$this->_p['link_style']."\"></div>";
	}
	
	/**
	 * Script javascript che gestisce l'ordinamento
	 * 
	 * @param string $path_action percorso dell'url action della request ajax
	 * @param array $options
	 *   array associativo di opzioni
	 *   - @b reference (integer): valore ID del record in riferimento al quale viene effettuato l'ordinamento
	 * @return string
	 */
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
	
	/**
	 * Riporta il valore successivo all'ultimo in un ordinamento
	 * @return integer
	 */
	public function newPriority() {

		$db = db::instance();
		
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
	
	/**
	 * Effettua l'aggiornamento dei dati
	 * @return boolean
	 */
	public function updateDbData() {
	
		if($this->_p['id']) { 
			$query = "UPDATE {$this->_p['table']} SET {$this->_p['field_sort']}=".$this->_priority." WHERE {$this->_p['field_id']}='{$this->_p['id']}'";
		}
		else {
			if(!empty($this->_p['instance'])) $instance = $this->_p['instance']; else $instance = 0;
			$this->_priority = $this->newPriority();
			
			$query = "INSERT INTO {$this->_p['table']} ('{$this->_p['field_id']}', '{$this->_p['field_sort']}') VALUES ({$this->_p['id']}, $instance, {$this->_priority})";
		}
		
		$db = db::instance();
		$result = $db->actionquery($query);

		return $result;
	}
}
?>