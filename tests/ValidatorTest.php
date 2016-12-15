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
    public function testValidateEmail()
    {
        $app = App::getInstance();
        $app->init('dev');

        $inputsOutputs = [
            [
                'inputs' => [
                    'email' => '',
                    'name'  => '',
                ],
                'output' => ['Email is required.', "'' is not a valid email."],
            ],
            [
                'inputs' => [
                    'email' => 'test@test.com',
                ],
                'output' => [],
            ],
            [
                'inputs' => [
                    'email' => ' test@test.com',
                ],
                'output' => ["' test@test.com' is not a valid email."],
            ],
            [
                'inputs' => [
                    'email' => 'test',
                ],
                'output' => ["'test' is not a valid email."],
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
            'name'  => '',
        ]);

        $nameInput = new Input('name', [$requiredRule]);
        $validator->addDefinedInput($nameInput);

        $output = $validator->validate();

        $this->assertEquals([
            'Email is required.',
            "'' is not a valid email.",
            'Name is required.',
        ], $output->getMessages());

        $this->assertEquals(false, $validator->getSuccess());

        $this->assertEquals([
            $emailInput->getKey() => $emailInput,
            $nameInput->getKey()  => $nameInput,
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
                    'id' => '',
                ],
                'output' => ['Id is required.', "'' is not a valid Id."],
            ],
            [
                'inputs' => [
                    'id' => '0',
                ],
                'output' => ["'0' is not a valid Id."],
            ],
            [
                'inputs' => [
                    'id' => 0,
                ],
                'output' => ["'0' is not a valid Id."],
            ],
            [
                'inputs' => [
                    'id' => '12d',
                ],
                'output' => ["'12d' is not a valid Id."],
            ],
            [
                'inputs' => [
                    'id' => 'd12',
                ],
                'output' => ["'d12' is not a valid Id."],
            ],
            [
                'inputs' => [
                    'id' => ' 12',
                ],
                'output' => ["' 12' is not a valid Id."],
            ],
            [
                'inputs' => [
                    'id' => '1 2',
                ],
                'output' => ["'1 2' is not a valid Id."],
            ],
            [
                'inputs' => [
                    'id' => 1,
                ],
                'output' => [],
            ],
            [
                'inputs' => [
                    'id' => '1',
                ],
                'output' => [],
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
                    'url' => 'ddd',
                ],
                'output' => ["'ddd' is not a valid URL."],
            ],
            [
                'inputs' => [
                    'url' => 'http://',
                ],
                'output' => ["'http://' is not a valid URL."],
            ],
            [
                'inputs' => [
                    'url' => 'http://ajax',
                ],
                'output' => ["'http://ajax' is not a valid URL."],
            ],
            [
                'inputs' => [
                    'url' => 'http://ajax.com',
                ],
                'output' => [],
            ],
            [
                'inputs' => [
                    'url' => 'http://www.ajax.com',
                ],
                'output' => [],
            ],
            [
                'inputs' => [
                    'url' => 'www.ajax.com',
                ],
                // http must be passed
                'output' => ["'www.ajax.com' is not a valid URL."],
            ],
            [
                'inputs' => [
                    'url' => 'http://www.ajax-test.com',
                ],
                'output' => [],
            ],
            [
                'inputs' => [
                    'url' => 'http://ajax-test.com',
                ],
                'output' => [],
            ],
            [
                'inputs' => [
                    'url' => 0,
                ],
                'output' => ["'0' is not a valid URL."],
            ],
            [
                'inputs' => [
                    'url' => '0',
                ],
                'output' => ["'0' is not a valid URL."],
            ],
            [
                'inputs' => [
                    'url' => ' ',
                ],
                'output' => ["' ' is not a valid URL."],
            ],
            [
                'inputs' => [
                    'url' => ' http://www.ehsan.com',
                ],
                'output' => ["' http://www.ehsan.com' is not a valid URL."],
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
        $this->assertEquals(["'tet' is not a valid URL."], $output->getMessages());
    }

    public function testValidateRequired()
    {
        $app = App::getInstance();
        $app->init('dev');

        $inputsOutputs = [
            [
                'inputs' => [
                    'email' => '',
                    'name'  => '',
                ],
                'output' => ['Name is required.'],
            ],
            [
                'inputs' => [
                    'email' => '',
                    'name'  => '0',
                ],
                'output' => [],
            ],
            [
                'inputs' => [
                    'email' => '',
                    'name'  => 0,
                ],
                'output' => [],
            ],
            [
                'inputs' => [
                    'email' => '',
                    'name'  => true,
                ],
                'output' => [],
            ],
            [
                'inputs' => [
                    'email' => '',
                    'name'  => false,
                ],
                'output' => [],
            ],
            [
                'inputs' => [
                    'email' => '',
                    'name'  => 'false',
                ],
                'output' => [],
            ],
            [
                'inputs' => [
                    'email' => '',
                    'name'  => 'true',
                ],
                'output' => [],
            ],
            [
                'inputs' => [
                    'email' => '',
                    'name'  => ' ',
                ],
                'output' => ['Name is required.'],
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
                    'toCheckSizeInput' => 'test@test.com',
                ],
                'output' => ['ToCheckSizeInput must be 1 in size.'],
            ],
            [
                'inputs' => [
                    'toCheckSizeInput' => 't',
                ],
                'output' => [],
            ],
            [
                'inputs' => [
                    'toCheckSizeInput' => 0,
                ],
                'output' => [],
            ],
            [
                'inputs' => [
                    'toCheckSizeInput' => 1,
                ],
                'output' => [],
            ],
            [
                'inputs' => [
                    'toCheckSizeInput' => ' ',
                ],
                'output' => ['ToCheckSizeInput must be 1 in size.'],
            ],
            [
                'inputs' => [
                    'toCheckSizeInput' => '',
                ],
                'output' => ['ToCheckSizeInput must be 1 in size.'],
            ],
            [
                'inputs' => [
                    'toCheckSizeInput' => [],
                ],
                'output' => ['ToCheckSizeInput must be 1 in size.'],
            ],
            [
                'inputs' => [
                    'toCheckSizeInput' => [1],
                ],
                'output' => [],
            ],
            [
                'inputs' => [
                    'toCheckSizeInput' => [0],
                ],
                'output' => [],
            ],
            [
                'inputs' => [
                    'toCheckSizeInput' => ['0'],
                ],
                'output' => [],
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
                    'username' => '',
                ],
                'output' => ["'' is not a valid username."],
            ],
            [
                'inputs' => [
                    'username' => 'test',
                ],
                'output' => [],
            ],
            [
                'inputs' => [
                    'username' => 'te st',
                ],
                'output' => ["'te st' is not a valid username."],
            ],
            [
                'inputs' => [
                    'username' => 'tt',
                ],
                'output' => ["'tt' is not a valid username."],
            ],
            [
                'inputs' => [
                    'username' => '_tt',
                ],
                'output' => ["'_tt' is not a valid username."],
            ],
            [
                'inputs' => [
                    'username' => 'tt_',
                ],
                'output' => [],
            ],
            [
                'inputs' => [
                    'username' => 'tt ',
                ],
                'output' => ["'tt ' is not a valid username."],
            ],
            [
                'inputs' => [
                    'username' => '12345678912345678910',
                ],
                'output' => [],
            ],
            [
                'inputs' => [
                    'username' => '12345678912345678910_',
                ],
                'output' => ["'12345678912345678910_' is not a valid username."],
            ],
            [
                'inputs' => [
                    'username' => '12345678912345678910 ',
                ],
                'output' => ["'12345678912345678910 ' is not a valid username."],
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
                    'password' => '',
                ],
                'output' => ['Password is not valid.'],
            ],
            [
                'inputs' => [
                    'password' => 'test',
                ],
                'output' => ['Password is not valid.'],
            ],
            [
                'inputs' => [
                    'password' => 'test123E',
                ],
                'output' => [],
            ],
            [
                'inputs' => [
                    'password' => 'tes t123E',
                ],
                'output' => [],
            ],
            [
                'inputs' => [
                    'password' => ' tes t123E ',
                ],
                'output' => [],
            ],
            [
                'inputs' => [
                    'password' => '! tes t123E ',
                ],
                'output' => [],
            ],
            [
                'inputs' => [
                    'password' => '#! tes t123E ',
                ],
                'output' => [],
            ],
            [
                'inputs' => [
                    'password' => '#! tes t123e ',
                ],
                'output' => ['Password is not valid.'],
            ],
            [
                'inputs' => [
                    'password' => '12345678912345678901',
                ],
                'output' => ['Password is not valid.'],
            ],
            [
                'inputs' => [
                    'password' => '12345678912345678901E',
                ],
                'output' => ['Password is not valid.'],
            ],
            [
                'inputs' => [
                    'password' => '1234567891235678901E',
                ],
                'output' => ['Password is not valid.'],
            ],
            [
                'inputs' => [
                    'password' => '12345e6789235678901E',
                ],
                'output' => [],
            ],
            [
                'inputs' => [
                    'password' => ' 2345e6789235678901E',
                ],
                'output' => [],
            ],
            [
                'inputs' => [
                    'password' => ' 2345e678923#678901E',
                ],
                'output' => [],
            ],
            [
                'inputs' => [
                    'password' => ' e67%$#@*_-y3#601E',
                ],
                'output' => [],
            ],
            [
                'inputs' => [
                    'password' => " e6%$#@'*_-y3#601E",
                ],
                'output' => [],
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
                    'password' => '',
                ],
                'output' => ['Password is not valid.'],
            ],
            [
                'inputs' => [
                    'password' => '1',
                ],
                'output' => ['Password does not match password confirmation.'],
            ],
            [
                'inputs' => [
                    'password' => '2345e6789235678901E',
                ],
                'output' => [],
            ],
            [
                'inputs' => [
                    'password' => ' 2345e6789235678901E',
                ],
                'output' => ['Password does not match password confirmation.'],
            ],
        ];

        foreach ($inputsOutputs as $inputsOutput) {
            $validator = new Validator([$passwordInput], $inputsOutput['inputs']);
            $output = $validator->validate();
            $this->assertEquals($inputsOutput['output'], $output->getMessages());
        }

        $passwordRule = new ValidatorRule('password', ['confirmationKey' => 'passwordConfirmation']);
        $passwordInput = new Input('password', [$passwordRule]);

        $inputsOutputs = [
            [
                'inputs' => [
                    'password'             => '',
                    'passwordConfirmation' => '',
                ],
                'output' => ['Password is not valid.'],
            ],
            [
                'inputs' => [
                    'password' => '1',
                ],
                'output' => ['Password does not match password confirmation.'],
            ],
            [
                'inputs' => [
                    'password'             => '2345e6789235678901E',
                    'passwordConfirmation' => '1',
                ],
                'output' => ['Password does not match password confirmation.'],
            ],
            [
                'inputs' => [
                    'password'             => '2345e6789235678901E',
                    'passwordConfirmation' => '2345e6789235678901E',
                ],
                'output' => [],
            ],
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
                    'toCheckInput' => '1',
                ],
                'output' => [],
            ],
            [
                'inputs' => [
                    'toCheckInput' => '',
                ],
                'output' => ['ToCheckInput is not valid.'],
            ],
            [
                'inputs' => [
                    'toCheckInput' => [1, 2, 3],
                ],
                'output' => [],
            ],
            [
                'inputs' => [
                    'toCheckInput' => [1, 2, 3, 4],
                ],
                'output' => ['ToCheckInput is not valid.'],
            ],
            [
                'inputs' => [
                    'toCheckInput' => [1, 2],
                ],
                'output' => [],
            ],
            [
                'inputs' => [
                    'toCheckInput' => '0',
                ],
                'output' => ['ToCheckInput is not valid.'],
            ],
            [
                'inputs' => [
                    'toCheckInput' => '5',
                ],
                'output' => ['ToCheckInput is not valid.'],
            ],
        ];

        foreach ($inputsOutputs as $inputsOutput) {
            $validator = new Validator([$dummyInput], $inputsOutput['inputs']);
            $output = $validator->validate();
            $this->assertEquals($inputsOutput['output'], $output->getMessages());
        }
    }

    public function testValidateNumber()
    {
        $app = App::getInstance();
        $app->init('dev');

        $numberRule = new ValidatorRule('number');
        $dummyInput = new Input('toCheckInput', [$numberRule]);

        $inputsOutputs = [
            [
                'inputs' => [
                    'toCheckInput' => '1',
                ],
                'output' => [],
            ],
            [
                'inputs' => [
                    'toCheckInput' => '65000.00',
                ],
                'output' => [],
            ],
            [
                'inputs' => [
                    'toCheckInput' => '123.23',
                ],
                'output' => [],
            ],
            [
                'inputs' => [
                    'toCheckInput' => 'eee',
                ],
                'output' => [
                    "'eee' is not a valid number.",
                ],
            ],
            [
                'inputs' => [
                    'toCheckInput' => '12.3e',
                ],
                'output' => [
                    "'12.3e' is not a valid number.",
                ],
            ],
            [
                'inputs' => [
                    // e actually a number
                    'toCheckInput' => '12.e343',
                ],
                'output' => [],
            ],
        ];

        foreach ($inputsOutputs as $inputsOutput) {
            $validator = new Validator([$dummyInput], $inputsOutput['inputs']);
            $output = $validator->validate();
            $this->assertEquals($inputsOutput['output'], $output->getMessages());
        }
    }

    public function testValidateMoney()
    {
        $app = App::getInstance();
        $app->init('dev');

        $rule = new ValidatorRule('money');
        $dummyInput = new Input('toCheckInput', [$rule]);

        $inputsOutputs = [
            [
                'inputs' => [
                    'toCheckInput' => '1',
                ],
                'output' => [],
            ],
            [
                'inputs' => [
                    'toCheckInput' => '1.0',
                ],
                'output' => [],
            ],
            [
                'inputs' => [
                    'toCheckInput' => '1.00',
                ],
                'output' => [],
            ],
            [
                'inputs' => [
                    'toCheckInput' => '11.00',
                ],
                'output' => [],
            ],
            [
                'inputs' => [
                    'toCheckInput' => '111.00',
                ],
                'output' => [],
            ],
            [
                'inputs' => [
                    'toCheckInput' => '111.56',
                ],
                'output' => [],
            ],
            [
                'inputs' => [
                    'toCheckInput' => '1.0 ',
                ],
                'output' => [
                    "'1.0 ' is not a valid money value. It can have 2 decimal points at most.",
                ],
            ],
            [
                'inputs' => [
                    'toCheckInput' => 'e',
                ],
                'output' => [
                    "'e' is not a valid money value. It can have 2 decimal points at most.",
                ],
            ],
            [
                'inputs' => [
                    'toCheckInput' => '   ',
                ],
                'output' => [
                    "'   ' is not a valid money value. It can have 2 decimal points at most.",
                ],
            ],
            [
                'inputs' => [
                    'toCheckInput' => '1.000',
                ],
                'output' => [
                    "'1.000' is not a valid money value. It can have 2 decimal points at most.",
                ],
            ],
            [
                'inputs' => [
                    'toCheckInput' => '12.e343',
                ],
                'output' => [
                    "'12.e343' is not a valid money value. It can have 2 decimal points at most.",
                ],
            ],
        ];

        foreach ($inputsOutputs as $inputsOutput) {
            $validator = new Validator([$dummyInput], $inputsOutput['inputs']);
            $output = $validator->validate();
            $this->assertEquals($inputsOutput['output'], $output->getMessages());
        }
    }

    public function testIsAllRequired()
    {
        $app = App::getInstance();
        $app->init('dev');

        $dummyInput1 = new Input('name');
        $dummyInput2 = new Input('email');

        $inputsOutputs = [
            [
                'inputs' => [
                    'toCheckInput' => '1',
                ],
                'output' => [
                    'Name is required.',
                    'Email is required.',
                ],
            ],
            [
                'inputs' => [
                    'name'  => 'ehsan',
                    'email' => 'ehsan@ehsan.com',
                ],
                'output' => [],
            ],
            [
                'inputs' => [
                    'name' => 'ehsan',
                ],
                'output' => [
                    'Email is required.',
                ],
            ],
        ];

        // calling setAllRequired after setting inputs
        foreach ($inputsOutputs as $inputsOutput) {
            $validator = new Validator([$dummyInput1, $dummyInput2], $inputsOutput['inputs']);
            $validator->setAllRequired(true);
            $output = $validator->validate();
            $this->assertEquals($inputsOutput['output'], $output->getMessages());
        }

        // calling setAllRequired before setting inputs
        foreach ($inputsOutputs as $inputsOutput) {
            $validator = new Validator();
            $validator->setAllRequired(true);
            $validator->setDefinedInputs([$dummyInput1, $dummyInput2]);
            $validator->setToCheckInputs($inputsOutput['inputs']);
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
            'email' => '',
        ];

        $validator = new Validator([$emailInput], $toBeCheckedInputs);
        $validator->validate();

        $errors = $validator->getErrors();
        $this->assertEquals(['Email is required.'], $errors);

        $this->assertEquals($toBeCheckedInputs, $validator->getToCheckInputs());
    }

    public function testGetFilteredInputs()
    {
        $dummyInput1 = new Input('name');
        $dummyInput2 = new Input('email');

        $inputsOutputs = [
            [
                'inputs' => [
                    'name'  => 'ehsan',
                    'email' => 'ehsan@ehsan.com',
                ],
                'output' => [
                    'name'  => 'ehsan',
                    'email' => 'ehsan@ehsan.com',
                ],
            ],
            [
                'inputs' => [
                    'name'  => 'ehsan',
                    'token' => 'g87g87f8f8bbm',
                ],
                'output' => [
                    'name' => 'ehsan',
                ],
            ],
            [
                'inputs' => [
                    'username' => 'ehsan',
                    'token'    => 'g87g87f8f8bbm',
                ],
                'output' => [],
            ],
        ];

        foreach ($inputsOutputs as $inputsOutput) {
            $validator = new Validator([$dummyInput1, $dummyInput2], $inputsOutput['inputs']);
            $filteredInputs = $validator->getFilteredInputs();
            $this->assertEquals($inputsOutput['output'], $filteredInputs);
        }

        // this time with validate()
        foreach ($inputsOutputs as $inputsOutput) {
            $validator = new Validator([$dummyInput1, $dummyInput2], $inputsOutput['inputs']);
            $validator->validate();
            $filteredInputs = $validator->getFilteredInputs();
            $this->assertEquals($inputsOutput['output'], $filteredInputs);
        }
    }
}
