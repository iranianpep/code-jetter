<?php

namespace CodeJetter\core;

/**
 * Class RouteInfo
 * @package CodeJetter\core
 */
class RouteInfo
{
    private $routeType;
    private $requestMethod;
    private $url;
    private $component;
    private $controller;
    private $action;
    private $baseUrl;
    private $accessRole;
    private $parameters;
    private $requestedUrl;
    private $checkAntiCSRFToken;

    /**
     * RouteInfo constructor.
     *
     * @param        $routeType
     * @param        $requestMethod
     * @param        $url
     * @param        $component
     * @param        $controller
     * @param string $action
     * @param string $accessRole
     * @param null   $baseUrl
     * @param null   $parameters
     * @param null   $requestedUrl
     */
    public function __construct(
        $routeType,
        $requestMethod,
        $url,
        $component,
        $controller,
        $action = 'index',
        $accessRole = 'public',
        $baseUrl = null,
        $parameters = null,
        $requestedUrl = null
    ) {
        $this->setRouteType($routeType);
        $this->setRequestMethod($requestMethod);
        $this->setUrl($url);
        $this->setComponent($component);
        $this->setController($controller);
        $this->setAction($action);
        $this->setAccessRole($accessRole);
        $this->setBaseUrl($baseUrl);
        $this->setParameters($parameters);
        $this->setRequestedUrl($requestedUrl);
    }

    /**
     * @return string
     */
    public function getAccessRole()
    {
        return $this->accessRole;
    }

    /**
     * @param string $accessRole
     */
    public function setAccessRole($accessRole)
    {
        $this->accessRole = $accessRole;
    }

    /**
     * @return string
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * @param string $action
     */
    public function setAction($action)
    {
        $this->action = $action;
    }

    /**
     * @return string
     */
    public function getBaseUrl()
    {
        return $this->baseUrl;
    }

    /**
     * @param string $baseUrl
     */
    public function setBaseUrl($baseUrl)
    {
        $this->baseUrl = $baseUrl;
    }

    /**
     * @return string
     */
    public function getComponent()
    {
        return $this->component;
    }

    /**
     * @param string $component
     */
    public function setComponent($component)
    {
        $this->component = $component;
    }

    /**
     * @return string
     */
    public function getController()
    {
        return $this->controller;
    }

    /**
     * @param string $controller
     */
    public function setController($controller)
    {
        $this->controller = $controller;
    }

    /**
     * @return string
     */
    public function getRequestMethod()
    {
        return $this->requestMethod;
    }

    /**
     * @param string $requestMethod
     */
    public function setRequestMethod($requestMethod)
    {
        $this->requestMethod = $requestMethod;
    }

    /**
     * @return string
     */
    public function getRouteType()
    {
        return $this->routeType;
    }

    /**
     * @param string $routeType
     */
    public function setRouteType($routeType)
    {
        $this->routeType = $routeType;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param string $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

    /**
     * @return mixed
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * @param mixed $parameters
     */
    public function setParameters($parameters)
    {
        $this->parameters = $parameters;
    }

    /**
     * @return string
     */
    public function getRequestedUrl()
    {
        return $this->requestedUrl;
    }

    /**
     * @param string $requestedUrl
     */
    public function setRequestedUrl($requestedUrl)
    {
        $this->requestedUrl = $requestedUrl;
    }

    /**
     * @return boolean
     */
    public function getCheckAntiCSRFToken()
    {
        return !isset($this->checkAntiCSRFToken) ? false : $this->checkAntiCSRFToken;
    }

    /**
     * @param boolean $checkAntiCSRFToken
     */
    public function setCheckAntiCSRFToken($checkAntiCSRFToken)
    {
        $this->checkAntiCSRFToken = $checkAntiCSRFToken;
    }
}
