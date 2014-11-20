<?php
/**
 * @file class.ResponseFile.php
 * @brief Contiene la definizione ed implementazione della classe \Gino\Http\ResponseFile
 *
 * @copyright 2014 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */

namespace Gino\Http;

/**
 * @brief Subclass di \Gino\Http\Response per gestire stream di file
 *
 * @copyright 2014 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
class ResponseFile extends Response {

    protected $_content_disposition,
              $_filename;

    /**
     * @brief Costruttore
     * @param string $content contenuto del file
     * @param string $content_type
     * @param string $filename
     * @param array $kwargs array associativo
     *              - disposition_type: disposition type header
     * @return istanza di \Gino\Http\ResponseAjax
     */
    function __construct($content, $content_type, $filename, array $kwargs = array()) {

        parent::__construct($content, $kwargs);

        $this->_disposition_type = isset($kwargs['disposition_type']) ? $kwargs['disposition_type'] : 'inline';
        $this->_filename = $filename;
        $this->setContentType($content_type);
    }

    /**
     * @bief Setter del disposition type
     * @param string $disposition_type
     * @return void
     */
    public function setDispositionType($disposition_type) {
        $this->_disposition_type = $disposition_type;
    }

    /**
     * @brief Invia gli header della richiesta HTTP
     * @return void
     */
    protected function sendHeaders() {

        parent::sendHeaders();
        // content disposition
        header(sprintf('Content-Disposition: %s; filename=%s', $this->_disposition_type, $this->_filename));
    }

}
