<?php
/**
 * Created by PhpStorm.
 * User: ehsanabbasi
 * Date: 26/07/15
 * Time: 2:05 AM.
 */

namespace CodeJetter\components\user\models;

use CodeJetter\core\BaseModel;

/**
 * Class GroupUserXref.
 */
abstract class GroupUserXref extends BaseModel
{
    protected $groupId;

    /**
     * @return int
     */
    public function getGroupId()
    {
        return $this->groupId;
    }

    /**
     * @param $groupId
     *
     * @throws \Exception
     */
    public function setGroupId($groupId)
    {
        if (!is_numeric($groupId) || !($groupId > 0)) {
            throw new \Exception('Group id is not valid');
        }

        $this->groupId = (int) $groupId;
    }
}
