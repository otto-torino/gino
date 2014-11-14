<?php
/**
 * @file class.directoryField.php
 * @brief Contiene la classe directoryField
 *
 * @copyright 2005 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
namespace Gino;

/**
 * @brief Campo di tipo DIRECTORY
 *
 * @copyright 2005 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class directoryField extends Field {

	/**
	 * Proprietà dei campi specifiche del tipo di campo
	 */
	protected $_path, $_prefix, $_default_name;
	
	/**
	 * Costruttore
	 * 
	 * @param array $options array associativo di opzioni del campo del database
	 *   - opzioni generali definite come proprietà nella classe field()
	 *   - @b path (string): percorso assoluto della directory superiore
	 *   - @b prefix (string): prefisso da aggiungere al nome della directory
	 *   - @b default_name (array): valori per il nome di default
	 *     - @a field (string): nome dell'input dal quale ricavare il nome della directory (default id)
	 *     - @a maxlentgh (integer): numero di caratteri da considerare nel nome dell'input (default 10)
	 *     - @a value_type (string): tipo di valore (default string)
	 * @return void
	 */
	function __construct($options) {

		parent::__construct($options);
		
		$this->_default_widget = 'text';
		$this->_value_type = 'string';
		
		$this->_path = isset($options['path']) ? $options['path'] : '';
		$this->_prefix = isset($options['prefix']) ? $options['prefix'] : '';
		$this->_default_name = isset($options['default_name']) ? $options['default_name'] : array();
	}
	
	public function getPath() {
		
		return $this->_path;
	}
	
	public function setPath($value) {
		
		$this->_path = $value;
	}
	
	public function getPrefix() {
		
		return $this->_prefix;
	}
	
	public function setPrefix($value) {
		
		$this->_prefix = $value;
	}
	
	/**
	 * @see field::clean()
	 */
	public function clean($options=null) {
		
		$value_type = isset($options['value_type']) ? $options['value_type'] : $this->_value_type;
		$method = isset($options['method']) ? $options['method'] : $_POST;
		$escape = gOpt('escape', $options, true);
		
		$value = cleanVar($method, $this->_name, $value_type, null, array('escape'=>$escape));
		
		if(!$value)
			$value = $this->defaultName($options);
		
		if($value != $this->_value)
			$value = $this->checkName($value);
		
		return $value;
	}
	
	private function defaultName($options){
		
		if($this->_default_name)
		{
			$field = array_key_exists('field', $this->_default_name) ? $this->_default_name['field'] : 'id';
			$maxlentgh = array_key_exists('maxlentgh', $this->_default_name) ? $this->_default_name['maxlentgh'] : 10;
			$value_type = array_key_exists('value_type', $this->_default_name) ? $this->_default_name['value_type'] : 'string';
			
			$method = isset($options['method']) ? $options['method'] : $_POST;
			$value = cleanVar($method, $field, $value_type, null);
			
			$name_dir = substr($value, 0, $maxlentgh);
			$name_dir = preg_replace("#[^a-zA-Z0-9_\.-]#", "_", $name_dir);
			return $name_dir;
		}
		else return null;
	}
	
	/**
	 * Sostituisce nel nome di una directory i caratteri diversi da [a-zA-Z0-9_.-] con il carattere underscore (_)
	 * 
	 * Se il nome della directory è presente lo salva aggiungendogli un numero progressivo
	 * 
	 * @param string $name_dir nome della directory
	 * @return string
	 */
	private function checkName($name_dir) {
	
		$name_dir = preg_replace("#[^a-zA-Z0-9_\.-]#", "_", $name_dir);
		$name_dir = $this->_prefix.$name_dir;
		
		$files = scandir($this->_path);
		$i=1;
		
		while(in_array($name_dir, $files)) { $name_dir = substr($name_dir, 0, strrpos($name_dir, '.')+1).$i.substr($name_dir, strrpos($name_dir, '.')); $i++; }
		
		return $name_dir;
	}
	
	/**
	 * @see field::validate()
	 */
	public function validate($value){

		if($value == $this->_value)	// directory preesistente
		{
			return true;
		}
		elseif($value)
		{
			if($_REQUEST['insert'])
			{
				if(!mkdir($this->_path.$value))
					return array('error'=>32);
			}
			elseif($_REQUEST['edit'])
			{
				if(!$this->_value)
				{
					if(!mkdir($this->_path.$value))
						return array('error'=>32);
				}
				else
				{
					if(!rename($this->_path.$this->_value, $this->_path.$value))
						return array('error'=>32);
				}
			}
			
			return true;
		}
		else return true;
	}
	
	/**
	 * Eliminazione della directory
	 * 
	 * @return boolean
	 */
	public function delete() {
		
		if(is_dir($this->_path.$this->_value)) {
			if(!$this->deleteFileDir($this->_path.$this->_value))
				return array('error'=>_("errore nella eliminazione della directory"));
		}

		return true;
	}
	
	/**
	 * Elimina ricorsivamente i file e le directory
	 *
	 * @param string $dir percorso assoluto alla directory
	 * @param boolean $delete_dir per eliminare o meno le directory
	 * @return void
	 */
	public function deleteFileDir($dir, $delete_dir=true){
	
		if(is_dir($dir))
		{
			if(substr($dir, -1) != '/') $dir .= '/';
			
			if($dh = opendir($dir))
			{
				while(($file = readdir($dh)) !== false)
				{
					if($file == "." || $file == "..") continue;
					
					if(is_file($dir.$file)) @unlink($dir.$file);
					else $this->deleteFileDir($dir.$file, true);
				}
				
				if($delete_dir)
				{
					closedir($dh);
					if(!@rmdir($dir))
						return false;
				}
			}
		}
		return true;
	}
}
?>
