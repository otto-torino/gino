<?php
/**
 * @file class_index.php
 * @brief Contiene la classe index
 * 
 * @copyright 2005 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */

/**
 * @brief 
 * 
 * @copyright 2005 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class index extends Controller{

	private $_page;
	
	function __construct(){

		parent::__construct();

	}
	
	/**
	 * Elenco dei metodi che possono essere richiamati dal menu e dal template
	 * 
	 * @return array
	 */
	public static function outputFunctions() {

		$list = array(
			"admin_page" => array("label"=>_("Home page amministrazione"), "role"=>'2')
		);

		return $list;
	}

	/**
	 * Pagina di autenticazione
	 * 
	 * @see sysfunc::tableLogin()
	 * @return string
	 */
	public function auth_page(){

		$registration = cleanVar($_GET, 'reg', 'int', '');
		
		if($registration == 1) $control = true; else $control = false;
		
		$GINO = "<div id=\"section_indexAuth\" class=\"section\">";

		$GINO .= "<p>"._("Per procedere è necessario autenticarsi.")."</p>";
		
		$func = new sysfunc();
		$GINO .= $func->tableLogin($control, $this->_className);
		$GINO .= "</div>";
		
		return $GINO;
	}

	/**
	 * Home page amministrazione
	 * 
	 * @return string
	 */
	public function admin_page(){

		if(!$this->_auth->getAccessAdmin()) {
			$this->session->auth_redirect = "$this->_home?evt[".$this->_className."-admin_page]";
			EvtHandler::HttpCall($this->_home, $this->_className.'-auth_page', '');
		}

		$buffer = '';
		$sysMdls = $this->sysModulesManageArray();
		$mdls = $this->modulesManageArray();
		if(count($sysMdls)) {
		
			$htmlsection = new htmlSection(array('class'=>'admin', 'headerTag'=>'h1', 'headerLabel'=>_("Amministrazione sistema")));
		
			$GINO = "<table class=\"sysMdlList\">";
			foreach($sysMdls as $sm) {
				$GINO .= "<tr>";
				$GINO .= "<td class=\"mdlLabel\"><a href=\"$this->_home?evt[".$sm['name']."-manage".ucfirst($sm['name'])."]\">".htmlChars($sm['label'])."</a></td>";
				$GINO .= "<td class=\"mdlDescription\">".htmlChars($sm['description'])."</td>";
				$GINO .= "</tr>";
			}
			$GINO .= "</table>\n";
			$htmlsection->content = $GINO;
		
			$buffer = $htmlsection->render();
		}	
		if(count($mdls)) {
		
			$htmlsection = new htmlSection(array('class'=>'admin', 'headerTag'=>'h1', 'headerLabel'=>_("Amministrazione moduli")));

			$GINO = "<table class=\"sysMdlList\">";
			foreach($mdls as $m) {
				$GINO .= "<tr>";
				$GINO .= "<td class=\"mdlLabel\"><a href=\"$this->_home?evt[".$m['name']."-manageDoc]\">".htmlChars($m['label'])."</a></td>";
				$GINO .= "<td class=\"mdlDescription\">".htmlChars($m['description'])."</td>";
				$GINO .= "</tr>";
			}
			$GINO .= "</table>\n";
			$htmlsection->content = $GINO;

			$buffer .= $htmlsection->render();

		}
		return $buffer;
	}

	/**
	 * Elenco dei moduli di sistema visualizzabili nell'area amministrativa
	 * 
	 * @return array
	 */
	public function sysModulesManageArray() {

		if(!$this->_auth->getAccessAdmin()) {
			return array();
		}

		$list = array();
		$query = "SELECT id, label, name, description FROM ".TBL_MODULE_APP." WHERE masquerade='no' AND instance='no' ORDER BY order_list";
		$a = $this->_db->selectquery($query);
		if(sizeof($a)>0) {
			foreach($a as $b) {
				if($this->_auth->AccessVerifyGroupIf($b['name'], 0, '', 'ALL') && method_exists($b['name'], 'manage'.ucfirst($b['name'])))
					$list[$b['id']] = array("label"=>$this->_trd->selectTXT(TBL_MODULE_APP, 'label', $b['id']), "name"=>$b['name'], "description"=>$this->_trd->selectTXT(TBL_MODULE_APP, 'description', $b['id']));
			}
		}

		return $list;
	}
	
	/**
	 * Elenco dei moduli non di sistema visualizzabili nell'area amministrativa
	 * 
	 * @return array
	 */
	public function modulesManageArray() {

		if(!$this->_auth->getAccessAdmin()) {
			return array();
		}

		$list = array();
		$query = "SELECT id, label, name, class, description FROM ".TBL_MODULE." WHERE masquerade='no' AND type='class' ORDER BY label";
		$a = $this->_db->selectquery($query);
		if(sizeof($a)>0) {
			foreach($a as $b) {
				if($this->_auth->AccessVerifyGroupIf($b['class'], $b['id'], '', 'ALL') && method_exists($b['class'], 'manageDoc'))
					$list[$b['id']] = array("label"=>$this->_trd->selectTXT(TBL_MODULE, 'label', $b['id']), "name"=>$b['name'], "class"=>$b['class'], "description"=>$this->_trd->selectTXT(TBL_MODULE, 'description', $b['id']));
			}
		}

		return $list;
	}
}
?>
