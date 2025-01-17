<?php

namespace BlazarRouter;

use BlazarRouter\Route\Exceptions\RouteNameAlreadyInUse;
use BlazarRouter\Router\Exceptions\FailedOnTryAddRoute;
use BlazarRouter\Router\Exceptions\PrefixIsEnabledButNotFound;
use Stellar\Setting;
use Stellar\Settings\Enum\SettingKey;
use Stellar\Settings\Exceptions\InvalidSettingException;

class RouterService extends AbstractRouterService
{
    public static function finder(): string
    {
        return RouteFinder::class;
    }

    public static function matcher(): string
    {
        return RouteMatch::class;
    }

    private array $routes = [];
    private array $prefixed_routes;
    private array $screening_routes = [];
    private static self $instance;
    private array $route_names = [];
    private bool $enable_entrance = true;
    private ?Route $fallback = null;

    public static function getInstance(): RouterService
    {
        if (!isset(self::$instance)) {
            self::$instance = new static();
        }

        return self::$instance;
    }

    /**
     * @param Route $route
     * @return void
     * @throws FailedOnTryAddRoute
     */
    public static function addFallbackRoute(Route &$route): void
    {
        self::addRoute($route);
        self::getInstance()->fallback = $route;
    }

    /**
     * @param Route $route
     * @return void
     * @throws FailedOnTryAddRoute
     */
    public static function addRoute(Route &$route): void
    {
        $router = self::getInstance();

        if ($router->enable_entrance === false) {
            throw new FailedOnTryAddRoute($route->getRoute());
        }

        $route->setOriginGroup($route->getOriginGroup());

        $router->screening_routes[$route->getOriginGroup()][$route->getHttpMethod()][$route->getRoute()] = &$route;
    }

    public static function flushRoutes(): void
    {
        $router = self::getInstance();
        $router->routes = array_merge_recursive($router->routes, $router->screening_routes);
        $router->screening_routes = [];
    }

    public static function setClosureRoutesPrefix(string $prefix, callable $routes): void
    {
        RouterService::flushRoutes();
        $routes();
        self::callMethodInScreeningRoutes('prefix', [$prefix]);
        RouterService::flushRoutes();
    }

    public static function setClosureRoutesController(string $controller, callable $routes): void
    {
        RouterService::flushRoutes();
        $routes();
        self::callMethodInScreeningRoutes('controller', [$controller]);
        RouterService::flushRoutes();
    }

    public static function setClosureRoutesMiddleware(string|array $middleware, callable $routes): void
    {
        RouterService::flushRoutes();
        $routes();
        self::callMethodInScreeningRoutes('middleware', [$middleware]);
        RouterService::flushRoutes();
    }

    public static function setClosureRoutesGroup(
        callable     $routes,
        string|array $middleware = [],
        ?string      $controller = null,
        ?string      $prefix = null
    ): void
    {
        RouterService::flushRoutes();
        $routes();

        if (!empty($middleware)) {
            self::callMethodInScreeningRoutes('middleware', [$middleware]);
        }

        if (!empty($controller)) {
            self::callMethodInScreeningRoutes('controller', [$controller]);
        }

        if (!empty($prefix)) {
            self::callMethodInScreeningRoutes('prefix', [$prefix]);
        }

        RouterService::flushRoutes();
    }

    private static function callMethodInScreeningRoutes(string $method, array $arguments = []): void
    {
        foreach (self::getInstance()->screening_routes as $routes) {
            foreach ($routes as $route) {
                $route->$method(...$arguments);
            }
        }
    }

    /**
     * @param Route $route
     * @return void
     * @throws RouteNameAlreadyInUse
     */
    private function checkRouteName(Route $route): void
    {
        if ($route->getName() !== null) {
            if (in_array($route->getName(), self::getInstance()->route_names)) {
                throw new RouteNameAlreadyInUse($route->getName());
            }

            self::getInstance()->route_names[] = $route->getName();
        }
    }

    /**
     * @return RouterService
     * @throws RouteNameAlreadyInUse
     */
    public function loadNames(): RouterService
    {
        self::flushRoutes();

        foreach (self::getInstance()->routes as $route_type) {
            foreach ($route_type as $routes) {
                foreach ($routes as $route) {
                    self::getInstance()->checkRouteName($route);
                }
            }
        }

        return self::getInstance();
    }

    /**
     * @return $this
     * @throws InvalidSettingException
     * @throws PrefixIsEnabledButNotFound
     */
    public function updateRoutesWithPrefix(): static
    {
        $custom_route_files = Setting::get(SettingKey::RouteFiles->value);

        foreach ($this->routes as $methods_routes) {
            foreach ($methods_routes as $method => $method_routes) {
                /** @var Route $route */
                foreach ($method_routes as $route) {
                    if (isset($custom_route_files[$route->getOriginGroup()]['use_prefix'])) {
                        if (!isset($custom_route_files[$route->getOriginGroup()]['prefix'])) {
                            throw new PrefixIsEnabledButNotFound($route->getOriginGroup());
                        }

                        $this->addPrefixedRoute(
                            $method,
                            $route,
                            $custom_route_files[$route->getOriginGroup()]['prefix']
                        );

                        continue;
                    }

                    $this->addPrefixedRoute($method, $route);
                }
            }
        }

        return $this;
    }

    public function disableEntrance(): static
    {
        $this->enable_entrance = false;

        return $this;
    }

    public function enableEntrance(): static
    {
        $this->enable_entrance = true;

        return $this;
    }

    public function getGroupedRoutes(): array
    {
        return $this->routes;
    }

    /**
     * @return array
     * @throws InvalidSettingException
     * @throws PrefixIsEnabledButNotFound
     */
    public function getRoutes(): array
    {
        if (isset($this->prefixed_routes)) {
            return $this->prefixed_routes;
        }

        return $this->updateRoutesWithPrefix()->prefixed_routes;
    }

    public function getRouteNames(): array
    {
        return $this->route_names;
    }

    private function addPrefixedRoute(string $method, Route $route, ?string $group_prefix = null): void
    {
        $this->prefixed_routes[$method][$group_prefix ?
            "$group_prefix/{$route->getPrefixedRoute()}" :
            $route->getPrefixedRoute()] = $route;
    }

    public function getFallbackRoute(): ?Route
    {
        return $this->fallback;
    }
}