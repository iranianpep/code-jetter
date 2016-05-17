<?php

namespace CodeJetter\components\user\mappers;

use CodeJetter\core\BaseMapper;
use CodeJetter\core\IO\Input;
use CodeJetter\core\IO\Output;
use CodeJetter\core\security\Validator;
use CodeJetter\core\security\ValidatorRule;

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

        return $this->update($criteria, $inputs, 1);
    }
}
