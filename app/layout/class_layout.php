<?php

class layout extends AbstractEvtClass {

	protected $_instance, $_instanceName;
	private $_tbl_skin, $_tbl_css;
	private $_relativeUrl;
	private $_template;
	private $_css;

	private $_action, $_block;

	function __construct($queryString=null) {

		parent::__construct();

		$this->_instance = 0;
		$this->_instanceName = $this->_className;

		$this->setAccess();

		$this->_tbl_skin = 'sys_layout_skin';
		$this->_tbl_css = 'sys_layout_css';

		$this->_action = cleanVar($_REQUEST, 'action', 'string', '');
		$this->_block = cleanVar($_REQUEST, 'block', 'string', 'skin');
		if(empty($this->_block)) $this->_block = 'css';

	}

	public function manageLayout() {

		$this->accessGroup('ALL');

		$htmltab = new htmlTab(array("linkPosition"=>'right', "title"=>_("Layout")));	
		$link_admin = "<a href=\"".$this->_home."?evt[$this->_className-manageLayout]&block=permissions\">"._("Permessi")."</a>";
		$link_dft = "<a href=\"".$this->_home."?evt[".$this->_className."-manageLayout]\">"._("Gestione")."</a>";
		$sel_link = $link_dft;

		if($this->_block == 'permissions' && $this->_access->AccessVerifyGroupIf($this->_className, $this->_instance, '', '')) {
			$buffer = sysfunc::managePermissions(null, $this->_className); 
			$sel_link = $link_admin;
		}
		else {
			if($this->_block=='template' && $this->_action=='mngtpl') {
				$id = cleanVar($_POST, 'id', 'int', '');
				$css = cleanVar($_POST, 'css', 'int', '');
				$tplObj = new template($id);
				$cssObj = new css('layout', array('id'=>$css));
				return $tplObj->manageTemplate($cssObj, $id);
			}
			elseif($this->_block=='template' && $this->_action=='mngblocks') {
				$id = cleanVar($_POST, 'id', 'int', '');
				$tplObj = new template($id);
				return $tplObj->tplBlockForm(); 
			}
			elseif($this->_block=='template' && $this->_action=='addblocks') {
				$id = cleanVar($_POST, 'id', 'int', '');
				$tplObj = new template($id);
				return $tplObj->addBlockForm(); 
			}
			elseif($this->_block=='template' && $this->_action=='copytpl') {
				$tplObj = new template(null);
				return $tplObj->actionCopyTemplate();
			}

			$buffer = "<div class=\"vertical_1\">";
			$buffer .= $this->layoutList();
			$buffer .= "</div>";

			$buffer .= "<div class=\"vertical_2\">\n";
			if($this->_action == $this->_act_insert || $this->_action == $this->_act_modify) $buffer .= $this->formBlock();
			elseif($this->_action == $this->_act_copy) $buffer .= $this->formCopyBlock();
			elseif($this->_action == $this->_act_delete) $buffer .= $this->formDelBlock();
			else $buffer .= $this->info();
			$buffer .= "</div>\n";

			$buffer .= "<div class=\"null\"></div>\n";
		}
		
		$htmltab->navigationLinks = $this->_access->AccessVerifyGroupIf($this->_className, $this->_instance, '', '')
			? array($link_admin, $link_dft)
			: array($link_dft);
		$htmltab->selectedLink = $sel_link;
		$htmltab->htmlContent = $buffer;
		return $htmltab->render();
	}

	private function layoutList() {

		$link_insert = "<a href=\"$this->_home?evt[$this->_className-manageLayout]&block=$this->_block&action=$this->_act_insert\">".pub::icon('insert')."</a>";

		$class_sel = "class=\"selected\"";
		$title = "[ <a href=\"$this->_home?evt[$this->_className-manageLayout]&block=css\" ".(($this->_block=='css')?$class_sel:"").">css</a> ]";
		$title .= " [ <a href=\"$this->_home?evt[$this->_className-manageLayout]&block=template\" ".(($this->_block=='template')?$class_sel:"").">template</a> ]";
		$title .= " [ <a href=\"$this->_home?evt[$this->_className-manageLayout]&block=skin\" ".(($this->_block=='skin')?$class_sel:"").">skin</a> ]";
		$htmlsection = new htmlSection(array('class'=>'admin', 'headerTag'=>'header', 'headerLabel'=>$title, 'headerLinks'=>$link_insert));

		if($this->_block == 'skin') $buffer = $this->skinList();
		elseif($this->_block == 'template') $buffer = $this->templateList();
		elseif($this->_block == 'css') $buffer = $this->cssList();
		
		$htmlsection->content = $buffer;

		return $htmlsection->render();

	}

	private function skinList() {
	
		$sel_id = cleanVar($_GET, 'id', 'int', '');
		$skin_list = skin::getAll();
		if(count($skin_list)) {
			$htmlList = new htmlList(array("numItems"=>sizeof($skin_list), "separator"=>false, "id"=>'priorityList'));
			$buffer = $htmlList->start();
			foreach($skin_list as $skin) {
				$selected = ($skin->id == $sel_id)?true:false;
				$link_modify = "<a href=\"$this->_home?evt[$this->_className-manageLayout]&block=skin&id={$skin->id}&action=$this->_act_modify\">".pub::icon('modify')."</a>";
				$link_delete = "<a href=\"$this->_home?evt[$this->_className-manageLayout]&block=skin&id={$skin->id}&action=$this->_act_delete\">".pub::icon('delete')."</a>";
				$link_sort = "<div class=\"orderPriority\" style=\"float:left;width:20px;height:20px;background:url('img/ico_sort.gif');cursor:move;margin-right:3px;\"></div>";
				$buffer .= $htmlList->item(htmlChars($skin->ml('label')), array($link_sort, $link_delete, $link_modify), $selected, true, null, "id$skin->id", "sortable");
			}	
			$buffer .= $htmlList->end();
			$buffer .= "<script>";
			$buffer .= "function message() { alert('"._("Ordinamento effettuato con successo")."')}";
			$buffer .= "var prioritySortables = new Sortables($('priorityList'), {
						constrain: false,
						clone: true,
						handle: '.orderPriority',
						onComplete: function() {
							var order = this.serialize(1, function(element, index) {
								return element.getProperty('id').replace('id', '');
							}).join(',');
							ajaxRequest('post', '$this->_home?pt[$this->_className-actionUpdateSkinOrder]', 'order='+order, null, {'callback':message});
       						}

			})";
			$buffer .= "</script>";
		}
		else {
			$buffer = "<p>"._("Non risultano skin registrati")."</p>\n";
		}

		return $buffer;

	}
	
	private function templateList() {
	
		$sel_id = cleanVar($_GET, 'id', 'int', '');
		$tpl_list = template::getAll();
		if(count($tpl_list)) {
			$htmlList = new htmlList(array("numItems"=>count($tpl_list), "separator"=>true));
			$buffer = $htmlList->start();
			foreach($tpl_list as $tpl) {
				$selected = ($tpl->id == $sel_id)?true:false;
				$link_modify = "<a href=\"$this->_home?evt[$this->_className-manageLayout]&block=template&id={$tpl->id}&action=$this->_act_modify\">".pub::icon('modify')."</a>";
				$link_copy = "<a href=\"$this->_home?evt[$this->_className-manageLayout]&block=template&id={$tpl->id}&action=$this->_act_copy\">".pub::icon('duplicate', _("crea una copia"))."</a>";
				$link_delete = "<a href=\"$this->_home?evt[$this->_className-manageLayout]&block=template&id={$tpl->id}&action=$this->_act_delete\">".pub::icon('delete')."</a>";
				$buffer .= $htmlList->item(htmlChars($tpl->ml('label')), array($link_delete, $link_copy, $link_modify), $selected, true, '('.$tpl->filename.')');
			}
			$buffer .= $htmlList->end();
		}
		else {
			$buffer = "<p>"._("Non risultano template registrati")."</p>\n";
		}

		return $buffer;
	}

	private function cssList() {
	
		$sel_id = cleanVar($_GET, 'id', 'int', '');
		$css_list = css::getAll();
		if(count($css_list)) {
			$htmlList = new htmlList(array("numItems"=>count($css_list), "separator"=>true));
			$buffer = $htmlList->start();
			foreach($css_list as $css) {
				$selected = ($css->id == $sel_id)?true:false;
				$link_modify = "<a href=\"$this->_home?evt[$this->_className-manageLayout]&block=css&id={$css->id}&action=$this->_act_modify\">".pub::icon('modify')."</a>";
				$link_delete = "<a href=\"$this->_home?evt[$this->_className-manageLayout]&block=css&id={$css->id}&action=$this->_act_delete\">".pub::icon('delete')."</a>";
				$buffer .= $htmlList->item(htmlChars($css->ml('label')), array($link_delete, $link_modify), $selected, true);
			}	
			$buffer .= $htmlList->end();
		}
		else {
			$buffer = "<p>"._("Non risultano css registrati")."</p>\n";
		}
		
		return $buffer;
	}

	private function formBlock() {
	
		$id = cleanVar($_GET, 'id', 'int', '');

		if($this->_block=='skin') {
			$skinObj = new skin($id);
			return $skinObj->formSkin();
		}
		elseif($this->_block=='template') {
			$tplObj = new template($id);
			return $tplObj->formTemplate();
		}
		elseif($this->_block=='css') {
			$cssObj = new css('layout', array('id'=>$id));
			return $cssObj->formCssLayout();
		}
	}
	
	private function formCopyBlock() {
	
		$id = cleanVar($_GET, 'id', 'int', '');
		
		if($this->_block=='skin') {
			return null;
		}
		elseif($this->_block=='template') {
			$tplObj = new template($id);
			return $tplObj->formCopyTemplate();
		}
		elseif($this->_block=='css') {
			return null;
		}
	}
	
	private function formDelBlock() {
	
		$id = cleanVar($_GET, 'id', 'int', '');

		if($this->_block=='css') {
			$cssObj = new css('layout', array('id'=>$id));
			return $cssObj->formDelCssLayout();
		}
		elseif($this->_block=='template') {
			$tplObj = new template($id);
			return $tplObj->formDelTemplate();
		}
		elseif($this->_block=='skin') {
			$tplObj = new skin($id);
			return $tplObj->formDelSkin();
		}
	}

	private function info() {

		if($this->_block == 'skin') return skin::layoutInfo();
		elseif($this->_block == 'template') return template::layoutInfo();
		elseif($this->_block == 'css') return css::layoutInfo();
	}
	
	public function actionSkin() {
		
		$this->accessGroup('');

		$id = cleanVar($_POST, 'id', 'int', '');
		$skinObj = new skin($id);
		$skinObj->actionSkin();

		exit();
	}
	
	public function actionDelSkin() {
		
		$this->accessGroup('');

		$id = cleanVar($_POST, 'id', 'int', '');
		$skin = new skin($id);
		$skin->actionDelSkin();

		exit();
	}
	
	public function actionUpdateSkinOrder() {
	
		$this->accessGroup('');

		$order = cleanVar($_POST, 'order', 'string', '');
		$items = explode(",", $order);
		$i=1;
		foreach($items as $item) {
			$skin = new skin($item);
			$skin->priority = $i;
			$skin->updateDbData();
			$i++;	
		}
	}

	public function actionCss() {
		
		$this->accessGroup('');

		$id = cleanVar($_POST, 'id', 'int', '');
		$css = new css('layout', array('id'=>$id));
		$css->actionCssLayout();

		exit();

	}
	
	public function actionDelCss() {
		
		$this->accessGroup('');

		$id = cleanVar($_POST, 'id', 'int', '');
		$css = new css('layout', array('id'=>$id));
		$css->actionDelCssLayout();

		exit();
	}
	
	public function actionTemplate() {

		$this->accessGroup('');

		$id = cleanVar($_POST, 'id', 'int', '');
		$tplObj = new template($id);
		$tplObj->actionTemplate();

		exit();
	}

	public function actionDelTemplate() {
		
		$this->accessGroup('');

		$id = cleanVar($_POST, 'id', 'int', '');
		$tpl = new template($id);
		$tpl->actionDelTemplate();

		exit();
	}

	public function modulesList() {

		$this->accessGroup('');

		$nav_id = cleanVar($_GET, 'nav_id', 'string', '');
		$refillable_id = cleanVar($_GET, 'refillable_id', 'string', '');
		$fill_id = cleanVar($_GET, 'fill_id', 'string', '');

		$buffer = "<div>";
		$buffer .= "<table class=\"layout_mdlList\">";
		/*
		 * Pages
		 */
		$query = "SELECT p.item_id as id, p.module as module, p.parent as parent, p.title as title, m.role1 as role1, m.role2 as role2, m.role3 as role3 
			  FROM ".$this->_tbl_page." as p, ".$this->_tbl_module." as m 
			  WHERE p.module=m.id
			  ORDER BY p.title";
		$a = $this->_db->selectquery($query);
		if(sizeof($a)>0) {
			$buffer .= "<tr><th class=\"title\" colspan=\"3\">"._("Pagine")."</th></tr>";
			$buffer .= "<tr><th colspan=\"2\">"._("Titolo")."</th><th>"._("Permessi")."</th></tr>";
			foreach($a as $b) {
				$role_txt = $this->_db->getFieldFromId($this->_tbl_user_role, 'name', 'role_id', $b['role1']);
				$code_block = "{module pageid=".$b['id']." func=block}";
				$code_full = "{module pageid=".$b['id']." func=full}";
				$buffer .= "<tr><td class=\"mdlTitle\" rowspan=\"2\">".htmlChars($b['title'])."</td>";
				$buffer .= "<td class=\"link\" onclick=\"ajaxRequest('post', '$this->_home?pt[page-blockItem]&id=".$b['id']."', '', '".$fill_id."', {'script':true});closeAll('$nav_id', '$refillable_id', '".jsVar(htmlChars($b['title']))." - "._("Blocco")."', '$code_block')\";>"._("Blocco personalizzabile attraverso le opzioni di pagina")."</td><td>$role_txt</td></tr>";
				$buffer .= "<td class=\"link\" onclick=\"ajaxRequest('post', '$this->_home?pt[page-displayItem]&id=".$b['id']."', '', '".$fill_id."', {'script':true});closeAll('$nav_id', '$refillable_id', '".jsVar(htmlChars($b['title']))." - "._("Completo")."', '$code_full')\";>"._("Pagina completa")."</td><td>$role_txt</td></tr>";

			}
		}
		/*
		 * Modules sys_module
		 */
		$query = "SELECT id, label, name, class, role1, role2, role3 FROM ".$this->_tbl_module." WHERE type='class' AND masquerade='no' ORDER BY label";
		$a = $this->_db->selectquery($query);
		if(sizeof($a)>0) {
			$buffer .= "<tr><th class=\"title\" colspan=\"3\">"._("Moduli")."</th></tr>";
			$buffer .= "<tr><th>"._("Nome")."</th><th>"._("Funzione")."</th><th>"._("Permessi")."</th></tr>";
			foreach($a as $b) {
				$output_functions = (method_exists($b['class'], 'outputFunctions'))? call_user_func(array($b['class'], 'outputFunctions')):array();
				if(count($output_functions)) {
					$buffer .= "<tr><td class=\"mdlTitle\" rowspan=\"".count($output_functions)."\">".htmlChars($b['label'])."</td>";
					foreach($output_functions as $func=>$data) {
						$role = $b['role'.$data['role']];
						$role_txt = $this->_db->getFieldFromId($this->_tbl_user_role, 'name', 'role_id', $role);
						$code = "{module classid=".$b['id']." func=".$func."}";
						$buffer .= "<td class=\"link\" onclick=\"ajaxRequest('post', '$this->_home?pt[".$b['name']."-$func]', '', '".$fill_id."', {'script':true});closeAll('$nav_id', '$refillable_id', '".htmlChars($b['label'])." - ".jsVar($data['label'])."', '$code')\";>{$data['label']}</td><td>$role_txt</td></tr>";
					}	
				}	
			}
		}
		/*
		 * Modules sys_module_app
		 */
		$query = "SELECT id, label, name, role1, role2, role3 FROM ".$this->_tbl_module_app." WHERE type='class' AND instance='no' AND masquerade='no' ORDER BY label";
		$a = $this->_db->selectquery($query);
		if(sizeof($a)>0) {
			$buffer .= "<tr><th class=\"title\" colspan=\"3\">"._("Moduli di sistema")."</th></tr>";
			$buffer .= "<tr><th>"._("Nome")."</th><th>"._("Funzione")."</th><th>"._("Permessi")."</th></tr>";
			foreach($a as $b) {
				$output_functions = (method_exists($b['name'], 'outputFunctions'))? call_user_func(array($b['name'], 'outputFunctions')):array();
				if(count($output_functions)) {
					$buffer .= "<tr><td class=\"mdlTitle\" rowspan=\"".count($output_functions)."\">".htmlChars($b['label'])."</td>";
					foreach($output_functions as $func=>$data) {
						$role = $b['role'.$data['role']];
						$role_txt = $this->_db->getFieldFromId($this->_tbl_user_role, 'name', 'role_id', $role);
						$code = "{module sysclassid=".$b['id']." func=".$func."}";
						$buffer .= "<td class=\"link\" onclick=\"ajaxRequest('post', '$this->_home?pt[".$b['name']."-$func]', '', '".$fill_id."', {'script':true});closeAll('$nav_id', '$refillable_id', '".htmlChars($b['label'])." - ".$data['label']."', '$code')\";>{$data['label']}</td><td>$role_txt</td></tr>";
					}	
				}	
			}
		}
		/*
		 * Functions
		 */
		$query = "SELECT id, label, name, role1, role2, role3 FROM ".$this->_tbl_module." WHERE type='func' AND masquerade='no' ORDER BY label";
		$a = $this->_db->selectquery($query);
		if(sizeof($a)>0) {
			$buffer .= "<tr><th class=\"title\" colspan=\"3\">"._("Moduli funzione")."</th></tr>";
			$buffer .= "<tr><th colspan=\"2\">"._("Nome")."</th><th>"._("Permessi")."</th></tr>";
			foreach($a as $b) {
				$output_functions = (method_exists('sysfunc', 'outputFunctions'))? call_user_func(array('sysfunc', 'outputFunctions')):array();
				if(count($output_functions)) {
					$buffer .= "<tr>";
					$role = $b['role'.$output_functions[$b['name']]['role']];
					$role_txt = $this->_db->getFieldFromId($this->_tbl_user_role, 'name', 'role_id', $role);
					$code = "{module funcid=".$b['id']."}";
					$buffer .= "<td class=\"link mdlTitle\" onclick=\"ajaxRequest('post', '$this->_home?pt[sysfunc-".$b['name']."]', '', '".$fill_id."', {'script':true});closeAll('$nav_id', '$refillable_id', '".htmlChars($b['label'])."', '$code')\";>".$b['label']."</td><td>".$output_functions[$b['name']]['label']."</td><td>$role_txt</td></tr>";
				}	
			}
		}
		/*
		 * Url module
		 */
		$code = "{module id=0}";
		$buffer .= "<tr><th class=\"title\" colspan=\"3\">"._("Moduli segnaposto")."</th></tr>";
		$buffer .= "<tr><th colspan=\"2\">"._("Nome")."</th><th>"._("Permessi")."</th></tr>";
		$buffer .= "<tr>";
		$buffer .= "<td colspan=\"2\" class=\"link mdlTitle\" onclick=\"closeAll('$nav_id', '$refillable_id', '"._("Modulo da url")."', '$code')\";>"._("Modulo da url")."</td><td>"._("Prende i permessi del modulo chiamato")."</td></tr>";
		$buffer .= "</tr>";

		$buffer .= "</table>";
		$buffer .= "</div>";

		return $buffer;
	}
}
?>
