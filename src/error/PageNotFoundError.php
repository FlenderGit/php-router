<?php
namespace Flender\PhpRouter\Error;

class PageNotFoundError extends \Exception {
    public function __construct() {
        parent::__construct("Page not found", 404);
    }
}