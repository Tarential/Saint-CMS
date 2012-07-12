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
		if (Saint_Model_Category::addCategory($category))
			$success = true;
		else
			$success = false;
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
		if (Saint_Model_Category::removeCategory($catid))
			$success = true;
		else
			$success = false;
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
		if (Saint_Model_Category::setCategory($id,$category))
			$success = true;
		else
			$success = false;
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

