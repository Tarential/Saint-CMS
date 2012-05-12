<?php
/**
 * Controller for WYSIWYG labels within Saint.
 * @author Preston St. Pierre
 * @package Saint
 */
class Saint_Controller_Wysiwyg {
	/**
	 * Change WYSIWYG label with given name to given content.
	 * @param string $name Name of label to change.
	 * @param string $content New value for label content.
	 */
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
