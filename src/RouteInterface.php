<?php
namespace Phpfox\Router;


interface RouteInterface
{
    public function match();

    public function compile();
}