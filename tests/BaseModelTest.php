<?php

namespace CodeJetter\tests;

use CodeJetter\components\user\models\MemberUser;
use CodeJetter\core\App;

// this is to fix Cannot send session cookie - headers already sent
    @session_start();

class BaseModelTest extends \PHPUnit_Framework_TestCase
{
    public function testGetMapperName()
    {
        $userModel = new \CodeJetter\components\user\models\MemberUser();
        $mapper = $userModel->getMapperName();

        $expected = 'CodeJetter\components\user\mappers\MemberUserMapper';

        $this->assertEquals($expected, $mapper);

        $mapper = $userModel->getMapperName(false, true);

        $expected = 'MemberUserMapper';

        $this->assertEquals($expected, $mapper);

        $userModel = new \CodeJetter\components\user\models\AdminUser();
        $mapper = $userModel->getMapperName();

        $expected = 'CodeJetter\components\user\mappers\AdminUserMapper';

        $this->assertEquals($expected, $mapper);

        $mapper = $userModel->getMapperName(false, true);

        $expected = 'AdminUserMapper';

        $this->assertEquals($expected, $mapper);
    }

    public function testGetModelsPath()
    {
        $app = App::getInstance();
        $app->init();

        $userModel = new MemberUser();
        $modelPath = $userModel->getModelsPath('user');

        $expected = 'CodeJetter/components/user/models/';

        $this->assertEquals($expected, $modelPath);

        $modelPath = $userModel->getModelsPath('user', false);

        $expected = 'CodeJetter/components/user/models';

        $this->assertEquals($expected, $modelPath);
    }

    public function testGetModelsNamespace()
    {
        $app = App::getInstance();
        $app->init();

        $userModel = new MemberUser();
        $namespacePath = $userModel->getModelsNamespace('user');

        $expected = 'CodeJetter\\components\\user\\models\\';

        $this->assertEquals($expected, $namespacePath);

        $namespacePath = $userModel->getModelsNamespace('user', false);

        $expected = 'CodeJetter\\components\\user\\models';

        $this->assertEquals($expected, $namespacePath);
    }
}
