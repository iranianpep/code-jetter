<?php

namespace CodeJetter\components\user\mappers;

use CodeJetter\core\BaseMapper;
use CodeJetter\core\io\Input;
use CodeJetter\core\io\Output;
use CodeJetter\core\security\Validator;
use CodeJetter\core\security\ValidatorRule;
use CodeJetter\core\utility\ArrayUtility;

/**
 * Class GroupUserXrefMapper
 * @package CodeJetter\components\user\mappers
 */
abstract class GroupUserXrefMapper extends BaseMapper
{
    /**
     * @param array $inputs
     *
     * @return Output
     * @throws \Exception
     */
    public function add(array $inputs)
    {
        /**
         * Start validating
         */
        $output = new Output();
        try {
            $requiredRule = new ValidatorRule('required');
            $idRule = new ValidatorRule('id');

            $groupIdInput = new Input('groupId', [$requiredRule, $idRule]);
            $memberIdInput = new Input('memberId', [$requiredRule, $idRule]);

            $validator = new Validator(
                [$groupIdInput, $memberIdInput],
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
         * Finish validating
         */

        $fieldsValues = [
            [
                'column' => 'groupId',
                'value' => $inputs['groupId'],
                'type' => \PDO::PARAM_INT
            ],
            [
                'column' => 'memberId',
                'value' => $inputs['memberId'],
                'type' => \PDO::PARAM_INT
            ]
        ];

        $insertedId = $this->insertOne($fieldsValues);

        if (!empty($insertedId) && is_numeric($insertedId) && (int) $insertedId > 0) {
            $output->setSuccess(true);
            $output->setMessage('Group member xref added successfully');
            $output->setData($insertedId);
        } else {
            $output->setSuccess(false);
        }

        return $output;
    }

    /**
     * @param array $inputs
     *
     * @throws \Exception
     */
    public function batchAdd(array $inputs)
    {
        $fieldsValuesCollection = [];

        foreach ($inputs as $input) {
            $fieldsValuesCollection[] = [
                [
                    'column' => 'groupId',
                    'value' => $input['groupId'],
                    'type' => \PDO::PARAM_INT
                ],
                [
                    'column' => 'memberId',
                    'value' => $input['memberId'],
                    'type' => \PDO::PARAM_INT
                ]
            ];
        }

        $this->batchInsert($fieldsValuesCollection);
    }

    /**
     * @param array $oldXrefs
     * @param array $newXrefs
     *
     * @throws \Exception
     */
    public function updateXref(array $oldXrefs, array $newXrefs)
    {
        $result = (new ArrayUtility())->arrayComparison($oldXrefs, $newXrefs);

        if (!empty($result['toBeDeleted'])) {
            // remove relations
            $criteria = [];

            $counter = 0;
            foreach ($result['toBeDeleted'] as $toBeDeletedId => $toBeDeleted) {
                if ($counter !== 0) {
                    // is not first element
                    $tempCriteria = ['logicalOperator' => 'OR'];
                } else {
                    $tempCriteria = [];
                }

                $tempCriteria['column'] = 'id';
                $tempCriteria['operator'] = '=';
                $tempCriteria['value'] = $toBeDeletedId;
                $tempCriteria['type'] = \PDO::PARAM_INT;

                $criteria[] = $tempCriteria;

                $counter++;
            }

            $this->delete($criteria);
        }

        if (!empty($result['toBeAdded'])) {
            // add relations
            $this->batchAdd($result['toBeAdded']);
        }
    }
}
