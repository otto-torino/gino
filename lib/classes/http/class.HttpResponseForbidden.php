<?php
/**
 * @file class.HttpResponseForbidden.php
 * @brief Contiene la definizione ed implementazione della classe HttpResponseForbidden
 *
 * @copyright 2014 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */

namespace Gino;

/**
 * @brief Subclass di \Gino\HttpResponse per gestire risposte a seguito di errori 403
 *
 * @copyright 2014 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class HttpResponseForbidden extends HttpResponse {

    /**
     * @brief Costruttore
     * @param array $kwargs
     * @return istanza di \Gino\HttpResponseForbidden
     */
    function __construct(array $kwargs = array()) {

        parent::__construct('', $kwargs);

        $this->setStatus(403, 'Forbidden');

    }

    /**
     * @brief Corpo della risposta HTTP
     * @description Mostra la pagina 403 di gino
     * @return void
     */
    protected function sendContent() {

        $document = Loader::load('Document', array(\Gino\App\Sysfunc\sysfunc::page403()));
        $buffer = $document->render();

        ob_start();
        echo $buffer;
        ob_end_flush();
    }

}
