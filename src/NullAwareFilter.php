<?php

namespace Neutron\Router;

/**
 * Class NullAwareFilter
 *
 * @package Neutron\Router
 */
class NullAwareFilter implements FilterInterface
{
    public function filter(RouteResult $result)
    {
        return true;
    }

}