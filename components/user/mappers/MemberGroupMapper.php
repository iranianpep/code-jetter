<?php

namespace CodeJetter\components\user\mappers;

use CodeJetter\components\user\models\MemberGroup;
use CodeJetter\core\io\DatabaseInput;
use CodeJetter\core\io\Output;
use CodeJetter\core\security\Validator;
use CodeJetter\core\security\ValidatorRule;
use CodeJetter\core\utility\ArrayUtility;
use CodeJetter\core\utility\InputUtility;

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
        $definedInputs = [];
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

        $additionalFieldsValues = $this->getFieldsValues($inputs, $definedInputs, 'add');
        $fieldsValues = $fieldsValues + $additionalFieldsValues;

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
        $definedInputs = $this->getDefinedInputs('update', ['id']);
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
    public function update(
        array $criteria,
        array $inputs,
        array $fieldsValues,
        $limit = 0,
        $additionalDefinedInputs = [],
        $excludeArchived = true,
        $batchAction = false
    ) {
        /**
         * start validating
         */
        $output = new Output();

        $definedInputs = [];
        try {
            $action = $batchAction === true ? 'batchUpdate' : 'update';
            $definedInputs = $this->getDefinedInputs($action, ['id']);
            $definedInputs = $definedInputs + $additionalDefinedInputs;

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

        $additionalFieldsValues = $this->getFieldsValues($inputs, $definedInputs, 'update');
        $fieldsValues = $fieldsValues + $additionalFieldsValues;

        $changedRows = parent::update($criteria, [], $fieldsValues, $limit);

        if (!empty($changedRows) && is_numeric($changedRows) && (int) $changedRows > 0) {
            $output->setSuccess(true);
            $output->setMessage('Updated successfully');
            $output->setData($changedRows);
        } else {
            $output->setSuccess(false);
            $output->setMessage('Did not updated');
        }

        return $output;
    }

    public function getDefinedInputs($action = null, array $includingInputs = [], array $excludingInputs = [])
    {
        $definedInputs = [];
        // for group actions, only id is checked if it is set
        if ($action === 'batchUpdate') {
            $idRule = new ValidatorRule('id');
            $definedInputs['id'] = new DatabaseInput('id', [$idRule]);

            return $definedInputs;
        }

        $requiredRule = new ValidatorRule('required');

        if ($action === 'add' || $action === 'update') {
            $definedInputs = [
                'name' => new DatabaseInput('name', [$requiredRule]),
                'status' => new DatabaseInput('status', [$requiredRule])
            ];
        }

        if ($action === 'update') {
            $archivedAt = new DatabaseInput('archivedAt');
            $definedInputs['archivedAt'] = $archivedAt;

            $live = new DatabaseInput('live');
            $definedInputs['live'] = $live;
        }

        if ($action !== 'add' || in_array('id', $includingInputs)) {
            $idRule = new ValidatorRule('id');
            $definedInputs['id'] = new DatabaseInput('id', [$idRule, $requiredRule]);
        }

        // remove excluded ones
        return (new ArrayUtility())->filter($definedInputs, $excludingInputs);
    }

    public function getFieldsValues(array $inputs, array $definedInputs = [], $action = null)
    {
        return (new InputUtility())->getFieldsValues($inputs, $definedInputs, $action);
    }
}
