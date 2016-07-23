<?php

namespace CodeJetter\components\user;

use CodeJetter\core\BaseConfig;

class GeolocationConfig extends BaseConfig
{
    protected static $configs = [
        'defaultCountryCode' => 'AU',
        'defaultCity' => 'Melbourne'
    ];
}
