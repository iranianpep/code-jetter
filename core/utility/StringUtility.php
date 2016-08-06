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
        return htmlspecialchars($string);
    }

    /**
     * Convert camel case to snake case e.g. AdminUser becomes Admin_User
     *
     * @param $string
     *
     * @return mixed
     */
    public function camelCaseToSnakeCase($string)
    {
        return preg_replace('/([a-z])([A-Z])/', '$1_$2', $string);
    }

    /**
     * Remove http and www from url
     * www.example.com/test becomes example.com/test
     *
     * @param  $domain
     * @return mixed
     */
    public function removeURLProtocol($domain)
    {
        // If scheme not included, prepend it
        if (!preg_match('#^http(s)?://#', $domain)) {
            $domain = 'http://' . $domain;
        }

        $urlParts = parse_url($domain);

        $path = empty($urlParts['path']) ? '' : $urlParts['path'];
        $query = empty($urlParts['query']) ? '' : '?' . $urlParts['query'];

        // remove www
        return preg_replace('/^www\./', '', $urlParts['host'] . $path . $query);
    }
}
