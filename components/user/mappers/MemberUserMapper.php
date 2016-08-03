<?php

namespace CodeJetter\components\user\mappers;

use CodeJetter\components\user\models\MemberUser;
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

        return $this->update($criteria, $inputs, 1);
    }

    /**
     * @param array $inputs
     *
     * @return Output
     * @throws \Exception
     */
    public function add(array $inputs, array $fieldsValues = [])
    {
        /**
         * Start validating specific inputs to this user
         */
        $output = new Output();
        try {
            $requiredRule = new ValidatorRule('required');
            $idRule = new ValidatorRule('id', ['includingZero' => true]);

            $parentIdInput = new Input('parentId', [$requiredRule, $idRule]);
            $definedInputs = [$parentIdInput];

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
         * Finish validating specific inputs to this user
         */

        $fieldsValues = [
            [
                'column' => 'parentId',
                'value' => $inputs['parentId'],
                'type' => \PDO::PARAM_INT
            ]
        ];

        return parent::add($inputs, $fieldsValues);
    }

    /**
     * @param array $criteria
     * @param array $inputs
     * @param int   $limit
     *
     * @return Output
     * @throws \Exception
     */
    public function update(array $criteria, array $inputs, $limit = 0)
    {
        /**
         * start validating
         */
        $output = new Output();

        try {
            $requiredRule = new ValidatorRule('required');
            $idRule = new ValidatorRule('id', ['includingZero' => true]);
            $emailRule = new ValidatorRule('email');
            $usernameRule = new ValidatorRule('username');
            $whitelistRule = new ValidatorRule(
                'whitelist',
                [
                    'whitelist' => (new DateTimeUtility())->getTimeZones()
                ]
            );

            //$idInput = new Input('id', [$requiredRule, $idRule]);
            // for batch update (e.g. group actions) id should not be required
            $idInput = new Input('id', [$idRule]);
            $nameInput = new Input('name');
            $phoneInput = new Input('phone');
            $emailInput = new Input('email', [$emailRule]);
            $statusInput = new Input('status');
            $usernameInput = new Input('username', [$usernameRule]);
            $timezoneInput = new Input('timeZone', [$whitelistRule]);

            $definedInputs = [
                $idInput,
                $nameInput,
                $phoneInput,
                $emailInput,
                $statusInput,
                $usernameInput,
                $timezoneInput
            ];

            if (isset($inputs['parentId'])) {
                // update password is not empty
                $definedInputs[] = new Input('parentId', [$requiredRule, $idRule]);
            }

            if (!empty($inputs['password'])) {
                if ($inputs['password'] !== $inputs['passwordConfirmation']) {
                    $output->setSuccess(false);
                    $output->setMessage('Password does not match with Confirm password');
                    return $output;
                }

                // update password is not empty
                $passwordRule = new ValidatorRule('password');
                $definedInputs[] = new Input('password', [$passwordRule]);
            }

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
         * Start checking if the username exists
         */
        if (isset($inputs['id'])) {
            $foundCurrentUser = $this->getOneById($inputs['id'])->getData();
        }

        // Keep this commented since this is not gonna work for batch update (multiple users)
//        if (empty($foundCurrentUser) || !($foundCurrentUser instanceof MemberUser)) {
//            return false;
//        }

        if (!empty($inputs['username'])) {
            if ($foundCurrentUser->getUsername() !== $inputs['username']) {
                // Username is updated, check if it does not exist
                $found = $this->getOneByUsername($inputs['username'])->getData();

                if (!empty($found) && $found instanceof MemberUser) {
                    $output->setSuccess(false);
                    $output->setMessage('Username already exists');
                    return $output;
                }
            }
        }
        /**
         * Finish checking if the username exists
         */

        /**
         * Start checking if the email exists
         */
        if (!empty($inputs['email'])) {
            if ($foundCurrentUser->getEmail() !== $inputs['email']) {
                // Username is updated, check if it does not exist
                $found = $this->getOneByEmail($inputs['email'])->getData();

                if (!empty($found) && $found instanceof MemberUser) {
                    $output->setSuccess(false);
                    $output->setMessage('Email already exists');
                    return $output;
                }
            }
        }
        /**
         * Finish checking if the email exists
         */

        $fieldsValues = [];

        if (isset($inputs['name'])) {
            // add to fields values
            array_push($fieldsValues, [
                'column' => 'name',
                'value' => $inputs['name'],

            ]);
        }

        // username cannot be empty
        if (!empty($inputs['username'])) {
            // add to fields values
            array_push($fieldsValues, [
                'column' => 'username',
                'value' => $inputs['username'],

            ]);
        }

        // email cannot be empty
        if (!empty($inputs['email'])) {
            // add to fields values
            array_push($fieldsValues, [
                'column' => 'email',
                'value' => $inputs['email'],

            ]);
        }

        if (isset($inputs['phone'])) {
            // add to fields values
            array_push($fieldsValues, [
                'column' => 'phone',
                'value' => $inputs['phone'],

            ]);
        }

        if (!empty($inputs['password'])) {
            $hashedPassword = (new Security())->hashPassword($inputs['password']);

            // add to fields values
            array_push($fieldsValues, [
                'column' => 'password',
                'value' => $hashedPassword,

            ]);
        }

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

        if (isset($inputs['status'])) {
            // add to fields values
            array_push($fieldsValues, [
                'column' => 'status',
                'value' => $inputs['status'],

            ]);
        }

        if (isset($inputs['token'])) {
            // add to fields values
            array_push($fieldsValues, [
                'column' => 'token',
                'value' => $inputs['token'],

            ]);

            array_push($fieldsValues, [
                'column' => 'tokenGeneratedAt',
                'value' => 'NOW()',
                'bind' => false
            ]);
        }

        if (isset($inputs['archivedAt'])) {
            // add to fields values
            array_push($fieldsValues, [
                'column' => 'archivedAt',
                'value' => $inputs['archivedAt']['value'],
                'bind' => $inputs['archivedAt']['bind']
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

        if (!empty($inputs['timeZone'])) {
            // add to fields values
            array_push($fieldsValues, [
                'column' => 'timeZone',
                'value' => $inputs['timeZone']
            ]);
        }

        // if id is not passed, ignore updating groups
        if (isset($inputs['id'])) {
            // To avoid not removing the relations when nothing is chosen
            if (!isset($inputs['groups'])) {
                $inputs['groups'] = [];
            }

            // get current groups
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

        $updatedRows = parent::update($criteria, $fieldsValues, $limit);

        $output->setSuccess(true);
        if ($updatedRows + $updatedGroups > 0) {
            $output->setData($updatedRows + $updatedGroups);
            $output->setMessage('Member updated successfully');
        } else {
            $output->setMessage('Nothing changed');
        }

        return $output;
    }
}
