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

$router->get('/thread/:id', function(string $id) use ($openai) {
    return new JsonResponse($openai->get_thread($id));
});

$router->post('/thread/:id/message', function(int $id) use ($openai) {
    $thread = $openai->get_thread($id);
    $thread->send_message($_POST['message']);
    $thread->run();
    return new JsonResponse($thread);
});


// THREAD: <Get id> / <Get> / <Delete>

// API Svelte -> PHP
// prefix: /api
// GET /thread              --> Get all
// GET /thread/:id          --> Get one
// POST /thread             --> Create
// { type }
// POST /thread/:id/message --> Add message to thread
// POST /thread/:id/file    --> Add file to thread
// PUT /thread/:id/run      --> Run
// DELETE /thread/:id       --> Delete

// API PHP -> Azure

// Create Azure OpenIA
// $openai = new OpenIA(credentials)

// Create Thread
// $thread = $openai->create_thread($type)

// Get Thread -- not info, just instance
// $thread = $openai->get_thread($id)

// Get Thread -- with info
// $thread = $openai->get_thread_data($id)

// Get Threads
// $threads = $openai->get_threads()

// Add Message
// $thread->add_message($message)

// Add File
// $thread->add_file($file)

// Run
// $thread->run($stream)

// Delete
// $thread->delete()

$router->get('/', function() {
    return "Hello World!";
});

$router->get('/hello/:name', function(string $name) {
    return new HtmlResponse("<strong>Hello, $name!</strong>");
});

$router->get('/:id', function(int $id) {
    is_authenticaded();
    return new JsonResponse(["id" => $id]);
});

$router->run();

