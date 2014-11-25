<?php
/**
 * @file class.Captcha.php
 * @brief Contiene la definizione ed implementazione della classe Gino.Captcha
 *
 * @copyright 2013-2014 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
namespace Gino;

/**
 * @brief Classe per la generazione di immagini captcha
 *
 * imagettftext() richiede le GD library e le FreeType library.
 *
 * @copyright 2013-2014 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class Captcha {

    /**
     * @brief name of the captcha field
     */
    private $_name;

    /**
     * @brief captcha image width
     */
    private $_width;

    /**
     * @brief captcha image height
     */
    private $_height;

    /**
     * @brief absolute path of the font ttf file
     */
    private $_font_file;

    /**
     * @brief letters allowed for the captcha
     */
    private $_letters;

    /**
     * @brief numbers allowed for the captcha
     */
    private $_numbers;

    /**
     * @brief whether to allow number in captcha or not
     */
    private $_allow_numbers;

    /**
     * @brief Costruttore
     * 
     * @param string $name nome del campo captcha
     * @param array $opts
     *   array associativo di opzioni
     *   - @b allow_numbers (boolean): permettere o meno caratteri di tipo numero, default FALSE
     * @return istanza di Gino.Captcha
     */
    function __construct($name, $opts=null) {

        $this->_name = $name;
        $this->_width = 200;
        $this->_height = 40;

        $this->_font_file = FONTS_DIR.OS.'initial.ttf';

        $this->_letters = array("a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k", "l", "m", "n", "o", "p", "q", "r", "s", "t", "u", "v", "w", "x", "y", "z");
        $this->_numbers = array("0", "1", "2", "3", "4", "5", "6", "7", "8", "9");

        $this->_allow_numbers = gOpt('allow_numbers', $opts, false);
    }

    /**
     * @brief Verifica se l'immagine captcha può essere generata
     * @return risultato verifica, bool
     */
    private function checkRequirements(){

        if(function_exists('gd_info'))
        {
            $array = gd_info();
            if(isset($array['FreeType Support']) && $array['FreeType Support'] == 'Enabled')
                return TRUE;
        }
        return FALSE;
    }

    /**
     * @brief Renderizza l'immagine e l'input per l'inserimento del codice
     * 
     * @see self::checkRequirements()
     * @param mixed $opts 
     *   array associativo di opzioni
     *   - @b bkg_color (string): hex code del colore del background, default #00ff00
     *   - @b color (string): hex code del colore dei caratteri, default #000000
     * @return immagine captcha e form input
     */
    public function render($opts=null) {

        $bkg_color = gOpt("bkg_color", $opts, "#00ff00");
        $color = gOpt("color", $opts, "#000000");

        if(!$this->checkRequirements())
            return _("Il codice captcha non può essere generato - verifica i requisiti necessari");

        $image = ImageCreatetruecolor($this->_width, $this->_height);

        // background
        $bcs = $this->hex2RGB($bkg_color);
        $bkg_c = ImageColorAllocate($image, $bcs['red'], $bcs['green'], $bcs['blue']);
        imagefill($image, 0, 0, $bkg_c);

        // text
        $tcs = $this->hex2RGB($color);
        $text_c = ImageColorAllocate($image, $tcs['red'], $tcs['green'], $tcs['blue']);
        list($s1, $s2) = $this->generateStrings();
        imagettftext($image, 22, 2, 5, $this->_height-5, $text_c, $this->_font_file, $s1);
        imagettftext($image, 22, -5, 110, 25, $text_c, $this->_font_file, $s2);

        // ellipses
        $i = 0;
        while($i<$this->_width-5) {
            $ell_color = ImageColorAllocate($image, rand(0,255), rand(0,255), rand(0,255));
            imagefilledellipse($image , $i+5 , rand(5, $this->_height-5) , rand(0, 8) , rand(0, 8), $ell_color);
            $i = $i+20;
        }

        // lines
        $i = 0;
        while($i<$this->_width-3) {
            $line_color = ImageColorAllocate($image, rand(0,255), rand(0,255), rand(0,255));
            imagesetthickness($image, rand(0,2));
            imageline($image, $i , rand(0, $this->_height) , rand($i, $i+20) , rand(0, $this->_height) , $line_color);
            $i = $i+10;
        }

        ob_start();
        imagejpeg($image);
        imagedestroy($image);
        $img = ob_get_contents();
        ob_end_clean();

        $_SESSION['captcha_code'] = $s1.$s2;

        $buffer = "<img src=\"data:image/jpeg;base64,".base64_encode($img)."\" /><br />";
        $buffer .= "<input name=\"$this->_name\" type=\"text\" size=\"10\" maxlength=\"20\" required=\"required\" />";

        return $buffer;
    }

    /**
     * @brief Genera due stringhe alfanumeriche random
     * @return array(stringa, stringa)
     */
    private function generateStrings() {

        $s1 = $s2 = '';

        $coin = rand(0,10) < 5 ? true : false;

        $ls1 = $coin ? 5 : 4;
        $ls2 = $coin ? 4 : 5;

        for($i=0; $i<$ls1; $i++) { 
            $coin = $this->_allow_numbers ? rand(0, 10) : 0;
            $s1 .= $coin < 5 ? $this->_letters[array_rand($this->_letters)] : $this->_numbers[array_rand($this->_numbers)];
        }

        for($i=0; $i<$ls2; $i++) { 
            $coin = $this->_allow_numbers ? rand(0, 10) : 0;
            $s2 .= $coin < 5 ? $this->_letters[array_rand($this->_letters)] : $this->_numbers[array_rand($this->_numbers)];
        }

        return array($s1, $s2);
    }

    /**
     * @brief Converte un colore da formato hex a formato array rgb
     * 
     * @param string $hexStr formato hex
     * @return array associativo contenente i valori rgb (red, blue, green)
     */
    private function hex2RGB($hexStr) {

        $hexStr = preg_replace("/[^0-9A-Fa-f]/", '', $hexStr); // Gets a proper hex string
        $rgbArray = array();

        if (strlen($hexStr) == 6) { //If a proper hex code, convert using bitwise operation. No overhead... faster
            $colorVal = hexdec($hexStr);
            $rgbArray['red'] = 0xFF & ($colorVal >> 0x10);
            $rgbArray['green'] = 0xFF & ($colorVal >> 0x8);
            $rgbArray['blue'] = 0xFF & $colorVal;
        }
        elseif (strlen($hexStr) == 3) { //if shorthand notation, need some string manipulations
            $rgbArray['red'] = hexdec(str_repeat(substr($hexStr, 0, 1), 2));
            $rgbArray['green'] = hexdec(str_repeat(substr($hexStr, 1, 1), 2));
            $rgbArray['blue'] = hexdec(str_repeat(substr($hexStr, 2, 1), 2));
        }
        else {
            return false; //Invalid hex color code
        }

        return $rgbArray;
    }

    /**
     * @brief Controlla che il codice inserito dall'utente sia corretto
     * @return risultato controllo, bool
     */
    public function check() {

        $request = \Gino\Http\Request::instance();
        $string = preg_replace("#[ \s]#", "", $request->POST[$this->_name]);

        if(!$string) return FALSE;

        $result = $string === $_SESSION['captcha_code'] ? TRUE : FALSE;

        unset($_SESSION['captcha_code']);

        return $result;
    }
}
