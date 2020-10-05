<?php
/**
 * @file class.ResponseForbidden.php
 * @brief Contiene la definizione ed implementazione della classe Gino.Http.ResponseForbidden
 */

namespace Gino\Http;

/**
 * @brief Subclass di Gino.Http.Response per gestire risposte a seguito di errori 403
 */
class ResponseForbidden extends Response {

    /**
     * @brief Costruttore
     * @param array $kwargs
     * @return void
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

        $document = \Gino\Loader::load('Document', array(\Gino\App\Sysfunc\sysfunc::page403()));

        ob_start();
        echo $document->render();
        ob_end_flush();
    }
}
