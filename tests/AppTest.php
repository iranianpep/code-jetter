<?php

    namespace CodeJetter\tests;

    use CodeJetter\core\App;
    use CodeJetter\core\Registry;

    // this is to fix Cannot send session cookie - headers already sent
    @session_start();

    class AppTest extends \PHPUnit_Framework_TestCase
    {
        public function testSetGetEnvironment()
        {
            $app = App::getInstance();

            $app->setEnvironment('dev');

            $this->assertEquals('dev', $app->getEnvironment());

            $app->setEnvironment('prod');

            $this->assertEquals('prod', $app->getEnvironment());
        }

        public function testGetSingletons()
        {
            $app = App::getInstance();
            $singletons = [
                'Config' => 'CodeJetter\core\Config',
                'Router' => 'CodeJetter\core\Router',
                'MySQL' => 'CodeJetter\core\database\MySQLDatabase',
                'Language' => 'CodeJetter\core\Language'
            ];

            $this->assertEquals($singletons, $app->getSingletons());
        }

        public function testGetRegistry()
        {
            $app = App::getInstance();
            $app->init('dev');

            $registry = $app->getRegistry();

            $this->assertEquals(Registry::getInstance(), $registry);
        }
    }
