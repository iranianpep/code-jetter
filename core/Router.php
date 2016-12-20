<?php

namespace CodeJetter\core;

use CodeJetter\components\page\models\MetaTag;
use CodeJetter\components\page\models\Page;
use CodeJetter\components\user\models\User;
use CodeJetter\components\user\services\UserAuthentication;
use CodeJetter\core\io\Output;
use CodeJetter\core\io\Request;
use CodeJetter\core\io\Response;
use CodeJetter\core\layout\blocks\ComponentTemplate;
use CodeJetter\Routes;

/**
 * Class Router.
 */
class Router
{
    private static $defaultComponent;
    private static $defaultController;
    private static $defaultAction;
    private static $regexDelimiter;
    private static $regexTypedPatterns;
    private $lastRoute;

    /**
     * Router constructor.
     */
    public function __construct()
    {
        static::$defaultComponent = Registry::getConfigClass()->get('defaultComponent');
        static::$defaultController = Registry::getConfigClass()->get('defaultController');
        static::$defaultAction = Registry::getConfigClass()->get('defaultAction');
        static::$regexDelimiter = Registry::getConfigClass()->get('regexDelimiter');
        static::$regexTypedPatterns = Registry::getConfigClass()->get('regexTypedPatterns');
    }

    /**
     * convert pattern to regex and return match ones.
     *
     * @param $pattern
     * @param $path
     *
     * @return bool
     */
    public function regexMatch($pattern, $path)
    {
        if (empty($pattern) || empty($path)) {
            return false;
        }

        $patternPieces = explode('/', $pattern);

        /**
         * start forming the regex pattern.
         */
        $regexDelimiter = static::$regexDelimiter;
        $regexPattern = "{$regexDelimiter}^";

        $i = 0;
        $patternPiecesNo = count($patternPieces);

        foreach ($patternPieces as $patternPiece) {
            $i++;

            // skip empty values
            if (empty($patternPiece)) {
                continue;
            }

            // extract parameter and type from inside the curly braces
            $found = preg_match("{$regexDelimiter}(?<={)[^}]*(?=}){$regexDelimiter}", $patternPiece, $matches);

            // first piece cannot be parameter
            if ($i !== 1 && $found === 1) {
                // convert :parameter|type to an array
                $parameterType = explode(':', $matches[0]);

                $parameter = $parameterType[0];

                // base on type, append regex
                $type = $parameterType[1];

                if (array_key_exists($type, static::$regexTypedPatterns)) {
                    $regexTypedPattern = static::$regexTypedPatterns[$type];
                } else {
                    // default
                    $regexTypedPattern = static::$regexTypedPatterns['string'];
                }

                // Only last variable in regex can be optional
                if ($i === $patternPiecesNo && isset($parameterType[2]) && $parameterType[2] === '?') {
                    $optional = '?';
                } else {
                    $optional = '';
                }

                $regexPattern .= "/{$optional}(?<{$parameter}>{$regexTypedPattern}){$optional}";
            } else {
                $regexPattern .= '/'.$patternPiece;
            }
        }

        $regexPattern .= "\${$regexDelimiter}";
        /**
         * finish forming the regex pattern.
         */

        // check pattern against path
        $found = preg_match($regexPattern, $path, $matches);

        if ($found === 1) {
            return $matches;
        } else {
            return false;
        }
    }

    /**
     * Return route info and parameters by URL path and request method.
     *
     * @param $urlPath
     * @param $requestMethod
     *
     * @return RouteInfo
     */
    public function getRouteInfo($urlPath, $requestMethod)
    {
        if (empty($requestMethod)) {
            return false;
        }

        if (empty($urlPath) || trim($urlPath) === '' || $urlPath === '/index.php') {
            $urlPath = '/';
        }

        $routes = $this->getRoutes();
        if (isset($routes['simple'][$requestMethod]) &&
            isset($routes['simple'][$requestMethod][$urlPath])) {
            // get route info straightway
            $url = $urlPath;
            $result = ['info' => $routes['simple'][$requestMethod][$urlPath]];
        } else {
            // look for regex
            if (!empty($routes['regex'][$requestMethod])) {
                foreach ($routes['regex'][$requestMethod] as $routePattern => $routeInfo) {
                    // find the match and return parameters if there is any
                    $found = $this->regexMatch($routePattern, $urlPath);

                    if (!empty($found)) {
                        $url = $routePattern;
                        $result = ['info' => $routeInfo, 'parameters' => $found];
                        break;
                    }
                }
            }
        }

        /*
         * Start creating route info object
         */
        if (!isset($result)) {
            return false;
        }

        $routeType = in_array('parameters', array_keys($result)) ? 'regex' : 'simple';
        $baseUrl = isset($result['info']['base']) ? $result['info']['base'] : null;
        $parameters = isset($result['parameters']) ? $result['parameters'] : null;
        $action = isset($result['info']['action']) ? $result['info']['action']
            : Registry::getConfigClass()->get('defaultAction');
        $accessRole = !empty($result['info']['accessRole']) ? $result['info']['accessRole']
            : $this->getAccessRole($urlPath);

        $routeInfoObject = new RouteInfo(
            $routeType,
            $requestMethod,
            $url,
            $result['info']['component'],
            $result['info']['controller'],
            $action,
            $accessRole,
            $baseUrl,
            $parameters,
            $urlPath
        );

        if (isset($result['info']['checkAntiCSRFToken'])) {
            $routeInfoObject->setCheckAntiCSRFToken($result['info']['checkAntiCSRFToken']);
        } else {
            // checkAntiCSRFToken is NOT set, use default value based on request method
            switch ($requestMethod) {
                case 'POST':
                    $routeInfoObject->setCheckAntiCSRFToken(true);
                    break;
                case 'GET':
                    $routeInfoObject->setCheckAntiCSRFToken(false);
                    break;
                default:
                    $routeInfoObject->setCheckAntiCSRFToken(true);
            }
        }
        /*
         * Finish creating route info object
         */

        return $routeInfoObject;
    }

    /**
     * @throws \Exception
     */
    public function route()
    {
        // get request method and URI
        $request = new Request();
        $requestMethod = $request->getRequestMethod();
        $HTTPInputs = $request->getInputs();
        $urlPath = $request->getURLPath();
        //$isAJAXRequest = $request->isAJAX();

        // get route info (component, controller, action, ..) by path and request method
        $routeInfo = $this->getRouteInfo($urlPath, $requestMethod);

        // TODO move this somewhere, maybe Response
        /*
         * For the time being, leave 404 handling here
         */
        if (empty($routeInfo)) {
            header('HTTP/1.0 404 Not Found');
            $page = new Page();
            $page->setTitle('404');

            $metaTag = (new MetaTag('robots', 'noindex, nofollow'));
            $page->addMetaTag($metaTag);

            $componentTemplate = new ComponentTemplate();
            $componentTemplate->setTemplatePath('404.php');

            (new View())->make(
                $page,
                [
                    '404' => $componentTemplate,
                ]
            );
            exit();
        }

        $rootNamespace = Registry::getConfigClass()->get('ROOT_NAMESPACE');

        $component = $routeInfo->getComponent();
        $accessRole = $routeInfo->getAccessRole();
        $controller = $routeInfo->getController();
        $action = $routeInfo->getAction();

        /**
         * Authorization - DO NOT REMOVE THIS:.
         */
        $authorized = $this->authorizeRoute($accessRole, $rootNamespace, 'user', $controller);

        // get controller
        $component = !empty($component) ? $component : static::$defaultComponent;
        $controller = !empty($controller) ? ucfirst($controller) : static::$defaultController;

        // get action
        $action = !empty($action) ? $action : static::$defaultAction;

        if (empty($controller) || empty($action)) {
            throw new \Exception('Controller and action cannot be empty');
        }

        /*
         * Set last route info here, after making sure that user is authorized
         */
        $this->setLastRoute($routeInfo);

        /*
         * Set user timezone
         */
        if ($authorized === true) {
            $currentUser = (new UserAuthentication())->getCurrentLoggedIn();

            if (!empty($currentUser) && $currentUser instanceof User) {
                // user is registered, set time zone
                $app = App::getInstance();
                $app->setTimeZone($currentUser->getTimeZone());
            }
        }

        // check session exists

        // check user last activity

        // check the origin of the request by checking the global anti CSRF token

        if ($routeInfo->getCheckAntiCSRFToken()) {
            $output = $request->checkOrigin();

            if ($output->getSuccess() === false) {
                (new Response())->echoContent($output->toJSON());
            }
        }

        $controllerClass = "{$rootNamespace}\\components\\{$component}\\controllers\\{$controller}Controller";

        $controllerObject = new $controllerClass($routeInfo, $HTTPInputs);
        $controllerObject->$action();
    }

    /**
     * @return RouteInfo
     */
    public function getLastRoute()
    {
        return $this->lastRoute;
    }

    /**
     * @param RouteInfo $lastRoute
     */
    public function setLastRoute(RouteInfo $lastRoute)
    {
        $this->lastRoute = $lastRoute;
    }

    /**
     * Return access role based on URL.
     *
     * This is used in getRouteInfo and should NOT be public
     *
     * @param       $urlPath
     * @param array $roles
     *
     * @throws \Exception
     *
     * @return int|mixed|string
     */
    private function getAccessRole($urlPath, $roles = [])
    {
        if (empty($roles)) {
            $roles = Registry::getConfigClass()->get('roles');
        }

        $baseURLs = [];

        if (!empty($roles)) {
            foreach ($roles as $key => $role) {
                if (empty($role['baseURL'])) {
                    continue;
                }

                if (in_array($role['baseURL'], $baseURLs)) {
                    throw new \Exception("baseURL for roles must be unique. '{$role['baseURL']}' is not unique.");
                }

                $baseURLs[$key] = $role['baseURL'];
            }
        }

        if (!empty($baseURLs)) {
            // sort base urls from long to short
            uasort($baseURLs, function ($url1, $url2) {
                return strlen($url2) - strlen($url1);
            });
        }

        $foundRole = '';
        if (!empty($baseURLs)) {
            foreach ($baseURLs as $role => $baseURL) {
                // do the regex - if found it break it, otherwise return the default access role

                if (preg_match("#^{$baseURL}#", $urlPath) === 1) {
                    $foundRole = $role;
                    break;
                }
            }
        }

        if (empty($foundRole)) {
            $foundRole = Registry::getConfigClass()->get('defaultRole');
        }

        return $foundRole;
    }

    /**
     * @param      $accessRole
     * @param      $rootNamespace
     * @param      $component
     * @param      $controller
     * @param bool $redirect
     *
     * @throws \Exception
     *
     * @return bool
     */
    public function authorizeRoute($accessRole, $rootNamespace, $component, $controller, $redirect = true)
    {
        $roles = Registry::getConfigClass()->get('roles');
        $redirections = Registry::getConfigClass()->get('redirections');

        if (!isset($roles[$accessRole])) {
            // e.g. Public page is requested
            return false;
        }

        if (isset($roles[$accessRole]['user'])) {
            $userModel = $roles[$accessRole]['user'];
            $model = "{$rootNamespace}\\components\\{$component}\\models\\{$userModel}";
        } else {
            $model = "{$rootNamespace}\\components\\{$component}\\models\\{$controller}";
            $userModel = $controller;
        }

        $user = new $model();
        if (!$user instanceof User) {
            throw new \Exception('Invalid User');
        }

        $userAuthentication = new UserAuthentication();
        if ($roles[$accessRole]['destination'] === 'private') {
            $result = $userAuthentication->isPrivateDestinationAccessible($user);

            if ($redirect === true && $result !== true) {
                // e.g. only logged in users should access private destination
                header('Location: '.$redirections[$userModel]['login']);
                exit;
            } else {
                return $result;
            }
        } else {
            if ($userAuthentication->isLoggedIn($user, true) === true) {
                // e.g. User is already logged in, and tries to login in login page
                if ((new Request())->isAJAX()) {
                    $output = new Output();
                    $output->setSuccess(true);
                    $output->setMessage('Already logged in');
                    if (isset($redirections[$userModel]) && isset($redirections[$userModel]['default'])) {
                        $output->setRedirectTo($redirections[$userModel]['default']);
                    }

                    (new Response())->echoContent($output->toJSON());
                } else {
                    if (isset($redirections[$userModel]) && isset($redirections[$userModel]['default'])) {
                        header('Location: '.$redirections[$userModel]['default']);
                    }
                    exit;
                }
            }

            $result = $userAuthentication->isGuestDestinationAccessible($user);

            if ($redirect === true && $result !== true) {
                // e.g. Login page should not be accessible by logged in users
                header('Location: '.$redirections[$userModel]['default']);
                exit;
            } else {
                return $result;
            }
        }
    }

    public function getRoutes()
    {
        return (new Routes())->routes;
    }
}
