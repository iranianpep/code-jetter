<?php
/**
 * Created by PhpStorm.
 * User: ehsanabbasi
 * Date: 18/08/15
 * Time: 11:33 PM
 */

namespace CodeJetter\core\security;

/**
 * Class ValidatorRule
 * @package CodeJetter\core\security
 */
class ValidatorRule
{
    private $key;
    private $message;
    private $functionArguments;

    /**
     * ValidatorRule constructor.
     *
     * @param      $key
     * @param null $functionArguments
     * @param null $message
     */
    public function __construct($key, $functionArguments = null, $message = null)
    {
        $this->key = $key;

        if ($message !== null) {
            $this->message = $message;
        }

        if ($functionArguments !== null) {
            $this->functionArguments = $functionArguments;
        }
    }

    /**
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @param string $key
     */
    public function setKey($key)
    {
        $this->key = $key;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param string $message
     */
    public function setMessage($message)
    {
        $this->message = $message;
    }

    /**
     * @return array
     */
    public function getFunctionArguments()
    {
        return $this->functionArguments;
    }

    /**
     * @param array $functionArguments
     */
    public function setFunctionArguments(array $functionArguments)
    {
        $this->functionArguments = $functionArguments;
    }
}
