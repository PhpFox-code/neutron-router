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
    private $masters = [];

    /**
     * Children router by name
     *
     * @var RouteInterface[]
     */
    private $children = [];

    /**
     * @var array
     */
    private $indexes = [];

    /**
     * @var array
     */
    private $temporary = [];

    /**
     * Start to build routing
     */
    public function bound()
    {
        $routing = \app()->cache()->get($cacheKey = 'platform_routing_start',
            0);

        if (!$routing) {

            \app()->emit('onRoutingStart', $this);

            $this->compileRoutes();

            \app()->cache()->set($cacheKey,
                serialize([$this->masters, $this->children, $this->indexes]),
                0);

        } else {
            list($this->masters, $this->children, $this->indexes)
                = unserialize($routing);
        }
    }

    /**
     * @ignore
     */
    public function compileRoutes()
    {
        $this->masters = [];
        $this->children = [];

        // clone delegate children data
        if (!empty($this->temporary['delegate'])) {
            foreach ($this->temporary['delegate'] as $name => $delegate) {
                $combine = [];

                if (!empty($this->temporary['children'][$name])) {
                    $combine = $this->temporary['children'][$name];
                }

                if (!empty($this->temporary['children'][$delegate])) {
                    $combine = array_merge($combine,
                        $this->temporary['children'][$delegate]);

                }
                $this->temporary['children'][$name] = $combine;
                $this->temporary['children'][$delegate] = $combine;
            }
        }


        /**
         * add children
         */
        foreach ($this->temporary['master'] as $name => $master) {
            if (!empty($this->temporary['children'][$name])) {
                $this->temporary['master'][$name]['children']
                    = array_keys($this->temporary['children'][$name]);
            }
        }

        // re-merged all data for children

        if (!empty($this->temporary['children'])) {
            foreach ($this->temporary['children'] as $group => $children) {
                if (empty($this->temporary['master'][$group])) {
                    throw new \InvalidArgumentException(sprintf('Unexpected group "%s", Could not compile children',
                        $group));
                }
                $master = $this->temporary['master'][$group];
                foreach ($children as $name => $child) {
                    $this->temporary['children'][$group][$name]
                        = $this->correctChildData($child, $master);
                    $this->indexes[$group][] = $group . '/' . $name;
                }
            }
        }


        /**
         * Initial we create master rules
         */
        foreach ($this->temporary['master'] as $name => $master) {
            $this->masters[$master['name']] = $this->create($master);
        }

        if (!empty($this->temporary['children'])) {
            foreach ($this->temporary['children'] as $group => $children) {
                foreach ($children as $name => $child) {
                    $key = $group . '/' . $name;
                    $this->children[$key] = $this->create($child);
                }
            }
        }

        unset($this->temporary);
    }

    /**
     * @ignore
     * @codeCoverageIgnore
     *
     * @param array $child
     * @param array $parent
     *
     * @return array
     */
    protected function correctChildData($child, $parent)
    {
        if (empty($child['tokens'])) {
            throw new \InvalidArgumentException(sprintf('Missing params "tokens"'));
        }

        $tokens = $this->correctTokensForChildRoute($child['tokens']);

        unset($child['tokens']);

        if (!empty($parent['protocol'])) {
            $child['protocol'] = $parent['protocol'];
        }

        if (!empty($parent['uri'])) {
            $child['uri'] = strtr($parent['uri'], $tokens);
        }

        if (!empty($parent['host'])) {
            $child['host'] = strtr($parent['host'], $tokens);
        }

        if (!empty($parent['defaults'])) {
            if (!empty($child['defaults'])) {
                $child['defaults'] = array_merge($parent['defaults'],
                    $child['defaults']);
            } else {
                $child['defaults'] = $parent['defaults'];
            }
        }

        return $child;
    }

    /**
     * @ignore
     * @codeCoverageIgnore
     *
     * @param $tokens
     *
     * @return array
     */
    protected function correctTokensForChildRoute($tokens)
    {
        $result = [];

        foreach ($tokens as $key => $value) {

            $key = preg_replace('(\W+)', '', $key);
            $value = '/' . trim($value, '/');

            $key1 = '(/<' . $key . '>)';
            $key2 = '/<' . $key . '>';

            $result[$key1] = $value;
            $result[$key2] = $value;
        }

        return $result;
    }

    /**
     * @ignore
     *
     * @param array $params
     *
     * @return RouteInterface
     */
    protected function create($params)
    {
        $class = empty($params['class']) ? StandardRoute::class
            : $params['class'];

        return new $class($params);
    }

    /**
     * has router
     *
     * @param string $name
     *
     * @return bool
     */
    public function hasRoute($name)
    {
        return isset($this->masters[$name]);
    }

    /**
     * @param string $group
     * @param string $uri
     * @param string $host
     * @param string $method
     * @param Result $result
     *
     * @return bool
     */
    public function resolveChildren($group, $uri, $host, $method, $result)
    {
        if (empty($this->indexes[$group])) {
            return false;
        }

        foreach ($this->indexes[$group] as $name) {

            if (!isset($this->children[$name])) {
                continue;
            }

            if ($this->children[$name]->resolve($uri, $host, $method, $result,
                true)
            ) {
                return true;
            }

        }

        return false;
    }

    /**
     * @param array $params Routing Params
     *
     * @return $this
     */
    public function add($params)
    {
        $arr = explode('/', $params['name'], 2);

        if (count($arr) == 2) {
            $params['name'] = $arr[1];
            $this->temporary['children'][$arr[0]][$arr[1]] = $params;
        } else {
            $this->temporary['master'][$params['name']] = $params;
        }

        if (isset($params['delegate'])) {
            $this->temporary['delegate'][$params['name']] = $params['delegate'];
        }

        return $this;
    }

    /**
     * @param string $name
     * @param array  $params
     *
     * @return string
     */
    public function getUrl($name, $params = [])
    {
        return $this->getRoute($name)->getUrl($params);
    }

    /**
     * @param  string $name
     *
     * @return RouteInterface
     * @throws \InvalidArgumentException
     */
    public function getRoute($name)
    {
        if (isset($this->masters[$name])) {
            return $this->masters[$name];
        }


        if (isset($this->children[$name])) {
            return $this->children[$name];
        }

        throw new \InvalidArgumentException(sprintf('Unexpected route "%s"',
            $name));


    }

    /**
     * @param string $path
     * @param string $host
     * @param string $method
     *
     * @return Result
     */
    public function resolve($path, $host = null, $method = null)
    {
        $result = new Result();
        $matched = false;

        foreach ($this->masters as $name => $route) {
            if (!$route->resolve($path, $host, $method, $result, false)) {
                continue;
            }
            $matched = true;
            break;
        }

        if (!$matched) {
            $result->setControllerName('Platform\Core\Controller\ErrorController');
            $result->setActionName('page-not-found');
        }

        return $result;
    }
}