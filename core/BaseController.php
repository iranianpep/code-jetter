<?php
    /**
     * Created by PhpStorm.
     * User: ehsanabbasi
     * Date: 26/04/15
     * Time: 6:56 PM
     */

namespace CodeJetter\core;

/**
 * Class BaseController
 * @package CodeJetter\core
 */
abstract class BaseController extends Base
{
    private $HTTPInputs;
    private $routeInfo;

    /**
     * @param RouteInfo $routeInfo
     * @param array $HTTPInputs
     */
    public function __construct(RouteInfo $routeInfo, array $HTTPInputs)
    {
        $this->setRouteInfo($routeInfo);
        $this->setHTTPInputs($HTTPInputs);
    }

    /**
     * @return array $HTTPInputs
     */
    public function getHTTPInputs()
    {
        return $this->HTTPInputs;
    }

    /**
     * @param array $HTTPInputs
     */
    public function setHTTPInputs($HTTPInputs)
    {
        $this->HTTPInputs = $HTTPInputs;
    }

    /**
     * @return array
     */
    public function getURLParameters()
    {
        $routeInfo = $this->getRouteInfo();
        $parameters = $routeInfo->getParameters();
        return (!empty($parameters)) ? $parameters : false;
    }

    /**
     * @return string
     */
    public function getBasePath()
    {
        $routeInfo = $this->getRouteInfo();
        $base = $routeInfo->getBaseUrl();
        return (!empty($base)) ? $base : false;
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
     * @return string
     */
    public function getTemplatesPath()
    {
        $component = $this->getRouteInfo()->getComponent();

        return 'components' . DIRECTORY_SEPARATOR . strtolower($component) . DIRECTORY_SEPARATOR . 'templates' .
        DIRECTORY_SEPARATOR;
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
}
