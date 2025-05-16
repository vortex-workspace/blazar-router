<?php

namespace BlazarRouter;

use BlazarRouter\RouteDriver\Exceptions\RouteNotFoundException;
use BlazarRouter\Router\Exceptions\PrefixIsEnabledButNotFound;
use Core\Contracts\RequestInterface;
use Stellar\Request;
use Stellar\Routes\AbstractRoute;
use Stellar\Routes\AbstractRouteMatcher;
use Stellar\Settings\Exceptions\InvalidSettingException;

class RouteMatch extends AbstractRouteMatcher
{
    private AbstractRoute $route;

    public function __construct(private Request $request)
    {
    }

    /**
     * @return Route|null
     * @throws InvalidSettingException
     * @throws PrefixIsEnabledButNotFound
     * @throws RouteNotFoundException
     */
    public function discover(): ?Route
    {
        RouteDriver::discover($this->request);

        return RouteDriver::getRoute();
    }

    /**
     * @return Route|null
     * @throws InvalidSettingException
     * @throws PrefixIsEnabledButNotFound
     * @throws RouteNotFoundException
     */
    public function getMatchRoute(): ?Route
    {
        if (!isset($this->route)) {
            $this->route = $this->discover();
        }

        return $this->route;
    }
}