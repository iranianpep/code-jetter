<?php
/**
 * Created by PhpStorm.
 * User: ehsanabbasi
 * Date: 26/07/15
 * Time: 2:05 AM.
 */

namespace CodeJetter\components\user\models;

/**
 * Class GroupMemberUserXref.
 */
class GroupMemberUserXref extends GroupUserXref
{
    private $memberId;

    /**
     * @return int
     */
    public function getMemberId()
    {
        return $this->memberId;
    }

    /**
     * @param $memberId
     *
     * @throws \Exception
     */
    public function setMemberId($memberId)
    {
        if (!is_numeric($memberId) || !($memberId > 0)) {
            throw new \Exception('Member id is not valid');
        }

        $this->memberId = (int) $memberId;
    }
}
