<?php
/**
 * Created by PhpStorm.
 * User: ehsanabbasi
 * Date: 26/04/15
 * Time: 6:56 PM.
 */

namespace CodeJetter\core;

/**
 * Class BaseController.
 */
abstract class BaseController extends Base
{
    private $httpInputs;

    /**
     * @param RouteInfo $routeInfo
     * @param array     $httpInputs
     */
    public function __construct(RouteInfo $routeInfo, array $httpInputs)
    {
        $this->setRouteInfo($routeInfo);
        $this->setHttpInputs($httpInputs);
    }

    /**
     * @return array $httpInputs
     */
    public function getHttpInputs()
    {
        return $this->httpInputs;
    }

    /**
     * @param array $httpInputs
     */
    public function setHttpInputs($httpInputs)
    {
        $this->httpInputs = $httpInputs;
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
     * @return string
     */
    public function getTemplatesPath()
    {
        $component = $this->getRouteInfo()->getComponent();

        return 'components'.DIRECTORY_SEPARATOR.strtolower($component).DIRECTORY_SEPARATOR.'templates'.
        DIRECTORY_SEPARATOR;
    }
}
