<?php

namespace CodeJetter\tests;

use CodeJetter\components\geolocation\mappers\StateMapper;

class BaseMapperTest extends \PHPUnit_Framework_TestCase
{
    public function testGetExcludeArchivedCriteria()
    {
        $stateMapper = new StateMapper();
        $criteria = $stateMapper->getExcludeArchivedCriteria();

        $expected = [
            [
            'column' => 'archivedAt',
            'operator' => 'IS NULL'
            ],
            [
                'column' => 'live',
                'value' => '1'
            ]
        ];

        $this->assertEquals($expected, $criteria);

        $criteria = $stateMapper->getExcludeArchivedCriteria('dummyTable');

        $expected = [
            [
                'column' => 'dummyTable.archivedAt',
                'operator' => 'IS NULL'
            ],
            [
                'column' => 'dummyTable.live',
                'value' => '1'
            ]
        ];

        $this->assertEquals($expected, $criteria);
    }
}
