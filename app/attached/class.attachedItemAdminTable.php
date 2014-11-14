<?php
/**
 * @file class.attachedItemAdminTable.php
 * @brief Contiene la classe attachedItemAdminTable
 *
 * @copyright 2013 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
namespace Gino\App\Attached;

/**
 * @brief Estende la classe adminTable base di Gino per permettere l'inserimento di file all'interno della directory della categoria
 */
class attachedItemAdminTable extends \Gino\AdminTable {

	/**
	 * @brief Costruttore
	 * @see adminTable::__construct
	*/
	function __construct($instance, $opts = array()) {
		parent::__construct($instance, $opts);
	}

	/**
	 * @brief rispetto al parent aggiunge la visualizzazione del path al file ed i link per il preview
	 * 
	 * @see adminTable::modelAction
	 * @see model::previewLink()
	 */
	public function adminList($model, $options_view=array()) {

		// $this->permission($options_view);
		
		$db = \Gino\db::instance();

		$model_structure = $model->getStructure();
		$model_table = $model->getTable();

		// some options
		$this->_filter_fields = \Gino\gOpt('filter_fields', $options_view, array());
		$this->_list_display = \Gino\gOpt('list_display', $options_view, array());
		$this->_list_remove = \Gino\gOpt('list_remove', $options_view, array('instance'));
		$this->_ifp = \Gino\gOpt('items_for_page', $options_view, 20);
		$list_title = \Gino\gOpt('list_title', $options_view, ucfirst($model->getModelLabel()));
		$list_description = \Gino\gOpt('list_description', $options_view, "<p>"._("Lista record registrati")."</p>");
		$list_where = \Gino\gOpt('list_where', $options_view, array());
		$link_fields = \Gino\gOpt('link_fields', $options_view, array());
		$addParamsUrl = \Gino\gOpt('add_params_url', $options_view, array());
		$add_buttons = \Gino\gOpt('add_buttons', $options_view, array());
		$view_export = \Gino\gOpt('view_export', $options_view, false);
		$name_export = \Gino\gOpt('name_export', $options_view, 'export_items.csv');
		$export = \Gino\gOpt('export', $options_view, false);

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
		
		$order = \Gino\cleanVar($_GET, 'order', 'string', '');
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

		$tot_records_no_filters_result = $db->select("COUNT(id) as tot", $query_table, $query_where_no_filters);
		$tot_records_no_filters = $tot_records_no_filters_result[0]['tot'];

		$tot_records_result = $db->select("COUNT(id) as tot", $query_table, implode(' AND ', $query_where));
		$tot_records = $tot_records_result[0]['tot'];

		$pagelist = \Gino\Loader::load('PageList', array($this->_ifp, $tot_records, 'array'));

		$limit = $export ? null: array($pagelist->start(), $pagelist->rangeNumber);

		$records = $db->select($query_selection, $query_table, implode(' AND ', $query_where), array('order'=>$query_order, 'limit'=>$limit));
		if(!$records) $records = array();

		$heads = array();
		$export_header = array();

		foreach($fields_loop as $field_name=>$field_obj) {

			if($this->permission($options_view, $field_name))
			{
				if(is_array($field_obj)) {
					$label = $field_obj['label'];
				}
				else {
					$model_label = $model_structure[$field_name]->getLabel();
					$label = is_array($model_label) ? $model_label[0] : $model_label;
				}
				$export_header[] = $label;

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
		if($export) $items[] = $export_header;
		$heads[] = _('URL relativo');
		$heads[] = _('URL download');
		$heads[] = array('text'=>'', 'class'=>'noborder nobkg');

		$rows = array();
		foreach($records as $r) {
			
			$record_model = new $model($r['id'], $this->_controller);
			$record_model_structure = $record_model->getStructure();

			$row = array();
			$export_row = array();
			foreach($fields_loop as $field_name=>$field_obj) {
				
				if($this->permission($options_view, $field_name))
				{
					if(is_array($field_obj)) {
						$record_value = $record_model->$field_obj['member']();
					}
					else {
						$record_value = (string) $record_model_structure[$field_name];
					}
					$export_row[] = $record_value;
					$record_value = \Gino\htmlChars($record_value);
					
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

					if($field_name == 'file') {
						$record_value = $record_model->previewLink();
					}
					
					$row[] = $record_value;
				}
			}

			$row[] = $record_model->path('view');
			$row[] = $record_model->path('download');

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
				$links[] = "<a href=\"".$this->editUrl($add_params_edit)."\">".\Gino\pub::icon('modify')."</a>";
			}
			if($this->_delete_deny != 'all' && !in_array($r['id'], $this->_delete_deny)) {
				$links[] = "<a href=\"javascript: if(confirm('".htmlspecialchars(sprintf(_("Sicuro di voler eliminare \"%s\"?"), $record_model), ENT_QUOTES)."')) location.href='".$this->editUrl($add_params_delete)."';\">".\Gino\pub::icon('delete')."</a>";
			}
			$buttons = array(
				array('text' => implode(' ', $links), 'class' => 'nowrap')
			); 

			if($export) $items[] = $export_row;
			$rows[] = array_merge($row, $buttons);
		}
		
		if($export)
		{
			require_once(CLASSES_DIR.OS.'class.export.php');
			
			$obj_export = new \Gino\export();
			$obj_export->setData($items);
			$obj_export->exportData($name_export, 'csv');
			return null;
		}

		if($tot_ff) {
			$caption = sprintf(_('Risultati %s di %s'), $tot_records, $tot_records_no_filters);
		}
		else {
			$caption = '';
		}

		$this->_view->setViewTpl('table');
		$this->_view->assign('class', 'table table-striped table-hover');
		$this->_view->assign('caption', $caption);
		$this->_view->assign('heads', $heads);
		$this->_view->assign('rows', $rows);

		$table = $this->_view->render();

		if($this->_allow_insertion) {
			$link_insert = "<a href=\"".$this->editUrl(array('insert'=>1))."\">".\Gino\pub::icon('insert', array('scale' => 2))."</a>";
		}
		else {
			$link_insert = "";
		}
		
		$link_export = $view_export ? "<a href=\"".$this->editUrl(array('export'=>1))."\">".\Gino\pub::icon('export')."</a>" : null;

		$this->_view->setViewTpl('admin_table_list');
		$this->_view->assign('title', $list_title);
		$this->_view->assign('description', $list_description);
		$this->_view->assign('link_insert', $link_insert);
		$this->_view->assign('link_export', $link_export);
		$this->_view->assign('search_icon', \Gino\pub::icon('search', array('scale' => 2)));
		$this->_view->assign('table', $table);
		$this->_view->assign('tot_records', $tot_records);
		$this->_view->assign('form_filters_title', _("Filtri"));
		$this->_view->assign('form_filters', $tot_ff ? $this->formFilters($model, $options_view) : null);
		$this->_view->assign('pnavigation', $pagelist->listReferenceGINO($_SERVER['REQUEST_URI'], false, '', '', '', false, null, null, array('add_no_permalink'=>true)));
		$this->_view->assign('psummary', $pagelist->reassumedPrint());

		return $this->_view->render();
	}

  /**
   * @brief rispetto al parent consente di salvare il file all'interno della directory selezionata
   * @see adminTable::modelAction
   */
  public function modelAction($model, $options=array(), $options_element=array()) {

    // Valori di default di form e sessione
    $default_formid = 'form'.$model->getTable().$model->id;
    $default_session = 'dataform'.$model->getTable().$model->id;

    // Opzioni generali per il recupero dei dati dal form
    $formId = array_key_exists('formId', $options) ? $options['formId'] : $default_formid;
    $method = array_key_exists('method', $options) ? $options['method'] : 'post';
    $validation = array_key_exists('validation', $options) ? $options['validation'] : true;
    $session_value = array_key_exists('session_value', $options) ? $options['session_value'] : $default_session;

    // Opzioni per selezionare gli elementi da recuperare dal form
    $removeFields = array_key_exists('removeFields', $options) ? $options['removeFields'] : null;
    $viewFields = array_key_exists('viewFields', $options) ? $options['viewFields'] : null;

    $gform = new \Gino\Form($formId, $method, $validation);
    $gform->save($session_value);
    $req_error = $gform->arequired();

    if($req_error > 0) {
      return array('error'=>1);
    }

    foreach($model->getStructure() as $field=>$object) {

      if($field == 'file') {
        $ctg = new attachedCtg($model->category, $this->_controller);
        $object->setDirectory($ctg->path('abs'));
      }

      if($this->permission($options, $field) &&
      (
        ($removeFields && !in_array($field, $removeFields)) || 
        ($viewFields && in_array($field, $viewFields)) || 
        (!$viewFields && !$removeFields)
      ))
      {
        if(isset($options_element[$field]))
          $opt_element = $options_element[$field];
        else
          $opt_element = array();

        if($field == 'instance' && is_null($model->instance))
        {
          $model->instance = $this->_controller->getInstance();
        }
        else
        {
          $value = $object->clean($opt_element);
          $result = $object->validate($value);

          if($result === true) {
            $model->{$field} = $value;
          }
          else {
            return array('error'=>$result['error']);
          }
        }
      }
    }

    if($import)
    {
      $result = $this->readFile($model, $path_to_file, array('field_verify'=>$field_verify, 'dump'=>$dump, 'dump_path'=>$dump_path));
      if($field_log)
        $model->{$field_log} = $result;
    }

    return $model->updateDbData();
  }

}

?>

