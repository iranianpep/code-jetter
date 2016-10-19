<?php

namespace CodeJetter\core;

use CodeJetter\components\user\services\UserAuthentication;

abstract class Base
{
    private $routeInfo;

    /**
     * Return the current access role
     *
     * @return bool|string
     */
    public function viewingAs()
    {
        return (new UserAuthentication())->viewingAs();
    }

    /**
     * @return bool
     */
    public function viewingAsAdmin()
    {
        return (new UserAuthentication())->viewingAsAdmin();
    }

    /**
     * @return bool
     */
    public function getCurrentLoggedIn()
    {
        return (new UserAuthentication())->getCurrentLoggedIn();
    }

    /**
     * @return RouteInfo
     */
    public function getRouteInfo()
    {
        return $this->routeInfo;
    }

    /**
     * @param RouteInfo $routeInfo
     */
    public function setRouteInfo(RouteInfo $routeInfo)
    {
        $this->routeInfo = $routeInfo;
    }

    /**
     * @param bool $lowercase
     *
     * @return string
     */
    public function getComponentName($lowercase = false)
    {
        $componentName = $this->getRouteInfo()->getComponent();

        if ($lowercase === true) {
            return strtolower($componentName);
        } else {
            return $componentName;
        }
    }

    /**
     * @param null $component
     *
     * @return string
     * @throws \Exception
     */
    public function getModelsPath($component = null)
    {
        $component = empty($component) ? $this->getComponentName(true) : strtolower($component);

        $rootNamespace = Registry::getConfigClass()->get('ROOT_NAMESPACE');

        return $rootNamespace . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . $component .
        DIRECTORY_SEPARATOR . 'models';
    }
}
