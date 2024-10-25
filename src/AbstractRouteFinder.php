<?php

namespace BlazarRouter;

use Stellar\Services\AbstractService;
use Stellar\Services\AbstractService\Traits\SingletonService;

abstract class AbstractRouteFinder extends AbstractService
{
    use SingletonService;

    /**
     * @var AbstractRoute[]
     */
    protected array $routes;

    abstract public function findRoutes(): void;

    final public function addRoute(AbstractRoute $route): void
    {
        $this->routes[] = $route;
    }
}