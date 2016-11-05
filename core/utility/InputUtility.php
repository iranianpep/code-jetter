<?php

namespace CodeJetter\core\utility;

use CodeJetter\core\io\DatabaseInput;

/**
 * Class InputUtility
 * @package CodeJetter\core\utility
 */
class InputUtility
{
    /**
     * Return fieldsValues by (external or user) inputs and our defined inputs
     *
     * @param array $inputs
     * @param array $definedInputs
     * @param       $case
     * @param array $tableColumnsWhitelist
     *
     * @return array
     */
    public function getFieldsValues(array $inputs, array $definedInputs, $case, array $tableColumnsWhitelist = [])
    {
        $fieldsValues = [];
        if (!empty($definedInputs)) {
            foreach ($definedInputs as $definedInput) {
                if (!$definedInput instanceof DatabaseInput) {
                    continue;
                }

                // If $tableColumnsWhitelist is set, ignore those defined inputs that are not in the whitelist
                if (!empty($tableColumnsWhitelist) && is_array($tableColumnsWhitelist)) {
                    if (!in_array($definedInput->getColumn(), $tableColumnsWhitelist)) {
                        continue;
                    }
                }

                // If the defined input is not set in the external inputs, check to see if it can be excluded or not
                if ($case == 'update' && !isset($inputs[$definedInput->getKey()])) {
                    // exclude from the list
                    continue;
                }

                $fieldsValues[] = [
                    'column' => $definedInput->getColumn(),
                    'value' => isset($inputs[$definedInput->getKey()]) ?
                        $inputs[$definedInput->getKey()] : $definedInput->getDefaultValue(),
                    'type' => $definedInput->getPDOType(),
                    'bind' => $definedInput->getPDOBind()
                ];
            }
        }

        return $fieldsValues;
    }
}
