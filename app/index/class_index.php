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
class index extends AbstractEvtClass{

	private $_page;
	
	function __construct(){

		parent::__construct();

	}
	
	/*
	 * Funzioni che possono essere richiamate da menu e messe all'interno del template;
	 * array ("function" => array("label"=>"description", "role"=>"privileges"))
	 */
	public static function outputFunctions() {

		$list = array(
			"homePage" => array("label"=>_("Home page di default di GINO CMS"), "role"=>'1'),
			"admin_page" => array("label"=>_("Home page amministrazione"), "role"=>'2')
		);

		return $list;
	}

	public function homePage() {

		$htmlsection = new htmlSection(array('class'=>'public', 'headerTag'=>'header', 'headerLabel'=>_("GINO CMS - Home page")));
		$GINO = _("Home page di GINO");
		$GINO .= "<p>"._("Benvenuto in GINO, il CMS open source sviluppato da ")."<a href=\"http://www.otto.to.it\">Otto srl</a></p>";
		$GINO .= "<p>"._("Per informazioni riguardo all'utilizzo di GINO CMS ti consigliamo di consultare la guida all'indirizzo seguente:")."<br/>";
		$GINO .= "<a href=\"".WIKI."\">gino.wiki</a>";
		$GINO .= "</p>";
		
		$GINO .= "<p>"._("Per incrementare le funzionalità della tua installazione ti invitiamo a visitare il repository ufficiale di moduli per GINO CMS:")."<br/>";
		$GINO .= "<a href=\"".REPOSITORY."\">repository ufficiale</a>";
		$GINO .= "</p>";

		$htmlsection->content = $GINO;

		return $htmlsection->render();

	}

	public function auth_page(){

		$registration = cleanVar($_GET, 'reg', 'int', '');
		
		if($registration == 1) $control = true; else $control = false;
		
		$GINO = "<div id=\"section_indexAuth\" class=\"section\">";

		$GINO .= "<p>"._("Per procedere è necessario autenticarsi.")."</p>";
		
		$func = new sysfunc();
		$GINO .= $func->Autenticazione($control, $this->_className);
		$GINO .= "</div>";
		
		return $GINO;
	}

	public function admin_page(){

		if(!$this->_access->getAccessAdmin()) {
			$_SESSION['auth_redirect'] = "$this->_home?evt[".$this->_className."-admin_page]";
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

	public function sysModulesManageArray() {

		if(!$this->_access->getAccessAdmin()) {
			return array();
		}

		$list = array();
		$query = "SELECT id, label, name, description FROM ".$this->_tbl_module_app." WHERE masquerade='no' AND instance='no' ORDER BY order_list";
		$a = $this->_db->selectquery($query);
		if(sizeof($a)>0) {
			foreach($a as $b) {
				if($this->_access->AccessVerifyGroupIf($b['name'], 0, '', 'ALL') && method_exists($b['name'], 'manage'.ucfirst($b['name'])))
					$list[$b['id']] = array("label"=>$this->_trd->selectTXT(TBL_MODULE_APP, 'label', $b['id']), "name"=>$b['name'], "description"=>$this->_trd->selectTXT(TBL_MODULE_APP, 'description', $b['id']));
			}
		}

		return $list;
	
	}
	
	public function modulesManageArray() {

		if(!$this->_access->getAccessAdmin()) {
			return array();
		}

		$list = array();
		$query = "SELECT id, label, name, class, description FROM ".TBL_MODULE." WHERE masquerade='no' AND type='class' ORDER BY label";
		$a = $this->_db->selectquery($query);
		if(sizeof($a)>0) {
			foreach($a as $b) {
				if($this->_access->AccessVerifyGroupIf($b['class'], $b['id'], '', 'ALL') && method_exists($b['class'], 'manageDoc'))
					$list[$b['id']] = array("label"=>$this->_trd->selectTXT(TBL_MODULE, 'label', $b['id']), "name"=>$b['name'], "class"=>$b['class'], "description"=>$this->_trd->selectTXT(TBL_MODULE, 'description', $b['id']));
			}
		}

		return $list;
	
	}


		
	

}
?>
