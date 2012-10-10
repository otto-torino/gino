<?php
/**
 * @file class.fileField.php
 * @brief Contiene la classe fileField
 *
 * @copyright 2005 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */

/**
 * @brief Campo di tipo FILE (estensione)
 *
 * @copyright 2005 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class fileField extends field {

	/**
	 * Percorso assoluto della directory del file
	 * 
	 * @var string
	 */
	protected $_directory;
	
	/**
	 * Controllo sulla eliminazione del file
	 * 
	 * @var boolean
	 */
	protected $_delete_file;
	
	/**
	 * Proprietà dei campi specifiche del tipo di campo
	 */
	protected $_extensions, $_path_abs, $_path_add, $_prefix, $_check_type, $_types_allowed, $_max_file_size;
	
	/**
	 * Costruttore
	 * 
	 * @param array $options array associativo di opzioni del campo del database
	 *   - opzioni generali definite come proprietà nella classe field()
	 *   - @b extensions (array): estensioni lecite di file
	 *   - @b path (string): percorso assoluto fino a prima del valore del record ID
	 *   - @b add_path (string): parte del percorso assoluto dal parametro @a path fino a prima del file
	 *   - @b prefix (string)
	 *   - @b check_type (boolean)
	 *   - @b types_allowed(array)
	 *   - @b max_file_size (integer)
	 * @return void
	 */
	function __construct($options) {

		parent::__construct($options);
		
		$this->_default_widget = 'file';
		$this->_value_type = null;
		
		$this->_delete_file = false;
		
		$this->_extensions = isset($options['extensions']) ? $options['extensions'] : array('txt','xml','html','htm','doc','xls','zip','pdf');
		$this->_path_abs = isset($options['path']) ? $options['path'] : '';
		$this->_path_add = isset($options['add_path']) ? $options['add_path'] : '';
		$this->_prefix = isset($options['prefix']) ? $options['prefix'] : '';
		$this->_check_type = isset($options['check_type']) ? $options['check_type'] : false;
		$this->_types_allowed = isset($options['types_allowed']) ? $options['types_allowed'] : array(
			"text/plain",
			"text/html",
			"text/xml",
			"video/mpeg",
			"audio/midi",
			"application/pdf",
			"application/x-compressed",
			"application/x-zip-compressed",
			"application/zip",
			"multipart/x-zip",
			"application/vnd.ms-excel",
			"application/msword",
			"application/x-msdos-program",
			"application/octet-stream"
		);
		$this->_max_file_size = isset($options['max_file_size']) ? $options['max_file_size'] : null;
		
		$this->_directory = $this->pathToFile();
	}
	
	public function getExtensions() {
		
		return $this->_extensions;
	}
	
	public function setExtensions($value) {
		
		$this->_extensions = $value;
	}
	
	public function getPath() {
		
		return $this->_path_abs;
	}
	
	public function setPath($value) {
		
		$this->_path_abs = $value;
	}
	
	public function getAddPath() {
		
		return $this->_path_add;
	}
	
	public function setAddPath($value) {
		
		$this->_path_add = $value;
	}
	
	public function getPrefix() {
		
		return $this->_prefix;
	}
	
	public function setPrefix($value) {
		
		$this->_prefix = $value;
	}
	
	public function getCheckType() {
		
		return $this->_check_type;
	}
	
	public function setCheckType($value) {
		
		$this->_check_type = $value;
	}
	
	public function getTypesAllowed() {
		
		return $this->_types_allowed;
	}
	
	public function setTypesAllowed($value) {
		
		$this->_types_allowed = $value;
	}
	
	public function getMaxFileSize() {
		
		return $this->_max_file_size;
	}
	
	public function setMaxFileSize($value) {
		
		$this->_max_file_size = $value;
	}
	
	public function getDirectory() {
		
		return $this->_directory;
	}
	
	public function setDirectory($value) {
		
		$this->_directory = $value;
	}
	
	/**
	 * Stampa l'elemento del form
	 * 
	 * @param object $form
	 * @param array $options opzioni dell'elemento del form
	 * @return string
	 */
	public function formElement($form, $options) {
		
		if(!isset($options['extensions'])) $options['extensions'] = $this->_extensions;
		
		return parent::formElement($form, $options);
	}
	
	/**
	 * @see field::clean()
	 */
	public function clean($options=null) {
		
		if(isset($_FILES[$this->_name]['name']) AND $_FILES[$this->_name]['name'] != '')
		{
			$filename = $_FILES[$this->_name]['name'];
			$filename = $this->checkFilename($filename, $this->_prefix);
		}
		else $filename = '';
		
		$check_name = "check_del_".$this->_name;
		$check_delete = (isset($_POST[$check_name]) && $_POST[$check_name]=='ok');
		$delete = (($filename && $this->_value) || $check_delete) ? true : false;
		$upload = $filename ? true : false;
		
		$this->_delete_file = $delete;
		
		if($upload) $file = $filename;
		elseif($delete) $file = '';
		else $file = $this->_value;
		
		return $file;
	}
	
	/**
	 * @see field::validate()
	 */
	public function validate($filename){

		if($filename == $this->_value)	// file preesistente
		{
			return true;
		}
		elseif($filename)
		{
			$filename_size = $_FILES[$this->_name]['size'];
			$filename_tmp = $_FILES[$this->_name]['tmp_name'];
			
			if($this->_max_file_size && $filename_size > $this->_max_file_size) {
				return array('error'=>33);
			}
			
			$finfo = finfo_open(FILEINFO_MIME_TYPE);
			$mime = finfo_file($finfo, $filename_tmp);
			finfo_close($finfo);
			if(
				!extension($filename, $this->_extensions) ||
				preg_match('#%00#', $filename) ||
				($this->_check_type && !in_array($mime, $this->_types_allowed))
			) {
				return array('error'=>03);
			}
			
			$this->saveFile($filename, $filename_tmp);
			return true;
		}
		else return true;
	}
	
	protected function saveFile($filename, $filename_tmp) {
		
		$upload = move_uploaded_file($filename_tmp, $this->_directory.$filename) ? true : false;
		if(!$upload) { 
			return array('error'=>16);
		}
		
		if($this->_delete_file)
			return $this->delete();
	}
	
	/**
	 * Eliminazione diretta del file
	 * 
	 * @return boolean
	 */
	public function delete() {
		
		if(is_file($this->_directory.$this->_value)) {
			if(!@unlink($this->_directory.$this->_value)) {
				return array('error'=>17);
			}
		}

		return true;
	}
	
	/**
	 * Ricostruisce il percorso a un file
	 * 
	 * @param array $options
	 *   array associativo di opzioni
	 *   - @b type (string): tipo di percorso
	 *     - @a abs: assoluto
	 *     - @a rel: relativo
	 *   - @b thumb_file (boolean): file thumbnail
	 *   - @b complete (boolean): percorso completo col nome del file
	 * @return string
	 */
	protected function pathToFile($options=array()) {
		
		$type = array_key_exists('type', $options) ? $options['type'] : 'abs';
		$complete = array_key_exists('complete', $options) ? $options['complete'] : false;
		$thumb_file = array_key_exists('thumb_file', $options) ? $options['thumb_file'] : false;
		
		$filename = $thumb_file ? $this->_prefix_thumb.$this->_value: $this->_value;
		$directory = $this->_path_abs.$this->_path_add;
		$directory = $this->conformPath($directory);
		if($type == 'abs')
			if(!is_dir($directory)) mkdir($directory, 0755, true);
		
		if($complete)
			$directory = $directory.$filename;
		
		if($type == 'rel')
			$directory = relativePath($directory);
		
		return $directory;
	}
	
	/**
	 * Imposta il carattere '/' come ultimo carattere della directory
	 *
	 * @param string $directory nome della directory
	 * @return string
	 */
	private function conformPath($directory){
		
		$directory = (substr($directory, -1) != '/' && $directory != '') ? $directory.'/' : $directory;
		return $directory;
	}
	
	/**
	 * Sostituisce nel nome di un file i caratteri diversi da [a-zA-Z0-9_.-] con il carattere underscore (_)
	 * 
	 * Se il nome del file è presente lo salva aggiungendogli un numero progressivo
	 * 
	 * @param string $filename nome del file
	 * @param string $prefix prefisso da aggiungere al nome del file
	 * @return string
	 */
	private function checkFilename($filename, $prefix) {
	
		$filename = preg_replace("#[^a-zA-Z0-9_\.-]#", "_", $filename);
		$filename = $prefix.$filename;
		
		$files = scandir($this->_directory);
		$i=1;
		
		while(in_array($filename, $files)) { $filename = substr($filename, 0, strrpos($filename, '.')+1).$i.substr($filename, strrpos($filename, '.')); $i++; }
		
		return $filename;
	}
}
?>
