<?php
/**
 * Created by PhpStorm.
 * User: ehsanabbasi
 * Date: 25/04/15
 * Time: 9:50 PM.
 */

namespace CodeJetter\core\io;

use CodeJetter\core\Registry;
use CodeJetter\core\security\Security;

/**
 * Class Request.
 */
class Request
{
    private $requestMethod;
    private $inputs;
    private $queryString;

    /**
     * Request constructor.
     *
     * @param null $requestMethod
     */
    public function __construct($requestMethod = null)
    {
        $this->requestMethod = $requestMethod !== null ? $requestMethod : $this->getRequestMethod();
    }

    /**
     * @param array $inputKeys
     * @param null  $requestMethod
     *
     * @return array
     */
    public function getInputs(array $inputKeys = [], $requestMethod = null)
    {
        if (isset($this->inputs)) {
            return $this->inputs;
        }

        // if $requestMethod is passed, overwrite others
        if ($requestMethod === null) {
            $requestMethod = $this->getRequestMethod();
        }

        $inputs = [];
        switch ($requestMethod) {
            case 'GET':
                if (!empty($inputKeys)) {
                    foreach ($inputKeys as $inputKey) {
                        if (isset($_GET[$inputKey])) {
                            $inputs[$inputKey] = $_GET[$inputKey];
                        }
                    }
                } else {
                    $inputs = $_GET;
                }

                break;
            case 'POST':
                if (!empty($inputKeys)) {
                    foreach ($inputKeys as $inputKey) {
                        if (isset($_POST[$inputKey])) {
                            $inputs[$inputKey] = $_POST[$inputKey];
                        }
                    }
                } else {
                    $inputs = $_POST;
                }

                break;
        }

        $this->setInputs($inputs);

        return $inputs;
    }

    /**
     * @return mixed
     */
    public function detectRequestMethod()
    {
        return $this->getServer('REQUEST_METHOD');
    }

    /**
     * @param $inputs
     */
    public function setInputs($inputs)
    {
        $this->inputs = $inputs;
    }

    /**
     * @throws \Exception
     *
     * @return Output
     */
    public function checkOrigin()
    {
        $output = new Output();

        switch ($this->getRequestMethod()) {
            case 'POST':
                $tokenName = Registry::getConfigClass()->get('defaultGlobalCSRFHtmlTokenName');
                $tokenMatch = (new Security())->checkAntiCSRF($_POST[$tokenName]);

                if (!isset($_POST['globalCSRFToken']) || $tokenMatch !== true) {
                    $output->setSuccess(false);
                    $output->setMessage('Global CSRF Token Mismatch. Please refresh the page');
                }
                break;
        }

        return $output;
    }

    /**
     * @return string
     */
    public function getRequestMethod()
    {
        if (!isset($this->requestMethod)) {
            $this->requestMethod = $this->detectRequestMethod();
        }

        return $this->requestMethod;
    }

    /**
     * @return string
     */
    public function getServerName()
    {
        return $this->getServer('SERVER_NAME');
    }

    /**
     * @return string
     */
    public function getRequestURI()
    {
        return $this->getServer('REQUEST_URI');
    }

    /**
     * @return string
     */
    public function getUserIP()
    {
        return $this->getServer('REMOTE_ADDR') ?: $this->getServer('HTTP_X_FORWARDED_FOR') ?: $this->getServer('HTTP_CLIENT_IP');
    }

    /**
     * @return string
     */
    public function getUserAgent()
    {
        return $this->getServer('HTTP_USER_AGENT');
    }

    /**
     * @return string
     */
    public function getReferer()
    {
        return $this->getServer('HTTP_REFERER');
    }

    /**
     * @return string
     */
    public function getStartTime()
    {
        /*
         * REQUEST_TIME_FLOAT and REQUEST_TIME are not accessible through filter_input
         * This is a reported bug: https://bugs.php.net/bug.php?id=61497
         */
        return $_SERVER['REQUEST_TIME_FLOAT'];
    }

    /**
     * @param $key
     *
     * @return string|bool
     */
    public function getServer($key)
    {
        switch ($key) {
            case 'SERVER_NAME':
                $info = filter_input(INPUT_SERVER, 'SERVER_NAME', FILTER_SANITIZE_STRING);
                break;
            case 'REQUEST_URI':
                $info = filter_input(INPUT_SERVER, 'REQUEST_URI', FILTER_SANITIZE_STRING);
                break;
            case 'REQUEST_METHOD':
                $info = filter_input(INPUT_SERVER, 'REQUEST_METHOD', FILTER_SANITIZE_STRING);
                break;
            case 'REMOTE_ADDR':
                $info = filter_input(INPUT_SERVER, 'REMOTE_ADDR', FILTER_SANITIZE_STRING);
                break;
            case 'HTTP_X_FORWARDED_FOR':
                $info = filter_input(INPUT_SERVER, 'HTTP_X_FORWARDED_FOR', FILTER_SANITIZE_STRING);
                break;
            case 'HTTP_CLIENT_IP':
                $info = filter_input(INPUT_SERVER, 'HTTP_CLIENT_IP', FILTER_SANITIZE_STRING);
                break;
            case 'HTTP_USER_AGENT':
                $info = filter_input(INPUT_SERVER, 'HTTP_USER_AGENT', FILTER_SANITIZE_STRING);
                break;
            case 'HTTP_REFERER':
                $info = filter_input(INPUT_SERVER, 'HTTP_REFERER', FILTER_SANITIZE_STRING);
                break;
            case 'HTTP_X_REQUESTED_WITH':
                $info = filter_input(INPUT_SERVER, 'HTTP_X_REQUESTED_WITH', FILTER_SANITIZE_STRING);
                break;
            default:
                throw new \Exception("Key '{$key}' is invalid server key info");
        }

        // Some servers returns null for a valid server key, in that case get value directly
        if (empty($info) && isset($_SERVER[$key])) {
            $info = $_SERVER[$key];
        }

        return $info;
    }

    /**
     * @return string
     */
    public function getURLPath()
    {
        $URI = $this->getRequestURI();

        $removeURLTrailingSlash = Registry::getConfigClass()->get('removeURLTrailingSlash');

        if ($removeURLTrailingSlash === true) {
            $URI = rtrim($URI, '/');
        }

        return parse_url($URI, PHP_URL_PATH);
    }

    /**
     * @return bool
     */
    public function isAJAX()
    {
        $requestedWith = $this->getServer('HTTP_X_REQUESTED_WITH');

        if (isset($requestedWith) && !empty($requestedWith) && strtolower($requestedWith) == 'xmlhttprequest') {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @return string
     */
    public function getQueryString()
    {
        if (!isset($this->queryString)) {
            $uri = $this->getRequestURI();
            $parsedUri = parse_url($uri);

            if (isset($parsedUri['query'])) {
                $this->setQueryString($parsedUri['query']);

                return $parsedUri['query'];
            }
        }

        return $this->queryString;
    }

    /**
     * @param string $queryString
     */
    public function setQueryString($queryString)
    {
        $this->queryString = $queryString;
    }

    /**
     * Get query string parameter - if $key is null, return all.
     *
     * @param null $key
     * @param null $queryString
     *
     * @return string
     */
    public function getQueryStringVariables($key = null, $queryString = null)
    {
        if ($queryString === null) {
            // get query string from request
            $queryString = $this->getQueryString();
        }

        parse_str($queryString, $output);

        if ($key === null) {
            // return all
            return $output;
        } else {
            return (isset($output[$key])) ? $output[$key] : '';
        }
    }

    /**
     * @return string
     */
    public function getSortingFromQueryString()
    {
        $config = Registry::getConfigClass();
        $orderByKey = $config->get('list')['orderBy'];
        $orderDirKey = $config->get('list')['orderDir'];

        $order = (new self())->getInputs([$orderByKey, $orderDirKey], 'GET');

        $orderQuery = '';

        if (!empty($order) && isset($order[$orderByKey])) {
            if (!empty($exclude) && in_array($order[$orderByKey], $exclude)) {
                return '';
            }

            $orderDir = (!isset($order[$orderDirKey]) ||
                strtoupper($order[$orderDirKey]) === 'ASC') ? 'ASC' : 'DESC';

            $orderBy = strtolower($order[$orderByKey]);
            $orderQuery = "{$orderBy} {$orderDir}";
        }

        return $orderQuery;
    }
}
