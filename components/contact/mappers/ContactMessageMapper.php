<?php

namespace CodeJetter\components\contact\mappers;

use CodeJetter\core\BaseMapper;
use CodeJetter\core\io\DatabaseInput;
use CodeJetter\core\io\Output;
use CodeJetter\core\security\Validator;
use CodeJetter\core\security\ValidatorRule;
use CodeJetter\core\utility\ArrayUtility;
use CodeJetter\core\utility\InputUtility;

class ContactMessageMapper extends BaseMapper
{
    public function add(array $inputs, array $fieldsValues = [], $additionalDefinedInputs = [])
    {
        /**
         * Start validating.
         */
        $output = new Output();
        try {
            $definedInputs = $this->getDefinedInputs('add');

            $validator = new Validator($definedInputs, $inputs);
            $validatorOutput = $validator->validate();

            if ($validatorOutput->getSuccess() !== true) {
                $output->setSuccess(false);
                $output->setMessages($validatorOutput->getMessages());

                return $output;
            }
        } catch (\Exception $e) {
            (new \CodeJetter\core\ErrorHandler())->logError($e);
        }
        /**
         * Finish validating.
         */

        /**
         * Start inserting.
         */
        $fieldsValues = $this->getFieldsValues($inputs, [], 'add');
        $insertedId = $this->insertOne($fieldsValues);

        $output = new Output();
        if (!empty($insertedId) && is_numeric($insertedId) && (int) $insertedId > 0) {
            $output->setSuccess(true);
            $output->setData($insertedId);
        } else {
            $output->setSuccess(false);
        }
        /*
         * Finish inserting
         */

        return $output;
    }

    public function getDefinedInputs($action = null, array $includingInputs = [], array $excludingInputs = [])
    {
        $requiredRule = new ValidatorRule('required');
        $emailRule = new ValidatorRule('email');

        $nameInput = new DatabaseInput('name');
        $nameInput->setDefaultValue('');

        $definedInputs = [
            'name'    => $nameInput,
            'email'   => new DatabaseInput('email', [$requiredRule, $emailRule]),
            'message' => new DatabaseInput('message', [$requiredRule]),
        ];

        // remove excluded ones
        return (new ArrayUtility())->filter($definedInputs, $excludingInputs);
    }

    public function getFieldsValues(array $inputs, array $definedInputs = [], $action = null)
    {
        if (empty($definedInputs)) {
            $definedInputs = $this->getDefinedInputs($action);
        }

        return (new InputUtility())->getFieldsValues($inputs, $definedInputs, $action);
    }
}
