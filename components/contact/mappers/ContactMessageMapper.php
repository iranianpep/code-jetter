<?php

namespace CodeJetter\components\contact\mappers;

use CodeJetter\core\BaseMapper;
use CodeJetter\core\io\Input;
use CodeJetter\core\io\Output;
use CodeJetter\core\security\Validator;
use CodeJetter\core\security\ValidatorRule;
use CodeJetter\core\utility\InputUtility;

class ContactMessageMapper extends BaseMapper
{
    public function add(array $inputs, array $fieldsValues = [], $additionalDefinedInputs = [])
    {
        /**
         * Start validating
         */
        $output = new Output();
        try {
            $definedInputs = $this->getDefinedInputs();

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
         * Finish validating
         */

        /**
         * Start inserting
         */
        $fieldsValues = $this->getFieldsValues($inputs);
        $insertedId = $this->insertOne($fieldsValues);

        $output = new Output();
        if (!empty($insertedId) && is_numeric($insertedId) && (int) $insertedId > 0) {
            $output->setSuccess(true);
            $output->setData($insertedId);
        } else {
            $output->setSuccess(false);
        }
        /**
         * Finish inserting
         */

        return $output;
    }

    public function getDefinedInputs(array $options = [])
    {
        $requiredRule = new ValidatorRule('required');
        $emailRule = new ValidatorRule('email');

        $nameInput = new Input('name');
        $nameInput->setDefaultValue('');

        return [
            $nameInput,
            new Input('email', [$requiredRule, $emailRule]),
            new Input('message', [$requiredRule])
        ];
    }

    public function getFieldsValues($inputs, $case = null)
    {
        return (new InputUtility())->getFieldsValues($inputs, $this->getDefinedInputs());

        /**
         * If anything else is needed for fields values which might not be in the inputs, add it here
         */

        // Another way of returning the fields and values:
//        $name = isset($inputs['name']) ? $inputs['name'] : '';
//
//        return [
//            [
//                'column' => 'name',
//                'value' => $name,
//            ],
//            [
//                'column' => 'email',
//                'value' => $inputs['email'],
//            ],
//            [
//                'column' => 'message',
//                'value' => $inputs['message'],
//            ],
//        ];
    }
}