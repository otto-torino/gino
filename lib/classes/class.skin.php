<?php

class skin extends propertyObject {

	protected $_tbl_data;
	private static $_tbl_skin = 'sys_layout_skin';
	private $_home, $_interface;

	function __construct($id) {

		$this->_tbl_data = self::$_tbl_skin;

		parent::__construct($this->initP($id));

		$this->_home = 'index.php';
		$this->_interface = 'layout';

	}
	
	private function initP($id) {
	
		$db = new Db;
		$query = "SELECT * FROM ".$this->_tbl_data." WHERE id='$id'";
		$a = $db->selectquery($query);
		if(sizeof($a)>0) return $a[0]; 
		else return array('id'=>null, 'label'=>null, 'rexp'=>null, 'urls'=>null, 'template'=>null, 'css'=>null, 'priority'=>null, 
			'shadow'=>null, 'shadowSize'=>null, 'shadowRadius'=>null, 'shadowColor'=>null, 'shadowOpacity'=>null, 'auth'=>null, 'cache'=>null);
	}
	
	public function setPriority($value) {
		
		if($this->_p['priority']!=$value && !in_array('priority', $this->_chgP)) $this->_chgP[] = 'priority';
		$this->_p['priority'] = $value;

		return true;

	}
	
	public function setRexp($value) {
		
		if($this->_p['rexp']!=$value && !in_array('rexp', $this->_chgP)) $this->_chgP[] = 'rexp';
		$this->_p['rexp'] = $value;

		return true;

	}
	
	public function setAuth($pName) {

		$value = cleanVar($_POST, $pName, 'string', '');
		$this->_chgP[] = 'auth';
		$this->_p['auth'] = $value;

		return true;

	}
	
	public function setCache($pName) {
		
		$value = cleanVar($_POST, $pName, 'int', '');
		if($this->_p['cache']!=$value && !in_array('cache', $this->_chgP)) $this->_chgP[] = 'cache';
		$this->_p['cache'] = $value;

		return true;

	}

	public static function getAll($order='priority') {

		$db = new Db;
		$res = array();
		$query = "SELECT id, label, rexp, urls, template, css, priority, auth FROM ".self::$_tbl_skin." ORDER BY $order";
		$a = $db->selectquery($query);
		if(sizeof($a)>0) {
			foreach($a as $b) {
				$res[] = new skin($b['id']);
			}
		}

		return $res;
	}

	public static function getSkin($queryString) {

		$db = new db;
		$relativeUrl = preg_replace("#".SITE_WWW.OS."#", "", $_SERVER['SCRIPT_NAME']).((!empty($queryString))?"?$queryString":"");

		$query = "SELECT id, rexp, urls, auth FROM ".self::$_tbl_skin." ORDER BY priority ASC";	
		$a = $db->selectquery($query);
		if(sizeof($a)>0) {
			foreach($a as $b) {
				$urls = explode(",", $b['urls']);

				foreach($urls as $url) 
					if($url == $relativeUrl) { 
						if($b['auth']=='' || (isset($_SESSION['userId']) && $b['auth']=='yes') || (!isset($_SESSION['userId']) && $b['auth']=='no'))
							return new skin($b['id']);
					}
			}
			foreach($a as $b) {

				if(!empty($b['rexp'])) 
					if(preg_match($b['rexp'], $relativeUrl)) 
						if($b['auth']=='' || (isset($_SESSION['userId']) && $b['auth']=='yes') || (!isset($_SESSION['userId']) && $b['auth']=='no'))
							return new skin($b['id']);

			}
			return false;
		}
		else return false;

	}

	public static function removeCss($id) {

		$db = new db;
		$query = "UPDATE ".self::$_tbl_skin." SET css=0 WHERE css='$id'";	
		$result = $db->actionquery($query);

		return $result;
	}
	
	public static function removeTemplate($id) {
		
		$db = new db;
		$query = "UPDATE ".self::$_tbl_skin." SET template=0 WHERE template='$id'";	
		$result = $db->actionquery($query);

		return $result;

	}

	public static function newSkinPriority() {

		$db = new db();
		$query = "SELECT MAX(priority) as m FROM ".self::$_tbl_skin;
		$a = $db->selectquery($query);
		if(sizeof($a)>0) {
			return ($a[0]['m']+1);
		}
		return 1;
	}

	public function formSkin() {

		$gform = new Form('gform', 'post', true);
		$gform->load('dataform');

		$title = ($this->id)? _("Modifica")." ".htmlChars($this->label):_("Nuova skin");
		
		$htmlsection = new htmlSection(array('class'=>'admin', 'headerTag'=>'h1', 'headerLabel'=>$title));

		$required = 'template';
		$buffer = $gform->form($this->_home."?evt[".$this->_interface."-actionSkin]", '', $required);
		$buffer .= $gform->hidden('id', $this->id);

		$buffer .= $gform->cinput('label', 'text', $gform->retvar('label', htmlInput($this->label)), _("Etichetta"), array("required"=>true, "size"=>40, "maxlength"=>200, "trnsl"=>true, "trnsl_table"=>$this->_tbl_data, "field"=>"label", "trnsl_id"=>$this->id));
		$buffer .= $gform->cinput('rexp', 'text', $gform->retvar('rexp', $this->rexp), _("Espressione regolare"), array("size"=>40, "maxlength"=>200));
		$buffer .= $gform->cinput('urls', 'text', $gform->retvar('urls', htmlInput($this->urls)), array(_("Urls"), _("Indicare uno o più indirizzi separati da virgole")), array("size"=>40, "maxlength"=>200));
		$css_list = array();
		foreach(css::getAll() as $css) {
			$css_list[$css->id] = htmlInput($css->label);
		}	
		$buffer .= $gform->cselect('css', $gform->retvar('css', $this->css), $css_list, _("Css"));
		$tpl_list = array();
		foreach(template::getAll() as $tpl) {
			$tpl_list[$tpl->id] = htmlInput($tpl->label);
		}	
		$buffer .= $gform->cselect('template', $gform->retvar('template', $this->template), $tpl_list, _("Template"), array("required"=>true));
		$buffer .= $gform->cradio('auth', $gform->retvar('auth', $this->auth), array(""=>"si & no", "yes"=>_("si"),"no"=>_("no")), '', _("Autenticazione"), array("required"=>true, "aspect"=>"v"));
		$buffer .= $gform->cinput('cache', 'text', $gform->retvar('cache', $this->cache), array(_("Tempo di caching dei contenuti (s)"), _("Se non si vogliono tenere in cache o non se ne conosce il significato lasciare vuoto o settare a 0")), array("size"=>6, "maxlength"=>16, "pattern"=>"^\d*$", "hint"=>_("Il campo deve contenere un numero intero")));

		$buffer .= $gform->cinput('submit_action', 'submit', (($this->id)?_("modifica"):_("inserisci")), '', array("classField"=>"submit"));

		$buffer .= $gform->cform();

		$htmlsection->content = $buffer;

		return $htmlsection->render();
	}

	public function actionSkin() {

		$gform = new Form('gform', 'post', false);
		$gform->save('dataform');
		$req_error = $gform->arequired();

		$action = ($this->id)?'modify':'insert';

		$rexp = cleanVar($_POST, 'rexp', 'string', '');
		
		$link_error = $this->_home."?evt[$this->_interface-manageLayout]&block=skin&id=$this->id&action=$action";

		if($req_error > 0) 
			exit(error::errorMessage(array('error'=>1), $link_error));

		foreach($_POST as $k=>$v) {
			$this->{$k} = $k;
		}

		$this->rexp = $rexp;
		if(!$this->id) $this->priority = skin::newSkinPriority();
		$this->updateDbData();

		header("Location: $this->_home?evt[$this->_interface-manageLayout]&block=skin");

	}
	
	public function formDelSkin() {
	
		$gform = new Form('gform', 'post', false);
		$gform->load('dataform');

		$id = cleanVar($_GET, 'id', 'int', '');

		$htmlsection = new htmlSection(array('class'=>'admin', 'headerTag'=>'h1', 'headerLabel'=>_("Elimina skin")));

		$required = '';
		$buffer = $gform->form($this->_home."?evt[$this->_interface-actionDelSkin]", '', $required);
		$buffer .= $gform->hidden('id', $this->id);
		$buffer .= $gform->cinput('submit_action', 'submit', _("elimina"), array(_("Attenzione!"), _("l'eliminazione è definitiva")), array("classField"=>"submit"));
		$buffer .= $gform->cform();

		$htmlsection->content = $buffer;

		return $htmlsection->render();

	}
	
	public function actionDelSkin() {
		
		language::deleteTranslations($this->_tbl_data, $this->id);
		$this->deleteDbData();

		header("Location: $this->_home?evt[$this->_interface-manageLayout]&block=skin");

	}

	public static function layoutInfo() {
	
		$htmlsection = new htmlSection(array('class'=>'admin', 'headerTag'=>'h1', 'headerLabel'=>_("Informazioni skin")));
		$buffer = "<p><b>"._("Indicazioni")."</b></p>\n";
		$buffer .= "<p>"._("In questa sezione si definiscono le skin che comprendono in sostanza un css ed un template e che possono essere associate alternativamente a");
		$buffer .= "<ul>
		<li>"._("un url")."</li>
		<li>"._("una serie di url")."</li>
		<li>"._("una classe di url")."</li>
		</ul>";
		$buffer .= "</p>\n";
		$buffer .= "<p><b>"._("Funzionamento")."</b></p>\n";
		$buffer .= "<p>"._("Css e template possono essere associati nei tre modi descritti sopra. Nel campo urls che compare nel form di modifica o inserimento si può inserire un indirizzo o più indirizzi separati da virgola ai quali associare la skin. Tali indirizzi hanno la <b>priorità</b> rispetto alle classi di url nel momento in cui viene cercata la skin da associare al documento richiesto.
		<br />Le classi di url, definite mediante il campo 'Espressione regolare', nel formato PCRE permettono di fare il matching con tutti gli url che soddisfano l'espressione regolare inserita.")."</p>\n";
		$buffer .= "<p>"._("Ciascuna skin ha una priorità, definita dall'ordine in cui compaiono le skin nell'elenco a sinistra e modificabile per trascinamento.")."</p>\n";
		$buffer .= "<p>"._("Quando viene richiesta una pagina (url) il sistema inizia a controllare il matching tra la pagina richiesta e gli indirizzi (url) associati alle skin partendo dalla skin con priorità più alta.
		<br />Se viene trovato un matching la skin viene utilizzata, altrimenti la ricerca riprende utilizzando le espressioni regolari, sempre per ordine di priorità.")."</p>\n";

		$htmlsection->content = $buffer;

		return $htmlsection->render();
	}
}
?>
