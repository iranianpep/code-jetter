<?php

use Phinx\Migration\AbstractMigration;

class CreateMemberGroupsTable extends AbstractMigration
{
    public function up()
    {
        $this->execute("CREATE TABLE `cj_member_groups` (
  `id` int(11) UNSIGNED NOT NULL,
  `name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `status` enum('active','inactive','suspended') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'active',
  `createdAt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modifiedAt` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `live` enum('1') COLLATE utf8_unicode_ci DEFAULT '1',
  `archivedAt` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");

        $this->execute('ALTER TABLE `cj_member_groups`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `Unique Name` (`name`,`live`);');

        $this->execute('ALTER TABLE `cj_member_groups`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;');
    }

    public function down()
    {
        $this->execute('DROP TABLE `cj_member_groups`');
    }
}
