<?php
if ($installing) {
$sql = array();

# Site languages
$sql[] = <<<EOT
CREATE TABLE IF NOT EXISTS `st_languages` (
	`id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
	`name` VARCHAR(255) NOT NULL,
	`title` VARCHAR(255),
	`enabled`boolean NOT NULL DEFAULT 1,
	PRIMARY KEY (`id`),
	UNIQUE INDEX(`name`)
) ENGINE=InnoDB;
EOT;

$sql[] = <<<EOT
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
EOT;

$sql[] = <<<EOT
CREATE TABLE IF NOT EXISTS `st_usergroups` (
	`id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
	`userid` INTEGER UNSIGNED NOT NULL,
	`group` VARCHAR(255) NOT NULL,
	PRIMARY KEY (`id`),
	UNIQUE INDEX `st_usergroups_usergroup`(`userid`,`group`)
) ENGINE=InnoDB;
EOT;

$sql[] = <<<EOT
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
EOT;

# General site configuration, global defaults
$sql[] = <<<EOT
CREATE TABLE IF NOT EXISTS `st_config` (
	`id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
	`version` DECIMAL(6,4) NOT NULL DEFAULT 0,
	`title` VARCHAR(255) DEFAULT '',
	`owner` VARCHAR(255),
	`keywords` TEXT DEFAULT '',
	`description` TEXT DEFAULT '',
	`allow_robots` BOOLEAN NOT NULL,
	`allow_registration` BOOLEAN NOT NULL,
	`blog_page` INTEGER DEFAULT 0,
	`shop_page` INTEGER DEFAULT 0,
	PRIMARY KEY (`id`),
	CONSTRAINT `st_config_owner_username`
	FOREIGN KEY (`owner`) REFERENCES st_users(`username`) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;
EOT;

# Labels for natural language
$sql[] = <<<EOT
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
EOT;

$sql[] = <<<EOT
CREATE TABLE IF NOT EXISTS `st_blocktypes` (
	`id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
	`name` VARCHAR(255) NOT NULL,
	`model` VARCHAR(255) DEFAULT 'Saint_Model_Block',
	PRIMARY KEY (`id`),
	INDEX `st_blocktypes_name` (`name`)
) ENGINE=InnoDB;
EOT;

$sql[] = <<<EOT
CREATE TABLE IF NOT EXISTS `st_blocks` (
	`id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
	`blocktypeid` INTEGER UNSIGNED NOT NULL,
	`blockid` INTEGER UNSIGNED NOT NULL,
	PRIMARY KEY (`id`),
	INDEX `st_blocks_blocktypeid` (`blocktypeid`),
	INDEX `st_blocks_blockid` (`blockid`)
) ENGINE=InnoDB;
EOT;

# Pages
$sql[] = <<<EOT
CREATE TABLE IF NOT EXISTS `st_pages` (
	`id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
	`enabled` BOOLEAN NOT NULL DEFAULT 1,
	`name` VARCHAR(255) NOT NULL,
	`title` VARCHAR(255) NOT NULL DEFAULT '',
	`layout` VARCHAR(255) NOT NULL DEFAULT '',
	`model` VARCHAR(255) DEFAULT 'Saint_Model_Page',
	`meta_keywords` TEXT DEFAULT '',
	`meta_description` TEXT DEFAULT '',
	`allow_robots` BOOLEAN NOT NULL,
	`created` DATETIME,
	`updated` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	PRIMARY KEY (`id`),
	UNIQUE INDEX `st_pages_name` (`name`)
) ENGINE=InnoDB;
EOT;

$sql[] = <<<EOT
CREATE TABLE IF NOT EXISTS `st_pageblocks` (
	`id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
	`pageid` INTEGER UNSIGNED NOT NULL,
	`block` VARCHAR(255) NOT NULL,
	`url` VARCHAR(255) NOT NULL,
	PRIMARY KEY (`id`),
	INDEX(`pageid`),
	INDEX(`block`),
	CONSTRAINT `st_pageblocks_pageid_id`
	FOREIGN KEY (`pageid`) REFERENCES st_pages(`id`) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB;
EOT;

# Pages
$sql[] = <<<EOT
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
EOT;

$sql[] = <<<EOT
CREATE TABLE IF NOT EXISTS `st_filelabels` (
	`id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
	`name` VARCHAR(255) NOT NULL,
	`fileid` INTEGER UNSIGNED NOT NULL,
	PRIMARY KEY (`id`),
	UNIQUE INDEX `st_filelabels_name` (`name`),
	CONSTRAINT `st_filelabels_fileid_id`
	FOREIGN KEY (`fileid`) REFERENCES st_files(`id`) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB;
EOT;

$sql[] = <<<EOT
CREATE TABLE IF NOT EXISTS `st_categories` (
	`id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
	`name` VARCHAR(255) NOT NULL,
	PRIMARY KEY (`id`),
	UNIQUE INDEX `st_categories_name` (`name`)
) ENGINE=InnoDB;
EOT;

$sql[] = <<<EOT
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
EOT;

$sql[] = <<<EOT
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
EOT;

$sql[] = <<<EOT
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
EOT;

# SHOP SECTION
$sql[] = <<<EOT
CREATE TABLE IF NOT EXISTS `st_shop_carts` (
	`id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
	`purchased` BOOLEAN NOT NULL DEFAULT FALSE,
	`owner` INTEGER UNSIGNED NOT NULL,
	`updated` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	PRIMARY KEY (`id`)
) ENGINE=InnoDB;
EOT;

$sql[] = <<<EOT
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
EOT;

$sql[] = <<<EOT
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
EOT;

$sql[] = <<<EOT
CREATE TABLE IF NOT EXISTS `st_shop_paypal_details` (
	`id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
	`paypalid` VARCHAR(255) NOT NULL UNIQUE,
	`details` LONGTEXT,
	PRIMARY KEY (`id`),
	CONSTRAINT `st_shop_paypal_details_paypalid_paypalid`
	FOREIGN KEY (`paypalid`) REFERENCES `st_shop_transactions`(`paypalid`) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;
EOT;

$sql[] = <<<EOT
CREATE TABLE IF NOT EXISTS `st_shop_downloads` (
	`id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
	`productid` INTEGER UNSIGNED NOT NULL,
	`linkid` VARCHAR(255) NOT NULL,
	`remaining` INTEGER UNSIGNED NOT NULL DEFAULT 1,
	`expires` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`id`)
) ENGINE=InnoDB;
EOT;

# END SHOP SECTION

# START CONTENT SECTION

$sql[] = <<<EOT
INSERT INTO `st_languages` (`name`,`title`) VALUES ('english','English');
EOT;

$sql[] = <<<EOT
INSERT INTO `st_pages` (`name`,`title`,`layout`,`created`) VALUES ('home','Home','blank','NOW()');
EOT;

$sql[] = <<<EOT
INSERT INTO `st_pages` (`name`,`title`,`layout`,`created`) VALUES ('user','User','user/edit','NOW()');
EOT;

$sql[] = <<<EOT
INSERT INTO `st_pages` (`name`,`title`,`layout`,`created`) VALUES ('login','Login','user/login','NOW()');
EOT;

$sql[] = <<<EOT
INSERT INTO `st_pages` (`name`,`title`,`layout`,`created`) VALUES ('search','Search','search/results','NOW()');
EOT;

$sql[] = <<<EOT
INSERT INTO `st_pages` (`name`,`title`,`layout`,`created`) VALUES ('system','Saint','system/system','NOW()');
EOT;

$sql[] = <<<EOT
INSERT INTO `st_pages` (`name`,`title`,`layout`,`created`) VALUES ('filemanager','Saint','file-manager/index','NOW()');
EOT;

$sql[] = <<<EOT
INSERT INTO `st_pages` (`name`,`title`,`layout`,`created`) VALUES ('upload','Saint','file-manager/upload','NOW()');
EOT;

$sql[] = <<<EOT
INSERT INTO `st_pages` (`name`,`title`,`layout`,`created`) VALUES ('contact','Contact','contact','NOW()');
EOT;

$sql[] = <<<EOT
INSERT INTO `st_pages` (`name`,`title`,`layout`,`created`) VALUES ('gallery','Gallery','gallery/gallery','NOW()');
EOT;

$sql[] = <<<EOT
INSERT INTO `st_pages` (`name`,`title`,`layout`,`created`) VALUES ('slideshow','Slideshow','gallery/slideshow','NOW()');
EOT;

$sql[] = <<<EOT
INSERT INTO `st_pages` (`name`,`title`,`layout`,`created`) VALUES ('maintenance','Site Maintenance','system/maintenance','NOW()');
EOT;

$sql[] = <<<EOT
INSERT INTO `st_pages` (`name`,`title`,`layout`,`created`) VALUES ('404','Page Not Found','system/404','NOW()');
EOT;

# END CONTENT SECTION

?>
<div id="db-create" class="info-block">
<?php 
foreach ($sql as $query) {
	mysql_query($query) or die("Error installing Saint: " . mysql_error());
}
?>
</div>

<?php 
}
?>