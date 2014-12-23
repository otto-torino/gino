<?php
/**
 * @file class.ResponseNotFound.php
 * @brief Contiene la definizione ed implementazione della classe Gino.Http.ResponseNotFound
 *
 * @copyright 2014 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */

namespace Gino\Http;

use \Gino\Loader;

/**
 * @brief Subclass di Gino.Http.Response per gestire risposte a seguito di errori 404
 *
 * @copyright 2014 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class ResponseNotFound extends Response {

    /**
     * @brief Costruttore
     * @param array $kwargs
     * @return istanza di Gino.Http.ResponseNotFound
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

        ob_start();
        echo $document->render();
        ob_end_flush();
    }

}
