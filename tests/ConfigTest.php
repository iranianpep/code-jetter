<?php

    namespace CodeJetter\tests;

    use CodeJetter\Config;

    // this is to fix Cannot send session cookie - headers already sent
    @session_start();

class ConfigTest extends \PHPUnit_Framework_TestCase {
    public function testSetGet()
    {
        $config = new Config();

        $inputOutputs = [
            [
                'config' => 'ROOT_NAMESPACE',
                'newValue' => 'CodeJetter'
            ]
        ];

        // this is for empty input
        //$this->setExpectedException('Exception');

        foreach ($inputOutputs as $inputOutput) {
            // set with the new value
            $config->set($inputOutput['config'], $inputOutput['newValue']);

            $this->assertEquals($config->get($inputOutput['config']), $inputOutput['newValue']);
        }
    }
}
 