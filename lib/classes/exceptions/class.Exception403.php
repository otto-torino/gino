<?php

namespace Gino\Exception;

class Exception403 extends \Exception {

    public function httpResponse() {
        $response = new HttpResponseForbidden();
        return $response;
    }

}
