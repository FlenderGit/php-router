<?php

namespace Flender\PhpRouter\Response;

class RedirectionResponse extends Response
{

    public function __construct(string $location, int $status = 302)
    {
        parent::__construct("", $status, [
            "Location: $location"
        ]);
    }

}
