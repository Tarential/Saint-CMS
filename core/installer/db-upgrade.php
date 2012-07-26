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
	UNIQUE INDEX `st_layouts_name` (`name`)
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
ALTER TABLE `st_pages` CHANGE COLUMN `allow_robots` `allow_robots` BOOLEAN NOT NULL DEFAULT 1;
UPDATE `st_pages` SET `allow_robots`=1 WHERE `layout` NOT LIKE 'system/%';
ALTER TABLE `st_blocks` ADD COLUMN `created` DATETIME;
ALTER TABLE `st_blocks` ADD COLUMN `updated` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;
UPDATE `st_blocks` as `b`, `st_blocks_blog_post` as `p`, `st_blocktypes` as `t`
SET `b`.`updated`=`p`.`postdate`,`b`.`created`=`p`.`postdate` WHERE `b`.`blockid`=`p`.`id` AND `b`.`blocktypeid`=`t`.`id` AND `t`.`name`='blog/post';
INSERT INTO `st_pages` (`name`,`title`,`layout`,`created`,`allow_robots`) VALUES ('sitemap.xml','Sitemap','system/sitemap',NOW(),1);
INSERT INTO `st_pages` (`name`,`title`,`layout`,`created`,`allow_robots`) VALUES ('sitemap','Sitemap','system/sitemap-user-friendly',NOW(),1);
INSERT INTO `st_pages` (`name`,`title`,`layout`,`created`,`allow_robots`) VALUES ('robots.txt','Robots','system/robots',NOW(),1);
RENAME TABLE `st_blockcats` TO `st_block_categories`;
RENAME TABLE `st_blocktypes` TO `st_block_types`;
RENAME TABLE `st_filecats` TO `st_file_categories`;
RENAME TABLE `st_filelabels` TO `st_file_labels`;
RENAME TABLE `st_pagecats` TO `st_page_categories`;
RENAME TABLE `st_usergroups` TO `st_user_groups`;
ALTER TABLE `st_layouts` ADD COLUMN `show` BOOLEAN NOT NULL DEFAULT 1;
UPDATE `st_config` SET `version`='1.0300';
EOT;

$upgrades['1.0300'] = <<<EOT
ALTER TABLE `st_sessions` ADD COLUMN `client_nonce` VARCHAR(255) NOT NULL;
CREATE TABLE IF NOT EXISTS `st_login_attempts` (
	`id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
	`ip` VARCHAR(255) NOT NULL,
	`attempts` INTEGER NOT NULL DEFAULT 1,
	`last_attempt` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	PRIMARY KEY (`id`),
	INDEX `st_login_attempts_ip` (`ip`)
) ENGINE=InnoDB;
UPDATE `st_config` SET `version`='1.0400';
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
