<?php

namespace BlazarRouter\Route\Traits;


use BlazarRouter\RouterService;

trait Grouped
{
    /**
     * @param string $prefix
     * @param callable $routes
     * @return void
     */
    public static function groupPrefix(string $prefix, callable $routes): void
    {
        RouterService::setClosureRoutesPrefix($prefix, $routes);
    }

    public static function groupController(string $controller, callable $routes): void
    {
        RouterService::setClosureRoutesController($controller, $routes);
    }

    /**
     * @param string|array $middleware
     * @param callable $routes
     * @return void
     */
    public static function groupMiddleware(string|array $middleware, callable $routes): void
    {
        RouterService::setClosureRoutesMiddleware($middleware, $routes);
    }

    /**
     * @param callable $routes
     * @param string|array $middleware
     * @param string|null $controller
     * @param string|null $prefix
     * @return void
     */
    public static function group(
        callable     $routes,
        string|array $middleware = [],
        ?string      $controller = null,
        ?string      $prefix = null
    ): void
    {
        RouterService::setClosureRoutesGroup($routes, $middleware, $controller, $prefix);
    }
}