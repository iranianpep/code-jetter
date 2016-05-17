<?php
/**
 * Created by PhpStorm.
 * User: ehsanabbasi
 * Date: 12/11/15
 * Time: 8:00 AM
 */

    // this is to fix Cannot send session cookie - headers already sent
    @session_start();

class BaseModelTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @runInSeparateProcess
     */
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
}
