<?php

namespace CodeJetter\core\utility;

use DateTime;

/**
 * Class DateTimeUtility.
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

        return $sign.str_pad($hour, 2, '0', STR_PAD_LEFT).':'.str_pad($minutes, 2, '0');
    }

    /**
     * Return date difference in full hours.
     *
     * @param $startDate
     * @param $endDate
     *
     * @return int
     */
    public function diffInFullHours($startDate, $endDate)
    {
        $diff = (new DateTime($startDate))->diff(new DateTime($endDate));

        return $diff->h + ($diff->days * 24);
    }

    /**
     * Return date difference in full minutes.
     *
     * @param $startDate
     * @param $endDate
     *
     * @return int
     */
    public function diffInFullMinutes($startDate, $endDate)
    {
        $diff = (new DateTime($startDate))->diff(new DateTime($endDate));

        return $this->diffInFullHours($startDate, $endDate) * 60 + $diff->i;
    }

    /**
     * Return date difference in full days - If start date is earlier than end date it returns positive.
     *
     * @param $startDate
     * @param $endDate
     *
     * @return int
     */
    public function diffInFullDays($startDate, $endDate)
    {
        $diff = (new DateTime($startDate))->diff(new DateTime($endDate));

        return (int) $diff->format('%r%a');
    }
}
