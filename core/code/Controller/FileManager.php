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
					$page->setStatus(array("saved"=>"Saved file information."));
					$success = true;
				} else {
					$page->addError("Problem saving file with id '$id'. Check the error log for more information.");
					$page->setTempLayout("system/error");
				}
			} else {
				$page->addError("Couldn't load model with id '$id'.");
				$page->setTempLayout("system/error");
			}
		} else {
			$page->addError("Sorry, but you don't have access to change file meta data.");
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
		$arguments = array(
			'id' => $id,
			'title' => $title,
			'keywords' => explode(',',$keywords),
			'description' => $description,
			'categories' => $categories
		);
		$page->setFiles(Saint_Model_FileManager::getAllFiles($arguments));
	}
	
	/**
	 * Process file manager arguments.
	 */
	public static function process() {
		$page = Saint::getCurrentPage();
		$args = $page->getArgs();
		$arguments = array();
		if (isset($args['fid']) && $args['fid'] != 0) {
			$arguments['id'] = $args['fid'];
		}
		if (isset($args['sfmcurpage'])) {
			$page->set("sfm-page-number",$args['sfmcurpage']);
		} else {
			$page->set("sfm-page-number",0);
		}
		$arguments['page-number'] = $page->get("sfm-page-number");
		if (isset($args['sfmperpage'])) {
			$page->set("sfm-results-per-page",$args['sfmperpage']);
		} else {
			$page->set("sfm-results-per-page",15);
		}
		$arguments['results-per-page'] = $page->get("sfm-results-per-page");
		$files = Saint_Model_FileManager::getAllFiles($arguments);
		$page->setFiles($files);
		$arguments['num-results-only'] = true;
		$page->set("sfm-number-of-files",Saint_Model_FileManager::getAllFiles($arguments));
		$page->set("sfm-number-of-pages", $page->get("sfm-number-of-files") / $page->get("sfm-results-per-page"));
	}
}
