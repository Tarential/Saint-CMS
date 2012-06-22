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
