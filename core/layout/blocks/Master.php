<?php

namespace CodeJetter\core\layout\blocks;

use CodeJetter\components\user\models\User;
use CodeJetter\core\Registry;

/**
 * Class Master
 * @package CodeJetter\core\layout\blocks
 */
class Master extends BaseBlock
{
    /**
     * @return Header
     */
    public function getHeader()
    {
        return $this->getView()->getHeader();
    }

    /**
     * @return Footer
     */
    public function getFooter()
    {
        return $this->getView()->getFooter();
    }

    /**
     * @return string
     */
    public function getGlobalJSConfiguration()
    {
        $configuration = Registry::getConfigClass()->get('globalJSConfiguration');
        $configuration = json_encode($configuration);
        return "<script type='text/javascript'>var globalConfig = {$configuration}</script>";
    }

    /**
     * @return string
     */
    public function getSessionTimeoutChecker()
    {
        $script = '';

        $loggedInUser = $this->getView()->getCurrentLoggedIn();
        if (!empty($loggedInUser) && $loggedInUser instanceof User) {
            $userModel = $loggedInUser->getClassNameFromNamespace();

            // stuff for registered users
            $script .= "<script type='text/javascript'>

            // update dom loading on page load for this user model
            var domLoading = window.performance.timing.domLoading;
            var userModel = '{$userModel}';

            loggedIn = localStorage.getItem('loggedIn');

            // if loggedIn is already defined, append to it
            if (typeof loggedIn != 'undefined' && loggedIn !== null) {
                loggedIn = JSON.parse(loggedIn);
            } else {
                var loggedIn = {};
            }

            loggedIn[userModel] = domLoading;

            localStorage.setItem('loggedIn', JSON.stringify(loggedIn));

            // check user idle time every minute
            setInterval(function() {checkSessionTimeout(userModel)}, globalConfig.sessionTimeoutCheckerInterval * 1000);
            </script>";
        }

        return $script;
    }
}
