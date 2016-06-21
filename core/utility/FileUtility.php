<?php

namespace CodeJetter\core\utility;

/**
 * Class FileUtility
 * @package CodeJetter\core\utility
 */
class FileUtility
{
    /**
     * Validate and convert a (JSON) file content to a PHP array
     *
     * @param $filePath
     * @param bool $checkFileType Check file extension is json if it is true
     * @return array|mixed
     * @throws Exception
     */
    public function jsonFileToArray($filePath, $checkFileType = false)
    {
        if (empty($filePath)) {
            throw new \Exception('File path is empty');
        }

        if (!file_exists($filePath) || !is_file($filePath)) {
            throw new \Exception("File: '{$filePath}' does not exist or is not a file");
        }

        if ($checkFileType === true && pathinfo($filePath, PATHINFO_EXTENSION) !== 'json') {
            throw new \Exception("File: '{$filePath}' is not a json file");
        }

        $content = file_get_contents($filePath);
        $jsonArray = empty($content) ? [] : json_decode($content, true);

        if ($jsonArray === null || !is_array($jsonArray) || json_last_error() !== 0) {
            throw new \Exception("File: '{$filePath}' does not contain valid json");
        } else {
            return $jsonArray;
        }
    }
}
