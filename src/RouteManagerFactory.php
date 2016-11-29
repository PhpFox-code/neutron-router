<?php

namespace Neutron\Router;

/**
 * Class RouteManagerFactory
 *
 * @package Neutron\Router
 */
class RouteManagerFactory
{
    /**
     * @return RouteContainer
     */
    public function factory()
    {
        return new RouteContainer();
    }

    public function shouldCache()
    {
        return false;
    }
}