<?php

namespace CodeJetter\core;

use CodeJetter\components\user\services\UserAuthentication;

abstract class Base
{
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
}
