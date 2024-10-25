<?php

namespace BlazarRouter;

use BlazarRouter\Route\Exceptions\RouteNameAlreadyInUse;
use BlazarRouter\Router\Exceptions\PrefixIsEnabledButNotFound;
use Stellar\Navigation\Directory;
use Stellar\Navigation\Enums\ApplicationPath;
use Stellar\Navigation\Path\Exceptions\PathNotFound;
use Stellar\Settings\Exceptions\InvalidSettingException;

class RouteFinder extends AbstractRouteFinder
{
    /**
     * @return void
     * @throws InvalidSettingException
     * @throws RouteNameAlreadyInUse
     * @throws PrefixIsEnabledButNotFound
     */
    public function findRoutes(): void
    {
        $route_files = [];

        try {
            $route_files = Directory::scan(root_path(ApplicationPath::Routes->value));
        } catch (PathNotFound) {
            return;
        }

        foreach ($route_files as $route_file) {
            try {
                require_once root_path(ApplicationPath::Routes->additionalPath($route_file));
            } catch (PathNotFound) {
            }
        }

        RouterService::getInstance()->updateRoutesWithPrefix()->loadNames();
    }
}