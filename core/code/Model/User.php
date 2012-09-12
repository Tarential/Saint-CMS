<?php
/**
 * Model of a user in the Saint framework.
 * @author Preston St. Pierre
 * @package Saint
 */
class Saint_Model_User {
	/**
	 * Get matching usernames from the database.
	 * @param array Filters to match.
	 * @return string Usernames of matching users.
	 */
	public static function getUsernames($filters = array()) {
		$options = array('id','username','password','email','fname','lname','phone','language');
		$sql = Saint::makeConditions($filters,$options);
		try {
			return Saint::getAll("SELECT `username` FROM `st_users`$sql");
		} catch (Exception $e) {
			if ($e->getCode()) {
				Saint::logError("Unable to select users: ".$e->getMessage(),__FILE__,__LINE__);
			}
			return array();
		}
	}
	
	/**
	 * Get matching users from the database.
	 * @param array Filters to match.
	 * @return Saint_Model_User[] Models of matching users.
	 */	
	public static function getUsers($filters = array()) {
		$usernames = Saint_Model_User::getUsernames($filters);
		$users = array();
		foreach ($usernames as $username) {
			$user = new Saint_Model_User();
			if ($user->loadByUsername($username)) {
				$users[] = $user;
			}
		}
		return $users;
	}
	
	/**
	 * Sets current user. Do not access directly; instead, use the login function.
	 * @param string $username New username.
	 * @return boolean True on success, false otherwise.
	 */
	private static function setCurrentUsername($username) {
		if ($sname = Saint::sanitize($username,SAINT_REG_USER_NAME)) {
			$_SESSION['username'] = $sname;
			return 1;
		} elseif ($username == '') {
			$_SESSION['username'] = '';
		} else
			return 0;
	}
	
	/**
	 * Remove user with given ID from database.
	 * @param int $id ID of user to remove.
	 * @return boolean True on success, false otherwise.
	 */
	public static function deleteUser($id) {
		$sid = Saint::sanitize($id,SAINT_REG_ID);
		try {
			Saint::query("DELETE FROM `st_users` WHERE `id`='$sid'");
			return 1;
		} catch (Exception $e) {
			if ($e->getCode()) {
				Saint::logError("Problem deleting user with id '$sid': ".$e->getMessage(),__FILE__,__LINE__);
			}
			return 0;
		}
	}
	
	/**
	 * Destroy all login sessions for the given user.
	 * @param string $username User to log out. 
	 * @return boolean True on success, false otherwise.
	 */
	public static function destroySessions($username) {
		$username = Saint::sanitize($username,SAINT_REG_USER_NAME, false);
		try {
			Saint::query("DELETE FROM `st_sessions` WHERE `username`='$username'");
			return 1;
		} catch (Exception $e) {
			Saint::logError("Error deleting session information for $username: ".$e->getMessage(),__FILE__,__LINE__);
			return 0;
		}
	}
	
	/**
	 * Create a session cookie and tracking entry in database.
	 * @param string $sequence Optional sequence number for session.
	 * @return boolean True on success, false otherwise.
	 */
	public static function createSession($sequence = null) {
		if ($sequence == null)
			$sequence = substr(md5(uniqid(rand(), true)), 0, SAINT_SEQ_LEN);
		$nonce = substr(md5(uniqid(rand(), true)), 0, SAINT_NONCE_LEN);
		
		# Client nonces may be greater than the md5 length, so we repeat
		$client_nonce = '';
		for ($i = 0; $i <= SAINT_CLIENT_NONCE_LEN; $i += 32) {
			$client_nonce .= md5(uniqid(rand(), true));
		}
		$client_nonce = substr($client_nonce, 0, SAINT_CLIENT_NONCE_LEN);
		try {
			$username = Saint::getCurrentUsername();
			$cookieval = $username.$sequence.$nonce;
			setcookie("saintcookie",$cookieval,time()+60*60*24*30,'/');
			Saint::query("INSERT INTO `st_sessions` (`username`,`sequence`,`nonce`,`client_nonce`) VALUES ('$username','$sequence','$nonce','$client_nonce')");
			return 1;
		} catch (Exception $e) {
			Saint::logError("Unable to save cookie info: ".$e->getMessage());
			return 0;
		}
	}
	
	/**
	 * Attempt to log user in via the given cookie var.
	 * @param string $cookie Value of cookie.
	 * @return boolean True on success, false otherwise.
	 */
	public static function loginViaCookie($cookie) {
		$sequence = substr($cookie,-(SAINT_SEQ_LEN+SAINT_NONCE_LEN), -SAINT_SEQ_LEN);
		$nonce = substr($cookie,-SAINT_SEQ_LEN);
		$username = substr($cookie,0,-(SAINT_SEQ_LEN+SAINT_NONCE_LEN));
		try {
			$session = Saint::getRow("SELECT `id`,`nonce` FROM `st_sessions` WHERE `username`='$username' AND `sequence`='$sequence' ORDER BY `id` DESC");
			$id = $session[0];
			$dbnonce = $session[1];
			if ($nonce == $dbnonce) {
				try {
					Saint::query("DELETE FROM `st_sessions` WHERE `id`='$id'");
					Saint_Model_User::setCurrentUsername($username);
					Saint_Model_User::createSession($sequence);
					return 1;
				} catch (Exception $f) {
					Saint::logError("Problem deleting session with id $id.",__FILE__,__LINE__);
					return 0;
				}
			} else {
				$page = new Saint_Model_Page();
				$page->setTempLayout("system/error");
				$page->addError("There has been a possible hacking attempt on this account. All related cookies have been disabled. You must enter your password to proceed.");
				$page->render();
				die();
			}
		} catch (Exception $e) {
			return 0;
		}
	}
	
	/**
	 * Attempt to log in with given username and password.
	 * @param string $username Username requesting login.
	 * @param string $password Password for authentication.
	 * @param boolean $setcookie Optional flag; true to set cookie, false by default.
	 * @return boolean True on success, false otherwise.
	 */
	public static function login($username,$password,$setcookie = false) {
		if (Saint_Model_User::authenticate($username,$password)) {
			Saint_Model_User::destroySessions($username);
			Saint_Model_User::setCurrentUsername($username);
			Saint_Model_User::createSession();
			return 1;
		}
		else
			return 0;
	}
	
	/**
	 * Log the current user out.
	 * @return boolean True on success, false otherwise.
	 */
	public static function logout() {
		$user = Saint::getCurrentUser();
		Saint_Model_User::destroySessions($user->getUsername());
		if (Saint_Model_User::setCurrentUsername('')) {
			return 1;
		} else
			return 0;
	}
	
	/**
	 * Checks if username is available for use
	 * @param string $username Username to test
	 * @global string SAINT_REG_USER_NAME Pattern matching valid usernames
	 * @return boolean True if available, false otherwise
	 */
	public static function nameAvailable($username) {
		if ($username = Saint::sanitize($username,SAINT_REG_USER_NAME)) {
			try {
				$id = Saint::getOne("SELECT `id` FROM `st_users` WHERE `username`='$username'");
				return 0;
			} catch (Exception $e) {
				return 1;
			}
		} else
			return 0;
	}

	/**
	 * Checks if email is available for use (ie does not exist in DB).
	 * @param string $email Address for which to check.
	 * @return boolean True if available, false otherwise.
	 */
	public static function emailAvailable($email) {
		if ($email = Saint::sanitize($email,SAINT_REG_EMAIL)) {
			try {
				$id = Saint::getOne("SELECT `id` FROM `st_users` WHERE `email`='$email'");
				return 0;
			} catch (Exception $e) {
				return 1;
			}
		} else
			return 0;
	}
	
	/**
	 * Authenticates username/password combination.
	 * @param string $username Username to check.
	 * @param string $password Password to check.
	 * @return boolean True if username/password matches, false otherwise.
	 */
	public static function authenticate($username, $password) {
		if (Saint_Model_User::failedLoginAttempts() <= 10) {
			if ($username = Saint::sanitize($username,SAINT_REG_USER_NAME)) {
				try {
					$hash = Saint::getOne("SELECT `password` FROM `st_users` WHERE `username`='$username'");
					$hasher = new Saint_Model_PasswordHasher(8, FALSE);
					if ($hasher->CheckPassword($password,$hash)) {
						Saint::query("DELETE FROM `st_login_attempts` WHERE `ip`='$_SERVER[REMOTE_ADDR]'");
						return 1;
					}
				} catch (Exception $e) {
					if ($e->getCode()) {
						Saint::logError("Unable to select password hash from database for username '$username': ".$e->getMessage(),__FILE__,__LINE__);
					}
				}
				try {
					$qid = Saint::getOne("SELECT `id` FROM `st_login_attempts` WHERE `ip`='$_SERVER[REMOTE_ADDR]'");
					Saint::query("UPDATE `st_login_attempts` SET `attempts`=`attempts`+1 WHERE `id`='$qid'");
				} catch (Exception $f) {
					if ($f->getCode()) {
						Saint::logError("Unable to update the number of failed login attempts: ".$f->getMessage(),__FILE__,__LINE__);
					}
					Saint::query("INSERT INTO `st_login_attempts` (`ip`) VALUES ('$_SERVER[REMOTE_ADDR]')");
				}
				return 0;
			}
		} else {
			Saint::logError("A user from $_SERVER[REMOTE_ADDR] failed login multiple times on account '$username'.",__FILE__,__LINE__);
			return 0;
		}
	}
	
	/**
	 * Get the number of recent failed login attempts by the current user.
	 * @return integer Number of recent failed login attempts by the current user.
	 */
	public static function failedLoginAttempts() {
		try {
			$attempts = Saint::getRow("SELECT `id`,`attempts`,`last_attempt` FROM `st_login_attempts` WHERE `ip`='$_SERVER[REMOTE_ADDR]'");
			Saint::logError("Time: ".time() . " DB Time: " . strtotime($attempts[2]) . " Added: " .strtotime("+60 minutes"));
			if (time() > strtotime($attempts[2])+strtotime("+60 minutes")) {
				Saint::query("DELETE FROM `st_login_attempts` WHERE `id`='$attempts[0]'");
				$attempts[1] = 0;
			}
			return $attempts[1];
		} catch (Exception $e) {
			if ($e->getCode()) {
				Saint::logError("Unable to select number of login attempts: ".$e->getMessage(),__FILE__,__LINE__);
			}
			return 0;
		}
	}
	
	/**
	 * Generate hash for given password using random salt.
	 * @param string $text Plain text password to be hashed.
	 * @return string Hashed password with salt prepended.
	 */
	protected static function genHash($text) {
		
    $hasher = new Saint_Model_PasswordHasher(8, FALSE);
    $hash = $hasher->HashPassword($text);
		return $hash;
	}
	
	protected $_id;
	protected $_username;
	protected $_password;
	protected $_language;
	protected $_email;
	protected $_fname;
	protected $_lname;
	protected $_phone;
	protected $_scid;
	protected $_nonce;
	
	/**
	 * Create a blank user model.
	 */
	public function __construct() {
		$this->_id = 0;
		$this->_username = 'guest';
		$this->_email = '';
		$this->_fname = '';
		$this->_lname = '';
		$this->_language = Saint::getDefaultLanguage();
		$this->_password = '';
		$this->_scid = 0;
		$this->_nonce = '';
	}
	
	/**
	 * Load user with given ID from database.
	 * @param int $id ID of user to load.
	 * @return boolean True on success, false otherwise.
	 */
	public function loadById($id) {
		if ($id = Saint::sanitize($id,SAINT_REG_ID)) {
			try {
				$info = Saint::getRow("SELECT `u`.`username`,`u`.`email`,`u`.`fname`,`u`.`lname`,`u`.`language`,`u`.`password`,`u`.`phone` ".
					"FROM `st_users` as `u` WHERE `u`.`id`='$id'");
				$this->_id = $id;
				$this->_username = $info[0];
				$this->_email = $info[1];
				$this->_fname = $info[2];
				$this->_lname = $info[3];
				$this->_language = $info[4];
				$this->_password = $info[5];
				$this->_phone = $info[6];
			} catch (Exception $e) {
				if ($e->getCode()) {
					Saint::logError("Cannot load User model from ID '$id'. Error: " . $e->getMessage(),__FILE__,__LINE__);
				}
				return 0;
			}
			try {
				$this->_nonce = Saint::getOne("SELECT `s`.`client_nonce` FROM `st_sessions` as `s` WHERE `s`.`username`='$this->_username'");
			} catch (Exception $f) {
				if ($f->getCode()) {
					Saint::logError("Cannot load user session nonce for ID '$id'. Error: " . $f->getMessage(),__FILE__,__LINE__);
					return 0;
				}
			}
			return 1;
		} else
			return 0;
	}

	/**
	 * Load user with given username from database. Grabs ID then passes work to loadById().
	 * @param string $username Username of user to load.
	 * @return boolean True on success, false otherwise.
	 */
	public function loadByUsername($username) {
		if ($username === "guest") {
			return 0; }
		if ($username = Saint::sanitize($username,SAINT_REG_USER_NAME)) {
			try {
				$id = Saint::getOne("SELECT `id` FROM `st_users` WHERE `username`='$username'");
				return $this->loadById($id);
			} catch (Exception $e) {
				Saint::logError("Could not find username $username. " . $e->getMessage(),__FILE__,__LINE__);
				return 0;
			}
		} else
			return 0;
	}
	
	/**
	 * Get the ID of the loaded user.
	 * @return int User ID.
	 */
	public function getId() {
		return $this->_id;
	}
	
	/**
	 * Get the client session nonce for the loaded user.
	 */
	public function getNonce() {
		return $this->_nonce;
	}
	
	/**
	 * Get the shopping cart of the loaded user.
	 * @return Saint_Model_ShoppingCart Cart of current user if successful, 0 otherwise.
	 */
	public function getShoppingCart() {
		$cart = new Saint_Model_ShoppingCart();
		$id = $this->getShoppingCartId();
		
		if ($cart->load($id))
			return $cart;
		else
			return 0;
	}
	
	/**
	 * Get the ID of the shopping cart of the loaded user.
	 * @return int ID of the cart of current user.
	 */
	public function getShoppingCartId() {
		try {
			return Saint::getOne("SELECT `id` FROM `st_shop_carts` WHERE `owner`='".$this->_id."' AND `purchased`='0'");
		} catch (Exception $f) {
			if ($f->getCode()) {
				Saint::logError("Unable to select user shopping cart: " . $f->getMessage(),__FILE__,__LINE__);
			}
			$newcart = new Saint_Model_ShoppingCart();
			$newcart->loadNew();
			return $newcart->getId();
		}
	}
	
	/**
	 * Get the loaded user's username.
	 * @return string Username of the loaded user.
	 */
	public function getUsername() {
		return $this->_username;
	}
	
	/**
	 * Get the loaded user's language.
	 * @return string User language.
	 */
	public function getLanguage() {
		return $this->_language;
	}
	
	/**
	 * Get the loaded user's e-mail address.
	 * @return string User e-mail address.
	 */
	public function getEmail() {
		return $this->_email;
	}
	
	/**
	 * Get the loaded user's first name.
	 * @return string User first name.
	 */
	public function getFirstName() {
		return $this->_fname;
	}
	
	/**
	 * Get the loaded user's last name. 
	 * @return string User last name.
	 */
	public function getLastName() {
		return $this->_lname;
	}

	/**
	 * Get the loaded user's phone number.
	 * @return string User phone number.
	 */
	public function getPhoneNumber() {
		return $this->_phone;
	}
	
	/**
	 * Changes the loaded user's username to given one.
	 * @param string $username New username for user.
	 * @return boolean True on success, false otherwise.
	 */
	public function setUsername($username) {
		if ($username = Saint::sanitize($username,SAINT_REG_USER_NAME)) {
			if (Saint_Model_User::nameAvailable($username)) {
				$this->_username = $username;
				return 1;
			} else
				return 0;
		} else
			return 0;
	}
	
	/**
	 * Change the loaded user's language to given one.
	 * @param string $language New language for user.
	 * @return boolean True on success, false otherwise.
	 */
	public function setLanguage($language) {
		if ($language = Saint::sanitize($language,SAINT_REG_USER_NAME)) {
			if (Saint_Model_Language::inUse($language)) {
				$this->_language = $language;
				return 1;
			} else
				return 0;
		} else
			return 0;
	}
	
	/**
	 * Change's the loaded user's e-mail address to given one.
	 * @param string $email New e-mail address for user.
	 * @return boolean True on success, false otherwise.
	 */
	public function setEmail($email) {
		if ($email = Saint::sanitize($email,SAINT_REG_EMAIL)) {
			if (Saint_Model_User::emailAvailable($email)) {
				$this->_email = $email;
				return 1;
			} else
				return 0;
		} else
			return 0;
	}
	
	/**
	 * Change's the loaded user's first name to given one.
	 * @param string $fname New first name for user.
	 * @return boolean True on success, false otherwise.
	 */
	public function setFirstName($fname) {
		if ($fname = Saint::sanitize($fname)) {
			$this->_fname = $fname;
			return 1;
		} else
			return 0;
	}
	
	/**
	 * Change's the loaded user's last name to given one.
	 * @param string $lname New last name for user.
	 * @return boolean True on success, false otherwise.
	 */
	public function setLastName($lname) {
		if ($lname = Saint::sanitize($lname)) {
			$this->_lname = $lname;
			return 1;
		} else
			return 0;
	}
	
	/**
	 * Change's the loaded user's phone number to given one.
	 * @param int $phone New phone number for user. 
	 * @return boolean True on success, false otherwise.
	 */
	public function setPhoneNumber($phone) {
		if ($phone = Saint::sanitize($phone,SAINT_REG_ID)) {
			$this->_phone = $phone;
			return 1;
		} else
			return 0;
	}
	
	/**
	 * Change's the loaded user's password to given one.
	 * @param string $password New password for user.
	 * @return boolean True on success, false otherwise.
	 */
	public function setPassword($password) {
			$this->_password = Saint_Model_User::genHash($password);
	}
	
	/**
	 * Checks if a user has permission to perform specified action.
	 * @param string $action Name of action user is requesting to perform.
	 * @return boolean True if permission granted, false otherwise.
	 */
	public function hasPermissionTo($action, $target = null) {
		global $saint_group_access;
		$name = Saint::sanitize($action,SAINT_REG_USER_NAME);
		if ($target == null) {
			foreach ($this->getGroups() as $group) {
				if (isset($saint_group_access[$group]) && in_array($name,$saint_group_access[$group])) {
					return 1;
				}
			}
			return 0;
		} else {
			return $target->hasPermissionTo($this,$name);
		}
		return 0;
	}

	/**
	 * Gets the names of the groups to which the loaded user belongs.
	 * @return string[] Names of the groups to which user belongs.
	 */
	public function getGroups() {
		if ($this->getUsername() == "guest")
			return array("guest");
		try {
			return Saint::getAll("SELECT `g`.`group` FROM `st_users` as `u`, `st_user_groups` as `g` WHERE `g`.`userid`=`u`.`id` AND `u`.`id`='".$this->getId()."'");
		} catch (Exception $e) {
			//Saint::logWarning($e->getMessage(),__FILE__,__LINE__);
			return array("guest");
		}
	}
	
	/**
	 * Checks if the loaded user is in the specified group.
	 * @param string $group Name of group in which to check for user.
	 * @return boolean True if user is in group, false otherwise.
	 */
	public function isInGroup($group) {
		if ($this->getUsername() == "guest") {
			if ($group == "guest")
				return 1;
			else
				return 0;
		}
		$name = Saint::sanitize($group, SAINT_REG_USER_NAME, false);
		if ($name == $group) {
			try {
				$id = Saint::getOne("SELECT `g`.`id` FROM `st_users` as `u`, `st_user_groups` as `g` WHERE `g`.`userid`=`u`.`id` AND `g`.`group`='$name' AND `u`.`id`='".$this->getId()."'");
				return 1;
			} catch (Exception $e) {
				if ($e->getCode()) {
					Saint::logError($e->getMessage(),__FILE__,__LINE__); }
				return 0;
			}
		} else {
			Saint::logError("Invalid group name: '$group'.",__FILE__,__LINE__);
			return 0;
		}
	}
	
	/**
	 * Add loaded user to the specified group.
	 * @param string $group Group into which to add user.
	 * @return boolean True on success, false otherwise.
	 */
	public function addToGroup($group) {
		$name = Saint::sanitize($group,SAINT_REG_USER_NAME);
		if ($name == $group) {
			if (!$this->isInGroup($group)) {
				try {
					Saint::query("INSERT INTO `st_user_groups` (`userid`,`group`) VALUES ('".$this->getId()."','$name')");
				} catch (Exception $e) {
					Saint::logError("Could not add user ".$this->getUsername()." with id '$this->_id' to group ".$name.":".$e->getMessage(),__FILE__,__LINE__);
					return 0;
				}
			} else
				return 1;
		} else
			return 0;
	}
	
	/**
	 * Remove loaded user from the specified group.
	 * @param string $group Group from which to remove user.
	 * @return boolean True on success, false otherwise.
	 */
	public function removeFromGroup($group) {
		$name = Saint::sanitize($group,SAINT_REG_USER_NAME);
		if ($name == $group) {
			try {
				Saint::query("DELETE FROM `st_user_groups` WHERE `group`='$name' AND `userid`='".$this->getId()."'");
				return 1;
			} catch (Exception $e) {
				Saint::logError($e->getMessage(),__FILE__,__LINE__);
				return 0;
			}
		} else
			return 0;
	}
	
	/**
	 * Saves loaded user information to database.
	 * @return boolean True on success, false otherwise.
	 * @todo Add proper conflict test and error reporting in return code.
	 */
	public function save() {
		if ($this->_id) {
			$query = "UPDATE `st_users` SET ".
			"`username`='$this->_username',".
			"`password`='$this->_password',".
			"`language`='$this->_language',".
			"`email`='$this->_email',".
			"`fname`='$this->_fname',".
			"`lname`='$this->_lname',".
			"`phone`='$this->_phone'".
			" WHERE `id`='$this->_id'";
		} else {
			$query = "INSERT INTO `st_users` (`username`,`language`,`email`,`fname`,`lname`,`phone`,`password`) VALUES ".
			"('$this->_username','$this->_language','$this->_email','$this->_fname','$this->_lname','$this->_phone','$this->_password')";
		}
		try {
			Saint::query($query);
			if (!$this->_id) {
				$this->_id = Saint::getLastInsertId(); }
			Saint::logEvent("Saved info for user '$this->_username' id '$this->_id'.");
			return 1;
		} catch (Exception $e) {
			Saint::logError("Problem saving user: ".$e->getMessage());
			return 0;
		}
	}
}

