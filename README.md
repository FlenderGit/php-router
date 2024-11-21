# 💡About
php-router is a minimalist and lightweight routing library for PHP designed to simplify web application development. It offers a streamlined approach to handling routes without the overhead of large frameworks, making it an excellent choice for lightweight projects or custom solutions.

# 🖥️Installation

# 📘Usage

A exemple cna be find in `exemple\index.php`

```php
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

// And add routes from a controller class
$router->scanController(ApiController::class, 'api');

$router->run();
```