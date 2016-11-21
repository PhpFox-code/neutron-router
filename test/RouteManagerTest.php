<?php

namespace Phpfox\Router;


class RouteManagerTest extends \PHPUnit_Framework_TestCase
{

    public function testService()
    {
        $routing = service('routing');

        $this->assertNotNull($routing, 'Can not init routing service');
    }

    public function testLogin()
    {
        $routing = service('routing');

        $result  = $routing->resolve('login', null, null);

        $this->assertNotNull($result);

    }
}
