<?php

use Phinx\Migration\AbstractMigration;

class CreateContactMessagesTable extends AbstractMigration
{
    public function up()
    {
        $this->execute("CREATE TABLE `cj_contact_messages` (
  `id` int(11) UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `message` text COLLATE utf8_unicode_ci NOT NULL,
  `createdAt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modifiedAt` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `live` enum('1') COLLATE utf8_unicode_ci DEFAULT '1',
  `archivedAt` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");

        $this->execute("ALTER TABLE `cj_contact_messages`
  ADD PRIMARY KEY (`id`);");

        $this->execute("ALTER TABLE `cj_contact_messages`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;");
    }

    public function down()
    {
        $this->execute("DROP TABLE `cj_contact_messages`");
    }
}
