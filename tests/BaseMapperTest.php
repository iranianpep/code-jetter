<?php

namespace CodeJetter\tests;

use CodeJetter\components\geolocation\mappers\StateMapper;
use CodeJetter\components\user\mappers\MemberUserMapper;
use CodeJetter\core\App;

// this is to fix Cannot send session cookie - headers already sent
@session_start();

class BaseMapperTest extends \PHPUnit_Framework_TestCase
{
    public function testGetExcludeArchivedCriteria()
    {
        $app = App::getInstance();
        $app->init('dev');

        $stateMapper = new StateMapper();
        $criteria = $stateMapper->getExcludeArchivedCriteria();

        $expected = [
            [
                'column' => '`archivedAt`',
                'operator' => 'IS NULL'
            ],
            [
                'column' => '`live`',
                'value' => '1'
            ]
        ];

        $this->assertEquals($expected, $criteria);

        $criteria = $stateMapper->getExcludeArchivedCriteria('dummyTable');

        $expected = [
            [
                'column' => '`dummyTable`.`archivedAt`',
                'operator' => 'IS NULL'
            ],
            [
                'column' => '`dummyTable`.`live`',
                'value' => '1'
            ]
        ];

        $this->assertEquals($expected, $criteria);
    }

    public function testGetTableNameByClassName()
    {
        $stateMapper = new StateMapper();

        $inputOutputs = [
            [
                'input' => 'job',
                'output' => 'jobs'
            ],
            [
                'input' => 'AdminUser',
                'output' => 'admin_users'
            ],
            [
                'input' => 'GroupUserXref',
                'output' => 'group_user_xrefs'
            ],
        ];

        foreach ($inputOutputs as $inputOutput) {
            $this->assertEquals($inputOutput['output'], $stateMapper->getTableNameByClassName($inputOutput['input']));
        }
    }

    public function testGetClassNameByTableName()
    {
        $stateMapper = new StateMapper();

        $inputOutputs = [
            [
                'input' => [
                    'name' => 'cj_jobs',
                    'namespace' => null,
                    'prefix' => 'cj_',
                    'suffix' => ''
                ],
                'output' => 'Job'
            ],
            [
                'input' => [
                    'name' => 'cj_group_member_user_xrefs',
                    'namespace' => 'CodeJetter\components\user\models',
                    'prefix' => 'cj_',
                    'suffix' => ''
                ],
                'output' => 'CodeJetter\components\user\models\GroupMemberUserXref'
            ],
        ];

        foreach ($inputOutputs as $inputOutput) {
            $this->assertEquals($inputOutput['output'], $stateMapper->getClassNameByTableName($inputOutput['input']['name'], $inputOutput['input']['namespace'], $inputOutput['input']['prefix'], $inputOutput['input']['suffix']));
        }
    }

    public function testGetMappersPath()
    {
        $app = App::getInstance();
        $app->init();

        $mapper = new MemberUserMapper();
        $path = $mapper->getMappersPath('user');

        $expected = 'CodeJetter/components/user/mappers/';

        $this->assertEquals($expected, $path);

        $path = $mapper->getMappersPath('user', false);

        $expected = 'CodeJetter/components/user/mappers';

        $this->assertEquals($expected, $path);
    }

    public function testGetMappersNamespace()
    {
        $app = App::getInstance();
        $app->init();

        $mapper = new MemberUserMapper();
        $namespacePath = $mapper->getMappersNamespace('user');

        $expected = 'CodeJetter\\components\\user\\mappers\\';

        $this->assertEquals($expected, $namespacePath);

        $namespacePath = $mapper->getMappersNamespace('user', false);

        $expected = 'CodeJetter\\components\\user\\mappers';

        $this->assertEquals($expected, $namespacePath);
    }
}
