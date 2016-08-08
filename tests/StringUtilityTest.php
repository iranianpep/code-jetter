<?php

namespace CodeJetter\tests;

use CodeJetter\core\utility\StringUtility;

class StringUtilityTest extends \PHPUnit_Framework_TestCase
{
    public function testGetClassNameFromNamespace()
    {
        $utility = new StringUtility();

        $inputOutputs = [
            [
                'input' => 'CodeJetter\test\UtilityTest',
                'output' => 'UtilityTest'
            ],
            [
                'input' => 'CodeJetter\core\App',
                'output' => 'App'
            ],
            [
                'input' => 'CodeJetter\core\database\BaseDatabase',
                'output' => 'BaseDatabase'
            ],
            [
                'input' => 'CodeJetter\core\database\\',
                'output' => ''
            ],
            [
                'input' => '',
                'output' => ''
            ]
        ];

        // this is for empty input
        $this->setExpectedException('Exception');

        foreach ($inputOutputs as $inputOutput) {
            $this->assertEquals($inputOutput['output'], $utility->getClassNameFromNamespace($inputOutput['input']));
        }
    }

    public function testStringLastReplace()
    {
        $utility = new StringUtility();

        $inputOutputs = [
            [
                'input' => 'AdminUser',
                'search' => 'User',
                'replace' => '',
                'output' => 'Admin'
            ],
            [
                'input' => 'AdminUser',
                'search' => 'user',
                'replace' => '',
                'output' => false
            ],
            [
                'input' => 'AdminUserUser',
                'search' => 'User',
                'replace' => '',
                'output' => 'AdminUser'
            ],
            [
                'input' => 'AdminUserLast',
                'search' => 'User',
                'replace' => '',
                'output' => 'AdminLast'
            ],
            [
                'input' => 'AdminUserLastUser',
                'search' => 'User',
                'replace' => '',
                'output' => 'AdminUserLast'
            ],
            [
                'input' => 'AdminUserLastUser',
                'search' => 'User',
                'replace' => 'User',
                'output' => 'AdminUserLastUser'
            ],
        ];

        foreach ($inputOutputs as $inputOutput) {
            $this->assertEquals($inputOutput['output'], $utility->stringLastReplace($inputOutput['search'], $inputOutput['replace'], $inputOutput['input']));
        }
    }

    public function testCamelCaseToSnakeCase()
    {
        $utility = new StringUtility();

        $inputOutputs = [
            [
                'input' => 'AdminUser',
                'output' => 'Admin_User'
            ],
            [
                'input' => 'GroupUserXref',
                'output' => 'Group_User_Xref'
            ],
            [
                'input' => 'GroupMemberUserXref',
                'output' => 'Group_Member_User_Xref'
            ],
            [
                'input' => 'HttpRequests',
                'output' => 'Http_Requests'
            ]
        ];

        foreach ($inputOutputs as $inputOutput) {
            $this->assertEquals($inputOutput['output'], $utility->camelCaseToSnakeCase($inputOutput['input']));
        }
    }

    public function testRemoveURLProtocol()
    {
        $utility = new StringUtility();

        $inputOutputs = [
            [
                'input' => 'www.example.com',
                'output' => 'example.com'
            ],
            [
                'input' => 'http://example.com',
                'output' => 'example.com'
            ],
            [
                'input' => 'http://www.example.com',
                'output' => 'example.com'
            ],
            [
                'input' => 'http://www.example.com/test',
                'output' => 'example.com/test'
            ],
            [
                'input' => 'http://test.example.com/test?id=1',
                'output' => 'test.example.com/test?id=1'
            ],
            [
                'input' => 'http://test.example.com?id=1',
                'output' => 'test.example.com?id=1'
            ],
        ];

        foreach ($inputOutputs as $inputOutput) {
            $this->assertEquals($inputOutput['output'], $utility->removeURLProtocol($inputOutput['input']));
        }
    }

    public function testJsonToArray()
    {
        $utility = new StringUtility();

        $inputOutputs = [
            [
                'input' => '',
                'output' => []
            ],
            [
                'input' => '0',
                'output' => []
            ],
            [
                'input' => 0,
                'output' => []
            ],
            [
                'input' => '{"foo-bar": 12345}',
                'output' => [
                    'foo-bar' => 12345
                ]
            ],
            [
                // this is an invalid json
                'input' => '{bar:"baz"}',
                'output' => []
            ],
            [
                // this is an invalid json
                'input' => "{'bar':'baz'}",
                'output' => []
            ],
            [
                'input' => '{"foo-bar": 12345,}',
                'output' => []
            ],
            [
                'input' => '{"a":1,"b":2,"c":3,"d":4,"e":5}',
                'output' => [
                    "a" => 1,
                    "b" => 2,
                    "c" => 3,
                    "d" => 4,
                    "e" => 5
                ]
            ],
        ];

        foreach ($inputOutputs as $inputOutput) {
            try {
                $this->assertEquals($inputOutput['output'], $utility->jsonToArray($inputOutput['input']));
            } catch (\Exception $e) {
                $this->assertEquals('Invalid JSON content', $e->getMessage());
            }
        }
    }
}
