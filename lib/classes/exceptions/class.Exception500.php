<?php
/**
 * @file class.Exception500.php
 * @brief Contiene la definizione ed implementazione della classe Gino.Exception.Exception500
 *
 * @copyright 2014 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */

namespace Gino\Exception;

/**
 * @brief Classe per la gestione di eccezioni 500
 *
 * Definisce un metodo che fornisce una Gino.Http.Response con il contenuto 500
 *
 * @see Gino.Http.ResponseServerError
 * @copyright 2014 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class Exception500 extends \Exception {

    /**
     * @brief Costruttore
     * @return istanza Gino.Exception.Exception500
     */
    function __construct() {
        parent::__construct(_('500 Server Error'));
    }

    /**
     * @brief Response con contenuto 500
     * @return Gino.Http.ResponseServerError
     */
    public function httpResponse() {
        $response = new \Gino\Http\ResponseServerError();
        return $response;
    }

}
