<?php
/**
 * Controller handling user interaction with Saint categories.
 * @author Preston St. Pierre
 * @package Saint
 */
class Saint_Controller_Category {
	/**
	 * Add a new category of given name.
	 * @param string $category New category name.
	 * @return boolean True on success, false otherwise.
	 */
	public static function addCategory($category) {
		if (Saint::getCurrentUser()->hasPermissionTo("add-category")) {
			if (Saint_Model_Category::addCategory($category))
				$success = true;
			else
				$success = false;
		} else {
			Saint::logError("User ".Saint::getCurrentUsername()." attempted to add a new category ".
				"($category) from IP $_SERVER[REMOTE_ADDR] but was denied access.",__FILE__,__LINE__);
			$success = false;
		}
		$page = Saint::getCurrentPage();
		$page->setTempLayout("system/json");
		$page->setJsonData(Saint_Controller_Category::prepareReply($success));
		return $success;
	}

	/**
	 * Delete category with given ID.
	 * @param int $catid ID of category to delete.
	 * @return boolean True on success, false otherwise.
	 */
	public static function removeCategory($catid) {
		if (Saint::getCurrentUser()->hasPermissionTo("delete-category")) {
			if (Saint_Model_Category::removeCategory($catid))
				$success = true;
			else
				$success = false;
		} else {
			Saint::logError("User ".Saint::getCurrentUsername()." attempted to remove the category ".
				"($catid) from IP $_SERVER[REMOTE_ADDR] but was denied access.",__FILE__,__LINE__);
			$success = false;
		}
		$page = Saint::getCurrentPage();
		$page->setTempLayout("system/json");
		$page->setJsonData(Saint_Controller_Category::prepareReply($success));
		return $success;
	}
	
	/**
	 * Rename category with given ID to the given name.
	 * @param int $id ID of category to rename.
	 * @param string $category New name for category.
	 * @return boolean True on success, false otherwise.
	 */
	public static function setCategory($id,$category) {
		if (Saint::getCurrentUser()->hasPermissionTo("edit-category")) {
			if (Saint_Model_Category::setCategory($id,$category))
				$success = true;
			else
				$success = false;
		} else {
			Saint::logError("User ".Saint::getCurrentUsername()." attempted to change category id $id to ".
				"'$category' from IP $_SERVER[REMOTE_ADDR] but was denied access.",__FILE__,__LINE__);
			$success = false;
		}
		$page = Saint::getCurrentPage();
		$page->setTempLayout("system/json");
		$page->setJsonData(Saint_Controller_Category::prepareReply($success));
		return $success;
	}
	
	public static function prepareReply($success = false) {
		$cats = Saint::getCategories();
		$cu = array();
		foreach ($cats as $id=>$cat) {
			$cu[] = array($id,$cat);
		}
		$json = array(
			'success' => $success,
			'categories' => $cu,
			'actionlog' => Saint::getActionLog(),
		);
		return $json;
	}
}

