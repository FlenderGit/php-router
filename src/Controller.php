<?php

namespace Flender\PhpRouter;

use ReflectionMethod;

class Controller {

    protected array $routes;

    private static string $GET = 'GET';
    private static string $POST = 'POST';

    protected function register_route(Route $route):void {
        $this->routes[$route->get_method()][] = $route;
    }

    public function get(string $path, callable $callback):void {
        $this->register_route(new Route(self::$GET, $path, $callback));
    }

    public function post(string $path, callable $callback):void {
        $this->register_route(new Route(self::$POST, $path, $callback));
    }

    public function add_controller(string $base_path, Controller $controller): void {
        foreach ($controller->routes as $method => $routes) {

            $routes = array_map(function($route) use ($base_path) {
                $route->add_base_path($base_path);
                return $route;
            }, $routes);

            if (!isset($this->routes[$method]))
                $this->routes[$method] = $routes;
            else
                $this->routes[$method] = array_merge($this->routes[$method], $routes);

        }
    }

}