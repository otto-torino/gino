<?php
/**
 * @file class.ResponseServerError.php
 * @brief Contiene la definizione ed implementazione della classe Gino.Http.ResponseServerError
 *
 * @copyright 2014-2018 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */

namespace Gino\Http;

use \Gino\Loader;

/**
 * @brief Subclass di Gino.Http.Response per gestire risposte a seguito di errori interni (code 500)
 *
 * @copyright 2014-2018 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class ResponseServerError extends Response {

    private $_add_content;
    
    /**
     * @brief Costruttore
     * @param array $kwargs
     * @return void
     */
    function __construct(array $kwargs = array()) {

        parent::__construct('', $kwargs);

        $this->setStatus(500, 'Internal Server Error');
        $this->_add_content = null;
    }
    
    /**
     * @brief Imposta contenuti aggiuntivi
     * @param string $string
     */
    public function setAddContent($string) {
        
        $this->_add_content = $string;
    }

    /**
     * @brief Corpo della risposta HTTP
     * @description Mostra la pagina 500 di gino
     * @return void
     */
    protected function sendContent() {

        // per evitare errori che causano un ciclo infinito
        try {
            $content = \Gino\App\Sysfunc\sysfunc::page500();
            if($this->_add_content) {
                $content .= $this->_add_content;
            }
            $document = Loader::load('Document', array($content));
            
            ob_start();
            echo $document->render();
            ob_end_flush();
        }
        catch(\Exception $e) {
            ob_start();
            echo \Gino\App\Sysfunc\sysfunc::page500();
            if($this->_add_content) {
                echo $this->_add_content;
            }
            ob_end_flush();
        }
    }

}
