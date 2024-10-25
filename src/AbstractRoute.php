<?php

namespace BlazarRouter;

use Core\Contracts\Route\RouteInterface;

abstract class AbstractRoute implements RouteInterface
{
    abstract public function call(): mixed;
}