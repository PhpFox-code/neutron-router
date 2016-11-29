<?php

namespace Neutron\Router;


interface FilterInterface
{
    public function filter(RouteResult $result);
}