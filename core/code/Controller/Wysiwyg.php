<?php
class Saint_Controller_Wysiwyg {
	public static function setContent($name,$content) {
		$page = Saint::getCurrentPage();
		$page->setTempLayout("system/json");
		$success = false;
		$model = new Saint_Model_Wysiwyg($name);
		$model->setContent($content);
		if ($model->save()) {
			$success = true;
		}
		$page->jsondata = array(
			'success' => $success,
			'actionlog' => Saint::getActionLog(),
		);
	}
}
