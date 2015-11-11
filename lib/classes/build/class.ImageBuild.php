<?php
/**
 * @file class.ImageBuild.php
 * @brief Contiene la definizione ed implementazione della classe Gino.ImageBuild
 *
 * @copyright 2015 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
namespace Gino;

Loader::import('class/build', array('\Gino\Build', '\Gino\FileBuild'));

/**
 * @brief Gestisce campi di tipo IMMAGINE
 *
 * @copyright 2015 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class ImageBuild extends FileBuild {

    const _IMAGE_GIF_ = 1;
    const _IMAGE_JPG_ = 2;
    const _IMAGE_PNG_ = 3;

    /**
     * Proprietà dei campi specifiche del modello
     */
    protected $_resize, $_thumb, $_prefix_file, $_prefix_thumb, $_width, $_height, $_thumb_width, $_thumb_height;

    /**
     * @brief Costruttore
     *
     * @see Gino.Build::__construct()
     * @param array $options array associativo di opzioni del campo del database
     *   - opzioni generali definite come proprietà nella classe Build()
     *   - opzioni generali definite come proprietà nella classe FileBuild()
     */
    function __construct($options) {

        parent::__construct($options);

        $this->_extensions = $options['extensions'];
        $this->_types_allowed = $options['types_allowed'];

        $this->_resize = $options['resize'];
        $this->_thumb = $options['thumb'];
        $this->_prefix_file = $options['prefix_file'];
        $this->_prefix_thumb = $options['prefix_thumb'];
        $this->_width = $options['width'];
        $this->_height = $options['height'];
        $this->_thumb_width = $options['thumb_width'];
        $this->_thumb_height = $options['thumb_height'];
    }

    /**
     * @brief Getter della proprietà resize (ridimensionare immagine)
     * @return proprietà resize
     */
    public function getResize() {

        return $this->_resize;
    }

    /**
     * @brief Setter della proprietà resize
     * @param bool $value
     * @return void
     */
    public function setResize($value) {

        $this->_resize = $value;
    }

    /**
     * @brief Getter della proprietà thumb (creazione thumb)
     * @return proprietà thumb
     */
    public function getThumb() {

        return $this->_thumb;
    }

    /**
     * @brief Setter della proprietà thumb
     * @param bool $value
     * @return void
     */
    public function setThumb($value) {

        $this->_thumb = $value;
    }

    /**
     * @brief Getter della proprietà prefix_file
     * @return proprietà prefix_file
     */
    public function getPrefixFile() {

        return $this->_prefix_file;
    }

    /**
     * @brief Setter della proprietà prefix_file
     * @param string $value
     * @return void
     */
    public function setPrefixFile($value) {

        $this->_prefix_file = $value;
    }

    /**
     * @brief Getter della proprietà prefix_thumb
     * @return proprietà prefix_thumb
     */
    public function getPrefixThumb() {

        return $this->_prefix_thumb;
    }

    /**
     * @brief Setter della proprietà prefix_thumb
     * @param bool $value
     * @return void
     */
    public function setPrefixThumb($value) {

        $this->_prefix_thumb = $value;
    }

    /**
     * @brief Getter della proprietà width (larghezza di ridimensionamento)
     * @return proprietà width
     */
    public function getWidth() {

        return $this->_width;
    }

    /**
     * @brief Setter della proprietà width
     * @param int $value
     * @return void
     */
    public function setWidth($value) {

        $this->_width = $value;
    }

    /**
     * @brief Getter della proprietà height (altezza di ridimensionamento)
     * @return proprietà height
     */
    public function getHeighth() {

        return $this->_height;
    }

    /**
     * @brief Setter della proprietà height
     * @param int $value
     * @return void
     */
    public function setHeight($value) {

        $this->_height = $value;
    }

    /**
     * @brief Getter della proprietà thumb_width (larghezza thumb)
     * @return proprietà thumb_width
     */
    public function getThumbWidth() {

        return $this->_thumb_width;
    }

    /**
     * @brief Setter della proprietà thumb_width
     * @param int $value
     * @return void
     */
    public function setThumbWidth($value) {

        $this->_thumb_width = $value;
    }

    /**
     * @brief Getter della proprietà thumb_height (altezza thumb)
     * @return proprietà thumb_height
     */
    public function getThumbHeighth() {

        return $this->_thumb_height;
    }

    /**
     * @brief Setter della proprietà thumb_height
     * @param int $value
     * @return void
     */
    public function setThumbHeight($value) {

        $this->_thumb_height = $value;
    }

    /**
     * @see Gino.Build::formElement()
     */
    public function formElement(\Gino\Form $form, $options) {

        if(!isset($options['extensions'])) $options['extensions'] = $this->_extensions;

        return parent::formElement($form, $options);
    }

    /**
     * @brief Salvataggio immagine
     * @see Gino.FileBuild::saveFile()
     */
    protected function saveFile($filename, $filename_tmp) {

        if(!is_dir($this->_directory))
            if(!@mkdir($this->_directory, 0755, true))
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

        return TRUE;
    }

    /**
     * @brief Eliminazione immagine
     * @see Gino.FileBuild::delete()
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

        return TRUE;
    }

    /**
     * @brief Salva le immagini eventualmente ridimensionandole
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
     * @return risultato operazione, bool
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
            elseif($thumb)
            {
                copy($file, $thumb_file);
            }

            @unlink($file);
            return TRUE;
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
            return TRUE;
        }
        else
        {
            @unlink($file);
            return FALSE;
        }
    }

    /**
     * @brief Calcola le dimensioni alle quali deve essere ridimensionata una immagine
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
