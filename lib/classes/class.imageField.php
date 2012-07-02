<?php
/**
 * @file class.imageField.php
 * @brief Contiene la classe imageField
 *
 * @copyright 2005 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */

/**
 * @brief Campo di tipo IMMAGINE (estensione)
 * 
 * Tipologie di input associabili: testo di tipo file
 *
 * @copyright 2005 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class imageField extends field {

	/**
	 * Proprietà dei campi specifiche del tipo di campo
	 */
	protected $_extensions, $_path_abs, $_path_add, $_prefix_thumb;
	
	/**
	 * Costruttore
	 * 
	 * @param array $options array associativo di opzioni del campo del database
	 *   - opzioni generali definite come proprietà nella classe field()
	 *   - @b extensions (array): estensioni lecite di file
	 *   - @b path (string): percorso assoluto fino a prima del valore del record ID
	 *   - @b add_path (string): parte del percorso assoluto dal parametro @a path fino a prima del file
	 *   - @b prefix_thumb (string): prefisso del file thumbnail
	 * @return void
	 */
	function __construct($options) {

		parent::__construct($options);
		
		$this->_default_widget = 'image';
		
		$this->_extensions = isset($options['extensions']) ? $options['extensions'] : array();
		$this->_path_abs = isset($options['path']) ? $options['path'] : '';
		$this->_path_add = isset($options['add_path']) ? $options['add_path'] : '';
		$this->_prefix_thumb = isset($options['prefix_thumb']) ? $options['prefix_thumb'] : 'thumb_';
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
	
	public function getPrefixThumb() {
		
		return $this->_prefix_thumb;
	}
	
	public function setPrefixThumb($value) {
		
		$this->_prefix_thumb = $value;
	}
	
	/**
	 * Ricostruisce il percorso al file immagine
	 * 
	 * @param string $type tipo di percorso
	 *   - @a abs: assoluto
	 *   - @a rel: relativo
	 * @param boolean $thumb file thumbnail
	 * @return string
	 */
	private function pathToImage($type='abs', $thumb=false) {
		
		$filename = $thumb ? $this->_prefix_thumb.$this->_value: $this->_value;
		$path = $this->_path_abs.$this->_path_add.$filename;
		
		if($type == 'rel')
			$path = relativePath($path);
		
		return $path;
	}
	
	/**
	 * Stampa l'elemento del form
	 * 
	 * @param object $form
	 * @param array $options opzioni dell'elemento del form
	 * @return string
	 */
	public function formElement($form, $options) {
		
		if(isset($options['preview']) && $options['preview'] && $this->_value != '')
		{
			$options['previewSrc'] = $this->pathToImage('rel', true);
		}
		
		if(!isset($options['extensions'])) $options['extensions'] = $this->_extensions;
		
		return parent::formElement($form, $options);
	}
}
?>
