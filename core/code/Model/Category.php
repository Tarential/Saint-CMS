<?php
/**
 * Model of a category for Saint blocks and files.
 * @author Preston St. Pierre
 * @package Saint
 */
class Saint_Model_Category {
	/**
	 * Get all category names and IDs.
	 * @return string[] Category IDs (keys) and names (values).
	 */
	public static function getCategories() {
		try {
			$all = array();
			$cats = Saint::getAll("SELECT `id`,`name` FROM `st_categories`");
			foreach ($cats as $cat) {
				$all[$cat[0]] = $cat[1];
			}
			return $all;
		} catch (Exception $e) {
			if ($e->getCode()) {
				Saint::logError("Unable to select categories: ".$e->getMessage(),__FILE__,__LINE__); }
			return array();
		}
	}
	
	/**
	 * Get ID for specified category.
	 * @param string $category Name of category for which to select the ID.
	 * @return int ID of specified category, or 0 for failure.
	 */
	public static function getId($category) {
		if ($scategory = Saint::sanitize($category,SAINT_REG_NAME)) {
			try {
				return Saint::getOne("SELECT `id` FROM `st_categories` WHERE `name`='$category'");
			} catch (Exception $e) {
				Saint::logError("Unable to select id for category '$scategory': ".$e->getMessage(),__FILE__,__LINE__);
				return 0;
			}
		} else {
			Saint::logError("Invalid category name '$category'",__FILE__,__LINE__);
			return 0;
		}
	}
	
	/**
	 * Get the name for the category associated with the given ID.
	 * @param int $id ID of category for which to retrieve the name.
	 */
	public static function getName($id) {
		if ($sid = Saint::sanitize($id,SAINT_REG_ID)) {
			try {
				return Saint::getOne("SELECT `name` FROM `st_categories` WHERE `id`='$sid'");
			} catch (Exception $e) {
				Saint::logError("Unable to select name for category id '$sid': ".$e->getMessage(),__FILE__,__LINE__);
				return 0;
			}
		} else {
			Saint::logError("Invalid category id '$id'",__FILE__,__LINE__);
			return 0;
		}
	}
	
	/**
	 * Add a new category with given name.
	 * @param string $category New category name.
	 * @return int New category ID on success, 0 on failure.
	 */
	public static function addCategory($category) {
		if ($scategory = Saint::sanitize($category,SAINT_REG_NAME)) {
			try {
				Saint::query("INSERT INTO `st_categories` (`name`) VALUES ('$scategory')");
				Saint::logEvent("Added category '$scategory'.",__FILE__,__LINE__);
				return Saint::getLastInsertId();
			} catch (Exception $e) {
				Saint::logError("Unable to add category '$scategory': ".$e->getMessage(),__FILE__,__LINE__);
				return 0;
			}
		} else {
			Saint::logError("Invalid category name '$category'",__FILE__,__LINE__);
			return 0;
		}
	}

	/**
	 * Remove category with given ID.
	 * @param int $id ID of category to remove.
	 * @return boolean True for success, false for failure.
	 */
	public static function removeCategory($id) {
		$sid = Saint::sanitize($id,SAINT_REG_ID);
		if ($sid) {
			try {
				Saint::query("DELETE FROM `st_categories` WHERE `id`='$sid'");
				return 1;
			} catch (Exception $e) {
				Saint::logError("Unable to remove category id '$sid': ".$e->getMessage(),__FILE__,__LINE__);
				return 0;
			}
		} else {
			Saint::logError("Invalid category id '$id'",__FILE__,__LINE__);
			return 0;
		}
	}
	
	/**
	 * Change name of category with given ID to specified name.
	 * @param int $id ID of category to change.
	 * @param string $newname New name for category.
	 */
	public static function setCategory($id,$newname) {
		$scategory = Saint::sanitize($category,SAINT_REG_NAME);
		$sid = Saint::sanitize($id,SAINT_REG_ID);
		if ($sid && $scategory) {
			try {
				Saint::query("UPDATE `st_categories` SET `name`='$scategory' WHERE `id`='$sid'");
				return 1;
			} catch (Exception $e) {
				Saint::logError("Unable to update category '$scategory': ".$e->getMessage(),__FILE__,__LINE__);
				return 0;
			}
		} else {
			Saint::logError("Invalid category name '$category'",__FILE__,__LINE__);
			return 0;
		}
	}
}
