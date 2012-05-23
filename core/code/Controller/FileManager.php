<?php
/**
 * Controller to manage file meta data within the Saint framework.
 * @author Preston St. Pierre
 * @package Saint
 */
class Saint_Controller_FileManager {
	/**
	 * Update file with the given ID to match the respective parameters.
	 * @param int $id ID of file to edit.
	 * @param string $title New title for file.
	 * @param string $keywords New keywords for file, comma separated.
	 * @param string $description New description for file.
	 * @param array $categories Optional categories into which to place the file.
	 * @return boolean True on success, false otherwise.
	 */
	public static function saveFileDetails($id,$title,$keywords,$description,$categories = array()) {
		$page = Saint::getCurrentPage();
		$success = false;
		if (Saint::getCurrentUser()->hasPermissionTo("manage-files")) {
			$model = new Saint_Model_FileManager();
			if ($model->load($id)) {
				$model->setTitle($title);
				$model->setKeywords($keywords);
				$model->setDescription($description);
				$model->setCategories($categories);
				if ($model->save()) {
					$page->sfmmessage = "Saved file information.";
					$page->sfmstatus = "saved";
					$success = true;
				} else {
					$page->error = "Problem saving file with id '$id'. Check the error log for more information.";
					$page->setTempLayout("system/error");
				}
			} else {
				$page->error = "Couldn't load model with id '$id'.";
				$page->setTempLayout("system/error");
			}
		} else {
			$page->error = "Sorry, but you don't have access to change file meta data.";
			$page->setTempLayout("system/error");
			Saint::logError("User ".Saint::getCurrentUsername()." tried to change file meta data for file id '".
				$id."' from IP $_SERVER[REMOTE_ADDR] but was denied access.",__FILE__,__LINE__);
		}
		return $success;
	}
	
	/**
	 * Set the search filter parameters for the file manager interface.
	 * @param int $id ID to match.
	 * @param string $title Title to match.
	 * @param string $keywords Keyword(s) to match.
	 * @param string $description Description to match.
	 * @param string $categories Categories to match.
	 */
	public static function filterFileDetails($id,$title,$keywords,$description,$categories = array()) {
		$page = Saint::getCurrentPage();
		$page->sfmarguments = array(
			'id' => $id,
			'title' => $title,
			'keywords' => explode(',',$keywords),
			'description' => $description,
			'categories' => $categories
		);
	}
}
