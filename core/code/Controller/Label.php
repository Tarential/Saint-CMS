<?php
class Saint_Controller_Label {
	public static function editLabel($lname,$lval) {
		$page = Saint::getCurrentPage();
		$label = new Saint_Model_Label();
		$user = Saint::getCurrentUser();
		$labelName = preg_replace('/^saint_/','',$lname);
		$labelName = preg_replace('/_/','/',$labelName);
		$page->setTempLayout("system/json");
		$page->jsondata = array();
		
		if ($label->loadByName($labelName)) {
			if (isset($_POST['label-language']))
				$language = $_POST['label-language'];
			else
				$language = $user->getLanguage();
			if ($user->hasPermissionTo("edit-label") || $user->getUsername() == $label->getOwner()) {
				$label->setLabel(nl2br(strip_tags($lval,'<a><i><b>')));
				$label->save();
				Saint::logEvent("Set label ".Saint_Model_Label::formatForDisplay($labelName).".");
				$page->jsondata['success'] = true;
				$page->jsondata['actionlog'] = Saint::getActionLog();
				return 1;
			} else {
				Saint::logError("User ".$user->getUsername()." attempted to set label ".$label->getName()." but was denied access.");
				$page->jsondata['success'] = false;
				$page->jsondata['actionlog'] = Saint::getActionLog();
				return 0;
			}
		} else {
			Saint::logError("User ".$user->getUsername()." attempted to set label ".Saint::sanitize($labelName)." but that label was not found.");
			$page->jsondata['success'] = false;
			$page->jsondata['actionlog'] = Saint::getActionLog();
			return 0;
		}
	}
}
