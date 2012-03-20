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
	
		$db = db::instance();
		$query = "SELECT * FROM ".$this->_tbl_data." WHERE id='$id'";
		$a = $db->selectquery($query);
		if(sizeof($a)>0) return $a[0]; 
		else return array('id'=>null, 'label'=>null, 'session'=>null, 'rexp'=>null, 'urls'=>null, 'template'=>null, 'css'=>null, 'priority'=>null, 
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

		$db = db::instance();
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

	public static function getSkin($relativeUrl) {

		$db = db::instance();
		$plink = new Link();

		$query = "SELECT id, session, rexp, urls, auth FROM ".self::$_tbl_skin." ORDER BY priority ASC";	
		$a = $db->selectquery($query);
		if(sizeof($a)>0) {

			foreach($a as $b) {

				$session_array = explode("=", trim($b['session']));

				if(count($session_array)==2) {

					if(isset($_SESSION[$session_array[0]]) && $_SESSION[$session_array[0]] == $session_array[1]) {
						
						$urls = explode(",", $b['urls']);

						foreach($urls as $url) 
						{	
							if(!preg_match('#\?(evt|pt)\[#', $url))
							$url = $plink->convertLink($url, array('pToLink'=>true, 'basename'=>true));
					
							if($url == $relativeUrl) { 
								if($b['auth']=='' || (isset($_SESSION['userId']) && $b['auth']=='yes') || (!isset($_SESSION['userId']) && $b['auth']=='no'))
									return new skin($b['id']);
							}
						}

						if(!empty($b['rexp']))
						{
							$p_relativeUrl = $plink->convertLink($relativeUrl, array('pToLink'=>false));
					
							if(preg_match($b['rexp'], $relativeUrl) || preg_match($b['rexp'], $p_relativeUrl))
							{
								if($b['auth']=='' || (isset($_SESSION['userId']) && $b['auth']=='yes') || (!isset($_SESSION['userId']) && $b['auth']=='no'))
									return new skin($b['id']);
							}
						}

					}
				}
			}

			foreach($a as $b) {

				if(!$b['session']) {
					$urls = explode(",", $b['urls']);

					foreach($urls as $url) 
					{	
						if(!preg_match('#\?(evt|pt)\[#', $url))
							$url = $plink->convertLink($url, array('pToLink'=>true, 'basename'=>true));
					
						if($url == $relativeUrl) { 
							if($b['auth']=='' || (isset($_SESSION['userId']) && $b['auth']=='yes') || (!isset($_SESSION['userId']) && $b['auth']=='no'))
								return new skin($b['id']);
						}
					}
				}
			}
			foreach($a as $b) {

				if(!$b['session'] && !empty($b['rexp']))
				{
					$p_relativeUrl = $plink->convertLink($relativeUrl, array('pToLink'=>false));
					
					if(preg_match($b['rexp'], $relativeUrl) || preg_match($b['rexp'], $p_relativeUrl))
					{
						if($b['auth']=='' || (isset($_SESSION['userId']) && $b['auth']=='yes') || (!isset($_SESSION['userId']) && $b['auth']=='no'))
							return new skin($b['id']);
					}
				}

			}
			return false;
		}
		else return false;
	}

	public static function removeCss($id) {

		$db = db::instance();
		$query = "UPDATE ".self::$_tbl_skin." SET css=0 WHERE css='$id'";	
		$result = $db->actionquery($query);

		return $result;
	}
	
	public static function removeTemplate($id) {
		
		$db = db::instance();
		$query = "UPDATE ".self::$_tbl_skin." SET template=0 WHERE template='$id'";	
		$result = $db->actionquery($query);

		return $result;

	}

	public static function newSkinPriority() {

		$db = db::instance();
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
		$buffer .= $gform->cinput('session', 'text', $gform->retvar('session', $this->session), array(_("Variabile di sessione"), _("esempi").":<br />mobile=1"), array("size"=>40, "maxlength"=>200));
		$buffer .= $gform->cinput('rexp', 'text', $gform->retvar('rexp', $this->rexp), array(_("Espressione regolare"), _("esempi").":<br />#\?evt\[news-(.*)\]#<br />#^news/(.*)#"), array("size"=>40, "maxlength"=>200));
		$buffer .= $gform->cinput('urls', 'text', $gform->retvar('urls', htmlInput($this->urls)), array(_("Urls"), _("Indicare uno o più indirizzi separati da virgole; esempi").":<br />index.php?evt[news-viewList]<br />news/viewList"), array("size"=>40, "maxlength"=>200));
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
		$buffer .= "<p>"._("In questa sezione si definiscono le skin che comprendono un file css ed un template e che possono essere associate a")."</p>";
		$buffer .= "<ul>
		<li>"._("un url")."</li>
		<li>"._("una serie di url")."</li>
		<li>"._("una classe di url")."</li>
		</ul>";
		$buffer .= "<p>"._("Questi metodi possono essere abbinati o meno ad una variabile di sessione.")."</p>";
		$buffer .= "<p><b>"._("Funzionamento")."</b></p>\n";
		$buffer .= "<p>"._("La ricerca di una corrispondenza pagina richiesta/skin avviene in base a dei principi di priorità secondo i quali vengono controllati prima gli url/classi di url appartenenti a skin che hanno un valore di variabile di sessione; successivamente vengono controllati quelli appartenenti a skin che non hanno un valore di variabile di sessione.")."</p>";
		$buffer .= "<p>"._("L'ordine di priorità delle skin è definito dall'ordine in cui compaiono nell'elenco a sinistra e modificabile per trascinamento.")."</p>";
		$buffer .= "<p>"._("Nel campo <b>Variabile di sessione</b> che compare nel form di modifica o inserimento si può inserire il valore di una variabile di sessione nel formato \"nome_variabile=valore\", per il quale verranno applicate le regole di matching di url e classi.<br />Nel campo <b>Urls</b> si può inserire un indirizzo o più indirizzi separati da virgola ai quali associare la skin. Tali indirizzi hanno la <b>priorità</b> rispetto alle classi di url nel momento in cui viene cercata la skin da associare al documento richiesto.
		<br />Le classi di url, definite mediante il campo <b>Espressione regolare</b>, nel formato PCRE permettono di fare il matching con tutti gli url che soddisfano l'espressione regolare inserita.")."</p>\n";

		$buffer .= "<p><b>"._("Regole di matching url/classi")."</b></p>\n";
		$buffer .= "<p>"._("Quando viene richiesta una pagina (url) il sistema inizia a controllare il matching tra la pagina richiesta e gli indirizzi associati alle skin.
		<br />Se il matching non viene trovato, la ricerca continua utilizzando le espressioni regolari.")."</p>\n";

		$buffer .= "<p>"._("Nei campi 'Espressione regolare' e 'Urls' possono essere inseriti valori nel formato permalink o in quello nativo di gino.")."</p>";

		$htmlsection->content = $buffer;

		return $htmlsection->render();
	}
}

?>
