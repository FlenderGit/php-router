<?php
use Flender\PhpRouter\Response\RedirectionResponse;
use Flender\PhpRouter\Router;
use Flender\PhpRouter\Response\JsonResponse;

require_once __DIR__ . '/../vendor/autoload.php';

// ----------------------------------------

function load_env() {
    $env = parse_ini_file(__DIR__ . '/.env');
    foreach ($env as $key => $value) {
        putenv("$key=$value");
    }
}

function is_authenticaded() {
    if (!isset($_SERVER['HTTP_AUTHORIZATION'])) {
        throw new Exception("Unauthorized", 401);
    }
}


// ----------------------------------------

load_env();
$router = new Router();

$router->get('/', function() {
    return "Hello World!";
});

$router->get('/blog/:id', function(string $id) {
    return new JsonResponse([
        'id' => $id,
        'title' => 'My first blog post',
        'content' => 'This is the content of my first blog post'
    ]);
});

$router->post('/thread/:id/message', function(int $id) {
    is_authenticaded();
    return new RedirectionResponse("/thread/$id");
});

$router->run();

