<?php

$upgrades = array();

$upgrades['1.0100'] = <<<EOT
UPDATE `st_config` SET `version`='1.0200';
EOT;

$upgrades['1.0200'] = <<<EOT
DROP TABLE IF EXISTS `st_wysiwyg`;
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
