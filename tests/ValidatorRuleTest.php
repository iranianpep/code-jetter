<?php

class ValidatorRuleTest extends \PHPUnit_Framework_TestCase
{
    public function testConstruct()
    {
        $rule = new \CodeJetter\core\security\ValidatorRule('size', ['size' => 3], 'Size does not match');

        $this->assertEquals('size', $rule->getKey());
        $this->assertEquals(['size' => 3], $rule->getFunctionArguments());
        $this->assertEquals('Size does not match', $rule->getMessage());
    }

    public function testSetKey()
    {
        $rule = new \CodeJetter\core\security\ValidatorRule('size', ['size' => 3], 'Size does not match');

        $rule->setKey('test123');

        $this->assertEquals('test123', $rule->getKey());
    }

    public function testSetMessage()
    {
        $rule = new \CodeJetter\core\security\ValidatorRule('size', ['size' => 3], 'Size does not match');

        $rule->setMessage('blah blah');

        $this->assertEquals('blah blah', $rule->getMessage());
    }

    public function testSetFunctionArguments()
    {
        $rule = new \CodeJetter\core\security\ValidatorRule('size', ['size' => 3], 'Size does not match');

        $rule->setFunctionArguments(['blah blah' => 2]);

        $this->assertEquals(['blah blah' => 2], $rule->getFunctionArguments());
    }
}
