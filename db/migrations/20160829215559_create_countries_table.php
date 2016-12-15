<?php

use Phinx\Migration\AbstractMigration;

class CreateCountriesTable extends AbstractMigration
{
    public function up()
    {
        $this->execute("CREATE TABLE `cj_countries` (
  `id` int(11) UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `code` char(2) COLLATE utf8_unicode_ci NOT NULL,
  `createdAt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modifiedAt` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `live` enum('1') COLLATE utf8_unicode_ci DEFAULT '1',
  `archivedAt` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");

        $this->execute("INSERT INTO `cj_countries` (`id`, `name`, `code`, `createdAt`, `modifiedAt`, `live`, `archivedAt`) VALUES
(1, 'Australia', 'AU', '2016-07-18 12:08:39', NULL, '1', NULL);");

        $this->execute('ALTER TABLE `cj_countries`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);');

        $this->execute('ALTER TABLE `cj_countries`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;');
    }

    public function down()
    {
        $this->execute('DROP TABLE `cj_countries`');
    }
}
