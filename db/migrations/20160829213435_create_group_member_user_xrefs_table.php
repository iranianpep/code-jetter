<?php

use Phinx\Migration\AbstractMigration;

class CreateGroupMemberUserXrefsTable extends AbstractMigration
{
    public function up()
    {
        $this->execute("CREATE TABLE `cj_group_member_user_xrefs` (
  `id` int(11) UNSIGNED NOT NULL,
  `groupId` int(11) UNSIGNED NOT NULL,
  `memberId` int(11) UNSIGNED NOT NULL,
  `createdAt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modifiedAt` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `live` enum('1') COLLATE utf8_unicode_ci DEFAULT '1',
  `archivedAt` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");

        $this->execute('ALTER TABLE `cj_group_member_user_xrefs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `Unique relation` (`groupId`,`memberId`,`live`),
  ADD KEY `ehsan_group_member_user_xrefs_ibfk_2` (`memberId`);');

        $this->execute('ALTER TABLE `cj_group_member_user_xrefs`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;');

        $this->execute('ALTER TABLE `cj_group_member_user_xrefs`
  ADD CONSTRAINT `cj_group_member_user_xrefs_ibfk_1` FOREIGN KEY (`groupId`) REFERENCES `cj_member_groups` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cj_group_member_user_xrefs_ibfk_2` FOREIGN KEY (`memberId`) REFERENCES `cj_member_users` (`id`) ON DELETE CASCADE;');
    }

    public function down()
    {
        $this->execute('DROP TABLE `cj_group_member_user_xrefs`');
    }
}
