<?php

namespace Gino;

class Exception403 extends \Exception {

    public function httpResponse() {
        $response = new HttpResponseForbidden();
        return $response;
    }

}
