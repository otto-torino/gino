<?php

namespace Gino\Exception;

class Exception403 extends \Exception {

    function __construct() {
        parent::__construct(_('403 Forbidden'));
    }

    public function httpResponse() {
        $response = new HttpResponseForbidden();
        return $response;
    }

}
