<?php
/**
 * @file class_attached.php
 * @brief Contiene la classe attached
 * 
 * @copyright 2005 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */

/**
 * @brief Gestione di archivi di file con struttura ad albero
 * 
 * @copyright 2005 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class attached extends AbstractEvtClass{

	protected $_instance, $_instanceName;

	private $_options;
	private $_title, $_items_for_page;
	
	private $_group_1;
	
	private $_tbl_category;
	private $_tbl_attached;
	
	public $_category;
	
	private $_ico_next_image, $_ico_prev_image;
	
	private $_path_img_array, $_path_file_array;
	private $_img_extension, $_xls_extension, $_pdf_extension, $_doc_extension;
	private $_extension;
	
	private $_block_ctg;
	private $_block;
	
	function __construct(){
		
		parent::__construct();

		$this->_instance = 0;
		$this->_instanceName = $this->_className;

		$this->setAccess();
		$this->setGroups();
		
		// Options
		
		// Default values
		$this->_optionsValue = array(
			'title'=>_("Allegati"),
			'opt_ctg'=>true,
			'items_for_page'=>30
		);
		
		$this->_title = $this->setOption('title', array('translation'=>true, 'value'=>$this->_optionsValue['title']));
		$this->_category = $this->setOption('opt_ctg');
		$this->_items_for_page = $this->setOption('items_for_page', array('value'=>$this->_optionsValue['items_for_page']));
		
		$this->_options = new options($this->_className, $this->_instance);
		$this->_optionsLabels = array(
			"title"=>array('label'=>_("Titolo"), 'value'=>$this->_optionsValue['title'], 'required'=>true), 
			"opt_ctg"=>_("Divisione in categorie"),
			"items_for_page"=>array('label'=>_("Numero di elementi per pagina"), 'value'=>$this->_optionsValue['items_for_page'])
		);
		
		if($this->_items_for_page == 0) $this->_items_for_page = 30;
		
		$this->_tbl_attached = 'attached';
		$this->_tbl_category = 'attached_ctg';
		
		$this->_ico_next_img = "<img src=\"".$this->_img_www."/right.png\" title=\""._("successiva")."\" alt=\""._("successiva")."\" />";
		$this->_ico_prev_img = "<img src=\"".$this->_img_www."/left.png\" title=\""._("precedente")."\" alt=\""._("precedente")."\" />";
		
		$this->_img_extension = array('jpg','jpeg','png','gif');
		$this->_xls_extension = array('xls','xlt','xlsx','csv','sxc','stc','ods','ots');
		$this->_doc_extension = array('doc','docx','odt','ott','sxw','stw','rtf','txt');
		$this->_pdf_extension = array('pdf');
		$this->_extension = array();
		
		$this->_block_ctg = 'ctg';

		$this->_block = cleanVar($_REQUEST, 'block', 'string', '');
	}
	
	/**
	 * Gruppi per accedere alle funzionalità del modulo
	 * 
	 * @b _group_1: assistenti
	 */
	private function setGroups(){

		// Gestione file
		$this->_group_1 = array($this->_list_group[0], $this->_list_group[1]);
	}
	
	private function pathDirectory($ctg_id, $type){
		
		$ctg_dir = $this->nameDirectory($ctg_id);
		
		if($type == 'abs')
			$directory = $this->_data_dir.$this->_os.$ctg_dir.$this->_os;
		elseif($type == 'rel')
			$directory = $this->_data_www.'/'.$ctg_dir.'/';
		elseif($type == 'view')
			$directory = preg_replace("#^".preg_quote(SITE_WWW)."/#", "", $this->_data_www.'/'.$ctg_dir);
		else $directory = '';
		
		return $directory;
	}
	
	/**
	 * Avvia il downolad il un allegato
	 * 
	 * @return void
	 */
	public function downloader(){
		
		$doc_id = cleanVar($_GET, 'id', 'int', '');
		
		if(!empty($doc_id))
		{
			$query = "SELECT category, name FROM ".$this->_tbl_attached." WHERE id='$doc_id'";
			$a = $this->_db->selectquery($query);
			if(sizeof($a) > 0)
			{
				foreach($a AS $b)
				{
					$category = $b['category'];
					$filename = htmlChars($b['name']);
					$full_path = $this->pathDirectory($category, 'abs').$filename;
					
					download($full_path);
					exit();
				}
			}
			else exit();
		}
		else exit();
	}
	
	/**
	 * Mostra l'indirizzo di un allegato
	 * 
	 * @return string
	 */
	public function textLink(){
	
		$this->accessGroup('ALL');

		$code = cleanVar($_GET, 'code', 'int', '');
		
		$query = "SELECT category, name FROM ".$this->_tbl_attached." WHERE id='$code'";
		$a = $this->_db->selectquery($query);
		if(sizeof($a) > 0)
		{
			foreach($a AS $b)
			{
				$reference = $b['category'];
				$filename = htmlChars($b['name']);
				
				$path = preg_replace("#^".preg_quote(SITE_WWW)."/#", "", $this->pathDirectory($reference, 'rel').$filename);
				$path_html = "&#60;a href=\"".$path."\"&#62;<b>"._("testo da sostituire")."</b>&#60;/a&#62;";
			}
		}
		else {
			$codeMessages = error::codeMessages();
			exit($codeMessages[9]);
		}
		
		$GINO = "<p>"._("Per creare un link a questo allegato utilizzare il codice seguente:")."</p>\n";
		$GINO .= $path_html;

		return $GINO;
	}
	
	private function nameDirectory($category){
	
		$query = "SELECT directory FROM ".$this->_tbl_category." WHERE id='$category'";
		$a = $this->_db->selectquery($query);
		if(sizeof($a) > 0)
		{
			foreach($a AS $b)
			{
				$directory = $b["directory"];
			}
		}
		else $directory = '';
		
		return $directory;
	}
	
	private function newDirectory($category){
	
		if(is_int($category)) $directory = 'c'.$category; else $directory = '';
		
		return $directory;
	}
	
	private function info(){
		
		$htmlsection = new htmlSection(array('class'=>'admin', 'headerTag'=>'h1', 'headerLabel'=>_("Informazioni")));
		$buffer = "<p>"._("Per inserire allegati è necessario prima creare una categoria.")."</p>";
		
		$htmlsection->content = $buffer;

		return $htmlsection->render();
	}
	
	/**
	 * Interfaccia amministrativa per la gestione degli allegati
	 * 
	 * @return string
	 */
	public function manageAttached(){
	
		$this->accessGroup('ALL');
		
		$htmltab = new htmlTab(array("linkPosition"=>'right', "title"=>$this->_title));	
		$link_admin = "<a href=\"".$this->_home."?evt[$this->_className-manageAttached]&block=permissions\">"._("Permessi")."</a>";
		$link_options = "<a href=\"".$this->_home."?evt[$this->_className-manageAttached]&block=options\">"._("Opzioni")."</a>";
		$link_dft = "<a href=\"".$this->_home."?evt[".$this->_className."-manageAttached]\">"._("Gestione")."</a>";
		$sel_link = $link_dft;

		if($this->_block == 'options') {
			$GINO = sysfunc::manageOptions($this->_instance, $this->_className);		
			$sel_link = $link_options;
		}
		elseif($this->_block == 'permissions') {
			$GINO = sysfunc::managePermissions($this->_instance, $this->_className);		
			$sel_link = $link_admin;
		}
		else {

			// Variables
			$id = cleanVar($_GET, 'id', 'int', '');
			$ref = cleanVar($_GET, 'ref', 'int', '');
			$action = cleanVar($_GET, 'action', 'string', '');
			$block = cleanVar($_GET, 'block', 'string', '');
			// End
		
			$form = '';
			
			if($block == $this->_block_ctg AND $this->_access->AccessVerifyGroupIf($this->_className, $this->_instance, '', ''))
			{
				if($action == $this->_act_delete)
				{
					$form .= $this->formDeleteCtg($id, $action);
				}
				else
				{
					$form .= $this->formCtg($id, $action);
				}
				$select = $id;
			}
			else
			{
				if($action == $this->_act_insert OR $action == $this->_act_modify)
				{
					$form .= $this->formFile($id, $action, $ref);
					$select = $id;
				}
				elseif($action == $this->_act_delete)
				{
					$form .= $this->formDeleteFile($id, $action, $ref);
					$select = $id;
				}
				else
				{
					$form .= $this->info();
					$select = '';
				}
			}
		
			$GINO = "<div class=\"vertical_1\">\n";
			$GINO .= $this->listTree($ref, $select);
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
	
	private function listTree($category, $select){
	
		if($this->_category AND $this->_access->AccessVerifyGroupIf($this->_className, $this->_instance, '', ''))
			$lnk_ctg = "<a href=\"".$this->_home."?evt[".$this->_className."-manageAttached]&amp;block=".$this->_block_ctg."&amp;action=".$this->_act_insert."\">".$this->icon('insert', _("nuova categoria"))."</a>";
		else $lnk_ctg = '';
		
		$htmlsection = new htmlSection(array('class'=>'admin', 'headerTag'=>'header', 'headerLabel'=>$this->_title));
		if($lnk_ctg) $htmlsection->headerLinks = $lnk_ctg;

		$query = "SELECT id, name FROM ".$this->_tbl_category." ORDER BY name ASC";
		$a = $this->_db->selectquery($query);
		if(sizeof($a) > 0)
		{
			$htmlList = new htmlList(array("numItems"=>sizeof($a), "separator"=>true));
			$GINO = $htmlList->start();
			foreach($a AS $b)
			{
				$ctg_id = $b['id'];
				$name = htmlChars($b['name']);
				
				$selected = (!empty($category) AND $category == $ctg_id)?true:false;
				
				$query_file = "SELECT id FROM ".$this->_tbl_attached." WHERE category='$ctg_id'";
				$result_file = $this->_db->selectquery($query_file);
				if(sizeof($result_file) > 0)
					$lnk_file = "<a href=\"".$this->_home."?evt[".$this->_className."-manageAttached]&amp;ref=$ctg_id\">$name</a>";
				else $lnk_file = $name;
				
				$directory = $this->pathDirectory($ctg_id, 'view');
				$lnk_file .= "<br /><span class=\"little\">"._("cartella").": $directory</span>";
				
				$lnk_modify = " <a href=\"".$this->_home."?evt[".$this->_className."-manageAttached]&amp;id=$ctg_id&amp;block=".$this->_block_ctg."&amp;action=".$this->_act_modify."\">".$this->icon('modify', _("modifica categoria"))."</a>";
				$lnk_insert = " <a href=\"".$this->_home."?evt[".$this->_className."-manageAttached]&amp;ref=$ctg_id&amp;action=".$this->_act_insert."\">".$this->icon('insert', _("nuovo allegato"))."</a>";
				$lnk_delete = " <a href=\"".$this->_home."?evt[".$this->_className."-manageAttached]&amp;id=$ctg_id&amp;block=".$this->_block_ctg."&amp;action=".$this->_act_delete."\">".$this->icon('delete', _("elimina categoria"))."</a>";
				
				if($this->_access->AccessVerifyGroupIf($this->_className, $this->_instance, '', ''))
					$links = array($lnk_modify, $lnk_insert, $lnk_delete);

				elseif($this->_access->AccessVerifyGroupIf($this->_className, $this->_instance, $this->_user_group, $this->_group_1))
					$links = "$lnk_insert";
				
				$itemContent = (!empty($category) AND $category == $ctg_id)? $this->tree($category, $select):null;
				$GINO .= $htmlList->item($lnk_file, $links, $selected, true, $itemContent);

			}
			$GINO .= $htmlList->end();	
		}
		else
		{
			$GINO = "<div class=\"message\">"._("non risultano categorie registrate")."</div>\n";
		}
		
		$htmlsection->content = $GINO;

		return $htmlsection->render();
	}
	
	private function tree($category, $select){
	
		$GINO = '';
		
		$numberTotRecord = "SELECT id FROM ".$this->_tbl_attached." WHERE category='$category'";
		$this->_list = new PageList($this->_items_for_page, $numberTotRecord, 'query');
		
		$start = $this->_list->start();
		$limit = $this->_db->limit($this->_list->rangeNumber, $start);
		$query = "SELECT id, name FROM ".$this->_tbl_attached." WHERE category='$category' ORDER BY name ASC $limit";
		$a = $this->_db->selectquery($query);
		if(sizeof($a) > 0)
		{
			$htmlList = new htmlList(array("class"=>"admin inside", "numItems"=>sizeof($a), "separator"=>true));
			$GINO = $htmlList->start();
			
			foreach($a AS $b)
			{
				$file_id = $b['id'];
				$name = htmlChars($b['name']);
				
				$sign = '';
				$selected = $select == $file_id?true:false;
				
				$lnk_delete = " <a href=\"".$this->_home."?evt[".$this->_className."-manageAttached]&amp;id=$file_id&amp;ref=$category&amp;action=".$this->_act_delete."\">".$this->icon('delete', '')."</a>";
				$lnk_modify = " <a href=\"".$this->_home."?evt[".$this->_className."-manageAttached]&amp;id=$file_id&amp;ref=$category&amp;action=".$this->_act_modify."\">".$this->icon('modify', '')."</a>";
				$url = $this->_home."?pt[".$this->_className."-textLink]&amp;code=$file_id";
				$lnk_link = " <span class=\"link\" onclick=\"window.myWin = new layerWindow({'title':'"._("Link alla risorsa")."', 'url':'$url', 'bodyId':'link$file_id', 'width':500});myWin.display($(this));\">".$this->icon('link', '')."</span>";
				$links = ($this->_access->AccessVerifyGroupIf($this->_className, $this->_instance, $this->_user_group, $this->_group_1))? array($lnk_link, $lnk_modify, $lnk_delete):array();

				$GINO .= $htmlList->item("$sign $name", $links, $selected, true);
			}
			$GINO .= $htmlList->end();
			
			$link = $this->_plink->aLink($this->_instanceName, 'manageAttached', "ref=$category", '', array('basename'=>false));
			$GINO .= "<p>".$this->_list->listReferenceGINO($link)."</p>";
		}
		
		return $GINO;
	}
	
	private function formDeleteFile($id, $action, $reference){
		
		$gform = new Form('gform', 'post', false);
		
		$filename = htmlChars($this->_db->getFieldFromId($this->_tbl_attached, 'name', 'id', $id));
		
		$htmlsection = new htmlSection(array('class'=>'admin', 'headerTag'=>'h1', 'headerLabel'=>_("Elimina")." '$filename'", 'headerLinks'=>$this->_link_return));
		
		$GINO = $gform->form($this->_home."?evt[".$this->_className."-actionDeleteFile]", '', '');
		$GINO .= $gform->hidden('id', $id);
		$GINO .= $gform->hidden('ref', $reference);
		$GINO .= $gform->hidden('action', $action);
		$GINO .= $gform->cinput('delete_action', 'submit', _("elimina"), array(_("Attenzione!"), _("l'eliminazione è definitiva")), array("classField"=>"submit"));
		$GINO .= $gform->cform();
		
		$htmlsection->content = $GINO;

		return $htmlsection->render();
	}
	
	/**
	 * Eliminazione di un allegato
	 * 
	 * @see $_group_1
	 */
	public function actionDeleteFile(){
	
		$this->accessGroup($this->_group_1);
		
		$id = cleanVar($_POST, 'id', 'int', '');
		$action = cleanVar($_POST, 'action', 'string', '');
		$reference = cleanVar($_POST, 'ref', 'string', '');
		
		$link = "ref=$reference";
		$link_error = "id=$id&ref=$reference&action=$action";
		$redirect = $this->_className.'-manageAttached';
		
		if(!empty($id) AND $action == $this->_act_delete)
		{
			$query = "SELECT name FROM ".$this->_tbl_attached." WHERE id='$id' AND category='$reference'";
			$a = $this->_db->selectquery($query);
			if(sizeof($a) > 0)
			{
				foreach($a AS $b)
				{
					$filename = $b['name'];
					$directory = $this->pathDirectory($reference, 'abs');
					
					$result = $this->deleteFile($directory.$filename, $this->_home, $redirect, $link_error);
				}
			}
			
			if($result)
			{
				$query_delete = "DELETE FROM ".$this->_tbl_attached." WHERE id='$id'";
				$this->_db->actionquery($query_delete);
				
				EvtHandler::HttpCall($this->_home, $this->_className.'-manageAttached', $link);
			}
			else
					exit(error::errorMessage(array('error'=>9), $this->_home."?evt[$this->_className-manageAttached]&$link_error"));
		}
		else
			exit(error::errorMessage(array('error'=>9), $this->_home."?evt[$this->_className-manageAttached]&$link_error"));
	}
	
	private function formFile($id, $action, $reference){
	
		$gform = new Form('gform', 'post', true);
		$gform->load('dataform');
		
		if(!empty($id) AND $action == $this->_act_modify)
		{
			$query = "SELECT name FROM ".$this->_tbl_attached." WHERE id='$id'";
			$a = $this->_db->selectquery($query);
			if(sizeof($a) > 0)
			{
				foreach($a AS $b)
				{
					$filename = htmlInput($b['name']);
				}
				
				$title = _("Allegato");
				$submit = _("modifica");
			}
		}
		else
		{
			$filename = '';
			$title = _("Nuovo allegato");
			$submit = _("inserisci");
		}
		
		$preview = ($filename && extension($filename, $this->_img_extension)) ? true : false;
		
		$htmlsection = new htmlSection(array('class'=>'admin', 'headerTag'=>'h1', 'headerLabel'=>$title, 'headerLinks'=>$this->_link_return));
		
		$GINO = "<a name=\"a1\"></a>";

		$required = 'file';
		$GINO = $gform->form($this->_home."?evt[".$this->_className."-actionFile]", true, $required);
		$GINO .= $gform->hidden('id', $id);
		$GINO .= $gform->hidden('action', $action);
		$GINO .= $gform->hidden('ref', $reference);
		$GINO .= $gform->hidden('old_file', $filename);
		$GINO .= $gform->cfile('file', $filename, _("File"), array("required"=>true, 'preview'=>$preview, 'previewSrc'=>$this->pathDirectory($reference, 'rel').$filename));
		$GINO .= $gform->cinput('submit_action', 'submit', $submit, '', array("classField"=>"submit"));
		$GINO .= $gform->cform();

		$htmlsection->content = $GINO;

		return $htmlsection->render();
	}
	
	/**
	 * Inserimento di un allegato
	 * 
	 * @see $_group_1
	 */
	public function actionFile(){
	
		$this->accessGroup($this->_group_1);
		
		$gform = new Form('gform', 'post', false);
		$gform->save('dataform');
		
		$filename_name = $_FILES['file']['name'];
		$old_file = cleanVar($_POST, 'old_file', 'string', '');
		
		$id = cleanVar($_POST, 'id', 'int', '');
		$action = cleanVar($_POST, 'action', 'string', '');
		$reference = cleanVar($_POST, 'ref', 'string', '');
		
		$link = "ref=$reference&action=$action";
		
		$path_dir = $this->pathDirectory($reference, 'abs');
		$redirect = $this->_className.'-manageAttached';
		$link_error = $this->_home."?evt[$redirect]&ref=$reference&action=$action";
		
		if(empty($filename_name))
			exit(error::errorMessage(array('error'=>2), $link_error));
		
		if($action == $this->_act_insert)
			$query = "INSERT INTO ".$this->_tbl_attached." (category) VALUES ($reference)";
		$result = $this->_db->actionquery($query);

		$rid = ($action == $this->_act_insert)? $this->_db->getlastid($this->_tbl_attached):$id;

		$query_err = "DELETE FROM ".$this->_tbl_attached." WHERE name=''";
		$gform->manageFile('file', $old_file, false, $this->_extension, $path_dir, $link_error, $this->_tbl_attached, 'name', 'id', $rid, array("errorQuery"=>$query_err));

		EvtHandler::HttpCall($this->_home, $redirect, $link);
	}
	
	private function resultSearchFileName($file_new, $file_old, $directory){
		
		$listFile = searchNameFile($directory);
		$count = 0;
		if(sizeof($listFile) > 0)
		{
			foreach($listFile AS $value)
			{
				if(!empty($file_old))
				{
					if($file_new == $value AND $file_old != $value) $count++;
				}
				elseif($file_new == $value) $count++;
			}
		}
		
		return $count;
	}
	
	/*
		Categorie
	*/
	
	private function formCtg($category, $action){
	
		$gform = new Form('gform', 'post', true);
		$gform->load('dataform');
		
		if(!empty($category) AND $action = $this->_act_modify)
		{
			$query = "SELECT name FROM ".$this->_tbl_category." WHERE id='$category'";
			$a = $this->_db->selectquery($query);
			if(sizeof($a) > 0)
			{
				foreach($a AS $b)
				{
					$id = $category;
					$name = htmlInput($b['name']);
				}
				
				$title = _("Modifica")." '$name'";
				$submit = _("modifica");
			}
		}
		else
		{
			$id = '';
			$name = $gform->retvar('name', '');
			
			$title = _("Nuova categoria");
			$submit = _("inserisci");
		}
		
		$htmlsection = new htmlSection(array('class'=>'admin', 'headerTag'=>'h1', 'headerLabel'=>$title));

		$required = 'name';
		$GINO = $gform->form($this->_home."?evt[".$this->_className."-actionCtg]", '', $required);
		$GINO .= $gform->hidden('id', $id);
		$GINO .= $gform->hidden('action', $action);
		$GINO .= $gform->cinput('name', 'text', $name, _("Nome"), array("required"=>true, "size"=>40, "maxlength"=>100));
		$GINO .= $gform->cinput('submit_action', 'submit', $submit, '', array("classField"=>"submit"));
		$GINO .= $gform->cform();
		
		$htmlsection->content = $GINO;

		return $htmlsection->render();
	}
	
	/**
	 * Inserimento e modifica di un allegato
	 */
	public function actionCtg(){
	
		$this->accessGroup('');
		
		$gform = new Form('gform', 'post', false);
		$gform->save('dataform');
		$req_error = $gform->arequired();
		
		$id = cleanVar($_POST, 'id', 'int', '');
		$action = cleanVar($_POST, 'action', 'string', '');
		$name = cleanVar($_POST, 'name', 'string', '');
		
		$ref = "block=".$this->_block_ctg."&action=".$action.($id?"&id=$id":"");
		$ref_error = $ref;
		$redirect = $this->_className.'-manageAttached';
		$link_error = $this->_home."?evt[$redirect]&$ref";
		
		if($req_error > 0) 
			exit(error::errorMessage(array('error'=>1), $link_error));
		
		if($action == $this->_act_insert)
		{
			$query = "INSERT INTO ".$this->_tbl_category." (id, name, directory)
			VALUES (DEFAULT, '$name', '')";
			$result = $this->_db->actionquery($query);
			
			if($result)
			{
				$last_id = $this->_db->getlastid($this->_tbl_category);
				$directory = $this->newDirectory($last_id);
				
				$query2 = "UPDATE ".$this->_tbl_category." SET directory='$directory' WHERE id='$last_id'";
				$result2 = $this->_db->actionquery($query2);
				
				if(!empty($directory) AND $result2) mkdir($this->_data_dir.$this->_os.$directory);
			}
		}
		elseif($action == $this->_act_modify)
		{
			$query = "UPDATE ".$this->_tbl_category." SET name='$name' WHERE id='$id'";
			$result = $this->_db->actionquery($query);
		}
		
		if($result)
			EvtHandler::HttpCall($this->_home, $redirect, $ref);
		else
			exit(error::errorMessage(array('error'=>9), $link_error));
	}
	
	private function formDeleteCtg($id, $action){
		
		$gform = new Form('gform', 'post', false);
		
		$name = htmlChars($this->_trd->selectTXT($this->_tbl_category, 'name', $id, 'id'));
		
		$htmlsection = new htmlSection(array('class'=>'admin', 'headerTag'=>'h1', 'headerLabel'=>_("Elimina la categoria")." '$name'", 'headerLinks'=>$this->_link_return));
		
		$GINO = $gform->form($this->_home."?evt[".$this->_className."-actionDeleteCtg]", '', '');
		$GINO .= $gform->hidden('id', $id);
		$GINO .= $gform->hidden('action', $action);

		$GINO .= $gform->cinput('delete_action', 'submit', _("elimina"), array(_("Attenzione!"), _("l'eliminazione è definitiva e comporta l'eliminazione dei file associati")), array("classField"=>"submit"));
		$GINO .= $gform->cform();
		
		$htmlsection->content = $GINO;

		return $htmlsection->render();
	}
	
	/**
	 * Eliminazione di un allegato
	 */
	public function actionDeleteCtg(){
		
		$this->accessGroup('');
		
		$id = cleanVar($_POST, 'id', 'int', '');
		$action = cleanVar($_POST, 'action', 'string', '');
		
		$link = '';
		$redirect = $this->_className.'-manageAttached';
		$link_error = $this->_home."?evt[$redirect]&id=$id&block=".$this->_block_ctg."&action=$action";

		if(empty($id) OR $action != $this->_act_delete)
			exit(error::errorMessage(array('error'=>1), $link_error));
		
		$directory = $this->pathDirectory($id, 'abs');
		if($directory)
		{
			// Eleminazione allegati
			$query_del_att = "DELETE FROM ".$this->_tbl_attached." WHERE category='$id'";
			$result_att = $this->_db->actionquery($query_del_att);
			
			// Eliminazione categoria
			if($result_att)
			{
				$query_del_ctg = "DELETE FROM ".$this->_tbl_category." WHERE id='$id'";
				$result_ctg = $this->_db->actionquery($query_del_ctg);
				//language::deleteTranslations($this->_tbl_category, $id);
			}
			
			// Eliminazione file e directory
			if($result_ctg)
				$this->deleteFileDir($directory, true);
		}
		else
		{
			exit(error::errorMessage(array('error'=>9), $link_error));
		}
		
		EvtHandler::HttpCall($this->_home, $redirect, $link);		
	}
	
	/**
	 * Slideshow degli allegati con i comandi per spostarsi avanti e indietro
	 * 
	 * @see $_group_1
	 * @return print
	 */
	public function slideShow() {
	
		$this->accessGroup($this->_group_1);
		
		$ctg = cleanVar($_REQUEST, 'ctg', 'int', '');
		$img = cleanVar($_REQUEST, 'img', 'int', '');
		
		if(empty($ctg))
		{
			echo ''; exit();
		}
		
		$directory = $this->pathDirectory($ctg, 'view');
		
		$GINO = '';
		
		$query = "SELECT id, name FROM ".$this->_tbl_attached." WHERE category='$ctg'";
		$a = $this->_db->selectquery($query);
		if(sizeof($a) > 0)
		{	
			foreach($a AS $b)
			{
				$name = htmlChars($b['name']);
				$path = $directory.'/'.$name;
				
				if(extension($name, $this->_img_extension))
					$path_to_mark = $path;
				elseif(extension($name, $this->_xls_extension))
					$path_to_mark = $this->_class_img.$this->_os.'mark_XLS.jpg';
				elseif(extension($name, $this->_pdf_extension))
					$path_to_mark = $this->_class_img.$this->_os.'mark_PDF.jpg';
				elseif(extension($name, $this->_doc_extension))
					$path_to_mark = $this->_class_img.$this->_os.'mark_DOC.jpg';
				else
					$path_to_mark = $this->_img_www.$this->_os.'mark_FILE.jpg';
				
				$this->_path_file_array[] = $path;
				$this->_path_img_array[] = $path_to_mark;
			}
		}
		
		if(sizeof($this->_path_file_array) > 0) $GINO .= $this->jsLib();
		else {
			echo _("la categoria non contiene allegati");
			exit();
		}
		
		if(sizeof($a) > 0)
		{
			$GINO .= "<div id=\"slideShow\" style=\"background-color:#ffffff;text-align:center;color:#000000;padding:10px 0px\">\n";
			$GINO .= "<span id=\"img_path\" >".$this->_path_file_array[$img]."</span><br />";
			$GINO .= "<span style=\"margin-right: 20px;padding:10px 0px;cursor:pointer\" onclick=\"changeImg('prev')\">".$this->_ico_prev_img."</span>";
			$GINO .= "<span id=\"act_image\"><img src=\"".$this->_path_img_array[$img]."\" height=\"80px\" id=\"act_img\"/><input type=\"hidden\" id=\"actual_key\" value=\"$img\" /></span>\n";
			$GINO .= "<span style=\"margin-left: 20px;padding:10px 0px;cursor:pointer\" onclick=\"changeImg('next')\">".$this->_ico_next_img."</span>";
			
			$GINO .= "</div>\n";
		}
		else
		{
			$GINO .= "<div id=\"slideShow\" style=\"background-color:#000000;text-align:center;color:#ffffff\">\n";
			$GINO .= "<span>"._("non risultano immagini registrate")."</span>\n";
			$GINO .= "</div>\n";
		}
		
		echo $GINO;
		exit();
	}
	
	/**
	 * Libreria javascript per lo slideshow
	 * 
	 * @see $_group_1
	 * @return string
	 */
	public function jsLib() {
	
		$this->accessGroup($this->_group_1);
		
		$GINO = "<script type=\"text/javascript\">\n";
				
		$GINO .= "function changeImg(way) {
					
					var path_file_array = new Array();
					var path_img_array = new Array();";
		
		for($i=0, $end=sizeof($this->_path_file_array); $i<$end; $i++)
		{
			$GINO .= "path_file_array[".$i."] = '".$this->_path_file_array[$i]."';";
			$GINO .= "path_img_array[".$i."] = '".$this->_path_img_array[$i]."';";
		}
		$GINO .= "		var myFx = new Fx.Tween($('act_image'), {
						duration: 'short'				
					});
					
					var key = $('actual_key').getProperty('value');
					
					if(way=='next') {";
		$GINO .= "
						myFx.start('opacity', '0').chain(function() {
							
							if(parseInt(key)==(path_file_array.length-1)) var next_el = 0;
							else var next_el = parseInt(key)+1;
							
							$('act_img').setProperty('src', path_img_array[next_el]);
							$('img_path').set('html', path_file_array[next_el]);
							$('actual_key').setProperty('value', next_el);
							this.start('opacity', '1');
						});
					}
					else if(way=='prev') {";
		$GINO .= "
						myFx.start('opacity', '0').chain(function() {
							
							if(key=='0') var prev_el = path_file_array.length-1;
							else var prev_el = parseInt(key)-1;
							
							$('act_img').setProperty('src', path_img_array[prev_el]);
							$('img_path').set('html', path_file_array[prev_el]);
							$('actual_key').setProperty('value', prev_el);
							this.start('opacity', '1');
						});
					}
				}\n";
		$GINO .= "</script>\n";
		
		return $GINO;
	}
}
?>
