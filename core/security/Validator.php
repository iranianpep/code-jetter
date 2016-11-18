<?php

namespace CodeJetter\core\security;

use CodeJetter\core\FormHandler;
use CodeJetter\core\io\Input;
use CodeJetter\core\io\Output;
use CodeJetter\core\Registry;

/**
 * Class Validator
 * @package CodeJetter\core\security
 */
class Validator
{
    /**
     * @var array
     */
    private $definedInputs;

    /**
     * @var array
     */
    private $toCheckInputs;

    /**
     * @var array
     */
    private $errors;

    /**
     * This contains returned data from the functions for each rule
     *
     * @var
     */
    private $data;

    /**
     * @var
     */
    private $success;

    /**
     * @var boolean
     */
    private $stopAtFirstError;

    /**
     * @var boolean
     */
    private $alreadyValidated;

    /**
     * contains each rule and its associated function
     *
     * this can be used as a whitelist as well
     *
     * @var array
     */
    private $rulesConfigs;

    /**
     * @var
     */
    private $filteredInputs;

    /**
     * Validator constructor.
     *
     * @param array|null $definedInputs
     * @param array|null $toCheckInputs
     */
    public function __construct(array $definedInputs = null, array $toCheckInputs = null)
    {
        if ($definedInputs !== null) {
            $this->setDefinedInputs($definedInputs);
        }

        if ($toCheckInputs !== null) {
            $this->setToCheckInputs($toCheckInputs);
        }

        $this->rulesConfigs = Registry::getConfigClass()->get('rulesConfigs');
    }

    /**
     * @throws \Exception
     */
    public function validate()
    {
        $output = new Output();
        if ($this->isAlreadyValidated()) {
            // return the current output is already validated
            $output->setSuccess($this->getSuccess());
            $output->setMessages($this->getErrors());
            $output->setData($this->getData());

            return $output;
        }

        $toBeCheckedInputs = $this->getToCheckInputs();
        $errors = [];

        // iterate through each defined input rules
        if (!empty($this->getDefinedInputs())) {
            foreach ($this->getDefinedInputs() as $definedInputKey => $definedInput) {
                if (!$definedInput instanceof Input) {
                    continue;
                }

                if (array_key_exists($definedInputKey, $toBeCheckedInputs)) {
                    $this->filteredInputs[$definedInputKey] = $toBeCheckedInputs[$definedInputKey];
                }

                // extract rules from the current defined input
                $rules = $definedInput->getRules();

                // check if $toBeCheckedInputs contains the defined rule
                if (isset($toBeCheckedInputs[$definedInputKey]) || array_key_exists($definedInputKey, $toBeCheckedInputs)) {
                    if (!empty($rules)) {
                        // validate based on rules for this input
                        foreach ($rules as $rule) {
                            $function = $this->getFunctionByRule($rule->getKey());

                            $args = [
                                'toBeCheckedInput' => $toBeCheckedInputs[$definedInputKey],
                                'inputTitle' => $definedInput->getTitle(),
                                'key' => $rule->getKey()
                            ];

                            $additionalArgs = $rule->getFunctionArguments();

                            // add additional arguments if it is defined for this rule
                            if (!empty($additionalArgs)) {
                                $args = array_merge($args, $additionalArgs);
                            }

                            // call the relevant function
                            $output = $this->$function($args);

                            if (!$output instanceof Output) {
                                continue;
                            }

                            // store the returned data from the function for this rule
                            $data = $output->getData();
                            if (isset($data)) {
                                $this->setDataByKey($rule->getKey(), $data);
                            }

                            if ($output->getSuccess() == false) {
                                $errors[] = $output->getMessage();

                                if ($this->isStopAtFirstError() == true) {
                                    break 2;
                                }
                            }
                        }
                    }
                } else {
                    // $toBeCheckedInputs misses the defined input
                    // check to see if the missing input is required
                    //if (isset($rules['required']) || array_key_exists('required', $rules)) {
                    if (isset($rules['required'])) {
                        if (!empty($rules['required']->getMessage())) {
                            $errors[] = $rules['required']->getMessage();
                        } else {
                            $errors[] = "{$definedInput->getTitle()} is required.";
                        }

                        if ($this->isStopAtFirstError() == true) {
                            break;
                        }
                    }
                }
            }
        }

        $success = empty($errors) ? true : false;
        $this->setSuccess($success);
        $this->setErrors($errors);
        $this->setAlreadyValidated(true);

        $output->setSuccess($success);
        $output->setMessages($errors);
        $output->setData($this->getData());

        return $output;
    }

    /**
     * @return mixed
     */
    public function getFilteredInputs()
    {
        if (!isset($this->filteredInputs)) {
            $this->filteredInputs = [];
            if (!empty($this->getDefinedInputs())) {
                $toBeCheckedInputs = $this->getToCheckInputs();
                foreach ($this->getDefinedInputs() as $definedInputKey => $definedInput) {
                    if (!$definedInput instanceof Input) {
                        continue;
                    }

                    if (array_key_exists($definedInputKey, $toBeCheckedInputs)) {
                        $this->filteredInputs[$definedInputKey] = $toBeCheckedInputs[$definedInputKey];
                    }
                }
            }
        }

        return $this->filteredInputs;
    }

    /**
     * @return array
     */
    public function getDefinedInputs()
    {
        return $this->definedInputs;
    }

    /**
     * @param Input $input
     *
     * @throws \Exception
     */
    public function addDefinedInput(Input $input)
    {
        $inputs = $this->getDefinedInputs();
        $inputs[] = $input;
        $this->setDefinedInputs($inputs);
    }

    /**
     * @param array $inputs
     *
     * @throws \Exception
     */
    public function setDefinedInputs(array $inputs)
    {
        $newInputs = [];
        foreach ($inputs as $input) {
            if (!$input instanceof Input) {
                throw new \Exception('defined input is not instance of Input');
            }

            /**
             * If isAllRequired is set, add it to all the inputs
             */
            if (!empty($this->isAllRequired())) {
                $input->addRule(new ValidatorRule('required'));
            }

            $newInputs[$input->getKey()] = $input;
        }

        $this->definedInputs = $newInputs;

        // set already validated to false each time defined inputs change
        $this->setAlreadyValidated(false);
    }

    /**
     * @return array
     */
    public function getToCheckInputs()
    {
        return $this->toCheckInputs;
    }

    /**
     * @param array $toCheckInput
     */
    public function addToCheckInputs(array $toCheckInput)
    {
        $toCheckInputs = $this->getToCheckInputs();

        if (!empty($toCheckInput)) {
            foreach ($toCheckInput as $key => $value) {
                $toCheckInputs[$key] = $value;
            }
        }

        $this->setToCheckInputs($toCheckInputs);
    }

    /**
     * @param array $toCheckInputs
     */
    public function setToCheckInputs(array $toCheckInputs)
    {
        $this->toCheckInputs = $toCheckInputs;

        // set already validated to false each time defined inputs change
        $this->setAlreadyValidated(false);
    }

    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * @param array $errors
     */
    public function setErrors(array $errors)
    {
        $this->errors = $errors;
    }

    /**
     * @param $key
     * @param array   $messages
     */
    public function setError($key, array $messages)
    {
        $errors = $this->getErrors();
        $errors[$key] = $messages;
        $this->setErrors($errors);
    }

    /**
     * @param  $key
     * @return array
     */
    public function getError($key)
    {
        $errors = $this->getErrors();
        return $errors[$key];
    }

    /**
     * @return array
     */
    public function getRulesConfigs()
    {
        return $this->rulesConfigs;
    }

    /**
     * @param  $rule
     * @throws \Exception
     * @return string
     */
    public function getFunctionByRule($rule)
    {
        $rulesConfigs = $this->getRulesConfigs();

        if (empty($rulesConfigs[$rule]['function'])) {
            // dynamically create the function name
            $function = 'validate' . ucfirst(strtolower($rule));
        } else {
            // overwrite the normal way
            $function = $rulesConfigs[$rule]['function'];
        }

        if (!method_exists($this, $function)) {
            throw new \Exception("Function '{$function}' does not exist in Validator.php");
        }

        return $function;
    }

    /**
     * @param $rule
     *
     * @return mixed
     * @throws \Exception
     */
    public function getRegexByRule($rule)
    {
        $rulesConfigs = $this->getRulesConfigs();

        if (!array_key_exists($rule, $rulesConfigs)) {
            throw new \Exception("There is no such a rule: {$rule} in rules functions list");
        }

        if (empty($rulesConfigs[$rule]['regex'])) {
            throw new \Exception("There is no defined regex for the rule: {$rule}");
        }

        return $rulesConfigs[$rule]['regex'];
    }

    /**
     * @return boolean
     */
    public function isStopAtFirstError()
    {
        return $this->stopAtFirstError;
    }

    /**
     * @param $stopAtFirstError
     * @throws \Exception
     */
    public function setStopAtFirstError($stopAtFirstError)
    {
        if (!is_bool($stopAtFirstError)) {
            throw new \Exception('stopAtFirstError must be boolean');
        }

        $this->stopAtFirstError = $stopAtFirstError;
    }

    /**
     * @return boolean
     */
    public function isAlreadyValidated()
    {
        return $this->alreadyValidated;
    }

    /**
     * @param $alreadyValidated
     * @throws \Exception
     */
    public function setAlreadyValidated($alreadyValidated)
    {
        if (!is_bool($alreadyValidated)) {
            throw new \Exception('alreadyValidated must be boolean');
        }

        $this->alreadyValidated = $alreadyValidated;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param mixed $data
     */
    public function setData($data)
    {
        $this->data = $data;
    }

    /**
     * @param $ruleKey
     * @param $value
     *
     * @return bool
     */
    public function setDataByKey($ruleKey, $value)
    {
        if (empty($ruleKey)) {
            return false;
        }

        $data = $this->getData();
        $data[$ruleKey] = $value;
        $this->setData($data);
    }

    /**
     * @param $ruleKey
     *
     * @return bool
     */
    public function getDataByKey($ruleKey)
    {
        if (empty($ruleKey)) {
            return false;
        }

        $data = $this->getData();
        $value = isset($data[$ruleKey]) ? $data[$ruleKey] : false;
        return $value;
    }

    /**
     * @return boolean
     */
    public function getSuccess()
    {
        return $this->success;
    }

    /**
     * @param $success
     *
     * @throws \Exception
     */
    public function setSuccess($success)
    {
        if (!is_bool($success)) {
            throw new \Exception("{$success} is not a valid boolean");
        }

        $this->success = $success;
    }

    /**
     * @var boolean
     */
    private $allRequired;

    /**
     * @return boolean
     */
    public function isAllRequired()
    {
        return $this->allRequired;
    }

    /**
     * @param boolean $allRequired
     */
    public function setAllRequired($allRequired)
    {
        if ($allRequired == true) {
            // add the rule to the defined inputs
            $this->addRequiredRule();
            $this->allRequired = true;
        } else {
            $this->allRequired = false;
        }
    }

    /**
     * Fetch all the defined inputs and add required rule to all of them
     *
     * @throws \Exception
     */
    private function addRequiredRule()
    {
        $inputs = $this->getDefinedInputs();
        if (!empty($inputs)) {
            foreach ($inputs as $input) {
                if (!$input instanceof Input) {
                    continue;
                }

                $input->addRule(new ValidatorRule('required'));
            }

            $this->setDefinedInputs($inputs);
        }
    }

    // ********** Add validation functions here ********** //

    /**
     * @param array $args
     * @return bool
     */
    private function validateEmail(array $args)
    {
        $output = new Output();
        if (filter_var($args['toBeCheckedInput'], FILTER_VALIDATE_EMAIL)) {
            $output->setSuccess(true);
        } else {
            $output->setSuccess(false);
            $output->setMessage("'{$args['toBeCheckedInput']}' is not a valid email.");
        }

        return $output;
    }

    /**
     * @param array $args
     *
     * @return Output
     * @throws \Exception
     */
    private function validateNumber(array $args)
    {
        $output = new Output();
        if (is_numeric($args['toBeCheckedInput'])) {
            $output->setSuccess(true);
        } else {
            $output->setSuccess(false);
            $output->setMessage("'{$args['toBeCheckedInput']}' is not a valid number.");
        }

        return $output;
    }

    /**
     * @param array $args
     *
     * @return Output
     * @throws \Exception
     */
    private function validateMoney(array $args)
    {
        $output = new Output();
        if (preg_match("/^-?[0-9]+(?:\.[0-9]{1,2})?$/", $args['toBeCheckedInput'])) {
            $output->setSuccess(true);
        } else {
            $output->setSuccess(false);
            $output->setMessage("'{$args['toBeCheckedInput']}' is not a valid money value. It can have 2 decimal points at most.");
        }

        return $output;
    }

    /**
     * @param array $args
     * @return bool
     * @throws \Exception
     */
    private function validateSize(array $args)
    {
        if (!isset($args['size'])) {
            throw new \Exception('size must be specified in validateSize');
        }

        if (is_array($args['toBeCheckedInput'])) {
            $result = count($args['toBeCheckedInput']) === $args['size'];
        } else {
            $result = strlen(trim($args['toBeCheckedInput'])) === $args['size'];
        }

        $output = new Output();

        if ($result === true) {
            $output->setSuccess(true);
        } else {
            $output->setSuccess(false);
            $output->setMessage("{$args['inputTitle']} must be {$args['size']} in size.");
        }

        return $output;
    }

    /**
     * @param array $args
     * @return bool
     */
    private function validateRequired(array $args)
    {
        if (isset($args['toBeCheckedInput']) && is_string($args['toBeCheckedInput'])) {
            $toBeChecked = trim($args['toBeCheckedInput']);
        } else {
            $toBeChecked = $args['toBeCheckedInput'];
        }

        $output = new Output();
        if (empty($toBeChecked)
            && $toBeChecked !== 0
            && $toBeChecked !== '0'
            && $toBeChecked !== false) {
            $output->setSuccess(false);
            $output->setMessage("{$args['inputTitle']} is required.");
        } else {
            $output->setSuccess(true);
        }

        return $output;
    }

    /**
     * @param array $args
     *
     * @return array
     */
    private function validateUrl(array $args)
    {
        $output = new Output();
        // FILTER_VALIDATE_URL is not sophisticated enough, so use the next regex:
        // https://gist.github.com/dperini/729294
        //if (filter_var($args['toBeCheckedInput'], FILTER_VALIDATE_URL)) {
        if (preg_match($this->getRegexByRule($args['key']), $args['toBeCheckedInput'])) {
            $output->setSuccess(true);
        } else {
            $output->setSuccess(false);
            $output->setMessage("'{$args['toBeCheckedInput']}' is not a valid URL.");
        }

        return $output;
    }

    /**
     * @param array $args
     *
     * @return array
     */
    private function validateId(array $args)
    {
        $output = new Output();
        if (!isset($args['toBeCheckedInput'])) {
            $output->setSuccess(false);
            $output->setMessage("'{$args['toBeCheckedInput']}' is not a valid Id.");
            return $output;
        }

        // find spaces in the input
        if (preg_match($this->getRegexByRule($args['key']), $args['toBeCheckedInput'])) {
            $output->setSuccess(false);
            $output->setMessage("'{$args['toBeCheckedInput']}' is not a valid Id.");
            return $output;
        }

        if (isset($args['includingZero']) && $args['includingZero'] == true) {
            $minRange = 0;
        } else {
            $minRange = 1;
        }

        if (filter_var($args['toBeCheckedInput'], FILTER_VALIDATE_INT, ['options' => ['min_range' => $minRange]]) === false) {
            $output->setSuccess(false);
            $output->setMessage("'{$args['toBeCheckedInput']}' is not a valid Id.");
        } else {
            $output->setSuccess(true);
        }

        return $output;
    }

    /**
     * @param array $args
     *
     * @return Output
     * @throws \Exception
     */
    private function validateToken(array $args)
    {
        if (!isset($args['formName'])) {
            throw new \Exception('form name must be specified in validateSize');
        }

        $finalOutput = new Output();

        if (empty($args['toBeCheckedInput'])) {
            $finalOutput->setSuccess(false);
            $finalOutput->setMessage('Token must be provided.');
            return $finalOutput;
        }

        $output = (new FormHandler($args['formName']))->checkAntiCSRF($args['toBeCheckedInput']);

        $finalOutput->setData($output->getData());

        if ($output->getSuccess() === true) {
            $finalOutput->setSuccess(true);
        } else {
            $finalOutput->setSuccess(false);
            $finalOutput->setMessage('Token is not valid.');
        }

        return $finalOutput;
    }

    /**
     * @param array $args
     *
     * @return Output
     * @throws \Exception
     */
    private function validateUsername(array $args)
    {
        $output = new Output();
        if (empty($args['toBeCheckedInput'])) {
            $output->setSuccess(false);
            $output->setMessage("'{$args['toBeCheckedInput']}' is not a valid username.");
            return $output;
        }

        if (preg_match($this->getRegexByRule($args['key']), $args['toBeCheckedInput'])) {
            // valid
            $output->setSuccess(true);
        } else {
            $output->setSuccess(false);
            $output->setMessage("'{$args['toBeCheckedInput']}' is not a valid username.");
        }

        return $output;
    }

    /**
     * @param array $args
     *
     * @return Output
     * @throws \Exception
     */
    private function validatePassword(array $args)
    {
        $output = new Output();
        if (empty($args['toBeCheckedInput'])) {
            $output->setSuccess(false);
            $output->setMessage('Password is not valid.');
            return $output;
        }

        // if password confirmation / verification is set, compare it with the password
        if (isset($args['confirmation'])) {
            if ($args['toBeCheckedInput'] !== $args['confirmation']) {
                $output->setSuccess(false);
                $output->setMessage('Password does not match password confirmation.');
                return $output;
            }
        }

        if (isset($args['confirmationKey'])) {
            $inputs = $this->getToCheckInputs();
            if (!isset($inputs[$args['confirmationKey']]) || $args['toBeCheckedInput'] !== $inputs[$args['confirmationKey']]) {
                $output->setSuccess(false);
                $output->setMessage('Password does not match password confirmation.');
                return $output;
            }
        }

        if (preg_match($this->getRegexByRule($args['key']), $args['toBeCheckedInput'])) {
            // valid
            $output->setSuccess(true);
        } else {
            $output->setSuccess(false);
            $output->setMessage('Password is not valid.');
        }

        return $output;
    }

    /**
     * @param array $args
     *
     * @return Output
     * @throws \Exception
     */
    private function validateWhitelist(array $args)
    {
        $output = new Output();

        if (!isset($args['whitelist']) || !is_array($args['whitelist'])) {
            throw new \Exception('whitelist must be specified and must be an array in validateWhiteList');
        }

        $flag = false;
        if (is_array($args['toBeCheckedInput'])) {
            foreach ($args['toBeCheckedInput'] as $input) {
                if (!in_array($input, $args['whitelist'])) {
                    $foundInvalid = false;
                    break;
                }
            }

            if (!isset($foundInvalid) || $foundInvalid != false) {
                $flag = true;
            }
        } elseif (in_array($args['toBeCheckedInput'], $args['whitelist'])) {
            $flag = true;
        } else {
            $flag = false;
        }

        if ($flag === true) {
            $output->setSuccess(true);
        } else {
            $output->setSuccess(false);
            $output->setMessage("{$args['inputTitle']} is not valid.");
        }

        return $output;
    }
}

///**
// * start testing
// */
//
//$rule = new ValidatorRule('required', 'is required');
//$rule2 = new ValidatorRule('email', 'must be valid');
//$rule3 = new ValidatorRule('size', 'must have 3 letters');
//$rule3->setFunctionArguments(['size' => 3]);
//
//$nameInput = new Input('name', 'Name');
//$nameInput->addRule($rule);
//$nameInput->addRule($rule3);
//
//$emailInput = new Input('email', 'Email');
//$emailInput->addRule($rule);
//$emailInput->addRule($rule2);
//
//$inputs = array(
//    'name'  => $nameInput,
//    //'email' => $emailInput,
//);
//
//$dummyInput = array(
//    'name' => 'eh',
//    //'email' => 'd'
//);
//
//$validator = new Validator($inputs, $dummyInput);
//
//$validator->validate();
// var_dump($validator->getErrors());exit;
//echo 'finish checking<br>';
///**
// * finish testing
// */
