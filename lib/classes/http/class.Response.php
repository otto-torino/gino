<?php
/**
 * @file class.Response.php
 * @brief Contiene la definizione ed implementazione della classe Gino.Http.Response
 *
 * @copyright 2014 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */

namespace Gino\Http;

use \Gino\Http\Request;
use \Gino\Loader;
use \Gino\Logger;
use \Gino\OutputCache;
use \Gino\Document;

/**
 * @brief Wrapper di una risposta HTTP
 *
 * Tutti i metodi dei @ref Gino.Controller eseguiti da @ref Gino.Router in risposta ad un url,
 * ritornano un oggetto Gino.Http.Response o una sua sottoclasse. Questo oggetto si preoccupa di
 * settare gli header e di inviare il contenuto della risposta HTTP
 *
 * @copyright 2014 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class Response {

    protected $_request,
              $_content,
              $_status_code,
              $_status_text,
              $_content_type,
              $_encoding,
              $_headers;

    /**
     * @brief Costruttore
     * @param string $content contenuto della risposta
     * @param array $kwargs array associativo di argomenti
     * @return istanza di Gino.Http.Response
     */
    function __construct($content, array $kwargs = array()) {

        $this->_request = Request::instance();

        $this->_content = $content;
        $this->_status_code = 200;
        $this->_status_text = 'OK';
        $this->_version = 'HTTP/1.0' != $this->_request->META['SERVER_PROTOCOL'] ? '1.1' : '1.0';
        $this->_content_type = 'text/html';
        $this->_encoding = 'utf-8';
        $this->_headers = array();
    }

    /**
     * @brief Invia la risposta HTTP
     * @description Metodo chiamato quando si usa l'oggetto come funzione
     * @return void
     */
    function __invoke() {
        $this->send();
    }

    /**
     * @bief Setter del contenuto
     * @param string $content contenuto della risposta http
     * @return void
     */
    public function setContent($content) {
        $this->_content = $content;
    }
    
	/**
     * @bief Getter del contenuto
     * @param string $content contenuto della risposta http
     * @return void
     */
    public function getContent() {
        return $this->_content;
    }

    /**
     * @brief Setter dello status della risposta
     * @param int $code codice risposta
     * @param string text testo dello status
     * @return void
     */
    public function setStatus($code, $text) {
        $this->_status_code = $code;
        $this->_status_text = $text;
    }

    /**
     * @bief Setter del content type
     * @param string $content_type
     * @return void
     */
    public function setContentType($content_type) {
        $this->_content_type = $content_type;
    }

    /**
     * @bief Setter dell'encoding
     * @param string $encoding
     * @return void
     */
    public function setEncoding($encoding) {
        $this->_content = $encoding;
    }

    /**
     * @bief Setter di headers ulteriori
     * @param array headers array associativo di headers nella forma chiave=>valore
     * @return void
     */
    public function setHeaders($headers) {
        $this->_headers = $headers;
    }

    /**
     * @brief Invia la risposta HTTP
     * @return void
     */
    public function send() {

        $this->sendHeaders();
        $this->sendContent();
    }

    /**
     * @brief Invia gli header della richiesta HTTP
     * @return void
     */
    protected function sendHeaders() {
        // status
        header(sprintf('HTTP/%s %s %s', $this->_version, $this->_status_code, $this->_status_text), true, $this->_status_code);
        // content type, encoding
        header(sprintf('Content-Type: %s; charset=%s', $this->_content_type, $this->_encoding), false, $this->_status_code);

        foreach($this->_headers as $key => $value) {
            header(sprintf('%s: %s', $key, $value));
        }

    }

    /**
     * @brief Invia il corpo della richiesta HTTP
     * @return void
     */
    protected function sendContent() {

        ob_start();
        echo $this->_content;
        ob_end_flush();

    }

}
