<?php
/**
 * @file class.pageEntryAdminTable.php
 * Contiene la definizione ed implementazione della classe pageEntryAdminTable.
 *
 * @version 1.0
 * @copyright 2013 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 * @authors Marco Guidotti guidottim@gmail.com
 * @authors abidibo abidibo@gmail.com
 */

/**
 * \ingroup page
 * Classe per la gestione del backoffice delle pagine (estensione della classe adminTable del core di gino).
 *
 * @version 1.0
 * @copyright 2013 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 * @authors Marco Guidotti guidottim@gmail.com
 * @authors abidibo abidibo@gmail.com
 */
class pageEntryAdminTable extends adminTable {
	
	/**
	 * Parte dell'indirizzo di una pagina da anteporre allo slug
	 * 
	 * @var string
	 */
	private static $_page_link = "page/view/";
	
	/**
	 * Metodo chiamato al salvataggio di una pagina 
	 * 
	 * @see pageTag::saveTag()
	 * @see pageEntry::saveTags()
	 * @param object $model istanza di @ref pageEntry
	 * @param array $options opzioni del form
	 * @param array $options_element opzioni dei campi
	 * @access public
	 * @return void
	 * 
	 * Quando la pagina è resa pubblica i tag vengono salvati nella tabella dei tag e in quella di join.
	 */
	public function modelAction($model, $options=array(), $options_element=array()) {

		$result = parent::modelAction($model, $options, $options_element);
		
		if(is_array($result) && isset($result['error'])) {
			return $result;
		}
		
		$session = session::instance();
		$model->author = $session->userId;
		$model->updateDbData();

		$model_tags = array();

		if($model->published) {
			
			$tags = explode(',', $model->tags);
			if(count($tags))
			{
				foreach($tags as $tag) {
					$tag_id = pageTag::saveTag($tag);
					if($tag_id) {
						$model_tags[] = $tag_id;
					}
				}
			}
		}

		return $model->saveTags($model_tags);
	}
	
	/**
	 * @see adminTable::adminList()
	 * 
	 * In corrispondenza del campo @a slug mostra l'indirizzo completo della pagina
	 */
	public function adminList($model, $options_view=array()) {

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

		// fields to be shown
		$fields_loop = array();
		if($this->_list_display) {
			foreach($this->_list_display as $fname) {
				$fields_loop[$fname] = $model_structure[$fname];
			}
		}
		else {
			$fields_loop = $model_structure;
			if(count($this->_list_remove))
			{
				foreach($this->_list_remove AS $value)
					unset($fields_loop[$value]);
			}
		}
		
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
		$query_where_no_filters = implode(' AND ', $query_where);
		// filters
		if($tot_ff) {
			$this->addWhereClauses($query_where, $model);
		}
		// order
		$query_order = $model_structure[$field_order]->adminListOrder($order_dir, $query_where, $query_table);

		$tot_records_no_filters_result = $db->select("COUNT(id) as tot", $query_table, $query_where_no_filters, null);
		$tot_records_no_filters = $tot_records_no_filters_result[0]['tot'];

		$tot_records_result = $db->select("COUNT(id) as tot", $query_table, implode(' AND ', $query_where), null);
		$tot_records = $tot_records_result[0]['tot'];

		$pagelist = new PageList($this->_ifp, $tot_records, 'array');

		$limit = array($pagelist->start(), $pagelist->rangeNumber);

		$records = $db->select($query_selection, $query_table, implode(' AND ', $query_where), $query_order, $limit);
		if(!$records) $records = array();

		$heads = array();

		foreach($fields_loop as $field_name=>$field_obj) {

			if($this->permission($options_view, $field_name))
			{
				$model_label = $model_structure[$field_name]->getLabel();
				$label = is_array($model_label) ? $model_label[0] : $model_label;
				
				if($field_name == 'slug') $label = _("Indirizzo");

				if($field_obj->canBeOrdered()) {

					$ord = $order == $field_name." ASC" ? $field_name." DESC" : $field_name." ASC";
					if($order == $field_name." ASC") {
						$jsover = "$(this).getNext('.arrow').removeClass('arrow_up').addClass('arrow_down')";
						$jsout = "$(this).getNext('.arrow').removeClass('arrow_down').addClass('arrow_up')";
						$css_class = "arrow_up";
					}
					elseif($order == $field_name." DESC") {
						$jsover = "$(this).getNext('.arrow').removeClass('arrow_down').addClass('arrow_up')";
						$jsout = "$(this).getNext('.arrow').removeClass('arrow_up').addClass('arrow_down')";
						$css_class = "arrow_down";
					}
					else {
						$js = '';
						$jsover = "$(this).getNext('.arrow').addClass('arrow_up')";
						$jsout = "$(this).getNext('.arrow').removeClass('arrow_up')";
						$a_style = "visibility:hidden";
						$css_class = '';
					}

					$add_params = $addParamsUrl;
					$add_params['order'] = $ord;
					$link = $this->editUrl($add_params, array('start'));
					$head_t = "<a href=\"".$link."\" onmouseover=\"".$jsover."\" onmouseout=\"".$jsout."\" onclick=\"$(this).setProperty('onmouseout', '')\">".$label."</a>";
					$heads[] = $head_t." <div style=\"margin-right: 5px;top:3px;\" class=\"right arrow $css_class\"></div>";
				}
				else {
					$heads[] = $label;
				}
			}
		}
		$heads[] = array('text'=>'', 'class'=>'no_border no_bkg');

		$rows = array();
		foreach($records as $r) {
				
			$record_model = new $model($r['id'], $this->_controller);
			$record_model_structure = $record_model->getStructure();

			$row = array();
			foreach($fields_loop as $field_name=>$field_obj) {
				
				if($this->permission($options_view, $field_name))
				{
					$record_value = (string) $record_model_structure[$field_name];
					if(isset($link_fields[$field_name]) && $link_fields[$field_name])
					{
						$link_field = $link_fields[$field_name]['link'];
						$link_field_param = array_key_exists('param_id', $link_fields[$field_name]) ? $link_fields[$field_name]['param_id'] : 'id';
						
						// PROBLEMI CON I PERMALINKS
						//$plink = new Link();
						//$link_field = $plink->addParams($link_field, $link_field_param."=".$r['id'], false);
						$link_field = $link_field.'&'.$link_field_param."=".$r['id'];
						
						$record_value = "<a href=\"".$link_field."\">$record_value</a>";
					}
					
					if($field_name == 'slug') $record_value = self::$_page_link.$record_value;
					
					$row[] = $record_value;
				}
			}

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
			
			$add_params_edit = array('edit'=>1, 'id'=>$r['id']);
			$add_params_delete = array('delete'=>1, 'id'=>$r['id']);
			if(count($addParamsUrl))
			{
				foreach($addParamsUrl AS $key=>$value)
				{
					$add_params_edit[$key] = $value;
					$add_params_delete[$key] = $value;
				}
			}
			
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
    }

    if($tot_ff) {
      $caption = sprintf(_('Risultati %s di %s'), $tot_records, $tot_records_no_filters);
    }
    else {
      $caption = '';
    }

		$this->_view->setViewTpl('table');
		$this->_view->assign('class', 'generic');
		$this->_view->assign('caption', $caption);
		$this->_view->assign('heads', $heads);
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
		$this->_view->assign('search_icon', pub::icon('search'));
		$this->_view->assign('table', $table);
		$this->_view->assign('tot_records', $tot_records);
		$this->_view->assign('form_filters_title', _("Filtri"));
		$this->_view->assign('form_filters', $tot_ff ? $this->formFilters($model, $options_view) : null);
		$this->_view->assign('pnavigation', $pagelist->listReferenceGINO($_SERVER['REQUEST_URI'], false, '', '', '', false, null, null, array('add_no_permalink'=>true)));
		$this->_view->assign('psummary', $pagelist->reassumedPrint());

		return $this->_view->render();
	}
}
?>
