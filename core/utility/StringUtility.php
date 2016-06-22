<?php

namespace CodeJetter\core\utility;

/**
 * Class StringUtility
 * @package CodeJetter\core\utility
 */
class StringUtility
{
    /**
     * Replace the last occurrence of the search string with the replacement string
     *
     * @param $search
     * @param $replace
     * @param $subject
     *
     * @return bool|mixed
     */
    public function stringLastReplace($search, $replace, $subject)
    {
        $position = strrpos($subject, $search);

        if ($position !== false) {
            return substr_replace($subject, $replace, $position, strlen($search));
        } else {
            return false;
        }
    }

    /**
     * Extract and return class name from a full namespace
     *
     * @param $namespace
     *
     * @return string
     * @throws \Exception
     */
    public function getClassNameFromNamespace($namespace)
    {
        if (empty($namespace)) {
            throw new \Exception('Namespace cannot be empty');
        }

        $namespaceParts = explode('\\', $namespace);
        return end($namespaceParts);
    }

    /**
     * Convert html special characters to html entities
     *
     * @param $string
     *
     * @return string
     */
    public function prepareForView($string)
    {
        return htmlspecialchars($string, ENT_QUOTES);
    }

    /**
     * Convert camel case to snake case e.g. AdminUser becomes admin_user
     *
     * @param $string
     *
     * @return mixed
     */
    public function camelCaseToSnakeCase($string)
    {
        return preg_replace('/([a-z])([A-Z])/', '$1_$2', $string);
    }
}
