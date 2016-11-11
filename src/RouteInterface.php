<?php
namespace Phpfox\RouteManager;

interface RouteInterface
{
    /**
     * @param string $uri
     * @param string $host
     * @param string $method
     * @param Result $result
     * @param bool   $is_children
     *
     * @return mixed
     */
    public function resolve(
        $uri,
        $host = null,
        $method = null,
        $result,
        $is_children = false
    );

    /**
     * @param array $params
     *
     * @return string
     */
    public function getUrl($params = []);
}