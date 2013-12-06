<?php
/**
 * @file class_searchSite.php
 * @brief Contiene la classe searchSite
 * 
 * @copyright 2005 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */

/**
 * @brief Gestisce le ricerche full text sui contenuti dell'applicazione
 * 
 * @copyright 2005 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class searchSite extends Controller {

	private $_optionsValue;
	private $_options;
	public $_optionsLabels;
	
	private $_template, $_sys_mdl, $_inst_mdl;
	private $_title;
	private $_action, $_block;

	function __construct() {
	
		parent::__construct();

		$this->_template = htmlChars($this->setOption('template', true));
		$this->_sys_mdl = $this->setOption('sys_mdl') ? $this->setOption('sys_mdl') : '';
		$this->_inst_mdl = $this->setOption('inst_mdl') ? $this->setOption('inst_mdl') : '';

		// Valori di default
		$this->_optionsValue = array(
		
		);
		
		$this->_options = Loader::load('Options', array($this->_class_name, $this->_instance));
		$this->_optionsLabels = array(
			"template"=>array("label"=>array(_("Template"), _("{FIELD}: campo di ricerca<br />{BUTTON}: pulsante di ricerca<br />{CHECK}: selezione moduli da ricercare")), "required"=>false),
			"sys_mdl"=>array("label"=>array(_("Moduli di sistema"), _("Inserire gli ID dei moduli che si vogliono includere nella ricerca separati da virgole")), "required"=>false),
			"inst_mdl"=>array("label"=>array(_("Moduli istanziabili"), _("Inserire gli ID dei moduli che si vogliono includere nella ricerca separati da virgole")), "required"=>false)
		);
		
		$this->_title = _("Ricerca nel sito");

		$this->_action = cleanVar($_REQUEST, 'action', 'string', '');
		$this->_block = cleanVar($_REQUEST, 'block', 'string', '');
	}

	/**
	 * Elenco dei metodi che possono essere richiamati dal menu e dal template
	 * 
	 * @return array
	 */
	public static function outputFunctions() {

		$list = array(
			"form" => array("label"=>_("Visualizza il form di ricerca"), "permissions"=>array())
		);

		return $list;
	}

	/**
	 * Interfaccia amministrativa per la gestione delle ricerche
	 * 
	 * @return string
	 */
	public function manageSearchSite() {
	
		$this->requirePerm('can_admin');
		
		$link_options = "<a href=\"".$this->_home."?evt[$this->_class_name-manageSearchSite]&block=options\">"._("Opzioni")."</a>";
		$link_dft = "<a href=\"".$this->_home."?evt[".$this->_class_name."-manageSearchSite]\">"._("Informazioni")."</a>";
		$sel_link = $link_dft;

		if($this->_block == 'options') {
			$GINO = $this->manageOptions();		
			$sel_link = $link_options;
		}
		else {
			$GINO = $this->info();
		}
		
    $dict = array(
      'title' => $this->_title,
      'links' => array($link_options, $link_dft),
      'selected_link' => $sel_link,
      'content' => $GINO
    );

    $view = new view();
    $view->setViewTpl('tab');

    return $view->render($dict);
	}

	/**
	 * Form di ricerca
	 * 
	 * @return string
	 */
	public function form() {

		$gform = Loader::load('Form', array('search_site_form', 'post', true, array('tblLayout'=>false)));
		$gform->load('dataform');

		$registry = registry::instance();
		$registry->addCss($this->_class_www."/searchSite.css");
		$registry->addJs($this->_class_www."/searchSite.js");
		
		$required = '';
		$buffer = $gform->open($this->_home."?evt[".$this->_class_name."-results]", '', $required);
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
		$buffer .= $gform->close();

    $view = new view(null, 'section');
    $dict = array(
      'title' => _("Ricerca nel sito"),
      'content' => $buffer
    );

    return $view->render($dict);
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

	/**
	 * Stampa i risultati di una ricerca
	 * 
	 * La ricerca viene effettuata sui moduli nei quali sono stati definiti i metodi @a searchSite() e @a searchSiteResult()
	 * 
	 * @see search::getSearchResults()
	 * @return string
	 */
	public function results() {

    Loader::import('class', 'Search');

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

		if($tot_results) {
      $buffer .= "<div class=\"search-results\">";
			foreach($order_results as $k=>$point) {
				$fr = $final_results[$k];
				if(preg_match("#(.*?)\|\|(\d+)#", $fr['class'], $matches)) $obj = new $matches[1]($matches[2]);
				else $obj = new $fr['class']();
				$buffer .= $obj->searchSiteResult($fr);
			}
			$buffer .= "</div>";
		}
		else $buffer .= "<p class=\"message\">"._("La ricerca non ha prodotto risultati")."</p>";


    $view = new view(null, 'section');
    $dict = array(
      'title' => $title,
      'header_links' => $right_title,
      'content' => $buffer
    );

    return $view->render($dict);
	}

	public function info() {
	
		$buffer = "<p>"._("Il modulo mette a disposizione un'interfaccia di ricerca nel sito.")."</p>";
		$buffer .= "<p>"._("Nelle <b>Opzioni</b> è possibile definire un template sostitutivo di quello di default, e indicare i valori ID dei moduli (di sistema e non) che si vogliono includere nella ricerca.")."</p>";
		$buffer .= "<p>"._("Per poter funzionare occorre")."</p>";
		$buffer .= "<ul>";
		$buffer .= "<li>"._("caricare sul database la funzione <b>replace_ci</b> (vedi INSTALL.TXT)")."</li>";
		$buffer .= "<li>"._("nei moduli indicati nella ricerca occorre definire e argomentare i metodi <b>searchSite</b> e <b>searchSiteResult</b>")."</li>";
		$buffer .= "</ul>";

    $view = new view(null, 'section');
    $dict = array(
      'title' => _("Modulo di ricerca nel sito"),
      'class' => 'admin',
      'content' => $buffer
    );

    return $view->render($dict);
	}
}

?>
