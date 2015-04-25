<?php
/**
 * @file class.GImage.php
 * @brief Contiene la definizione ed implementazione della classe Gino.GImage
 *
 * @copyright 2014 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
namespace Gino;

define('GIMAGE_DIR', CONTENT_DIR.OS.'gimage');

/**
 * @brief Classe per il trattamento di immagini
 *
 * @copyright 2014 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class GImage {

    private $_table = 'sys_gimage',
            $_dir = GIMAGE_DIR,
            $_abspath,
            $_image,
            $_width,
            $_height,
            $_image_type,
            $_tmp_image;


    /**
     * @brief Costruttore
     * @param string $abspath percorso assoluto del file
     * @return istanza di Gino.GImage
     */
    public function __construct($abspath) {

        if(!is_file($abspath)) {
            throw new \Exception(sprintf('Il file con path %s non esiste', $abspath));
        }

        $image_info = getimagesize($abspath);
        $this->_abspath = $abspath;
        $this->_width = $image_info[0];
        $this->_height = $image_info[1];
        $this->_image_type = $image_info[2];

        if($this->_image_type == IMAGETYPE_JPEG) {
            $this->_image = imagecreatefromjpeg($abspath);
        }
        elseif($this->_image_type == IMAGETYPE_GIF) {
            $this->_image = imagecreatefromgif($abspath);
        }
        elseif($this->_image_type == IMAGETYPE_PNG) {
            $this->_image = imagecreatefrompng($abspath);
        }
        else {
            throw new \Exception('Formato immagine non supportato');
        }

        imagealphablending($this->_image, FALSE);
        imagesavealpha($this->_image, TRUE);
    }

    /**
     * @brief Ritorna il percorso relativo dell'immagine (da usare come attributo src del tag img)
     * @see Gino.relativePath
     * @return path relativo immagine
     */
    public function getPath() {
        return relativePath($this->_abspath);
    }

    /**
     * @brief Ritorna la larghezza dell'immagine
     * @return larghezza immagine in px
     */
    public function getWidth() {
        return $this->_width;
    }

    /**
     * @brief Ritorna l'altezza dell'immagine
     * @return altezza immagine in px
     */
    public function getHeight() {
        return $this->_height;
    }

    /**
     * @brief Ritorna la resource dell'immagine
     * @return resource immagine
     */
    public function getResource() {
        return $this->_image;
    }

    /**
     * @brief Salva l'immagine su filesystem
     * @param string $abspath percorso (default il percorso originale dell'immagine)
     * @param int $compression compressione, default 75
     * @param string $permission permessi
     * @return void
     */
    public function save($abspath = null, $compression=75, $permissions=null) {

        if(is_null($abspath)) {
            $abspath = $this->_abspath;
        }

        if($this->_image_type == IMAGETYPE_JPEG) {
            imagejpeg($this->_image, $abspath, $compression);
        }
        elseif($this->_image_type == IMAGETYPE_GIF) {
            imagegif($this->_image, $abspath);
        }
        elseif($this->_image_type == IMAGETYPE_PNG) {
            imagepng($this->_image, $abspath);
        }

        if($permissions != null) {
            chmod($filepath, $permissions);
        }
    }

    /**
     * @brief Output dell'immagine
     *
     * @code
     *   ob_start();
     *   $image->stream();
     *   $i = ob_get_clean();
     *   echo "<img src='data:image/jpeg;base64," . base64_encode( $i )."'>";
     * @endcode
     * @param int $compression compressione, default 75
     * @return stream immagine
     */
    public function stream($compression=75) {

        if($this->_image_type == IMAGETYPE_JPEG) {
            imagejpeg($this->_image, null, $compression);
        }
        elseif($this->_image_type == IMAGETYPE_GIF) {
            imagegif($this->_image);
        }
        elseif($this->_image_type == IMAGETYPE_PNG) {
            imagepng($this->_image);
        }

    }

    /*
     * @brief Resize dell'immagine
     *
     * @param int|null $width Larghezza della thumb
     * @param int|null $height Altezza della thumb
     * @param array $options Opzioni.
     *              Array associativo di opzioni:
     *              - 'allow_enlarge': default true. Consente l'allargamento di immagini per soddisfare le dimensioni richieste.
     * @return void
     */
    public function resize($width, $height, $options) {
        if($width === null or $height === null) {
            $ratio = $width === null ? $height / $this->_height : $width / $this->_width;
            $this->_image = $this->resizeImage($this->_image, $this->_width * $ratio, $this->_height * $ratio, $options);
        }
        else {
            $this->_image = $this->resizeImage($this->_image, $width, $height, $options);
        }
    }

    /**
     * @brief Crop dell'immagine con larghezza, altezza e punto iniziali dati
     * @param int $width Larghezza crop
     * @param int $height Altezza crop
     * @param int $xo Coordinata x punto top left di taglio
     * @param int $yo Coordinata y punto top left di taglio
     * @param array $options Opzioni.
     * @return void
     */
    public function crop($width, $height, $xo, $y0, $options = array()) {
        $this->_image = $this->cropImage($this->_image, $width, $height, $x0, $yo, $options);
    }

    /**
     * @brief Crop centrale dell'immagine con larghezza e altezza dati
     * @param int $width Larghezza crop
     * @param int $height Altezza crop
     * @param array $options Opzioni.
     * @return void
     */
    public function cropCenter($width, $height, $options = array()) {
        $x0 = (imagesx($this->_image) - $width)/2;
        $y0 = (imagesy($this->_image) - $height)/2;
        $this->_image = $this->cropImage($this->_image, $width, $height, $x0, $y0, $options);
    }

    /**
     * @brief Crop dell'immagine con larghezza e altezza dati nella zona a massima entropia
     * @param int $width Larghezza crop
     * @param int $height Altezza crop
     * @param array $options Opzioni.
     * @return void
     */
    public function cropEntropy($width, $height, $options = array()) {
        $this->_image = $this->cropImageEntropy($this->_image, $width, $height, $options);
    }

    /**
     * @brief Genera una thumb delle dimensioni
     * @description Le thumb sono generate al volo e tenute in cache su db.
     *              Se viene richiesta una thumb già creata viene direttamente
     *              restituita. Altrimenti viene creata.
     * @param int|null $width Larghezza della thumb
     * @param int|null $height Altezza della thumb
     * @param array $options Opzioni.
     *                       Array associativo di opzioni:
     *                       - 'allow_enlarge': default false. Consente l'allargamento di immagini per soddisfare le dimensioni richieste.
     * @return Gino.GImage nuovo oggetto immagine wrapper della thumb generata
     */
    public function thumb($width, $height, $options = array()) {

        $key = $this->toKey($this->_abspath, $width, $height, $options);
        
        if(!$thumb = $this->getThumbFromKey($key)) {
        	$thumb = $this->makeThumb($key, $width, $height, $options);
        }
        return $thumb;
    }

    /**
     * @brief Genera una key univoca per un'operazione eseguita su un'immagine
     * @param string $abspath percorso assoluto dell'immagine
     * @param int $width Larghezza dell'immagine dopo l'operazione
     * @param int $height Altezza dell'immagine dopo l'operazione
     * @params array $options Opzioni
     * @return chiave univoca
     */
    private function toKey($abspath, $width, $height, $options) {
        $json_obj = array(
            'path' => $abspath,
            'time' => filemtime($abspath),
            'width' => $width,
            'height' => $height,
            'options' => $options
        );
        $key = md5(json_encode($json_obj));
        return $key;
    }

    /**
     * @brief Recupera il path dell'immagine se già stata sottoposta alla stessa operazione (cache)
     * 
     * @param string $key chiave univoca dell'operazione
     * @return Gino.GImage|FALSE oggetto GImage dell'immagine in cache o false
     */
    private function getThumbFromKey($key) {
        
    	$db = db::instance();
        $rows = $db->select('path', $this->_table, "`key`='".$key."'");
        if($rows and count($rows) == 1) {
            
        	$path_to_file = absolutePath($rows[0]['path']);
        	
        	if(is_file($path_to_file)) {
        		return new GImage($path_to_file);
        	}
        	else {
        		$db->delete($this->_table, "`key`='".$key."'");
        	}
        }

        return FALSE;
    }

    /**
     * @brief Genera una thumb delle dimensioni fornite
     * @description Se viene fornita solo una dimensione oppure entrambe le dimensioni fornite rispecchiano il ratio dell'immagine originale viene effettuato un resize.
     *              Altrimenti viene effettuato un resize prima ed un crop dopo, cercando di tagliare la parte di immagine con maggiore entropia.
     * @param string $key Chiave univoca che identifica la thumb da generare
     * @param int|null $width Larghezza della thumb
     * @param int|null $height Altezza della thumb
     * @param array $options Opzioni.
     *                       Array associativo di opzioni:
     *                       - 'allow_enlarge': default false. Consente l'allargamento di immagini per soddisfare le dimensioni richieste.
     * @return Gino.GImage nuovo oggetto GImage della thumb generata
     */
    private function makeThumb($key, $width, $height, $options) {
        // simple resize or enlargement
        if($width === null or $height === null or round($width/$height, 2) == round($this->_width/$this->_height, 2)) {
            $ratio = $width === null ? $height / $this->_height : $width / $this->_width;
            $this->_tmp_image = $this->resizeImage($this->_image, $this->_width * $ratio, $this->_height * $ratio, $options);
        }
        // resize || enlargement + crop
        else {
            // force enlargement
            $options['allow_enlarge'] = true;
            $ratio = ($this->_width / $this->_height) < ($width / $height) ? $width / $this->_width : $height / $this->_height;
            $this->_tmp_image = $this->resizeImage($this->_image, $this->_width * $ratio, $this->_height * $ratio, $options);
            // crop
            $this->_tmp_image = $this->cropImageEntropy($this->_tmp_image, $width, $height, $options);
        }
        
        $thumb_path = $this->saveTmpImage($key);
        return new GImage($thumb_path);
    }

    /**
     * @brief Salva l'immagine temporanea su filesystem e su db
     * @return Gino.GImage oggetto GImage della nuova immagine salvata
     */
    private function saveTmpImage($key, $compression=75) {

        $db = db::instance();

        $pathinfo = pathinfo($this->_abspath);

        $filename = sprintf('gimage_%d.%s', $db->autoIncValue($this->_table), $pathinfo['extension']);
        $path = $this->_dir.OS.$filename;
        $data = array(
            'key' => $key,
            'path' => relativePath($path),
            'width' => imagesx($this->_tmp_image),
            'height' => imagesy($this->_tmp_image)
        );
        if($db->insert($data, $this->_table)) {
            if($this->_image_type == IMAGETYPE_JPEG) {
                imagejpeg($this->_tmp_image, $path, $compression);
            }
            elseif($this->_image_type == IMAGETYPE_GIF) {
                imagegif($this->_tmp_image, $path);
            }
            elseif($this->_image_type == IMAGETYPE_PNG) {
                imagepng($this->_tmp_image, $path);
            }
            return $path;
        }
        else {
            throw new \Exception('thumb creation error');
        }
    }

    /**
     * @brief Resize dell'immagine alle dimensioni fornite
     * @param resource $image resource dell'immagine
     * @param int $width Larghezza della thumb
     * @param int $height Altezza della thumb
     * @param array $options Opzioni.
     * @return resource immagine ridimensionata
     */
    public function resizeImage($image, $width, $height, $options = array()) {

        $allow_enlarge = gOpt('allow_enlarge', $options, true);
        if(!$allow_enlarge and ($width > imagesx($image) or $height > imagesy($image))) {
            return $image;
        }

        $new_image = imagecreatetruecolor($width, $height);
        imagecopyresampled($new_image, $image, 0, 0, 0, 0, $width, $height, imagesx($image), imagesy($image));

        return $new_image;

    }

    /**
     * @brief Crop dell'immagine nella parte con maggiore entropia
     * @param resource $image resource immagine
     * @param int $width larghezza crop
     * @param int $height altezza crop
     * @param array $options Opzioni.
     * @return resource immagine
     */
    private function cropImageEntropy($image, $width, $height, $options) {
        $clone = $this->cloneImage($image);
        imagefilter($clone, IMG_FILTER_EDGEDETECT);
        imagefilter($clone, IMG_FILTER_GRAYSCALE);
        $this->blackThresholdImage($clone, 30, 30, 30);
        imagefilter($clone,  IMG_FILTER_SELECTIVE_BLUR);
        $left_x = $this->slice($image, $width, 'h');
        $top_y = $this->slice($image, $height, 'v');

        $new_image = $this->cropImage($image, $width, $height, $left_x, $top_y, $options);

        return $new_image;
    }

    /**
     * @brief Converte ogni px con rgb maggiore di una soglia a nero
     * @param resource $image image resource
     * @param int $rt red threshold
     * @param int $gt green threshold
     * @param int $bt blue threshold
     * @return void
     */
    private function blackThresholdImage($image, $rt, $gt, $bt) {
        $xdim = imagesx($image);
        $ydim = imagesy($image);
        $black = imagecolorallocate($image , 0, 0, 0);
        for($x = 1; $x <= $xdim-1; $x++) {
            for($y = 1; $y <= $ydim-1; $y++) {
                $rgb = imagecolorat($image, $x, $y);
                $r = ($rgb >> 16) & 0xFF;
                $g = ($rgb >> 8) & 0xFF;
                $b = $rgb & 0xFF;
                if($r < 30 and $g < 30 and $b < 30) {
                    imagesetpixel($image, $x, $y, $black);
                }
            }
        }
    }

    /**
     * @brief Clona una risorsa immagine
     * @param resopurce $image risorsa
     * @return resource clone
     */
    private function cloneImage($image) {
        $clone = imagecreatetruecolor(imagesx($image), imagesy($image));
        imagecopy($clone, $image, 0, 0, 0, 0, imagesx($image), imagesy($image));
        return $clone;
    }

    /**
     * @brief slice
     * @param resource $image
     * @param int $target_size dimensione finale
     * @param string $axis asse h=orizzontale, v=verticale
     * @return coordinata dalla quale tagliare
     */
    private function slice($image, $target_size, $axis) {

        $rank = array();
        $original_size = $axis == 'h' ? imagesx($image) : imagesy($image);
        $long_size = $axis == 'h' ? imagesy($image) : imagesx($image);

        if($original_size == $target_size) {
            return 0;
        }

        $number_of_slices = 30; // Arbitrary number, maybe base it on image dimensions
        $slice_size = ceil($original_size / $number_of_slices);
        // How many slices out of the ranked slices we need to get our target width.
        $required_slices = ceil($target_size / $slice_size);
        $start = 0;
        $i = 0;
        while($start <= $original_size) {
            $i++;
            $slice = $this->cloneImage($image);
            if($axis === 'h') {
                $slice = $this->cropImage($slice, $slice_size, $long_size, $start, 0);
            }
            else {
                $slice = $this->cropImage($slice, $long_size, $slice_size, 0, $start);
            }
            $rank[] = array(
                'offset'=>$start,
                'entropy' => $this->grayscaleEntropy($slice)
            );
            $start += $slice_size;
        }

        // rounding changes actual slices number
        $number_of_slices = $i;

        $max = 0;
        $max_index = 0;
        for($i = 0; $i < $number_of_slices - $required_slices; $i++) {
            $temp = 0;
            for($j = 0; $j < $required_slices; $j++) {
                $temp += $rank[$i+$j]['entropy'];
            }
            if($temp > $max) {
                $max_index = $i;
                $max = $temp;
            }
        }
        return $rank[$max_index]['offset'];
    }

    /**
     * Brief Ritorna il crop di un'immagine
     * @param resource $image Immagine da croppare
     * @param int $width larghezza immagine croppata
     * @param int $height altezza immagine croppata
     * @param int $x0 coordinata x dalla quale partire a tagliare
     * @param int $y0 coordinata y dalla quale partire a tagliare
     * @param array $options Opzioni.
     * @return resource immagine croppata
     */
    private function cropImage($image, $width, $height, $x0, $y0, $options = array()) {
        $crop = imagecreatetruecolor($width, $height);
        imagecopy($crop, $image, 0, 0, $x0, $y0, $width, $height);
        return $crop;
    }

    /**
     * @brief Calcola l'entropia di un'immagine
     * @param resource $image resource immagine
     * @return float entropia
     */
    private function grayscaleEntropy($image) {
        // The histogram consists of a list of 0-254 and the number of pixels that has that value
        $histogram = $this->getImageHistogram($image);
        return $this->getEntropy($histogram, imagesx($image) * imagesy($image));
    }

    /**
     * @brief Ricava una array di frequenze di tonalità di grigio dell'immagine
     * @param resource $image resource dell'immagine
     * @return array istogramma
     */
    private function getImageHistogram($image) {
        $histogram = array();
        $xdim = imagesx($image);
        $ydim = imagesy($image);
        for($x = 1; $x <= $xdim-1; $x++) {
            for($y = 1; $y <= $ydim-1; $y++) {
                $rgb = imagecolorat($image, $x, $y);
                if(!isset($histogram[$rgb])) {
                    $histogram[$rgb] = 1;
                }
                else {
                    $histogram[$rgb] += 1;
                }
            }
        }

        return $histogram;
    }

    /**
     * @brief Calcola l'entropia dato l'istogramma di frequenze di colori
     * @param array $histogram istogramma di frequenze di colori
     * @param int $area area dell'immagine
     * @return float entropia
     */
    private function getEntropy($histogram, $area) {
        $value = 0.0;
        $colors = count($histogram);
        foreach($histogram as $color => $frequency) {
            // calculates the percentage of pixels having this color value
            $p = $frequency / $area;
            // A common way of representing entropy in scalar
            $value = $value + $p * log($p, 2);
        }
        // $value is always 0.0 or negative, so transform into positive scalar value
        return -$value;
    }
}
