<?php

namespace CodeJetter\core\utility;

use CodeJetter\core\io\Input;

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
     *
     * @return array
     */
    public function getFieldsValues(array $inputs, array $definedInputs)
    {
        $fieldsValues = [];
        if (!empty($definedInputs)) {
            foreach ($definedInputs as $definedInput) {
                if (!$definedInput instanceof Input) {
                    continue;
                }

                // If the defined input is not set in the external inputs, check to see if it can be excluded or not
                if (!isset($inputs[$definedInput->getKey()]) && $definedInput->skipIfIsNotSet() === true) {
                    // exclude from the list
                    continue;
                }

                $fieldsValues[] = [
                    'column' => $definedInput->getColumn(),
                    'value' => isset($inputs[$definedInput->getKey()]) ?
                        $inputs[$definedInput->getKey()] : $definedInput->getDefaultValue()
                ];
            }
        }

        return $fieldsValues;
    }
}
