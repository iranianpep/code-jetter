<?php

namespace CodeJetter\components\user;

use CodeJetter\core\BaseConfig;

class UserConfig extends BaseConfig
{
    protected static $configs = [
        'testUser' => 'testUser',
        'personalizedMenus' => [
            'member' => 'memberPersonalizedMenu.php',
            'admin' => 'adminPersonalizedMenu.php'
        ]
    ];
}
