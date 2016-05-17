<?php

namespace CodeJetter\core\utility;

use DateTime;

/**
 * Class DateTimeUtility
 * @package CodeJetter\core\utility
 */
class DateTimeUtility
{
    /**
     * @return array
     */
    public function getTimeZones()
    {
        return \DateTimeZone::listIdentifiers(\DateTimeZone::ALL);
    }

    /**
     * @param $timeZone
     *
     * @return string
     */
    public function calculateTimeZoneOffset($timeZone)
    {
        $z = new \DateTimeZone($timeZone);
        $c = new DateTime(null, $z);
        $offset = $z->getOffset($c);

        $hours = $offset / 3600;
        $remainder = $offset % 3600;
        $sign = $hours > 0 ? '+' : '-';
        $hour = (int) abs($hours);
        $minutes = (int) abs($remainder / 60);

        if ($hour == 0 && $minutes == 0) {
            $sign = '+';
        }

        return $sign . str_pad($hour, 2, '0', STR_PAD_LEFT) . ':' . str_pad($minutes, 2, '0');
    }
}
