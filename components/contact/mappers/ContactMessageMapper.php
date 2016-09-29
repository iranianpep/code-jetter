<?php

namespace CodeJetter\components\contact\mappers;

use CodeJetter\core\BaseMapper;
use CodeJetter\core\io\Input;
use CodeJetter\core\io\Output;
use CodeJetter\core\security\Validator;
use CodeJetter\core\security\ValidatorRule;

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

    public function getDefinedInputs($case = null)
    {
        $requiredRule = new ValidatorRule('required');
        $emailRule = new ValidatorRule('email');

        return [
            new Input('name'),
            new Input('email', [$requiredRule, $emailRule]),
            new Input('message', [$requiredRule])
        ];
    }

    public function getFieldsValues($inputs, $case = null)
    {
        $name = isset($inputs['name']) ? $inputs['name'] : '';

        return [
            [
                'column' => 'name',
                'value' => $name,
            ],
            [
                'column' => 'email',
                'value' => $inputs['email'],
            ],
            [
                'column' => 'message',
                'value' => $inputs['message'],
            ],
        ];
    }
}