<?php
/**
 * @file class.ResponseJson.php
 * @brief Contiene la definizione ed implementazione della classe \Gino\Http\ResponseJson
 *
 * @copyright 2014 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */

namespace Gino\Http;

/**
 * @brief Subclass di \Gino\Http\Response per gestire risposte in formato json
 *
 * @copyright 2014 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class ResponseJson extends Response {

    /**
     * @brief Costruttore
     * @param mixed $content contenuto della risposta. Se diverso da stringa viene codificato in json
     * @param array $kwargs
     * @return istanza di \Gino\Http\ResponseJson
     */
    function __construct($content, array $kwargs = array()) {

        if(!is_string($content)) {
            $content = json_encode($content);
        }

        parent::__construct($content, $kwargs);
        $this->setContentType('application/json');

    }

}
