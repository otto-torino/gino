<?php

class PhpModule extends propertyObject {

	protected $_tbl_data;
	public static $_tbl_php_mdl = 'php_module';
	private $_home, $_interface;

	function __construct($instance, $interface) {

		$this->_tbl_data = self::$_tbl_php_mdl;

		parent::__construct($this->initP($instance));

		$this->instance = $instance;

		$this->_home = 'index.php';
		$this->_interface = $interface;
		
	}
	
	private function initP($instance) {
	
		$db = new Db;
		$query = "SELECT * FROM ".$this->_tbl_data." WHERE instance='$instance'";
		$a = $db->selectquery($query);
		if(sizeof($a)>0) return $a[0]; 
		else return array('id'=>null, 'instance'=>null, 'content'=>null);
	}
	
	public function setInstance($value) {
		
		if($this->_p['instance']!=$value && !in_array('instance', $this->_chgP)) $this->_chgP[] = 'instance';
		$this->_p['instance'] = $value;

		return true;

	}

	public function setContent($value) {
		
		if($this->_p['content']!=$value && !in_array('content', $this->_chgP)) $this->_chgP[] = 'content';
		$this->_p['content'] = $value;

		return true;

	}
	
	public function formPhpModule() {

		$htmlsection = new htmlSection(array('class'=>'admin', 'headerTag'=>'h1', 'headerLabel'=>_("Modifica codice")));
		$gform = new Form('gform', 'post', true);
		$gform->load('dataform');
	
		$required = 'content';
		$buffer = $gform->form($this->_home."?evt[".$this->_interface."-manageDoc]&action=save", '', $required, array("generateToken"=>true));

		$content = $this->content? $this->content:"\$buffer = '';";
		$buffer .= $gform->ctextarea('content', htmlspecialchars($gform->retvar('content',$content)), array(_("Codice"), _("Il codice php deve ritornare tutto l'output immagazzinato dentro la variabile <b>\$buffer</b>, la quale <b>non</b> deve essere reinizializzata. Attenzione a <b>non stampare</b> direttamente variabili con <b>echo</b> o <b>print()</b>, perchè in questo caso i contenuti verrebbero stampati al di fuori del layout.<br/>Le funzioni di esecuzione di programmi sono disabilitate.")), array("cols"=>'96%', "rows"=>30));

		$buffer .= $gform->cinput('submit_action', 'submit', _("salva"), '', array("classField"=>"submit"));
		$buffer .= $gform->cform();

		$htmlsection->content = $buffer;

		return $htmlsection->render();

	}

	public function actionPhpModule() {
		
		$gform = new Form('gform', 'post', false, array("verifyToken"=>true));
		$gform->save('dataform');
		$req_error = $gform->arequired();

		$content = mysql_real_escape_string(htmlspecialchars_decode($_POST['content']));
		
		$link_error = $this->_home."?evt[$this->_interface-manageDoc]&action=modify";

		if($req_error > 0) 
			exit(error::errorMessage(array('error'=>1), $link_error));

		$this->content = $content;
		$this->updateDbData();

		header("Location: $this->_home?evt[$this->_interface-manageDoc]");

	}


}


?>
