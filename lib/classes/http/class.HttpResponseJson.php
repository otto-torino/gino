<?php
/**
 * @file class.HttpResponseJson.php
 * @brief Contiene la definizione ed implementazione della classe HttpResponseJson
 *
 * @copyright 2014 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */

namespace Gino;

/**
 * @brief Subclass di \Gino\HttpResponse per gestire risposte in formato json
 *
 * @copyright 2014 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class HttpResponseJson extends HttpResponse {

    /**
     * @brief Costruttore
     * @param mixed $content contenuto della risposta. Se diverso da stringa viene codificato in json
     * @param array $kwargs
     * @return istanza di \Gino\HttpResponseJson
     */
    function __construct($content, array $kwargs = array()) {

        if(!is_string($content)) {
            $content = json_encode($content);
        }

        $kwargs['wrap_in_document'] = FALSE;
        parent::__construct($content, $kwargs);

        $this->setContentType('application/json');

    }

}
