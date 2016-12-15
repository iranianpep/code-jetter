<?php

namespace CodeJetter\core\utility;

use CodeJetter\core\io\DatabaseInput;

/**
 * Class InputUtility.
 */
class InputUtility
{
    /**
     * Return fieldsValues by (external or user) inputs and our defined inputs.
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

                // Determine the value
                $value = $definedInput->getValue();
                if (isset($inputs[$definedInput->getKey()]) && isset($value)) {
                    // check if value is a function
                    if (is_callable($value)) {
                        $value = $value($inputs[$definedInput->getKey()]);
                    }
                } elseif (isset($inputs[$definedInput->getKey()])) {
                    $value = $inputs[$definedInput->getKey()];
                } else {
                    // fall back to the default value
                    $value = $definedInput->getDefaultValue();
                }

                // determine bind
                $bind = $definedInput->getPDOBind();
                if (isset($inputs[$definedInput->getKey()]) && is_callable($bind)) {
                    $bind = $bind($inputs[$definedInput->getKey()]);
                }

                $fieldsValues[$definedInput->getColumn()] = [
                    'column' => $definedInput->getColumn(),
                    'value'  => $value,
                    'type'   => $definedInput->getPDOType(),
                    'bind'   => $bind,
                ];
            }
        }

        return $fieldsValues;
    }
}
