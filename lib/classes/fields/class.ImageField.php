<?php
/**
 * @file class.imageField.php
 * @brief Contiene la classe imageField
 *
 * @copyright 2005 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
namespace Gino;

loader::import('class/fields', array('\Gino\Field', '\Gino\FileField'));

/**
 * @brief Campo di tipo IMMAGINE (estensione)
 * 
 * Tipologie di input associabili: testo di tipo file
 *
 * @copyright 2005 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class ImageField extends FileField {

	const _IMAGE_GIF_ = 1;
	const _IMAGE_JPG_ = 2;
	const _IMAGE_PNG_ = 3;
	
	/**
	 * Proprietà dei campi specifiche del tipo di campo
	 */
	protected $_resize, $_thumb, $_prefix_file, $_prefix_thumb, $_width, $_height, $_thumb_width, $_thumb_height;
	
	/**
	 * Costruttore
	 * 
	 * @param array $options array associativo di opzioni del campo del database
	 *   - opzioni generali definite come proprietà nella classe field()
	 *   - opzioni generali definite come proprietà nella classe fileField()
	 *   - @b resize (boolean)
	 *   - @b thumb (boolean)
	 *   - @b prefix_file (string)
	 *   - @b prefix_thumb (string)
	 *   - @b width (integer)
	 *   - @b height (integer)
	 *   - @b thumb_width (integer)
	 *   - @b thumb_height (integer)
	 * @return void
	 */
	function __construct($options) {

		parent::__construct($options);
		
		$this->_default_widget = 'image';
		
		$this->_extensions = isset($options['extensions']) ? $options['extensions'] : array("jpg, png");
		$this->_types_allowed = isset($options['types_allowed']) ? $options['types_allowed'] : array(
			"image/jpeg",
			"image/gif",
			"image/png"
		);
		
		$this->_resize = isset($options['resize']) ? $options['resize'] : true;
		$this->_thumb = isset($options['thumb']) ? $options['thumb'] : true;
		$this->_prefix_file = isset($options['prefix_file']) ? $options['prefix_file'] : '';
		$this->_prefix_thumb = isset($options['prefix_thumb']) ? $options['prefix_thumb'] : 'thumb_';
		$this->_width = isset($options['width']) ? $options['width'] : 800;
		$this->_height = isset($options['height']) ? $options['height'] : null;
		$this->_thumb_width = isset($options['thumb_width']) ? $options['thumb_width'] : 200;
		$this->_thumb_height = isset($options['thumb_height']) ? $options['thumb_height'] : null;
	}
	
	public function getResize() {
		
		return $this->_resize;
	}
	
	public function setResize($value) {
		
		$this->_resize = $value;
	}
	
	public function getThumb() {
		
		return $this->_thumb;
	}
	
	public function setThumb($value) {
		
		$this->_thumb = $value;
	}
	
	public function getPrefixFile() {
		
		return $this->_prefix_file;
	}
	
	public function setPrefixFile($value) {
		
		$this->_prefix_file = $value;
	}
	
	public function getPrefixThumb() {
		
		return $this->_prefix_thumb;
	}
	
	public function setPrefixThumb($value) {
		
		$this->_prefix_thumb = $value;
	}
	
	public function getWidth() {
		
		return $this->_width;
	}
	
	public function setWidth($value) {
		
		$this->_width = $value;
	}
	
	public function getHeighth() {
		
		return $this->_height;
	}
	
	public function setHeight($value) {
		
		$this->_height = $value;
	}
	
	public function getThumbWidth() {
		
		return $this->_thumb_width;
	}
	
	public function setThumbWidth($value) {
		
		$this->_thumb_width = $value;
	}
	
	public function getThumbHeighth() {
		
		return $this->_thumb_height;
	}
	
	public function setThumbHeight($value) {
		
		$this->_thumb_height = $value;
	}
	
	/**
	 * Stampa l'elemento del form
	 * 
	 * @param object $form
	 * @param array $options opzioni dell'elemento del form
	 * @return string
	 */
	public function formElement($form, $options) {
		
		if($this->_value != '' and (!isset($options['preview']) or $options['preview']))
		{
			$options['preview'] = true;
			$options['previewSrc'] = $this->pathToFile(array('type'=>'rel', 'complete'=>true));
		}
		
		if(!isset($options['extensions'])) $options['extensions'] = $this->_extensions;
		
		return parent::formElement($form, $options);
	}
	
	/**
	 * @see fileField::saveFile()
	 */
	protected function saveFile($filename, $filename_tmp) {
		
		if(!is_dir($this->_directory))
			if(!mkdir($this->_directory, 0755, true))
				return array('error'=>32);
		
		$upload = move_uploaded_file($filename_tmp, $this->_directory.$filename) ? true : false;
		if(!$upload) { 
			return array('error'=>16);
		}
		if($this->_resize) {
			
			if(!$this->_thumb) { $this->_thumb_width = $this->_thumb_height = null; }

			if(!$this->saveImage($filename, $this->_prefix_file, $this->_prefix_thumb, $this->_width, $this->_height, $this->_thumb_width, $this->_thumb_height)) {
				return array('error'=>18);
			}
		}
		
		if($this->_delete_file)
		{
			if($this->_resize)
			{
				if(is_file($this->_directory.$this->_prefix_file.$this->_value)) 
					if(!@unlink($this->_directory.$this->_prefix_file.$this->_value)) {
						return array('error'=>17);
				}
				
				if($this->_thumb && !empty($this->_prefix_thumb)) {
					if(is_file($this->_directory.$this->_prefix_thumb.$this->_value))
						if(!@unlink($this->_directory.$this->_prefix_thumb.$this->_value)) {
							return array('error'=>17);
						}
				}
			}
			elseif(!$this->_resize)
			{
				if(is_file($this->_directory.$this->_value)) 
					if(!@unlink($this->_directory.$this->_value)) {
						return array('error'=>17);
					}
			}
		}

		return true;
	}
	
	/**
	 * @see fileField::delete()
	 */
	public function delete() {
		
		if($this->_resize)
		{
			if(is_file($this->_directory.$this->_prefix_file.$this->_value)) 
				if(!@unlink($this->_directory.$this->_prefix_file.$this->_value)) {
					return array('error'=>17);
			}
			
			if($this->_thumb && !empty($this->_prefix_thumb)) {
				if(is_file($this->_directory.$this->_prefix_thumb.$this->_value))
					if(!@unlink($this->_directory.$this->_prefix_thumb.$this->_value)) {
						return array('error'=>17);
					}
			}
		}
		elseif(!$this->_resize)
		{
			if(is_file($this->_directory.$this->_value)) 
				if(!@unlink($this->_directory.$this->_value)) {
					return array('error'=>17);
				}
		}

		return true;
	}
	
	/**
	 * Salva le immagini eventualmente ridimensionandole
	 * 
	 * Se @b thumb_width e @b thumb_height sono nulli, il thumbnail non viene generato
	 * 
	 * @param string $filename nome del file
	 * @param string $prefix_file prefisso da aggiungere al file
	 * @param string $prefix_thumb prefisso da aggiungere al thumbnail
	 * @param integer $new_width larghezza dell'immagine
	 * @param integer $new_height altezza dell'immagine
	 * @param integer $thumb_width larghezza del thumbnail
	 * @param integer $thumb_height altezza del thumbnail
	 * @return boolean
	 */
	protected function saveImage($filename, $prefix_file, $prefix_thumb, $new_width, $new_height, $thumb_width, $thumb_height){

		$thumb = (is_null($thumb_width) && is_null($thumb_height)) ? false : true;
		$file = $this->_directory.$filename;
		list($im_width, $im_height, $type) = getimagesize($file);
		
		if(empty($prefix_file))
		{
			$rename = $this->_directory.'tmp_'.$filename;
			if(rename($file, $rename))
				$file = $rename;
		}
		
		$img_file = $this->_directory.$prefix_file.$filename;
		$img_size = $this->resizeImage($new_width, $new_height, $im_width, $im_height);

		if($thumb)
		{
			$thumb_file = $this->_directory.$prefix_thumb.$filename;
			$thumb_size = $this->resizeImage($thumb_width, $thumb_height, $im_width, $im_height);
		}
		
		if($type == self::_IMAGE_JPG_)
		{
			if($img_size[0] != $im_width AND $img_size[1] != $im_height)
			{
				$sourcefile_id = @imagecreatefromjpeg($file);
				$destfile_id = imagecreatetruecolor($img_size[0], $img_size[1]);
				imagecopyresampled($destfile_id, $sourcefile_id, 0, 0, 0, 0, $img_size[0], $img_size[1], $im_width, $im_height);
				imagejpeg($destfile_id, $img_file);
			}
			else
			{
				copy($file, $img_file);
			}
			
			if($thumb && $thumb_size[0] != $im_width && $thumb_size[1] != $im_height)
			{
				$sourcefile_id = @imagecreatefromjpeg($file);
				$destfile_id = imagecreatetruecolor($thumb_size[0], $thumb_size[1]);
				imagecopyresampled($destfile_id, $sourcefile_id, 0, 0, 0, 0, $thumb_size[0], $thumb_size[1], $im_width, $im_height);
				imagejpeg($destfile_id, $thumb_file);
			}
			else
			{
				copy($file, $thumb_file);
			}
			
			@unlink($file);
			return true;
		}
		elseif($type == self::_IMAGE_PNG_)
		{
			if($img_size[0] != $im_width AND $img_size[1] != $im_height)
			{
				$sourcefile_id = @imagecreatefrompng($file);
				$destfile_id = imagecreatetruecolor($img_size[0], $img_size[1]);
				imagecopyresampled($destfile_id, $sourcefile_id, 0, 0, 0, 0, $img_size[0], $img_size[1], $im_width, $im_height);
				imagepng($destfile_id, $img_file);
			}
			else
			{
				copy($file, $img_file);
			}
			
			if($thumb && $thumb_size[0] != $im_width && $thumb_size[1] != $im_height)
			{
				$sourcefile_id = @imagecreatefrompng($file);
				$destfile_id = imagecreatetruecolor($thumb_size[0], $thumb_size[1]);
				imagecopyresampled($destfile_id, $sourcefile_id, 0, 0, 0, 0, $thumb_size[0], $thumb_size[1], $im_width, $im_height);
				imagepng($destfile_id, $thumb_file);
			}
			else
			{
				copy($file, $thumb_file);
			}
			
			@unlink($file);
			return true;
		}
		else
		{
			@unlink($file);
			return false;
		}
	}
	
	/**
	 * Calcola le dimensioni alle quali deve essere ridimensionata una immagine
	 * 
	 * @param integer $new_width
	 * @param integer $new_height
	 * @param integer $im_width
	 * @param integer $im_height
	 * @return array (larghezza, altezza)
	 */
	private function resizeImage($new_width, $new_height, $im_width, $im_height){
		
		if(!empty($new_width) AND $im_width > $new_width)
		{
			$width = $new_width;
			$height = ($im_height / $im_width) * $new_width;
		}
		elseif(!empty($new_height) AND $im_height > $new_height)
		{
			$width = ($im_width / $im_height) * $new_height;
			$height = $new_height;
		}
		else
		{
			$width = $im_width;
			$height = $im_height;
		}
		
		return array($width, $height);
	}
}
?>
