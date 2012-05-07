<?php
class Saint_Controller_FileManager {
	public static function saveFileDetails($id,$title,$keywords,$description,$categories = array()) {
		$page = Saint::getCurrentPage();
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
				} else {
					$page->error = "Problem saving file with id '$id'. Check the error log for more information.";
					$page->setTempLayout("error");
				}
			} else {
				$page->error = "Couldn't load model with id '$id'.";
				$page->setTempLayout("error");
			}
		} else {
			$page->error = "Sorry, but you don't have access to change file meta data.";
			$page->setTempLayout("error");
			Saint::logError("User ".Saint::getCurrentUsername()." tried to change file meta data for file id '".
				$id."' from IP ".$_SERVER['REMOTE_ADDR']." but was denied access.",__FILE__,__LINE__);
		}
	}
	
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
