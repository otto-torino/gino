<?php
/**
 * @file class.HttpResponseView.php
 * @brief Contiene la definizione ed implementazione della classe HttpResponseView
 *
 * @copyright 2014 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */

namespace Gino;

/**
 * @brief Subclass di \Gino\HttpResponse per gestire risposte a partire da una \Gino\View ed un context
 *
 * @copyright 2014 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class HttpResponseView extends HttpResponse {

    private $_view,
            $_context;

    /**
     * @brief Costruttore
     * @param \Gino\View $view
     * @param array $context context da passare al template della view
     * @return istanza di \Gino\HttpResponseView
     */
    function __construct(\Gino\View $view, array $context = array()) {

        parent::__construct('');

        $this->_view = $view;
        $this->_context = $context;
    }

    /**
     * @brief Corpo della risposta HTTP
     * @description Chiama il render della vista passando il context
     * @return void
     */
    protected function sendContent() {

        $document = Loader::load('Document', array($this->_view->render($this->_context)));
        $buffer = $document->render();

        ob_start();
        echo $buffer;
        ob_end_flush();
    }

}
