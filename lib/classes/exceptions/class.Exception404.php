<?php

namespace Gino\Exception;

class Exception404 extends \Exception {

    function __construct() {
        parent::__construct(_('404 Page Not Found'));
    }

    public function httpResponse() {
        $response = new HttpResponseNotFound();
        return $response;
    }

}
