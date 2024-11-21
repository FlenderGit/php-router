<?php
use Flender\PhpRouter\Azure\Credentials;
use Flender\PhpRouter\Azure\OpenAI;
use Flender\PhpRouter\Response\HtmlResponse;
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

$credentials = Credentials::from_env();
$openai = new OpenAI($credentials);

$router->get('/', function() {
    return "Hello World!";
});

$router->get('/thread/:id', function(string $id) use ($openai) {
    return new JsonResponse($openai->get_thread($id));
});

$router->post('/thread/:id/message', function(int $id) use ($openai) {
    is_authenticaded();
    $thread = $openai->get_thread($id);
    $thread->send_message($_POST['message']);
    $thread->run();
    return new JsonResponse($thread);
});

$router->run();

