<?php
/**
 * @file class.HttpResponseNotFound.php
 * @brief Contiene la definizione ed implementazione della classe HttpResponseNotFound
 *
 * @copyright 2014 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */

namespace Gino;

/**
 * @brief Subclass di \Gino\HttpResponse per gestire risposte a seguito di errori 404
 *
 * @copyright 2014 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class HttpResponseNotFound extends HttpResponse {

    /**
     * @brief Costruttore
     * @param array $kwargs
     * @return istanza di \Gino\HttpResponseNotFound
     */
    function __construct(array $kwargs = array()) {

        parent::__construct('', $kwargs);

        $this->setStatus(404, 'Not Found');

    }

    /**
     * @brief Corpo della risposta HTTP
     * @description Mostra la pagina 404 di gino
     * @return void
     */
    protected function sendContent() {

        $document = Loader::load('Document', array(\Gino\App\Sysfunc\sysfunc::page404()));
        $buffer = $document->render();

        ob_start();
        echo $buffer;
        ob_end_flush();
    }

}
