<?php

namespace BlazarRouter;

use Stellar\Services\AbstractService;
use Stellar\Services\AbstractService\Traits\SingletonService;

abstract class AbstractRouteMatch extends AbstractService
{
    use SingletonService;

    protected Route $route;

    abstract public function getMatchRoute(): ?Route;
}