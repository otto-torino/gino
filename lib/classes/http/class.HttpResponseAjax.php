<?php
/**
 * @file class.HttpResponseAjax.php
 * @brief Contiene la definizione ed implementazione della classe HttpResponseAjax
 *
 * @copyright 2014 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */

namespace Gino;

/**
 * @brief Subclass di \Gino\HttpResponse per gestire risposte a richieste ajax
 * @description Si tratta semplicemente di un alias che definisce l'argomento wrap_in_document a FALSE della parent class
 *
 * @copyright 2014 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class HttpResponseAjax extends HttpResponse {

    /**
     * @brief Costruttore
     * @param string $content contenuto della risposta
     * @param array $kwargs
     * @return istanza di \Gino\HttpResponseAjax
     */
    function __construct($content, array $kwargs = array()) {
        $kwargs['wrap_in_document'] = FALSE;
        parent::__construct($content, $kwargs);
    }

}
