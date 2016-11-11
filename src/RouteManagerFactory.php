<?php

namespace Phpfox\RouteManager;

/**
 * Class RouteManagerFactory
 *
 * @package Phpfox\RouteManager
 */
class RouteManagerFactory
{
    /**
     * @return RouteManager
     */
    public function factory()
    {
        return new RouteManager();
    }

    public function shouldCache()
    {
        return false;
    }
}