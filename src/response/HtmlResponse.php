<?php

namespace Flender\PhpRouter\Response;

class HtmlResponse extends Response {

    public function __construct($body, int $status = 200) {
        parent::__construct($body, $status, [
            "Content-Type: text/html"
        ]);
    }

}