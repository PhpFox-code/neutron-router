<?php
namespace Phpfox\Router;

/**
 * Class RouteResult
 *
 * @package Phpfox\Router
 */
class RouteResult
{
    /**
     * @var string
     */
    protected $controllerName;

    /**
     * @var string
     */
    protected $actionName;

    /**
     * @var array
     */
    protected $params = [];

    /**
     * @return string
     */
    public function getControllerName()
    {
        return $this->controllerName;
    }

    /**
     * @param string $controllerName
     *
     * @return $this
     */
    public function setControllerName($controllerName)
    {
        $this->controllerName = $controllerName;
        return $this;
    }

    /**
     * @return string
     */
    public function getActionName()
    {
        return $this->actionName;
    }

    /**
     * @param string $actionName
     *
     * @return $this
     */
    public function setActionName($actionName)
    {
        $this->actionName = $actionName;
        return $this;
    }

    /**
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * @param array $params
     */
    public function setParams($params)
    {
        $this->params = [];
        foreach ($params as $k => $v) {
            if ($k == 'controller' || $k == 'controllerName') {
                $this->controllerName = $v;
            } else {
                if ($k == 'action' || $k == 'actionName') {
                    $this->actionName = $v;
                } else {
                    $this->params[$k] = $v;
                }
            }
        }
    }
}