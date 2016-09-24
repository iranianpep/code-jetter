<?php

use Phinx\Migration\AbstractMigration;

class CreateMemberUsersTable extends AbstractMigration
{
    public function up()
    {
        $this->execute("CREATE TABLE `cj_member_users` (
  `id` int(11) UNSIGNED NOT NULL,
  `name` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `username` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `email` varchar(150) COLLATE utf8_unicode_ci NOT NULL,
  `phone` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `password` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `parentId` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `status` enum('active','inactive','suspended') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'inactive',
  `token` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `tokenGeneratedAt` timestamp NULL DEFAULT NULL,
  `timeZone` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Australia/Melbourne',
  `createdAt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modifiedAt` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `live` enum('1') COLLATE utf8_unicode_ci DEFAULT '1',
  `archivedAt` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");

        $this->execute("ALTER TABLE `cj_member_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `Unique Username` (`username`,`live`),
  ADD UNIQUE KEY `Unique Email` (`email`,`live`);");

        $this->execute("ALTER TABLE `cj_member_users`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;");
    }

    public function down()
    {
        $this->execute("DROP TABLE `cj_member_users`");
    }
}
