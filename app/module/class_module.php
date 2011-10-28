<?php
/*================================================================================
Gino - a generic CMS framework
Copyright (C) 2005  Otto Srl - written by Marco Guidotti

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

For additional information: <opensource@otto.to.it>
================================================================================*/
class module extends AbstractEvtClass{

	protected $_instance, $_instanceName;
	private $_title;
	private $_action;
	private $_mdlTypes;

	function __construct(){

		parent::__construct();

		$this->_instance = 0;
		$this->_instanceName = $this->_className;

		$this->setAccess();

		$this->_title = _("Gestione moduli");

		$this->_action = cleanVar($_REQUEST, 'action', 'string', '');

		$this->_mdlTypes = array('class'=>_("classe"), 'page'=>_("pagina"), 'func'=>_("funzione"));

	}
	
	public static function permission(){

		$access_2 = _("Permessi di amministrazione");
		$access_3 = '';
		return array($access_2, $access_3);
	}

	private function nameRole($role) {

		$query = "SELECT name FROM ".$this->_tbl_user_role." WHERE role_id='$role'";
		$a = $this->_db->selectquery($query);
		if(sizeof($a) > 0)
		{
			foreach($a AS $b)
			{
				$name = htmlChars($b['name']);
			}
		}
		return $name;
	}
	
	private function accessRoleValue($name, $valuedb, $text, $role_list){
		
		$GINO = "<p class=\"line\"><span class=\"subtitle\">$text</span><br />";
		foreach($role_list AS $key => $value)
		{
			if(!$this->_access->AccessVerifyRoleIDIf($key)) $disabled = 'disabled'; else $disabled = '';
			if($key == $valuedb) $checked = 'checked'; else $checked = '';

			$GINO .= "<input type=\"radio\" id=\"$name\" name=\"$name\" value=\"$key\" $checked $disabled /> $value<br />";
		}
		$GINO .= "</p>\n";
		
		return $GINO;
	}

	public function manageModule(){

		$this->accessType($this->_access_2);
		
		$htmltab = new htmlTab(array("linkPosition"=>'right', "title"=>_("Moduli")));	
		$link_dft = "<a href=\"".$this->_home."?evt[".$this->_className."-manageModule]\">"._("Gestione")."</a>";
		$sel_link = $link_dft;

		$id = cleanVar($_GET, 'id', 'int', '');

		$GINO = "<div class=\"vertical_1\">\n";
		$GINO .= $this->listModule($id);
		$GINO .= "</div>\n";

		$GINO .= "<div class=\"vertical_2\">\n";
		if($id && $this->_action == $this->_act_modify) $GINO .= $this->formEditModule($id);
		elseif($this->_action == $this->_act_insert) $GINO .= $this->formInsertModule();
		elseif($id && $this->_action == $this->_act_delete) $GINO .= $this->formRemoveModule($id);
		else $GINO .= $this->infoDoc();
		$GINO .= "</div>\n";

		$GINO .= "<div class=\"null\"></div>";

		$GINO .= "</div>\n";

		$htmltab->navigationLinks = array($link_dft);
		$htmltab->selectedLink = $sel_link;
		$htmltab->htmlContent = $GINO;
		return $htmltab->render();

	}

	private function infoDoc(){
		
		$htmlsection = new htmlSection(array('class'=>'admin', 'headerTag'=>'h1', 'headerLabel'=>_("Informazioni")));
		$buffer = "<p>"._("In questa sezione è possibile creare nuovi moduli come istanze di classi o funzioni presenti nel sistema")."</p>\n";
		
		$htmlsection->content = $buffer;

		return $htmlsection->render();

	}

	private function listModule($sel_id){

		$link_1 = '';

		$link_insert = "<a href=\"$this->_home?evt[$this->_className-manageModule]&amp;action=$this->_act_insert\">".pub::icon('insert', _("nuovo modulo"))."</a>";

		$htmlsection = new htmlSection(array('class'=>'admin', 'headerTag'=>'header', 'headerLabel'=>_("Moduli"), 'headerLinks'=>$link_insert));

		$query = "SELECT id, label, name, type, masquerade FROM ".$this->_tbl_module." WHERE type!='page' ORDER BY label";
		$a = $this->_db->selectquery($query);
		if(sizeof($a)>0) {
			$htmlList = new htmlList(array("numItems"=>sizeof($a), "separator"=>true));
			$GINO = $htmlList->start();
			foreach($a as $b) {
				$id = htmlChars($b['id']);
				$label = htmlChars($this->_trd->selectTXT(TBL_MODULE, 'label', $b['id']));
				$name = htmlChars($b['name']);
				$type = htmlChars($b['type']);
				$masquerade = htmlChars($b['masquerade']);
				$active = ($masquerade=='no')?_("si"):_("no");
	
				$selected = ($id===$sel_id)?true:false;
				$link_modify = "<a href=\"$this->_home?evt[$this->_className-manageModule]&id=$id&action=$this->_act_modify\">".pub::icon('modify', _("modifica"))."</a>";
				$link_delete = "<a href=\"$this->_home?evt[$this->_className-manageModule]&id=$id&action=$this->_act_delete\">".pub::icon('delete', _("elimina"))."</a>";

				$text = "$label<br/>ID: $id - <span style=\"font-weight:normal\">"._("tipo: ").$this->_mdlTypes[$type]." "._("attivo: ").$active."</span>";
				$GINO .= $htmlList->item($text, array($link_delete, $link_modify), $selected, true);

			}
			$GINO .= $htmlList->end();
		}
		
		$htmlsection->content = $GINO;
		
		return $htmlsection->render();
	}

	private function formRemoveModule($id) {
		
		$gform = new Form('gform', 'post', true);
		$gform->load('dataform');

		$mdlName = $this->_db->getFieldFromId($this->_tbl_module, 'label', 'id', $id);

		$htmlsection = new htmlSection(array('class'=>'admin', 'headerTag'=>'h1', 'headerLabel'=>_("Eliminazione modulo")."'".htmlChars($mdlName)."'"));

		$GINO = "<p>"._("L'eliminazione del modulo comporta l'eliminazione di tutti i dati")."</p>\n";

		$required = '';
		$GINO .= $gform->form($this->_home."?evt[".$this->_className."-actionRemoveModule]", '', $required);
		$GINO .= $gform->hidden('id', $id);
		$GINO .= $gform->cinput('submit_action', 'submit', _("elimina"), _("sicuro di voler procedere?"), array("classField"=>"submit"));
		$GINO .= $gform->cform();
					
		$htmlsection->content = $GINO;

		return $htmlsection->render();

	}
	
	public function actionRemoveModule() {

		$this->accessType($this->_access_2);

		$id = cleanVar($_POST, 'id', 'int', '');
		$type= $this->_db->getFieldFromId($this->_tbl_module, 'type', 'id', $id);

		if($type=='class') {
			
			$class= $this->_db->getFieldFromId($this->_tbl_module, 'class', 'id', $id);
			$classObj = new $class($id);
			$classObj->deleteInstance();

		}

		$query = "DELETE FROM ".$this->_tbl_module." WHERE id='$id'";
		$result = $this->_db->actionquery($query);

		language::deleteTranslations($this->_tbl_module, $id);

		EvtHandler::HttpCall($this->_home, $this->_className.'-manageModule', '');
	}

	private function formInsertModule() {
		
		$gform = new Form('gform', 'post', true);
		$gform->load('dataform');

		$htmlsection = new htmlSection(array('class'=>'admin', 'headerTag'=>'h1', 'headerLabel'=>_("Nuovo modulo")));

		$required = 'type,name,label,role1';
		$GINO = $gform->form($this->_home."?evt[".$this->_className."-actionInsertModule]", '', $required);
		$onchange = "onchange=\"ajaxRequest('post', '$this->_home?pt[$this->_className-formModule]', 'type='+$(this).value, 'formNewModule', {'load':'formNewModule', 'script':true})\"";
		$GINO .= $gform->cselect('type', $gform->retvar('type', ''), array('class'=>_("modulo di classe"), 'func'=>_("modulo funzione")), _("Seleziona il tipo di modulo da inserire"), array("required"=>true, "js"=>$onchange));

		$GINO .= $gform->cell($this->formModule($gform->retvar('type', '')), array("id"=>'formNewModule'));

		$GINO .= $gform->cform();
					
		$htmlsection->content = $GINO;

		return $htmlsection->render();

	}
	
	public function formModule($type=null) {

		$this->accessType($this->_access_2);

		$role_list = $this->_access->listRole();

		$gform = new Form('gform', 'post', true);
		if(!$type) $type = cleanVar($_POST, 'type', 'string', '');

		if(empty($type)) return '';

		$GINO = $gform->startTable();
		if($type=='func') {
			$sysfunc = new sysfunc();
			$functions = $sysfunc->outputFunctions();
			$funcSel = array();
			foreach($functions as $k=>$v) $funcSel[$k] = $v['label'];
			$GINO .= $gform->cselect('name', $gform->retvar('name', ''), $funcSel, _("Modulo funzione"), array("required"=>true));
			$GINO .= $gform->cinput('label', 'text', $gform->retvar('label', ''), _("Etichetta"), array("required"=>true, "size"=>40, "maxlength"=>200));
			$GINO .= $gform->ctextarea('description', $gform->retvar('description', ''), _("Descrizione"), array("cols"=>45, "rows"=>4));
			$GINO .= $gform->cradio('role1', $gform->retvar('role1', 5), $role_list, '', _("Permessi di visualizzazione"), array("required"=>true, "aspect"=>"v"));
			$GINO .= $gform->cinput('submit_action', 'submit', _("inserisci"), '', array("classField"=>"submit"));

		}
		else {
			$query = "SELECT name, label FROM ".$this->_tbl_module_app." WHERE instance='yes' AND masquerade='no' ORDER BY label";
			$onchange = "onchange=\"ajaxRequest('post', '$this->_home?pt[$this->_className-formModuleClass]', 'class='+$(this).value, 'formNewModuleClass', {'load':'formNewModuleClass', 'script':true})\"";
			$GINO .= $gform->cselect('class', $gform->retvar('class', ''), $query, _("Classe"), array("required"=>true, "js"=>$onchange));
			$GINO .= $gform->cell($this->formModuleClass($gform->retvar('class', '')), array("id"=>"formNewModuleClass"));
			
		}
		$GINO .= $gform->endTable();

		return $GINO;
	}

	public function formModuleClass($class=null) {
	
		$this->accessType($this->_access_2);

		$role_list = $this->_access->listRole();

		$gform = new Form('gform', 'post', true);
		if(!$class) $class = cleanVar($_POST, 'class', 'string', '');
		
		if(empty($class)) return '';

		$GINO = $gform->startTable();
		$GINO .= $gform->cinput('name', 'text', $gform->retvar('name', ''), array(_("Nome"), _("Deve contenere solamente caratteri alfanumerici o il carattere '_'")), array("required"=>true, "size"=>40, "maxlength"=>200, "pattern"=>"^[\w\d_]*$", "hint"=>_("solo caretteri alfnumerici o underscore")));
		$GINO .= $gform->cinput('label', 'text', $gform->retvar('label', ''), _("Etichetta"), array("required"=>true, "size"=>40, "maxlength"=>200));
		$GINO .= $gform->ctextarea('description', $gform->retvar('description', ''), _("Descrizione"), array("cols"=>45, "rows"=>4));
		if(method_exists($class, 'outputFunctions')) {
			$GINO .= $gform->cradio('role1', $gform->retvar('role1', 5), $role_list, '', _("Permessi di visualizzazione"), array("required"=>true, "aspect"=>"v"));
		}

		// Metodi aggiuntivi
		if(method_exists($class, 'permission')) {
			$permission = call_user_func(array($class, 'permission'));

			if(!empty($permission[0])) {
				$GINO .= $gform->cradio('role2', $gform->retvar('role2', 5), $role_list, '', $permission[0], array("required"=>true, "aspect"=>"v"));
			}
			if(!empty($permission[1])) {
				$GINO .= $gform->cradio('role3', $gform->retvar('role3', 5), $role_list, '', $permission[1], array("required"=>true, "aspect"=>"v"));
			}
		}
		$GINO .= $gform->cinput('submit_action', 'submit', _("inserisci"), '', array("classField"=>"submit"));
		$GINO .= $gform->endTable();

		return $GINO;

	}

	public function actionInsertModule() {
		
		$this->accessType($this->_access_2);
		
		$this->_gform = new Form('gform','post', true);
		$this->_gform->save('dataform');
		$req_error = $this->_gform->arequired();

		$type = cleanVar($_POST, 'type', 'string', '');
		$name = cleanVar($_POST, 'name', 'string', '');
		$class = cleanVar($_POST, 'class', 'string', '');
		$label = cleanVar($_POST, 'label', 'string', '');
		$description = cleanVar($_POST, 'description', 'string', '');
		$role1 = cleanVar($_POST, 'role1', 'int', '');
		$role2 = cleanVar($_POST, 'role2', 'int', '');
		$role3 = cleanVar($_POST, 'role3', 'int', '');
		if(!$role2) $role2 = 5;
		if(!$role3) $role3 = 5;

		$link_error = $this->_home."?evt[$this->_className-manageModule]&action=$this->_act_insert";

		if($req_error > 0) 
			exit(error::errorMessage(array('error'=>1), $link_error));

		$query = "SELECT id FROM ".$this->_tbl_module." WHERE name='$name'";
		$a = $this->_db->selectquery($query);
		if(sizeof($a)>0) exit(error::errorMessage(array('error'=>_("il nome del modulo è già presente")), $link_error));

		if(preg_match("/[^\w]/", $name)) exit(error::errorMessage(array('error'=>_("il nome del modulo contiene caratteri non permessi")), $link_error));


		if($type=='class') {
			$classElements = call_user_func(array($class, 'getClassElements'));
			/*
			 * create css files
			 */
			$css_files = $classElements['css'];
			foreach($css_files as $css_file) {

				$css_content = file_get_contents(APP_DIR.OS.$class.OS.$css_file);

				$baseCssName = baseFileName($css_file);

				if(!($fo = @fopen(APP_DIR.OS.$class.OS.$baseCssName.'_'.$name.'.css', 'wb')))
					exit(error::errorMessage(array('error'=>_("impossibile creare i file di stile"), 'hint'=>_("controllare i permessi in scrittura")), $link_error));

				$reg_exp = "/#(.*?)".$class." /";
				$replace = "#$1".$class."_".$name." ";
				$content = preg_replace($reg_exp, $replace, $css_content);

				fwrite($fo, $content);
				fclose($fo);

			}
			/*
			 * create folder structure
			 */
			$folderStructure = (isset($classElements['folderStructure']))?$classElements['folderStructure']:array();
			if(count($folderStructure)) {
				foreach($folderStructure as $k=>$v) {
					mkdir($k.OS.$name);
					$this->createMdlFolders($k.OS.$name, $v);
				}
			}
		}

		$query = "INSERT INTO ".$this->_tbl_module." (label, name, class, type, role1, role2, role3, masquerade, role_group, description) VALUES ('$label', '$name', '$class', '$type', '$role1', '$role2', '$role3', 'no', 0, '$description')";
		$result = $this->_db->actionquery($query);

		EvtHandler::HttpCall($this->_home, $this->_className.'-manageModule', '');

	}

	private function createMdlFolders($pdir, $nsdir) {
	
		// if next structure is null break
		if(!$nsdir) return true;
		elseif(is_array($nsdir)) {
			foreach($nsdir as $k=>$v) {
				mkdir($pdir.OS.$k);
				$this->createMdlFolders($pdir.OS.$k, $v);
			}
		}
		else return true;
	}

	private function formEditModule($id) {

		$gform = new Form('gform', 'post', true, array("trnsl_table"=>$this->_tbl_module, "trnsl_id"=>$id));
		$gform->load('dataform');

		$query = "SELECT * FROM ".$this->_tbl_module." WHERE id='$id'";
		$a = $this->_db->selectquery($query);
		if(sizeof($a)>0) {
			$label = htmlInput($a[0]['label']);
			$label_txt = htmlChars($a[0]['label']);
			$description = htmlInput($a[0]['description']);
			$masquerade = htmlInput($a[0]['masquerade']);
			$active = ($masquerade=='no')?'yes':'no';
			$type = htmlChars($a[0]['type']);
			$class = htmlChars($a[0]['class']);
			$role1 = htmlInput($a[0]['role1']);
			$role2 = htmlInput($a[0]['role2']);
			$role3 = htmlInput($a[0]['role3']);
		}
		else exit(error::syserrorMessage("module", "formEditModule", "ID non associato ad alcun modulo", __LINE__));

		$role_list = $this->_access->listRole();
	
		$htmlsection = new htmlSection(array('class'=>'admin', 'headerTag'=>'h1', 'headerLabel'=>_("Modifica ")."'".$label_txt."'"));

		$required = 'label,role1';
		$GINO = $gform->form($this->_home."?evt[".$this->_className."-actionEditModule]", '', $required);
		$GINO .= $gform->hidden('id', $id);
		
		$GINO .= $gform->cinput('label', 'text', $gform->retvar('label', $label), _("Etichetta"), array("required"=>true, "size"=>40, "maxlength"=>200, "trnsl"=>true, "field"=>"label"));
		$GINO .= $gform->ctextarea('description', $gform->retvar('description', $description), _("Descrizione"), array("cols"=>45, "rows"=>4, "trnsl"=>true, "field"=>"description"));
		
		if($type=='class') {

			if(method_exists($class, 'outputFunctions')) {
				$GINO .= $gform->cradio('role1', $gform->retvar('role1', $role1), $role_list, '', _("Permessi di visualizzazione"), array("required"=>true, "aspect"=>"v"));
			}

			// Metodi aggiuntivi
			if(method_exists($class, 'permission'))
			{
				$classObj = new $class($id);
				$permission = $classObj->permission();
			
				if(!empty($permission[0]))
				{
					$GINO .= $gform->cradio('role2', $gform->retvar('role2', $role2), $role_list, '', $permission[0], array("required"=>true, "aspect"=>"v"));
				}
				if(!empty($permission[1]))
				{
					$GINO .= $gform->cradio('role3', $gform->retvar('role3', $role3), $role_list, '', $permission[1], array("required"=>true, "aspect"=>"v"));
				}
			}
		}
		else {
			$GINO .= $gform->cradio('role1', $gform->retvar('role1', $role1), $role_list, '', _("Permessi di visualizzazione"), array("required"=>true, "aspect"=>"v"));
		}

		$GINO .= $gform->cinput('submit_action', 'submit', _("modifica"), '', array("classField"=>"submit"));
		$GINO .= $gform->cform();
		
		$htmlsection->content = $GINO;

		$buffer = $htmlsection->render();
			
		$buffer .= $this->formActivateModule($id, $active);

		return $buffer;

	}
	
	private function formActivateModule($id, $active) {
		
		$gform = new Form('gform', 'post', true);
		$gform->load('dataform');

		$htmlsection = new htmlSection(array('class'=>'admin', 'headerTag'=>'h1', 'headerLabel'=>_("Attivazione")));
		
		$required = '';
		$GINO = $gform->form($this->_home."?evt[".$this->_className."-actionEditModuleActive]", '', $required);
		$GINO .= $gform->hidden('id', $id);
		$GINO .= $gform->cradio('active', $active, array("yes"=>_("si"),"no"=>_("no")), 'no', array(_("Attivo"), _("Assicurarsi di eliminare dai template i moduli che si vogliono disattivare")), array("required"=>false));
		$GINO .= $gform->cinput('submit_action', 'submit', _("modifica"), '', array("classField"=>"submit"));
		$GINO .= $gform->cform();

		$htmlsection->content = $GINO;

		return $htmlsection->render();

	}

	public function actionEditModule() {

		$this->accessType($this->_access_2);

		$gform = new Form('gform', 'post', true);
		$gform->save('dataform');
		$req_error = $gform->arequired();

		$id = cleanVar($_POST, 'id', 'int', '');
		$label = cleanVar($_POST, 'label', 'string', '');
		$description = cleanVar($_POST, 'description', 'string', '');
		$role1 = cleanVar($_POST, 'role1', 'int', '');
		$role2 = cleanVar($_POST, 'role2', 'int', '');
		$role3 = cleanVar($_POST, 'role3', 'int', '');

		$link_error = $this->_home."?evt[$this->_className-manageModule]&id=$id&action=$this->_act_modify";

		if($req_error > 0) 
			exit(error::errorMessage(array('error'=>1), $link_error));

		$query = "UPDATE ".$this->_tbl_module." SET label='$label', description='$description'";
		if(isset($_POST['role1'])) $query .= ", role1='$role1'";
		if(isset($_POST['role'])) $query .= ", role2='$role2'";
		if(isset($_POST['role'])) $query .= ", role3='$role3'";
		$query .= " WHERE id='$id'";
		$result = $this->_db->actionquery($query);
		
		EvtHandler::HttpCall($this->_home, $this->_className.'-manageModule', '');

	}

	public function actionEditModuleActive() {

		$this->accessType($this->_access_2);
		$id = cleanVar($_POST, 'id', 'int', '');
		$active = cleanVar($_POST, 'active', 'string', '');
		$masquerade = ($active=='no')?'yes':'no';

		$query = "UPDATE ".$this->_tbl_module." SET masquerade='$masquerade' WHERE id='$id'";
		$result = $this->_db->actionquery($query);
		
		EvtHandler::HttpCall($this->_home, $this->_className.'-manageModule', '');


	}

}
?>
