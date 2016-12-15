<?php

namespace CodeJetter\components\geolocation;

use CodeJetter\core\BaseConfig;

class GeolocationConfig extends BaseConfig
{
    protected static $configs = [
        'defaultCountryCode' => 'AU',
        'defaultCity'        => 'Melbourne',
    ];
}
