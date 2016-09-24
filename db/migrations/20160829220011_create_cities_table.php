<?php

use Phinx\Migration\AbstractMigration;

class CreateCitiesTable extends AbstractMigration
{
    public function up()
    {
        $this->execute("CREATE TABLE `cj_cities` (
`id` int(11) UNSIGNED NOT NULL,
`name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
`stateId` int(11) UNSIGNED NOT NULL,
`countryCode` char(2) COLLATE utf8_unicode_ci NOT NULL,
`createdAt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
`modifiedAt` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
`live` enum('1') COLLATE utf8_unicode_ci DEFAULT '1',
`archivedAt` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");

        $this->execute("INSERT INTO `cj_cities` (`id`, `name`, `stateId`, `countryCode`, `createdAt`, `modifiedAt`, `live`, `archivedAt`) VALUES
(1, 'Melbourne', 1, 'AU', '2016-07-18 12:34:02', '2016-08-24 10:38:02', '1', NULL),
(2, 'Geelong', 1, 'AU', '2016-07-18 12:34:02', NULL, '1', NULL),
(3, 'Ballarat', 1, 'AU', '2016-07-18 12:34:02', NULL, '1', NULL),
(4, 'Bendigo', 1, 'AU', '2016-07-18 12:34:02', NULL, '1', NULL),
(5, 'Shepparton-Mooroopna', 1, 'AU', '2016-07-18 12:34:02', NULL, '1', NULL),
(6, 'Melton', 1, 'AU', '2016-07-18 12:34:02', NULL, '1', NULL),
(7, 'Sunbury', 1, 'AU', '2016-07-18 12:34:02', NULL, '1', NULL),
(8, 'Pakenham', 1, 'AU', '2016-07-18 12:34:02', NULL, '1', NULL),
(9, 'Mildura', 1, 'AU', '2016-07-18 12:34:02', NULL, '1', NULL),
(10, 'Wodonga', 1, 'AU', '2016-07-18 12:34:02', NULL, '1', NULL),
(11, 'Warrnambool', 1, 'AU', '2016-07-18 12:34:02', NULL, '1', NULL),
(12, 'Traralgon', 1, 'AU', '2016-07-18 12:34:02', NULL, '1', NULL),
(13, 'Wangaratta', 1, 'AU', '2016-07-18 12:34:02', NULL, '1', NULL),
(14, 'Ocean Grove-Barwon Heads', 1, 'AU', '2016-07-18 12:34:02', NULL, '1', NULL),
(15, 'Moe-Yallourn', 1, 'AU', '2016-07-18 12:34:02', NULL, '1', NULL),
(16, 'Horsham', 1, 'AU', '2016-07-18 12:34:02', NULL, '1', NULL),
(17, 'Bacchus Marsh', 1, 'AU', '2016-07-18 12:34:02', NULL, '1', NULL),
(18, 'Morwell', 1, 'AU', '2016-07-18 12:34:02', NULL, '1', NULL),
(19, 'Torquay-Jan Juc', 1, 'AU', '2016-07-18 12:34:02', NULL, '1', NULL),
(20, 'Warragul', 1, 'AU', '2016-07-18 12:34:02', NULL, '1', NULL),
(21, 'Sale', 1, 'AU', '2016-07-18 12:34:02', NULL, '1', NULL),
(22, 'Echuca', 1, 'AU', '2016-07-18 12:34:02', NULL, '1', NULL),
(23, 'Bairnsdale', 1, 'AU', '2016-07-18 12:34:02', NULL, '1', NULL),
(24, 'Colac', 1, 'AU', '2016-07-18 12:34:02', NULL, '1', NULL),
(25, 'Lara', 1, 'AU', '2016-07-18 12:34:02', NULL, '1', NULL),
(26, 'Drysdale-Clifton Springs', 1, 'AU', '2016-07-18 12:34:02', NULL, '1', NULL),
(27, 'Portland', 1, 'AU', '2016-07-18 12:34:02', NULL, '1', NULL),
(28, 'Swan Hill', 1, 'AU', '2016-07-18 12:34:02', NULL, '1', NULL),
(29, 'Leopold', 1, 'AU', '2016-07-18 12:34:02', NULL, '1', NULL),
(30, 'Drouin', 1, 'AU', '2016-07-18 12:34:02', NULL, '1', NULL),
(31, 'Hamilton', 1, 'AU', '2016-07-18 12:34:02', NULL, '1', NULL),
(32, 'Benalla', 1, 'AU', '2016-07-18 12:34:02', NULL, '1', NULL),
(33, 'Castlemaine', 1, 'AU', '2016-07-18 12:34:02', NULL, '1', NULL),
(34, 'Gisborne', 1, 'AU', '2016-07-18 12:34:02', NULL, '1', NULL),
(35, 'Healesville', 1, 'AU', '2016-07-18 12:34:02', NULL, '1', NULL),
(36, 'Wallan', 1, 'AU', '2016-07-18 12:34:02', NULL, '1', NULL),
(37, 'Wonthaggi', 1, 'AU', '2016-07-18 12:34:02', NULL, '1', NULL),
(38, 'Maryborough', 1, 'AU', '2016-07-18 12:34:02', NULL, '1', NULL),
(39, 'Ararat', 1, 'AU', '2016-07-18 12:34:02', NULL, '1', NULL),
(40, 'Yarrawonga', 1, 'AU', '2016-07-18 12:34:02', NULL, '1', NULL),
(41, 'Kilmore', 1, 'AU', '2016-07-18 12:34:02', NULL, '1', NULL),
(42, 'Lakes Entrance', 1, 'AU', '2016-07-18 12:34:02', NULL, '1', NULL),
(43, 'Seymour', 1, 'AU', '2016-07-18 12:34:02', NULL, '1', NULL),
(44, 'Stawell', 1, 'AU', '2016-07-18 12:34:02', NULL, '1', NULL),
(45, 'Kyabram', 1, 'AU', '2016-07-18 12:34:02', NULL, '1', NULL),
(46, 'Cobram', 1, 'AU', '2016-07-18 12:34:02', NULL, '1', NULL),
(47, 'Maffra', 1, 'AU', '2016-07-18 12:34:02', NULL, '1', NULL),
(48, 'Leongatha', 1, 'AU', '2016-07-18 12:34:02', NULL, '1', NULL),
(49, 'Churchill', 1, 'AU', '2016-07-18 12:34:02', NULL, '1', NULL),
(50, 'Kyneton', 1, 'AU', '2016-07-18 12:34:02', NULL, '1', NULL);");

        $this->execute("ALTER TABLE `cj_cities`
ADD PRIMARY KEY (`id`),
ADD UNIQUE KEY `name` (`name`,`stateId`,`countryCode`),
ADD KEY `stateId` (`stateId`),
ADD KEY `countryCode` (`countryCode`);");

        $this->execute("ALTER TABLE `cj_cities`
MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;");

        $this->execute("ALTER TABLE `cj_cities`
ADD CONSTRAINT `country_code_fk` FOREIGN KEY (`countryCode`) REFERENCES `cj_countries` (`code`) ON DELETE CASCADE ON UPDATE CASCADE,
ADD CONSTRAINT `state_id_fk` FOREIGN KEY (`stateId`) REFERENCES `cj_states` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;");
    }

    public function down()
    {
        $this->execute("DROP TABLE `cj_cities`");
    }
}
