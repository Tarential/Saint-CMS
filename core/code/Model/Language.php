<?php

class Saint_Model_Language {
	
	/**
	 * Checks if $language is in the database 
	 * @param string $language Language name to check
	 * @global string Pattern matching valid language names
	 * @return boolean True if available, false otherwise
	 */
	public static function inUse($language) {
		if ($language = Saint::sanitize($language,SAINT_REG_NAME)) {
			try {
				Saint::getOne("SELECT `id` FROM `st_languages` WHERE `name`='$language'");
				return 1;
			} catch (Exception $e) {
				return 0;
			}
		} else
			return 0;
	}
	
	public static function getCurrentLanguage() {
		$user = Saint::getCurrentUser();
		return $user->getLanguage();
	}
	
	public static function getDefaultLanguage() {
		if (defined("SAINT_DEF_LANG")) {
			return SAINT_DEF_LANG;
		} else {
			try {
				$language = Saint::getOne("SELECT `name` FROM `st_languages` ORDER BY `id` ASC LIMIT 1");
				return $language;
			} catch (Exception $e) {
				Saint::logWarning("No default language found. " . $e->getMessage() . " Adding default language English.");
				try {
					$newdef = "english";
					$success = Saint::query("INSERT INTO `st_languages` (`name`,`title`) VALUES ('$newdef','English')");
					return $newdef;
				} catch (Exception $f) {
					Saint::logError("No default language found. Could not add default language. Fatal error, killing script." . $f->getMessage(),__FILE__,__LINE__);
					die();
				}
			}
		}
	}
	
	public static function addLanguage($language,$title) {
		if (Saint_Model_Language::inUse($language))
			return 1;
		else {
			if ($language = Saint::sanitize($language,SAINT_REG_NAME)) {
				try {
					Saint::getOne("INSERT INTO `st_languages` (`name`,`$title`) VALUES ('$language','$title')");
					return 1;
				} catch (Exception $e) {
					return 0;
				}
			} else
				return 0;
		}
	}

	public static function removeLanguage($language) {
		if ($language = Saint::sanitize($language,SAINT_REG_NAME)) {
			try {
				Saint::getOne("DELETE FROM `st_languages` WHERE `name`='$language'");
				return 1;
			} catch (Exception $e) {
				return 0;
			}
		} else
			return 0;
	}
	
	public static function enableLanguage($language) {
		if ($language = Saint::sanitize($language,SAINT_REG_NAME)) {
			try {
				Saint::getOne("UPDATE `st_languages` SET `enabled`=1 WHERE `name`='$language'");
				return 1;
			} catch (Exception $e) {
				return 0;
			}
		} else
			return 0;
	}
	
	public static function disableLanguage($language) {
		if ($language = Saint::sanitize($language,SAINT_REG_NAME)) {
			try {
				Saint::getOne("UPDATE `st_languages` SET `enabled`=0 WHERE `name`='$language'");
				return 1;
			} catch (Exception $e) {
				return 0;
			}
		} else
			return 0;
	}
}

