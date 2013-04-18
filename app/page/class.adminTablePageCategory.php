<?php

class adminTablePageCategory extends adminTable {

	/**
	 * Lista dei record
	 * 
	 * @see backOffice()
	 * @param object $model
	 * @param array $options_view
	 *   array associativo di opzioni
	 *   - @b filter_fields (array): campi sui quali applicare il filtro per la ricerca automatica
	 *   - @b list_display (array): campi mostrati nella lista (se vuoto mostra tutti)
	 *   - @b list_remove (array): campi da non mostrare nella lista (default: instance)
	 *   - @b items_for_page (integer): numero di record per pagina
	 *   - @b list_title (string): titolo
	 *   - @b list_description (string): descrizione sotto il titolo (informazioni aggiuntive)
	 *   - @b list_where (array): condizioni della query che estrae i dati dell'elenco
	 *   - @b link_fields (array): campi sui quali impostare un collegamento, nel formato nome_campo=>array('link'=>indirizzo, 'param_id'=>'ref')
	 *     - @a link (string), indirizzo del collegamento
	 *     - @a param_id (string), nome del parametro identificativo da aggiungere all'indirizzo (default: id[=valore_id])
	 *     esempio: array('link_fields'=>array('codfisc'=>array('link'=>$this->_plink->aLink($this->_instanceName, 'view')))
	 *   - @b add_params_url (array): parametri aggiuntivi da passare ai link delle operazioni sui record
	 *   - @b add_buttons (array): bottoni aggiuntivi da anteporre a quelli di modifica ed eliminazione, nel formato array(array('label'=>pub::icon('group'), 'link'=>indirizzo, 'param_id'=>'ref'))
	 *     - @a label (string), nome del bottone
	 *     - @a link (string), indirizzo del collegamento
	 *     - @a param_id (string), nome del parametro identificativo da aggiungere all'indirizzo (default: id[=valore_id])
	 * @return string
	 */
	
	public function adminList($model, $options_view=array()) {

		// $this->permission($options_view);
		
		$this->_filter_fields = gOpt('filter_fields', $options_view, array());
		$list_where = gOpt('list_where', $options_view, array());
		
		// NEW
		// link
		
		
		
		$db = db::instance();

		$model_structure = $model->getStructure();
		$model_table = $model->getTable();	// page_category
		
		// Definizione WHERE
		
		// filter form
		$tot_ff = count($this->_filter_fields);
		if($tot_ff) $this->setSessionSearch($model);
		
		// managing instance
		$query_where = array();
		if(array_key_exists('instance', $model_structure)) {
			$query_where[] = "instance='".$this->_controller->getInstance()."'";
		}
		
		if(count($list_where)) {
			$query_where = array_merge($query_where, $list_where);
		}
		// filters
		if($tot_ff) {
			$this->addWhereClauses($query_where, $model);
		}
		
		//$options_view['where'] = $query_where;
		
		
		$category = new category($model_table, $this->_controller);
		
		$buffer = $category->printTree(null, $options_view);
		
		return $buffer;
		
		
		$this->_view->setViewTpl('table');
		$this->_view->assign('class', 'generic');
		$this->_view->assign('caption', '');
		$this->_view->assign('heads', null);
		$this->_view->assign('rows', $rows);

		$table = $this->_view->render();

		if($this->_allow_insertion) {
			$link_insert = "<a href=\"".$this->editUrl(array('insert'=>1))."\">".pub::icon('insert')."</a>";
		}
		else {
			$link_insert = "";
		}

		$this->_view->setViewTpl('admin_table_list');
		$this->_view->assign('title', $list_title);
		$this->_view->assign('description', $list_description);
		$this->_view->assign('link_insert', $link_insert);
		$this->_view->assign('table', $table);
		$this->_view->assign('tot_records', $tot_records);
		$this->_view->assign('form_filters_title', _("Filtri"));
		$this->_view->assign('form_filters', $tot_ff ? $this->formFilters($model, $options_view) : null);
		$this->_view->assign('pnavigation', null);
		$this->_view->assign('psummary', null);

		return $this->_view->render();
	}
	
	/**
	 * @see adminTable::adminList()
	 */
	public function __adminList($model, $options_view=array()) {

		// $this->permission($options_view);
		
		$db = db::instance();

		$model_structure = $model->getStructure();
		$model_table = $model->getTable();

		// some options
		$this->_filter_fields = gOpt('filter_fields', $options_view, array());
		$this->_list_display = gOpt('list_display', $options_view, array());
		$this->_list_remove = gOpt('list_remove', $options_view, array('instance'));
		$this->_ifp = gOpt('items_for_page', $options_view, 20);
		$list_title = gOpt('list_title', $options_view, ucfirst($model->getModelLabel()));
		$list_description = gOpt('list_description', $options_view, "<p>"._("Lista record registrati")."</p>");
		$list_where = gOpt('list_where', $options_view, array());
		$link_fields = gOpt('link_fields', $options_view, array());
		$addParamsUrl = gOpt('add_params_url', $options_view, array());
		$add_buttons = gOpt('add_buttons', $options_view, array());
		
		//NEW
		$this->_field_name = gOpt('field_name', $options_view, 'name');
		$field_name = $this->_field_name;
		
		$this->_insert_deny = gOpt('insert_deny', $options_view, array());	// METTERE NEL COSTRUTTORE? (DA ESTENDERE)
		
		$category = new Category('pageCategory', 'page_category', $this->_controller);
		

		$order = cleanVar($_GET, 'order', 'string', '');
		if(!$order) $order = 'id DESC';
		// get order field and direction
		preg_match("#^([^ ,]*)\s?((ASC)|(DESC))?.*$#", $order, $matches);
		$field_order = isset($matches[1]) && $matches[1] ? $matches[1] : '';
		$order_dir = isset($matches[2]) && $matches[2] ? $matches[2] : '';

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
		// filters
		if($tot_ff) {
			$this->addWhereClauses($query_where, $model);
		}
		// order
		$query_order = $model_structure[$field_order]->adminListOrder($order_dir, $query_where, $query_table);

		$tot_records_result = $db->select("COUNT(id) as tot", $query_table, implode(' AND ', $query_where), null);
		$tot_records = $tot_records_result[0]['tot'];

		$records = $db->select($query_selection, $query_table, implode(' AND ', $query_where), $query_order, null);
		if(!$records) $records = array();

		$rows = array();
		foreach($records as $r) {
				
			$record_model = new $model($r['id'], $this->_controller);
			$record_model_structure = $record_model->getStructure();
			
			$row = array();
			
			$field_obj = $model_structure[$field_name];
			
			$record_value = (string) $record_model_structure[$field_name];
			if(isset($link_fields[$field_name]) && $link_fields[$field_name])
			{
				$link_field = $link_fields[$field_name]['link'];
				$link_field_param = array_key_exists('param_id', $link_fields[$field_name]) ? $link_fields[$field_name]['param_id'] : 'id';
				
				$link_field = $link_field.'&'.$link_field_param."=".$r['id'];
				
				$record_value = "<a href=\"".$link_field."\">$record_value</a>";
			}
			
			$row[] = $record_value;

			$links = array();
			
			if(count($add_buttons))
			{
				foreach($add_buttons AS $value)
				{
					if(is_array($value))
					{
						$label_button = array_key_exists('label', $value) ? $value['label'] : null;
						$link_button = array_key_exists('link', $value) ? $value['link'] : null;
						$param_id_button = array_key_exists('param_id', $value) ? $value['param_id'] : 'id';
						
						if($label_button && $link_button && $param_id_button)
						{
							$link_button = $link_button.'&'.$param_id_button."=".$r['id'];
							$links[] = "<a href=\"$link_button\">$label_button</a>";
						}
					}
				}
			}
			
			$add_params_insert = array('insert'=>1, 'id'=>$r['id']);	// NEW
			$add_params_edit = array('edit'=>1, 'id'=>$r['id']);
			$add_params_delete = array('delete'=>1, 'id'=>$r['id']);
			if(count($addParamsUrl))
			{
				foreach($addParamsUrl AS $key=>$value)
				{
					$add_params_insert[$key] = $value;	// NEW
					$add_params_edit[$key] = $value;
					$add_params_delete[$key] = $value;
				}
			}
			
			// NEW
			if($this->_insert_deny != 'all' && !in_array($r['id'], $this->_insert_deny)) {
				$links[] = "<a href=\"".$this->editUrl($add_params_insert)."\">".pub::icon('insert')."</a>";
			}
			//
			if($this->_edit_deny != 'all' && !in_array($r['id'], $this->_edit_deny)) {
				$links[] = "<a href=\"".$this->editUrl($add_params_edit)."\">".pub::icon('modify')."</a>";
			}
			if($this->_delete_deny != 'all' && !in_array($r['id'], $this->_delete_deny)) {
				$links[] = "<a href=\"javascript: if(confirm('".htmlspecialchars(sprintf(_("Sicuro di voler eliminare \"%s\"?"), $record_model), ENT_QUOTES)."')) location.href='".$this->editUrl($add_params_delete)."';\">".pub::icon('delete')."</a>";
			}
			$buttons = array(
				array('text' => implode(' ', $links), 'class' => 'no_border no_bkg')
			); 

			$rows[] = array_merge($row, $buttons);
			
			// -> tree
			//$rows = $category->printTree($r['id'], array('data'=>$rows, 'link'=>$this->_home."?evt[$this->_instanceName-managePage]&block=ctg"));
		}

		$this->_view->setViewTpl('table');
		$this->_view->assign('class', 'generic');
		$this->_view->assign('caption', '');
		$this->_view->assign('heads', null);
		$this->_view->assign('rows', $rows);

		$table = $this->_view->render();

		if($this->_allow_insertion) {
			$link_insert = "<a href=\"".$this->editUrl(array('insert'=>1))."\">".pub::icon('insert')."</a>";
		}
		else {
			$link_insert = "";
		}

		$this->_view->setViewTpl('admin_table_list');
		$this->_view->assign('title', $list_title);
		$this->_view->assign('description', $list_description);
		$this->_view->assign('link_insert', $link_insert);
		$this->_view->assign('table', $table);
		$this->_view->assign('tot_records', $tot_records);
		$this->_view->assign('form_filters_title', _("Filtri"));
		$this->_view->assign('form_filters', $tot_ff ? $this->formFilters($model, $options_view) : null);
		$this->_view->assign('pnavigation', null);
		$this->_view->assign('psummary', null);

		return $this->_view->render();
	}
}
?>
