<?php

    namespace CodeJetter\tests;

    use CodeJetter\core\utility\StringUtility;

    class UtilityTest extends \PHPUnit_Framework_TestCase
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
    }
