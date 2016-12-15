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
                'column'   => '`archivedAt`',
                'operator' => 'IS NULL',
            ],
            [
                'column' => '`live`',
                'value'  => '1',
            ],
        ];

        $this->assertEquals($expected, $criteria);

        $criteria = $stateMapper->getExcludeArchivedCriteria('dummyTable');

        $expected = [
            [
                'column'   => '`dummyTable`.`archivedAt`',
                'operator' => 'IS NULL',
            ],
            [
                'column' => '`dummyTable`.`live`',
                'value'  => '1',
            ],
        ];

        $this->assertEquals($expected, $criteria);
    }

    public function testGetTableNameByClassName()
    {
        $stateMapper = new StateMapper();

        $inputOutputs = [
            [
                'input'  => 'job',
                'output' => 'jobs',
            ],
            [
                'input'  => 'AdminUser',
                'output' => 'admin_users',
            ],
            [
                'input'  => 'GroupUserXref',
                'output' => 'group_user_xrefs',
            ],
            [
                'input'  => null,
                'output' => 'states',
            ],
            [
                'input'  => '',
                'output' => 'states',
            ],
            [
                'input'  => ' ',
                'output' => 'states',
            ],
            [
                'input'  => 'StateMapper',
                'output' => 'states',
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
                    'name'      => 'cj_jobs',
                    'namespace' => null,
                    'prefix'    => 'cj_',
                    'suffix'    => '',
                ],
                'output' => 'Job',
            ],
            [
                'input' => [
                    'name'      => 'cj_group_member_user_xrefs',
                    'namespace' => 'CodeJetter\components\user\models',
                    'prefix'    => 'cj_',
                    'suffix'    => '',
                ],
                'output' => 'CodeJetter\components\user\models\GroupMemberUserXref',
            ],
            [
                'input' => [
                    'name'      => '',
                    'namespace' => null,
                    'prefix'    => 'cj_',
                    'suffix'    => '',
                ],
                'output' => 'State',
            ],
            [
                'input' => [
                    'name'      => null,
                    'namespace' => null,
                    'prefix'    => 'cj_',
                    'suffix'    => '',
                ],
                'output' => 'State',
            ],
            [
                'input' => [
                    'name'      => null,
                    'namespace' => null,
                    'prefix'    => null,
                    'suffix'    => null,
                ],
                'output' => 'State',
            ],
        ];

        foreach ($inputOutputs as $inputOutput) {
            $this->assertEquals($inputOutput['output'], $stateMapper->getClassNameByTableName($inputOutput['input']['name'], $inputOutput['input']['namespace'], $inputOutput['input']['prefix'], $inputOutput['input']['suffix']));
        }
    }

    public function testRemoveTablePrefixAndSuffix()
    {
        $stateMapper = new StateMapper();

        $inputOutputs = [
            [
                'input' => [
                    'name'   => 'CJ_JOBS',
                    'prefix' => 'CJ_',
                    'suffix' => '',
                ],
                'output' => 'JOBS',
            ],
            [
                'input' => [
                    'name'   => 'cj_jobs',
                    'prefix' => 'cj_',
                    'suffix' => '',
                ],
                'output' => 'jobs',
            ],
            [
                'input' => [
                    'name'   => 'cj_group_member_user_xrefs',
                    'prefix' => 'cj_',
                    'suffix' => '',
                ],
                'output' => 'group_member_user_xrefs',
            ],
            [
                'input' => [
                    'name'   => '',
                    'prefix' => 'cj_',
                    'suffix' => '',
                ],
                'output' => 'states',
            ],
            [
                'input' => [
                    'name'   => null,
                    'prefix' => 'cj_',
                    'suffix' => '',
                ],
                'output' => 'states',
            ],
            [
                'input' => [
                    'name'   => null,
                    'prefix' => null,
                    'suffix' => null,
                ],
                'output' => 'states',
            ],
        ];

        foreach ($inputOutputs as $inputOutput) {
            $this->assertEquals($inputOutput['output'], $stateMapper->removeTablePrefixAndSuffix($inputOutput['input']['name'], $inputOutput['input']['prefix'], $inputOutput['input']['suffix']));
        }
    }

    public function testGetTableAlias()
    {
        $stateMapper = new StateMapper();

        $inputOutputs = [
            [
                'input' => [
                    'name'   => 'CJ_JOBS',
                    'prefix' => 'CJ_',
                    'suffix' => '',
                ],
                'output' => 'jobs',
            ],
            [
                'input' => [
                    'name'   => 'cj_jobs',
                    'prefix' => 'cj_',
                    'suffix' => '',
                ],
                'output' => 'jobs',
            ],
            [
                'input' => [
                    'name'   => 'cj_group_member_user_xrefs',
                    'prefix' => 'cj_',
                    'suffix' => '',
                ],
                'output' => 'group_member_user_xrefs',
            ],
            [
                'input' => [
                    'name'   => '',
                    'prefix' => 'cj_',
                    'suffix' => '',
                ],
                'output' => 'states',
            ],
            [
                'input' => [
                    'name'   => null,
                    'prefix' => 'cj_',
                    'suffix' => '',
                ],
                'output' => 'states',
            ],
            [
                'input' => [
                    'name'   => null,
                    'prefix' => null,
                    'suffix' => null,
                ],
                'output' => 'states',
            ],
        ];

        foreach ($inputOutputs as $inputOutput) {
            $this->assertEquals($inputOutput['output'], $stateMapper->getTableAlias($inputOutput['input']['name'], $inputOutput['input']['prefix'], $inputOutput['input']['suffix']));
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

    public function testMapRowsToObjects()
    {
        $app = App::getInstance();
        $app->init();

        $mapper = new MemberUserMapper();
        $model = $mapper->getModelName();

        $dummyRows = [
            [
                'member.id' => 1,
            ],
            [
                'member.id' => 2,
            ],
        ];

        $tables = [
            'member' => [
                'name'  => 'cj_member_users',
                'class' => $model,
            ],
        ];

        $objects = $mapper->mapRowsToObjects($dummyRows, $tables);

        foreach ($objects as $index => $object) {
            $this->assertEquals($dummyRows[$index]['member.id'], $object['member']->getId());
        }

        $dummyRows = [
            [
                'member.id' => 1,
            ],
            [
                'member.id' => 2,
            ],
        ];

        $tables = [
            'member' => [
                'name'  => 'cj_member_users',
                'class' => $model,
            ],
        ];

        $objects = $mapper->mapRowsToObjects($dummyRows, $tables);

        foreach ($objects as $index => $object) {
            $this->assertEquals($dummyRows[$index]['member.id'], $object['member']->getId());
        }
    }

    public function testGetTableColumns()
    {
        $app = App::getInstance();
        $app->init('dev');

        $stateMapper = new StateMapper();

        $expected = [
            'id',
            'name',
            'abbr',
            'countryCode',
            'createdAt',
            'modifiedAt',
            'live',
            'archivedAt',
        ];

        $this->assertEquals($expected, $stateMapper->getTableColumns());
    }
}
