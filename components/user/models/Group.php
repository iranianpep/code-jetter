<?php
/**
 * Created by PhpStorm.
 * User: ehsanabbasi
 * Date: 26/07/15
 * Time: 2:05 AM
 */

namespace CodeJetter\components\user\models;

use CodeJetter\core\BaseModel;

/**
 * Class Group
 * @package CodeJetter\components\user\models
 */
abstract class Group extends BaseModel
{
    protected $name;
    protected $members;
    protected $status;

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param string $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @return array
     */
    public function getMembers()
    {
        return $this->members;
    }

    /**
     * @param array $members
     */
    public function setMembers($members)
    {
        $this->members = $members;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }
}
