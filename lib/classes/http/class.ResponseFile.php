<?php

namespace \Gino\Http;

class ResponseFile extends Response {

    protected $_content_disposition,
              $_filename;

    function __construct($content, $content_type, $filename, array $kwargs = array()) {
        parent::__construct($content, $kwargs);
        $this->_disposition_type = 'inline';
        $this->_filename = $filename;
        $this->setContentType($content_type);
    }

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
        header(sprintf('Content-Disposition: %s; filename=%s'), $this->_disposition_type, $this->_filename);
    }

}
