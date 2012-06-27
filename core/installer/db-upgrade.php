<?php

$upgrades = array();

$upgrades['1.0100'] = <<<EOT
UPDATE `st_config` SET `version`='1.0200';
EOT;

$upgrades['1.0200'] = <<<EOT
DROP TABLE IF EXISTS `st_wysiwyg`;
UPDATE `st_pages` SET `layout`='system/user-edit' WHERE `layout`='user/edit';
UPDATE `st_pages` SET `layout`='system/user-login' WHERE `layout`='user/login';
UPDATE `st_pages` SET `layout`='system/search-results' WHERE `layout`='search/results';
UPDATE `st_pages` SET `layout`='system/file-manager' WHERE `layout`='file-manager/index';
UPDATE `st_pages` SET `layout`='system/upload' WHERE `layout`='file-manager/upload';
ALTER TABLE `st_config` DROP COLUMN `blog_page`;
ALTER TABLE `st_config` DROP COLUMN `shop_page`;
ALTER TABLE `st_blocks` ADD COLUMN `page_id` INTEGER UNSIGNED NOT NULL DEFAULT 0;
ALTER TABLE `st_pages` DROP INDEX `st_pages_name`;
ALTER TABLE `st_pages` ADD INDEX `st_pages_name` (`name`);
DROP TABLE IF EXISTS `st_pageblocks`;
CREATE TABLE IF NOT EXISTS `st_layouts` (
	`id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
	`enabled` BOOLEAN NOT NULL DEFAULT 1,
	`name` VARCHAR(255) NOT NULL,
	`title` VARCHAR(255) NOT NULL DEFAULT '',
	`model` VARCHAR(255) DEFAULT 'Saint_Model_Page',
	PRIMARY KEY (`id`),
	INDEX `st_layouts_name` (`name`)
) ENGINE=InnoDB;
ALTER TABLE `st_pages` DROP COLUMN `model`;
INSERT IGNORE INTO `st_categories` (`name`) VALUES ('Main Menu');
INSERT IGNORE INTO `st_pagecats` (`catid`,`pageid`)
SELECT `c`.`id`,`p`.`id` FROM `st_categories` AS `c`, `st_pages` AS `p` WHERE `p`.`name` IN ('home','blog','shop','gallery','slideshow','contact') AND `c`.`name`='main-menu';
ALTER TABLE `st_pages` ADD COLUMN `parent` INTEGER UNSIGNED NOT NULL DEFAULT 0;
UPDATE `st_pages` as `parent` INNER JOIN `st_pages` as `child` ON `parent`.`name`='gallery' SET `child`.`parent`=`parent`.`id` WHERE `child`.`name`='slideshow';
ALTER TABLE `st_pages` ADD COLUMN `weight` INTEGER NOT NULL DEFAULT 0;
UPDATE `st_pages` SET `weight`=-10 WHERE `name`='home';
UPDATE `st_pages` SET `weight`=-9 WHERE `name`='blog';
UPDATE `st_pages` SET `weight`=-8 WHERE `name`='shop';
UPDATE `st_pages` SET `weight`=-7 WHERE `name`='gallery';
UPDATE `st_pages` SET `weight`=10 WHERE `name`='contact';
UPDATE `st_config` SET `version`='1.0300';
EOT;

foreach ($upgrades as $version=>$sql) {
	if (SAINT_DB_VERSION <= $version) {
		foreach (explode(";",$sql) as $query) {
			if (!preg_match('/^\s*$/',$query)) {
				try {
					Saint::query($query);
				} catch (Exception $e) {
					Saint::logError("There was a problem upgrading Saint from version $version to ".SAINT_CODE_VERSION.": ".
						$e->getMessage(),__FILE__,__LINE__);
				}
			}
		}
	}
}
