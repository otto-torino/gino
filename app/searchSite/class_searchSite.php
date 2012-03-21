<?php

require_once(CLASSES_DIR.OS."class.search.php");

class searchSite extends AbstractEvtClass {

	private $_optionsValue;
	private $_options;
	public $_optionsLabels;
	
	private $_template, $_sys_mdl, $_inst_mdl;
	private $_title;
	private $_action, $_block;

	function __construct() {
	
		parent::__construct();

		$this->_instance = 0;
		$this->_instanceName = $this->_className;

		$this->setAccess();

		$this->_template = htmlChars($this->setOption('template', true));
		$this->_sys_mdl = $this->setOption('sys_mdl') ? $this->setOption('sys_mdl') : '';
		$this->_inst_mdl = $this->setOption('inst_mdl') ? $this->setOption('inst_mdl') : '';

		// Valori di default
		$this->_optionsValue = array(
		
		);
		
		$this->_options = new options($this->_className, $this->_instance);
		$this->_optionsLabels = array(
			"template"=>array("label"=>array(_("Template"), _("{FIELD}: campo di ricerca<br />{BUTTON}: pulsante di ricerca<br />{CHECK}: selezione moduli da ricercare")), "required"=>false),
			"sys_mdl"=>array("label"=>array(_("Moduli di sistema"), _("Inserire gli ID dei moduli che si vogliono includere nella ricerca separati da virgole")), "required"=>false),
			"inst_mdl"=>array("label"=>array(_("Moduli istanziabili"), _("Inserire gli ID dei moduli che si vogliono includere nella ricerca separati da virgole")), "required"=>false)
		);
		
		$this->_title = _("Ricerca nel sito");

		$this->_action = cleanVar($_REQUEST, 'action', 'string', '');
		$this->_block = cleanVar($_REQUEST, 'block', 'string', '');
	}

	public static function outputFunctions() {

		$list = array(
			"form" => array("label"=>_("Visualizza il form di ricerca"), "role"=>'1')
		);

		return $list;
	}

	public function manageSearchSite() {
	
		$this->accessGroup('ALL');
		
		$htmltab = new htmlTab(array("linkPosition"=>'right', "title"=>$this->_title));	
		$link_admin = "<a href=\"".$this->_home."?evt[$this->_className-manageSearchSite]&block=permissions\">"._("Permessi")."</a>";
		$link_options = "<a href=\"".$this->_home."?evt[$this->_className-manageSearchSite]&block=options\">"._("Opzioni")."</a>";
		$link_dft = "<a href=\"".$this->_home."?evt[".$this->_className."-manageSearchSite]\">"._("Gestione")."</a>";
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
			$GINO = $this->info();
		}
		
		$htmltab->navigationLinks = array($link_admin, $link_options, $link_dft);
		$htmltab->selectedLink = $sel_link;
		$htmltab->htmlContent = $GINO;
		return $htmltab->render();
	}

	public function form() {
	
		$gform = new Form('gform', 'post', true, array('tblLayout'=>false));
		$gform->load('dataform');

		$buffer = $this->scriptAsset("searchSite.js", "searchSiteJS", 'js');
		$buffer .= $this->scriptAsset("searchSite.css", "searchSiteCSS", 'css');
		$required = '';
		$buffer .= $gform->form($this->_home."?evt[".$this->_className."-results]", '', $required);
		$field = "<input type=\"text\" name=\"search_site\" id=\"search_site\"/>";
		$button = "<input type=\"submit\" id=\"search_site_submit\" value=\" \" />";
		
		$check = ($this->_sys_mdl || $this->_inst_mdl) ? "<input type=\"button\" id=\"search_site_check\" value=\" \" />" : '';
		if($this->_template) {
			$tpl = preg_replace("#{FIELD}#", $field, $this->_template);	
			$tpl = preg_replace("#{BUTTON}#", $button, $tpl);	
			$buffer .= preg_replace("#{CHECK}#", $check, $tpl);	
		}
		else {
			$buffer .= $check." ".$field." ".$button;
		}

		$buffer .= $this->checkOptions();
		$buffer .= $gform->cform();

		return $buffer;
	}

	private function checkOptions() {
	
		$buffer = "<div id=\"search_site_check_options\" style=\"display:none; position:absolute;text-align:left;\">";
		$buffer .= "<div>";
		$buffer .= "<p><b>"._("Ricerca solo in")."</b></p>";

		$i=1;
		if($this->_sys_mdl)
		{
		foreach(explode(",", $this->_sys_mdl) as $smid) {
			$label = $this->_db->getFieldFromId($this->_tbl_module_app, 'label', 'id', $smid);
			$buffer .= "<input type=\"checkbox\" name=\"sysmdl[]\" value=\"$smid\"> ".htmlChars($label);
			if($i++%3==0) $buffer .= "<br />";
		}
		}
		if($this->_inst_mdl)
		{
		foreach(explode(",", $this->_inst_mdl) as $mid) {
			$label = $this->_db->getFieldFromId($this->_tbl_module, 'label', 'id', $mid);
			$buffer .= "<input type=\"checkbox\" name=\"instmdl[]\" value=\"$mid\"> ".htmlChars($label);
			if($i++%3==0) $buffer .= "<br />";
		}
		}
		$buffer .= "</div>";
		$buffer .= "</div>";

		return $buffer;
	}

	public function results() {

		$keywords = cleanVar($_POST, 'search_site', 'string', '');
		$keywords = cutHtmlText($keywords, 500, '', true, false, true);
		$sysmdl = cleanVar($_POST, 'sysmdl', 'array', '');
		$instmdl = cleanVar($_POST, 'instmdl', 'array', '');

		$opt = (!count($sysmdl) && !count($instmdl)) ? false : true;
		$results = array();
		$buffer = '';

		foreach(explode(",", $this->_sys_mdl) as $smdlid) {
			if(!$opt || in_array($smdlid, $sysmdl)) {
				$classname = $this->_db->getFieldFromId($this->_tbl_module_app, 'name', 'id', $smdlid);
				if(method_exists($classname, "searchSite")) {
					$obj = new $classname();
					$data = $obj->searchSite();
					$searchObj = new search($data['table']);
					foreach($data['weight_clauses'] as $k=>$v) $data['weight_clauses'][$k]['value'] = $keywords;
					$results[$classname] = $searchObj->getSearchResults(db::instance(), $data['selected_fields'], $data['required_clauses'], $data['weight_clauses']);
				}
			}
		}
		foreach(explode(",", $this->_inst_mdl) as $mdlid) {
			if(!$opt || in_array($mdlid, $instmdl)) {
				$instancename = $this->_db->getFieldFromId($this->_tbl_module, 'name', 'id', $mdlid);
				$classname = $this->_db->getFieldFromId($this->_tbl_module, 'class', 'id', $mdlid);
				if(method_exists($classname, "searchSite")) {
					$obj = new $classname($mdlid);
					$data = $obj->searchSite();
					$searchObj = new search($data['table']);
					foreach($data['weight_clauses'] as $k=>$v) $data['weight_clauses'][$k]['value'] = $keywords;
					$results[$classname."||".$mdlid] = $searchObj->getSearchResults(db::instance(), $data['selected_fields'], $data['required_clauses'], $data['weight_clauses']);
				}
			}
		}

		$order_results = array();
		$final_results = array();
		
		if(count($results) > 0)
		{
			$i = 0;
			foreach($results as $classname=>$res) {
				foreach($res as $k=>$v) {
					$order_results[$i] = $v['relevance']*1000 + round($v['occurrences']);
					$final_results[$i] = array_merge(array("class"=>$classname), $v);	
					$i++;
				}	
			}

			arsort($order_results);
		}
		$tot_results = count($final_results);

		$title = _("Ricerca")." \"$keywords\"";
		$name_result = $tot_results == 1 ? _("risultato") : _("risultati");
		$right_title = $tot_results." ".$name_result;
		$htmlsection = new htmlSection(array('id'=>"searchSite",'class'=>'public', 'headerTag'=>'header', 'headerLabel'=>$title, 'headerLinks'=>$right_title));

		if($tot_results) {
			$htmlList = new htmlList(array("numItems"=>sizeof($final_results), "separator"=>true));

			$buffer .= $htmlList->start();
			foreach($order_results as $k=>$point) {
				$fr = $final_results[$k];
				if(preg_match("#(.*?)\|\|(\d+)#", $fr['class'], $matches)) $obj = new $matches[1]($matches[2]);
				else $obj = new $fr['class']();
				$buffer .= $htmlList->item($obj->searchSiteResult($fr), array(), false, true);
			}
			$buffer .= $htmlList->end();
		}
		else $buffer .= "<p class=\"message\">"._("La ricerca non ha prodotto risultati")."</p>";

		$htmlsection->content = $buffer;

		return $htmlsection->render();
	}

	public function info() {
	
		$htmlsection = new htmlSection(array('class'=>'admin', 'headerTag'=>'h1', 'headerLabel'=>_("Modulo di ricerca nel sito")));

		$buffer = "<p>"._("Definire un template nelle opzioni, altrimenti verrà utilizzato il template di default.")."</p>";
		$buffer .= "<p>"._("Nelle opzioni bisogna inserire gli ID dei moduli di sistema e non che si vogliono includere tra i ricercabili.")."</p>";

		$htmlsection->content = $buffer;

		return $htmlsection->render();
	}
}

?>
