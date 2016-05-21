<?php

    namespace CodeJetter\tests;

use CodeJetter\core\App;
use CodeJetter\core\io\Input;
use CodeJetter\core\security\Validator;
use CodeJetter\core\security\ValidatorRule;

// this is to fix Cannot send session cookie - headers already sent
@session_start();

class ValidatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @runInSeparateProcess
     */
    public function testValidateEmail()
    {
        $app = App::getInstance();
        $app->init('dev');

        $inputsOutputs = [
            [
                'inputs' => [
                    'email' => '',
                    'name' => ''
                ],
                'output' => ['Email is required.', "&#039;&#039; is not a valid email."]
            ],
            [
                'inputs' => [
                    'email' => 'test@test.com'
                ],
                'output' => []
            ],
            [
                'inputs' => [
                    'email' => ' test@test.com'
                ],
                'output' => ["&#039; test@test.com&#039; is not a valid email."]
            ],
            [
                'inputs' => [
                    'email' => 'test'
                ],
                'output' => ["&#039;test&#039; is not a valid email."]
            ],
        ];

        $requiredRule = new ValidatorRule('required');
        $emailRule = new ValidatorRule('email');
        $emailInput = new Input('email', [$requiredRule, $emailRule]);

        foreach ($inputsOutputs as $inputsOutput) {
            $validator = new Validator([$emailInput], $inputsOutput['inputs']);
            $output = $validator->validate();
            $this->assertEquals($inputsOutput['output'], $output->getMessages());
        }

        $validator = new Validator([$emailInput], [
            'email' => '',
            'name' => ''
        ]);

        $nameInput = new Input('name', [$requiredRule]);
        $validator->addDefinedInput($nameInput);

        $output = $validator->validate();

        $this->assertEquals([
            'Email is required.',
            "&#039;&#039; is not a valid email.",
            'Name is required.'
        ], $output->getMessages());

        $this->assertEquals(false, $validator->getSuccess());

        $this->assertEquals([
            $emailInput->getKey() => $emailInput,
            $nameInput->getKey() => $nameInput
        ], $validator->getDefinedInputs());
    }

    public function testValidateId()
    {
        $app = App::getInstance();
        $app->init('dev');

        $requiredRule = new ValidatorRule('required');
        $idRule = new ValidatorRule('id');
        $idInput = new Input('id', [$requiredRule, $idRule]);

        $inputsOutputs = [
            [
                'inputs' => [
                    'id' => ''
                ],
                'output' => ['Id is required.', "&#039;&#039; is not a valid Id."]
            ],
            [
                'inputs' => [
                    'id' => '0'
                ],
                'output' => ["&#039;0&#039; is not a valid Id."]
            ],
            [
                'inputs' => [
                    'id' => 0
                ],
                'output' => ["&#039;0&#039; is not a valid Id."]
            ],
            [
                'inputs' => [
                    'id' => '12d'
                ],
                'output' => ["&#039;12d&#039; is not a valid Id."]
            ],
            [
                'inputs' => [
                    'id' => 'd12'
                ],
                'output' => ["&#039;d12&#039; is not a valid Id."]
            ],
            [
                'inputs' => [
                    'id' => ' 12'
                ],
                'output' => ["&#039; 12&#039; is not a valid Id."]
            ],
            [
                'inputs' => [
                    'id' => '1 2'
                ],
                'output' => ["&#039;1 2&#039; is not a valid Id."]
            ],
            [
                'inputs' => [
                    'id' => 1
                ],
                'output' => []
            ],
            [
                'inputs' => [
                    'id' => '1'
                ],
                'output' => []
            ],
        ];

        foreach ($inputsOutputs as $inputsOutput) {
            $validator = new Validator([$idInput], $inputsOutput['inputs']);
            $output = $validator->validate();
            $this->assertEquals($inputsOutput['output'], $output->getMessages());
        }
    }

    public function testValidateURL()
    {
        $app = App::getInstance();
        $app->init('dev');

        $urlRule = new ValidatorRule('url');
        $urlInput = new Input('url', [$urlRule]);

        $inputsOutputs = [
            [
                'inputs' => [
                    'url' => 'ddd'
                ],
                'output' => ["&#039;ddd&#039; is not a valid URL."]
            ],
            [
                'inputs' => [
                    'url' => 'http://'
                ],
                'output' => ["&#039;http://&#039; is not a valid URL."]
            ],
            [
                'inputs' => [
                    'url' => 'http://ajax'
                ],
                'output' => ["&#039;http://ajax&#039; is not a valid URL."]
            ],
            [
                'inputs' => [
                    'url' => 'http://ajax.com'
                ],
                'output' => []
            ],
            [
                'inputs' => [
                    'url' => 'http://www.ajax.com'
                ],
                'output' => []
            ],
            [
                'inputs' => [
                    'url' => 'www.ajax.com'
                ],
                // http must be passed
                'output' => ["&#039;www.ajax.com&#039; is not a valid URL."]
            ],
            [
                'inputs' => [
                    'url' => 'http://www.ajax-test.com'
                ],
                'output' => []
            ],
            [
                'inputs' => [
                    'url' => 'http://ajax-test.com'
                ],
                'output' => []
            ],
            [
                'inputs' => [
                    'url' => 0
                ],
                'output' => ["&#039;0&#039; is not a valid URL."]
            ],
            [
                'inputs' => [
                    'url' => '0'
                ],
                'output' => ["&#039;0&#039; is not a valid URL."]
            ],
            [
                'inputs' => [
                    'url' => ' '
                ],
                'output' => ["&#039; &#039; is not a valid URL."]
            ],
            [
                'inputs' => [
                    'url' => ' http://www.ehsan.com'
                ],
                'output' => ["&#039; http://www.ehsan.com&#039; is not a valid URL."]
            ],
        ];

        foreach ($inputsOutputs as $inputsOutput) {
            $validator = new Validator([$urlInput], $inputsOutput['inputs']);
            $output = $validator->validate();
            $this->assertEquals($inputsOutput['output'], $output->getMessages());
        }

        $differentNameURL = new Input('testURL', [$urlRule]);
        $validator = new Validator([$differentNameURL], ['testURL' => 'tet']);
        $output = $validator->validate();
        $this->assertEquals(["&#039;tet&#039; is not a valid URL."], $output->getMessages());
    }

    public function testValidateRequired()
    {
        $app = App::getInstance();
        $app->init('dev');

        $inputsOutputs = [
            [
                'inputs' => [
                    'email' => '',
                    'name' => ''
                ],
                'output' => ['Name is required.']
            ],
            [
                'inputs' => [
                    'email' => '',
                    'name' => '0'
                ],
                'output' => []
            ],
            [
                'inputs' => [
                    'email' => '',
                    'name' => 0
                ],
                'output' => []
            ],
            [
                'inputs' => [
                    'email' => '',
                    'name' => true
                ],
                'output' => []
            ],
            [
                'inputs' => [
                    'email' => '',
                    'name' => false
                ],
                'output' => []
            ],
            [
                'inputs' => [
                    'email' => '',
                    'name' => 'false'
                ],
                'output' => []
            ],
            [
                'inputs' => [
                    'email' => '',
                    'name' => 'true'
                ],
                'output' => []
            ],
            [
                'inputs' => [
                    'email' => '',
                    'name' => ' '
                ],
                'output' => ['Name is required.']
            ],
        ];

        $requiredRule = new ValidatorRule('required');
        $dummyInput = new Input('name', [$requiredRule]);

        foreach ($inputsOutputs as $inputsOutput) {
            $validator = new Validator([$dummyInput], $inputsOutput['inputs']);
            $output = $validator->validate();
            $this->assertEquals($inputsOutput['output'], $output->getMessages());
        }
    }

    public function testValidateSize()
    {
        $app = App::getInstance();
        $app->init('dev');

        $sizeRule = new ValidatorRule('size', ['size' => 1]);
        $dummyInput = new Input('toCheckSizeInput', [$sizeRule]);

        $inputsOutputs = [
            [
                'inputs' => [
                    'toCheckSizeInput' => 'test@test.com'
                ],
                'output' => ["ToCheckSizeInput must be 1 in size."]
            ],
            [
                'inputs' => [
                    'toCheckSizeInput' => 't'
                ],
                'output' => []
            ],
            [
                'inputs' => [
                    'toCheckSizeInput' => 0
                ],
                'output' => []
            ],
            [
                'inputs' => [
                    'toCheckSizeInput' => 1
                ],
                'output' => []
            ],
            [
                'inputs' => [
                    'toCheckSizeInput' => ' '
                ],
                'output' => ['ToCheckSizeInput must be 1 in size.']
            ],
            [
                'inputs' => [
                    'toCheckSizeInput' => ''
                ],
                'output' => ['ToCheckSizeInput must be 1 in size.']
            ],
            [
                'inputs' => [
                    'toCheckSizeInput' => []
                ],
                'output' => ['ToCheckSizeInput must be 1 in size.']
            ],
            [
                'inputs' => [
                    'toCheckSizeInput' => [1]
                ],
                'output' => []
            ],
            [
                'inputs' => [
                    'toCheckSizeInput' => [0]
                ],
                'output' => []
            ],
            [
                'inputs' => [
                    'toCheckSizeInput' => ['0']
                ],
                'output' => []
            ],
        ];

        foreach ($inputsOutputs as $inputsOutput) {
            $validator = new Validator([$dummyInput], $inputsOutput['inputs']);
            $output = $validator->validate();
            $this->assertEquals($inputsOutput['output'], $output->getMessages());
        }
    }

    public function testValidateUsername()
    {
        $app = App::getInstance();
        $app->init('dev');

        $usernameRule = new ValidatorRule('username');
        $usernameInput = new Input('username', [$usernameRule]);

        $inputsOutputs = [
            [
                'inputs' => [
                    'username' => ''
                ],
                'output' => ["&#039;&#039; is not a valid username."]
            ],
            [
                'inputs' => [
                    'username' => 'test'
                ],
                'output' => []
            ],
            [
                'inputs' => [
                    'username' => 'te st'
                ],
                'output' => ["&#039;te st&#039; is not a valid username."]
            ],
            [
                'inputs' => [
                    'username' => 'tt'
                ],
                'output' => ["&#039;tt&#039; is not a valid username."]
            ],
            [
                'inputs' => [
                    'username' => '_tt'
                ],
                'output' => ["&#039;_tt&#039; is not a valid username."]
            ],
            [
                'inputs' => [
                    'username' => 'tt_'
                ],
                'output' => []
            ],
            [
                'inputs' => [
                    'username' => 'tt '
                ],
                'output' => ["&#039;tt &#039; is not a valid username."]
            ],
            [
                'inputs' => [
                    'username' => '12345678912345678910'
                ],
                'output' => []
            ],
            [
                'inputs' => [
                    'username' => '12345678912345678910_'
                ],
                'output' => ["&#039;12345678912345678910_&#039; is not a valid username."]
            ],
            [
                'inputs' => [
                    'username' => '12345678912345678910 '
                ],
                'output' => ["&#039;12345678912345678910 &#039; is not a valid username."]
            ],
        ];

        foreach ($inputsOutputs as $inputsOutput) {
            $validator = new Validator([$usernameInput], $inputsOutput['inputs']);
            $output = $validator->validate();
            $this->assertEquals($inputsOutput['output'], $output->getMessages());
        }
    }

    public function testValidatePassword()
    {
        $app = App::getInstance();
        $app->init('dev');

        $passwordRule = new ValidatorRule('password');
        $passwordInput = new Input('password', [$passwordRule]);

        $inputsOutputs = [
            [
                'inputs' => [
                    'password' => ''
                ],
                'output' => ["Password is not valid."]
            ],
            [
                'inputs' => [
                    'password' => 'test'
                ],
                'output' => ["Password is not valid."]
            ],
            [
                'inputs' => [
                    'password' => 'test123E'
                ],
                'output' => []
            ],
            [
                'inputs' => [
                    'password' => 'tes t123E'
                ],
                'output' => []
            ],
            [
                'inputs' => [
                    'password' => ' tes t123E '
                ],
                'output' => []
            ],
            [
                'inputs' => [
                    'password' => '! tes t123E '
                ],
                'output' => []
            ],
            [
                'inputs' => [
                    'password' => '#! tes t123E '
                ],
                'output' => []
            ],
            [
                'inputs' => [
                    'password' => '#! tes t123e '
                ],
                'output' => ["Password is not valid."]
            ],
            [
                'inputs' => [
                    'password' => '12345678912345678901'
                ],
                'output' => ["Password is not valid."]
            ],
            [
                'inputs' => [
                    'password' => '12345678912345678901E'
                ],
                'output' => ["Password is not valid."]
            ],
            [
                'inputs' => [
                    'password' => '1234567891235678901E'
                ],
                'output' => ["Password is not valid."]
            ],
            [
                'inputs' => [
                    'password' => '12345e6789235678901E'
                ],
                'output' => []
            ],
            [
                'inputs' => [
                    'password' => ' 2345e6789235678901E'
                ],
                'output' => []
            ],
            [
                'inputs' => [
                    'password' => ' 2345e678923#678901E'
                ],
                'output' => []
            ],
            [
                'inputs' => [
                    'password' => ' e67%$#@*_-y3#601E'
                ],
                'output' => []
            ],
            [
                'inputs' => [
                    'password' => " e6%$#@'*_-y3#601E"
                ],
                'output' => []
            ],
        ];

        foreach ($inputsOutputs as $inputsOutput) {
            $validator = new Validator([$passwordInput], $inputsOutput['inputs']);
            $output = $validator->validate();
            $this->assertEquals($inputsOutput['output'], $output->getMessages());
        }

        $passwordRule = new ValidatorRule('password', ['confirmation' => '2345e6789235678901E']);
        $passwordInput = new Input('password', [$passwordRule]);

        $inputsOutputs = [
            [
                'inputs' => [
                    'password' => ''
                ],
                'output' => ["Password is not valid."]
            ],
            [
                'inputs' => [
                    'password' => '1'
                ],
                'output' => ["Password does not match password confirmation."]
            ],
            [
                'inputs' => [
                    'password' => '2345e6789235678901E'
                ],
                'output' => []
            ],
            [
                'inputs' => [
                    'password' => ' 2345e6789235678901E'
                ],
                'output' => ["Password does not match password confirmation."]
            ]
        ];

        foreach ($inputsOutputs as $inputsOutput) {
            $validator = new Validator([$passwordInput], $inputsOutput['inputs']);
            $output = $validator->validate();
            $this->assertEquals($inputsOutput['output'], $output->getMessages());
        }
    }

    public function testValidateWhiteList()
    {
        $app = App::getInstance();
        $app->init('dev');

        $whiteListRule = new ValidatorRule('whitelist', ['whitelist' => [1, 2, 3]]);
        $dummyInput = new Input('toCheckInput', [$whiteListRule]);

        $inputsOutputs = [
            [
                'inputs' => [
                    'toCheckInput' => '1'
                ],
                'output' => []
            ],
            [
                'inputs' => [
                    'toCheckInput' => ''
                ],
                'output' => ['input is not valid.']
            ],
            [
                'inputs' => [
                    'toCheckInput' => [1, 2, 3]
                ],
                'output' => ['input is not valid.']
            ],
            [
                'inputs' => [
                    'toCheckInput' => '0'
                ],
                'output' => ['input is not valid.']
            ]
        ];

        foreach ($inputsOutputs as $inputsOutput) {
            $validator = new Validator([$dummyInput], $inputsOutput['inputs']);
            $output = $validator->validate();
            $this->assertEquals($inputsOutput['output'], $output->getMessages());
        }
    }

    public function testGetErrors()
    {
        $app = App::getInstance();
        $app->init('dev');

        $requiredRule = new ValidatorRule('required');
        $emailInput = new Input('email', [$requiredRule]);

        $toBeCheckedInputs = [
            'email' => ''
        ];

        $validator = new Validator([$emailInput], $toBeCheckedInputs);
        $validator->validate();

        $errors = $validator->getErrors();
        $this->assertEquals(['Email is required.'], $errors);

        $this->assertEquals($toBeCheckedInputs, $validator->getToCheckInputs());
    }
}
 