<?php
/**
 * Created by PhpStorm.
 * User: ehsanabbasi
 * Date: 18/08/15
 * Time: 11:38 PM.
 */

namespace CodeJetter\core\io;

/**
 * Class DatabaseInput.
 */
class DatabaseInput extends Input
{
    private $column;
    private $PDOType;
    private $PDOBind;

    /**
     * Return the associated column name with this input in database
     * If column name is not set, consider the key as column name.
     *
     * @return string
     */
    public function getColumn()
    {
        if (!isset($this->column)) {
            return $this->getKey();
        }

        return $this->column;
    }

    /**
     * Set the associated column name with this input in database.
     *
     * @param string $column
     */
    public function setColumn($column)
    {
        $this->column = $column;
    }

    /**
     * Get PDO type e.g. \PDO::PARAM_INT.
     *
     * @return int
     */
    public function getPDOType()
    {
        return $this->PDOType;
    }

    /**
     * Set PDO type e.g. \PDO::PARAM_INT.
     *
     * @param int $PDOType
     */
    public function setPDOType($PDOType)
    {
        $this->PDOType = $PDOType;
    }

    /**
     * @return mixed
     */
    public function getPDOBind()
    {
        return $this->PDOBind;
    }

    /**
     * @param mixed $PDOBind
     */
    public function setPDOBind($PDOBind)
    {
        $this->PDOBind = $PDOBind;
    }
}
