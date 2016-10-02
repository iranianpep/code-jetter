<?php

namespace CodeJetter\components\user\mappers;

use CodeJetter\components\user\models\MemberGroup;
use CodeJetter\core\io\Input;
use CodeJetter\core\io\Output;
use CodeJetter\core\security\Validator;
use CodeJetter\core\security\ValidatorRule;

/**
 * Class MemberGroupMapper
 * @package CodeJetter\components\user\mappers
 */
class MemberGroupMapper extends GroupMapper
{
    /**
     * @param array $inputs
     *
     * @return Output
     * @throws \Exception
     */
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
         * Start checking if the name exists
         */
        $found = $this->getOneByName($inputs['name'])->getData();

        if (!empty($found) && $found instanceof MemberGroup) {
            $output->setSuccess(false);
            $output->setMessage('Name already exists');
            return $output;
        }
        /**
         * Finish checking if the name exists
         */

        $fieldsValues = [
            [
                'column' => 'name',
                'value' => $inputs['name']
            ],
            [
                'column' => 'status',
                'value' => $inputs['status']
            ]
        ];

        $insertedId = $this->insertOne($fieldsValues);

        if (!empty($insertedId) && is_numeric($insertedId) && (int) $insertedId > 0) {
            $output->setSuccess(true);
            $output->setMessage('Group added successfully');
            $output->setData($insertedId);
        } else {
            $output->setSuccess(false);
        }

        return $output;
    }

    /**
     * @param       $id
     * @param array $inputs
     *
     * @return Output|int
     * @throws \Exception
     */
    public function updateById($id, array $inputs)
    {
        $definedInputs = $this->getDefinedInputs('all');
        $validator = new Validator($definedInputs, $inputs);
        $validatorOutput = $validator->validate();

        if ($validatorOutput->getSuccess() !== true) {
            $output = new Output();
            $output->setSuccess(false);
            $output->setMessages($validatorOutput->getMessages());
            return $output;
        }

        $criteria = [
            [
                'column' => 'id',
                'value' => $id,
                'type' => \PDO::PARAM_INT
            ]
        ];

        return $this->update($criteria, $inputs, [], 1);
    }

    /**
     * @param array $criteria
     * @param array $inputs
     * @param int   $limit
     *
     * @return Output
     * @throws \Exception
     */
    public function update(array $criteria, array $inputs, array $fieldsValues, $limit = 0, $additionalDefinedInputs = [], $excludeArchived = true)
    {
        /**
         * start validating
         */
        $output = new Output();

        try {
            // because of group actions, only id is checked if it is set
            $idRule = new ValidatorRule('id');
            $idInput = new Input('id', [$idRule]);

            $validator = new Validator(
                [$idInput],
                $inputs
            );

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
         * finish validating
         */

        /**
         * If name AND id are set start checking if the name exists
         */
        if (isset($inputs['name']) && isset($inputs['id'])) {
            $foundCurrentGroup = $this->getOneById($inputs['id'])->getData();

            if (empty($foundCurrentGroup) || !($foundCurrentGroup instanceof MemberGroup)) {
                return false;
            }

            if ($foundCurrentGroup->getName() !== $inputs['name']) {
                $found = $this->getOneByName($inputs['name'])->getData();

                if (!empty($found) && $found instanceof MemberGroup) {
                    $output->setSuccess(false);
                    $output->setMessage('Name already exists');
                    return $output;
                }
            }
        }
        /**
         * Finish checking if the name exists
         */

        if (isset($inputs['name'])) {
            // add to fields values
            array_push($fieldsValues, [
                'column' => 'name',
                'value' => $inputs['name']
            ]);
        }

        if (isset($inputs['status'])) {
            // add to fields values
            array_push($fieldsValues, [
                'column' => 'status',
                'value' => $inputs['status']
            ]);
        }

        if (isset($inputs['archivedAt'])) {
            // add to fields values
            array_push($fieldsValues, [
                'column' => 'archivedAt',
                'value' => $inputs['archivedAt']['value'],
                'bind' => $inputs['live']['bind']
            ]);
        }

        if (isset($inputs['live'])) {
            // add to fields values
            array_push($fieldsValues, [
                'column' => 'live',
                'value' => $inputs['live']['value'],
                'bind' => $inputs['live']['bind']
            ]);
        }

        $changedRows = parent::update($criteria, [], $fieldsValues, $limit);

        if (!empty($changedRows) && is_numeric($changedRows) && (int) $changedRows > 0) {
            $output->setSuccess(true);
            $output->setMessage('Updated successfully');
            $output->setData($changedRows);
        } else {
            $output->setSuccess(false);
            $output->setMessage('Updated successfully');
        }

        return $output;
    }

    public function getDefinedInputs($case = null)
    {
        $requiredRule = new ValidatorRule('required');
        $definedInputs = [
            new Input('name', [$requiredRule]),
            new Input('status', [$requiredRule])
        ];

        if ($case === 'all') {
            $idRule = new ValidatorRule('id');
            $definedInputs[] = new Input('id', [$idRule, $requiredRule]);
        }

        return $definedInputs;
    }

    public function getFieldsValues($inputs, $case = null)
    {
        // TODO: Implement getFieldsValues() method.
    }
}
