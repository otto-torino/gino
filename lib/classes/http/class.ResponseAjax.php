<?php
/**
 * @file class.ResponseAjax.php
 * @brief Contiene la definizione ed implementazione della classe \Gino\Http\ResponseAjax
 *
 * @copyright 2014 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */

namespace Gino\Http;

/**
 * @brief Subclass di \Gino\Http\Response per gestire risposte a richieste ajax
 * @description Si tratta semplicemente di un alias che definisce l'argomento wrap_in_document a FALSE della parent class
 *
 * @copyright 2014 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class ResponseAjax extends Response {

    /**
     * @brief Costruttore
     * @param string $content contenuto della risposta
     * @param array $kwargs
     * @return istanza di \Gino\Http\ResponseAjax
     */
    function __construct($content, array $kwargs = array()) {
        $kwargs['wrap_in_document'] = FALSE;
        parent::__construct($content, $kwargs);
    }

}
