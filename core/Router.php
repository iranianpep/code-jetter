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

/**
 * Class Router
 * @package CodeJetter\core
 */
class Router
{
    /**
     * Only last variable in regex can be optional
     *
     * @var array
     */
    private $routes = [
        /**
         * Simple
         */
        'simple' => [
            'GET' => [
                '/' => [
                    'component' => 'page',
                    'controller' => 'Page'
                ],
                '/contact' => [
                    'component' => 'contact',
                    'controller' => 'Contact'
                ],
                '/register' => [
                    'component' => 'user',
                    'controller' => 'MemberUser',
                    'action' => 'registerForm',
                    'accessRole' => 'guest'
                ],
                '/login' => [
                    'component' => 'user',
                    'controller' => 'MemberUser',
                    'action' => 'loginForm',
                    'accessRole' => 'guest'
                ],
                '/admin/login' => [
                    'component' => 'user',
                    'controller' => 'AdminUser',
                    'action' => 'loginForm',
                    'accessRole' => 'guest'
                ],
                '/admin/members' => [
                    'component' => 'user',
                    'controller' => 'AdminUser',
                    'action' => 'listMembers'
                ],
                '/admin/groups' => [
                    'component' => 'user',
                    'controller' => 'MemberGroup',
                    'action' => 'listGroups'
                ],
                '/admin/contact/messages' => [
                    'component' => 'contact',
                    'controller' => 'Contact',
                    'action' => 'listMessages'
                ],
                '/admin/profile' => [
                    'component' => 'user',
                    'controller' => 'AdminUser',
                    'action' => 'profileForm'
                ],
                '/account/profile' => [
                    'component' => 'user',
                    'controller' => 'MemberUser',
                    'action' => 'profileForm'
                ],
                '/logout' => [
                    'component' => 'user',
                    'controller' => 'MemberUser',
                    'action' => 'logout'
                ],
                '/admin/logout' => [
                    'component' => 'user',
                    'controller' => 'AdminUser',
                    'action' => 'logout'
                ],
                '/account/members' => [
                    'component' => 'user',
                    'controller' => 'MemberUser',
                    'action' => 'listUsers'
                ],
                '/reset-password' => [
                    'component' => 'user',
                    'controller' => 'MemberUser',
                    'action' => 'resetPasswordForm',
                    'accessRole' => 'guest'
                ],
                '/forgot-password' => [
                    'component' => 'user',
                    'controller' => 'MemberUser',
                    'action' => 'forgotPasswordForm',
                    'accessRole' => 'guest'
                ],
                '/admin/forgot-password' => [
                    'component' => 'user',
                    'controller' => 'AdminUser',
                    'action' => 'forgotPasswordForm',
                    'accessRole' => 'guest'
                ],
            ],
            'POST' => [
                '/register' => [
                    'component' => 'user',
                    'controller' => 'MemberUser',
                    'action' => 'register',
                    'accessRole' => 'guest'
                ],
                '/contact/new' => [
                    'component' => 'contact',
                    'controller' => 'Contact',
                    'action' => 'newMessage'
                ],
                '/login' => [
                    'component' => 'user',
                    'controller' => 'MemberUser',
                    'action' => 'login',
                    'accessRole' => 'guest'
                ],
                '/admin/login' => [
                    'component' => 'user',
                    'controller' => 'AdminUser',
                    'action' => 'login',
                    'accessRole' => 'guest'
                ],
                '/admin/add-member' => [
                    'component' => 'user',
                    'controller' => 'AdminUser',
                    'action' => 'addMember'
                ],
                '/admin/add-group-member' => [
                    'component' => 'user',
                    'controller' => 'MemberGroup',
                    'action' => 'addGroup'
                ],
                '/admin/update-group-member' => [
                    'component' => 'user',
                    'controller' => 'MemberGroup',
                    'action' => 'updateGroup'
                ],
                '/admin/safe-delete-group-member' => [
                    'component' => 'user',
                    'controller' => 'MemberGroup',
                    'action' => 'safeDeleteGroup'
                ],
                '/admin/safe-batch-delete-group-member' => [
                    'component' => 'user',
                    'controller' => 'MemberGroup',
                    'action' => 'safeBatchDeleteGroup'
                ],
                '/admin/delete-group-member' => [
                    'component' => 'user',
                    'controller' => 'MemberGroup',
                    'action' => 'deleteGroup'
                ],
                '/admin/batch-delete-group-member' => [
                    'component' => 'user',
                    'controller' => 'MemberGroup',
                    'action' => 'batchDeleteGroup'
                ],
                '/admin/contact/safe-delete-message' => [
                    'component' => 'contact',
                    'controller' => 'Contact',
                    'action' => 'safeDeleteMessage'
                ],
                '/admin/contact/safe-batch-delete-message' => [
                    'component' => 'contact',
                    'controller' => 'Contact',
                    'action' => 'safeBatchDeleteMessage'
                ],
                '/admin/contact/delete-message' => [
                    'component' => 'contact',
                    'controller' => 'Contact',
                    'action' => 'deleteMessage'
                ],
                '/admin/contact/batch-delete-message' => [
                    'component' => 'contact',
                    'controller' => 'Contact',
                    'action' => 'batchDeleteMessage'
                ],
                '/account/notify' => [
                    'component' => 'user',
                    'controller' => 'MemberUser',
                    'action' => 'notify'
                ],
                '/account/delete-child' => [
                    'component' => 'user',
                    'controller' => 'MemberUser',
                    'action' => 'safeDeleteChild'
                ],
                '/account/batch-delete-child' => [
                    'component' => 'user',
                    'controller' => 'MemberUser',
                    'action' => 'safeBatchDeleteChild'
                ],
                '/admin/delete-member' => [
                    'component' => 'user',
                    'controller' => 'AdminUser',
                    'action' => 'deleteMember'
                ],
                '/admin/batch-delete-member' => [
                    'component' => 'user',
                    'controller' => 'AdminUser',
                    'action' => 'batchDeleteMember'
                ],
                '/admin/safe-delete-member' => [
                    'component' => 'user',
                    'controller' => 'AdminUser',
                    'action' => 'safeDeleteMember'
                ],
                '/admin/safe-batch-delete-member' => [
                    'component' => 'user',
                    'controller' => 'AdminUser',
                    'action' => 'safeBatchDeleteMember'
                ],
                '/account/update-child' => [
                    'component' => 'user',
                    'controller' => 'MemberUser',
                    'action' => 'updateChild'
                ],
                '/admin/update-member' => [
                    'component' => 'user',
                    'controller' => 'AdminUser',
                    'action' => 'updateMember'
                ],
                '/account/add-child' => [
                    'component' => 'user',
                    'controller' => 'MemberUser',
                    'action' => 'addChild'
                ],
                '/reset-password' => [
                    'component' => 'user',
                    'controller' => 'MemberUser',
                    'action' => 'resetPassword',
                    'accessRole' => 'guest'
                ],
                '/admin/reset-password' => [
                    'component' => 'user',
                    'controller' => 'AdminUser',
                    'action' => 'resetPassword',
                    'accessRole' => 'guest'
                ],
                '/forgot-password' => [
                    'component' => 'user',
                    'controller' => 'MemberUser',
                    'action' => 'forgotPassword',
                    'accessRole' => 'guest'
                ],
                '/admin/forgot-password' => [
                    'component' => 'user',
                    'controller' => 'AdminUser',
                    'action' => 'forgotPassword',
                    'accessRole' => 'guest'
                ],
                '/account/update-profile' => [
                    'component' => 'user',
                    'controller' => 'MemberUser',
                    'action' => 'updateProfile'
                ],
                '/admin/update-profile' => [
                    'component' => 'user',
                    'controller' => 'AdminUser',
                    'action' => 'updateProfile'
                ]
            ]
        ],
        /**
         * Regex
         */
        'regex' => [
            'GET' => [
                '/account/members/page/{page:int}/limit/{limit:int:?}' => [
                    'component' => 'user',
                    'controller' => 'MemberUser',
                    'action' => 'listUsers',
                    'base' => '/account/members'
                ],
                '/admin/members/page/{page:int}/limit/{limit:int:?}' => [
                    'component' => 'user',
                    'controller' => 'AdminUser',
                    'action' => 'listMembers',
                    'base' => '/admin/members'
                ],
                '/admin/groups/page/{page:int}/limit/{limit:int:?}' => [
                    'component' => 'user',
                    'controller' => 'MemberGroup',
                    'action' => 'listGroups',
                    'base' => '/admin/groups'
                ],
                '/admin/contact/messages/page/{page:int}/limit/{limit:int:?}' => [
                    'component' => 'contact',
                    'controller' => 'Contact',
                    'action' => 'listMessages',
                    'base' => '/admin/contact/messages'
                ],
                // TODO think about type of parameter: currently is any
                '/reset-password/email/{email:any}/token/{token:any}' => [
                    'component' => 'user',
                    'controller' => 'MemberUser',
                    'action' => 'resetPasswordForm',
                    'base' => '/reset-password',
                    'accessRole' => 'guest'
                ],
                '/admin/reset-password/email/{email:any}/token/{token:any}' => [
                    'component' => 'user',
                    'controller' => 'AdminUser',
                    'action' => 'resetPasswordForm',
                    'base' => '/admin/reset-password',
                    'accessRole' => 'guest'
                ],
                '/admin/member/{id:int}' => [
                    'component' => 'user',
                    'controller' => 'AdminUser',
                    'action' => 'viewMember'
                ],
                '/account/member/{id:int}' => [
                    'component' => 'user',
                    'controller' => 'MemberUser',
                    'action' => 'viewChild'
                ]
            ],
            'POST' => [
//                '/reset-password/email/{email:any}/token/{token:any}' => [
//                    'component' => 'user',
//                    'controller' => 'MemberUser',
//                    'action' => 'resetPassword',
//                    'base' => '/reset-password',
//                    'accessRole' => 'guest'
//                ],
            ]
        ]
    ];

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
     * convert pattern to regex and return match ones
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
         * start forming the regex pattern
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
                $regexPattern .= '/' . $patternPiece;
            }
        }

        $regexPattern .= "\${$regexDelimiter}";
        /**
         * finish forming the regex pattern
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
     * Return route info and parameters by URL path and request method
     *
     * @param $URLPath
     * @param $requestMethod
     *
     * @return RouteInfo
     */
    public function getRouteInfo($URLPath, $requestMethod)
    {
        if (empty($requestMethod)) {
            return false;
        }

        if (empty($URLPath) || trim($URLPath) === '' || $URLPath === '/index.php') {
            $URLPath = '/';
        }

        if (isset($this->routes['simple'][$requestMethod]) &&
            isset($this->routes['simple'][$requestMethod][$URLPath])) {
            // get route info straightway
            $url = $URLPath;
            $result = ['info' => $this->routes['simple'][$requestMethod][$URLPath]];
        } else {
            // look for regex
            $regexRoutes = $this->routes['regex'][$requestMethod];

            if (!empty($regexRoutes)) {
                foreach ($regexRoutes as $routePattern => $routeInfo) {
                    // find the match and return parameters if there is any
                    $found = $this->regexMatch($routePattern, $URLPath);

                    if (!empty($found)) {
                        $url = $routePattern;
                        $result = ['info' => $routeInfo, 'parameters' => $found];
                        break;
                    }
                }
            }
        }

        /**
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
            : $this->getAccessRole($URLPath);

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
            $URLPath
        );

        if (isset($result['info']['checkAntiCSRFToken'])) {
            $routeInfoObject->setCheckAntiCSRFToken($result['info']['checkAntiCSRFToken']);
        } else {
            // checkAntiCSRFToken is NOT set, use default value based on request method
            switch($requestMethod) {
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
        /**
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
        $URLPath = $request->getURLPath();
        //$isAJAXRequest = $request->isAJAX();

        // get route info (component, controller, action, ..) by path and request method
        $routeInfo = $this->getRouteInfo($URLPath, $requestMethod);

        // TODO move this somewhere, maybe Response
        /**
         * For the time being, leave 404 handling here
         */
        if (empty($routeInfo)) {
            header("HTTP/1.0 404 Not Found");
            $page = new Page();
            $page->setTitle('404');

            $metaTag = (new MetaTag('robots', 'noindex, nofollow'));
            $page->addMetaTag($metaTag);

            $componentTemplate = new ComponentTemplate();
            $componentTemplate->setTemplatePath('404.php');

            (new View())->make(
                $page,
                [
                    '404' => $componentTemplate
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
         * Authorization - DO NOT REMOVE THIS:
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

        /**
         * Set last route info here, after making sure that user is authorized
         */
        $this->setLastRoute($routeInfo);

        /**
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
     * Return access role based on URL
     *
     * This is used in getRouteInfo and should NOT be public
     *
     * @param       $URLPath
     * @param array $roles
     *
     * @return int|mixed|string
     * @throws \Exception
     */
    private function getAccessRole($URLPath, $roles = [])
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
            uasort($baseURLs, function ($a, $b) {
                return strlen($b) - strlen($a);
            });
        }

        $foundRole = '';
        if (!empty($baseURLs)) {
            foreach ($baseURLs as $role => $baseURL) {
                // do the regex - if found it break it, otherwise return the default access role

                if (preg_match("#^{$baseURL}#", $URLPath) === 1) {
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
     * @return bool
     * @throws \Exception
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

        $user = new $model;
        if (!$user instanceof User) {
            throw new \Exception("Invalid User");
        }

        $userAuthentication = new UserAuthentication();
        if ($roles[$accessRole]['destination'] === 'private') {
            $result = $userAuthentication->isPrivateDestinationAccessible($user);

            if ($redirect === true && $result !== true) {
                // e.g. only logged in users should access private destination
                header('Location: ' . $redirections[$userModel]['login']);
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
                    $output->setRedirectTo($redirections[$userModel]['default']);

                    (new Response())->echoContent($output->toJSON());
                } else {
                    header('Location: ' . $redirections[$userModel]['default']);
                    exit;
                }
            }

            $result = $userAuthentication->isGuestDestinationAccessible($user);

            if ($redirect === true && $result !== true) {
                // e.g. Login page should not be accessible by logged in users
                header('Location: ' . $redirections[$userModel]['default']);
                exit;
            } else {
                return $result;
            }
        }
    }
}
