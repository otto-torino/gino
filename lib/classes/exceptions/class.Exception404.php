<?php

namespace Gino\Exception;

class Exception404 extends \Exception {

    public function httpResponse() {
        $response = new HttpResponseNotFound();
        return $response;
    }

}
