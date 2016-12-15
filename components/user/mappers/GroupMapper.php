<?php

namespace CodeJetter\components\user\mappers;

use CodeJetter\core\BaseMapper;
use CodeJetter\core\io\Input;
use CodeJetter\core\io\Output;
use CodeJetter\core\security\Validator;
use CodeJetter\core\security\ValidatorRule;

/**
 * Class GroupMapper.
 */
abstract class GroupMapper extends BaseMapper
{
    /**
     * @param      $name
     * @param null $status
     * @param bool $excludeArchived
     *
     * @throws \Exception
     *
     * @return Output
     */
    public function getOneByName($name, $status = null, $excludeArchived = true)
    {
        /**
         * Start validating.
         */
        $output = new Output();
        try {
            $requiredRule = new ValidatorRule('required');

            $nameInput = new Input('name', [$requiredRule]);

            $validatorOutput = (new Validator([$nameInput], ['name' => $name]))->validate();

            if ($validatorOutput->getSuccess() !== true) {
                $output->setSuccess(false);
                $output->setMessages($validatorOutput->getMessages());

                return $output;
            }
        } catch (\Exception $e) {
            (new \CodeJetter\core\ErrorHandler())->logError($e);
        }
        /**
         * Finish validating.
         */
        $criteria = [
            [
                'column' => 'name',
                'value'  => $name,
            ],
        ];

        if ($status !== null && is_numeric($status)) {
            $criteria[] = [
                'column' => 'status',
                'value'  => $status,
            ];
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
}
