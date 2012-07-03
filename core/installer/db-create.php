<?php
if ($installing) {

$sql = <<<EOT
CREATE TABLE IF NOT EXISTS `st_languages` (
	`id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
	`name` VARCHAR(255) NOT NULL,
	`title` VARCHAR(255),
	`enabled`boolean NOT NULL DEFAULT 1,
	PRIMARY KEY (`id`),
	UNIQUE INDEX(`name`)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `st_users` (
	`id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
	`username` VARCHAR(255) NOT NULL,
	`password` VARCHAR(255) NOT NULL,
	`email` VARCHAR(255) NOT NULL,
	`fname` VARCHAR(255) NOT NULL,
	`lname` VARCHAR(255) NOT NULL,
	`phone` VARCHAR(255) NOT NULL,
	`language` VARCHAR(255) NOT NULL,
	PRIMARY KEY (`id`),
	UNIQUE INDEX(`username`),
	CONSTRAINT `st_users_language_name`
	FOREIGN KEY (`language`) REFERENCES st_languages(`name`) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `st_usergroups` (
	`id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
	`userid` INTEGER UNSIGNED NOT NULL,
	`group` VARCHAR(255) NOT NULL,
	PRIMARY KEY (`id`),
	UNIQUE INDEX `st_usergroups_usergroup`(`userid`,`group`)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `st_sessions` (
	`id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
	`username` VARCHAR(255) NOT NULL,
	`sequence` VARCHAR(255) NOT NULL,
	`nonce` VARCHAR(255) NOT NULL,
	PRIMARY KEY (`id`),
	UNIQUE INDEX `st_sessions_username` (`username`),
	CONSTRAINT `st_sessions_username_username`
	FOREIGN KEY (`username`) REFERENCES st_users(`username`) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `st_config` (
	`id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
	`version` DECIMAL(6,4) NOT NULL DEFAULT 0,
	`title` VARCHAR(255) DEFAULT '',
	`owner` VARCHAR(255),
	`keywords` TEXT DEFAULT '',
	`description` TEXT DEFAULT '',
	`allow_robots` BOOLEAN NOT NULL,
	`allow_registration` BOOLEAN NOT NULL,
	PRIMARY KEY (`id`),
	CONSTRAINT `st_config_owner_username`
	FOREIGN KEY (`owner`) REFERENCES st_users(`username`) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `st_labels` (
	`id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
	`name` VARCHAR(255) NOT NULL,
	`owner` VARCHAR(255) NOT NULL,
	`revision`int UNSIGNED NOT NULL,
	`language` VARCHAR(255) NOT NULL,
	`label` MEDIUMTEXT,
	PRIMARY KEY (`id`),
	INDEX `st_labels_name` (`name`),
	CONSTRAINT `st_labels_language_name`
	FOREIGN KEY (`language`) REFERENCES st_languages(`name`) ON UPDATE CASCADE ON DELETE RESTRICT,
	CONSTRAINT `st_labels_owner_username`
	FOREIGN KEY (`owner`) REFERENCES st_users(`username`) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `st_blocktypes` (
	`id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
	`name` VARCHAR(255) NOT NULL,
	`model` VARCHAR(255) DEFAULT 'Saint_Model_Block',
	PRIMARY KEY (`id`),
	INDEX `st_blocktypes_name` (`name`)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `st_blocks` (
	`id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
	`page_id` INTEGER UNSIGNED NOT NULL DEFAULT 0,
	`blocktypeid` INTEGER UNSIGNED NOT NULL,
	`blockid` INTEGER UNSIGNED NOT NULL,
	`created` DATETIME,
	`updated` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	PRIMARY KEY (`id`),
	INDEX `st_blocks_blocktypeid` (`blocktypeid`),
	INDEX `st_blocks_blockid` (`blockid`)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `st_layouts` (
	`id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
	`enabled` BOOLEAN NOT NULL DEFAULT 1,
	`name` VARCHAR(255) NOT NULL,
	`title` VARCHAR(255) NOT NULL DEFAULT '',
	`model` VARCHAR(255) DEFAULT 'Saint_Model_Page',
	PRIMARY KEY (`id`),
	INDEX `st_layouts_name` (`name`)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `st_pages` (
	`id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
	`enabled` BOOLEAN NOT NULL DEFAULT 1,
	`name` VARCHAR(255) NOT NULL,
	`title` VARCHAR(255) NOT NULL DEFAULT '',
	`layout` VARCHAR(255) NOT NULL DEFAULT '',
	`meta_keywords` TEXT DEFAULT '',
	`meta_description` TEXT DEFAULT '',
	`allow_robots` BOOLEAN NOT NULL DEFAULT 1,
	`created` DATETIME,
	`updated` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	`parent` INTEGER UNSIGNED NOT NULL DEFAULT 0,
	`weight` INTEGER NOT NULL DEFAULT 0,
	PRIMARY KEY (`id`),
	INDEX `st_pages_name` (`name`)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `st_files` (
	`id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
	`enabled` BOOLEAN NOT NULL DEFAULT 1,
	`user` BOOLEAN NOT NULL DEFAULT 0,
	`location` VARCHAR(255) NOT NULL,
	`title` VARCHAR(255) NOT NULL,
	`keywords` TEXT NOT NULL,
	`description` TEXT NOT NULL,
	`extension` VARCHAR(255) NOT NULL,
	`type` VARCHAR(255) NOT NULL,
	`width` INTEGER UNSIGNED NOT NULL DEFAULT 0,
	`height` INTEGER UNSIGNED NOT NULL DEFAULT 0,
	`size` INTEGER UNSIGNED NOT NULL DEFAULT 0,
	PRIMARY KEY (`id`),
	UNIQUE INDEX `st_files_location` (`location`)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `st_filelabels` (
	`id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
	`name` VARCHAR(255) NOT NULL,
	`fileid` INTEGER UNSIGNED NOT NULL,
	PRIMARY KEY (`id`),
	UNIQUE INDEX `st_filelabels_name` (`name`),
	CONSTRAINT `st_filelabels_fileid_id`
	FOREIGN KEY (`fileid`) REFERENCES st_files(`id`) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `st_categories` (
	`id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
	`name` VARCHAR(255) NOT NULL,
	`title` VARCHAR(255) NOT NULL DEFAULT '',
	PRIMARY KEY (`id`),
	UNIQUE INDEX `st_categories_name` (`name`)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `st_blockcats` (
	`id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
	`catid` INTEGER UNSIGNED NOT NULL,
	`blockid` INTEGER UNSIGNED NOT NULL,
	PRIMARY KEY (`id`),
	INDEX `st_blockcats_catid` (`catid`),
	INDEX `st_blockcats_blockid` (`blockid`),
	UNIQUE INDEX `st_blockcats_catidblockid`(`catid`,`blockid`),
	CONSTRAINT `st_blockcats_catid_id`
	FOREIGN KEY (`catid`) REFERENCES st_categories(`id`) ON UPDATE CASCADE ON DELETE CASCADE,
	CONSTRAINT `st_blockcats_blockid_id`
	FOREIGN KEY (`blockid`) REFERENCES st_blocks(`id`) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `st_pagecats` (
	`id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
	`catid` INTEGER UNSIGNED NOT NULL,
	`pageid` INTEGER UNSIGNED NOT NULL,
	PRIMARY KEY (`id`),
	INDEX `st_pagecats_catid` (`catid`),
	INDEX `st_pagecats_pageid` (`pageid`),
	UNIQUE INDEX `st_pagecats_catidpageid`(`catid`,`pageid`),
	CONSTRAINT `st_pagecats_catid_id`
	FOREIGN KEY (`catid`) REFERENCES st_categories(`id`) ON UPDATE CASCADE ON DELETE CASCADE,
	CONSTRAINT `st_pagecats_pageid_id`
	FOREIGN KEY (`pageid`) REFERENCES st_pages(`id`) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `st_filecats` (
	`id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
	`catid` INTEGER UNSIGNED NOT NULL,
	`fileid` INTEGER UNSIGNED NOT NULL,
	PRIMARY KEY (`id`),
	INDEX `st_filecats_catid` (`catid`),
	INDEX `st_filecats_fileid` (`fileid`),
	UNIQUE INDEX `st_filecats_catidfileid`(`catid`,`fileid`),
	CONSTRAINT `st_filecats_catid_id`
	FOREIGN KEY (`catid`) REFERENCES st_categories(`id`) ON UPDATE CASCADE ON DELETE CASCADE,
	CONSTRAINT `st_filecats_fileid_id`
	FOREIGN KEY (`fileid`) REFERENCES st_files(`id`) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `st_shop_carts` (
	`id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
	`purchased` BOOLEAN NOT NULL DEFAULT FALSE,
	`owner` INTEGER UNSIGNED NOT NULL,
	`updated` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	PRIMARY KEY (`id`)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `st_shop_cart_products` (
	`id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
	`cartid` INTEGER UNSIGNED NOT NULL,
	`productid` INTEGER UNSIGNED NOT NULL,
	`number` INTEGER UNSIGNED NOT NULL DEFAULT 1,
	PRIMARY KEY (`id`),
	UNIQUE INDEX `st_shop_cart_products_cartidproductid` (`cartid`,`productid`),
	CONSTRAINT `st_shop_cart_products_cartid_id`
	FOREIGN KEY (`cartid`) REFERENCES st_shop_carts(`id`) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `st_shop_transactions` (
	`id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
	`userid` INTEGER UNSIGNED NOT NULL,
	`cartid` INTEGER UNSIGNED NOT NULL,
	`paypalid` VARCHAR(255) NOT NULL UNIQUE,
	`paypaluser` VARCHAR(255) NOT NULL,
	`date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`amount` DECIMAL(10,2) NOT NULL DEFAULT 0,
	PRIMARY KEY (`id`)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `st_shop_paypal_details` (
	`id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
	`paypalid` VARCHAR(255) NOT NULL UNIQUE,
	`details` LONGTEXT,
	PRIMARY KEY (`id`),
	CONSTRAINT `st_shop_paypal_details_paypalid_paypalid`
	FOREIGN KEY (`paypalid`) REFERENCES `st_shop_transactions`(`paypalid`) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `st_shop_downloads` (
	`id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
	`productid` INTEGER UNSIGNED NOT NULL,
	`linkid` VARCHAR(255) NOT NULL,
	`remaining` INTEGER UNSIGNED NOT NULL DEFAULT 1,
	`expires` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`id`)
) ENGINE=InnoDB;

INSERT INTO `st_languages` (`name`,`title`) VALUES ('english','English');
INSERT INTO `st_pages` (`name`,`title`,`layout`,`created`,`weight`) VALUES ('home','Home','blank','NOW()',-10);
INSERT INTO `st_pages` (`name`,`title`,`layout`,`created`,`allow_robots`) VALUES ('user','User','system/user-edit','NOW()',0);
INSERT INTO `st_pages` (`name`,`title`,`layout`,`created`,`allow_robots`) VALUES ('login','Login','system/user-login','NOW()',0);
INSERT INTO `st_pages` (`name`,`title`,`layout`,`created`,`allow_robots`) VALUES ('search','Search','system/search-results','NOW()',0);
INSERT INTO `st_pages` (`name`,`title`,`layout`,`created`,`allow_robots`) VALUES ('system','Saint','system/system','NOW()',0);
INSERT INTO `st_pages` (`name`,`title`,`layout`,`created`,`allow_robots`) VALUES ('filemanager','Saint','system/file-manager','NOW()',0);
INSERT INTO `st_pages` (`name`,`title`,`layout`,`created`,`allow_robots`) VALUES ('upload','Saint','system/upload','NOW()',0);
INSERT INTO `st_pages` (`name`,`title`,`layout`,`created`,`weight`) VALUES ('contact','Contact','contact','NOW()',10);
INSERT INTO `st_pages` (`name`,`title`,`layout`,`created`,`weight`) VALUES ('gallery','Gallery','gallery/gallery','NOW()',-7);
INSERT INTO `st_pages` (`name`,`title`,`layout`,`created`) VALUES ('slideshow','Slideshow','gallery/slideshow','NOW()');
INSERT INTO `st_pages` (`name`,`title`,`layout`,`created`,`allow_robots`) VALUES ('maintenance','Site Maintenance','system/maintenance','NOW()',0);
INSERT INTO `st_pages` (`name`,`title`,`layout`,`created`,`allow_robots`) VALUES ('404','Page Not Found','system/404','NOW()',0);
INSERT INTO `st_pages` (`name`,`title`,`layout`,`created`,`allow_robots`) VALUES ('sitemap.xml','Sitemap','system/sitemap','NOW()',0);
INSERT INTO `st_categories` (`name`,`title`) VALUES ('main-menu','Main Menu');
UPDATE `st_pages` as `parent` INNER JOIN `st_pages` as `child` ON `parent`.`name`='gallery' SET `child`.`parent`=`parent`.`id` WHERE `child`.`name`='slideshow';
EOT;

?>
<div id="db-create" class="info-block">
<?php 

foreach (explode(";",$sql) as $query) {
	if (!preg_match('/^\s*$/',$query)) {
		mysql_query(trim($query)) or die("Error installing Saint: " . mysql_error());
	}
}
?>
</div>

<?php 
}
?>