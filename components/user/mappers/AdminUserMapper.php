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
     * @param array $inputs
     */
    public function add(array $inputs, array $fieldsValues = [], $additionalDefinedInputs = [])
    {
        // TODO: Implement add() method.
    }

    public function getDefinedInputs($case = null)
    {
        // TODO: Implement getDefinedInputs() method.
    }
}
