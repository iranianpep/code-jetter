<?php

namespace CodeJetter\components\user\mappers;

use CodeJetter\components\user\models\AdminUser;
use CodeJetter\core\io\Input;
use CodeJetter\core\io\Output;
use CodeJetter\core\security\Security;
use CodeJetter\core\security\Validator;
use CodeJetter\core\security\ValidatorRule;
use CodeJetter\core\utility\DateTimeUtility;

/**
 * Class AdminUserMapper
 * @package CodeJetter\components\user\mappers
 */
class AdminUserMapper extends UserMapper
{
    /**
     * @param array $criteria
     * @param array $inputs
     * @param int   $limit
     *
     * @return Output|void
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
            $idRule = new ValidatorRule('id');
            $emailRule = new ValidatorRule('email');
            $usernameRule = new ValidatorRule('username');
            $whitelistRule = new ValidatorRule(
                'whitelist',
                [
                    'whitelist' => (new DateTimeUtility())->getTimeZones()
                ]
            );

            $idInput = new Input('id', [$requiredRule, $idRule]);
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
        $foundCurrentUser = $this->getOneById($inputs['id'])->getData();

        // TODO this is not gonna work for batch update
        if (empty($foundCurrentUser) || !($foundCurrentUser instanceof AdminUser)) {
            return false;
        }

        if (!empty($inputs['username'])) {
            if ($foundCurrentUser->getUsername() !== $inputs['username']) {
                // Username is updated, check if it does not exist
                $found = $this->getOneByUsername($inputs['username'])->getData();

                if (!empty($found) && $found instanceof AdminUser) {
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

                if (!empty($found) && $found instanceof AdminUser) {
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

        $updatedRows = parent::update($criteria, $fieldsValues, $limit);

        $output->setSuccess(true);
        if ($updatedRows > 0) {
            $output->setMessage('Member updated successfully');
        } else {
            $output->setMessage('Nothing changed');
        }

        return $output;
    }

    /**
     * @param array $inputs
     */
    public function add(array $inputs)
    {
        // TODO: Implement add() method.
    }
}
