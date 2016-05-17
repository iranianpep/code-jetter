<?php

    // this is to fix Cannot send session cookie - headers already sent
    @session_start();

    use CodeJetter\core\App;

class RegistryTest extends \PHPUnit_Framework_TestCase
{
    public function testAdd()
    {
        $app = App::getInstance();
        $app->setEnvironment('dev');
        $registry = \CodeJetter\core\Registry::getInstance();

        $registry::resetContainer();

        $config = new \CodeJetter\core\Config();

        $registry->add($config);
        $registry->add($config, 'config2');

        $classes = [
            'Config' => new \CodeJetter\core\Config(),
            'config2' => new \CodeJetter\core\Config()
        ];

        $this->assertEquals($classes, $registry->getClasses());
    }

    public function testGet()
    {
        $registry = \CodeJetter\core\Registry::getInstance();

        $registry::resetContainer();

        $config = new \CodeJetter\core\Config();
        $config->set('mapperSuffix', 'blah blah value');

        $registry->add($config);
        $registry->add($config, 'config2');

        $originalConfig = $registry->get('config2');

        $this->assertEquals('blah blah value', $originalConfig->get('mapperSuffix'));
    }

    public function testGetClassList()
    {
        $registry = \CodeJetter\core\Registry::getInstance();

        $registry::resetContainer();

        $config = new \CodeJetter\core\Config();

        $registry->add($config);
        $registry->add($config, 'config2');

        $classes = [
            'Config' => 'CodeJetter\core\Config',
            'config2' => 'CodeJetter\core\Config'
        ];

        $this->assertEquals($classes, $registry->getClassList());
    }

    public function testIsInContainer()
    {
        $registry = \CodeJetter\core\Registry::getInstance();

        $registry::resetContainer();

        $config = new \CodeJetter\core\Config();

        $registry->add($config, 'config3');
        $registry->add($config, 'config2');

        $this->assertEquals(true, $registry->isInContainer('config3'));

        $registry->remove('config3');

        $this->assertEquals(false, $registry->isInContainer('config3'));
        $this->assertEquals(true, $registry->isInContainer('config2'));

        $registry->add($config, 'config3');
        $this->assertEquals(true, $registry->isInContainer('config3'));
    }

    public function testRemove()
    {
        $registry = \CodeJetter\core\Registry::getInstance();

        $registry::resetContainer();

        $config = new \CodeJetter\core\Config();
        $registry->add($config);

        $this->setExpectedException('Exception', "'Config' is a reserved class. Cannot be removed");
        $registry->remove('Config');
    }
}
 