<?php

class css extends propertyObject {

	private static $_tbl_css = 'sys_layout_css';
	protected $_tbl_data;
	private $_class, $_module, $_name, $_label, $_css_list;
	private $_mdlLink;
	private $_home, $_interface;
	private $_tbl_module;

	function __construct($type, $params) {
		
		$db = db::instance();
		if($type=='module') {
			$this->_class = $params['class'];
			$this->_module = $params['module'];
			$this->_name = $params['name'];
			$this->_label = $params['label'];
			$classElements = call_user_func(array($this->_class, 'getClassElements'));
			$this->_css_list = $classElements['css'];
			$this->_mdlLink = HOME_FILE."?evt[$this->_name-manageDoc]&block=css";
		}
		elseif($type=='layout') {
			$id = $params['id'];
			$this->_tbl_data = self::$_tbl_css;
			parent::__construct($this->initP($id));

			$this->_home = 'index.php';
			$this->_interface = 'layout';
		}
	}
	
	/*
	 * MANAGE MODULES' CSS
	 */
	public function manageModuleCss() {

		$action = cleanVar($_GET, 'action', 'string', '');

		$buffer = "<div class=\"vertical_1\">\n";
		$buffer .= $this->moduleCssList();
		$buffer .= "</div>\n";

		$buffer .= "<div class=\"vertical_2\">\n";
		if($action=='modify') $buffer .= $this->formModuleCssFile();
		elseif($action=='save') $buffer .= $this->actionModuleCssFile();
		else $buffer .= $this->moduleInfo();
		$buffer .= "</div>\n";

		$buffer .= "<div class=\"null\"></div>\n";

		return $buffer;
	}

	private function moduleCssList() {
	
		$key = isset($_GET['key'])? (int) $_GET['key']:'';
		if($key==='') $key = null;
		else $key = cleanVar($_GET, 'key', 'int', '');

		$htmlsection = new htmlSection(array('class'=>'admin', 'headerTag'=>'h1', 'headerLabel'=>_("Elenco")));
		
		if(count($this->_css_list)) {
			$htmlList = new htmlList(array("numItems"=>count($this->_css_list), "separator"=>true));
			$buffer = $htmlList->start();
			foreach($this->_css_list as $k=>$css_file) {
				$selected = ($key === $k)?true:false;
				$link_modify = "<a href=\"$this->_mdlLink&key=$k&action=modify\">".pub::icon('modify')."</a>";
				$buffer .= $htmlList->item(baseFileName($css_file)."_".$this->_name.".css", $link_modify, $selected);
			}	
			$buffer .= $htmlList->end();
		}
		else
			$buffer = "<p>"._("Non risultano css per il modulo ").htmlChars($this->_label)."</p>\n";

		$htmlsection->content = $buffer;
		return $htmlsection->render();
	}

	private function formModuleCssFile() {

		$gform = new Form('gform', 'post', true, array("tblLayout"=>false));
		$gform->load('dataform');

		$key = cleanVar($_GET, 'key', 'int', '');

		$filename = baseFileName($this->_css_list[$key])."_".$this->_name.".css";
		
		$htmlsection = new htmlSection(array('class'=>'admin', 'headerTag'=>'h1', 'headerLabel'=>_("Modifica CSS")." - ".$filename));

		$required = '';
		$buffer = $gform->form($this->_mdlLink."&action=save&key=$key", '', $required);

		$css_contents = file_get_contents(APP_DIR.OS.$this->_class.OS.$filename);
		$buffer .= "<textarea name=\"file_content\" style=\"width:98%;height:300px;overflow:auto;border:2px solid #000;\">".$css_contents."</textarea>\n";
		
		$buffer .= "<p>".$gform->input('submit_action', 'submit', _("salva"), array("classField"=>"submit"));
		$buffer .= " ".$gform->input('cancel_action', 'button', _("annulla"), array("js"=>"onclick=\"location.href='$this->_mdlLink'\" class=\"generic\""))."</p>";

		$buffer .= $gform->cform();

		$htmlsection->content = $buffer;

		return $htmlsection->render();
	}

	private function actionModuleCssFile() {
	
		$key = cleanVar($_GET, 'key', 'int', '');
		$filename = baseFileName($this->_css_list[$key])."_".$this->_name.".css";

		$file_content = $_POST['file_content'];
		$fo = fopen(APP_DIR.OS.$this->_class.OS.$filename, 'wb');
		fwrite($fo, $file_content);
		fclose($fo);

		header("Location: ".$this->_mdlLink);
	}

	private function moduleInfo() {
		
		$htmlsection = new htmlSection(array('class'=>'admin', 'headerTag'=>'h1', 'headerLabel'=>_("Informazioni")));
		$buffer = "<p>"._("Selezionare uno dei file elencati a lato per entrare nella modalità di modifica.")."</p>\n";
		
		$htmlsection->content = $buffer;

		return $htmlsection->render();
	}
	
	/*
	 * MANAGE LAYOUT CSS
	 */

	private function initP($id) {
	
		$db = db::instance();
		$query = "SELECT * FROM ".$this->_tbl_data." WHERE id='$id'";
		$a = $db->selectquery($query);
		if(sizeof($a)>0) return $a[0]; 
		else return array('id'=>null, 'filename'=>null, 'label'=>null, 'description'=>null);
	}
	
	public function setFilename($value) {
		
		if($this->_p['filename']!=$value && !in_array('filename', $this->_chgP)) $this->_chgP[] = 'filename';
		$this->_p['filename'] = $value;
		return true;
	}

	public static function getAll($order='label') {

		$db = db::instance();
		$res = array();
		$query = "SELECT id, label, filename, description FROM ".self::$_tbl_css." ORDER BY $order";
		$a = $db->selectquery($query);
		if(sizeof($a)>0) {
			foreach($a as $b) {
				$res[] = new css('layout', array('id'=>$b['id']));
			}
		}

		return $res;
	}

	public function formCssLayout() {
	
		$gform = new Form('gform', 'post', true, array("trnsl_table"=>$this->_tbl_data, "trnsl_id"=>$this->id));
		$gform->load('dataform');

		$action = ($this->id)?'modify':'insert';
		$title = ($this->id)?_("Modifica ").htmlChars($this->label):_("Nuovo css");
		$htmlsection = new htmlSection(array('class'=>'admin', 'headerTag'=>'h1', 'headerLabel'=>$title));

		$required = 'label';
		$buffer = $gform->form($this->_home."?evt[layout-actionCss]", true, $required);
		$buffer .= $gform->hidden('id', $this->id);
		$buffer .= $gform->hidden('old_filename', $this->filename);
		
		$buffer .= $gform->cfile('filename', $this->filename, _("File"), array("required"=>true, "extensions"=>array("css"), "del_check"=>true));
		$buffer .= $gform->cinput('label', 'text', $gform->retvar('label', htmlInput($this->label)), _("Etichetta"), array("required"=>true, "size"=>40, "maxlength"=>200, "trnsl"=>true, "field"=>"label"));
		$buffer .= $gform->ctextarea('description', $gform->retvar('description', htmlInput($this->description)), _("Descrizione"), array("cols"=>45, "rows"=>4, "trnsl"=>true, "field"=>"description"));

		$buffer .= $gform->cinput('submit_action', 'submit', (($this->id)?_("modifica"):_("inserisci")), '', array("classField"=>"submit"));
		$buffer .= $gform->cform();

		$htmlsection->content = $buffer;

		return $htmlsection->render();
	}

	public function actionCssLayout() {
		
		$gform = new Form('gform', 'post', true);
		$gform->save('dataform');
		$req_error = $gform->arequired();

		$action = ($this->id)?'modify':'insert';
		$link_error = $this->_home."?evt[$this->_interface-manageLayout]&block=css&id=$this->id&action=$action";

		if($req_error > 0) 
			exit(error::errorMessage(array('error'=>1), $link_error));

		$filename_tmp = $_FILES['filename']['tmp_name'];
		$old_filename = cleanVar($_POST, 'old_filename', 'string', '');
		
		$directory = CSS_DIR.OS;
		$redirect = $this->_interface.'-manageLayout';
		$link = "block=css";
		$link .= ($this->id)?"&action=modify&id=$this->id":"&action=insert";
		
		foreach($_POST as $k=>$v) {
			$this->{$k} = $k;
		}
		$this->updateDbData();

		$gform->manageFile('filename', $old_filename, false, array('css'), $directory, $link_error, $this->_tbl_data, 'filename', 'id', $this->id, array("check_type"=>true, "types_allowed"=>array("text/css")));

		header("Location: $this->_home?evt[$this->_interface-manageLayout]&block=css");
	}
	
	public function formDelCssLayout() {
	
		$gform = new Form('gform', 'post', true);
		$gform->load('dataform');

		$id = cleanVar($_GET, 'id', 'int', '');

		$htmlsection = new htmlSection(array('class'=>'admin', 'headerTag'=>'h1', 'headerLabel'=>_("Elimina css")));

		$required = '';
		$buffer = $gform->form($this->_home."?evt[layout-actionDelCss]", '', $required);
		$buffer .= $gform->hidden('id', $this->id);
		$buffer .= $gform->cinput('submit_action', 'submit', _("elimina"), array(_("Attenzione!"), _("l'eliminazione determina l'eliminazione del file css dalle skin che lo contengono!")), array("classField"=>"submit"));
		$buffer .= $gform->cform();

		$htmlsection->content = $buffer;

		return $htmlsection->render();
	}
	
	public function actionDelCssLayout() {
		
		if($this->filename) @unlink(CSS_DIR.OS.$this->filename);		

		skin::removeCss($this->id);

		language::deleteTranslations($this->_tbl_data, $this->id);
		$this->deleteDbData();

		header("Location: $this->_home?evt[$this->_interface-manageLayout]&block=css");
	}

	public static function layoutInfo() {
		
		$htmlsection = new htmlSection(array('class'=>'admin', 'headerTag'=>'h1', 'headerLabel'=>_("Informazioni css")));
		$buffer = "<p><b>"._("Indicazioni")."</b></p>\n";
		$buffer .= "<p>"._("Upload di fogli di stile da associare eventualmente ad una skin. Il css viene accodato ai file di default di Gino CMS, pertanto è possibile definire nuovi stili o sovrascrivere quelli già presenti.")."</p>\n";
		
		$htmlsection->content = $buffer;

		return $htmlsection->render();
	}
}
?>
