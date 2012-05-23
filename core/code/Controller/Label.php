<?php
/**
 * Controller for label editing within the Saint framework.
 * @author Preston St. Pierre
 * @package Saint
 */
class Saint_Controller_Label {
	/**
	 * Update label of given name to equal given value.
	 * @param string $lname Name of label to edit.
	 * @param string $lval New value for label.
	 * @return boolean True on success, false otherwise.
	 */
	public static function editLabel($lname,$lval) {
		$page = Saint::getCurrentPage();
		$label = new Saint_Model_Label();
		$user = Saint::getCurrentUser();
		$labelName = preg_replace('/^saint_/','',$lname);
		$labelName = preg_replace('/_/','/',$labelName);
		$page->setTempLayout("system/json");
		$page->jsondata = array();
		
		if ($label->loadByName($labelName)) {
			if ($user->hasPermissionTo("edit-label") || $user->getUsername() == $label->getOwner()) {
				if (isset($_POST['label-language']))
					$language = $_POST['label-language'];
				else
					$language = $user->getLanguage();
				//$label->setLabel(nl2br(strip_tags($lval,'<a><i><b>')));
				$label->setLabel($lval);
				$label->save();
				Saint::logEvent("Set label ".Saint_Model_Label::formatForDisplay($labelName).".");
				$page->jsondata['success'] = true;
				$page->jsondata['actionlog'] = Saint::getActionLog();
				return 1;
			} else {
				Saint::logError("User ".$user->getUsername()." attempted to set label ".$label->getName().
					" from IP $_SERVER[REMOTE_ADDR] but was denied access.");
				$page->jsondata['success'] = false;
				$page->jsondata['actionlog'] = Saint::getActionLog();
				return 0;
			}
		} else {
			Saint::logError("User ".$user->getUsername()." attempted to set label ".
				Saint::sanitize($labelName)." but that label was not found.");
			$page->jsondata['success'] = false;
			$page->jsondata['actionlog'] = Saint::getActionLog();
			return 0;
		}
	}
}
