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
class Frontend extends Model {

	private $_class, $_module, $_name, $_label, $_css_list;
	private $_view_list;
	private $_instance_class;
	private $_mdlLink;

	/**
	 * Costruttore
	 * 
	 * @param array $params
	 *   array associativo di opzioni
	 *   - @b id (integer): valore ID del record
	 *   - @b class (string): nome della classe
	 *   - @b module (integer): valore ID del modulo
	 *   - @b name (string): nome del modulo
	 *   - @b label (string): etichetta del modulo
	 * @return void
	 */
	function __construct($params) {
		
		$db = db::instance();
		
		$this->_class = $params['class'];
		$this->_module = $params['module'];
		$this->_name = $params['name'];
		$this->_label = $params['label'];
		
		$classElements = call_user_func(array($this->_class, 'getClassElements'));
		$this->_css_list = $classElements['css'];
		$this->_view_list = $this->getFileList('view');
		
		$this->_instance_class = array_key_exists('instance', $classElements) ? $classElements['instance'] : true;
		$method = $this->_instance_class ? 'manageDoc' : 'manage'.ucfirst($this->_class);
		$this->_mdlLink = HOME_FILE."?evt[{$this->_name}-{$method}]&block=frontend";
	}
	
	/**
	 * Nome del file
	 * 
	 * @param string $file nome del file
	 * @param string $ext estensione del file
	 * @return string
	 */
	private function fileName($file, $ext) {
		
		$name = $this->_instance_class ? baseFileName($file)."_".$this->_name.".".$ext : $file;
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
		
		$dir = $this->pathToFile($code);
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
		return $array;
	}
	
	/**
	 * Interfaccia per la gestione dei file di front-end dei moduli
	 * 
	 * @see moduleList()
	 * @see formModuleFile()
	 * @see actionModuleFile()
	 * @see moduleInfo()
	 * @return string
	 */
	public function manageFrontend() {

		$action = cleanVar($_GET, 'action', 'string', '');
		$code = cleanVar($_GET, 'code', 'string', '');

		$buffer = "<div class=\"vertical_1\">\n";
		$buffer .= $this->moduleList('css', $code);
		$buffer .= $this->moduleList('view', $code);
		$buffer .= "</div>\n";

		$buffer .= "<div class=\"vertical_2\">\n";
		if($action=='modify') $buffer .= $this->formModuleFile($code);
		elseif($action=='save') $buffer .= $this->actionModuleFile($code);
		else $buffer .= $this->moduleInfo();
		$buffer .= "</div>\n";

		$buffer .= "<div class=\"null\"></div>\n";

		return $buffer;
	}

	private function moduleList($code, $param_code) {
	
		$key = isset($_GET['key']) ? (int) $_GET['key'] : '';
		$key = $key === '' ? null : cleanVar($_GET, 'key', 'int', '');
		
		if(!$param_code) $param_code = null;
		
		if($code == 'css')
		{
			$items = $this->_css_list;
			$num_items = count($items);
			$ext = 'css';
			$title = _("File CSS");
			$text = _("Non risultano file css per il modulo");
		}
		elseif($code == 'view')
		{
			$items = $this->_view_list;
			$num_items = count($items);
			$ext = 'php';
			$title = _("Viste");
			$text = _("Non risultano viste per il modulo");
		}

		$htmlsection = new htmlSection(array('class'=>'admin', 'headerTag'=>'h1', 'headerLabel'=>$title));
		
		if($num_items) {
			$htmlList = new htmlList(array("numItems"=>$num_items, "separator"=>true));
			$buffer = $htmlList->start();
			foreach($items as $k=>$file) {
				$selected = ($key === $k && $code == $param_code) ? true : false;
				$link_modify = "<a href=\"$this->_mdlLink&key=$k&code=$code&action=modify\">".pub::icon('modify')."</a>";
				
				$filename = $this->fileName($file, $ext);
				$buffer .= $htmlList->item($filename, $link_modify, $selected);
			}
			$buffer .= $htmlList->end();
		}
		else
			$buffer = "<p>".$text.' '.htmlChars($this->_label)."</p>\n";

		$htmlsection->content = $buffer;
		return $htmlsection->render();
	}
	
	private function formModuleFile($code) {

		$gform = new Form('gform', 'post', true, array("tblLayout"=>false));
		$gform->load('dataform');

		$key = cleanVar($_GET, 'key', 'int', '');
		
		if($code == 'css')
		{
			$list = $this->_css_list;
			$ext = 'css';
			$title = _("Modifica il file CSS");
		}
		elseif($code == 'view')
		{
			$list = $this->_view_list;
			$ext = 'php';
			$title = _("Modifica la vista");
		}

		$filename = $this->fileName($list[$key], $ext);
		
		$htmlsection = new htmlSection(array('class'=>'admin', 'headerTag'=>'h1', 'headerLabel'=>$title." - ".$filename));

		$required = '';
		$buffer = $gform->form($this->_mdlLink."&action=save&code=$code&key=$key", '', $required);

		$contents = file_get_contents($this->pathToFile($code).$filename);
		
		$buffer .= "<textarea name=\"file_content\" style=\"width:98%;height:300px;overflow:auto;border:2px solid #000;\">".$contents."</textarea>\n";
		
		$buffer .= "<p>".$gform->input('submit_action', 'submit', _("salva"), array("classField"=>"submit"));
		$buffer .= " ".$gform->input('cancel_action', 'button', _("annulla"), array("js"=>"onclick=\"location.href='$this->_mdlLink'\" class=\"generic\""))."</p>";

		$buffer .= $gform->cform();

		$htmlsection->content = $buffer;

		return $htmlsection->render();
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
		
		$filename = $this->fileName($list[$key], $ext);

		$file_content = $_POST['file_content'];
		$fo = fopen($this->pathToFile($code).$filename, 'wb');
		fwrite($fo, $file_content);
		fclose($fo);

		header("Location: ".$this->_mdlLink);
	}

	private function moduleInfo() {
		
		$htmlsection = new htmlSection(array('class'=>'admin', 'headerTag'=>'h1', 'headerLabel'=>_("Informazioni")));
		$buffer = "<p>"._("Selezionare uno dei file elencati a lato per entrare nella modalità di modifica.")."</p>\n";
		
		$htmlsection->content = $buffer;

		return $htmlsection->render();
	}
}
?>
