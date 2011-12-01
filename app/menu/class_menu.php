<?php
require_once('class_menuVoice.php');

class menu extends AbstractEvtClass {

	private static $_menu_functions_list = 'menuFunctionsList';
	private $_group_1;
	protected $_instance;
	protected $_instanceName;

	private $_tbl_opt;
	
	private $_options;
	public $_optionsLabels;
	private $_title, $_title_visible, $_opt_home, $_opt_admin, $_opt_logout;
	private $_opt_horizontal, $_opt_click_event, $_opt_init_show_icon, $_path_to_sel, $_cache;

	private $_ico_mini_sort;
	private $_block;

	function __construct($instance) {

		parent::__construct();

		$this->_instance = $instance;
		$this->_instanceName = $this->_db->getFieldFromId($this->_tbl_module, 'name', 'id', $this->_instance);
		
		$this->setAccess();
		$this->setGroups();

		$this->_tbl_opt = "sys_menu_opt";

		/*
			Opzioni
		*/
		
		// Valori di default
		$this->_optionsValue = array(
			'home_voice'=>_("Home"), 
			'admin_voice'=>_("Amministrazione"), 
			'logout_voice'=>_("Logout")
		);
		
		$this->_title = htmlChars($this->setOption('title', true));
		$this->_title_visible = $this->setOption('vis_title');
		$this->_opt_home = htmlChars($this->setOption('home_voice', array('value'=>$this->_optionsValue['home_voice'], 'translation'=>true)));
		$this->_opt_admin = htmlChars($this->setOption('admin_voice', array('value'=>$this->_optionsValue['admin_voice'], 'translation'=>true)));
		$this->_opt_logout = htmlChars($this->setOption('logout_voice', array('value'=>$this->_optionsValue['logout_voice'], 'translation'=>true)));
		$this->_opt_horizontal = $this->setOption('horizontal');
		$this->_opt_click_event = $this->setOption('click_event');
		$this->_opt_init_show_icon = $this->setOption('initShowIcon');
		$this->_path_to_sel = $this->setOption('path_to_sel');
		$this->_cache = $this->setOption('cache', array("value"=>0));

		// the second paramether will be the class instance
		$this->_options = new options($this->_className, $this->_instance);
		$this->_optionsLabels = array(
			"title"=>_("Titolo"),
			"vis_title"=>_("Titolo visibile"),
			"home_voice"=>array('label'=>array(_("Label voce 'Home'"), _("se vuota non è visibile")), 'value'=>$this->_optionsValue['home_voice']),
			"admin_voice"=>array('label'=>array(_("Label voce 'Amministrazione'"), _("se vuota non è visibile")), 'value'=>$this->_optionsValue['admin_voice']),
			"logout_voice"=>array('label'=>array(_("Label voce 'Logout'"), _("se vuota non è visibile")), 'value'=>$this->_optionsValue['logout_voice']),
			"horizontal"=>_("Menu orizzontale"),
			"initShowIcon"=>array(_("Stile livelli con sottomenu"), _("'si': sempre visibile<br/>'no': visibile su over")),
			"click_event"=>array(_("Evento apertura livelli"), _("'si': su click<br/>'no': su over")),
			"path_to_sel"=>array(_("Briciole di pane"), _("'si': percorso scritto<br/>'no': percorso rappresentato graficamenteo")),
			"cache"=>array("label"=>array(_("Tempo di caching dei contenuti (s)"), _("Se non si vogliono tenere in cache o non si è sicuri del significato lasciare vuoto o settare a 0")), "required"=>false)
		);
		$this->_action = (isset($_POST['action']) || isset($_GET['action'])) ? $_REQUEST['action']:null;

		$this->_ico_mini_sort = "<img style=\"margin-bottom:3px;cursor:move\" src=\"$this->_class_img/ico_minisort.gif\" />";
		$this->_ico_more = "<img class=\"ico_more_menu\" src=\"$this->_class_img/icoMoreV.png\" />";

		$this->_block = cleanVar($_REQUEST, 'block', 'string', '');
	}
	
	public static function getClassElements() {

		return array("tables"=>array('sys_menu_voices', 'sys_menu_opt', 'sys_menu_grp', 'sys_menu_usr'),
			     "css"=>array('menu.css', 'menuH.css', 'menuV.css')
		      );

	}
	
	public function deleteInstance() {

		$this->accessGroup('');

		/*
		 * delete menu voices and translations
		 */
		menuVoice::deleteInstanceVoices($this->_instance);
		
		/*
		 * delete record and translation from table menu_opt
		 */
		$opt_id = $this->_db->getFieldFromId($this->_tbl_opt, "id", "instance", $this->_instance);
		language::deleteTranslations($this->_tbl_opt, $opt_id);
		
		$query = "DELETE FROM ".$this->_tbl_opt." WHERE instance='$this->_instance'";	
		$result = $this->_db->actionquery($query);

		/*
		 * delete css files
		 */
		$classElements = $this->getClassElements();
		foreach($classElements['css'] as $css) {
			unlink(APP_DIR.OS.$this->_className.OS.baseFileName($css)."_".$this->_instanceName.".css");
		}

		return $result;
	}

	private function setGroups(){
		
		$this->_group_1 = array($this->_list_group[0], $this->_list_group[1]);
	}
	
	/*
	 * Funzioni che possono essere richiamate da menu e messe all'interno del template;
	 * array ("function" => array("label"=>"description", "role"=>"privileges"))
	 */
	public static function outputFunctions() {

		$list = array(
			"blockList" => array("label"=>_("visualizzazione menu"), "role"=>'1'),
			"breadCrumbs" => array("label"=>_("Briciole di pane"), "role"=>1)
		);

		return $list;
	}

	public function blockList() {

		$this->accessType($this->_access_base);

		$sel_voice = menuVoice::getSelectedVoice($this->_instance);

		$buffer  = '';

		$cache = new outputCache($buffer, $this->_cache ? true : false);
		if($cache->start($this->_instanceName, "view".$sel_voice.$this->_lng_nav, $this->_cache)) {
			
			$options = "{";
			$options .= "fmode: ".(($this->_opt_horizontal)?"'horizontal'":"'vertical'").",";
			$options .= "initShowIcon: ".(($this->_opt_init_show_icon)?"true":"false").",";
			$options .= "clickEvent: ".(($this->_opt_click_event)?"true":"false").",";
			$options .= "selectVoiceSnake: ".(($this->_path_to_sel)?"false":"true");
			$options .= "}";
			
			$GINO = $this->scriptAsset("menu_".$this->_instanceName.".css", "menuCSS".$this->_instance, 'css');

			$GINO .= "<nav id=\"menu_$this->_instanceName\">";
			if($this->_title_visible) $GINO .= "<div class=\"section_title\">$this->_title</div>";

			if($this->_opt_horizontal) 
				$GINO .= $this->scriptAsset("menuH_".$this->_instanceName.".css", "menuHCSS".$this->_instance, 'css');
			else 
				$GINO .= $this->scriptAsset("menuV_".$this->_instanceName.".css", "menuVCSS".$this->_instance, 'css');

			if($this->_path_to_sel) {
				if($this->_opt_horizontal) $GINO .= "<div class=\"pathToSelVoice\">".$this->pathToSelectedVoice()."</div>";
				else {
					$GINO .= "<script type=\"text/javascript\">";
					$GINO .= "var content = '".jsVar($this->pathToSelectedVoice())."';
						  divPTSV = new Element('div', {'class':'pathToSelVoice', 'id':'pathToSelVoice'});
						  divPTSV.set('html', content);
						  divPTSV.inject($('menu_$this->_instanceName'), 'top')";
					$GINO .= "</script>";
				}
			}

			$GINO .= $this->renderMenu(0, $sel_voice);
			$GINO .= "</nav>";
			
			$GINO .= "<script type=\"text/javascript\">\n";
			$GINO .= "chargeMenu = function() {
					var myMenu".$this->_instance." = new AbidiMenu(\"menu_$this->_instance\", $options);
				  }";
			$GINO .= "</script>\n"; 
			
			$GINO .= $this->scriptAsset("abidiMenu.js", "AbidiMenuJS", 'js', null, array("onload"=>"chargeMenu"));

			$cache->stop($GINO);
		}

		return $buffer;
	
	}

	public function breadCrumbs() {
		
		$this->accessType($this->_access_base);
		
		$sel_voice = menuVoice::getSelectedVoice($this->_instance);
		$GINO = '';

		$cache = new outputCache($GINO, $this->_cache ? true : false);
		if($cache->start($this->_instanceName, "breadcrumbs".$sel_voice.$this->_lng_nav, $this->_cache)) {
			$htmlsection = new htmlSection(array('id'=>"pathmenu_".$this->_instanceName,'class'=>'public'));
			$buffer = $this->scriptAsset("menu_".$this->_instanceName.".css", "menuCSS".$this->_instance, 'css');
			$buffer .= $this->pathToSelectedVoice();

			$htmlsection->content = $buffer;

			$buffer = $htmlsection->render();

			$cache->stop($buffer);
		}

		return $GINO;

	}

	private function renderMenu($parent=0, $s=0) {

		$GINO = '';
		$auth_where = $this->_session_user?"AND authView='1'":"";
		$query = "SELECT id FROM ".menuVoice::$tbl_voices." WHERE instance='$this->_instance' AND parent='$parent' $auth_where AND role1>=$this->_session_role ORDER BY orderList";
		$a = $this->_db->selectquery($query);
		if(sizeof($a)>0) {
			$GINO .= ($parent!=0)?"<ul>\n":"<ul id=\"menu_".$this->_instance."\" class=\"mainmenu\">\n"; 
			$GINO .= ($parent==0 && $this->_opt_home)? "<li class=\"".(($s=='home')?"selectedVoice":"")."\"><a href=\"$this->_home\">$this->_opt_home</a></li>\n":"";
			foreach($a as $b) {
				$voice = new menuVoice($b['id']);
				$link = ($voice->link)?"href=\"".$this->_plink->linkFromDB($voice->link)."\"":"";
				$rel = ($voice->type=='ext')?"target=\"_blank\" rel=\"external\"":"";
				$class = ($s==$voice->id)?"selectedVoice":"";
				$GINO .= "<li id=\"id".$voice->id."\" class=\"$class\"><a $rel $link>".htmlChars($voice->ml('label'))."</a>";
				$GINO .= $this->renderMenu($voice->id, $s);
				$GINO .= "</li>\n";
			}
			$GINO .= ($parent==0 && $this->_opt_admin && $this->_access->getAccessAdmin())? "<li class=\"".(($s=='admin')?"selectedVoice":"")."\"><a href=\"$this->_home?evt[index-admin_page]\">$this->_opt_admin</a></li>\n":"";
			$GINO .= ($parent==0 && $this->_opt_logout && $this->_access->AccessVerifyIf())? "<li><a href=\"$this->_home?action=logout\">$this->_opt_logout</a></li>\n":"";

			$GINO .= "</ul>\n"; 
		}
		else {
			if($parent==0) {
				if($this->_opt_home || ($this->_opt_admin && $this->_access->getAccessAdmin()) || ($this->_opt_logout && $this->_access->AccessVerifyIf())) {
					$GINO .= "<ul id=\"menu_".$this->_instance."\" class=\"mainmenu\">\n";
					$GINO .= ($this->_opt_home)? "<li class=\"".(($s=='home')?"selectedVoice":"")."\"><a href=\"$this->_home\">$this->_opt_home</a></li>\n":"";
					$GINO .= ($this->_opt_admin && $this->_access->getAccessAdmin())? "<li class=\"".(($s=='admin')?"selectedVoice":"")."\"><a href=\"$this->_home?evt[index-admin_page]\">$this->_opt_admin</a></li>\n":"";
					$GINO .= ($this->_opt_logout && $this->_access->AccessVerifyIf())? "<li><a href=\"$this->_home?action=logout\">$this->_opt_logout</a></li>\n":"";

					$GINO .= "</ul>";
				}
			}
		}

		$GINO .= "<div class=\"null\"></div>";

		return $GINO;
	}

	private function pathToSelectedVoice() {
	
		$s = menuVoice::getSelectedVoice($this->_instance);
		if($s=='home') return "<a href=\"$this->_home\">$this->_opt_home</a>";
		$sVoice = new menuVoice($s);
		$buffer = ($sVoice->link)?"<a href=\"".$this->_plink->linkFromDB($voice->link)."\">".htmlChars($sVoice->label)."</a>":htmlChars($sVoice->label);
		$parent = $sVoice->parent;
		while($parent!=0) {
			$pVoice = new menuVoice($parent);
			$buffer = (($pVoice->link)?"<a href=\"".$this->_plink->linkFromDB($voice->link)."\">".htmlChars($pVoice->label)."</a>":htmlChars($pVoice->label))." ".$this->_ico_more." ".$buffer;	
			$parent = $pVoice->parent;	
		}
		return $buffer;
	}

	public function manageDoc() {
		
		$this->accessGroup('ALL');

		$htmltab = new htmlTab(array("linkPosition"=>'right', "title"=>$this->_title));	
		$link_admin = "<a href=\"".$this->_home."?evt[$this->_instanceName-manageDoc]&block=permissions\">"._("Permessi")."</a>";
		$link_options = "<a href=\"".$this->_home."?evt[$this->_instanceName-manageDoc]&block=options\">"._("Opzioni")."</a>";
		$link_css = "<a href=\"".$this->_home."?evt[$this->_instanceName-manageDoc]&block=css\">"._("CSS")."</a>";
		$link_dft = "<a href=\"".$this->_home."?evt[".$this->_instanceName."-manageDoc]\">"._("Gestione")."</a>";
		$sel_link = $link_dft;
		
		if($this->_block == 'css') {
			$GINO = sysfunc::manageCss($this->_instance, $this->_className);		
			$sel_link = $link_css;
		}
		elseif($this->_block == 'permissions' && $this->_access->AccessVerifyGroupIf($this->_className, $this->_instance, '', '')) {
			$GINO = sysfunc::managePermissions($this->_instance, $this->_className);		
			$sel_link = $link_admin;
		}
		elseif($this->_block == 'options') {
			$GINO = sysfunc::manageOptions($this->_instance, $this->_className);		
			$sel_link = $link_options;
		}
		else {

			$id = cleanVar($_GET, 'id', 'int', '');
			$parent = cleanVar($_GET, 'parent', 'int', '');
			$voice = ($parent)?null:$id;
			$menuVoice = new menuVoice($voice);

			if($this->_action == $this->_act_delete) $form = $this->formDelMenuVoice($menuVoice);
			elseif($this->_action == $this->_act_insert) $form = $this->formMenuVoice($menuVoice, $parent);
			elseif($voice) $form = $this->formMenuVoice($menuVoice, $menuVoice->parent);
			else $form = $this->info();

			$GINO = $this->scriptAsset("menu_".$this->_instanceName.".css", "menuCSS".$this->_instance, 'css');

			$GINO .= "<div class=\"vertical_1\">\n";
			$GINO .= $this->listMenu($id);
			$GINO .= "</div>\n";
		
			$GINO .= "<div class=\"vertical_2\">\n";
			$GINO .= $form;
			$GINO .= "</div>\n";
		
			$GINO .= "<div class=\"null\"></div>";
		}
		
		if($this->_access->AccessVerifyGroupIf($this->_className, $this->_instance, '', '')) $links_array = array($link_admin, $link_css, $link_options, $link_dft);
		else $links_array = array($link_css, $link_options, $link_dft);

		$htmltab->navigationLinks = $links_array;
		$htmltab->selectedLink = $sel_link;
		$htmltab->htmlContent = $GINO;
		return $htmltab->render();

	}

	private function listMenu($id) {
		
		$link_insert = "<a href=\"$this->_home?evt[$this->_instanceName-manageDoc]&amp;action=$this->_act_insert\">".$this->icon('insert', _("nuova voce"))."</a>";
		$htmlsection = new htmlSection(array('class'=>'admin', 'headerTag'=>'header', 'headerLabel'=>_("Menu"), 'headerLinks'=>$link_insert));
		
		$htmlsection->content = $this->renderMenuAdmin(0, $id);

		return $htmlsection->render();

	}
	
	private function renderMenuAdmin($parent=0, $s=0) {

		$GINO = (!$parent)? "<div id=\"menuContainer\">\n":"";
		
		$query = "SELECT id FROM ".menuVoice::$tbl_voices." WHERE instance='$this->_instance' AND parent='$parent' ORDER BY orderList";
		$a = $this->_db->selectquery($query);

		$sort = ($parent==$s && count($a)>1)? true:false;
		$GINO .= ($sort)? $this->jsSortLib():"";

		if(sizeof($a)>0) {
			$htmlList = new htmlList(array("class"=>"admin".($parent?" inside":""), "numItems"=>sizeof($a), "separator"=>false, "id"=>($sort?"sortContainer":"")));
			$GINO .= $htmlList->start();
			foreach($a as $b) {
				$voice = new menuVoice($b['id']);
				$link_modify = "<a href=\"$this->_home?evt[$this->_instanceName-manageDoc]&id={$voice->id}\">".pub::icon('modify')."</a>";
				$link_subvoice = "<a href=\"$this->_home?evt[$this->_instanceName-manageDoc]&id={$voice->id}&action={$this->_act_insert}&parent={$voice->id}\">".pub::icon('insert', _("nuova sottovoce"))."</a>";
				$selected = ($s==$voice->id)?true:false;
				$handle = ($sort)?"<div class=\"sortHandler\" title=\""._("ordina")."\"></div> ":"";
				$links = ($sort)? array($handle):array();
				$links[] = $link_modify;
				$links[] = $link_subvoice;
				$itemContent = $this->renderMenuAdmin($voice->id, $s);
				$itemContent .= "<span class=\"null\"></span>\n";
				$title = ($parent?"<img style=\"padding-bottom:4px\" src=\"".SITE_IMG."/list_mini.gif\" /> &#160;":"").htmlChars($voice->label);
				$GINO .= $htmlList->item($title, $links, $selected, true, $itemContent, "id$voice->id", "border sortable");
			}
			$GINO .= $htmlList->end();
		}

		$GINO .= (!$parent)? "</div>":"";
		return $GINO;
	}

	public function actionUpdateOrder() {
	
		$this->accessGroup($this->_group_1);

		$order = cleanVar($_POST, 'order', 'string', '');
		$items = explode(",", $order);
		$i=1;
		foreach($items as $item) {
			$voice = new menuVoice($item);
			$voice->orderList = $i;
			$voice->updateDbData();
			$i++;	
		}
	}

	private function info() {
	
		$htmlsection = new htmlSection(array('class'=>'admin', 'headerTag'=>'h1', 'headerLabel'=>_("Informazioni")));
		
		$GINO = "<p>"._("E' possibile inserire voci di menu con link interni o esterni al sito.")."</p>";
		
		$GINO .= "<p>"._("Il modulo di 'ricerca pagine e classi' permette di visualizzare i link interni.")."</p>";
	
		$GINO .= "<p>"._("Per ordinare le voci di menu trascinare l'elemento nella posizione desiderata.")."</p>";

		$GINO .= "<table class=\"menuInfo\">";
		$GINO .= "<tr>";
		$GINO .= "<th>"._("Opzioni")."</th>";
		$GINO .= "<th>"._("Descrizione")."</th>";
		$GINO .= "</tr>";
		$GINO .= "<tr>";
		$GINO .= "<td>"._("Titolo")."</td>";
		$GINO .= "<td>"._("<i>titolo mostrato sopra il menu.</i>")."</td>";
		$GINO .= "</tr>";
		$GINO .= "<tr>";
		$GINO .= "<td>"._("Titolo visibile")."</td>";
		$GINO .= "<td>"._("<i>mostrare o no il titolo.</i>")."</td>";
		$GINO .= "</tr>";
		$GINO .= "<tr>";
		$GINO .= "<td>"._("Voce ")."'$this->_opt_home'</td>";
		$GINO .= "<td>"._("<i>mostrare o no il link alla homepage.</i>")."</td>";
		$GINO .= "</tr>";
		$GINO .= "<tr>";
		$GINO .= "<td>"._("Voce ")."'$this->_opt_admin'</td>";
		$GINO .= "<td>"._("<i>mostrare o no il link all'area amministrativa.</i>")."</td>";
		$GINO .= "</tr>";
		$GINO .= "<tr>";
		$GINO .= "<td>"._("Voce ")."'$this->_opt_logout'</td>";
		$GINO .= "<td>"._("<i>mostrare o no il link per effettuare il logout.</i>")."</td>";
		$GINO .= "</tr>";
		$GINO .= "<tr>";
		$GINO .= "<td>"._("Menu orizzontale")."</td>";
		$GINO .= "<td>"._("<i>per scegliere se utilizzare il menu orizzontale o verticale.</i>")."</td>";
		$GINO .= "</tr>";
		$GINO .= "<tr>";
		$GINO .= "<td>"._("Evento apertura livelli")."</td>";
		$GINO .= "<td>"._("<i>si può scegliere se far aprire le sottovoci di menu sugli eventi over o click del mouse. Con il Browser Internet Explorer 6 e con la piattaforma iPod Touch/iPhone l'evento sarà di default sul click.</i>")."</td>";
		$GINO .= "</tr>";
		$GINO .= "<tr>";
		$GINO .= "<td>"._("Stile livelli con sottomenu")."</td>";
		$GINO .= "<td>"._("<i>le voci di menu che contengono sottovoci hanno associato uno stile che può essere sempre visibile o comparire solo sull'evento sopra citato.</i>")."</td>";
		$GINO .= "</tr>";
		$GINO .= "<tr>";
		$GINO .= "<td>"._("Briciole di pane")."</td>";
		$GINO .= "<td>"._("<i>metodo per tenere traccia della voce di menu selezionata. Il metodo testuale scrive il percorso alla voce selezionata. Il metodo grafico evidenzia graficamente tutte le voci fino a quella selezionata.</i>")."</td>";
		$GINO .= "</tr>";
		$GINO .= "</table>";

		$htmlsection->content = $GINO;

		return $htmlsection->render();
	}

	private function formMenuVoice($voice, $parent) {
		
		$buffer =  $voice->formVoice($this->_home."?evt[$this->_instanceName-actionMenuVoice]", $parent);
		$buffer .=  $this->searchModules();

		return $buffer;

	}

	public function actionMenuVoice() {
		
		$this->accessGroup($this->_group_1);

		$gform = new Form('gform', 'post', false);
		$gform->save('dataform');
		$req_error = $gform->arequired();

		$id = cleanVar($_POST, 'id', 'int', '');
		$parent = cleanVar($_POST, 'parent', 'int', '');
		$link = cleanVar($_POST, 'link', 'string', '');
		$voice = ($parent)?null:$id;

		$link_error_p = "action=$this->_action";
		if($id) $link_error_p .= "&id=$id";

		$link_error = $this->_home."?evt[$this->_instanceName-manageDoc]&$link_error_p";

		if($req_error > 0) 
			exit(error::errorMessage(array('error'=>1), $link_error));

		$voice = new menuVoice($voice);

		foreach($_POST as $k=>$v) {
			$voice->{$k} = $k;
		}
		$voice->instance = $this->_instance;
		$voice->link = $this->_plink->convertLink($link);
		if(!$id) $voice->initOrderList();

		$voice->updateDbData();

		EvtHandler::HttpCall($this->_home, $this->_instanceName.'-manageDoc', '');
	}
	
	private function formDelMenuVoice($voice) {
		
		$buffer =  $voice->formDelVoice($this->_home."?evt[$this->_instanceName-actionDelMenuVoice]");

		return $buffer;


	}

	public function actionDelMenuVoice() {
		
		$this->accessGroup($this->_group_1);

		$gform = new Form('gform', 'post', false);
		$gform->save('dataform');
		$req_error = $gform->arequired();

		$id = cleanVar($_POST, 'id', 'int', '');

		$link_error = $this->_home."?evt[$this->_instanceName-manageDoc]";
		if($req_error > 0 OR !$id)
			exit(error::errorMessage(array('error'=>9), $link_error));

		$voice = new menuVoice($id);
		$voice->deleteVoice();
		$voice->updateOrderList();

		EvtHandler::HttpCall($this->_home, $this->_instanceName.'-manageDoc', '');
	}

	public function searchModules(){

		$this->accessGroup($this->_group_1);

		$htmlsection = new htmlSection(array('class'=>'admin', 'headerTag'=>'h1', 'headerLabel'=>_("Ricerca moduli")));
		$gform = new Form('gform', 'post', false);
		$buffer = $this->jsSearchModulesLib();
		$buffer .= "<div style=\"text-align:center;\">\n";
		$buffer .= _("pagine").": <input type=\"text\" id=\"s_page\" name=\"s_page\" size=\"10\" />&nbsp; &nbsp; ";
		$buffer .= _("moduli").": <input type=\"text\" id=\"s_class\" name=\"s_class\" size=\"10\" />\n";
		$buffer .= "&nbsp; ";
		$buffer .= $gform->input('s_all', 'button', _("mostra tutti"), array("classField"=>"generic", "id"=>"s_all"));

		$buffer .= "</div>\n";
		
		$buffer .= "<div id=\"items_list\"></div>\n";
		
		$htmlsection->content = $buffer;

		return $htmlsection->render();
	}
	
	private function jsSortLib() {
	
		$GINO = "<script type=\"text/javascript\">\n";
		$GINO .= "function message() { alert('"._("Ordinamento effettuato con successo")."')}";
		$GINO .= "window.addEvent('load', function() { var menuSortables = new Sortables('#sortContainer', {
					constrain: false,
					handle: '.sortHandler',
					clone: false,
					revert: { duration: 500, transition: 'elastic:out' },
					onComplete: function() {
						var order = this.serialize(1, function(element, index) {
							return element.getProperty('id').replace('id', '');
						}).join(',');
						ajaxRequest('post', '$this->_home?pt[$this->_instanceName-actionUpdateOrder]', 'order='+order, null, {'callback':message});
       					}
				});
			})
		";
		$GINO .= "</script>";
		return $GINO;
	}

	private function jsSearchModulesLib() {
	
		$buffer = "<script type=\"text/javascript\">\n";
		$buffer .= "window.addEvent('load', function() {
					
					var myclass, mypage, all, active, other;
					var url = '".$this->_home."?pt[".$this->_instanceName."-printItemsList]';
					$$('#s_class', '#s_page').each(function(el) {
						el.addEvent('keyup', function(e) {
							active = el.getProperty('id');
							other = (active=='s_class')? 's_page':'s_class';
							$(other).setProperty('value', '');
							ajaxRequest('post', url, active+'='+$(active).value, 'items_list', {'load':'items_list', 'cache':true});
						})
					})	
			
					$('s_all').addEvent('click', function() {
							
							$$('#s_page', '#s_class').setProperty('value', '');
							ajaxRequest('post', url, 'all=all', 'items_list', {'load':'items_list', 'cache':true});
						}
					);

				});\n";
		$buffer .= "</script>\n";
		
		return $buffer;
	}
	
	public function printItemsList() {
	
		$this->accessGroup($this->_group_1);

		$class = cleanVar($_POST, 's_class', 'string', '');
		$page = cleanVar($_POST, 's_page', 'string', '');
		$all = cleanVar($_POST, 'all', 'string', '');
		
		if(!($class || $page || $all)) return false;
		
		$GINO = "<div style=\"max-height:300px;overflow:auto;\">";
		
		if(!empty($class)) {
			
			$query = "SELECT id, class as name, name as instance, label, role1 FROM ".$this->_tbl_module." WHERE label LIKE '$class%' AND type='class' AND masquerade='no' ORDER BY label";
			$a = $this->_db->selectquery($query);
			
			$query2 = "SELECT id, name, null as instance, label, role1 FROM ".$this->_tbl_module_app." WHERE label LIKE '$class%' AND type='class' AND masquerade='no' AND instance='no' ORDER BY label";
			$a2 = $this->_db->selectquery($query2);
			
			$j = array_merge($a,$a2);
			
			if(sizeof($j) > 0) $GINO .= $this->printItemsClass($j);
		}
		else if(!empty($page)) {
			
			$results_nav = array();
			$results = array();
			$final_results = array();
			
		 	$query = "SELECT p.item_id, m.role1, t.text FROM ".$this->_tbl_page." AS p, ".$this->_tbl_module." AS m, ".$this->_tbl_translation." AS t WHERE t.text LIKE '%$page%' AND t.language='".$this->_lng_nav."' AND t.tbl='".$this->_tbl_page."' AND t.field='title' AND t.tbl_id_value=p.item_id AND  p.module=m.id AND m.masquerade='no'";
			$a = $this->_db->selectquery($query);
			if(sizeof($a) > 0)
			{
				foreach($a AS $b)
				{
					$results_nav[$b['item_id']] = $b['text'];
				}
			}
				
			$query = "SELECT p.item_id, p.title, m.role1 FROM ".$this->_tbl_page." AS p, ".$this->_tbl_module." AS m WHERE p.title LIKE '%$page%' AND  p.module=m.id AND m.masquerade='no' AND p.item_id NOT IN(SELECT tbl_id_value FROM ".$this->_tbl_translation." WHERE tbl='".$this->_tbl_page."' AND field='title' AND language='".$this->_lng_nav."') ORDER BY title";
			$a = $this->_db->selectquery($query);
			
			if(sizeof($a) > 0)
			{	
				foreach($a AS $b)
				{
					$results[$b['item_id']] = $b['title'];
				}
			}
			
			foreach($results_nav as $key=>$value) $final_results[$key] = $value;
			foreach($results as $key=>$value) $final_results[$key] = $value;
			
			asort($final_results);
			
			if(sizeof($final_results)) $GINO .= $this->printItemsPage($final_results);
		}
		else if(!empty($all) && $all=='all') {
			
			$query = "SELECT p.item_id, m.role1 FROM ".$this->_tbl_page." AS p, ".$this->_tbl_module." AS m WHERE p.module=m.id AND m.masquerade='no' ORDER BY title";
			$results_ordered = $this->_trd->listItemOrdered($query, 'item_id', $this->_tbl_page, 'title', 'asc');
			if(sizeof($results_ordered) > 0)
			{
				$GINO .= $this->printItemsPage($results_ordered);
			}

			$query = "SELECT id, class as name, name as instance, label, role1 FROM ".$this->_tbl_module." WHERE type='class' AND masquerade='no' ORDER BY label";
			$a = $this->_db->selectquery($query);
			
			$query2 = "SELECT id, name, name as instance, label, role1 FROM ".$this->_tbl_module_app." WHERE type='class' AND masquerade='no' AND instance='no' ORDER BY label";
			$a2 = $this->_db->selectquery($query2);
			
			$j = array_merge($a,$a2);
			
			if(sizeof($j) > 0) $GINO .= $this->printItemsClass($j);
			
		}

		$GINO .= "</div>";

		return $GINO;
	}
	
	private function printItemsPage($array_search){
		
		$GINO = "<fieldset>";
		$GINO .= "<legend><b>"._("Pagine")."</b></legend>";
		
		$odd = true;
		foreach($array_search AS $key=>$value)
		{
			$class = ($odd)?"m_list_item_odd":"m_list_item_even";
			$page_id = $key;
			$page_title = htmlChars($value);
			$page_module = $this->_db->getFieldFromId($this->_tbl_page, 'module', 'item_id', $page_id);
			$page_role1 = $this->_db->getFieldFromId($this->_tbl_module, 'role1', 'id', $page_module);
			
			$GINO .= "<div class=\"$class\" style=\"padding:5px;\">";
			$GINO .= "<div class=\"left\"><span style=\"font-weight:bold\">".$page_title."</span><br/>";
			$GINO .= "<span style=\"color:#ff0000\">(".$this->_db->getFieldFromId($this->_tbl_user_role, 'name', 'role_id', $page_role1).")</span> - \n";
			$GINO .= "<span>index.php?evt[page-displayItem]&id=".$page_id."</span>";
			$GINO .= "</div>";
			$GINO .= "<div class=\"right\">";
			$GINO .= "<a href=\"#top\"><input class=\"generic\" type=\"button\" value=\""._("aggiungi dati")."\" onclick=\"
			$('link').setProperty('value', 'index.php?evt[page-displayItem]&id=".$page_id."');
			$('reference').setProperty('value', '".jsVar($page_title)."');
			$$('input[name=type]').each(function(it){if(it.getProperty('value') == 'int') it.setProperty('checked','checked')});
			$('role1').addEvent('change', updaterole('".$page_role1."'))\" /></a>\n";
			$GINO .= "</div>";
			$GINO .= "<div class=\"null\"></div>";
			$GINO .= "</div>";
			$odd = !$odd;
			
		}
		$GINO .= "</fieldset>";
		
		return $GINO;
	}
	
	/**
	 * Print Function's Class
	 *
	 * @param array $array_search		field: id,name,label,role1
	 * @return string
	 */
	private function printItemsClass($array_search){
		
		$GINO = "<fieldset>";
		$GINO .= "<legend><b>"._("Classi")."</b></legend>";
		
		$cnt = 0;
		
		$odd = true;
		foreach($array_search AS $value)
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
							$text = "$class_label - $description";
							
							$GINO .= "<div class=\"$class_style\" style=\"padding:5px;\">";
							$GINO .= "<div class=\"left\"><b>$class_label - $description</b><br/>";
							$GINO .= "<span style=\"color:#ff0000\">($role_name)</span> - \n";
							$GINO .= "index.php?evt[$instanceName-$func]</div>";
							$GINO .= "<div class=\"right\"><a href=\"#top\"><input class=\"generic\" type=\"button\" value=\""._("aggiungi dati")."\" onclick=\"
							$('link').setProperty('value', 'index.php?evt[".$instanceName."-".$func."]');
							$('reference').setProperty('value', '$text');
							$$('input[name=type]').each(function(it){if(it.getProperty('value') == 'int') it.setProperty('checked','checked')});
							$('role1').addEvent('change', updaterole('".$class_role."'))\" /></a></div>\n";
							$GINO .= "<div class=\"null\"></div>";
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
		
		return $GINO;
	}


}


?>
