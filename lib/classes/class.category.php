<?php
/**
 * @file class.category.php
 * @brief Contiene la classe Category
 * 
 * @copyright 2005 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */

/**
 * @brief Libreria per gestire una categorizzazione ad albero infinito
 * 
 * @copyright 2005 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 * 
 * Per poter utilizzare questa libreria occorre:
 * 1. includere la classe
 * 2. creare una tabella
 * @code
 * CREATE TABLE IF NOT EXISTS `[RIFERIMENTO-TABELLA]_ctg` (
 * `id` int(11) NOT NULL AUTO_INCREMENT,
 * `instance` int(11) NOT NULL,
 * `name` varchar(200) NOT NULL,
 * `parent` int(11) NOT NULL,
 * `description` text NOT NULL,
 * PRIMARY KEY (`id`)
 * ) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
 * @endcode
 */
class category extends adminTable {

	private $_tbl_data;
	
	/**
	 * @see adminTable::adminList()
	 * @see printTree()
	 * 
	 * Alle opzioni proprie del metodo adminTable::adminList() sono aggiunte le seguenti opzioni che vengono poi passate al metodo printTree(): \n
	 *   - @a css_list
	 *   - @a view
	 *   - @a list_order
	 */
	public function adminList($model, $options_view=array()) {

		$db = db::instance();
		
		$model_structure = $model->getStructure();
		$model_table = $model->getTable();
		
		$this->_tbl_data = $model_table;
		
		$this->_filter_fields = gOpt('filter_fields', $options_view, array());
		$list_title = gOpt('list_title', $options_view, ucfirst($model->getModelLabel()));
		$list_description = gOpt('list_description', $options_view, "<p>"._("Albero delle categorie")."</p>");
		$list_where = gOpt('list_where', $options_view, array());
		
		// filter form
		$tot_ff = count($this->_filter_fields);
		if($tot_ff) $this->setSessionSearch($model);
		
		// managing instance
		$query_where = array();
		if(array_key_exists('instance', $model_structure)) {
			$query_where[] = "instance='".$this->_controller->getInstance()."'";
		}
		
		//prepare query
		$query_selection = "DISTINCT(".$model_table.".id)";
		$query_table = array($model_table);
		if(count($list_where)) {
			$query_where = array_merge($query_where, $list_where);
		}
		$query_where_no_filters = implode(' AND ', $query_where);
		// filters
		if($tot_ff) {
			$this->addWhereClauses($query_where, $model);
		}
		
		$tot_records_no_filters_result = $db->select("COUNT(id) as tot", $query_table, $query_where_no_filters, null);
		$tot_records_no_filters = $tot_records_no_filters_result[0]['tot'];

		$tot_records_result = $db->select("COUNT(id) as tot", $query_table, implode(' AND ', $query_where), null);
		$tot_records = $tot_records_result[0]['tot'];

		//$records = $db->select($query_selection, $query_table, implode(' AND ', $query_where), null, null, true);
		//if(!$records) $records = array();
		
		$tree = $this->printTree($model, null, $options_view);
		$tot_records = $tree;
		
		if($this->_allow_insertion) {
			$link_insert = "<a href=\"".$this->editUrl(array('insert'=>1))."\">".pub::icon('insert')."</a>";
		}
		else {
			$link_insert = "";
		}

		$this->_view->setViewTpl('admin_tree_list');
		$this->_view->assign('title', $list_title);
		$this->_view->assign('description', $list_description);
		$this->_view->assign('link_insert', $link_insert);
		$this->_view->assign('tree', $tree);
		$this->_view->assign('tot_records', $tot_records);
		$this->_view->assign('form_filters_title', _("Filtri"));
		$this->_view->assign('form_filters', $tot_ff ? $this->formFilters($model, $options_view) : null);
		
		return $this->_view->render();
	}
	
	/**
	 * Print the category tree expanded, with the possibility to give 4 links: insert, modify, delete and view
	 * 
	 * Even the list class is custom
	 * 
	 * @see getChildren()
	 * @param object $model oggetto dell'elemento
	 * @param integer $parent valore ID della categoria superiore (default 0)
	 * @param array $options
	 *   array associativo di opzioni del metodo adminList(); trovano riscontro: 
	 *   - @b add_params_url (array)
	 *   - @b css_list (string)
	 *   - @b view (boolean)
	 *   - @b list_order (string)
	 * @return string
	 */
	public function printTree($model, $parent, $options_view=array()) {

		if(!$parent) $parent = 0;
		
		$addParamsUrl = gOpt('add_params_url', $options_view, array());
		$css_list = gOpt('css_list', $options_view, "admin");
		$view = gOpt('view', $options_view, true);
		$order = gOpt('list_order', $options_view, "name ASC");
		
		$buffer = '';
		
		$records = $this->_db->select("id", $this->_tbl_data, "parent='$parent' AND instance='".$this->_controller->getInstance()."'", $order);
		$tot_records = count($records);
		
		if(count($tot_records) > 0)
		{	
			$htmlList = $parent == 0
				? new htmlList(array("class"=>"$css_list", "numItems"=>$tot_records, "separator"=>true))
				: new htmlList(array("class"=>"$css_list inside", "numItems"=>$tot_records, "separator"=>true));
			$buffer = $htmlList->start();
			
			foreach($records as $r)
			{
				$id = $r['id'];
				
				$selected = ($model->id == $id) ? true : false;
				
				$record_model = new $model($id, $this->_controller);
				$record_model_structure = $record_model->getStructure();
				
				$record_value = (string) $record_model_structure['name'];
				
				if($view)
				{
					$add_params_view = array('view'=>1, 'id'=>$id);
					foreach($addParamsUrl AS $key=>$value)
					{
						$add_params_view[$key] = $value;
					}
					$record_value = "<a href=\"".$this->editUrl($add_params_view)."\">".$record_value."</a>";
				}
				
				$add_params_insert = array('insert'=>1, 'ref'=>$id);
				$add_params_edit = array('edit'=>1, 'id'=>$id);
				$add_params_delete = array('delete'=>1, 'id'=>$id);
				if(count($addParamsUrl))
				{
					foreach($addParamsUrl AS $key=>$value)
					{
						$add_params_insert[$key] = $value;
						$add_params_edit[$key] = $value;
						$add_params_delete[$key] = $value;
					}
				}
				
				$links = array();
				
				if($this->_allow_insertion) {
					$links[] = "<a href=\"".$this->editUrl($add_params_insert)."\">".pub::icon('insert', _("nuova sottocategoria"))."</a>";
				}
				if($this->_edit_deny != 'all' && !in_array($id, $this->_edit_deny)) {
					$links[] = "<a href=\"".$this->editUrl($add_params_edit)."\">".pub::icon('modify')."</a>";
				}
				if($this->_delete_deny != 'all' && !in_array($id, $this->_delete_deny)) {
					$links[] = "<a href=\"javascript: if(confirm('".htmlspecialchars(sprintf(_("Sicuro di voler eliminare \"%s\"?"), $record_model), ENT_QUOTES)."')) location.href='".$this->editUrl($add_params_delete)."';\">".pub::icon('delete')."</a>";
				}
			
				$itemContent = count($this->getChildren($id)) ? $this->printTree($model, $id, $options_view) : null;
				$buffer .= $htmlList->item($record_value, $links, $selected, true, $itemContent);
			}
			$buffer .= $htmlList->end();
		}

		return $buffer;
	}
	
	/**
	 * Gets first level or all category children
	 * 
	 * @param integer $parent
	 * @param array $options
	 *   array associativo di opzioni
	 *   - @b all (boolean)
	 *   - @b table (string)
	 *   - @b order (string)
	 * @return array
	 */
	public function getChildren($parent, $options=array()) {

		$all = gOpt('all', $options, false);
		$table = gOpt('table', $options, $this->_tbl_data);
		$order = gOpt('order', $options, "name ASC");
		
		$children = array();

		$query = "SELECT id FROM $table WHERE parent='".$parent."' AND instance='".$this->_controller->getInstance()."' ORDER BY $order";
		$a = $this->_db->selectquery($query);
		if(sizeof($a)>0) {
			foreach($a as $b)
			{
				$ctg_id = $b['id'];
				
				$children[] = $ctg_id;
				if($all) $children = array_merge($children, $this->getChildren($ctg_id, $options));
			}
		}

		return $children;
	}
	
	////// VERIFICARE ->

	/**
	 * Ritorna l'elenco di tutte le categorie superiori. Da utilizzare in un select form
	 * 
	 * @param array $options
	 *   array associativo di opzioni
	 *   - @b table (string)
	 *   - @b query (string)
	 * @return array
	 */
	public function inputTreeArray($options=array()) {
	
		$opt_table = gOpt('table', $options, null);
		$opt_query = gOpt('query', $options, null);
		$parent = gOpt('parent', $options, 0);
		
		if($opt_query)
			$query = $opt_query;
		elseif($opt_table)
			$query = "SELECT id, name FROM ".$opt_table." WHERE parent='0'";
		else
			return array();
		
		$db = db::instance();
		$ctg_ordered = $this->ctgParentTree($query, $parent);
		
		return $ctg_ordered;
	}
	
	private function setQueryTree($query, $parent) {
		
		$query = preg_replace('#(parent=\'[0-9]+\')#', "parent='$parent'", $query);
		
		return $query;
	}
	
	private function ctgParentTree($query, $parent, $level=0) {

		$db = db::instance();
		
		$array = array();
		
		$query = $this->setQueryTree($query, $parent);
		$a = $db->selectquery($query);
		if(sizeof($a) > 0)
		{
			foreach($a as $b)
			{
				$id = htmlChars($b['id']);
				$name = htmlChars($b['name']);
				
				$array[$id] = $name;
				
				$array_sub = $this->ctgParentTree($query, $id);
				$array = array_merge($array, $array_sub);
			}
		}

		return $array;
	}
	
	
	/**
	 * Ritorna l'elenco di tutte le categorie superiori. Da utilizzare in un select form
	 * 
	 * @param array $options
	 *   array associativo di opzioni
	 *   - @b table (string)
	 *   - @b query (string)
	 * @return array
	 */
	/*
	public function inputTreeArray($options=array()) {
	
		$opt_table = gOpt('table', $options, null);
		$opt_query = gOpt('query', $options, null);
		
		if($opt_query)
			$query = $opt_query;
		elseif($opt_table)
			$query = "SELECT id FROM ".$opt_table." WHERE id NOT IN (SELECT parent FROM ".$opt_table.")";
		else
			return array();
		
		$db = db::instance();
		$ctg_ordered = array();
		
		$a = $db->selectquery($query);
		if(sizeof($a)>0) {
			foreach($a as $b)
			{
				$id = htmlChars($b['id']);
				
				$aCtg = new category($this->_controller);
				$value_init = htmlChars($aCtg->ml('name'));
				$value = "";
				$ctg_parent_tree = $aCtg->ctgParentTree($id);
				foreach($ctg_parent_tree as $pCtg) {
					$value .= htmlChars($pCtg->ml('name'))." - ";
				}
				$ctg_ordered[$id] = $value.$value_init;
			}
		}
		
		asort($ctg_ordered);

		return $ctg_ordered;
	}
	
	private function ctgParentTree($parent) {

		$db = db::instance();
		$ctg_parent_tree = array();
		
		while($parent != 0) {
			$pCtg = new category($this->_controller);
			$ctg_parent_tree[] = $pCtg;
			$parent = $pCtg->parent;
		}
		$ctg_parent_tree = array_reverse($ctg_parent_tree);

		return $ctg_parent_tree;
	}
	*/

	/**
	 * Ritorna l'elenco delle categorie che non hanno rami figli
	 * 
	 * @return array
	 */
	/*
	public function getEndTreeCategories() {
	
		$results = array();

		$db = db::instance();
		$query = "SELECT id FROM ".$this->_tbl_data." WHERE id NOT IN (SELECT parent FROM ".$this->_tbl_data." WHERE instance='".$this->_controller->getInstance()."') AND instance='".$this->_controller->getInstance()."'";
		$a = $db->selectquery($query);
		if(sizeof($a)>0) {
			foreach($a as $b) {
				$results[] = $b['id'];
			}
		}

		return $results;
	}

	public function selectParentArray() {

		$query = "SELECT id FROM $this->_tbl_data WHERE instance='".$this->_controller->getInstance()."'";
		if($this->id) { 
	       		$query .= "AND id NOT IN (";
			$query .= $this->id;
			foreach($this->getChildren($this->id, array('all'=>true)) as $child) $query .= ",".$child->id;
			$query .= ")";
		}

		return $this->inputTreeArray($query);
	}
	
	public function selectEndTreeArray() {

		$query = "SELECT id FROM $this->_tbl_data WHERE instance='".$this->_controller->getInstance()."' AND id NOT IN 
		(SELECT parent FROM $this->_tbl_data WHERE instance='".$this->_controller->getInstance()."')";

		return $this->inputTreeArray($query);
	}
	
	public function completeName() {

		$value_init = htmlInput($this->ml('name'));
		$value = "";
		$ctg_parent_tree = $this->ctgParentTree();
		foreach($ctg_parent_tree as $pCtg) {
			$value .= $pCtg->ml('name')." - ";
		}

		return $value.$value_init;
	}
	*/
}

?>
