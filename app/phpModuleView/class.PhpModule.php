<?php
/**
 * @file class.PhpModule.php
 * @brief Contiene la classe PhpModule
 * 
 * @copyright 2005 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
namespace Gino\App\PhpModuleView;

/**
 * @brief Fornisce gli strumenti alla classe phpModuleView per la gestione amministrativa
 * 
 * @copyright 2005 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class PhpModule extends \Gino\Model {

	protected $_tbl_data;
	public static $_tbl_php_mdl = 'php_module';
	private $_home, $_interface;

	/**
	 * Costruttore
	 * 
	 * @param integer $instance valore ID dell'istanza
	 * @param string $interface nome dell'istanza
	 * @return void
	 */
	function __construct($instance, $interface) {

		$this->_tbl_data = self::$_tbl_php_mdl;

		parent::__construct($instance);

		$this->instance = $instance;

		$this->_home = 'index.php';
		$this->_interface = $interface;
	}
	
	// Ricostruisce la struttura
	public function structure($id) {
	
		parent::structure(null);
		if($id)
		{
			$query = "SELECT * FROM ".$this->_tbl_data." WHERE instance='$id'";
			$a = $this->_db->selectquery($query);
			if(sizeof($a)>0) $this->_p = $a[0];
		}
		else $this->_p = array('id'=>null, 'instance'=>null, 'content'=>null);
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
	
	/**
	 * Form di inserimento e modifica del codice php
	 * 
	 * @return string
	 */
	public function formPhpModule() {

		$this->_registry->addJs(SITE_JS."/CodeMirror/codemirror.js");
		$this->_registry->addCss(CSS_WWW."/codemirror.css");
		$this->_registry->addJs(SITE_JS."/CodeMirror/htmlmixed.js");
		$this->_registry->addJs(SITE_JS."/CodeMirror/matchbrackets.js");
		$this->_registry->addJs(SITE_JS."/CodeMirror/css.js");
		$this->_registry->addJs(SITE_JS."/CodeMirror/xml.js");
		$this->_registry->addJs(SITE_JS."/CodeMirror/clike.js");
		$this->_registry->addJs(SITE_JS."/CodeMirror/php.js");

		$options = "{
		lineNumbers: true,
		matchBrackets: true,
		mode: \"application/x-httpd-php\",
		indentUnit: 4,
		indentWithTabs: true,
		enterMode: \"keep\",
		tabMode: \"shift\"
		}";

		$gform = \Gino\Loader::load('Form', array('gform', 'post', true));
		$gform->load('dataform');
	
		$required = 'content';
		$buffer = $gform->open($this->_home."?evt[".$this->_interface."-manageDoc]&action=save", '', $required, array("generateToken"=>true));

		$content = $this->content? $this->content:"\$buffer = '';";
		$buffer .= $gform->ctextarea('content', htmlspecialchars($gform->retvar('content',$content)), array(_("Codice"), _("Il codice php deve ritornare tutto l'output immagazzinato dentro la variabile <b>\$buffer</b>, la quale <b>non</b> deve essere reinizializzata. Attenzione a <b>non stampare</b> direttamente variabili con <b>echo</b> o <b>print()</b>, perchè in questo caso i contenuti verrebbero stampati al di fuori del layout.<br/>Le funzioni di esecuzione di programmi sono disabilitate.")), array("id"=>"codemirror", "required"=> true, "other"=>"style=\"width: 95%\"", "rows"=>30));

		$buffer .= $gform->cinput('submit_action', 'submit', _("salva"), '', array("classField"=>"submit"));
		$buffer .= $gform->close();
		
		$buffer .= "<script>var myCodeMirror = CodeMirror.fromTextArea(document.getElementById('codemirror'), $options);</script>";

		$view =   new \Gino\View(null, 'section');
		$dict = array(
		'title' => _('Modifica codice'),
		'class' => 'admin',
		'content' => $buffer
		);
		return $view->render($dict);
	}

	/**
	 * Inserimento e modifica del codice php
	 */
	public function actionPhpModule() {
		
		$gform = \Gino\Loader::load('Form', array('gform', 'post', false, array("verifyToken"=>true)));
		$gform->save('dataform');
		$req_error = $gform->arequired();

		$content = $this->_db->escapeString(htmlspecialchars_decode($_POST['content']));
		
		$link_error = $this->_home."?evt[$this->_interface-manageDoc]&action=modify";

		if($req_error > 0) 
			exit(error::errorMessage(array('error'=>1), $link_error));

		$this->content = $content;
		$this->updateDbData();

		header("Location: $this->_home?evt[$this->_interface-manageDoc]");
		exit();
	}
}
?>
