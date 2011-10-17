<?php

class template extends propertyObject {
	
	protected $_tbl_data;
	private static $_tbl_tpl = 'sys_layout_tpl';
	private static $_tbl_tpl_block = 'sys_layout_tpl_block';
	private $_home, $_interface;

	private $_blocks_number, $_blocks_properties;
	private $_align_dict;
	private $_um_dict;

	function __construct($id) {

		$this->_tbl_data = self::$_tbl_tpl;

		parent::__construct($this->initP($id));

		$this->_home = 'index.php';
		$this->_interface = 'layout';

		$this->initBlocksProperties();

		$this->_align_dict = array("1"=>"destra", "2"=>"centro", "3"=>"destra");
		$this->_um_dict = array("1"=>"px", "2"=>"%");

	}
	
	private function initP($id) {
	
		$db = new Db;
		$query = "SELECT * FROM ".$this->_tbl_data." WHERE id='$id'";
		$a = $db->selectquery($query);
		if(sizeof($a)>0) return $a[0]; 
		else return array('id'=>null, 'filename'=>null, 'label'=>null, 'description'=>null);
	}
	
	private function initBlocksProperties() {
	
		$this->_blocks_properties = array();	
		if(!$this->id) $this->_blocks_number = 0;
		else {
			$query = "SELECT COUNT(id) as tot FROM ".self::$_tbl_tpl_block." WHERE tpl='$this->id'";
			$a = $this->_db->selectquery($query);
			if(sizeof($a)>0) $this->_blocks_number = $a[0]['tot'];
			else $this->_blocks_number = 0;
		}

		$query = "SELECT position, width, um, align, rows, cols FROM ".self::$_tbl_tpl_block." WHERE tpl='$this->id' ORDER BY position ASC";
		$a = $this->_db->selectquery($query);
		if(sizeof($a)>0) {
			foreach($a as $b) {
				$this->_blocks_properties[$b['position']] = array(
						"width"=>$b['width'],
						"um"=>$b['um'],
						"align"=>$b['align'],
						"rows"=>$b['rows'],
						"cols"=>$b['cols']
				);
			}
		}
	}

	public function setFilename($value) {
		
		if($this->_p['filename']!=$value && !in_array('filename', $this->_chgP)) $this->_chgP[] = 'filename';
		$this->_p['filename'] = $value;

		return true;

	}

	public static function getAll($order='label') {

		$db = new db;
		$res = array();
		$query = "SELECT id, label, filename, description FROM ".self::$_tbl_tpl." ORDER BY $order";
		$a = $db->selectquery($query);
		if(sizeof($a)>0) {
			foreach($a as $b) {
				$res[] = new template($b['id']);
			}
		}

		return $res;
	}
	
	public static function layoutInfo() {
		
		$htmlsection = new htmlSection(array('class'=>'admin', 'headerTag'=>'h1', 'headerLabel'=>_("Informazioni template")));
		$buffer = "<p>"._("In questa sezione è possibile creare dei template da associare ad una skin. L'associazione del template con un indirizzo (url) dovrà essere effettuato nella sezione 'skin'.");
		
		$buffer .= "<p><b>"._("Procedura di utilizzo (ovvero come associare un template a una pagina)")."</b></p>\n";
		$buffer .= "<p>"._("1. Creare un nuovo template");
		$buffer .= "<ul>
		<li>"._("se necessario inserire anche header e footer")."</li>
		<li>"._("se è una pagina specifica si può utilizzare 'Modulo da url'")."</li>
		</ul>";
		$buffer .= "</p>\n";
		$buffer .= "<p>"._("2. Creare una nuova skin");
		$buffer .= "<ul>
		<li>"._("inserire una espressione regolare completa, tipo").": #index.php\?evt\[course-viewParticipant\].*#</li>
		<li>"._("selezionare il template appena creato")."</li>
		</ul>";
		$buffer .= "</p>\n";
		$buffer .= "<p>"._("3. Una volta inserita la skin");
		$buffer .= "<ul>
		<li>"._("spostare come posizione prima della chiamata standard (ad esempio di 'Pagine pubbliche') per far sì che all'indirizzo inserito possa venire associato il template abbinato alla skin")."</li>
		</ul>";
		$buffer .= "</p>\n";
		
		$buffer .= "<p><b>"._("Indicazioni")."</b></p>\n";
		$buffer .= "<p>"._("Nella maschera di modifica e inserimento è presente il campo 'css' nel quale si può specificare un foglio di stile che viene caricato nella maschera di creazione del template. Selezionando un file css, il foglio di stile non viene automaticamente associato al template, cosa che deve essere fatta al momento di creazione della skin, ma viene utilizzato per creare un template adatto se si ha in previsione di utilizzarlo all'interno di una skin con un css che modifichi le dimensioni degli elementi strutturali.")."</p>\n";
		$buffer .= "<p><b>"._("Funzionamento")."</b></p>\n";
		$buffer .= "<p>"._("La struttura del template è formata da blocchi che contengono navate. Ciascuna navata può contenere un numero qualsiasi di moduli. I moduli lasciati 'vuoti' non occuperanno spazio all'interno del layout finale, mentre le navate 'vuote' occuperanno lo spazio in larghezza esattamente come definito nel template.")."</p>\n";
		
		$htmlsection->content = $buffer;

		return $htmlsection->render();
	
	}

	public function formTemplate() {

		$gform = new Form('gform', 'post', true, array("trnsl_table"=>$this->_tbl_data, "trnsl_id"=>$this->id));
		$gform->load('dataform');

		$title = ($this->id)?_("Modifica template ").htmlChars($this->label):_("Nuovo template");
		$htmlsection = new htmlSection(array('class'=>'admin', 'headerTag'=>'h1', 'headerLabel'=>$title));

		$required = 'label';
		$buffer = $gform->form($this->_home."?pt[".$this->_interface."-manageLayout]&block=template&action=mngtpl", '', $required);
		$buffer .= $gform->hidden('id', $this->id);
		$buffer .= $gform->cinput('label', 'text', $gform->retvar('label', htmlInput($this->label)), _("Etichetta"), array("required"=>true, "size"=>40, "maxlength"=>200, "trnsl"=>true, "field"=>"label"));
		$buffer .= ($this->id)
			? $gform->noinput("&#160;&#160;"._("Nome file"), $this->filename)
			: $gform->cinput('filename', 'text', $gform->retvar('filename', htmlInput($this->filename)), array(_("Nome file"), _("Senza estensione")), array("required"=>true, "size"=>40, "maxlength"=>200, "pattern"=>"^[\d\w_-]*$", "hint"=>_("il nome del file può contenere solamente caratteri alfanumerici o i caratteri '_' e '-'")));
		$buffer .= $gform->ctextarea('description', $gform->retvar('description', htmlInput($this->description)), _("Descrizione"), array("cols"=>45, "rows"=>4, "trnsl"=>true, "field"=>"description"));

		$css_list = array();
		foreach(css::getAll() as $css) {
			$css_list[$css->id] = htmlInput($css->label);
		}
		$buffer .= $gform->cselect('css', $gform->retvar('css', $this->css), $css_list, array(_("Css"), _("Selezionare il css qualora lo si voglia associare al template nel momento di definizione della skin")), null);

		$buffer .= $this->formBlock($gform);

		$buffer .= $gform->cinput('submit_action', 'submit', (($this->id)?_("prosegui"):_("crea template")), '', array("classField"=>"submit"));

		$buffer .= $gform->cform();

		$htmlsection->content = $buffer;

		return $htmlsection->render();

	}

	private function formBlock($gform) {
	
		if($this->id) {
			$buffer = $gform->noinput(_("Numero blocchi"), $this->_blocks_number);
			$buffer .= $gform->hidden('blocks_number', $this->_blocks_number);
			$buffer .= $gform->cell($this->tplBlockForm(), array("id"=>"blocks_form"));
		}
		else {
			for($i=1, $blocks_list=array(); $i<11; $i++) $blocks_list[$i] = $i;

			$onchange = "onchange=\"ajaxRequest('post', '$this->_home?pt[layout-manageLayout]&block=template&action=mngblocks', 'id=$this->id&blocks_number='+$(this).value, 'blocks_form', {'load':'blocks_form'});\"";
			$buffer = $gform->cselect('blocks_number', $gform->retvar('blocks_number', $this->_blocks_number), $blocks_list, array(_("Numero blocchi"), _("Selezionare il numero di blocchi che devono comporre il layout")), array("js"=>$onchange));
			$buffer .= $gform->cell($this->tplBlockForm(), array("id"=>"blocks_form"));
		}

		return $buffer;
	}

	public function tplBlockForm() {
	
		$gform = new Form('gform', 'post', false);

		$blocks_number = $this->id ? $this->_blocks_number : cleanVar($_POST, 'blocks_number', 'int', '');

		$buffer = $gform->startTable();
		for($i=1; $i<$blocks_number+1; $i++) {

			$buffer .= $gform->cell("<p><b>"._("Blocco ").$i."</b></p>");

			if($this->id) {
				$um = $this->_blocks_properties[$i]['um'] ? $this->_um_dict[$this->_blocks_properties[$i]['um']] : ''; 
				$width = $this->_blocks_properties[$i]['width'] ? $this->_blocks_properties[$i]['width'] : '';
				$align = $this->_blocks_properties[$i]['align'] ? $this->_align_dict[$this->_blocks_properties[$i]['align']] : ''; 
				$buffer .= $gform->noinput(_("Larghezza"), $width.$um);
				$buffer .= $gform->noinput(_("Allineamento"), $align);
				$buffer .= $gform->noinput(_("Numero righe"), $this->_blocks_properties[$i]['rows']);
				$buffer .= $gform->noinput(_("Numero colonne"), $this->_blocks_properties[$i]['cols']);

				$buffer .= $gform->hidden('width_'.$i, $width);
				$buffer .= $gform->hidden('um_'.$i, $this->_blocks_properties[$i]['um']);
				$buffer .= $gform->hidden('align_'.$i, $this->_blocks_properties[$i]['align']);
				$buffer .= $gform->hidden('rows_'.$i, $this->_blocks_properties[$i]['rows']);
				$buffer .= $gform->hidden('cols_'.$i, $this->_blocks_properties[$i]['cols']);
			}
			else {
				$um = " ".$gform->select('um_'.$i, '', $this->_um_dict, array());
				$buffer .= $gform->cinput('width_'.$i, 'text', '', array(_("Larghezza"), _("Se non specificata occupa tutto lo spazio disponibile")), array("required"=>false, "size"=>4, "maxlength"=>4, "text_add"=>$um));
				$buffer .= $gform->cselect('align_'.$i, '', $this->_align_dict, _("Allineamento"), array());
				$buffer .= $gform->cinput('rows_'.$i, 'text', '', _("Numero righe"), array("required"=>true, "size"=>2, "maxlength"=>2));
				$buffer .= $gform->cinput('cols_'.$i, 'text', '', _("Numero colonne"), array("required"=>true, "size"=>2, "maxlength"=>2));
			}
		}
			
		$buffer .= $gform->endTable();

		return $buffer;
	}
	
	public function formDelTemplate() {
	
		$gform = new Form('gform', 'post', true);
		$gform->load('dataform');

		$id = cleanVar($_GET, 'id', 'int', '');

		$htmlsection = new htmlSection(array('class'=>'admin', 'headerTag'=>'h1', 'headerLabel'=>_("Elimina template")));

		$required = '';
		$buffer = $gform->form($this->_home."?evt[layout-actionDelTemplate]", '', $required);
		$buffer .= $gform->hidden('id', $this->id);
		$buffer .= $gform->cinput('submit_action', 'submit', _("elimina"), array(_("Attenzione!"), _("l'eliminazione determina l'eliminazione del template dalle skin che lo contengono!")), array("classField"=>"submit"));
		$buffer .= $gform->cform();

		$htmlsection->content = $buffer;

		return $htmlsection->render();

	}
	
	public function actionDelTemplate() {
		if($this->filename) @unlink(TPL_DIR.OS.$this->filename);		

		skin::removeTemplate($this->id);

		language::deleteTranslations($this->_tbl_data, $this->id);
		$this->deleteBlocks();
		$this->deleteDbData();

		header("Location: $this->_home?evt[$this->_interface-manageLayout]&block=template");

	}

	public function manageTemplate($css, $tpl_id=0) {

		$gform = new Form('tplform', 'post', false, array("tblLayout"=>false));
		$gform->load('dataform');

		$dftTpl = cleanVar($_POST, 'dftTpl', 'int', '');
		$label = cleanVar($_POST, 'label', 'string', '');
		$filename = cleanVar($_POST, 'filename', 'string', '');
		$description = cleanVar($_POST, 'description', 'string', '');
		$blocks_number = cleanVar($_POST, 'blocks_number', 'int', '');

		if($this->id && !$dftTpl) {
			$template = $this->filename;
			$template = file_get_contents(TPL_DIR.OS.$template);
		}
		else $template = $this->createEmptyTemplate($blocks_number);

		$buffer = "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Strict//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd\">\n";
		$buffer .= "<html xmlns=\"http://www.w3.org/1999/xhtml\" lang=\"en\" xml:lang=\"en\">\n";
		$buffer .= "<head>\n";
		$buffer .= "<meta http-equiv=\"content-type\" content=\"text/html; charset=utf-8\" />\n";
		$buffer .= "<title>Template</title>\n";
		$buffer .= css::mainCss();
		$buffer .= "<style type=\"text/css\">\n";
		$buffer .= "@import url(\"".SITE_APP.OS."layout".OS."layout.css\");\n";
		if($css->id) $buffer .= "@import url(\"".CSS_WWW."/$css->filename\");\n";
		$buffer .= "</style>\n";
		$buffer .= javascript::mootoolsLib();
		$buffer .= javascript::fullGinoMinLib();
		$buffer .= "<script type=\"text/javascript\" src=\"".SITE_APP."/layout/layout.js\"></script>\n";
		$buffer .= "</head>\n";

		$buffer .= "<body>\n";
		$buffer .= "<p class=\"title\">$label</p>";

		$regexp = "/(<div(?:.*?)(id=\"(nav_.*?)\")(?:.*?)>)\n?([^<>]*?)\n?(<\/div>)/";
		$render = preg_replace_callback($regexp, array($this, "renderNave"), $template);
		$buffer .= $render;
		
		$buffer .= "<table style=\"width:100%;background-color:#eee;margin-top:20px;\">";
		$buffer .= "<tr>";
		$buffer .= "<td style=\"width:50%;text-align:right;padding-top:5px;padding-bottom:8px;\">";
		$gform1 = new Form('gform', 'post', false, array("tblLayout"=>false));
		$gform1->load('dataform');

		$required = '';
		$buffer .= $gform1->form('', '', $required);
		$buffer .= $gform1->hidden('id', $this->id);
		$buffer .= $gform1->hidden('dftTpl', 1);
		$buffer .= $gform1->hidden('label', htmlInput($label));
		$buffer .= $gform1->hidden('description', htmlInput($description));
		$buffer .= $gform1->hidden('filename', $filename);
		$buffer .= $gform1->hidden('css', $css->id);
		$buffer .= $gform1->hidden('selMdlTitle', _("Selezione modulo"), array("id"=>"selMdlTitle"));
		$buffer .= $gform1->hidden('blocks_number', htmlInput($blocks_number));
		for($i=1; $i<$blocks_number + 1; $i++) {
			$buffer .= $gform1->hidden('width_'.$i, cleanVar($_POST, 'width_'.$i, 'int', ''));
			$buffer .= $gform1->hidden('um_'.$i, cleanVar($_POST, 'um_'.$i, 'int', ''));
			$buffer .= $gform1->hidden('align_'.$i, cleanVar($_POST, 'align_'.$i, 'int', ''));
			$buffer .= $gform1->hidden('rows_'.$i, cleanVar($_POST, 'rows_'.$i, 'int', ''));
			$buffer .= $gform1->hidden('cols_'.$i, cleanVar($_POST, 'cols_'.$i, 'int', ''));
		}
		$buffer .= $gform1->input('dft', 'submit', _("template originale"), array("classField"=>"generic"));
		$buffer .= $gform1->cform();
		
		$buffer .= "</td>";
		$buffer .= "<td style=\"text-align:left;background-color:#d4d4d4;padding-top:5px;padding-bottom:8px;\">";

		$required = '';
		$buffer .= $gform->form($this->_home."?evt[".$this->_interface."-actionTemplate]", '', $required);
		$buffer .= $gform->hidden('id', $this->id);
		$buffer .= $gform->hidden('label', htmlInput($label));
		$buffer .= $gform->hidden('description', htmlInput($description));
		$buffer .= $gform->hidden('filename', $filename);
		$buffer .= $gform->hidden('tplform_text', '', array("id"=>"tplform_text"));
		$buffer .= $gform1->hidden('blocks_number', htmlInput($blocks_number));
		for($i=1; $i<$blocks_number + 1; $i++) {
			$buffer .= $gform1->hidden('width_'.$i, cleanVar($_POST, 'width_'.$i, 'int', ''));
			$buffer .= $gform1->hidden('um_'.$i, cleanVar($_POST, 'um_'.$i, 'int', ''));
			$buffer .= $gform1->hidden('align_'.$i, cleanVar($_POST, 'align_'.$i, 'int', ''));
			$buffer .= $gform1->hidden('rows_'.$i, cleanVar($_POST, 'rows_'.$i, 'int', ''));
			$buffer .= $gform1->hidden('cols_'.$i, cleanVar($_POST, 'cols_'.$i, 'int', ''));
		}
		$buffer .= $gform->input('back', 'button', _("indietro"), array("classField"=>"generic", "js"=>"onclick=\"history.go(-1)\""));
		$buffer .= " ".$gform->input('save', 'button', _("salva template"), array("classField"=>"submit", "js"=>"onclick=\"saveTemplate();\""));
		$buffer .= $gform->cform();
		$buffer .= "</td>";
		$buffer .= "</tr>";
		$buffer .= "</table>";

		$buffer .= "</div>\n";

		$buffer .= "</body>\n";
		$buffer .= "</html>\n";

		return $buffer;

	}

	private function createEmptyTemplate($blocks_number) {
	
		$buffer = '';
		for($i=1; $i<$blocks_number+1; $i++) {
			
			if(cleanVar($_POST, 'align_'.$i, 'int', '')==2) $margin = "margin: auto;"; 
			elseif(cleanVar($_POST, 'align_'.$i, 'int', '')==3) $margin = "float: right;";
		        else $margin = '';

			$rows = cleanVar($_POST, 'rows_'.$i, 'int', '');
			$cols = cleanVar($_POST, 'cols_'.$i, 'int', '');
			$um = cleanVar($_POST, 'um_'.$i, 'int', '') == 1 ? 'px' : '%';
			$width = cleanVar($_POST, 'width_'.$i, 'int', '');

			$block_style_width = $width ? "width:".$width.$um.";" : '';

			if($um == 'px' && $width) $nav_style = "width:".floor($width/$cols)."px".($cols>1 ? ";float:left;" : "");
			else $nav_style = "width:".floor(100/$cols)."%".($cols>1 ? ";float:left;" : "");

			$buffer .= "<div id=\"block_$i\" style=\"$block_style_width$margin\">\n";

			for($ii=1; $ii<$rows+1; $ii++) {
				for($iii=1; $iii<$cols+1; $iii++) {
					$buffer .= "<div id=\"nav_".$i."_".$ii."_".$iii."\" style=\"".$nav_style."\">";
					$buffer .= "</div>";
				}
				$buffer .= "<div class=\"null\"></div>";
			}

			$buffer .= "</div>";
			$buffer .= "<div class=\"null\"></div>";

		}
			
		return $buffer;

	}

	private function renderNave($matches) {
		/*
		 * $matches[0] complete matching 
		 * $matches[1] match open tag, es. <div id="nav_1_1" style="float:left;width:200px">
		 * $matches[3] match div id, es. nav_1_1
		 * $matches[4] match div content, es. {module classid=20 func=blockList}
		 * $matches[5] match close tag, es. </div>
		 */

		/*
		 *
		 * contare i refillable_1 _2 _3 etc... metterne uno vuoto alla fine, attivare i pulsanti su quelli pieni
		 *
		 */

		$buffer = $matches[1];
		$buffer .= $this->cellCtrl($matches[3]);
		$buffer .= "<div id=\"sortables_".$matches[3]."\">";
		$count = 0;
		foreach(explode("\n", $matches[4]) as $mdlMarker) {
			if(preg_match("#module#", $mdlMarker)) {
				$mdlMarker = preg_replace("#[\r\n]#", "", $mdlMarker);
				preg_match("#\s(\w+)id=([0-9]+)\s*(\w+=(\w+))?#", $mdlMarker, $m);
				$mdlId = (!empty($m[2]))? $m[2]:null;
				$mdlType = (!empty($m[1]))? $m[1]:null;

				if($mdlType=='page') {
					$title = $this->_db->getFieldFromId('page', 'title', 'item_id', $mdlId);
					$mdlFunc = $m[4];
					$title .= $mdlFunc=='block'? _(" - Blocco"):_(" - Completo");
					$jsurl = $this->_home."?pt[page-".($mdlFunc=='block'?"blockItem":"displayItem")."]&id=".$mdlId; 
				}
				elseif($mdlType=='class' || $mdlType=='class') {
					$classname = $this->_db->getFieldFromId('sys_module', 'class', 'id', $mdlId);
					$instancename = $this->_db->getFieldFromId('sys_module', 'name', 'id', $mdlId);
					$title = $this->_db->getFieldFromId('sys_module', 'label', 'id', $mdlId);
					$mdlFunc = $m[4];
					$output_functions = (method_exists($classname, 'outputFunctions'))? call_user_func(array($classname, 'outputFunctions')):array();
					$title .= " - ".$output_functions[$mdlFunc]['label'];
					$jsurl = $this->_home."?pt[$instancename-$mdlFunc]";
				}
				elseif($mdlType=='class' || $mdlType=='sysclass') {
					$classname = $this->_db->getFieldFromId('sys_module_app', 'name', 'id', $mdlId);
					$title = $this->_db->getFieldFromId('sys_module_app', 'label', 'id', $mdlId);
					$mdlFunc = $m[4];
					$output_functions = (method_exists($classname, 'outputFunctions'))? call_user_func(array($classname, 'outputFunctions')):array();
					$title .= " - ".$output_functions[$mdlFunc]['label'];
					$jsurl = $this->_home."?pt[$classname-$mdlFunc]";
				}
				elseif($mdlType=='func') {
					$funcname = $this->_db->getFieldFromId('sys_module', 'name', 'id', $mdlId);
					$title = $this->_db->getFieldFromId('sys_module', 'label', 'id', $mdlId);
					$jsurl = $this->_home."?pt[sysfunc-$funcname]";
				}
				elseif($mdlType=='' && $mdlId == 0) {
					$title = _("Modulo da url");
					$jsurl = null;
				}
				else exit(error::syserrorMessage("document", "renderModule", "Tipo di modulo sconosciuto", __LINE__));
 	
				$buffer .= "<div id=\"mdlContainer_".$matches[3]."_$count\">";
				$buffer .= "<div class=\"mdlContainerCtrl\">";
				$buffer .= "<div class=\"disposeMdl\"></div>";
				$buffer .= "<div class=\"sortMdl\"></div>";
				$buffer .= "<div class=\"toggleMdl\"></div>";
				$buffer .= "<div class=\"null\"></div>";
				$buffer .= "</div>";
				$buffer .= "<div id=\"refillable_".$matches[3]."_$count\" class=\"refillableFilled\">";
				$buffer .= "<input type=\"hidden\" name=\"navElement\" value=\"".$mdlMarker."\" />";
				$buffer .= "<div>".htmlChars($title)."</div>";
				$buffer .= "</div>";
				$buffer .= "<div id=\"fill_".$matches[3]."_$count\" style=\"display:none;\"></div>";
				$buffer .= "</div>";

				if($jsurl) {
					$buffer .= "<script>ajaxRequest('post', '$jsurl', '', 'fill_".$matches[3]."_$count', {'script':true})</script>";
				}
				$count++;
			}
		}
		
		$buffer .= "<div id=\"mdlContainer_".$matches[3]."_$count\">";
		$buffer .= "<div class=\"mdlContainerCtrl\">";
		$buffer .= "<div class=\"disposeMdlDisabled\"></div>";
		$buffer .= "<div class=\"sortMdlDisabled\"></div>";
		$buffer .= "<div class=\"toggleMdlDisabled\"></div>";
		$buffer .= "<div class=\"null\"></div>";
		$buffer .= "</div>";
		$buffer .= "<div id=\"refillable_".$matches[3]."_$count\" class=\"refillable\">";
		$buffer .= "</div>";
		$buffer .= "<div id=\"fill_".$matches[3]."_$count\" style=\"display:none;\"></div>";
		$buffer .= "</div>";

		$buffer .= "</div>";
		$buffer .= "<div class=\"navSizeCtrl\"> &nbsp; <div class=\"widthCtrl\"></div></div>";
		$buffer .= $matches[5];
	
		return $buffer;

	}

	private function cellCtrl($id) {

		$buffer = "<div class=\"navCtrl\">";
		$buffer .= "<div class=\"left\">$id &#160;</div>";
		$buffer .= "<div class=\"left\"><span class=\"navWidth\"></span></div>";
		$buffer .= "<div class=\"right\">";
		$buffer .= "<div class=\"fineMoreWidthCtrl\" title=\""._("aumenta larghezza")."\"></div>";
		$buffer .= "<div class=\"fineLessWidthCtrl\" title=\""._("diminuisci larghezza")."\"></div>";
		$buffer .= "<div class=\"floatCtrl\" title=\""._("modifica proprietà float")."\"></div>";
		$buffer .= "<div class=\"disposeCtrl\" title=\""._("elimina navata")."\"></div>";
		$buffer .= "</div>";
		$buffer .= "<div class=\"null\"></div>";

		$buffer .= "</div>";	

		return $buffer;

	}

	public function actionTemplate() {
	
		$edit = $this->id ? true : false;

		$tplContent = $_POST['tplform_text'];
		if(get_magic_quotes_gpc()) $tplContent = stripslashes($tplContent);	// magic_quotes_gpc = On

		$this->label = 'label';
		$this->description = 'description';
		$tplFilename = cleanVar($_POST, 'filename', 'string', '');
		if($tplFilename) $this->filename = $tplFilename.".tpl";

		$action = ($this->id)? "modify":"insert";

		$link_error = $this->_home."?evt[$this->_interface-manageLayout]&block=template&action=$action";

		if(!$this->id && is_file(TPL_DIR.OS.$this->filename.".tpl")) 
			exit(error::errorMessage(array('error'=>_("Nome file già presente")), $link_error));

		if($fp = @fopen(TPL_DIR.OS.$this->filename, "wb")) {
			fwrite($fp, $tplContent) || exit(error::errorMessage(array('error'=>_("Impossibile scrivere il file")), $link_error));
			fclose($fp);
		}
		else
			exit(error::errorMessage(array('error'=>_("Impossibile creare il file"), 'hint'=>_("Controllare i permessi in scrittura all'interno della cartella ".TPL_DIR.OS)), $link_error));
		$this->updateDbData();

		$blocks_number = cleanVar($_POST, 'blocks_number', 'int', '');
		for($i=1; $i<$blocks_number+1; $i++) {
			$width = cleanVar($_POST, 'width_'.$i, 'int', '');
			$um = cleanVar($_POST, 'um_'.$i, 'int', '');
			$align = cleanVar($_POST, 'align_'.$i, 'int', '');
			$rows = cleanVar($_POST, 'rows_'.$i, 'int', '');
			$cols = cleanVar($_POST, 'cols_'.$i, 'int', '');

			if(!$edit) $this->saveBlock(null, $i, $width, $um, $align, $rows, $cols);

		}

		header("Location: $this->_home?evt[$this->_interface-manageLayout]&block=template");
	
	}

	private function saveBlock($id, $position, $width, $um, $align, $rows, $cols) {
	
		$query = "INSERT INTO ".self::$_tbl_tpl_block." (tpl, position, width, um, align, rows, cols) VALUES ('$this->id', '$position', '$width', '$um', '$align', '$rows', '$cols')";
		return $this->_db->actionquery($query);

	}

	private function deleteBlocks() {

		$query = "DELETE FROM ".self::$_tbl_tpl_block." WHERE tpl='$this->id'"; 	
		return $this->_db->actionquery($query);
	}

}
?>
