<?php
/**
 * @file class.Exception403.php
 * @brief Contiene la definizione ed implementazione della classe Gino.Exception.Exception403
 *
 * @copyright 2014 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */

namespace Gino\Exception;

/**
 * @brief Classe per la gestione di eccezioni 403
 *
 * Definisce un metodo che fornisce una Gino.Http.Response con il contenuto 403
 *
 * @see Gino.Http.ResponseForbidden
 * @copyright 2014 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class Exception403 extends \Exception {

    private $_redirect;

    /**
     * @brief Costruttore
     * @param array $kwargs array associativo
     *                      - redirect (bool), default FALSE. Se TRUE il metodo Gino.Exception.Exception403::httpResponse()
     *                                                        ritorna un Gino.Http.Redirect alla pagina di login
     * @return istanza Gino.Exception.Exception403
     */
    function __construct(array $kwargs = array()) {
        parent::__construct(_('403 Forbidden'));
        $this->_redirect = isset($kwargs['redirect']) ? $kwargs['redirect'] : FALSE;
    }

    /**
     * @brief Response con contenuto 403
     * @return Gino.Http.ResponseForbidden
     */
    public function httpResponse() {
        if($this->_redirect) {
            $registry = \Gino\Registry::instance();
            $url = $registry->router->link('auth', 'login');
            $response = new \Gino\Http\Redirect($url);
        }
        else {
            $response = new \Gino\Http\ResponseForbidden();
        }

        return $response;
    }

}
