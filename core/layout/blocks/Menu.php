<?php
/**
 * Created by PhpStorm.
 * User: ehsanabbasi
 * Date: 24/04/15
 * Time: 7:26 PM
 */

namespace CodeJetter\core\layout\blocks;

use CodeJetter\components\user\models\User;
use CodeJetter\components\user\services\UserAuthentication;
use CodeJetter\core\Registry;
use CodeJetter\core\RouteInfo;
use CodeJetter\core\utility\StringUtility;

/**
 * Class Menu
 * @package CodeJetter\core\layout\blocks
 */
class Menu extends BaseBlock
{
    private $personalizedMenu;

    /**
     * This is used for different type of users e.g. member, admin, ...
     * @return Menu
     */
    public function getPersonalizedMenu()
    {
        $routeInfo = Registry::getRouterClass()->getLastRoute();

        if (!$routeInfo instanceof RouteInfo) {
            return false;
        }

        /**
         * If there is any access role for the route, it's already checked in route() that the right user is logged in
         */
        $personalizedMenu = new Menu($this->getView());

        $personalizedMenus = Registry::getConfigClass()->get('personalizedMenus', 'user');
        if (array_key_exists($routeInfo->getAccessRole(), $personalizedMenus)) {
            $personalizedMenu->setTemplateName($personalizedMenus[$routeInfo->getAccessRole()]);
        } else {
            // public

            $redirections = Registry::getConfigClass()->get('redirections');

            $loggedInUsers = (new UserAuthentication())->getLoggedIn();

            $usersDefaultLinks = [];
            if (!empty($loggedInUsers)) {
                // find the default page for them
                foreach ($loggedInUsers as $loggedInUser) {
                    if (!$loggedInUser instanceof User) {
                        continue;
                    }

                    $className = $loggedInUser->getClassNameFromNamespace();
                    $user = (new StringUtility())->stringLastReplace('User', '', $className);

                    if (isset($redirections[$className]['default'])) {
                        $usersDefaultLinks[$user] = $redirections[$className]['default'];
                    }
                }
            }

            if (!empty($usersDefaultLinks)) {
                $personalizedMenu->setData($usersDefaultLinks);
                $personalizedMenu->setTemplateName('listLoggedInUsers.php');
            } else {
                return false;
            }
        }

        return $personalizedMenu;
    }

    /**
     * @param string $personalizedMenu
     */
    public function setPersonalizedMenu($personalizedMenu)
    {
        $this->personalizedMenu = $personalizedMenu;
    }
}
