<?php

namespace BlazarRouter;

use BlazarRouter\RouteDriver\Exceptions\RouteNotFoundException;
use BlazarRouter\Router\Exceptions\PrefixIsEnabledButNotFound;
use Core\Contracts\RequestInterface;
use Stellar\Routes\AbstractRouteMatcher;
use Stellar\Settings\Exceptions\InvalidSettingException;

class RouteMatch extends AbstractRouteMatcher
{
    /**
     * @param RequestInterface $request
     * @return Route|null
     * @throws RouteNotFoundException
     * @throws PrefixIsEnabledButNotFound
     * @throws InvalidSettingException
     */
    public function discover(RequestInterface $request): ?Route
    {
        RouteDriver::discover($request);

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
            $this->route = $this->discover($this->request);
        }

        return $this->route;
    }
}