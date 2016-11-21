<?php

namespace Phpfox\Router;

/**
 * Class RouteManager
 *
 * @package Phpfox\Router
 */
class RouteManager
{
    /**
     * router by name
     *
     * @var RouteInterface[]
     */
    protected $routes = [];

    /**
     * RouteManager constructor.
     */
    public function __construct()
    {
        $this->reset();
    }

    /**
     * Start to build routing
     */
    public function reset()
    {
        $this->routes = [];
        $routes = config('routes');
        foreach ($routes as $k => $v) {
            $this->routes[$k] = $this->build($v);
        }
    }

    /**
     * @ignore
     *
     * @param array $params
     *
     * @return RouteInterface
     */
    protected function build($params)
    {
        if (empty($params['type'])) {
            $params['type'] = StandardRoute::class;
        }

        return new $params['type']($params);
    }

    /**
     * has router
     *
     * @param string $id
     *
     * @return bool
     */
    public function has($id)
    {
        return isset($this->routes[$id]);
    }

    /**
     * @param string $id
     * @param array  $params
     *
     * @return $this
     */
    public function add($id, $params)
    {
        $this->routes[$id] = $this->build($params);

        return $this;
    }

    /**
     * @param string $id
     * @param array  $params
     *
     * @return string
     */
    public function getUrl($id, $params = [])
    {
        return $this->get($id)->getUrl($params);
    }

    /**
     * @param  string $id
     *
     * @return RouteInterface
     * @throws RouteException
     */
    public function get($id)
    {
        if (isset($this->routes[$id])) {
            return $this->routes[$id];
        }

        throw new RouteException("Unexpected route '{$id}'");
    }

    /**
     * @param string $path
     * @param string $host
     * @param string $method
     * @param string $protocol
     *
     * @return RouteResult
     */
    public function resolve($path, $host, $method, $protocol)
    {
        $result = new RouteResult();

        foreach ($this->routes as $id => $route) {
            if (!$route->match($path, $host, $method, $protocol, $result)) {
                $result->reset();
                continue;
            }
            break;
        }

        $result->ensure();

        return $result;
    }
}