<?php

namespace Gino\Exception;

class Exception404 extends \Exception {

    function __construct() {
        parent::__construct(_('Pagina non trovata'));
    }

    public function httpResponse() {
        $response = new HttpResponseNotFound();
        return $response;
    }

}
