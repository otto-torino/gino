<?php
/**
 * @file class.Headers.php
 * @brief Contiene la definizione ed implementazione della classe Gino.Middleware.Headers
 * 
 * @copyright 2018 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */

namespace Gino\Middleware;

/**
 * @brief Inietta header nelle risposte
 * 
 * @copyright 2018 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class Headers {

    /**
     * @brief Elenco degli header da aggiungere alle risposte
     * @return array
     */
    public function inject() {

        // HTTP response headers that servers send back for access control requests
        // as defined by the Cross-Origin Resource Sharing specification 
        // (https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS)
        
        return [
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Credentials' => 'true'
        ];
    }

}
