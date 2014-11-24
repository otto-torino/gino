<?php

namespace Gino\Exception;

class Exception500 extends \Exception {

    function __construct() {
        parent::__construct(_('500 Server Error'));
    }

    public function httpResponse() {
        $response = new HttpResponseServerError();
        return $response;
    }

}
