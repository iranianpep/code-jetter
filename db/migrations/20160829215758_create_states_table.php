<?php

use Phinx\Migration\AbstractMigration;

class CreateStatesTable extends AbstractMigration
{
    public function up()
    {
        $this->execute("CREATE TABLE `cj_states` (
  `id` int(11) UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `abbr` varchar(25) COLLATE utf8_unicode_ci DEFAULT NULL,
  `countryCode` char(2) COLLATE utf8_unicode_ci NOT NULL,
  `createdAt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modifiedAt` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `live` enum('1') COLLATE utf8_unicode_ci DEFAULT '1',
  `archivedAt` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");

        $this->execute("INSERT INTO `cj_states` (`id`, `name`, `abbr`, `countryCode`, `createdAt`, `modifiedAt`, `live`, `archivedAt`) VALUES
(1, 'Victoria', 'Vic', 'AU', '2016-07-18 12:09:16', NULL, '1', NULL),
(2, 'Australian Capital Territory', 'ACT', 'AU', '2016-07-18 12:09:16', NULL, '1', NULL),
(3, 'New South Wales', 'NSW', 'AU', '2016-07-18 12:09:16', NULL, '1', NULL),
(4, 'Queensland', 'Qld', 'AU', '2016-07-18 12:09:16', NULL, '1', NULL),
(5, 'South Australia', 'SA', 'AU', '2016-07-18 12:09:16', NULL, '1', NULL),
(6, 'Western Australia', 'WA', 'AU', '2016-07-18 12:09:16', NULL, '1', NULL),
(7, 'Tasmania', 'Tas', 'AU', '2016-07-18 12:09:16', NULL, '1', NULL),
(8, 'Northern Territory', 'NT', 'AU', '2016-07-18 12:09:16', NULL, '1', NULL);");

        $this->execute("ALTER TABLE `cj_states`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_countrycode_state_name` (`name`,`countryCode`),
  ADD KEY `countryCode` (`countryCode`);");

        $this->execute("ALTER TABLE `cj_states`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;");

        $this->execute("ALTER TABLE `cj_states`
  ADD CONSTRAINT `states_country_code_fk` FOREIGN KEY (`countryCode`) REFERENCES `cj_countries` (`code`) ON DELETE CASCADE ON UPDATE CASCADE;");
    }

    public function down()
    {
        $this->execute("DROP TABLE `cj_states`");
    }
}
