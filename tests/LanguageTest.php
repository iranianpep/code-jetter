<?php

namespace CodeJetter\tests;

use CodeJetter\Config;
use CodeJetter\core\App;
use CodeJetter\core\Language;

// this is to fix Cannot send session cookie - headers already sent
@session_start();

class LanguageTest extends \PHPUnit_Framework_TestCase
{
    public function testGetFileFullPath()
    {
        $app = App::getInstance();
        $app->init('dev');

        $language = new Language();
        $language->setCurrentLanguage('en');

        $config = new Config();
        $uri = $config->get('URI');

        $this->assertEquals($uri.'core/language/en.json', $language->getFileFullPath());
    }

    public function testGet()
    {
        $app = App::getInstance();
        $app->init('dev');

        $language = new Language();

        $this->assertEquals('en', $language->get('language'));

        $this->assertEquals('en, hello, world, hello', $language->get('testLanguage', ['placeholder1' => 'hello', 'placeholder2' => 'world']));

        $this->assertEquals('en, hello, {placeholder2}, hello', $language->get('testLanguage', ['placeholder1' => 'hello']));

        $language->setCurrentLanguage('fa');

        $this->assertEquals('fa', $language->get('language'));
    }

    public function testHas()
    {
        $app = App::getInstance();
        $app->init('dev');

        $language = new Language();

        $this->assertEquals(true, $language->has('language'));
        $this->assertEquals(false, $language->has(' '));
        $this->assertEquals(false, $language->has(''));
    }
}
