<?php
/**
 * @file class.MobileDetect.php
 * @brief Contiene la definizione ed implementazione della classe Gino.MobileDetect
 */
namespace Gino;

/**
 * @brief Verifica se una richiesta HTTP è stata effettuata da un dispositivo mobile
 *
 * @license    http://www.opensource.org/licenses/mit-license.php The MIT License
 * @version    SVN: $Id: Mobile_Detect.php 4 2011-05-26 08:04:20Z vic.stanciu@gmail.com $
 */
class MobileDetect {

    protected $accept;
    protected $userAgent;

    protected $isMobile     = FALSE;
    protected $isAndroid    = null;
    protected $isBlackberry = null;
    protected $isIphone     = null;
    protected $isOpera      = null;
    protected $isPalm       = null;
    protected $isWindows    = null;
    protected $isGeneric    = null;

    protected $devices = array(
        "android"       => "android",
        "blackberry"    => "blackberry",
        "iphone"        => "(iphone|ipod)",
        "opera"         => "opera mini",
        "palm"          => "(avantgo|blazer|elaine|hiptop|palm|plucker|xiino)",
        "windows"       => "windows ce; (iemobile|ppc|smartphone)",
        "generic"       => "(kindle|mobile|mmp|midp|o2|pda|pocket|psp|symbian|smartphone|treo|up.browser|up.link|vodafone|wap)"
    );

    /**
     * @brief Verifica se la richiesta proviene da un dispositivo mobile e sovrascrive la proprietà @a isMobile
     * @return void
     */
    public function __construct() {
        $this->userAgent = $_SERVER['HTTP_USER_AGENT'];
        $this->accept    = isset($_SERVER['HTTP_ACCEPT']) ? $_SERVER['HTTP_ACCEPT'] : '';

        if (isset($_SERVER['HTTP_X_WAP_PROFILE'])|| isset($_SERVER['HTTP_PROFILE'])) {
            $this->isMobile = TRUE;
        } elseif (strpos($this->accept,'text/vnd.wap.wml') > 0 || strpos($this->accept,'application/vnd.wap.xhtml+xml') > 0) {
            $this->isMobile = TRUE;
        } else {
            foreach ($this->devices as $device => $regexp) {
                if ($this->isDevice($device)) {
                    $this->isMobile = TRUE;
                }
            }
        }
    }

    /**
     * @brief Overloads isAndroid() | isBlackberry() | isOpera() | isPalm() | isWindows() | isGeneric() through isDevice()
     *
     * @param string $name
     * @param array $arguments
     * @return bool
     */
    public function __call($name, $arguments) {
        $device = substr($name, 2);
        if ($name == "is" . ucfirst($device)) {
            return $this->isDevice($device);
        } else {
            throw new Exception("Metodo '$name' non definito");
        }
    }

    /**
     * @brief Verifica se il dispositivo connesso è mobile
     * @return TRUE se il dispositivo è mobile, FALSE altrimenti
     */
    public function isMobile() {
        return $this->isMobile;
    }

    /**
     * @brief Verifica se il dispositivo è un particolare device
     * @param string $device nome device (android, opera, blackberry, ...)
     * @return risultato verifica, bool
     */
    protected function isDevice($device) {
        $var    = "is" . ucfirst($device);
        $return = $this->$var === null ? (bool) preg_match("/" . $this->devices[$device] . "/i", $this->userAgent) : $this->$var;

        if ($device != 'generic' && $return == TRUE) {
            $this->isGeneric = FALSE;
        }

        return $return;
    }
}
