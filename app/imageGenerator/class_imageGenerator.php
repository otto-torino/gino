<?php

require_once(APP_DIR.OS."imageGenerator".OS."abiPaint".OS."class_abiPaint.php");

class imageGenerator extends AbstractEvtClass {

	protected $_instance, $_instanceName;
	private $_tbl_image;

	private $_action, $_block;

	function __construct() {

		parent::__construct();

		$this->_instance = 0;
		$this->_instanceName = $this->_className;

		$this->setAccess();

		$this->_tbl_image = 'sys_image';

		$this->_action = cleanVar($_REQUEST, 'action', 'string', '');
		$this->_block = cleanVar($_REQUEST, 'block', 'string', 'skin');

	}

	public function manageImageGenerator() {
		
		$this->accessGroup('ALL');

		$htmltab = new htmlTab(array("linkPosition"=>'right', "title"=>_("Generatore di immagini")));	
		$link_admin = "<a href=\"".$this->_home."?evt[$this->_instanceName-manageImageGenerator]&block=permissions\">"._("Permessi")."</a>";
		$link_dft = "<a href=\"".$this->_home."?evt[".$this->_instanceName."-manageImageGenerator]\">"._("Gestione")."</a>";
		$sel_link = $link_dft;

		// Variables
		$id = cleanVar($_GET, 'id', 'int', '');
		// end

		if($this->_block == 'permissions') {
			$GINO = sysfunc::managePermissions($this->_instance, $this->_className);		
			$sel_link = $link_admin;
		}
		else {
			if($this->_action == $this->_act_modify OR $this->_action == $this->_act_insert)
				$form = $this->formImage($id);
			elseif($this->_action == $this->_act_delete)
				$form = $this->formDelImage($id);
			else
				$form = $this->info();

			$GINO = "<div class=\"vertical_1\">\n";
			$GINO .= $this->listImages($id);
			$GINO .= "</div>\n";

			$GINO .= "<div class=\"vertical_2\">\n";
			$GINO .= $form;
			$GINO .= "</div>\n";

			$GINO .= "<div class=\"null\"></div>";
		}

		$htmltab->navigationLinks = array($link_admin, $link_dft);
		$htmltab->selectedLink = $sel_link;
		$htmltab->htmlContent = $GINO;
		return $htmltab->render();

	}
	
	private function listImages($sel_id) {
		
		$link_insert = "<a href=\"$this->_home?evt[$this->_instanceName-manageImageGenerator]&action=$this->_act_insert\">".pub::icon('insert')."</a>";
		$htmlsection = new htmlSection(array('class'=>'admin', 'headerTag'=>'header', 'headerLabel'=>_("Elenco"), 'headerLinks'=>$link_insert));

		$query = "SELECT id, name FROM ".$this->_tbl_image." ORDER BY name";
		$a = $this->_db->selectquery($query);
		if(sizeof($a) > 0)
		{
			$htmlList = new htmlList(array("numItems"=>sizeof($a), "separator"=>true));
			$GINO = $htmlList->start();
			foreach($a AS $b)
			{
				$id = htmlChars($b['id']);
				$name = htmlChars($b['name']);
				
				$selected = ($id == $sel_id)?true:false;
				
				$link_modify = "<a href=\"index.php?evt[".$this->_instanceName."-manageImageGenerator]&amp;id=$id&amp;&amp;action=".$this->_act_modify."\">".$this->icon('modify', '')."</a>";
				$link_delete = "<a href=\"index.php?evt[".$this->_instanceName."-manageImageGenerator]&amp;id=$id&amp;&amp;action=".$this->_act_delete."\">".$this->icon('delete', '')."</a>";
				$html = _("Path dell'immagine: ").CONTENT_WWW."/imageGenerator/".$name.".png";
				$link_lnk = " <a onclick=\"window.myWin = new layerWindow({'title':'"._("Link alla risorsa")."', 'html':'".jsVar($html)."', 'bodyId':'link$id', 'width':500});myWin.display($(this));\">".$this->icon('link', '')."</a>";
				$html2 = "<img align=\"center\" src=\"".CONTENT_WWW."/imageGenerator/$name.png\" alt=\"$name\" />";
				$link_view = " <a onclick=\"window.myWin2 = new layerWindow({'title':'"._("Preview")."', 'html':'".jsVar($html2)."', 'bodyId':'link$id', 'width':580});myWin2.display();\">".$this->icon('view', '')."</a>";
				
				$GINO .= $htmlList->item($name, array($link_lnk, $link_view, $link_modify, $link_delete), $selected, true);

			}
			$GINO .= $htmlList->end();
		}
		else {
			$GINO = "<p>"._("non risultano immagini registrate")."</p>\n";
		}
		
		$htmlsection->content = $GINO;
		
		return $htmlsection->render();

	}

	private function formImage($id) {

		$gform = new Form('imageform', 'post', true);
		$gform->load('dataform');

		$query = "SELECT * FROM ".$this->_tbl_image." WHERE id='$id'";
		$a = $this->_db->selectquery($query);
		if(sizeof($a)>0) {
			$name = htmlInput($a[0]['name']);
			$description = htmlInput($a[0]['description']);
			$image_src = CONTENT_WWW."/imageGenerator".OS.$name.".png";
		}
		else {
			$name = $gform->retvar('name', '');
			$description = $gform->retvar('description', '');
			$image_src = null;
		}

		$title = $id? _("Modifica immagine"):_("Nuova immagine");
		$submit = $id? _("modifica"):_("inserisci");

		$url = SITE_WWW."/extra/colorPalette.html";
		$pick_color = "<span class=\"link\" onclick=\"window.myWin = new layerWindow({'title':'"._("Paletta colori")."', 'url':'$url', 'bodyId':'palette', 'width':500, 'height':300});window.myWin.display();\">".pub::icon('palette')."</span>";		

		$htmlsection = new htmlSection(array('class'=>'admin', 'headerTag'=>'h1', 'headerLabel'=>$title, 'headerLinks'=>$pick_color));
		
		$ap = new abiPaint();

		$buffer = $ap->render($image_src);

		$buffer .= "<div style=\"margin-top:10px;\">";
		$required = 'name';
		$buffer .= $gform->form($this->_home."?evt[".$this->_className."-actionImage]", '', $required);
		$buffer .= $gform->hidden('id', $id);
		$buffer .= $gform->hidden('imageCode', '', array("id"=>"imageCode"));

		$buffer .= $gform->cinput('name', 'text', $name, _("Nome"), array("required"=>true, "size"=>20, "maxlength"=>200));
		$buffer .= $gform->ctextarea('description', $description, _("Descrizione"), array("cols"=>40, "rows"=>4,
			"trnsl"=>true, "trnsl_table"=>$this->_tbl_image, "field"=>"description", "trnsl_id"=>$id));

		$buffer .= $gform->cinput('submit_action', 'button', $submit, '', array("classField"=>"submit", "js"=>"onclick=\"$('imageCode').value=$('canvasArea').toDataURL();document.imageform.submit();\""));

		$buffer .= $gform->cform();
		$buffer .= "</div>";
		
		$htmlsection->content = $buffer;

		return $htmlsection->render();
	}

	private function formDelImage($id) {
		
		$gform = new Form('gform', 'post', false);
		
		$htmlsection = new htmlSection(array('class'=>'admin', 'headerTag'=>'h1', 'headerLabel'=>_("Elimina"), 'headerLinks'=>$this->_link_return));
		
		$GINO = $gform->form($this->_home."?evt[".$this->_className."-actionDelImage]", '', '');
		$GINO .= $gform->hidden('id', $id);
		$GINO .= $gform->cinput('delete_action', 'submit', _("elimina"), array(_("Attenzione!"), _("l'eliminazione è definitiva")), array("classField"=>"submit"));
		$GINO .= $gform->cform();
		
		$htmlsection->content = $GINO;

		return $htmlsection->render();

	}

	private function info(){

		$htmlsection = new htmlSection(array('class'=>'admin', 'headerTag'=>'h1', 'headerLabel'=>_("Informazioni")));
		$buffer = "<p>"._("Tool per la creazione di immagini a bassa definizione. Le immagini create vengono salvate su filesystem e possono essere utilizzate come normali risorse all'interno del sito.")."</p>\n";
		
		$htmlsection->content = $buffer;

		return $htmlsection->render();
	}

	public function saveImage() {
	
		$this->accessGroup('');
		$ap = new abiPaint();
		$ap->saveImage();
	}

	public function actionImage() {

		$this->accessGroup('');

		$id = cleanVar($_POST, 'id', 'int', '');
		$name = cleanVar($_POST, 'name', 'string', '');
		$description = cleanVar($_POST, 'description', 'string', '');

		$name = preg_replace("#[^\w\d]#", "_", $name);

		$ap = new abiPaint();
		$ap->saveImage($name);

		$query = $id
			? "UPDATE ".$this->_tbl_image." SET name='$name', description='$description' WHERE id='$id'"
			: "INSERT INTO ".$this->_tbl_image." (name, description) VALUES ('$name', '$description')";
		$result = $this->_db->actionquery($query);

		EvtHandler::HttpCall($this->_home, $this->_instanceName.'-manageImageGenerator', '');

	}

	public function actionDelImage() {
	
		$this->accessGroup('');

		$id = cleanVar($_POST, 'id', 'int', '');

		$name = $this->_db->getFieldFromId($this->_tbl_image,'name','id',$id);

		if(@unlink(CONTENT_DIR.OS."imageGenerator".OS.$name.".png")) {
			$query = "DELETE FROM ".$this->_tbl_image." WHERE id='$id'";
			$result = $this->_db->actionquery($query);

			EvtHandler::HttpCall($this->_home, $this->_instanceName.'-manageImageGenerator', '');
		}
		else
			exit(error::errorMessage(array('error'=>_("Impossibile eliminare il file")), $this->_home."?evt[$this->_className-manageImageGenerator]"));

	}


}

?>
