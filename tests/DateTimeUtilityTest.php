<?php

namespace CodeJetter\tests;

use CodeJetter\core\utility\DateTimeUtility;

class DateTimeUtilityTest extends \PHPUnit_Framework_TestCase
{
    public function testDiffInFullDays()
    {
        $utility = new DateTimeUtility();

        $ios = [
            [
                'i' => [
                    'start' => '2016-07-24 13:57:41',
                    'end'   => '2016-07-24 13:57:41',
                ],
                'o' => 0,
            ],
            [
                'i' => [
                    'start' => '2016-07-24 13:57:41',
                    'end'   => '2016-07-25 13:57:41',
                ],
                'o' => 1,
            ],
            [
                'i' => [
                    'start' => '2016-07-25 13:57:41',
                    'end'   => '2016-07-24 13:57:41',
                ],
                'o' => -1,
            ],
            [
                'i' => [
                    'start' => '2016-07-24 13:57:41',
                    'end'   => '2016-08-25 13:57:41',
                ],
                'o' => 32,
            ],
            [
                'i' => [
                    'start' => '2016/5/26',
                    'end'   => '1773/7/4',
                ],
                'o' => -88714,
            ],
            [
                'i' => [
                    'start' => '1773/7/4',
                    'end'   => '2016/5/26',
                ],
                'o' => 88714,
            ],
        ];

        foreach ($ios as $io) {
            $this->assertEquals($io['o'], $utility->diffInFullDays($io['i']['start'], $io['i']['end']));
        }
    }
}
