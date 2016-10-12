<?php

namespace CodeJetter\components\user\mappers;

use CodeJetter\components\user\models\User;
use CodeJetter\core\BaseMapper;
use CodeJetter\core\io\Input;
use CodeJetter\core\io\Output;
use CodeJetter\core\security\Security;
use CodeJetter\core\security\Validator;
use CodeJetter\core\security\ValidatorRule;
use CodeJetter\core\utility\DateTimeUtility;

/**
 * Class UserMapper
 * @package CodeJetter\components\user\mappers
 */
abstract class UserMapper extends BaseMapper
{
    /**
     * @param      $email
     * @param null $parentId
     * @param null $status
     *
     * @return Output
     * @throws \Exception
     */
    public function getOneByEmail($email, $parentId = null, $status = null)
    {
        /**
         * start validating
         */
        $output = new Output();
        try {
            $requiredRule = new ValidatorRule('required');
            $emailRule = new ValidatorRule('email');

            $emailInput = new Input('email', [$requiredRule, $emailRule]);

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
                'value' => $status
            ];

            array_push($criteria, $statusCriteria);
        }

        try {
            $result = $this->getOne($criteria);
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
     * @param      $username
     * @param null $status
     * @param bool $excludeArchived
     *
     * @return Output
     * @throws \Exception
     */
    public function getOneByUsername($username, $status = null, $excludeArchived = true)
    {
        /**
         * start validating
         */
        $output = new Output();
        try {
            $requiredRule = new ValidatorRule('required');

            $usernameInput = new Input('username', [$requiredRule]);

            $validatorOutput = (new Validator([$usernameInput], ['username' => $username]))->validate();

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
                'column' => 'username',
                'value' => $username
            ]
        ];

        if ($status !== null && is_numeric($status)) {
            $criteria[] = [
                'column' => 'status',
                'value' => $status
            ];
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
     * @param array $criteria
     * @param array $inputs
     * @param array $fieldsValues
     * @param int   $limit
     * @param array $additionalDefinedInputs
     * @param bool  $excludeArchived
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
        $excludeArchived = true
    ) {
        /**
         * start validating
         */
        $output = new Output();

        try {
            $definedInputsConditions = [];
            if (!empty($inputs['password'])) {
                $definedInputsConditions = ['passwordRequired' => true];
                if (isset($inputs['passwordConfirmation'])) {
                    $definedInputsConditions['passwordConfirmation'] = $inputs['passwordConfirmation'];
                }
            }

            $updatingChildByParent = false;
            foreach ($criteria as $aCriteriaKey => $aCriteriaInfo) {
                if ($aCriteriaInfo['column'] === 'parentId' && $aCriteriaInfo['value'] > 0) {
                    $updatingChildByParent = true;
                    break;
                }
            }

            $definedInputsConditions['updatingChildByParent'] = $updatingChildByParent;

            $definedInputs = $this->getDefinedInputs($definedInputsConditions);

            /**
             * Merge the default defined inputs with the additional one
             * This allows us to use update function for different user types
             */
            $definedInputs = array_merge($definedInputs, $additionalDefinedInputs);

            $validator = new Validator($definedInputs, $inputs);
            $validatorOutput = $validator->validate();

            // Get filtered inputs
            $inputs = $validator->getFilteredInputs();

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
         * Start checking if the user exists
         */
        if (isset($inputs['id'])) {
            $foundCurrentUser = $this->getOneById($inputs['id'])->getData();

            if (empty($foundCurrentUser) || !($foundCurrentUser instanceof User)) {
                return false;
            }
        }
        /**
         * Finish checking if the user exists
         */

        /**
         * Start checking if the username exists
         */
        if (!empty($inputs['username'])) {
            if (isset($foundCurrentUser) && $foundCurrentUser instanceof User && $foundCurrentUser->getUsername() !== $inputs['username']) {
                // Username is updated, check if it does not exist
                $found = $this->getOneByUsername($inputs['username'])->getData();

                if (!empty($found) && $found instanceof User) {
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
            if (isset($foundCurrentUser) && $foundCurrentUser instanceof User && $foundCurrentUser->getEmail() !== $inputs['email']) {
                // Username is updated, check if it does not exist
                $found = $this->getOneByEmail($inputs['email'])->getData();

                if (!empty($found) && $found instanceof User) {
                    $output->setSuccess(false);
                    $output->setMessage('Email already exists');
                    return $output;
                }
            }
        }
        /**
         * Finish checking if the email exists
         */

        if (isset($inputs['name'])) {
            // add to fields values
            array_push($fieldsValues, [
                'column' => 'name',
                'value' => $inputs['name']
            ]);
        }

        // username cannot be empty
        if (!empty($inputs['username'])) {
            // add to fields values
            array_push($fieldsValues, [
                'column' => 'username',
                'value' => $inputs['username']
            ]);
        }

        // email cannot be empty
        if (!empty($inputs['email'])) {
            // add to fields values
            array_push($fieldsValues, [
                'column' => 'email',
                'value' => $inputs['email']
            ]);
        }

        if (isset($inputs['phone'])) {
            // add to fields values
            array_push($fieldsValues, [
                'column' => 'phone',
                'value' => $inputs['phone']
            ]);
        }

        if (!empty($inputs['password'])) {
            $hashedPassword = (new Security())->hashPassword($inputs['password']);

            // add to fields values
            array_push($fieldsValues, [
                'column' => 'password',
                'value' => $hashedPassword
            ]);
        }

        if (isset($inputs['status'])) {
            // add to fields values
            array_push($fieldsValues, [
                'column' => 'status',
                'value' => $inputs['status']
            ]);
        }

        if (isset($inputs['token'])) {
            // add to fields values
            array_push($fieldsValues, [
                'column' => 'token',
                'value' => $inputs['token']
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

        if (!empty($inputs['timeZone'])) {
            // add to fields values
            array_push($fieldsValues, [
                'column' => 'timeZone',
                'value' => $inputs['timeZone']
            ]);
        }

        $updatedRows = parent::update($criteria, [], $fieldsValues, $limit);

        $output->setSuccess(true);
        $output->setMessage('Updated successfully');
        $output->setData($updatedRows);

        return $output;
    }

    /**
     * @param       $id
     * @param array $inputs
     *
     * @return Output
     * @throws \Exception
     */
    public function updateById($id, array $inputs)
    {
        $criteria = [
            [
                'column' => 'id',
                'value' => $id,
                'type' => \PDO::PARAM_INT
            ]
        ];

        $inputs['id'] = $id;

        return $this->update($criteria, $inputs, [], 1);
    }

    /**
     * Contains all the common functions and checks for adding a user
     * Any specific function or check happens in the children 'add' functions
     * And this function is called after the specific ones
     * @param array $inputs
     * @param array $fieldsValues
     * @param array $additionalDefinedInputs
     *
     * @return Output
     * @throws \Exception
     */
    public function add(array $inputs, array $fieldsValues = [], $additionalDefinedInputs = [])
    {
        /**
         * Start validating common inputs
         */
        $output = new Output();
        try {
            $definedInputsConditions = ['passwordRequired' => true];
            if (isset($inputs['passwordConfirmation'])) {
                $definedInputsConditions['passwordConfirmation'] = $inputs['passwordConfirmation'];
            }

            $definedInputs = $this->getDefinedInputs($definedInputsConditions);

            /**
             * Merge the default defined inputs with the additional one
             * This allows us to use add function for different user types
             */
            $definedInputs = array_merge($definedInputs, $additionalDefinedInputs);

            if (!isset($inputs['status'])) {
                $inputs['status'] = 'active';
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
         * Finish validating common inputs
         */

        /**
         * Start checking if the email exists
         */
        $found = $this->getOneByEmail($inputs['email'])->getData();

        if (!empty($found) && $found instanceof User) {
            $output->setSuccess(false);
            $output->setMessage('Email already exists');
            return $output;
        }
        /**
         * Finish checking if the email exists
         */

        /**
         * Start checking if the username exists
         */
        if (isset($inputs['username'])) {
            $found = $this->getOneByUsername($inputs['username'])->getData();

            if (!empty($found) && $found instanceof User) {
                $output->setSuccess(false);
                $output->setMessage('Username already exists');
                return $output;
            }
        }
        /**
         * Finish checking if the username exists
         */

        /**
         * Start inserting
         */
        $commonFieldsValues = [
            [
                'column' => 'name',
                'value' => isset($inputs['name']) ? $inputs['name'] : '',
            ],
            [
                'column' => 'email',
                'value' => $inputs['email'],
            ],
            [
                'column' => 'phone',
                'value' => isset($inputs['phone']) ? $inputs['phone'] : '',
            ],
            [
                'column' => 'status',
                'value' => $inputs['status'],
            ],
        ];

        if (isset($inputs['username'])) {
            $commonFieldsValues[] = [
                'column' => 'username',
                'value' => $inputs['username'],
            ];
        }

        $fieldsValues = array_merge($commonFieldsValues, $fieldsValues);

        if (!empty($inputs['password']) || (isset($inputs['passwordRequired']) && $inputs['passwordRequired'] === true)) {
            $hashedPassword = (new Security())->hashPassword($inputs['password']);

            // add to fields values
            array_push($fieldsValues, [
                'column' => 'password',
                'value' => $hashedPassword,

            ]);
        }

        $insertedId = $this->insertOne($fieldsValues);

        if (!empty($insertedId) && is_numeric($insertedId) && (int) $insertedId > 0) {
            $output->setSuccess(true);
            $output->setMessage('Added successfully');
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
        $idRule = new ValidatorRule('id');
        $emailRule = new ValidatorRule('email');
        $requiredRule = new ValidatorRule('required');
        $usernameRule = new ValidatorRule('username');

        $whitelistRule = new ValidatorRule(
            'whitelist',
            [
                'whitelist' => (new DateTimeUtility())->getTimeZones()
            ]
        );

        $idInput = new Input('id', [$idRule]);
        $nameInput = (isset($options['nameRequired']) && $options['nameRequired'] === true) ?
            new Input('name', [$requiredRule]) : new Input('name');
        $phoneInput = new Input('phone');
        $emailInput = new Input('email', [$emailRule, $requiredRule]);
        $usernameInput = new Input('username', [$usernameRule]);
        $timezoneInput = new Input('timeZone', [$whitelistRule]);

        $definedInputs = [
            $idInput,
            $nameInput,
            $phoneInput,
            $emailInput,
            $timezoneInput,
            $usernameInput
        ];

        if ($this->viewingAsAdmin() === true ||
            (isset($options['updatingChildByParent']) && $options['updatingChildByParent'] === true)) {
            $statusesWhitelist = $this->getEnumValues('status');
            $statusesWhitelistRule = new ValidatorRule('whitelist', ['whitelist' => $statusesWhitelist]);

            $statusInput = new Input('status', [$statusesWhitelistRule]);
            $definedInputs[] = $statusInput;
        }

        if (isset($options['passwordRequired'])) {
            if (isset($options['passwordConfirmation'])) {
                $passwordRuleOptions = ['confirmation' => $options['passwordConfirmation']];
            } else {
                $passwordRuleOptions = [];
            }

            $passwordRule = new ValidatorRule('password', $passwordRuleOptions);
            $definedInputs[] = new Input('password', [$requiredRule, $passwordRule]);
        }

        return $definedInputs;
    }
}
