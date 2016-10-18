<?php

namespace CodeJetter\core\utility;

/**
 * Class StringUtility
 * @package CodeJetter\core\utility
 */
class StringUtility
{
    public $plural = [
        '/(quiz)$/i'               => "$1zes",
        '/^(ox)$/i'                => "$1en",
        '/([m|l])ouse$/i'          => "$1ice",
        '/(matr|vert|ind)ix|ex$/i' => "$1ices",
        '/(x|ch|ss|sh)$/i'         => "$1es",
        '/([^aeiouy]|qu)y$/i'      => "$1ies",
        '/(hive)$/i'               => "$1s",
        '/(?:([^f])fe|([lr])f)$/i' => "$1$2ves",
        '/(shea|lea|loa|thie)f$/i' => "$1ves",
        '/sis$/i'                  => "ses",
        '/([ti])um$/i'             => "$1a",
        '/(tomat|potat|ech|her|vet)o$/i'=> "$1oes",
        '/(bu)s$/i'                => "$1ses",
        '/(alias)$/i'              => "$1es",
        '/(octop)us$/i'            => "$1i",
        '/(ax|test)is$/i'          => "$1es",
        '/(us)$/i'                 => "$1es",
        '/s$/i'                    => "s",
        '/$/'                      => "s"
    ];

    public $irregular = [
        'move'   => 'moves',
        'foot'   => 'feet',
        'goose'  => 'geese',
        'sex'    => 'sexes',
        'child'  => 'children',
        'man'    => 'men',
        'tooth'  => 'teeth',
        'person' => 'people',
        'valve'  => 'valves'
    ];

    public $uncountable = [
        'sheep',
        'fish',
        'deer',
        'series',
        'species',
        'money',
        'rice',
        'information',
        'equipment'
    ];

    public $singular = [
        '/(quiz)zes$/i'             => "$1",
        '/(matr)ices$/i'            => "$1ix",
        '/(vert|ind)ices$/i'        => "$1ex",
        '/^(ox)en$/i'               => "$1",
        '/(alias)es$/i'             => "$1",
        '/(octop|vir)i$/i'          => "$1us",
        '/(cris|ax|test)es$/i'      => "$1is",
        '/(shoe)s$/i'               => "$1",
        '/(o)es$/i'                 => "$1",
        '/(bus)es$/i'               => "$1",
        '/([m|l])ice$/i'            => "$1ouse",
        '/(x|ch|ss|sh)es$/i'        => "$1",
        '/(m)ovies$/i'              => "$1ovie",
        '/(s)eries$/i'              => "$1eries",
        '/([^aeiouy]|qu)ies$/i'     => "$1y",
        '/([lr])ves$/i'             => "$1f",
        '/(tive)s$/i'               => "$1",
        '/(hive)s$/i'               => "$1",
        '/(li|wi|kni)ves$/i'        => "$1fe",
        '/(shea|loa|lea|thie)ves$/i'=> "$1f",
        '/(^analy)ses$/i'           => "$1sis",
        '/((a)naly|(b)a|(d)iagno|(p)arenthe|(p)rogno|(s)ynop|(t)he)ses$/i'  => "$1$2sis",
        '/([ti])a$/i'               => "$1um",
        '/(n)ews$/i'                => "$1ews",
        '/(h|bl)ouses$/i'           => "$1ouse",
        '/(corpse)s$/i'             => "$1",
        '/(us)es$/i'                => "$1",
        '/s$/i'                     => ""
    ];

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
     * @param $string
     * @param $prefix
     *
     * @return string
     */
    public function removePrefix($string, $prefix)
    {
        if (substr($string, 0, strlen($prefix)) == $prefix) {
            $string = substr($string, strlen($prefix));
        }

        return $string;
    }

    /**
     * @param $string
     * @param $suffix
     *
     * @return bool|mixed
     */
    public function removeSuffix($string, $suffix)
    {
        $filtered = $this->stringLastReplace($suffix, '', $string);
        return $filtered !== false ? $filtered : $string;
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
     * Convert singular word to plural
     *
     * @param $string
     *
     * @return mixed
     */
    public function singularToPlural($string)
    {
        // save some time in the case that singular and plural are the same
        if (in_array(strtolower($string), $this->uncountable)) {
            return $string;
        }

        // check for irregular singular forms
        foreach ($this->irregular as $pattern => $result) {
            $pattern = '/' . $pattern . '$/i';

            if (preg_match($pattern, $string)) {
                return preg_replace($pattern, $result, $string);
            }
        }

        // check for matches using regular expressions
        foreach ($this->plural as $pattern => $result) {
            if (preg_match($pattern, $string)) {
                return preg_replace($pattern, $result, $string);
            }
        }

        return $string;
    }

    /**
     * Convert plural word to singular
     *
     * @param $string
     *
     * @return mixed
     */
    public function pluralToSingular($string)
    {
        // save some time in the case that singular and plural are the same
        if (in_array(strtolower($string), $this->uncountable)) {
            return $string;
        }

        // check for irregular plural forms
        foreach ($this->irregular as $result => $pattern) {
            $pattern = '/' . $pattern . '$/i';

            if (preg_match($pattern, $string)) {
                return preg_replace($pattern, $result, $string);
            }
        }

        // check for matches using regular expressions
        foreach ($this->singular as $pattern => $result) {
            if (preg_match($pattern, $string)) {
                return preg_replace($pattern, $result, $string);
            }
        }

        return $string;
    }

    /**
     * Convert snake case to camel case e.g. admin_user becomes AdminUser
     *
     * @param $string
     *
     * @return mixed
     */
    public function snakeCaseToCamelCase($string)
    {
        return str_replace(' ', '', ucwords(str_replace('_', ' ', $string)));
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

    /**
     * @param $json
     *
     * @return array|mixed
     * @throws \Exception
     */
    public function jsonToArray($json)
    {
        $array = empty($json) ? [] : json_decode($json, true);

        if ($array === null || !is_array($array) || json_last_error() !== 0) {
            throw new \Exception('Invalid JSON content');
        }

        return $array;
    }
}
