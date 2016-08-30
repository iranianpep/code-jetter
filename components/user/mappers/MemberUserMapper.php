<?php

namespace CodeJetter\components\user\mappers;

use CodeJetter\components\user\models\MemberUser;
use CodeJetter\components\user\models\User;
use CodeJetter\core\io\Input;
use CodeJetter\core\io\Output;
use CodeJetter\core\security\Security;
use CodeJetter\core\security\Validator;
use CodeJetter\core\security\ValidatorRule;
use CodeJetter\core\utility\DateTimeUtility;

/**
 * Class MemberUserMapper
 * @package CodeJetter\components\user\mappers
 */
class MemberUserMapper extends UserMapper
{
    /**
     * @param      $email
     * @param null $parentId
     * @param null $status
     * @param bool $excludeArchived
     *
     * @return Output
     * @throws \Exception
     */
    public function getOneByEmail($email, $parentId = null, $status = null, $excludeArchived = true)
    {
        /**
         * start validating
         */
        try {
            $output = new Output();
            $requiredRule = new ValidatorRule('required');
            $emailRule = new ValidatorRule('email');
            $rules = [
                $requiredRule,
                $emailRule
            ];

            $emailInput = new Input('email', $rules);

            $validatorOutput = (new Validator([$emailInput], ['email' => $email]))->validate();

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

        $criteria = [
            [
                'column' => 'email',
                'value' => $email
            ]
        ];

        if ($parentId !== null && is_numeric($parentId)) {
            $parentCriteria = [
                'column' => 'parentId',
                'value' => (int) $parentId,
                'type' => \PDO::PARAM_INT
            ];

            array_push($criteria, $parentCriteria);
        }

        if ($status !== null && is_numeric($status)) {
            $statusCriteria = [
                'column' => 'status',
                'value' => $status,

            ];

            array_push($criteria, $statusCriteria);
        }

        try {
            $result = $this->getOne($criteria, [], $excludeArchived);
            if (!empty($result)) {
                $output->setSuccess(true);
                $output->setData($result);
            } else {
                $output->setSuccess(false);
            }

            return $output;
        } catch (\PDOException $e) {
            (new \CodeJetter\core\ErrorHandler())->logError($e);
        }
    }

    /**
     * @param array           $criteria
     * @param string          $order
     * @param int             $start
     * @param int             $limit
     * @param bool            $returnTotalNo
     * @param MemberUser|null $userMember
     *
     * @return Output
     * @throws \Exception
     */
    public function getChildren(
        array $criteria = [],
        $order = '',
        $start = 0,
        $limit = 0,
        $returnTotalNo = false,
        MemberUser $userMember = null
    ) {
        // if user member is null, get it from the session
        if ($userMember === null) {
            $userMember =  (new MemberUser())->getLoggedIn();
        }

        $output = new Output();

        /**
         * start validating
         */
        try {
            $requiredRule = new ValidatorRule('required');
            $idRule = new ValidatorRule('id');
            $rules = [
                $requiredRule,
                $idRule
            ];

            $idInput = new Input('id', $rules);

            $validatorOutput = (new Validator([$idInput], ['id' => $userMember->getId()]))->validate();

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

        $criteria[] = [
            'column' => 'parentId',
            'value' => $userMember->getId(),
            'type' => \PDO::PARAM_INT
        ];

        try {
            $result = $this->getAll($criteria, [], $order, $start, $limit, $returnTotalNo);

            $output = new Output();

            if (!empty($result)) {
                $output->setSuccess(true);
                $output->setData($result);
            } else {
                $output->setSuccess(false);
            }

            return $output;
        } catch (\PDOException $e) {
            (new \CodeJetter\core\ErrorHandler())->logError($e);
        }
    }

    /**
     * @param                 $childId
     * @param bool            $safeDelete
     * @param MemberUser|null $userMember
     *
     * @return Output
     * @throws \Exception
     */
    public function deleteChildById($childId, $safeDelete = true, MemberUser $userMember = null)
    {
        if ($userMember === null) {
            $userMember =  (new MemberUser())->getLoggedIn();
        }

        /**
         * start validating
         */
        $output = new Output();
        try {
            $requiredRule = new ValidatorRule('required');
            $idRule = new ValidatorRule('id');
            $rules = [
                $requiredRule,
                $idRule
            ];

            $parentIdInput = new Input('parentId', $rules);
            $childIdInput = new Input('childId', $rules);

            $validator = new Validator(
                [$parentIdInput, $childIdInput],
                [
                    'parentId' => $userMember->getId(),
                    'childId'  => $childId
                ]
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

        $criteria = [
            [
                'column' => 'parentId',
                'value' => $userMember->getId(),
                'type' => \PDO::PARAM_INT
            ],
            [
                'column' => 'id',
                'value' => $childId,
                'type' => \PDO::PARAM_INT
            ],
            [
                'column' => 'status',
                'value' => 'active',

            ]
        ];

        try {
            $output->setSuccess(true);
            $this->safeDeleteOne($criteria);
            return $output;
        } catch (\PDOException $e) {
            (new \CodeJetter\core\ErrorHandler())->logError($e);
        }
    }

    /**
     * @param                 $childId
     * @param array           $inputs
     * @param MemberUser|null $userMember
     *
     * @return Output|int
     */
    public function updateChildById($childId, array $inputs, MemberUser $userMember = null)
    {
        if ($userMember === null) {
            $userMember =  (new MemberUser())->getLoggedIn();
        }

        $criteria = [
            [
                'column' => 'parentId',
                'value' => $userMember->getId(),
                'type' => \PDO::PARAM_INT
            ],
            [
                'column' => 'id',
                'value' => $childId,
                'type' => \PDO::PARAM_INT
            ]
        ];

        return $this->update($criteria, $inputs, [], 1);
    }

    /**
     * @param array $inputs
     *
     * @return Output
     * @throws \Exception
     */
    public function add(array $inputs, array $fieldsValues = [], $additionalDefinedInputs = [])
    {
        $requiredRule = new ValidatorRule('required');
        $idRule = new ValidatorRule('id', ['includingZero' => true]);

        $parentIdInput = new Input('parentId', [$requiredRule, $idRule]);
        $definedInputs = [$parentIdInput];

        $fieldsValues = [
            [
                'column' => 'parentId',
                'value' => $inputs['parentId'],
                'type' => \PDO::PARAM_INT
            ]
        ];

        return parent::add($inputs, $fieldsValues, $definedInputs);
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
        $output = new Output();

        $definedInputs = [];
        if (isset($inputs['parentId'])) {
            $requiredRule = new ValidatorRule('required');
            $idRule = new ValidatorRule('id', ['includingZero' => true]);
            $definedInputs[] = new Input('parentId', [$requiredRule, $idRule]);
        }

        $fieldsValues = [];
        if (isset($inputs['parentId'])) {
            // if parent id is 0, do not check it
            if ((int) $inputs['parentId'] !== 0) {
                // parent id cannot be the current user id
                if ((int) $inputs['parentId'] === (int) $inputs['id']) {
                    $output->setSuccess(false);
                    $output->setMessage("Parent id must be different from the current user id: '{$inputs['id']}'");
                    return $output;
                }

                // check member exists with this id
                $getOneOutput = $this->getOneById($inputs['parentId']);

                if (empty($getOneOutput->getData())) {
                    $output->setSuccess(false);
                    $output->setMessage("Parent (member) with id: '{$inputs['parentId']}' does not exist");
                    return $output;
                }
            }

            // add to fields values
            array_push($fieldsValues, [
                'column' => 'parentId',
                'value' => $inputs['parentId'],
                'type' => \PDO::PARAM_INT
            ]);
        }

        $updateOutput = parent::update($criteria, $inputs, $fieldsValues, $limit, $definedInputs);
        $updatedRows = $updateOutput->getData();

        if ($updateOutput->getSuccess() !== true) {
            $output->setSuccess(false);
            $output->setMessages($updateOutput->getMessages());
            return $output;
        }

        // if id is not passed, ignore updating groups
        if ($updateOutput->getSuccess() === true && isset($inputs['id'])) {
            // To avoid not removing the relations when nothing is chosen
            if (!isset($inputs['groups'])) {
                $inputs['groups'] = [];
            }

            // get current groups
            $foundCurrentUser = $this->getOneById($inputs['id'])->getData();

            if (!empty($foundCurrentUser) && $foundCurrentUser instanceof User) {
                $assignedGroupIds = $foundCurrentUser->getGroupIds();

                $oldGroupMemberXrefs = [];
                foreach ($assignedGroupIds as $key => $assignedGroupId) {
                    $oldGroupMemberXrefs[$key] = [
                        'groupId' => $assignedGroupId,
                        'memberId' => $inputs['id']
                    ];
                }

                $newGroupMemberXrefs = [];
                foreach ($inputs['groups'] as $group) {
                    $newGroupMemberXrefs[] = [
                        'groupId' => $group,
                        'memberId' => $inputs['id']
                    ];
                }

                $updatedGroups = (new GroupMemberUserXrefMapper())->updateXref($oldGroupMemberXrefs, $newGroupMemberXrefs);
            } else {
                $updatedGroups = 0;
            }
        } else {
            $updatedGroups = 0;
        }

        $output->setSuccess(true);
        $output->setMessage('Updated successfully');
        $output->setData($updatedRows + $updatedGroups);

        return $output;
    }
}
