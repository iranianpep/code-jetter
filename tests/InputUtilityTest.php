<?php

namespace CodeJetter\tests;

use CodeJetter\core\io\DatabaseInput;
use CodeJetter\core\security\ValidatorRule;
use CodeJetter\core\utility\InputUtility;

class InputUtilityTest extends \PHPUnit_Framework_TestCase
{
    public function testGetFieldsValues()
    {
        $utility = new InputUtility();

        $requiredRule = new ValidatorRule('required');
        $emailRule = new ValidatorRule('email');

        $ios = [
            [
                'i1' => [
                    'name' => 'test',
                    'email' => 'test@test.com',
                    'message' => 'test message'
                ],
                'i2' => [
                    new DatabaseInput('name'),
                    new DatabaseInput('email', [$requiredRule, $emailRule]),
                    new DatabaseInput('message', [$requiredRule])
                ],
                'i3' => 'add',
                'o' => [
                    'name' => [
                        'column' => 'name',
                        'value' => 'test',
                        'type' => null,
                        'bind' => null
                    ],
                    'email' => [
                        'column' => 'email',
                        'value' => 'test@test.com',
                        'type' => null,
                        'bind' => null
                    ],
                    'message' => [
                        'column' => 'message',
                        'value' => 'test message',
                        'type' => null,
                        'bind' => null
                    ]
                ]
            ],
            [
                'i1' => [
                    'email' => 'test@test.com',
                    'message' => 'test message'
                ],
                'i2' => [
                    new DatabaseInput('name'),
                    new DatabaseInput('email', [$requiredRule, $emailRule]),
                    new DatabaseInput('message', [$requiredRule])
                ],
                'i3' => 'update',
                'o' => [
                    'email' => [
                        'column' => 'email',
                        'value' => 'test@test.com',
                        'type' => null,
                        'bind' => null
                    ],
                    'message' => [
                        'column' => 'message',
                        'value' => 'test message',
                        'type' => null,
                        'bind' => null
                    ]
                ]
            ],
            [
                'i1' => [
                    'email' => 'test@test.com',
                    'message' => 'test message'
                ],
                'i2' => [
                    new DatabaseInput('name'),
                    new DatabaseInput('email', [$requiredRule, $emailRule]),
                    new DatabaseInput('message', [$requiredRule])
                ],
                'i3' => 'add',
                'o' => [
                    'name' => [
                        'column' => 'name',
                        'value' => '',
                        'type' => null,
                        'bind' => null
                    ],
                    'email' => [
                        'column' => 'email',
                        'value' => 'test@test.com',
                        'type' => null,
                        'bind' => null
                    ],
                    'message' => [
                        'column' => 'message',
                        'value' => 'test message',
                        'type' => null,
                        'bind' => null
                    ]
                ]
            ],
        ];

        foreach ($ios as $io) {
            $this->assertEquals($io['o'], $utility->getFieldsValues($io['i1'], $io['i2'], $io['i3']));
        }
    }
}
