<?php

class Saint_Controller_Category {
	public static function addCategory($category) {
		if (Saint::getCurrentUser()->hasPermissionTo("add-category")) {
			if (Saint_Model_Category::addCategory($category))
				$success = true;
			else
				$success = false;
		} else {
			Saint::logError("User ".Saint::getCurrentUsername()." attempted to add a new category ($category) but was denied access.",__FILE__,__LINE__);
			$success = false;
		}
		$page = Saint::getCurrentPage();
		$page->setTempLayout("system/json");
		$page->jsondata = array(
			'success' => $success,
			'actionlog' => Saint::getActionLog(),
		);
		return $success;
	}

	public static function removeCategory($catid) {
		if (Saint::getCurrentUser()->hasPermissionTo("delete-category")) {
			if (Saint_Model_Category::removeCategory($catid))
				$success = true;
			else
				$success = false;
		} else {
			Saint::logError("User ".Saint::getCurrentUsername()." attempted to remove the category ($catid) but was denied access.",__FILE__,__LINE__);
			$success = false;
		}
		$page = Saint::getCurrentPage();
		$page->setTempLayout("system/json");
		$page->jsondata = array(
			'success' => $success,
			'actionlog' => Saint::getActionLog(),
		);
		return $success;
	}
	
	public static function setCategory($id,$category) {
		if (Saint::getCurrentUser()->hasPermissionTo("edit-category")) {
			if (Saint_Model_Category::setCategory($id,$category))
				$success = true;
			else
				$success = false;
		} else {
			Saint::logError("User ".Saint::getCurrentUsername()." attempted to change category id $id to '$category' but was denied access.",__FILE__,__LINE__);
			$success = false;
		}
		$page = Saint::getCurrentPage();
		$page->setTempLayout("system/json");
		$page->jsondata = array(
			'success' => $success,
			'actionlog' => Saint::getActionLog(),
		);
		return $success;
	}
}

