<?php
require_once(CLASSES_DIR.OS.'class.propertyObject.php');

class menuVoice extends propertyObject {
	
	public static $tbl_voices = "sys_menu_voices";
	private static $_tbl_user_role = "user_role";
	private $_gform;

	function __construct($id) {
	
		$this->_tbl_data = self::$tbl_voices;
		parent::__construct($this->initP($id));
	}
	
	public static function deleteInstanceVoices($instance) {

		$db = db::instance();
		$query = "SELECT id FROM ".self::$tbl_voices." WHERE instance='$instance' AND parent='0'";
		$a = $db->selectquery($query);
		if(sizeof($a)>0) {
			foreach($a as $b) {
				language::deleteTranslations(self::$tbl_voices, $b['id']);
				$mv = new menuVoice($b['id']);
				$mv->deleteVoice();
			}
		}

		return true;
	}

	private function initP($id) {
	
		$db = db::instance();
		$query = "SELECT * FROM ".self::$tbl_voices." WHERE id='$id'";
		$a = $db->selectquery($query);
		if(sizeof($a)>0) return $a[0]; 
		else return array('id'=>null, 'instance'=>null, 'parent'=>null, 'label'=>null, 'link'=>null, 'type'=>null, 'role1'=>null, 'orderList'=>null, 'authView'=>null, 'reference'=>null);
	}
	
	public function setParent($postLabel) {
		
		$value = cleanVar($_POST, $postLabel, 'int', '');
		if($this->_p['parent']!=$value && !in_array('parent', $this->_chgP)) $this->_chgP[] = 'parent';
		$this->_p['parent'] = $value;
		return true;

	}
	
	public function setLink($value) {
		
		if($this->_p['link']!=$value && !in_array('link', $this->_chgP)) $this->_chgP[] = 'link';
		$this->_p['link'] = $value;
	}

	public function setInstance($value) {
		
		if($this->_p['instance']!=$value && !in_array('instance', $this->_chgP)) $this->_chgP[] = 'instance';
		$this->_p['instance'] = $value;

	}

	public function setOrderList($value) {
	
		if($this->_p['orderList']!=$value && !in_array('orderList', $this->_chgP)) $this->_chgP[] = 'orderList';
		$this->_p['orderList'] = $value;
	}

	public function initOrderList() {

		$query = "SELECT max(orderList) AS last FROM ".self::$tbl_voices." WHERE instance='{$this->_p['instance']}' AND parent='{$this->_p['parent']}'";
		$a = $this->_db->selectquery($query);
		if(sizeof($a)>0) {
			$last = $a[0]['last'];
		}
		else $last = 0;

		$this->orderList = $last+1;
	}

	public function updateOrderList() {

		$query = "UPDATE ".self::$tbl_voices." SET orderList=orderList-1 WHERE orderList>'".$this->orderList."' AND parent='".$this->parent."'";		
		$result = $this->_db->actionquery($query);
	}

	public function deleteVoice() {

		foreach($this->getChildren() as $child) $child->deleteVoice();
		$this->deleteDbData();
	}

	private function getChildren() {

		$children = array();

		$query = "SELECT id FROM ".self::$tbl_voices." WHERE parent='{$this->_p['id']}' ORDER BY orderList";
		$a = $this->_db->selectquery($query);
		if(sizeof($a)>0) {
			foreach($a as $b) {
				$children[$b['id']] = new menuVoice($b['id']);
			}
		}

		return $children;
	}

	public function formVoice($formaction, $parent) {
	
		$gform = new Form('gform', 'post', true);
		$gform->load('dataform');

		$parentVoice = new menuVoice($parent);

		if($this->_p['id']) {$title = _("Modifica voce"); $submit = _("modifica");$action='modify';}
		else {
			$title = ($parent)? _("Nuova voce sotto ")."\"".htmlChars($parentVoice->label)."\"":_("Nuova voce principale");
			$submit = _("inserisci");
			$action='insert';
		}
		$title = "<a name=\"top\">$title</a>";

		$pub = new pub;
		$link_delete = ($this->_p['id'])? "<a href=\"".$_SERVER['QUERY_STRING']."&action=delete\">".$pub->icon('delete', _("elimina voce"))."</a>":"";
		
		$htmlsection = new htmlSection(array('class'=>'admin', 'headerTag'=>'h1', 'headerLabel'=>$title, 'headerLinks'=>array($link_delete)));
		$required = 'label,type,role1,authView';

		$required = '';
		$buffer = $gform->form($formaction, '', $required);
		$buffer .= $gform->hidden('action', $action);
		$buffer .= $gform->hidden('parent', $parent);
		if($this->_p['id'])
			$buffer .= $gform->hidden('id', $this->_p['id']);

		$buffer .= $gform->cinput('label', 'text', $gform->retvar('label', htmlInput($this->_p['label'])), _("Voce"), array("required"=>true, "size"=>40, "maxlength"=>200, "trnsl"=>true, "trnsl_table"=>self::$tbl_voices, "field"=>"label", "trnsl_id"=>$this->_p['id']));

		$buffer .= $gform->cinput('link', 'text', $gform->retvar('link', htmlInput($this->_p['link'])), _("Link"), array("size"=>40, "maxlength"=>200, "id"=>"link"));

		$buffer .= $gform->cradio('type', $gform->retvar('type', htmlInput($this->_p['type'])), array('int'=>_("interno (utilizzare il 'modulo di ricerca pagine e classi')"), 'ext'=>_("esterno (http://www.otto.to.it)")), 'int', _("Tipo di link"), array("required"=>true, "aspect"=>"v"));

		$buffer .= $gform->cinput('reference', 'text', $gform->retvar('reference', htmlInput($this->_p['reference'])), _("Nome pagina/classe"), array("size"=>40, "maxlength"=>100, "id"=>"reference"));

		$buffer .= $this->selectRole($this->_gform, $gform->retvar('role1', htmlInput($this->_p['role1'])));

		$buffer .= $gform->cradio('authView', $gform->retvar('authView', $this->_p['authView']), array('1'=>_("si"), '0'=>_("no")), '1', _("Visibile se autenticati"), array("required"=>true));

		$buffer .= $gform->cinput('submit_action', 'submit', $submit, '', array("classField"=>"submit"));
		$buffer .= $gform->cform();

		$htmlsection->content = $buffer;

		return $htmlsection->render();
	}
	
	public function formDelVoice($formaction) {
	
		$gform = new Form('gform', 'post', false);
		$htmlsection = new htmlSection(array('class'=>'admin', 'headerTag'=>'h1', 'headerLabel'=>_("Eliminazione voce di menu ")."'{$this->_p['label']}'"));

		$required = 'id';
		$buffer = $gform->form($formaction, '', $required);
		$buffer .= $gform->hidden('id', $this->_p['id']);
		$buffer .= $gform->cinput('submit_action', 'submit', _("elimina"), array(_("Attenzione!"), _("l'eliminazione è definitiva e comporta l'eliminazione delle eventuali sottovoci")), array("classField"=>"submit"));
		$buffer .= $gform->cform();

		$htmlsection->content = $buffer;

		return $htmlsection->render();
	}

	private function selectRole($object, $role1){
		
		$gform = new Form('gform', 'post', true);
		$GINO = '';
		$buffer = '';
		
		$onchange = "updaterole(this.selectedIndex)";
		$query = "SELECT role_id, name FROM ".self::$_tbl_user_role;
		$buffer .= $gform->cselect('role1', $role1, $query, _("Permessi di visualizzazione"), array("required"=>true, "id"=>"role1"));
		
		// Roles
		$a_role = array(); $a_role[] = '';
		$a = $this->_db->selectquery($query);
		if(sizeof($a) > 0)
		{
			foreach ($a AS $b)
			{
				$a_role[] = htmlChars($b['name']).'|'.$b['role_id'];
			}
		}
		
		$buffer .= "
		<script type=\"text/javascript\">

		var rolelist=document.gform.role1
		var selrolelist=document.gform.role1

		var newrolelist=new Array()
		";
		
		$buffer .= "newrolelist[0]=\"\"\n";
		for ($i=1, $end=sizeof($a_role); $i<$end; $i++)
		{
			$buffer .= "newrolelist[$i]=\"".$a_role[$i]."\"\n";
		}

		$buffer .= "
		function updaterole(selectedrolegroup){
			selrolelist.options.length=0
			if (selectedrolegroup>0)
			{
				for (i=0; i<=selectedrolegroup; i++)
				{
					// selrolelist.options.length => end of select
					selrolelist.options[i]=new Option(newrolelist[i].split(\"|\")[0], newrolelist[i].split(\"|\")[1])

					if(selectedrolegroup==i){
						selrolelist.options[i].selected=true
					}
				}
			}
		}
		</script>";
		
		return $buffer;
	}
	
	public static function getSelectedVoice($instance) {
	
		$query_string = urldecode($_SERVER['QUERY_STRING']);	// "evt[page-displayItem]&id=5" 
		
		if(!preg_match("/\[(.+)\]/is", $query_string, $matches)) return 'home';	//  home		
		if($matches[0] == "[index-index_page]") return 'home';			// home
		if($matches[0] == "[index-admin_page]") return 'admin';			// admin home

		$db = db::instance();
		
		$result = '';
		$result_link = '';
		$query = "SELECT id, link FROM ".self::$tbl_voices." WHERE link LIKE '%".$matches[0]."%' AND instance='$instance'";
		$a = $db->selectquery($query);
		if(sizeof($a)>0) {
			foreach($a as $b) {
				if(preg_match("#".self::regExpText(stristr($b['link'], '?'))."(&.*)?$#", "?".$query_string) && strlen($result_link)<strlen($b['link']) )
				{
					$result = $b['id'];
					$result_link=$b['link'];
				}
			}
		}
		else
		{
			/*
			L'indirizzo di base è nel formato di gino, ovvero ad esempio: 
			urldecode($_SERVER['QUERY_STRING']) => string(26) "evt[page-displayItem]&id=5" 
			in quanto l'eventuale permalink è già stato convertito (class Document).
			Nella tabella del menu i link sono registrati nel formato permalink.
			*/
			$obj = new Link();
			$plink = $obj->convertLink($query_string);	// => page/displayItem/5
			$search_link = $obj->alternativeLink($plink);
			
			$query = "SELECT id, link FROM ".self::$tbl_voices." WHERE $search_link AND instance='$instance'";
			$a = $db->selectquery($query);
			if(sizeof($a)>0) {
				foreach($a as $b) {
					
					$mlink = $b['link'];
					$mlink = $obj->convertLink($mlink, array('pToLink'=>true));	// => evt[page-displayItem]&id=5
					
					if(preg_match("#".self::regExpText(stristr($mlink, '?'))."(&.*)?$#", "?".$query_string) 
					&& strlen($result_link)<strlen($mlink))
					{
						$result = $b['id'];
						$result_link = $mlink;
					}
				}
			}
		}
		return $result;
	}

	private static function regExpText($string) {
		$find = array("\\", ".", "[", "]", "$", "(", ")", "|", "*", "+", "?", "{", "}");
		$replace = array("\\\\", "\.", "\[", "\]", "\$", "\(", "\)", "\|", "\*", "\+", "\?", "\{", "\}");
		$string = str_replace($find, $replace, $string);

		return $string;
	}
}
?>
