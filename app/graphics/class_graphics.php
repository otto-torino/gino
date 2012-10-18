<?php
/**
 * @file class_graphics.php
 * @brief Contiene la classe graphics
 * 
 * @copyright 2005 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */

/**
 * @brief Gestione personalizzata degli header e footer del sistema
 * 
 * Sono disponibili 5 header e 5 footer completamente personalizzabili ed utilizzabili nella composizione del layout.
 * Un header/footer può essere di due tipologie:
 *   - grafica, prevede il caricamento di una immagine
 *   - codice, prevede l'inserimento di codice html
 * 
 * @copyright 2005 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class graphics extends AbstractEvtClass{

	protected $_instance, $_instanceName;

	private $_title;
	
	private $_group_1;
	
	private $_fck_toolbar, $_fck_width, $fck_height;

	private $_extension_img;
	
	private $_num_methods;

	private $_tbl_graphics;
	private $_header_function, $_footer_function;

	private $_block;
	
	function __construct(){
		
		parent::__construct();
		
		$this->_instance = 0;
		$this->_instanceName = $this->_className;
		$this->_title = _("Layout - header/footer");
		
		$this->setAccess();
		$this->setGroups();
		
		$this->_fck_toolbar = 'Basic';
		$this->_fck_width = '95%';
		$this->_fck_height = '300';

		$this->_extension_img = array("jpg", "png", "gif");
		
		$this->_tbl_graphics = 'sys_graphics';

		$this->_num_methods = 10;
		
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
	 * Elenco dei metodi che possono essere richiamati dal menu e dal template
	 * 
	 * @return array
	 */
	public static function outputFunctions() {

		$list = array(
			"printHeaderPublic" => array("label"=>_("Header Pubblico"), "role"=>'1'),
			"printHeaderPrivate" => array("label"=>_("Header Privato"), "role"=>'1'),
			"printHeaderAdmin" => array("label"=>_("Header Amministrazione"), "role"=>'1'),
			"printHeaderMobile" => array("label"=>_("Header Dispositivo Mobile"), "role"=>'1'),
			"printHeaderAdhoc" => array("label"=>_("Header Adhoc"), "role"=>'1'),
			"printFooterPublic" => array("label"=>_("Footer Pubblico"), "role"=>'1'),
			"printFooterPrivate" => array("label"=>_("Footer Privato"), "role"=>'1'),
			"printFooterAdmin" => array("label"=>_("Footer Amministrazione"), "role"=>'1'),
			"printFooterMobile" => array("label"=>_("Footer Dispositivo Mobile"), "role"=>'1'),
			"printFooterAdhoc" => array("label"=>_("Footer Adhoc"), "role"=>'1')
		);

		return $list;
	}

	private function isHeader($id) {
		
		return $id<6 ? true : false;
	}

	/**
	 * Imposta dei codici di sostituzione da utilizzare con un header/footer di tipo @a codice
	 * 
	 * @return array
	 */
	private function setReplaceHtml(){
		
		$lng = new language();
		$language = $lng->choiceLanguage(true);
		
		$array = array(
			'_GRAPHICS_'	=>	$this->_graphics_www,
			'_HOMEPAGE_'	=>	$this->_home,
			'_HOME_'		=>	$this->_site_www,
			'_LANGUAGE_'	=>	$language
		);
		
		return $array;
	}
	
	private function replaceHtml($html){
		
		if(!empty($html))
		{
			$substitution = $this->setReplaceHtml();
			
			if(sizeof($substitution) > 0)
			{
				foreach ($substitution AS $key=>$value)
				{
					$html = preg_replace("/$key/", $value, $html);
				}
			}
		}
		
		return $html;
	}
	
	/**
	 * Interfaccia all'header con valore ID 1
	 * 
	 * @see $_access_base
	 * @return string
	 */
	public function printHeaderPublic() {

		$this->accessType($this->_access_base);

		return $this->render(1);
	}
	
	/**
	 * Interfaccia all'header con valore ID 2
	 * 
	 * @see $_access_base
	 * @return string
	 */
	public function printHeaderPrivate() {

		$this->accessType($this->_access_base);

		return $this->render(2);
	}
	
	/**
	 * Interfaccia all'header con valore ID 3
	 * 
	 * @see $_access_base
	 * @return string
	 */
	public function printHeaderAdmin() {

		$this->accessType($this->_access_base);

		return $this->render(3);
	}
	
	/**
	 * Interfaccia all'header con valore ID 4
	 * 
	 * @see $_access_base
	 * @return string
	 */
	public function printHeaderMobile() {

		$this->accessType($this->_access_base);

		return $this->render(4);
	}

	/**
	 * Interfaccia all'header con valore ID 5
	 * 
	 * @see $_access_base
	 * @return string
	 */
	public function printHeaderAdhoc() {

		$this->accessType($this->_access_base);

		return $this->render(5);
	}

	/**
	 * Interfaccia al footer con valore ID 6
	 * 
	 * @see $_access_base
	 * @return string
	 */
	public function printFooterPublic() {

		$this->accessType($this->_access_base);

		return $this->render('6');
	}
	
	/**
	 * Interfaccia al footer con valore ID 7
	 * 
	 * @see $_access_base
	 * @return string
	 */
	public function printFooterPrivate() {

		$this->accessType($this->_access_base);

		return $this->render('7');
	}
	
	/**
	 * Interfaccia al footer con valore ID 8
	 * 
	 * @see $_access_base
	 * @return string
	 */
	public function printFooterAdmin() {

		$this->accessType($this->_access_base);

		return $this->render('8');
	}

	/**
	 * Interfaccia al footer con valore ID 9
	 * 
	 * @see $_access_base
	 * @return string
	 */
	public function printFooterMobile() {

		$this->accessType($this->_access_base);

		return $this->render('9');
	}

	/**
	 * Interfaccia al footer con valore ID 10
	 * 
	 * @see $_access_base
	 * @return string
	 */
	public function printFooterAdhoc() {

		$this->accessType($this->_access_base);

		return $this->render('10');
	}

	/**
	 * Prepara l'header/footer
	 * 
	 * @param integer $id valore ID del record
	 * @return string
	 */
	private function render($id) {
	
		if(!$id) return '';
		$buffer = "<section id=\"site_".($this->isHeader($id) ? "header" : "footer")."\" class=\"public\">\n";
		
		$query = "SELECT type, html, image FROM ".$this->_tbl_graphics." WHERE id='$id'";
		$a = $this->_db->selectquery($query);
		if(sizeof($a) > 0)
		{
			foreach ($a AS $b)
			{
				$type = $b['type'];
				$html = $b['html'];
				$html = $this->replaceHtml($html);
				$image = $b['image'];
			}
		}

		if($type==1 && $image) 
		{
			$src = $this->_graphics_www."/$image";
			if($this->isHeader($id))
			{
				$buffer .= "<a href=\"".$this->_home."\"><img src=\"$src\" alt=\""._("header")."\" /></a>\n";
			}
			else
			{
				$buffer .= "<img src=\"$src\" alt=\""._("footer")."\" />\n";
			}
		}
		elseif($type==2) 
			$buffer .= $html;

		$buffer .= "</section>";

		return $buffer;
	}
	
	/**
	 * Interfaccia amministrativa per la gestione di header e footer
	 * 
	 * @return string
	 */
	public function manageGraphics(){

		$this->accessGroup('ALL');
		
		$htmltab = new htmlTab(array("linkPosition"=>'right', "title"=>$this->_title));	
		$link_admin = "<a href=\"".$this->_home."?evt[".$this->_className."-manageGraphics]&block=permissions\">"._("Permessi")."</a>";
		$link_dft = "<a href=\"".$this->_home."?evt[".$this->_className."-manageGraphics]\">"._("Gestione")."</a>";
		$sel_link = $link_dft;

		if($this->_block == 'permissions' && $this->_access->AccessVerifyGroupIf($this->_className, $this->_instance, '', '')) {
			$GINO = sysfunc::managePermissions(null, $this->_className); 
			$sel_link = $link_admin;
		}
		else {
			// Variables
			$id = cleanVar($_GET, 'id', 'int', '');
			$action = cleanVar($_GET, 'action', 'string', '');
			// end

			if($action == $this->_act_modify OR $action == $this->_act_insert)
				$form = $this->formDoc($id, $action);
			else
				$form = $this->infoDoc();

			$GINO = "<div class=\"vertical_1\">\n";
			$GINO .= $this->listDoc($id);
			$GINO .= "</div>\n";

			$GINO .= "<div class=\"vertical_2\">\n";
			$GINO .= $form;
			$GINO .= "</div>\n";

			$GINO .= "<div class=\"null\"></div>";
		}

		$htmltab->navigationLinks = $this->_access->AccessVerifyGroupIf($this->_className, $this->_instance, '', '')
			? array($link_admin, $link_dft)
			: array($link_dft);
		$htmltab->selectedLink = $sel_link;
		$htmltab->htmlContent = $GINO;
		return $htmltab->render();
	}
	
	private function infoDoc(){

		$htmlsection = new htmlSection(array('class'=>'admin', 'headerTag'=>'h1', 'headerLabel'=>_("Informazioni")));
		$GINO = "<p>"._("Sono disponibili 5 header e 5 footer completamente personalizzabili ed utilizzabili nella composizione del layout. Ciascuno di essi può essere di tipo grafico, cioè un'immagine (jpg, png o gif) oppure generato attraverso del codice html.")."</p>";
		
		$htmlsection->content = $GINO;

		return $htmlsection->render();
	}
	
	/**
	 * Preview dell'header/footer
	 * 
	 * @return string
	 */
	public function preview(){

		$this->accessGroup('ALL');

		$id = cleanVar($_GET, 'id', 'int', '');

		$GINO = $this->render($id);

		return $GINO;
	}
	
	private function listDoc($select_doc){
		
		$htmlsection = new htmlSection(array('class'=>'admin', 'headerTag'=>'header', 'headerLabel'=>$this->_title));

		$htmlList = new htmlList(array("numItems"=>$this->_num_methods, "separator"=>true));
		$GINO = $htmlList->start();

		for($i=1; $i<$this->_num_methods+1; $i++) {
				
			$query = "SELECT id, name, description, type FROM ".$this->_tbl_graphics." WHERE id='$i'";
			$a = $this->_db->selectquery($query);
			if(sizeof($a) > 0)
			{
				foreach($a AS $b)
				{
					$id = $b['id'];
					$method = htmlChars($b['name']);
					$description = htmlChars($this->_trd->selectTXT($this->_tbl_graphics, 'description', $id));
					$type = htmlChars($b['type']);
				
					$link_edit = "<a href=\"".$this->_home."?evt[".$this->_className."-manageGraphics]&amp;id=$id&amp;action=".$this->_act_modify."\">".$this->icon('modify', '')."</a>";
					$url = "$this->_home?pt[".$this->_className."-preview]&amp;id=$id";
					$onclick = "window.myWin = new layerWindow({'title':'"._("Preview")."', 'url':'$url', 'bodyId':'prev', 'width':900});window.myWin.display();";
					$link_preview = "<span class=\"link\" onclick=\"$onclick\">".$this->icon('view', '')."</span>";
					
				
					$selected = ($id == $select_doc)?true:false;
					
					if($type == '1') $default_text = _("grafica");
					else $default_text = _("codice");
					
					$GINO .= $htmlList->item("$description<br/><span class=\"little\">$default_text</span>", array($link_preview, $link_edit), $selected, true);
				}
			}
		}

		$GINO .= $htmlList->end();
		
		$htmlsection->content = $GINO;
		
		return $htmlsection->render();
	}
	
	/**
	 * Form di modifica header/footer
	 * 
	 * @param integer $id valore ID del record
	 * @param string $action azione da eseguire
	 * @return string
	 */
	private function formDoc($id, $action){
	
		$gform = new Form('gform', 'post', true);
		$gform->load('dataform');
		
		if(!empty($id) AND $action == $this->_act_modify)
		{
			$query = "SELECT description, type, html, image FROM ".$this->_tbl_graphics." WHERE id='$id'";
			$a = $this->_db->selectquery($query);
			if(sizeof($a) > 0)
			{
				foreach($a AS $b)
				{
					$description = htmlInput($b['description']);
					$type = htmlInput($b['type']);
				}

				$title_form = _("Modifica")." '$description'";
				$submit = _("modifica");
				$required = 'description,type';
			}
		}
		else
		{
			exit();
		}
		
		$htmlsection = new htmlSection(array('class'=>'admin', 'headerTag'=>'h1', 'headerLabel'=>$title_form));
		
		$GINO = $gform->form($this->_home."?evt[".$this->_className."-actionDoc]", true, $required);
		$GINO .= $gform->hidden('id', $id, array("id"=>"id"));
		$GINO .= $gform->hidden('action', $action, array("id"=>"action"));
		$GINO .= $gform->cinput('description', 'text', $description, _("Descrizione"), array("required"=>true, "size"=>40, "maxlength"=>100, "trnsl"=>true, "trnsl_table"=>$this->_tbl_graphics, "field"=>"description", "trnsl_id"=>$id));
		$GINO .= $gform->cradio('type', $type, array("1"=>_("grafica"),"2"=>_("codice")), 1, _("Tipologia"), array("id"=>"type", "required"=>true, "js"=>"onchange=\"ajaxRequest('post', '$this->_home?pt[$this->_className-formType]', 'id=$id&type='+$(this).value, 'type_form', {'load':'type_form'})\""));

		$GINO .= $gform->cell($this->formType($id), array("id"=>"type_form"));

		$GINO .= $gform->cform();
		
		$htmlsection->content = $GINO;
		
		return $htmlsection->render();
	}

	/**
	 * Nel form di modifica di header/footer, in base alla tipologia scelta, mostra un input file o un textarea per il codice html
	 * 
	 * @see $_group_1
	 * @param integer $id valore ID del record
	 * @return string
	 */
	public function formType($id=null) {
	
		$this->accessGroup($this->_group_1);

		if(is_null($id)) {
			$id = cleanVar($_POST, 'id', 'int', '');
			$type = cleanVar($_POST, 'type', 'int', '');
		}
		else {
			$type = $this->_db->getFieldFromId($this->_tbl_graphics, 'type', 'id', $id);
		}
		
		$query = "SELECT html, image FROM ".$this->_tbl_graphics." WHERE id='$id'";
		$a = $this->_db->selectquery($query);
		if(sizeof($a) > 0)
		{
			foreach($a AS $b)
			{
				$html = htmlInput($b['html']);
				$image = htmlInput($b['image']);
			}
		}

		$gform = new Form('gform', 'post', true);
		$gform->load('dataform');

		$GINO = $gform->startTable();

		if($type==1) {
			$GINO .= $gform->cfile('image', $image, _("Immagine"), array("extensions"=>$this->_extension_img, "del_check"=>true, "preview"=>true, "previewSrc"=>$this->_graphics_www."/".$image));
		}
		else {
			$substitution = $this->setReplaceHtml();
			$text_sub = '';
			if(sizeof($substitution) > 0)
			{
				foreach ($substitution AS $key=>$value)
				{
					if(!empty($value))
					{
						if($key == '_LANGUAGE_') $value = _("scelta lingua");
						$text_sub .= "'$key': ".$value."<br />";
					}
				}
			}
			else
			{
				$text_sub = _("non presenti");
			}

			$GINO .= $gform->ctextarea('html', $html, _("Codice html"), array("cols"=>40, "rows"=>15));
			$GINO .= $gform->noinput(_("Codici di sostituzione"), $text_sub);
		}

		$GINO .= $gform->cinput('submit_action', 'submit', _("modifica"), '', array("classField"=>"submit"));

		$GINO .= $gform->endTable();

		return $GINO;
	}
	
	/**
	 * Modifica header/footer
	 * 
	 * @see $_group_1
	 */
	public function actionDoc(){
	
		$this->accessGroup($this->_group_1);
		
		$gform = new Form('gform', 'post', false);
		$gform->save('dataform');
		$req_error = $gform->arequired();
		
		$id = cleanVar($_POST, 'id', 'int', '');
		$action = cleanVar($_POST, 'action', 'string', '');
		$description = cleanVar($_POST, 'description', 'string', '');
		$type = cleanVar($_POST, 'type', 'string', '');
		$html = cleanVarEditor($_POST, 'html', '');
		$old_image = cleanVar($_POST, 'old_image', 'string', '');
		
		if(isset($_POST['html']) AND !empty($_POST['html'])) $html = $_POST['html'];
		else $html = '';
		
		if(!empty($id)) $link = "id=$id&action=$action";
		else $link = "action=$action";

		$redirect = $this->_className.'-manageGraphics';
		$link_error = $this->_home."?evt[$redirect]&$link"; 
		
		if($req_error > 0) 
			exit(error::errorMessage(array('error'=>1), $link_error));
		
		if($type == 2 AND empty($html))
			exit(error::errorMessage(array('error'=>1), $link_error));
		
		if(empty($id) OR $action != $this->_act_modify)
			exit(error::errorMessage(array('error'=>9), $link_error));
		
		if($type == 1) {
			$query = "UPDATE ".$this->_tbl_graphics." SET description='$description', type='$type' WHERE id='$id'";
			$result = $this->_db->actionquery($query);
			$gform->manageFile('image', $old_image, false, $this->_extension_img, GRAPHICS_DIR, $link_error, $this->_tbl_graphics, 'image', 'id', $id);
		}
		else {
			$query = "UPDATE ".$this->_tbl_graphics." SET description='$description', type='$type', html='$html' WHERE id='$id'";
			$result = $this->_db->actionquery($query);
		}
		
		EvtHandler::HttpCall($this->_home, $redirect, $link);
	}
}
?>
