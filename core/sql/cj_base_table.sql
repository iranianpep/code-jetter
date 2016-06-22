CREATE TABLE `cj_base_table` (
`id` int(11) unsigned NOT NULL,
  `createdAt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modifiedAt` timestamp NULL DEFAULT NULL,
  `live` enum('1') COLLATE utf8_unicode_ci DEFAULT '1',
  `archivedAt` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
DELIMITER //
CREATE TRIGGER `updateModifiedAt` BEFORE UPDATE ON `cj_base_table` FOR EACH ROW SET NEW.modifiedAt = CURRENT_TIMESTAMP
//
DELIMITER ;


ALTER TABLE `cj_base_table` ADD PRIMARY KEY (`id`);


ALTER TABLE `cj_base_table` MODIFY `id` int(11) unsigned NOT NULL AUTO_INCREMENT;