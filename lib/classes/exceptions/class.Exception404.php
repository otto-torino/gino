<?php
/**
 * @file class.Exception404.php
 * @brief Contiene la definizione ed implementazione della classe Gino.Exception.Exception404
 *
 * @copyright 2014 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */

/**
 * @namespace Gino.Exception
 * @description Namespace che comprende tutte le classi di tipo Exception
 */
namespace Gino\Exception;

/**
 * @brief Classe per la gestione di eccezioni 404
 *
 * Definisce un metodo che fornisce una Gino.Http.Response con il contenuto 404
 *
 * @see Gino.Http.ResponseNotFound
 * @copyright 2014 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class Exception404 extends \Exception {

    /**
     * @brief Costruttore
     * @return istanza Gino.Exception.Exception404
     */
    function __construct() {
        parent::__construct(_('404 Page Not Found'));
    }

    /**
     * @brief Response con contenuto 404
     * @return Gino.Http.ResponseNotFound
     */
    public function httpResponse() {
        $response = new \Gino\Http\ResponseNotFound();
        return $response;
    }

}
