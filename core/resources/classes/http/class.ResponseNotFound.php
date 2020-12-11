<?php
/**
 * @file class.ResponseNotFound.php
 * @brief Contiene la definizione ed implementazione della classe Gino.Http.ResponseNotFound
 */

namespace Gino\Http;

use \Gino\Loader;

/**
 * @brief Subclass di Gino.Http.Response per gestire risposte a seguito di errori 404
 */
class ResponseNotFound extends Response {

    /**
     * @brief Costruttore
     * @param array $kwargs
     * @return void
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
