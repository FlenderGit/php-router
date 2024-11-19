<?php
namespace Flender\PhpRouter;
use Flender\PhpRouter\Response\Response;

#[\Attribute(\Attribute::TARGET_METHOD)]
class Route {

    private string $method;
    private string $path;
    private $callback;
    private array $params;

    public function __construct(string $method, string $path, callable $callback = null) {
        $this->method = $method;
        $this->path = $path;
        $this->callback = $callback;
        $this->params = [];
    }

    public function add_base_path(string $base_path): void {
        $this->path = $base_path . $this->path;
    }

    public function get_method(): string {
        return $this->method;
    }

    public function set_callback(callable $callback): void {
        $this->callback = $callback;
    }

    public function get_callback(): callable {
        return $this->callback;
    }

    public function add_param($param): void {
        $this->params[] = $param;
    }

    public function matches_uri(string $uri): bool {
        $path = preg_replace('#:([\w]+)#', '([^/]+)', $this->path);
        $regex = "#^$path$#";

        $uri = substr($uri, 10);

        /* echo $uri . ' ' . strlen($uri) . "<br>";
        echo $regex . ' ' . strlen($this->path) . "<hr>"; */

        if (!preg_match($regex, $uri, $this->params)) {
            return false;
        }

        array_shift($this->params);
        return true;
    }

    public function run(): void {
        $response = call_user_func_array($this->callback, $this->params);

        if ($response instanceof Response) {
            $response->send();
        } else {
            echo $response;
        }
    }

}