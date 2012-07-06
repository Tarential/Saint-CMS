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
	 * Process file manager arguments.
	 */
	public static function process() {
		$page = Saint::getCurrentPage();
		$args = $page->getArgs();
		$arguments = array();
		
		# File ID to match
		if (isset($args['fid']) && $args['fid'] != 0) {
			$arguments['id'] = $args['fid'];
		}
		
		# Current page
		if (isset($args['sfmcurpage'])) {
			$page->set("sfm-page-number",$args['sfmcurpage']);
		} else {
			$page->set("sfm-page-number",0);
		}
		$arguments['page-number'] = $page->get("sfm-page-number");
		
		# Number of results per page
		if (isset($args['sfmperpage'])) {
			$page->set("sfm-results-per-page",$args['sfmperpage']);
		} else {
			$page->set("sfm-results-per-page",15);
		}
		$arguments['results-per-page'] = $page->get("sfm-results-per-page");

		# Block view
		if (isset($args['view']) && $args['view'] == 'file-list' && Saint::getCurrentUser()->hasPermissionTo("manage-files")) {
			$page->setTempLayout("system/file-manager-list");
		}
		
		# Posted file data
		if (isset($_POST['saint-file-mode']) && isset($_POST['saint-file-id']) && isset($_POST['saint-file-title'])
			&& isset($_POST['saint-file-keywords']) && isset($_POST['saint-file-description'])) {
			if (isset($_POST['saint-file-categories']))
				$categories = $_POST['saint-file-categories'];
			else
				$categories = array();
			
			if ($_POST['saint-file-mode'] == "search") {
				# Filter the shown files by search parameters
				$arguments = array(
					'id' => $_POST['saint-file-id'],
					'title' => $_POST['saint-file-title'],
					'keywords' => explode(',',$_POST['saint-file-keywords']),
					'description' => $_POST['saint-file-description'],
					'categories' => $categories,
				);
			} else {
				# Save file details to database and send a JSON reply
				Saint_Controller_FileManager::saveFileDetails($_POST['saint-file-id'],
					$_POST['saint-file-title'],$_POST['saint-file-keywords'],
					$_POST['saint-file-description'],$categories);
				if (isset($_POST['saint-file-label']) && $_POST['saint-file-label']) {
					if (Saint_Model_FileLabel::setFile(
						Saint::convertNameFromWeb($_POST['saint-file-label']),
						array('fid'=>$_POST['saint-file-id']))) {
						$success = true;
					} else {
						$success = false;
					}
					
					$page->setTempLayout("system/json");
					$jsondata = array(
						"success" => $success,
						"sfl" => $_POST['saint-file-label'],
						"sfid" => $_POST['saint-file-id'],
					);
					if (isset($_POST['saint-file-label-width']) && $_POST['saint-file-label-width'] != "0" 
						&& isset($_POST['saint-file-label-height']) && $_POST['saint-file-label-height'] != "0" ) {
						$arguments = array(
							"max-width" => $_POST['saint-file-label-width'],
							"max-height" => $_POST['saint-file-label-height'],
						);
						$img = new Saint_Model_Image($_POST['saint-file-id']);
						$jsondata['url'] = $img->getResizedUrl($arguments);
					}
					$page->setJsonData($jsondata);
				}
			}
		}
		# Finally, retrieve the matching files and configure paging.
		$files = Saint_Model_FileManager::getAllFiles($arguments);
		$page->setFiles($files);
		$arguments['num-results-only'] = true;
		$page->set("sfm-number-of-files",Saint_Model_FileManager::getAllFiles($arguments));
		$page->set("sfm-number-of-pages", $page->get("sfm-number-of-files") / $page->get("sfm-results-per-page"));
	}
}
