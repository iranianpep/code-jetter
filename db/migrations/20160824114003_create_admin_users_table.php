<?php

use Phinx\Migration\AbstractMigration;

class CreateAdminUsersTable extends AbstractMigration
{
    public function up()
    {
        $this->execute("CREATE TABLE `cj_admin_users` (
  `id` int(11) UNSIGNED NOT NULL,
  `name` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `username` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `email` varchar(150) COLLATE utf8_unicode_ci NOT NULL,
  `phone` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `password` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `status` enum('active','inactive','suspended') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'inactive',
  `token` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `tokenGeneratedAt` timestamp NULL DEFAULT NULL,
  `timeZone` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Australia/Melbourne',
  `createdAt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modifiedAt` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `live` enum('1') COLLATE utf8_unicode_ci DEFAULT '1',
  `archivedAt` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");

        $this->execute('INSERT INTO `cj_admin_users` (`id`, `name`, `username`, `email`, `phone`, `password`, `status`, `token`, `tokenGeneratedAt`, `timeZone`, `createdAt`, `modifiedAt`, `live`, `archivedAt`) VALUES
(1, \'admin\', \'admin\', \'iranianpep@gmail.com\', \'04123466\', \'$2y$10$xQtVTySowkdOB0marP/bkedgBKrkmd.RDwPzl.1NSbKJbr5quL4N2\', \'active\', \'8d9c024326502fd5528a37409dc58802983df249\', \'2016-08-18 10:25:10\', \'Australia/Melbourne\', \'2015-11-29 11:41:43\', \'2016-08-24 10:36:49\', \'1\', NULL);');

        $this->execute("ALTER TABLE `cj_admin_users` ADD PRIMARY KEY (`id`);");

        $this->execute("ALTER TABLE `cj_admin_users` MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT");
    }

    public function down()
    {
        $this->execute("DROP TABLE `cj_admin_users`");
    }
}
