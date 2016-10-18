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

    public function testSnakeCaseToCamelCase()
    {
        $utility = new StringUtility();

        $inputOutputs = [
            [
                'input' => 'admin_user',
                'output' => 'AdminUser'
            ],
            [
                'input' => 'member_group',
                'output' => 'MemberGroup'
            ],
            [
                'input' => 'group_member_user_xrefs',
                'output' => 'GroupMemberUserXrefs'
            ],
        ];

        foreach ($inputOutputs as $inputOutput) {
            $this->assertEquals($inputOutput['output'], $utility->snakeCaseToCamelCase($inputOutput['input']));
        }
    }

    public function testSingularToPlural()
    {
        $utility = new StringUtility();

        $inputOutputs = [
            [
                'input' => 'user',
                'output' => 'users'
            ],
            [
                'input' => 'party',
                'output' => 'parties'
            ],
            [
                'input' => 'bottle',
                'output' => 'bottles'
            ],
            [
                'input' => 'box',
                'output' => 'boxes'
            ],
            [
                'input' => 'watch',
                'output' => 'watches'
            ],
            [
                'input' => 'moss',
                'output' => 'mosses'
            ],
            [
                'input' => 'bus',
                'output' => 'buses'
            ],
            [
                'input' => 'wolf',
                'output' => 'wolves'
            ],
            [
                'input' => 'wife',
                'output' => 'wives'
            ],
            [
                'input' => 'leaf',
                'output' => 'leaves'
            ],
            [
                'input' => 'life',
                'output' => 'lives'
            ],
            [
                'input' => 'child',
                'output' => 'children'
            ],
            [
                'input' => 'woman',
                'output' => 'women'
            ],
            [
                'input' => 'man',
                'output' => 'men'
            ],
            [
                'input' => 'mouse',
                'output' => 'mice'
            ],
            [
                'input' => 'goose',
                'output' => 'geese'
            ],
            [
                'input' => 'baby',
                'output' => 'babies'
            ],
            [
                'input' => 'toy',
                'output' => 'toys'
            ],
            [
                'input' => 'kidney',
                'output' => 'kidneys'
            ],
            [
                'input' => 'potato',
                'output' => 'potatoes'
            ],
            [
                'input' => 'memo',
                'output' => 'memos'
            ],
            [
                'input' => 'stereo',
                'output' => 'stereos'
            ],
            [
                'input' => 'sheep',
                'output' => 'sheep'
            ],
            [
                'input' => 'deer',
                'output' => 'deer'
            ],
            [
                'input' => 'series',
                'output' => 'series'
            ],
            [
                'input' => 'species',
                'output' => 'species'
            ],
            [
                'input' => 'business',
                'output' => 'businesses'
            ],
        ];

        foreach ($inputOutputs as $inputOutput) {
            $this->assertEquals($inputOutput['output'], $utility->singularToPlural($inputOutput['input']));
        }
    }

    public function testPluralToSingular()
    {
        $utility = new StringUtility();

        $inputOutputs = [
            [
                'output' => 'user',
                'input' => 'users'
            ],
            [
                'output' => 'party',
                'input' => 'parties'
            ],
            [
                'output' => 'bottle',
                'input' => 'bottles'
            ],
            [
                'output' => 'box',
                'input' => 'boxes'
            ],
            [
                'output' => 'watch',
                'input' => 'watches'
            ],
            [
                'output' => 'moss',
                'input' => 'mosses'
            ],
            [
                'output' => 'bus',
                'input' => 'buses'
            ],
            [
                'output' => 'wolf',
                'input' => 'wolves'
            ],
            [
                'output' => 'wife',
                'input' => 'wives'
            ],
            [
                'output' => 'leaf',
                'input' => 'leaves'
            ],
            [
                'output' => 'life',
                'input' => 'lives'
            ],
            [
                'output' => 'child',
                'input' => 'children'
            ],
            [
                'output' => 'woman',
                'input' => 'women'
            ],
            [
                'output' => 'man',
                'input' => 'men'
            ],
            [
                'output' => 'mouse',
                'input' => 'mice'
            ],
            [
                'output' => 'goose',
                'input' => 'geese'
            ],
            [
                'output' => 'baby',
                'input' => 'babies'
            ],
            [
                'output' => 'toy',
                'input' => 'toys'
            ],
            [
                'output' => 'kidney',
                'input' => 'kidneys'
            ],
            [
                'output' => 'potato',
                'input' => 'potatoes'
            ],
            [
                'output' => 'memo',
                'input' => 'memos'
            ],
            [
                'output' => 'stereo',
                'input' => 'stereos'
            ],
            [
                'output' => 'sheep',
                'input' => 'sheep'
            ],
            [
                'output' => 'deer',
                'input' => 'deer'
            ],
            [
                'output' => 'series',
                'input' => 'series'
            ],
            [
                'output' => 'species',
                'input' => 'species'
            ],
            [
                'output' => 'business',
                'input' => 'businesses'
            ],
        ];

        foreach ($inputOutputs as $inputOutput) {
            $this->assertEquals($inputOutput['output'], $utility->pluralToSingular($inputOutput['input']));
        }
    }

    public function testRemovePrefix()
    {
        $utility = new StringUtility();

        $inputOutputs = [
            [
                'output' => 'jobs',
                'input' => [
                    'text' => 'cj_jobs',
                    'prefix' => 'cj_'
                ]
            ],
            [
                'output' => 'cj_jobs',
                'input' => [
                    'text' => 'cj_jobs',
                    'prefix' => ''
                ]
            ],
            [
                'output' => '',
                'input' => [
                    'text' => 'cj_jobs',
                    'prefix' => 'cj_jobs'
                ]
            ],
            [
                'output' => 'cj_jobs',
                'input' => [
                    'text' => 'cj_jobs',
                    'prefix' => 'cj_jobsv'
                ]
            ],
            [
                'output' => 'string_bla_bla_bla',
                'input' => [
                    'text' => 'bla_string_bla_bla_bla',
                    'prefix' => 'bla_'
                ]
            ],
        ];

        foreach ($inputOutputs as $inputOutput) {
            $this->assertEquals($inputOutput['output'], $utility->removePrefix($inputOutput['input']['text'], $inputOutput['input']['prefix']));
        }
    }

    public function testRemoveSuffix()
    {
        $utility = new StringUtility();

        $inputOutputs = [
            [
                'output' => 'cj_jobs',
                'input' => [
                    'text' => 'cj_jobs_xyz',
                    'suffix' => '_xyz'
                ]
            ],
            [
                'output' => 'c',
                'input' => [
                    'text' => 'cj_jobs_xyz',
                    'suffix' => 'j_jobs_xyz'
                ]
            ],
            [
                'output' => '',
                'input' => [
                    'text' => 'cj_jobs_xyz',
                    'suffix' => 'cj_jobs_xyz'
                ]
            ],
            [
                'output' => 'cj_jobs_xyz',
                'input' => [
                    'text' => 'cj_jobs_xyz',
                    'suffix' => 'bcj_jobs_xyz'
                ]
            ],
        ];

        foreach ($inputOutputs as $inputOutput) {
            $this->assertEquals($inputOutput['output'], $utility->removeSuffix($inputOutput['input']['text'], $inputOutput['input']['suffix']));
        }
    }
}
