<?php
if ($installing) {
$sql = array();

# Site languages
$sql[] = <<<EOT
CREATE TABLE IF NOT EXISTS `st_languages` (
	`id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
	`name` varchar(255) NOT NULL,
	`title` varchar(255),
	`enabled`boolean NOT NULL DEFAULT 1,
	PRIMARY KEY (`id`),
	UNIQUE INDEX(`name`)
) ENGINE=InnoDB;
EOT;

$sql[] = <<<EOT
CREATE TABLE IF NOT EXISTS `st_users` (
	`id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
	`username` varchar(255) NOT NULL,
	`password` varchar(255) NOT NULL,
	`email` varchar(255) NOT NULL,
	`fname` varchar(255) NOT NULL,
	`lname` varchar(255) NOT NULL,
	`phone` varchar(255) NOT NULL,
	`language` varchar(255) NOT NULL,
	PRIMARY KEY (`id`),
	UNIQUE INDEX(`username`),
	FOREIGN KEY (`language`) REFERENCES st_languages(`name`) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;
EOT;

$sql[] = <<<EOT
CREATE TABLE IF NOT EXISTS `st_usergroups` (
	`id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
	`userid` INTEGER UNSIGNED NOT NULL,
	`group` varchar(255) NOT NULL,
	PRIMARY KEY (`id`),
	UNIQUE INDEX `ug`(`userid`,`group`)
) ENGINE=InnoDB;
EOT;

$sql[] = <<<EOT
CREATE TABLE IF NOT EXISTS `st_sessions` (
	`id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
	`username` varchar(255) NOT NULL,
	`sequence` varchar(255) NOT NULL,
	`nonce` varchar(255) NOT NULL,
	PRIMARY KEY (`id`),
	UNIQUE INDEX(`username`),
	FOREIGN KEY (`username`) REFERENCES st_users(`username`) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;
EOT;

# General site configuration, global defaults
$sql[] = <<<EOT
CREATE TABLE IF NOT EXISTS `st_config` (
	`id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
	`installed` boolean NOT NULL DEFAULT false,
	`title` varchar(255),
	`owner` varchar(255),
	`meta_keywords` text,
	`meta_description` text,
	`allow_robots`boolean NOT NULL,
	`allow_registration`boolean NOT NULL,
	`allow_guestedits`boolean NOT NULL,
	PRIMARY KEY (`id`),
	FOREIGN KEY (`owner`) REFERENCES st_users(`username`) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;
EOT;

# Labels for natural language
$sql[] = <<<EOT
CREATE TABLE IF NOT EXISTS `st_labels` (
	`id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
	`name` varchar(255) NOT NULL,
	`owner` varchar(255) NOT NULL,
	`revision`int UNSIGNED NOT NULL,
	`language` varchar(255) NOT NULL,
	`label` mediumtext,
	PRIMARY KEY (`id`),
	INDEX(`name`),
	FOREIGN KEY (`language`) REFERENCES st_languages(`name`) ON UPDATE CASCADE ON DELETE RESTRICT,
	FOREIGN KEY (`owner`) REFERENCES st_users(`username`) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;
EOT;

$sql[] = <<<EOT
CREATE TABLE IF NOT EXISTS `st_blocktypes` (
	`id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
	`name` varchar(255) NOT NULL,
	PRIMARY KEY (`id`),
	INDEX(`name`)
) ENGINE=InnoDB;
EOT;

$sql[] = <<<EOT
CREATE TABLE IF NOT EXISTS `st_blocks` (
	`id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
	`blocktypeid` INTEGER UNSIGNED NOT NULL,
	`blockid` INTEGER UNSIGNED NOT NULL,
	PRIMARY KEY (`id`),
	INDEX(`blocktypeid`),
	INDEX(`blockid`)
) ENGINE=InnoDB;
EOT;

$sql[] = <<<EOT
CREATE TABLE IF NOT EXISTS `st_wysiwyg` (
	`id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
	`name` varchar(255) NOT NULL,
	`content` longtext NOT NULL,
	PRIMARY KEY (`id`),
	UNIQUE INDEX(`name`)
) ENGINE=InnoDB;
EOT;

# Pages
$sql[] = <<<EOT
CREATE TABLE IF NOT EXISTS `st_pages` (
	`id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
	`enabled`boolean NOT NULL DEFAULT 1,
	`name` varchar(255) NOT NULL,
	`title` varchar(255) NOT NULL DEFAULT '',
	`layout` varchar(255) NOT NULL DEFAULT '',
	`meta_keywords` text DEFAULT '',
	`meta_description` text DEFAULT '',
	`allow_robots` boolean NOT NULL,
	`created` TIMESTAMP,
	`updated` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	PRIMARY KEY (`id`),
	UNIQUE INDEX(`name`)
) ENGINE=InnoDB;
EOT;

$sql[] = <<<EOT
CREATE TABLE IF NOT EXISTS `st_pageblocks` (
	`id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
	`pageid` INTEGER UNSIGNED NOT NULL,
	`block` varchar(255) NOT NULL,
	`url` varchar(255) NOT NULL,
	PRIMARY KEY (`id`),
	INDEX(`pageid`),
	INDEX(`block`),
	FOREIGN KEY (`pageid`) REFERENCES st_pages(`id`) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB;
EOT;

# Pages
$sql[] = <<<EOT
CREATE TABLE IF NOT EXISTS `st_files` (
	`id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
	`enabled` boolean NOT NULL DEFAULT 1,
	`user` boolean NOT NULL DEFAULT 0,
	`location` varchar(255) NOT NULL,
	`title` varchar(255) NOT NULL,
	`keywords` text NOT NULL,
	`description` text NOT NULL,
	`extension` varchar(255) NOT NULL,
	`type` varchar(255) NOT NULL,
	`width` INTEGER UNSIGNED NOT NULL DEFAULT 0,
	`height` INTEGER UNSIGNED NOT NULL DEFAULT 0,
	`size` INTEGER UNSIGNED NOT NULL DEFAULT 0,
	PRIMARY KEY (`id`),
	UNIQUE INDEX(`location`)
) ENGINE=InnoDB;
EOT;

$sql[] = <<<EOT
CREATE TABLE IF NOT EXISTS `st_filelabels` (
	`id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
	`name` varchar(255) NOT NULL,
	`fileid` INTEGER UNSIGNED NOT NULL,
	PRIMARY KEY (`id`),
	UNIQUE INDEX(`name`),
	FOREIGN KEY (`fileid`) REFERENCES st_files(`id`) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB;
EOT;

$sql[] = <<<EOT
CREATE TABLE IF NOT EXISTS `st_categories` (
	`id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
	`name` varchar(255) NOT NULL,
	PRIMARY KEY (`id`),
	UNIQUE INDEX(`name`)
) ENGINE=InnoDB;
EOT;

$sql[] = <<<EOT
CREATE TABLE IF NOT EXISTS `st_blockcats` (
	`id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
	`catid` INTEGER UNSIGNED NOT NULL,
	`blockid` INTEGER UNSIGNED NOT NULL,
	PRIMARY KEY (`id`),
	INDEX(`catid`),
	INDEX(`blockid`),
	UNIQUE INDEX `ug`(`catid`,`blockid`),
	FOREIGN KEY (`catid`) REFERENCES st_categories(`id`) ON UPDATE CASCADE ON DELETE CASCADE,
	FOREIGN KEY (`blockid`) REFERENCES st_blocks(`id`) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB;
EOT;

$sql[] = <<<EOT
CREATE TABLE IF NOT EXISTS `st_pagecats` (
	`id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
	`catid` INTEGER UNSIGNED NOT NULL,
	`pageid` INTEGER UNSIGNED NOT NULL,
	PRIMARY KEY (`id`),
	INDEX(`catid`),
	INDEX(`pageid`),
	UNIQUE INDEX `ug`(`catid`,`pageid`),
	FOREIGN KEY (`catid`) REFERENCES st_categories(`id`) ON UPDATE CASCADE ON DELETE CASCADE,
	FOREIGN KEY (`pageid`) REFERENCES st_pages(`id`) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB;
EOT;

$sql[] = <<<EOT
CREATE TABLE IF NOT EXISTS `st_filecats` (
	`id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
	`catid` INTEGER UNSIGNED NOT NULL,
	`fileid` INTEGER UNSIGNED NOT NULL,
	PRIMARY KEY (`id`),
	INDEX(`catid`),
	INDEX(`fileid`),
	UNIQUE INDEX `ug`(`catid`,`fileid`),
	FOREIGN KEY (`catid`) REFERENCES st_categories(`id`) ON UPDATE CASCADE ON DELETE CASCADE,
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
	UNIQUE INDEX `scpcp` (`cartid`,`productid`),
	FOREIGN KEY (`cartid`) REFERENCES st_shop_carts(`id`) ON UPDATE CASCADE ON DELETE CASCADE,
	FOREIGN KEY (`productid`) REFERENCES st_blocks(`id`) ON UPDATE CASCADE ON DELETE RESTRICT
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
	PRIMARY KEY (`id`),
	FOREIGN KEY (`productid`) REFERENCES st_blocks(`id`) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;
EOT;

# END SHOP SECTION

# START CONTENT SECTION

$sql[] = <<<EOT
INSERT INTO st_languages (`name`,`title`) VALUES ('english','English');
EOT;

$sql[] = <<<EOT
INSERT INTO st_pages (`name`,`title`,`layout`,`created`) VALUES ('home','Home','blank','NOW()');
EOT;

$sql[] = <<<EOT
INSERT INTO st_pages (`name`,`title`,`layout`,`created`) VALUES ('user','User','user/edit','NOW()');
EOT;

$sql[] = <<<EOT
INSERT INTO st_pages (`name`,`title`,`layout`,`created`) VALUES ('login','Login','user/login','NOW()');
EOT;

$sql[] = <<<EOT
INSERT INTO st_pages (`name`,`title`,`layout`,`created`) VALUES ('blog','Blog','blog/index','NOW()');
EOT;

$sql[] = <<<EOT
INSERT INTO st_pages (`name`,`title`,`layout`,`created`) VALUES ('feed','RSS Feed','blog/rss','NOW()');
EOT;

$sql[] = <<<EOT
INSERT INTO st_pages (`name`,`title`,`layout`,`created`) VALUES ('search','Search','search/results','NOW()');
EOT;

$sql[] = <<<EOT
INSERT INTO st_pages (`name`,`title`,`layout`,`created`) VALUES ('system','Saint','system/system','NOW()');
EOT;

$sql[] = <<<EOT
INSERT INTO st_pages (`name`,`title`,`layout`,`created`) VALUES ('filemanager','Saint','file-manager/index','NOW()');
EOT;

$sql[] = <<<EOT
INSERT INTO st_pages (`name`,`title`,`layout`,`created`) VALUES ('upload','Saint','file-manager/upload','NOW()');
EOT;

$sql[] = <<<EOT
INSERT INTO st_pages (`name`,`title`,`layout`,`created`) VALUES ('contact','Contact','contact','NOW()');
EOT;

$sql[] = <<<EOT
INSERT INTO st_pages (`name`,`title`,`layout`,`created`) VALUES ('gallery','Gallery','gallery/gallery','NOW()');
EOT;

$sql[] = <<<EOT
INSERT INTO st_pages (`name`,`title`,`layout`,`created`) VALUES ('slideshow','Slideshow','gallery/slideshow','NOW()');
EOT;

$sql[] = <<<EOT
INSERT INTO st_pages (`name`,`title`,`layout`,`created`) VALUES ('shop','Shop','shop/shop','NOW()');
EOT;

$sql[] = <<<EOT
INSERT INTO st_pages (`name`,`title`,`layout`,`created`) VALUES ('maintenance','Site Maintenance','system/maintenance','NOW()');
EOT;

$sql[] = <<<EOT
INSERT INTO st_pages (`name`,`title`,`layout`,`created`) VALUES ('404','Page Not Found','system/404','NOW()');
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