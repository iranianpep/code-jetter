<?php
/**
 * Created by PhpStorm.
 * User: ehsanabbasi
 * Date: 24/04/15
 * Time: 7:23 PM
 */

namespace CodeJetter\components\user\models;

use CodeJetter\components\user\mappers\GroupMemberUserXrefMapper;

/**
 * Class MemberUser
 * @package CodeJetter\components\user\models
 */
class MemberUser extends User
{
    private $parentId;
    // for the time being only MemberUser can have groups
    private $groupIds;

    /**
     * @return int
     */
    public function getParentId()
    {
        return $this->parentId;
    }

    /**
     * @param $parentId
     *
     * @throws \Exception
     */
    public function setParentId($parentId)
    {
        if (!is_numeric($parentId) || !($parentId >= 0)) {
            throw new \Exception('Parent id must be numeric');
        }

        $this->parentId = $parentId;
    }

    /**
     * @return array
     */
    public function getGroupIds()
    {
        if (isset($this->groupIds)) {
            return $this->groupIds;
        }

        $criteria = [
            [
                'column' => 'memberId',
                'value' => $this->getId(),
                'type' => \PDO::PARAM_INT
            ]
        ];

        $groupMemberXrefs = (new GroupMemberUserXrefMapper())->getAll($criteria);

        $groupIds = [];
        if (!empty($groupMemberXrefs)) {
            foreach ($groupMemberXrefs as $groupMemberXref) {
                if ($groupMemberXref instanceof GroupMemberUserXref) {
                    $groupIds[$groupMemberXref->getId()] = $groupMemberXref->getGroupId();
                }
            }
        }

        $this->setGroupIds($groupIds);

        return $this->groupIds;
    }

    /**
     * @param array $groupIds
     */
    public function setGroupIds(array $groupIds)
    {
        $this->groupIds = $groupIds;
    }
}
