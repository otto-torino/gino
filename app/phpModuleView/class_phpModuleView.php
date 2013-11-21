<?php
/**
 * @file class_phpModuleView.php
 * @brief Contiene la classe phpModuleView
 * 
 * @copyright 2005 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */

// Include il file class.PhpModule.php
require_once('class.PhpModule.php');

/**
 * @brief Permette la creazione di moduli di classe in grado di eseguire codice php completamente personalizzabile
 * 
 * Procedura:
 *   - si crea un modulo di classe selezionando la classe phpModuleView
 *   - il modulo diventa visibile nella sezione moduli
 *   - selezionando il modulo creato si accede alle funzionalità della classe phpModuleView
 *   - nella sezione @a Contenuto è possibile scrivere direttamente il codice php
 * 
 * Per precauzione tutte le funzioni di php che permettono di eseguire programmi direttamente sulla macchina sono vietate.
 * Nel caso in cui venisse rilevata la presenza di una di queste funzioni il codice non verrebbe eseguito e l'output risultante sarebbe nullo.
 * 
 * Per una corretta integrazione dell'output prodotto all'interno del layout del sito, si consiglia di non utilizzare le funzioni per la stampa diretta @a echo e @a print, ma di immagazzinare tutto l'output all'interno della variabile @a $buffer, che verrà stampata all'interno del layout.
 * Si consiglia di fare molta attenzione perché nonostante l'accesso alle funzionalità più pericolose del php sia proibito, si ha un controllo completo sulle variabili, ed in caso di cattivo uso del modulo si potrebbe seriamente compromettere la visualizzazione del modulo o dell'intero sito.
 * 
 * @copyright 2005 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class phpModuleView extends AbstractEvtClass {
	
	protected $_instance, $_instanceName;

	private $_tbl_opt, $_tbl_usr;
	private $_blackList;

	private $_title, $_title_visible_home, $_title_visible_page;
	
	private $_options;
	public $_optionsLabels;
	
	private $_group_1;
	
	private $_action, $_block;

	function __construct($mdlId){
		
		parent::__construct();

		$this->_instance = $mdlId;
		$this->_instanceName = $this->_db->getFieldFromId($this->_tbl_module, 'name', 'id', $this->_instance);
		$this->_instanceLabel = $this->_db->getFieldFromId($this->_tbl_module, 'label', 'id', $this->_instance);

		$this->setAccess();
		$this->setGroups();

		$this->_tbl_opt = "php_module_opt";
		$this->_tbl_usr = "php_module_usr";

		// options
		$this->_title = htmlChars($this->setOption('title', true));
		$this->_title_visible = htmlChars($this->setOption('title_vis'));

		// the second paramether will be the class instance
		$this->_options = new options($this->_className, $this->_instance);
		$this->_optionsLabels = array(
		"title"=>_("Titolo"),
		"title_vis"=>_("Titolo visibile")
		);

		$this->_action = cleanVar($_REQUEST, 'action', 'string', '');
		$this->_block = cleanVar($_REQUEST, 'block', 'string', '');

		$this->_blackList = array("exec", "passthru", "proc_close", "proc_get_status", "proc_nice", "proc_open", "proc_terminate", "shell_exec", "system");
	}
	
	/**
	 * Fornisce i riferimenti della classe, da utilizzare nel processo di creazione e di eliminazione di una istanza 
	 * 
	 * @return array
	 */
	public function getClassElements() {

		return array("tables"=>array('php_module', 'php_module_opt', 'php_module_grp', 'php_module_usr'),
			"css"=>array('phpModule.css'),
			"folderStructure"=>array(
				CONTENT_DIR.OS.'phpModule'=> null
			)
		);
	}

	/**
	 * Eliminazione di una istanza
	 * 
	 * @return boolean
	 */
	public function deleteInstance() {

		$this->accessGroup('');

		$phpMdl = new PhpModule($this->_instance, $this->_instanceName);
		$phpMdl->deleteDbData();
		
		/*
		 * delete record and translation from table php_module_opt
		 */
		$opt_id = $this->_db->getFieldFromId($this->_tbl_opt, "id", "instance", $this->_instance);
		language::deleteTranslations($this->_tbl_opt, $opt_id);
		
		$query = "DELETE FROM ".$this->_tbl_opt." WHERE instance='$this->_instance'";	
		$result = $this->_db->actionquery($query);

		/*
		 * delete group users association
		 */
		$query = "DELETE FROM ".$this->_tbl_usr." WHERE instance='$this->_instance'";	
		$result = $this->_db->actionquery($query);

		$classElements = $this->getClassElements();
		foreach($classElements['css'] as $css) {
			@unlink(APP_DIR.OS.$this->_className.OS.baseFileName($css)."_".$this->_instanceName.".css");
		}
		foreach($classElements['folderStructure'] as $fld=>$fldStructure) {
			$this->deleteFileDir($fld.OS.$this->_instanceName, true);
		}

		return $result;
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
			"viewList" => array("label"=>_("Visualizzazione modulo"), "role"=>'1')
		);

		return $list;
	}

	/**
	 * Visualizzazione del modulo
	 * 
	 * @see $_access_base
	 * @return string
	 */
	public function viewList() {

		$this->accessType($this->_access_base);
		
		$registry = registry::instance();

		$phpMdl = new PhpModule($this->_instance, $this->_instanceName);
		
		$htmlsection = new htmlSection(array('id'=>"phpModuleView_".$this->_instanceName, 'class'=>'public', 'headerTag'=>'h1', 'headerLabel'=>$this->_title, 'headerClass'=>($this->_title_visible ? '' : 'hidden')));
		$registry->addCss($this->_class_www."/phpModule_".$this->_instanceName.".css");
		
		$rexpf = array();
		foreach($this->_blackList as $fc) {
			$rexpf[] = $fc."\(.*?\)";
		}
		$rexp = "#".implode("|", $rexpf)."#";
		if(preg_match($rexp, $phpMdl->content)) {
			$buffer = '';
		}
		else eval($phpMdl->content);

		$htmlsection->content = $buffer;

		return $htmlsection->render();
	}

	/**
	 * Interfaccia amministrativa per la gestione dei moduli di classe 'phpModuleView'
	 * 
	 * @return string
	 */
	public function manageDoc() {

		$this->accessGroup('ALL');

		$phpMdl = new PhpModule($this->_instance, $this->_instanceName);

		$htmltab = new htmlTab(array("title"=>$this->_instanceLabel, "linkPosition"=>'right'));	
		$link_admin = "<a href=\"".$this->_home."?evt[$this->_instanceName-manageDoc]&block=permissions\">"._("Permessi")."</a>";
		$link_css = "<a href=\"".$this->_home."?evt[$this->_instanceName-manageDoc]&block=css\">"._("CSS")."</a>";
		$link_options = "<a href=\"".$this->_home."?evt[$this->_instanceName-manageDoc]&block=options\">"._("Opzioni")."</a>";
		$link_edit = "<a href=\"".$this->_home."?evt[".$this->_instanceName."-manageDoc]&amp;action=".$this->_act_modify."\">"._("Contenuto")."</a>";
		$link_info = "<a href=\"".$this->_home."?evt[".$this->_instanceName."-manageDoc]\">"._("Informazioni")."</a>";
		$sel_link = $link_info;
		
		if($this->_access->AccessVerifyGroupIf($this->_className, $this->_instance, '', ''))
			$links_array = array($link_admin, $link_css, $link_options, $link_edit, $link_info);
		else $links_array = array($link_css, $link_options, $link_edit, $link_info);

		$htmltab->navigationLinks = $links_array;

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

			if($this->_action == 'save') {
				$phpMdl->actionPhpModule();
				exit;
			}
			if($this->_action == $this->_act_modify) {
				$sel_link = $link_edit;
				$form = $phpMdl->formPhpModule();
			}
			else
				$form = $this->info();

			$GINO = $form;
		}

		$htmltab->selectedLink = $sel_link;
		$htmltab->htmlContent = $GINO;
		return $htmltab->render();
	}

	private function info(){

		$htmlsection = new htmlSection(array('class'=>'admin', 'headerTag'=>'h1', 'headerLabel'=>_("Informazioni")));
		$buffer = "<p>"._("Il modulo permette di eseguire codice php completamente personalizzabile, e di visualizzare l'output prodotto. Per precauzione tutte le funzioni di php che permettono di eseguire programmi direttamente sulla macchina sono vietate. Nel caso in cui venisse rilevata la presenza di una di queste funzioni il codice non verrebbe eseguito e l'output risultante sarebbe nullo.")."</p>\n";
		$buffer .= "<p>"._("Per una corretta integrazione dell'output prodotto all'interno del layout del sito, si consiglia di <b>non</b> utilizzare le funzioni per la stampa diretta <b>echo</b> e <b>print</b>, ma di immagazzinare tutto l'output all'interno della variabile <b>\$buffer</b>, che verrà stampata all'interno del layout.")."</p>\n";
		$buffer .= "<p>"._("Si consiglia di fare molta attenzione perché nonostante l'accesso alle funzionalità più pericolose del php sia proibito, si ha un controllo completo sulle variabili, ed in caso di cattivo uso del modulo si potrebbe seriamente compromettere la visualizzazione del modulo o dell'intero sito.")."</p>\n";
		
		$htmlsection->content = $buffer;

		return $htmlsection->render();
	}
}
?>
