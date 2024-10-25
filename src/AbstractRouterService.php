<?php

namespace BlazarRouter;

abstract class AbstractRouterService
{
    abstract public static function finder(): string;

    abstract public static function matcher(): string;
}