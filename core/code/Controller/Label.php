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
		$jsondata = array();
		$success = false;
		if ($label->loadByName($labelName)) {
			if ($user->hasPermissionTo("edit-label") || $user->getUsername() == $label->getOwner()) {
				if (isset($_POST['label-language']))
					$language = $_POST['label-language'];
				else
					$language = $user->getLanguage();
				$label->setLabel($lval);
				if ($label->save()) {
					$label_data = Saint_Model_Label::parseName($labelName);
					$message = 'Set label <span class="name">' . Saint_Model_Label::formatForDisplay($label_data['label_name']) . '</span>';
					if (isset($label_data['block_name'])) {
						$message .= ' in <span class="name">' . Saint_Model_Label::formatForDisplay($label_data['block_name']) . '</span>';
					}
					if (isset($label_data['block_id'])) {
						$message .= ' <span class="name">#' . Saint_Model_Label::formatForDisplay($label_data['block_id']) . '</span>';
					}
					if (isset($label_data['page_id'])) {
						$tp = new Saint_Model_Page;
						if ($tp->loadById($label_data['page_id'])) {
							$message .= ' on page <span class="name">' . $tp->getTitle() . '</span>';
						}
					}
					Saint::logEvent($message.".");
					$success = true;
				}
				$jsondata['success'] = $success;
				$jsondata['actionlog'] = Saint::getActionLog();
				$page->setJsonData($jsondata);
				return 1;
			} else {
				Saint::logError("User ".$user->getUsername()." attempted to set label ".$label->getName().
					" from IP $_SERVER[REMOTE_ADDR] but was denied access.");
				$jsondata['success'] = false;
				$jsondata['actionlog'] = Saint::getActionLog();
				$page->setJsonData($jsondata);
				return 0;
			}
		} else {
			Saint::logError("User ".$user->getUsername()." attempted to set label ".
				Saint::sanitize($labelName)." but that label was not found.");
			$jsondata['success'] = false;
			$jsondata['actionlog'] = Saint::getActionLog();
			$page->setJsonData($jsondata);
			return 0;
		}
	}
}
