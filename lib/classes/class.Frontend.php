<?php
/**
 * @file class.frontend.php
 * @brief Contiene la classe Frontend
 * 
 * @copyright 2005 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */

/**
 * @brief Libreria per la gestione dei file di front-end dei singoli moduli (css e viste)
 * 
 * @copyright 2005 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 * 
 * Possono essere selezionati e modificati i file con le seguenti caratteristiche: \n
 *   - i file css definiti nel metodo getClassElements() della classe del modulo
 *   - i file delle viste presenti nella directory @a views presente nella directory dell'applicazione 
 */
class Frontend {

	private $_class, $_module_id;
  private $_module;
	private $_css_list, $_view_list;
	private $_mdlLink;

	/**
	 * Costruttore
	 * 
	 * @param array $params
	 *   array associativo di opzioni
	 *   - @b class (string): nome della classe
	 *   - @b module_id (integer): valore ID del modulo
	 * @return void
	 */
	function __construct($params) {
		
		$db = db::instance();

    Loader::import('sysClass', 'ModuleApp');
    Loader::import('module', 'ModuleInstance');
		
		$this->_class = $params['class'];
    $this->_module_id = $params['module_id'];

    if($this->_module_id) {
      $this->_module = new ModuleInstance($this->_module_id);
    }
    else {
      $this->_module = ModuleApp::getFromName($this->_class);
    }

    $this->setLists();

    $method = $this->_module_id ? 'manageDoc' : 'manage'.ucfirst($this->_class);
    $this->_mdlLink = HOME_FILE."?evt[{$this->_module->name}-{$method}]&block=frontend";

  }

  private function setLists() {

    $this->_css_list = array();
    $this->_view_list = array();

    $classElements = call_user_func(array($this->_class, 'getClassElements'));

    if(isset($classElements['css'])) {
      foreach($classElements['css'] as $file) {
        $this->_css_list[] = array(
          'filename' => $file,
          'description' => ''
        );
      }
    }

    if(isset($classElements['views'])) {
      foreach($classElements['views'] as $file => $description) {
        $this->_view_list[] = array(
          'filename' => $file,
          'description' => $description
        );
      }
    }
  
  }
	/**
	 * Nome del file
	 * 
	 * @param string $file nome del file
	 * @param string $ext estensione del file
	 * @return string
	 */
	private function fileName($file, $ext) {
		
		$name = $this->_module_id ? baseFileName($file)."_".$this->_module->name.".".$ext : $file;
		return $name;
	}
	
	/**
	 * Percorso assoluto della directory dei file di front-end
	 * 
	 * @param string $code
	 * @return string
	 */
	private function pathToFile($code) {
		
		$dir = APP_DIR.OS.$this->_class.OS;
		
		if($code == 'css')
		{
			//
		}
		elseif($code == 'view')
		{
			$dir .= 'views'.OS;
		}
		
		return $dir;
	}
	
	private function getFileList($code) {
		
		/*$dir = $this->pathToFile($code);
		$ext = 'php';
		
		$array = array();
		$buffer = '';
		
		if(is_dir($dir))
		{
			if($dh = opendir($dir)) {
				while (($file = readdir($dh)) !== false) {
					if($file != "." && $file != ".." && preg_match('#^[0-9a-zA-Z]+[0-9a-zA-Z_.\-]+\.'.$ext.'$#', $file))
					{
						$array[] = $file;
					}
				}
				closedir($dh);
			}
		}
    return $array;*/
	}
	
	/**
	 * Interfaccia per la gestione dei file di front-end dei moduli
	 * 
	 * @see moduleList()
	 * @see formModuleFile()
	 * @see actionModuleFile()
	 * @return string
	 */
	public function manageFrontend() {

		$action = cleanVar($_GET, 'action', 'string', '');
		$code = cleanVar($_GET, 'code', 'string', '');

    if($action == 'modify') {
      $buffer = $this->formModuleFile($code);
    }
    elseif($action == 'save') {
      $buffer .= $this->actionModuleFile($code);
    }
    else {
      $buffer = "<p class=\"backoffice-info\">"._('In questa sezione si può decidere l\'aspetto ed il modo di visualizzare le informazioni, modificando direttamente i fogli di stile e le viste utilizzati dal modulo.')."</p>";
      $buffer .= $this->moduleList('view');
      $buffer .= $this->moduleList('css');
    }

    $view = new View();
    $view->setViewTpl('section');
    $dict = array(
      'title' => 'Frontend',
      'class' => 'admin',
      'content' => $buffer
    );

		return $view->render($dict);
	}

	private function moduleList($code) {
	
		if($code == 'css')
		{
			$items = $this->_css_list;
			$ext = 'css';
			$title = _("Fogli di stile");
		}
		elseif($code == 'view')
		{
			$items = $this->_view_list;
			$ext = 'php';
			$title = _("Viste");
		}

		$num_items = count($items);
    if($num_items) {
      $buffer = "<h2>".$title."</h2>";
      $view_table = new View(null, 'table');
      $view_table->assign('class', 'table table-striped table-hover');
      $tbl_rows = array();
      $tb_rows[] = array(
        'text' => 'Viste',
        'header' => true,
        'colspan' => 3
      );
      foreach($items as $k=>$v) {
        $filename = $this->fileName($v['filename'], $ext);
        $description = $v['description'];
        $link_modify = "<a href=\"$this->_mdlLink&key=$k&code=$code&action=modify\">".pub::icon('modify')."</a>";
        $tbl_rows[] = array(
          $filename,
          $description,
          $link_modify
        );
      }
      $view_table->assign('rows', $tbl_rows);
      $buffer .= $view_table->render();
		}
		else {
			return '';
    }

    return $buffer;
	}
	
	private function formModuleFile($code) {

    $registry = registry::instance();
		$registry->addJs(SITE_JS."/CodeMirror/codemirror.js");
		$registry->addCss(CSS_WWW."/codemirror.css");
		$gform = Loader::load('Form', array('gform', 'post', true));
		$gform->load('dataform');

		$key = cleanVar($_GET, 'key', 'int', '');
		
		if($code == 'css')
		{
		  $registry->addJs(SITE_JS."/CodeMirror/css.js");
      $options = "{
        lineNumbers: true,
        matchBrackets: true,
        indentUnit: 4,
        indentWithTabs: true,
        enterMode: \"keep\",
        tabMode: \"shift\"
      }";
			$list = $this->_css_list;
			$ext = 'css';
		  $filename = $this->fileName($list[$key]['filename'], $ext);
			$title = sprintf(_("Modifica il foglio di stile \"%s\""), $filename);
		}
		elseif($code == 'view')
		{
		  $registry->addJs(SITE_JS."/CodeMirror/htmlmixed.js");
		  $registry->addJs(SITE_JS."/CodeMirror/matchbrackets.js");
		  $registry->addJs(SITE_JS."/CodeMirror/css.js");
		  $registry->addJs(SITE_JS."/CodeMirror/xml.js");
		  $registry->addJs(SITE_JS."/CodeMirror/clike.js");
		  $registry->addJs(SITE_JS."/CodeMirror/php.js");
      $options = "{
        lineNumbers: true,
        matchBrackets: true,
        mode: \"application/x-httpd-php\",
        indentUnit: 4,
        indentWithTabs: true,
        enterMode: \"keep\",
        tabMode: \"shift\"
      }";
			$list = $this->_view_list;
			$ext = 'php';
		  $filename = $this->fileName($list[$key]['filename'], $ext);
			$title = sprintf(_("Modifica la vista \"%s\""), $filename);
		}
		
		$required = '';
		$buffer = $gform->open($this->_mdlLink."&action=save&code=$code&key=$key", '', $required);

		$contents = file_get_contents($this->pathToFile($code).$filename);
		
		$buffer .= "<textarea id=\"codemirror\" class=\"form-no-check\" name=\"file_content\" style=\"width:98%; padding-top: 10px; padding-left: 10px; height:580px;overflow:auto;\">".$contents."</textarea>\n";

    $buffer .= "<div class=\"form-row\">";
    $buffer .= $gform->input('submit_action', 'submit', _("salva"), array("classField"=>"submit"));
    $buffer .= " ".$gform->input('cancel_action', 'button', _("annulla"), array("js"=>"onclick=\"location.href='$this->_mdlLink'\" class=\"generic\""));
    $buffer .= "</div>";
		
		$buffer .= $gform->close();

    $buffer .= "<script>var myCodeMirror = CodeMirror.fromTextArea(document.getElementById('codemirror'), $options);</script>";

    $view = new View(null, 'section');
    $dict = array(
      'title' => $title,
      'class' => 'admin',
      'content' => $buffer
    );


		return $view->render($dict);
	}

	private function actionModuleFile($code) {
	
		$key = cleanVar($_GET, 'key', 'int', '');
		
		if($code == 'css')
		{
			$list = $this->_css_list;
			$ext = 'css';
		}
		elseif($code == 'view')
		{
			$list = $this->_view_list;
			$ext = 'php';
		}
		
		$filename = $this->fileName($list[$key]['filename'], $ext);

		$file_content = filter_input(INPUT_POST, 'file_content');
		$fo = fopen($this->pathToFile($code).$filename, 'wb');
		fwrite($fo, $file_content);
		fclose($fo);

		header("Location: ".$this->_mdlLink);
    exit();
	}

}
?>