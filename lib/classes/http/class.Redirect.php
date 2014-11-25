<?php
/**
 * @file class.Redirect.php
 * @brief Contiene la definizione ed implementazione della classe Gino.Http.Redirect
 *
 * @copyright 2014 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */

namespace Gino\Http;

/**
 * @brief Subclass di Gino.Http.Response per gestire reindirizzamenti
 *
 * @copyright 2014 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class Redirect extends Response {

    private $_url;

    /**
     * @brief Costruttore
     * @param string $url redirect url
     * @param array $kwargs
     * @return istanza di Gino.Http.Redirect
     */
    function __construct($url, array $kwargs = array()) {
        $this->_url = $url;
        parent::__construct('', $kwargs);
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
