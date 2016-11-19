<?php

namespace CodeJetter\tests;

use CodeJetter\core\utility\ArrayUtility;

class ArrayUtilityTest extends \PHPUnit_Framework_TestCase
{
    public function testArrayComparison()
    {
        $utility = new ArrayUtility();

        $ios = [
            [
                'input' => [
                    '1' => [1, 2, 3],
                    '2' => [2, 3, 4]
                ],
                'output' => [
                    'toBeDeleted' => [1],
                    'toBeAdded' => [2 => 4]
                ]
            ],
            [
                'input' => [
                    '1' => [],
                    '2' => []
                ],
                'output' => [
                    'toBeDeleted' => [],
                    'toBeAdded' => []
                ]
            ],
            [
                'input' => [
                    '1' => [2],
                    '2' => []
                ],
                'output' => [
                    'toBeDeleted' => [2],
                    'toBeAdded' => []
                ]
            ],
            [
                'input' => [
                    '1' => [],
                    '2' => [1]
                ],
                'output' => [
                    'toBeDeleted' => [],
                    'toBeAdded' => [1]
                ]
            ]
        ];

        foreach ($ios as $io) {
            $this->assertEquals($io['output'], $utility->arrayComparison($io['input']['1'], $io['input']['2']));
        }
    }

    public function testFilter()
    {
        $utility = new ArrayUtility();

        $ios = [
            [
                'input' => [
                    'array' => [1, 2, 3],
                    'excluding' => [2, 3, 4],
                    'filterBy' => 'value'
                ],
                'output' => [1]
            ],
            [
                'input' => [
                    'array' => [1, 2, 3],
                    'excluding' => [],
                    'filterBy' => 'value'
                ],
                'output' => [1, 2, 3]
            ],
            [
                'input' => [
                    'array' => [1, 2, 3],
                    'excluding' => [1, 2, 3],
                    'filterBy' => 'value'
                ],
                'output' => []
            ],
            [
                'input' => [
                    'array' => [1, 2, 3],
                    'excluding' => [1, 2, 3],
                    'filterBy' => 'key'
                ],
                'output' => [1]
            ],
            [
                'input' => [
                    'array' => [1 => 2, 2 => 3, 3 => 4],
                    'excluding' => [1, 2, 3],
                    'filterBy' => 'key'
                ],
                'output' => []
            ],
        ];

        foreach ($ios as $io) {
            $this->assertEquals($io['output'], $utility->filter($io['input']['array'], $io['input']['excluding'], $io['input']['filterBy']));
        }
    }
}
