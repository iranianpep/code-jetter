<?php

namespace CodeJetter\core\utility;

/**
 * Class ArrayUtility
 * @package CodeJetter\core\utility
 */
class ArrayUtility
{
    /**
     * Return the difference between two arrays
     * This is mainly designed for update functionality where we need to compare the old relations with the new ones
     *
     * @param array $oldArray
     * @param array $newArray
     *
     * @return array
     */
    public function arrayComparison(array $oldArray, array $newArray)
    {
        $toBeDeleted = [];
        $toBeAdded = [];

        if (!empty($oldArray)) {
            foreach ($oldArray as $key => $oldArrayItem) {
                if (!in_array($oldArrayItem, $newArray)) {
                    $toBeDeleted[$key] = $oldArrayItem;
                }
            }
        }

        if (!empty($newArray)) {
            foreach ($newArray as $key => $newArrayItem) {
                if (!in_array($newArrayItem, $oldArray)) {
                    $toBeAdded[$key] = $newArrayItem;
                }
            }
        }

        return ['toBeDeleted' => $toBeDeleted, 'toBeAdded' => $toBeAdded];
    }

    /**
     * Filter an array based on $excludingElements
     * Filter can be happened by array keys or values in $excludingElements
     *
     * @param array  $array
     * @param array  $excludingElements
     * @param string $filterBy Can be 'key' or 'value'
     *
     * @return array
     */
    public function filter(array $array, array $excludingElements, $filterBy = 'key')
    {
        if (empty($excludingElements)) {
            return $array;
        }

        if (!empty($array)) {
            foreach ($array as $key => $value) {
                if ($filterBy === 'key') {
                    if (in_array($key, $excludingElements)) {
                        unset($array[$key]);
                    }
                } else {
                    // filter by value
                    if (in_array($value, $excludingElements)) {
                        unset($array[$key]);
                    }
                }
            }
        }

        return $array;
    }
}
