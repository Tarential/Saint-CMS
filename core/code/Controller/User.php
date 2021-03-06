<?php
/**
 * Controller for users within the Saint framework.
 * @author Preston St. Pierre
 * @package Saint
 */
class Saint_Controller_User {
	
	/**
	 * Attempt to log in a user with the given username and password.
	 * @param string $username Username to check.
	 * @param string $password Password to check.
	 */
	public static function login($username,$password) {
		$page = Saint::getCurrentPage();
		$success = 1;
		if (isset($_POST['rememberme']) && $_POST['rememberme'] == true)
			$setcookie = true;
		else
			$setcookie = false;
		if (Saint_Model_User::login($username,$password,$setcookie)) {
			header("Location: " . SAINT_URL);
		} else {
			$page->addError("Username/password combination incorrect. Please try again.");
			$page->setTempLayout("system/error");
			
			$success = 0;
		}
		return $success;
	}
	
	/**
	 * Update details for user with given ID.
	 * @param int $id ID of user to edit.
	 */
	public static function saveUser($id) {
		$user = new Saint_Model_User();
		$errors = array();
		
		if ($id) {
			$user->loadById($id); }
		
		if ($user->isInGroup("administrator") && !Saint::getCurrentUser()->isInGroup("administrator")) {
			$success = false;
			$errors[] = "Only administrators may edit administrators.";
		} else {
			
			if (isset($_POST['saint-edit-user-delete']) && $_POST['saint-edit-user-delete'] == "1") {
				Saint_Model_User::deleteUser($id);
			}
			
			if ($_POST['saint-edit-user-username'] != $user->getUsername() && Saint::getCurrentUser()->hasPermissionTo("edit-user") || $user->getId() === 0) {
				if (Saint_Model_User::nameAvailable($_POST['saint-edit-user-username'])) {
					$user->setUsername($_POST['saint-edit-user-username']);
				} else {
					$errors[] = "Username unavailable.";
				}
			}
			
			if (isset($_POST['saint-edit-user-firstname'])) {
				$user->setFirstName($_POST['saint-edit-user-firstname']);
			}
			
			if (isset($_POST['saint-edit-user-lastname'])) {
				$user->setLastName($_POST['saint-edit-user-lastname']);
			}
			
			if (isset($_POST['saint-edit-user-email'])) {
				$user->setEmail($_POST['saint-edit-user-email']);
			}
			
			if ($user->getId() != 0 && isset($_POST['saint-edit-user-newpassone']) && $_POST['saint-edit-user-newpassone'] != '') {
				if (Saint_Model_User::authenticate($user->getUsername(),$_POST['saint-edit-user-oldpass'])
					|| Saint::getCurrentUser()->hasPermissionTo("edit-user")) {
					if ($_POST['saint-edit-user-newpassone'] == $_POST['saint-edit-user-newpasstwo']) {
						$user->setPassword($_POST['saint-edit-user-newpassone']);
					} else
						$errors[] = "New passwords do not match.";
				} else
					$errors[] = "Incorrect password.";
			}
			
			if ($user->getId() == 0 && isset($_POST['saint-edit-user-newpassone']) && $_POST['saint-edit-user-newpassone'] != '') {
				if ($_POST['saint-edit-user-newpassone'] == $_POST['saint-edit-user-newpasstwo']) {
					$user->setPassword($_POST['saint-edit-user-newpassone']);
				} else
					$errors[] = "Passwords do not match.";
			}
			
			if (count($errors) === 0) {
				if (!$user->save()) {
					$errors[] = "Unable to save user.";
				}
			
				if (isset($_POST['saint-edit-user-groups'])) {
					if (is_array($_POST['saint-edit-user-groups'])) {
						$groups = $_POST['saint-edit-user-groups'];
					} else {
						$groups = array($_POST['saint-edit-user-groups']);
					}
				} else {
					$groups = array();
				}
					
					
				foreach ($groups as $group) {
					if (!in_array($group,$user->getGroups())) {
						if ($group == "administrator") {
							if (Saint::getCurrentUser()->hasPermissionTo("make-administrator"))
								$user->addToGroup($group);
						} elseif ($group == "moderator") {
							if (Saint::getCurrentUser()->hasPermissionTo("make-moderator"))
								$user->addToGroup($group);
						} else
							$user->addToGroup($group);
					}
				}
				
				foreach ($user->getGroups() as $ugroup) {
					if (!in_array($ugroup,$groups)) {
						if ($ugroup == "administrator") {
							if (Saint::getCurrentUser()->hasPermissionTo("break-administrator"))
								$user->removeFromGroup($ugroup);
							else
								Saint::logError("You don't have permission to remove administrator status from user $user->getUsername()",__FILE__,__LINE__);
						} elseif ($ugroup == "moderator") {
							if (Saint::getCurrentUser()->hasPermissionTo("break-moderator"))
								$user->removeFromGroup($ugroup);
							else
								Saint::logError("You don't have permission to remove moderator status from user $user->getUsername()",__FILE__,__LINE__);
						} else
							$user->removeFromGroup($ugroup);
					}
				}
			
			}
		}
		
		$page = Saint::getCurrentPage();
		
		if (isset($_POST['saint-edit-user-ajax']) && $_POST['saint-edit-user-ajax']) {
			$page->setTempLayout("system/json");
			$users = Saint::getUsers();
			$su = array();
			foreach ($users as $user) {
				$su[] = array($user->getId(),$user->getUsername());
			}
			$jsondata = array(
				'actionlog' => Saint::getActionLog(),
				'users' => $su,
			);
			if (sizeof($errors) > 0) {
				$jsondata['success'] = false;
				$jsondata['error'] = $errors;
			} else {
				$jsondata['success'] = true;
			}
			$page->setJsonData($jsondata);
		} else {
			if (sizeof($errors) == 0) {
				$page->setTempLayout("system/error");
				$page->addError('Registration Successful. Use your new username and password to <a href="'.SAINT_URL.'/login">login</a>.');
			} else {
				$page->setTempLayout("system/user-register");
				foreach ($errors as $error) {
					Saint::addNotice($error);
				}
			}
		}
	}
}
