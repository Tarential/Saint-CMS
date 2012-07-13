<?php
/**
 * Model of a natural language within the Saint framework.
 * @author Preston St. Pierre
 * @package Saint
 */
class Saint_Model_Language {
	/**
	 * Checks if given language is in the database.
	 * @param string $language Name of language to check.
	 * @return boolean True if in use, false otherwise.
	 */
	public static function inUse($language) {
		if ($language = Saint::sanitize($language,SAINT_REG_LANG_NAME)) {
			try {
				Saint::getOne("SELECT `id` FROM `st_languages` WHERE `name`='$language'");
				return 1;
			} catch (Exception $e) {
				return 0;
			}
		} else
			return 0;
	}
	
	/**
	 * Get the active language.
	 * @return string Name of the active language.
	 */
	public static function getCurrentLanguage() {
		$user = Saint::getCurrentUser();
		return $user->getLanguage();
	}
	
	/**
	 * Get the site's default language.
	 * @return string Name of the default language.
	 */
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
	
	/**
	 * Add a new language to the database.
	 * @param string $language Name of language to add.
	 * @param string $title Description of language.
	 * @return boolean True on success, false on failure.
	 */
	public static function addLanguage($language,$title) {
		if (Saint_Model_Language::inUse($language))
			return 1;
		else {
			if ($language = Saint::sanitize($language,SAINT_REG_LANG_NAME)) {
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

	/**
	 * Remove language from database.
	 * @param string $language Name of language to remove.
	 * @return boolean True on success, false on failure.
	 */
	public static function removeLanguage($language) {
		if ($language = Saint::sanitize($language,SAINT_REG_LANG_NAME)) {
			try {
				Saint::getOne("DELETE FROM `st_languages` WHERE `name`='$language'");
				return 1;
			} catch (Exception $e) {
				return 0;
			}
		} else
			return 0;
	}
	
	/**
	 * Flag given language as enabled.
	 * @param string $language Name of language to enable.
	 * @return boolean True on success, false on failure.
	 */
	public static function enableLanguage($language) {
		if ($language = Saint::sanitize($language,SAINT_REG_LANG_NAME)) {
			try {
				Saint::getOne("UPDATE `st_languages` SET `enabled`=1 WHERE `name`='$language'");
				return 1;
			} catch (Exception $e) {
				return 0;
			}
		} else
			return 0;
	}
	
	/**
	 * Flag given language as disabled.
	 * @param string $language Name of language to disable.
	 * @return boolean True on success, false on failure.
	 */
	public static function disableLanguage($language) {
		if ($language = Saint::sanitize($language,SAINT_REG_LANG_NAME)) {
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

