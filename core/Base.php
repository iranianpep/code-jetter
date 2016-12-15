<?php

namespace CodeJetter\core;

use CodeJetter\components\user\services\UserAuthentication;

abstract class Base
{
    private $routeInfo;

    /**
     * Return the current access role.
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
        // TODO what if $this->getRouteInfo() is null, specially while testing?
        $componentName = $this->getRouteInfo()->getComponent();

        if ($lowercase === true) {
            return strtolower($componentName);
        } else {
            return $componentName;
        }
    }

    /**
     * @param bool $trailingSlash
     *
     * @throws \Exception
     *
     * @return string
     */
    public function getComponentsPath($trailingSlash = true)
    {
        $rootNamespace = Registry::getConfigClass()->get('ROOT_NAMESPACE');

        $componentsPath = $rootNamespace.DIRECTORY_SEPARATOR.'components';

        return ($trailingSlash === true) ? $componentsPath.DIRECTORY_SEPARATOR : $componentsPath;
    }

    /**
     * @param bool $trailingBackSlash
     *
     * @throws \Exception
     *
     * @return string
     */
    public function getComponentsNamespace($trailingBackSlash = true)
    {
        $rootNamespace = Registry::getConfigClass()->get('ROOT_NAMESPACE');
        $componentsNamespace = "{$rootNamespace}\\components";

        return ($trailingBackSlash === true) ? $componentsNamespace.'\\' : $componentsNamespace;
    }

    /**
     * @param null $component
     * @param bool $trailingSlash
     *
     * @throws \Exception
     *
     * @return string
     */
    public function getModelsPath($component = null, $trailingSlash = true)
    {
        $component = empty($component) ? $this->getComponentName(true) : strtolower($component);

        $path = $this->getComponentsPath().$component.DIRECTORY_SEPARATOR.'models';

        return ($trailingSlash === true) ? $path.DIRECTORY_SEPARATOR : $path;
    }

    /**
     * @param null $component
     * @param bool $trailingSlash
     *
     * @throws \Exception
     *
     * @return string
     */
    public function getControllersPath($component = null, $trailingSlash = true)
    {
        $component = empty($component) ? $this->getComponentName(true) : strtolower($component);

        $path = $this->getComponentsPath().$component.DIRECTORY_SEPARATOR.'controllers';

        return ($trailingSlash === true) ? $path.DIRECTORY_SEPARATOR : $path;
    }

    /**
     * @param null $component
     * @param bool $trailingSlash
     *
     * @throws \Exception
     *
     * @return string
     */
    public function getMappersPath($component = null, $trailingSlash = true)
    {
        $component = empty($component) ? $this->getComponentName(true) : strtolower($component);

        $path = $this->getComponentsPath().$component.DIRECTORY_SEPARATOR.'mappers';

        return ($trailingSlash === true) ? $path.DIRECTORY_SEPARATOR : $path;
    }

    /**
     * @param null $component
     * @param bool $trailingBackSlash
     *
     * @return string
     */
    public function getModelsNamespace($component = null, $trailingBackSlash = true)
    {
        $component = empty($component) ? $this->getComponentName(true) : strtolower($component);

        $namespace = $this->getComponentsNamespace().$component.'\\'.'models';

        return ($trailingBackSlash === true) ? $namespace.'\\' : $namespace;
    }

    /**
     * @param null $component
     * @param bool $trailingBackSlash
     *
     * @throws \Exception
     *
     * @return string
     */
    public function getControllersNamespace($component = null, $trailingBackSlash = true)
    {
        $component = empty($component) ? $this->getComponentName(true) : strtolower($component);

        $namespace = $this->getComponentsNamespace().$component.'\\'.'components';

        return ($trailingBackSlash === true) ? $namespace.'\\' : $namespace;
    }

    /**
     * @param null $component
     * @param bool $trailingBackSlash
     *
     * @return string
     */
    public function getMappersNamespace($component = null, $trailingBackSlash = true)
    {
        $component = empty($component) ? $this->getComponentName(true) : strtolower($component);

        $namespace = $this->getComponentsNamespace().$component.'\\'.'mappers';

        return ($trailingBackSlash === true) ? $namespace.'\\' : $namespace;
    }
}
