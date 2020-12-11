<?php
/**
 * @file class.Redirect.php
 * @brief Contiene la definizione ed implementazione della classe Gino.Http.Redirect
 */

namespace Gino\Http;

/**
 * @brief Subclass di Gino.Http.Response per gestire reindirizzamenti
 */
class Redirect extends Response {

    private $_url;

    /**
     * @brief Costruttore
     * @param string $url redirect url
     * @param array $kwargs
     * @return void
     */
    function __construct($url, array $kwargs = array()) {
        $this->_url = $this->redirectUrl($url);
        parent::__construct('', $kwargs);
    }

    /**
     * @brief Redrect url
     *
     * Ricava un url adatto per il redirect.
     * Se l'url è assoluto oppure relativo a partire dalla document root lo restituisce invariato,
     * altrimenti aggiunge il path di differenza tra document root e site root
     *
     * @param string $url
     * @return string
     */
    private function redirectUrl($url) {
        if(preg_match("#^https?://#", $url) or (SITE_WWW and preg_match("#^".preg_quote(SITE_WWW)."#", $url))) {
            return $url;
        }
        else {
            return SITE_WWW . '/' .$url;
        }
    }

    /**
     * @brief Invia la risposta HTTP
     * @description Invia solamente un header per il redirect
     * @return void
     */
    public function send() {

        $this->sendHeaders();
    }

    /**
     * @brief Invia gli header della richiesta HTTP
     * @description Invia un header per il redirect
     * @return void
     */
    protected function sendHeaders() {
        header('Location: '.$this->_url);
    }

}
