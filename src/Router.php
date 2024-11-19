<?php
namespace Flender\PhpRouter;
use Flender\PhpRouter\Error\PageNotFoundError;
use PDO;

class Router extends Controller {

    private string $base_url;

    private ?PDO $pdo;

    public static int $ENABLE_AUTOLOAD = 1;


    public function __construct(string $base_url = '', int $mode = 0, PDO $pdo = null) {
        $this->routes = [
            "GET" => [],
            "POST" => [],
            "DELETE" => [],
            "ERROR" => function($e) {
                http_response_code($e->getCode());
                echo "<h1>Error: {$e->getMessage()}</h1>";
            },
        ];
        $this->base_url = $base_url;
        $this->pdo = $pdo;

        if ($mode & self::$ENABLE_AUTOLOAD)
            throw new \Exception("Autoload not implemented yet");
    }

    public function set_error_handler(callable $callback): void {
        $this->routes["ERROR"] = $callback;
    }

    public function run(): void {

        $method = $_SERVER['REQUEST_METHOD'];
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        $route = $this->find_route($method, $path);

        if ($route) {
            try {
                $this->add_additional_params($route);
                $route->run();
            } catch (\Exception $e) {
                $this->routes["ERROR"]($e);
            }
        } else {
            $this->routes["ERROR"](new PageNotFoundError());
        }
    }

    private function find_route(string $method, string $path): ?Route {
        foreach ($this->routes[$method] as $route) {
            if ($route->matches_uri($path)) {
                return $route;
            }
        }
        return null;
    }

    private function add_additional_params(Route $route): void {

        if ($this->pdo) {
            $reflection = new \ReflectionFunction($route->get_callback());
            $params = $reflection->getParameters();
            foreach ($params as $param) {
                if ($param->getType() && $param->getType()->getName() == 'PDO') {
                    $route->add_param($this->pdo);
                }
            }
        }
    }

    public function scanController(string $class, ?string $base_path = null):void {
        foreach((new \ReflectionClass($class))->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
            $class = new $class;
            if (PHP_MAJOR_VERSION >= 8) {
                foreach($method->getAttributes(Route::class) as $attribute) {
                    $route = $attribute->newInstance();
                    if ($base_path)
                        $route->add_base_path($base_path);
                    $route->set_callback(\Closure::fromCallable([$class, $method->getName()]));
                    $this->register_route($route);
                }
            } else {
                $docblock = $method->getDocComment();
                if ($docblock) {
                    $docblock = explode("\n", $docblock);
                    foreach($docblock as $line) {
                        if (preg_match('/@route\s+(GET|POST|DELETE)\s+(.*)/', $line, $matches)) {
                            $path = trim($matches[2]);
                            $route = new Route($matches[1], $path);
                            if ($base_path)
                                $route->add_base_path($base_path);
                            $route->set_callback(\Closure::fromCallable([$class, $method->getName()]));
                            $this->register_route($route);
                        }
                    }
                }
            }

            /* foreach($method->getAttributes(Route::class) as $attribute) {
                $route = $attribute->newInstance();
                if ($base_path)
                    $route->add_base_path($base_path);
                $route->set_callback(\Closure::fromCallable([$class, $method->getName()]));
                $this->register_route($route);
            } */
        }

    }

}