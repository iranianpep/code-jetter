<?php
/**
 * Created by PhpStorm.
 * User: ehsanabbasi
 * Date: 18/08/15
 * Time: 11:38 PM
 */

namespace CodeJetter\core\io;

use CodeJetter\core\security\ValidatorRule;

/**
 * Class Input
 * @package CodeJetter\core\io
 */
class Input
{
    private $key;
    private $title;
    private $rules;
    private $value;
    private $defaultValue;
    private $column;
    private $PDOType;
    private $skipIfIsNotSet;

    /**
     * Input constructor.
     *
     * @param       $key
     * @param array $rules
     * @param null  $title
     */
    public function __construct($key, array $rules = [], $title = null)
    {
        $this->setKey($key);

        if (!empty($rules)) {
            $this->setRules($rules);
        }

        if ($title !== null) {
            $this->setTitle($title);
        }
    }

    /**
     * @return array
     */
    public function getRules()
    {
        return $this->rules;
    }

    /**
     * @param array $rules
     *
     * @throws \Exception
     */
    public function setRules(array $rules)
    {
        $newRules = [];
        foreach ($rules as $rule) {
            if (!$rule instanceof ValidatorRule) {
                throw new \Exception('defined rule is not instance of ValidatorRule');
            }

            $newRules[$rule->getKey()] = $rule;
        }

        $this->rules = $newRules;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        if ($this->title === null) {
            // if title is not set, make the first letter of key uppercase and return it
            return ucfirst($this->key);
        } else {
            return $this->title;
        }
    }

    /**
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
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
     * @return mixed
     */
    public function getValue()
    {
        // return the default value, in case value is not set
        if (!isset($this->value)) {
            return $this->getDefaultValue();
        }

        return $this->value;
    }

    /**
     * @param mixed $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

    /**
     * @param $key
     * @param ValidatorRule $value
     */
    public function addRule(ValidatorRule $value, $key = null)
    {
        if ($key === null) {
            $key = $value->getKey();
        }

        $rules = $this->getRules();
        $rules[$key] = $value;
        $this->setRules($rules);
    }

    /**
     * @param $key
     * @return mixed
     * @throws \Exception
     */
    public function getRule($key)
    {
        $rules = $this->getRules();

        if (!array_key_exists($key, $rules)) {
            throw new \Exception("Rule key: {$key} does not exists");
        }

        return $rules[$key];
    }

    /**
     * Return the associated column name with this input in database
     *
     * @return string
     */
    public function getColumn()
    {
        // if column name is not set, consider the key as column name
        if (!isset($this->column)) {
            return $this->getKey();
        }

        return $this->column;
    }

    /**
     * Set the associated column name with this input in database
     *
     * @param string $column
     */
    public function setColumn($column)
    {
        $this->column = $column;
    }

    /**
     * Get the default value
     * Default value is used in case the value is not set
     *
     * @return mixed
     */
    public function getDefaultValue()
    {
        return $this->defaultValue;
    }

    /**
     * Set the default value
     * Default value is used in case the value is not set
     *
     * @param mixed $defaultValue
     */
    public function setDefaultValue($defaultValue)
    {
        $this->defaultValue = $defaultValue;
    }

    /**
     * Get PDO type e.g. \PDO::PARAM_INT
     *
     * @return mixed
     */
    public function getPDOType()
    {
        return $this->PDOType;
    }

    /**
     * Set PDO type e.g. \PDO::PARAM_INT
     *
     * @param mixed $PDOType
     */
    public function setPDOType($PDOType)
    {
        $this->PDOType = $PDOType;
    }

    /**
     * Determine whether to include or exclude this input from fieldsValues
     * If default value is not set, it means that this input can be excluded from fieldsValues
     * IF external input is not set
     *
     * @return bool
     */
    public function skipIfIsNotSet()
    {
        $defaultValue = $this->getDefaultValue();

        return !isset($defaultValue) ? true : false;
    }
}
