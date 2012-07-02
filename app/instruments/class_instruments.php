<?php
/**
 * @file class_instruments.php
 * @brief Contiene la classe instruments
 * 
 * @copyright 2005 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */

/**
 * @brief Strumenti agguintivi di gino
 * 
 * Adesso sono disponibili l'elenco delle risorse disponibili (con i relativi link) e dei mime type.
 * Per aggiungere uno strumento è necessario:
 *   - creare un record nella tabella @a instruments
 *   - associare nel metodo viewItem() il valore del campo id dello strumento con un suo metodo personalizzato (ad es. itemNew)
 *   - creare il metodo personalizzato (ad es. itemNew)
 * 
 * @copyright 2005 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class instruments extends AbstractEvtClass {

	private $_optionsValue;
	private $_options;
	public $_optionsLabels;
	
	private $_title;
	private $_tbl_data;
	private $_action, $_block;

	function __construct() {
	
		parent::__construct();

		$this->_instance = 0;
		$this->_instanceName = $this->_className;

		$this->setAccess();

		// Valori di default
		$this->_optionsValue = array(
			
		);
		
		$this->_title = htmlChars($this->setOption('title', true));
		
		$this->_options = new options($this->_className, $this->_instance);
		$this->_optionsLabels = array(
			"title"=>_("Titolo")
		);
		
		$this->_tbl_item = 'instruments';
		
		$this->_action = cleanVar($_REQUEST, 'action', 'string', '');
		$this->_block = cleanVar($_REQUEST, 'block', 'string', '');
	}

	/**
	 * Gruppi per accedere alle funzionalità del modulo
	 * 
	 * @b _group_1: assistenti
	 */
	private function setGroups(){
		
		$this->_group_1 = array($this->_list_group[0], $this->_list_group[1]);
	}

	/**
	 * Interfaccia amministrativa alla gestione degli strumenti
	 * 
	 * @return string
	 */
	public function manageInstruments() {
	
		$this->accessGroup('ALL');
		
		$htmltab = new htmlTab(array("linkPosition"=>'right', "title"=>$this->_title));	
		$link_admin = "<a href=\"".$this->_home."?evt[$this->_className-manageInstruments]&block=permissions\">"._("Permessi")."</a>";
		$link_options = "<a href=\"".$this->_home."?evt[$this->_className-manageInstruments]&block=options\">"._("Opzioni")."</a>";
		$link_dft = "<a href=\"".$this->_home."?evt[".$this->_className."-manageInstruments]\">"._("Gestione")."</a>";
		$sel_link = $link_dft;

		if($this->_block == 'permissions' && $this->_access->AccessVerifyGroupIf($this->_className, $this->_instance, '', '')) {
			$GINO = sysfunc::managePermissions($this->_instance, $this->_className);		
			$sel_link = $link_admin;
		}
		elseif($this->_block == 'options') {
			$GINO = sysfunc::manageOptions($this->_instance, $this->_className);		
			$sel_link = $link_options;
		}
		else {
			
			$id = cleanVar($_GET, 'id', 'int', '');
			
			if($id && $this->_action == $this->_act_modify)
				$form = $this->formItem($id);
			elseif($id)
				$form = $this->viewItem($id);
			else
				$form = $this->info();
			
			$GINO = "<div class=\"vertical_1\">\n";
			$GINO .= $this->listItems($id);
			$GINO .= "</div>\n";
		
			$GINO .= "<div class=\"vertical_2\">\n";
			$GINO .= $form;
			$GINO .= "</div>\n";
			
			$GINO .= "<div class=\"null\"></div>";
		}
		
		$htmltab->navigationLinks = array($link_admin, $link_options, $link_dft);
		$htmltab->selectedLink = $sel_link;
		$htmltab->htmlContent = $GINO;
		return $htmltab->render();
	}

	/**
	 * Elenco degli strumenti
	 * 
	 * @param integer $select_item valore ID dello strumento selezionato
	 * @return string
	 */
	private function listItems($select_item){
	
		$htmlsection = new htmlSection(array('class'=>'admin', 'headerTag'=>'header', 'headerLabel'=>_("Elenco strumenti")));

		$GINO = '';
		
		$query = "SELECT id FROM ".$this->_tbl_item." ORDER BY order_list ASC";
		$a = $this->_db->selectquery($query);
		if(sizeof($a) > 0)
		{
			$htmlList = new htmlList(array("numItems"=>sizeof($a), "separator"=>true));
			$GINO .= $htmlList->start();
			
			foreach($a AS $b)
			{
				$refid = $b['id'];
				
				if($this->_access->AccessVerifyGroupIf($this->_className, $this->_instance, '', ''))
				{
					$lnk_modify = "<a href=\"".$this->_plink->aLink($this->_className, 'manageInstruments', array("id"=>$refid, 'action'=>$this->_act_modify))."\">".$this->icon('modify', _("modifica"))."</a>";
					$a_link = array($lnk_modify);
				}
				else $a_link = array();
				
				$name = htmlChars($this->_trd->selectTXT($this->_tbl_item, 'name', $refid));
				$description = htmlChars($this->_trd->selectTXT($this->_tbl_item, 'description', $refid));
				
				$selected = $select_item == $refid ? true : false;
				
				$itemLabel = "<a href=\"".$this->_plink->aLink($this->_className, 'manageInstruments', array("id"=>$refid))."\">$name</a>";
				
				$GINO .= $htmlList->item($itemLabel, $a_link, $selected, true, $description);
			}
			
			$GINO .= $htmlList->end();
		}
		else
		{
			$GINO = "<div class=\"message\">"._("non risultano strumenti disponibili.")."</div>\n";
		}
		
		$htmlsection->content = $GINO;
		
		return $htmlsection->render();
	}
	
	/**
	 * Associazione del valore ID dello strumento con il metodo di visualizzazione
	 * 
	 * @param integer $id valore ID dello strumento
	 * @return string
	 */
	private function viewItem($id){
		
		$buffer = '';
		if($id == 1)
			$buffer = $this->itemLink();
		elseif($id == 2)
			$buffer = $this->itemMimetype();
		else
			$buffer = null;
		
		return $buffer;
	}
	
	/**
	 * Form di modifica delle intestazioni di uno strumento
	 * 
	 * @param integer $id valore ID dello strumento
	 * @return string
	 */
	private function formItem($id){
		
		$gform = new Form('gform', 'post', true, array("trnsl_table"=>$this->_tbl_item, "trnsl_id"=>$id));
		$gform->load('dataform');
		
		$title_page = htmlChars($this->_trd->selectTXT($this->_tbl_item, 'name', $id, 'id'));
		
		if(!empty($id) AND $this->_action == $this->_act_modify)
		{
			$query = "SELECT name, description FROM ".$this->_tbl_item." WHERE id='$id'";
			$a = $this->_db->selectquery($query);
			if(sizeof($a) > 0)
			{
				foreach($a AS $b)
				{
					$name = htmlInput($b['name']);
					$description = htmlInput($b['description']);
				}
			}	
			
			$submit = _("modifica");
			$title_text = _("Modifica")." '$title_page'";
		}
		else
		{
			return null;
		}
		
		$htmlsection = new htmlSection(array('class'=>'admin', 'headerTag'=>'h1', 'headerLabel'=>$title_text, 'headerLinks'=>array($this->_link_return)));

		$required = 'name';
		$GINO = $gform->form($this->_home."?evt[".$this->_className."-actionItem]", '', $required);
		$GINO .= $gform->hidden('id', $id);
		$GINO .= $gform->hidden('action', $this->_action);

		$GINO .= $gform->cinput('name', 'text', $name, _("Nome"), array("required"=>true, "size"=>40, "maxlength"=>200, "trnsl"=>true, "field"=>"name"));
		$GINO .= $gform->ctextarea('description', $description, _("Descrizione"), array("cols"=>45, "rows"=>2, "trnsl"=>true, "field"=>"description"));
		
		$GINO .= $gform->cinput('submit_action', 'submit', $submit, '', array("classField"=>"submit"));
		$GINO .= $gform->cform();
		
		$htmlsection->content = $GINO;
		
		return $htmlsection->render();
	}
	
	/**
	 * Modifica delle intestazioni di uno strumento
	 */
	public function actionItem(){
		
		$this->accessGroup('');
		
		$gform = new Form('gform', 'post', true);
		$gform->save('dataform');
		$req_error = $gform->arequired();
		
		$id = cleanVar($_POST, 'id', 'int', '');
		$name = cleanVar($_POST, 'name', 'string', '');
		$description = cleanVar($_POST, 'description', 'string', '');
		
		$reference = cleanVar($_POST, 'ref', 'string', '');
		
		$link_error = $this->_home."?evt[$this->_className-manageInstruments]&id=$id&action=$this->_action";
		
		if($req_error > 0) 
			exit(error::errorMessage(array('error'=>1), $link_error));
		
		if($name != '')
		{
			if($this->_action == $this->_act_modify)
			{
				$query = "UPDATE ".$this->_tbl_item." SET name='$name', description='$description' WHERE id='$id'";
				$this->_db->actionquery($query);
				
				EvtHandler::HttpCall($this->_home, $this->_className.'-manageInstruments', "id=$id&action=$this->_action");
			}
			elseif($this->_action == $this->_act_insert)
			{
				$query = "INSERT INTO ".$this->_tbl_item." (name, description) VALUES ('$name', '$description')";
				$result = $this->_db->actionquery($query);
				$last_id_item = $this->_db->getlastid($this->_tbl_item);
				
				EvtHandler::HttpCall($this->_home, $this->_className.'-manageInstruments', "id=$last_id_item&action=".$this->_act_modify);
			}
		}
		else
		{
			exit(error::errorMessage(array('error'=>1), $link_error));
		}
	}

	private function info() {
	
		$htmlsection = new htmlSection(array('class'=>'admin', 'headerTag'=>'h1', 'headerLabel'=>_("Informazioni")));

		$buffer = "<p>"._("In questa sezione sono raggruppati alcuni strumenti utili per la costruzione di un sito internet a partire da gino o per la modifica/sviluppo del CMS.")."</p>";
		
		$htmlsection->content = $buffer;

		return $htmlsection->render();
	}
	
	// Metodi personalizzati degli strumenti
	
	/**
	 * Strumento - mostra l'elenco delle risorse disponibili (con i relativi link)
	 * 
	 * @return string
	 */
	private function itemLink(){
		
		$GINO = '';
		
		$query = "SELECT p.item_id, m.role1 FROM ".$this->_tbl_page." AS p, ".$this->_tbl_module." AS m WHERE p.module=m.id AND m.masquerade='no' ORDER BY title";
		$results_ordered = $this->_trd->listItemOrdered($query, 'item_id', $this->_tbl_page, 'title', 'asc');
		if(sizeof($results_ordered) > 0)
		{
			$GINO .= "<fieldset>";
			$GINO .= "<legend><b>"._("Pagine")."</b></legend>";
			
			$odd = true;
			foreach($results_ordered AS $key=>$value)
			{
				$class = ($odd)?"m_list_item_odd":"m_list_item_even";
				$page_id = $key;
				$page_title = htmlChars($value);
				$path_link = $this->_plink->aLink('page', 'displayItem', "id=$page_id");
				
				$GINO .= "<div class=\"$class\" style=\"padding:5px;\">";
				$GINO .= "<span style=\"font-weight:bold\">".$page_title."</span><br/>";
				$GINO .= "<span>$path_link</span>";
				$GINO .= "</div>";
				$odd = !$odd;
			}
			$GINO .= "</fieldset>";
		}

		$query = "SELECT id, class as name, name as instance, label, role1 FROM ".$this->_tbl_module." WHERE type='class' AND masquerade='no' ORDER BY label";
		$a = $this->_db->selectquery($query);
			
		$query2 = "SELECT id, name, name as instance, label, role1 FROM ".$this->_tbl_module_app." WHERE type='class' AND masquerade='no' AND instance='no' ORDER BY label";
		$a2 = $this->_db->selectquery($query2);
		
		$join = array_merge($a,$a2);
		
		if(sizeof($join) > 0)
		{
			$GINO .= "<fieldset>";
			$GINO .= "<legend><b>"._("Classi")."</b></legend>";
			
			$cnt = 0;
			$odd = true;
			foreach($join AS $value)
			{
				$class_name = htmlChars($value['name']);
				$class_label = htmlChars($value['label']);
				$instanceName = htmlChars($value['instance']);
				
				if(method_exists($class_name, 'outputFunctions'))
				{
					$cnt++;
					$list = call_user_func(array($class_name, 'outputFunctions'));
					foreach($list as $func => $desc)
					{
						$desc_role = $desc['role'];
						$description = $desc['label'];
						
						// Search function role
						$field_role = 'role'.$desc_role;
						
						$query = !$instanceName 
							? "SELECT $field_role FROM $this->_tbl_module_app WHERE name='$class_name'"
							: "SELECT $field_role FROM $this->_tbl_module WHERE name='$instanceName' AND class='$class_name'";
						$a = $this->_db->selectquery($query);
						if(sizeof($a) > 0)
						{
							foreach($a AS $b)
							{
								$class_style = ($odd)?"odd":"even";
								$class_role = $b[$field_role];
								$role_name = $this->_db->getFieldFromId($this->_tbl_user_role, 'name', 'role_id', $class_role);
								$text = jsVar("$class_label - $description");
								$path_link = $this->_plink->aLink($instanceName, $func);
								
								$GINO .= "<div class=\"$class_style\" style=\"padding:5px;\">";
								$GINO .= "<b>$class_label - $description</b><br/>";
								$GINO .= "<span style=\"color:#ff0000\">($role_name)</span> - $path_link";
								$GINO .= "</div>";
							}
						}
					}
					if($cnt == 0)
					$GINO.= _("non ci sono classi visualizzabili");
					$odd = !$odd;
				}
			}
			$GINO .= "</fieldset>";
		}
		return $GINO;
	}
	
	/**
	 * Strumento - mostra l'elenco dei mime type (include come iframe il file mime-type-table.html)
	 * 
	 * @return string
	 */
	private function itemMimetype(){
		
		$file = $this->_app_dir.$this->_os.$this->_className.'/doc/mime-type-table.html';
		
		$GINO = '';
		if(is_file($file))
		{
			$src = $this->_url_root.$this->_class_www.'/doc/mime-type-table.html';
			$GINO .= "<iframe src=\"$src\" width=\"600\" height=\"500\"></iframe>";
		}
		return $GINO;
	}
}

?>
