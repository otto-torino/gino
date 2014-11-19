<?php

namespace Gino\Exception;

class Exception500 extends \Exception {

    public function httpResponse() {
        $response = new HttpResponseServerError();
        return $response;
    }

}
