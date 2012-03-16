<?php

class Category extends propertyObject {

	protected $_tbl_data;
	private $_class_instance;

	function __construct($id, $tbl, $instance) {
		
		$this->_tbl_data = $tbl;
		$this->_class_instance = $instance;
		parent::__construct($this->initP($id));
	
	}
	
	private function initP($id) {
	
		$db = db::instance();
		$query = "SELECT * FROM ".$this->_tbl_data." WHERE id='$id'";
		$a = $db->selectquery($query);
		if(sizeof($a)>0) return $a[0]; 
		else return array('id'=>null, 'instance'=>0, 'name'=>null, 'parent'=>null, 'description'=>null);
	}

	/*
	 * Print the category tree expanded, with the possibility to give 4 links: insert, modify, delete and view
	 * Even the list class is custom
	 */
	public function printTree($parent, $link, $options=null) {

		$db = db::instance();
		
		$css_list = isset($options['css_list']) ? $options['css_list'] : "admin";
		$id_name = isset($options['id_name']) ? $options['id_name'] : "ctg_id";
		$insert = isset($options['insert']) ? $options['insert'] : true;
		$modify = isset($options['modify']) ? $options['modify'] : true;
		$delete = isset($options['delete']) ? $options['delete'] : true;
		$view = isset($options['view']) ? $options['view'] : true;

		$buffer = '';
		$query = "SELECT id, name FROM ".$this->_tbl_data." WHERE parent='$parent' AND instance='$this->_class_instance' ORDER BY name";
		$a = $db->selectquery($query);

		if(sizeof($a)>0) {
			$htmlList = $parent==0
				? new htmlList(array("class"=>"$css_list", "numItems"=>sizeof($a), "separator"=>true))
				: new htmlList(array("class"=>"$css_list inside", "numItems"=>sizeof($a), "separator"=>true));
			$buffer = $htmlList->start();
			foreach($a as $b) {
				$id = htmlChars($b['id']);
				$ctg = new category($id, $this->_tbl_data, $this->_class_instance);
				$selected = ($this->id==$id)?true:false;
				$itemLabel = ($link && $view)
					? "<a href=\"".$link."$id_name=$id&action=view\">".$ctg->ml('name')."</a>"
					: $ctg->ml('name');
				$link_insert = "<a href=\"".$link."$id_name=$id&action=insert\">".pub::icon('insert')."</a>";
				$link_modify = "<a href=\"".$link."$id_name=$id&action=modify\">".pub::icon('modify')."</a>";
				$link_delete = "<a href=\"".$link."$id_name=$id&action=delete\">".pub::icon('delete')."</a>";
				$links = array();
				if($insert) $links[] = $link_insert;
				if($modify) $links[] = $link_modify;
				if($delete) $links[] = $link_delete;
				$itemContent = count($ctg->getChildren())? $this->printTree($id, $link, $options):null;
				$buffer .= $htmlList->item($itemLabel, $links, $selected, true, $itemContent);
			}
			$buffer .= $htmlList->end();
		}
		else if($parent==0) return "<div><p>"._("Nessuna categoria registrata")."</p></div>";

		return $buffer;
	}

	/*
	 * Gets first level or all category children
	 */
	public function getChildren($all=false) {

		$children = array();

		$query = "SELECT id FROM ".$this->_tbl_data." WHERE parent='{$this->_p['id']}' AND instance='$this->_class_instance' ORDER BY name";
		$a = $this->_db->selectquery($query);
		if(sizeof($a)>0) {
			foreach($a as $b) {
				$ctg = new Category($b['id'], $this->_tbl_data, $this->_class_instance);
				$children[$b['id']] = $ctg;
				if($all) $children = array_merge($children, $ctg->getChildren($all));
			}
		}

		return $children;
	}

	/*
	 * return an array("key"=>"value") from a query. the value contains all the parent categories. Needed in select form.
	 */
	public function inputTreeArray($query) {
	
		$db = db::instance();
		$ctg_ordered = array();
		
		$a = $db->selectquery($query);
		if(sizeof($a)>0) {
			foreach($a as $b) {
				$id = htmlChars($b['id']);
				$aCtg = new Category($id, $this->_tbl_data, $this->_class_instance);
				$value_init = htmlChars($aCtg->ml('name'));
				$value = "";
				$ctg_parent_tree = $aCtg->ctgParentTree();
				foreach($ctg_parent_tree as $pCtg) {
					$value .= htmlChars($pCtg->ml('name'))." - ";
				}
				$ctg_ordered[$id] = $value.$value_init;
			}
		}
		
		asort($ctg_ordered);

		return $ctg_ordered;
	}
	
	private function ctgParentTree() {

		$db = db::instance();
		$ctg_parent_tree = array();
		$parent = $this->parent;

		while($parent!=0) {
			$pCtg = new Category($parent, $this->_tbl_data, $this->_class_instance);
			$ctg_parent_tree[] = $pCtg;
			$parent = $pCtg->parent;
		}
		$ctg_parent_tree = array_reverse($ctg_parent_tree);

		return $ctg_parent_tree;
	}

	/*
	 * return all end tree categories (categories that have no children)
	 */
	public function getEndTreeCategories() {
	
		$results = array();

		$db = db::instance();
		$query = "SELECT id FROM ".$this->_tbl_data." WHERE id NOT IN (SELECT parent FROM ".$this->_tbl_data." WHERE instance='".$this->_class_instance."') AND instance='".$this->_class_instance."'";
		$a = $db->selectquery($query);
		if(sizeof($a)>0) {
			foreach($a as $b) {
				$results[$b['id']] = new Category($b['id'], $this->_tbl_data, $this->_class_instance);
			}
		}

		return $results;
	}
	
	public function formCtg($formaction, $options=null) {
	
		$title = isset($options['title']) ? $options['title'] : _("Nuova categoria");
		$parent = $this->parent ? $this->parent : (isset($options['parent']) ? $options['parent'] : 0);
		$submit = $this->id ? _("modifica"):_("inserisci");
		$id_name = isset($options['id_name']) ? $options['id_name'] : "ctg_id";

		$gform = new Form('gform', 'post', true, array("trnsl_table"=>$this->_tbl_data, "trnsl_id"=>$this->id));
		$gform->load('dataform');

		$htmlsection = new htmlSection(array('class'=>'admin', 'headerTag'=>'h1', 'headerLabel'=>$title));

		$required = 'name';
		$buffer = $gform->form($formaction, '', $required);
		$buffer .= $gform->hidden($id_name, $this->id);
		$buffer .= $gform->hidden('instance', $this->_class_instance);

		$buffer .= $gform->cselect('parent', $gform->retvar('parent', $parent), $this->selectParentArray(), _("Categoria madre"), array());
		$buffer .= $gform->cinput('name', 'text', $gform->retvar('name', htmlInput($this->name)), _("Nome"), array("required"=>true, "size"=>40, "maxlength"=>200, 
			"trnsl"=>true, "field"=>"name"));
		$buffer .= $gform->ctextarea('description', $gform->retvar('description', htmlInput($this->description)), _("Descrizione"), array("cols"=>45, "rows"=>4,
			"trnsl"=>true, "field"=>"name"));

		$buffer .= $gform->cinput('submit_action', 'submit', $submit, '', array("classField"=>"submit"));

		$buffer .= $gform->cform();

		$htmlsection->content = $buffer;
		
		return $htmlsection->render();
	}
	
	public function actionCtg($link_error) {

		$this->instance = 'instance';
		$this->parent = 'parent';
		$this->name = 'name';
		if(!$this->name) 
			exit(error::errorMessage(array('error'=>1), $link_error));
		$this->description = 'description';

		$this->updateDbData(); 

		return true;
	}

	public function formDelCtg($formaction, $options=null) {
	
		$title = isset($options['title']) ? $options['title'] : _("Elimina categoria");
		$more_info = isset($options['more_info']) ? $options['more_info'] : null;
		$id_name = isset($options['id_name']) ? $options['id_name'] : "ctg_id";

		$gform = new Form('gform', 'post', true);
		$gform->load('dataform');

		$htmlsection = new htmlSection(array('class'=>'admin', 'headerTag'=>'h1', 'headerLabel'=>$title));

		$required = '';
		$buffer = $gform->form($formaction, '', $required);
		$buffer .= $gform->hidden($id_name, $this->id);

		$buffer .= $gform->cinput('submit_action', 'submit', _("elimina"), array(_("Attenzione!"), _("l'eliminazione è definitiva e comporta l'eliminazione di tutte le sottocategorie che seguono nell'albero. ").$more_info), array("classField"=>"submit"));

		$buffer .= $gform->cform();

		$htmlsection->content = $buffer;
		
		return $htmlsection->render();
	}
	
	public function actionDelCtg($link_error) {

		if(!$this->id) 
			exit(error::errorMessage(array('error'=>9), $link_error));

		foreach($this->getChildren() as $child) 
			$child->actionDelCtg($link_error);
		
		language::deleteTranslations($this->_tbl_data, $this->id);
		$this->deleteDbData(); 

		return true;
	}

	public function selectParentArray() {

		$query = "SELECT id FROM $this->_tbl_data WHERE instance='$this->_class_instance'";
		if($this->id) { 
	       		$query .= "AND id NOT IN (";
			$query .= $this->id;
			foreach($this->getChildren(true) as $child) $query .= ",".$child->id;
			$query .= ")";
		}

		return $this->inputTreeArray($query);
	}
	
	public function selectEndTreeArray() {

		$query = "SELECT id FROM $this->_tbl_data WHERE instance='$this->_class_instance' AND id NOT IN (SELECT parent FROM $this->_tbl_data WHERE instance='$this->_class_instance')";

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
}

?>
