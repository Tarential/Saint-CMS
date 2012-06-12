<?php

/**
 * Root class for the Saint framework.
 * @author Preston St. Pierre
 * @package Saint
 */
class Saint {
	static protected $_pc;
	static protected $_user;
	
	/**
	 * Starts the installation process.
	 */
	public static function runInstall() {
		include SAINT_SITE_ROOT . "/core/installer/install.php";
	}
	
	/**
	 * Sanitize input for use in database queries and filesystem traversal.
	 * @param string $input Input to sanitize.
	 * @param string $pattern Optional regex pattern to match.
	 */
	public static function sanitize($input, $pattern = null) {
		$safe = mysql_real_escape_string($input);
		if (($pattern == null || preg_match($pattern,$safe)) && !preg_match('/\.\.\//',$safe))
			return $safe;
		else
			return 0;
	}
	
	/**
	 * Add a user to the database.
	 * @param string $username Username to add.
	 * @param string $password Password for user.
	 * @param string $email E-mail address for user.
	 * @param string $fname Optional user's first name.
	 * @param string $lname Optional user's last name.
	 * @param string $language Optional user's default language.
	 */
	public static function addUser($username,$password,$email,$fname = '', $lname = '',$language = null) {
		if ($language == null)
			$language = SAINT_DEF_LANG;
		
		$user = new Saint_Model_User();
		$user->setUsername($username);
		$user->setPassword($password);
		$user->setEmail($email);
		$user->setFirstName($fname);
		$user->setLastName($lname);
		$user->setLanguage($language);
		return $user->save();
	}
	
	/**
	 * Check if site has an administrative user.
	 * Used internally to test installation status.
	 * @return boolean True if there exists at least one administrative user in the database, false otherwise.
	 */
	public static function siteHasAdmin() {
		try {
			Saint::getOne("SELECT `u`.`id` FROM `st_users` as `u`,`st_usergroups` as `g` WHERE `g`.`group`='administrator' AND `g`.`userid`=`u`.`id` LIMIT 1");
			return 1;
		} catch (Exception $f) {
			if ($f->getCode()) {
				Saint::logError($f->getMessage()); }
			return 0;
		}
	}
	
	/**
	 * Returns username of site owner.
	 */
	public static function getSiteOwner() {
		try {
			$owner = Saint::getOne("SELECT `owner` FROM `st_config`");
			if ($owner == null)
				throw new Exception("Null owner.");
			return $owner;
		} catch (Exception $e) {
			Saint::logError("Your site has no admin users... how did that happen? Reinstall the cms or see the documentation to add a user manually.",__FILE__,__LINE__);
			die();
		}
	}
	
	/**
	 * Notices added here are displayed to the user at each page load if supported by the template.
	 * @param string $notice Contents of notice to be displayed to user.
	 */
	public static function addNotice($notice) {
		if (isset($_SESSION['saint_notice']) && is_array($_SESSION['saint_notice'])) {
			$_SESSION['saint_notice'][] = $notice;
		} else {
			$_SESSION['saint_notice'] = array($notice);
		}
		return 1;
	}
	
	/**
	 * Remove given entry from display notices.
	 * @param string $notice Contents of notice to remove.
	 */
	public static function removeNotice($notice) {
		$index = array_search($notice,$_SESSION['saint_notice']);
		if ($index !== false) {
			unset($notice,$_SESSION['saint_notice'][$index]);
		}
		return 1;
	}
	
	/**
	 * Clear list of notices.
	 */
	public static function clearNotices() {
		$_SESSION['saint_notice'] = array();
	}
	
	/**
	 * Get array of notices.
	 * @return string[] Current notices.
	 */
	public static function getNotices() {
		if (!isset($_SESSION['saint_notice']))
			$_SESSION['saint_notice'] = array();
		return $_SESSION['saint_notice'];
	}
	
	/**
	 * Get the username of the current user.
	 * @return string Username of the current user.
	 */
	public static function getCurrentUsername() {
		if (!isset($_SESSION['username']))
			$_SESSION['username'] = 'guest';
		return $_SESSION['username'];
	}
	
	/**
	 * Get the current user.
	 * Model is cached on first call for performance.
	 * @return Saint_Model_User Current user model.
	 */
	public static function getCurrentUser() {
		global $_user;
		if ($_user == null) {
			$_user = new Saint_Model_User();
			try {
				$_user->loadByUsername(Saint::getCurrentUsername());
			} catch (Exception $e) {
				# No account by that name, so we return a guest user
			}
		}
		return $_user;
	}

	/**
	 * Outputs a phone number in the site format.
	 * Additional formatting options will be added in the future.
	 * @param string $phone Phone number in digit-only format
	 */
	public static function formatPhoneNumber($phone) {
		return preg_replace('/(\d{3})(\d{3})(\d{4})/','$1-$2-$3',$phone);
	}
	
	/**
	 * Shortcut for Saint_Model_User::getAllUsers().
	 * @return Saint_Model_User[] Array of all site users.
	 */
	public static function getAllUsers() {
		return Saint_Model_User::getAllUsers();
	}
	
	/**
	 * Shortcut for Saint_Model_Page::getAllPages().
	 * @return Saint_Model_Page[] Array of all site pages.
	 */
	public static function getAllPages() {
		return Saint_Model_Page::getAllPages();
	}
	
	/**
	 * Shortcut for Saint_Model_Category::getCategories()
	 * @return string[] Array with keys matching category ID and values matching category name.
	 */
	public static function getAllCategories() {
		return Saint_Model_Category::getCategories();
	}
	
	/**
	 * Get the action log for the current user.
	 * @return string[] Array of action log entries.
	 */
	public static function getActionLog() {
		if (!isset($_SESSION['actionlog']))
			$_SESSION['actionlog'] = array();
		return $_SESSION['actionlog'];
	}
	
	/**
	 * Add an entry to the action log.
	 * @param string $newentry New log entry.
	 * @return string[] Updated action log.
	 */
	public static function addLogEntry($newentry) {
		if (!isset($_SESSION['actionlog']))
			$_SESSION['actionlog'] = array();
		array_unshift($_SESSION['actionlog'],$newentry);
		$_SESSION['actionlog'] = array_splice($_SESSION['actionlog'],0,25);
		return $_SESSION['actionlog'];
	}
	
	/**
	 * Remove an entry from the action log.
	 * @param int $key Key of entry to remove.
	 * @return string[] Updated action log.
	 */
	public static function removeLogEntry($key) {
		if (isset($_SESSION['actionlog'][$key]))
			unset($_SESSION['actionlog'][$key]);
		return $_SESSION['actionlog'];
	}
	
	/**
	 * Clear action log.
	 */
	public static function purgeActionLog() {
		$_SESSION['actionlog'] = array();
		return 1;
	}
	
	/**
	 * Get the site title.
	 * @return string Site title.
	 */
	public static function getSiteTitle() {
		try {
			return Saint::getOne("SELECT `title` FROM `st_config`");
		} catch (Exception $e) {
			Saint::logWarning("Problem getting site title: ".$e->getMessage(),__FILE__,__LINE__);
			return '';
		}
	}
	
	/**
	 * Get the site description.
	 * @return string Site description.
	 */
	public static function getSiteDescription() {
		try {
			return Saint::getOne("SELECT meta_description FROM st_config");
		} catch (Exception $e) {
			Saint::logWarning("Problem getting site description: ".$e->getMessage(),__FILE__,__LINE__);
			return '';
		}
	}
	
	/**
	 * Get a model for applying sales discounts.
	 * Model is cached after first access for performance.
	 * return Saint_Model_Discount Current sales discounts.
	 */
	public static function getDiscounter() {
		global $_pc;
		return $_pc->getDiscounter();
	}
	
	/**
	 * Get the ID of the current user's shopping cart.
	 * @return int Model ID for shopping cart of current user.
	 */
	public static function getShoppingCartId() {
		$user = Saint::getCurrentUser(); 
		if ($user->getId() != 0) {
			return $user->getShoppingCartId();
		} else {
			if (!isset($_SESSION['saint_scid']) || !Saint_Model_ShoppingCart::isActive($_SESSION['saint_scid'])) {
				$cart = new Saint_Model_ShoppingCart();
				$_SESSION['saint_scid'] = $cart->loadNew();
			}
			return $_SESSION['saint_scid'];
		}
	}
	
	/**
	 * Get the current user's shopping cart.
	 * @return Saint_Model_ShoppingCart Current user's shopping cart.
	 */
	public static function getShoppingCart() {
		$cart = new Saint_Model_ShoppingCart();
		$cart->load(Saint::getShoppingCartId());
		return $cart;
	}
	
	/**
	 * Get the currently selected language.
	 * @return string Name of the current language.
	 */
	public static function getCurrentLanguage() {
		return Saint_Model_Language::getCurrentLanguage();
	}
	
	/**
	 * Get the default language.
	 * @return string Name of the default language.
	 */
	public static function getDefaultLanguage() {
		return Saint_Model_Language::getDefaultLanguage();
	}
	
	/**
	 * Get layout matching given name.
	 * @param string $name Name of layout.
	 * @return Saint_Model_Layout Layout matching parameter name if found, false if not.
	 */
	public static function getLayout($name) {
		$layout = new Saint_Model_Layout();
		try {
			$layout->loadByName($name);
			return $layout;
		} catch (Exception $e) {
			return 0;
		}
	}
	
	/**
	 * Get a list of in-use layout names.
	 * @return string[] Array of layout names.
	 */
	public static function getLayoutNames() {
		$layouts = array();
		$user = glob(Saint::getThemeDir()."/blocks/layouts/*.php");
		$core = glob(SAINT_SITE_ROOT."/core/blocks/layouts/*.php");
		foreach (array_merge($user,$core) as $name) {
			$name = preg_replace('/^.*\/([^\/]*)\.php$/','$1',$name);
			if (!in_array($name,$layouts))
				$layouts[] = $name;
		}
		return $layouts;
	}
	
	/**
	 * Add a page to the site.
	 * @param string $name Name for new page.
	 * @param string $layout Name of layout to use for new page.
	 * @param string $title Title for new page.
	 * @param string $keywords Keywords for page.
	 * @param string $description Description for page.
	 * @return boolean True for success, false for failure.
	 */
	public static function addPage($name,$layout,$title='',$keywords='',$description='',$cats=array()) {
		return Saint_Model_Page::addPage($name,$layout,$title,$keywords,$description,$cats);
	}
	
	/**
	 * Remove a page from the site.
	 * @param int $id Model ID to be removed
	 */
	public static function deletePage($id) {
		return Saint_Model_Page::deletePage($id);
	}
	
	/**
	 * Start the page pre-rendering process.
	 * @param string $name Name of page to call.
	 * @param string[] $args Array of URI arguments on current page.
	 * @return boolean True if page found, false otherwise
	 */
	public static function callPage($name,$args) {
		global $_pc;
		# With maintenance mode enabled only users having proper access can view the site.
		# Of a necessity the login page is exempted from this limitation.
		if (SAINT_MAINT_MODE && $name != "login" && !Saint::getCurrentUser()->hasPermissionTo("maintenance-mode")) {
			try {
				$_pc = new Saint_Controller_Page("maintenance",$args);
			} catch (Exception $e) {
				Saint::logWarning($e->getMessage(),__FILE__,__LINE__);
				$_pc = new Saint_Controller_Page("404",$args);
			}
		} else {
			try {
				$_pc = new Saint_Controller_Page($name,$args);
			} catch (Exception $e) {
				Saint::logWarning($e->getMessage(),__FILE__,__LINE__);
				$_pc = new Saint_Controller_Page("404",$args);
			}
		}
		$_pc->process();
	}

	/**
	 * Change the current page.
	 * @param Saint_Model_Page $page New page to use.
	 */
	public static function setCurrentPage($page) {
		global $_pc;
		return $_pc->setCurrentPage($page);
	}
	
	/**
	 * Get the current page.
	 * @return Saint_Model_Page Currently running page.
	 */
	public static function getCurrentPage() {
		global $_pc;
		return $_pc->getCurrentPage();
	}
	
	/**
	 * Include maintenance block.
	 */
	public static function renderMaintenance() {
		Saint::includeBlock("layouts/maintenance");
	}
	
	/**
	 * Add message to the error log.
	 * @param string $message Message to add to the error log.
	 * @param string $file File in which the error occurred.
	 * @param string $line Line on which the error occurred.
	 */
	public static function logError($message, $file = null, $line = null) {
		Saint::addLogEntry($message);
		if (SAINT_LOG_LEVEL >= 1) {
			$rootpattern = preg_replace('/\//','\/',SAINT_SITE_ROOT);
			$file = preg_replace("/$rootpattern/",'',$file);
			if (isset($line))
				$message = "line " . $line . " " . $message;
			if (isset($file))
				$message = $file . " " . $message;
			$fh = fopen(SAINT_ERR_FILE, 'a') or die("Critical Error: Could not open error file for writing. Please contact the site administrator. If this is your site, check the config.php file and ensure the permissions on your log files and directories are correct (they should be 777).");
			fwrite($fh, "\n" . date('Y-m-d H:i:s') . ' ' . $message . "\n");
			fclose($fh);
		}
	}

	/**
	 * Add message to the warning log.
	 * @param string $message Message to add to the warning log.
	 * @param string $file File in which the warning occurred.
	 * @param string $line Line on which the warning occurred.
	 */
	public static function logWarning($message, $file = null, $line = null) {
		Saint::addLogEntry($message);
		if (SAINT_LOG_LEVEL >= 2) {
			$rootpattern = preg_replace('/\//','\/',SAINT_SITE_ROOT);
			$file = preg_replace("/$rootpattern/",'',$file);
			if (isset($line))
				$message = $line . " " . $message;
			if (isset($file))
				$message = $file . " " . $message;
			$fh = fopen(SAINT_WARN_FILE, 'a') or Saint::logError("Problem opening warning file (Dir: ".SAINT_LOG_DIR."  File: ".SAINT_WARN_FILE.") for writing. Check config.php and ensure the permissions on your log files and directories are correct (they should be 777).",__FILE__,__LINE__);
			fwrite($fh, "\n" . date('Y-m-d H:i:s') . ' ' . $message . "\n");
			fclose($fh);
		}
	}

	/**
	 * Add message to the event log.
	 * @param string $message Message to add to the event log.
	 * @param string $file File in which the event occurred.
	 * @param string $line Line on which the event occurred.
	 */
	public static function logEvent($message, $file = null, $line = null) {
		Saint::addLogEntry($message);
		if (SAINT_LOG_LEVEL >= 3) {
			$rootpattern = preg_replace('/\//','\/',SAINT_SITE_ROOT);
			$file = preg_replace("/$rootpattern/",'',$file);
			if (isset($line))
				$message = $line . " " . $message;
			if (isset($file))
				$message = $file . " " . $message;
			$fh = fopen(SAINT_EVENT_FILE, 'a') or Saint::logError("Problem opening event file (Dir: ".SAINT_LOG_DIR."  File: ".SAINT_EVENT_FILE.") for writing. Check config.php and ensure the permissions on your log files and directories are correct (they should be 777).",__FILE__,__LINE__);
			fwrite($fh, "\n" . date('Y-m-d H:i:s') . ' ' . $message . "\n");
			fclose($fh);
		}
	}

	/**
	 * Query the database and return a single cell.
	 * @param string $query Query to execute
	 * @return string First result from the database.
	 */
	public static function getOne($query) {
		if (!preg_match('/LIMIT/',$query))
			$query .= " LIMIT 1";
		$result = @mysql_query($query);
		if (!$result)
			throw new Exception(mysql_error(),1);
		if (!mysql_num_rows($result))
			throw new Exception("There were no results matching your query.",0);
		else {
			$accessrow = mysql_fetch_row($result);
			return $accessrow[0]; }
	}

	/**
	 * Query the database and return a single row.
	 * @param string $query Query to execute
	 * @return string[] First row from the database.
	 */
	public static function getRow($query) {
		if (!preg_match('/LIMIT/',$query))
			$query .= " LIMIT 1";
		$result = @mysql_query($query);
		if (!$result)
			throw new Exception(mysql_error(),1);
		if (!mysql_num_rows($result))
			throw new Exception("There were no results matching your query.",0);
		else {
			$accessrow = mysql_fetch_row($result);
			return $accessrow; }
	}

	/**
	 * Query the database and return all results.
	 * @param string $query Query to execute
	 * @return string[] Array containing arrays of data for each row.
	 */
	public static function getAll($query) {
		$result = @mysql_query($query);
		if (!$result)
			throw new Exception(mysql_error(),1);
		if (!mysql_num_rows($result))
			throw new Exception("There were no results matching your query.",0);
		else {
			$results = array();
			while ($row = mysql_fetch_row($result)) {
				if (isset($row[1]))
					$results[] = $row;
				else
					$results[] = $row[0];
			}
			return $results; }
	}
	
	/**
	 * Query the database and return the number of rows.
	 * @param string $query Query to execute.
	 * @return int Number of rows of results.
	 */
	public static function getNumRows($query) {
		$result = @mysql_query($query);
		if (!$result)
			throw new Exception(mysql_error(),1);
		return mysql_num_rows($result);
	}

	/**
	 * Query the database.
	 * @param string $query Query to execute.
	 * @return boolean True for success.
	 * @throws Exception Database error on failure.
	 */
	public static function query($query) {
		$result = @mysql_query($query);
		if (!$result)
			throw new Exception(mysql_error(),1);
		else
			return 1;	
	}
	
	/**
	 * Get last insert ID.
	 * @return int ID of last insert query.
	 */
	public static function getLastInsertId() {
		return mysql_insert_id();
	}
	
	/**
	 * Compile given search parameters into SQL.
	 * @param array[] $matches Arguments to match exactly. Also accepts single array of scalar values to match.
	 * @param array[] $search Arguments to match substrings. Also accepts single string.
	 * @return string Compiled query.
	 */
	public static function compileMatches($matches, $search = null) {
		$where = '';
		if (isset($matches[0]) && is_array($matches[0])) {
			foreach ($matches as $match) {
				if(isset($match[2]))
					$eq = Saint::sanitize($match[2]);
				else
					$eq = "=";
				$lsm = Saint::sanitize($match[0]);
				$rsm = Saint::sanitize($match[1]);
				$where .= " `$lsm`$eq'$rsm' AND";
			}
		} else {
			if (is_array($matches) && isset($matches[0]) && isset($matches[1])) {
				if(isset($matches[2]))
						$eq = Saint::sanitize($matches[2]);
					else
						$eq = "=";
				$where .= " `".Saint::sanitize($matches[0])."`$eq'".Saint::sanitize($matches[1])."' AND";
			}
		}
		
		if ($search != null) {
			if (isset($search[0]) && is_array($search[0])) {
				foreach ($search as $match)
					$where .= " `".Saint::sanitize($match[0])."` LIKE '".Saint::sanitize($match[1])."' AND";
			} else {
				$where .= " `".Saint::sanitize($search[0])."` LIKE '".Saint::sanitize($search[1])."' AND";
			}
		}
		
		if ($where != '')
			$where = "WHERE ".preg_replace('/\s*AND$/','',$where);
		
		return $where;
	}
	
	/**
	 * Shortcut for Saint_Model_Block::convertNameToWeb($name).
	 * @param string $name Name to be converted.
	 * @return string Converted name.
	 */
	public static function convertNameToWeb($name) {
		return Saint_Model_Block::convertNameToWeb($name);
	}
	
	/**
	 * Shortcut for Saint_Model_Block::convertFromToWeb($name).
	 * @param string $name Name to be reverted.
	 * @return string Reverted name.
	 */
	public static function convertNameFromWeb($name) {
		return Saint_Model_Block::convertNameFromWeb($name);
	}
	
	/**
	 * Includes a slideshow block with images matching the given arguments.
	 * @param string[] $arguments Optional slideshow arguments.
	 */
	public static function includeSlideshow($arguments = array()) {
		$page = Saint::getCurrentPage();
		$page->sfmarguments = $arguments;
		Saint::includeBlock("gallery/slideshow");
	}
	
	/**
	 * Includes a gallery block with images matching the given arguments.
	 * @param string[] $arguments Optional gallery arguments.
	 */
	public static function includeGallery($arguments = array()) {
		$page = Saint::getCurrentPage();
		$page->sfmarguments = $arguments;
		Saint::includeBlock("gallery/list");
	}
	
	/**
	 * Get code for CMS-editable image specific to the given block.
	 * @param string $block Name of block in which image is included.
	 * @param string $id Model ID of block in which image is included.
	 * @param string $name Name of image label.
	 * @param array[] $arguments Optional arguments for image label.
	 * @return string Code for image label.
	 */
	public static function getBlockImage($block, $id, $name, $arguments = array()) {
		$name = "block/" . $id . "/" . $block . "/n/" . $name;
		return Saint_Model_ImageLabel::getImage($name, $arguments);
	}
	
	/**
	 * Get code for CMS-editable image specific to the current page.
	 * @param string $name Name of image label.
	 * @param $arguments Optional arguments for image label.
	 * @return string Code for image label.
	 */
	public static function getPageImage($name, $arguments = array()) {
		$name = "page/" . Saint::getCurrentPage()->getName() . "/n/" . $name;
		return Saint_Model_ImageLabel::getImage($name, $arguments);
	}
	
	/**
	 * Get code for CMS-editable image general to the entire site.
	 * @param string $name Name of image label.
	 * @param $arguments Optional arguments for image label.
	 * @return string Code for image label.
	 */
	public static function getImage($name, $arguments = array()) {
		return Saint_Model_ImageLabel::getImage($name, $arguments);
	}
	
	/**
	 * Get code for CMS-editable WYSIWYG area specific to the given block.
	 * @param string $block Name of block in which the label is included.
	 * @param string $id Model ID of block in which the label is included.
	 * @param string $name Name of WYSIWYG label.
	 * @param string $default Default content to be inserted on first generation.
	 * @return string Code for WYSIWYG block.
	 */
	public static function getBlockWysiwyg($block, $id, $name, $default = '') {
		$name = "block/" . $id . "/" . $block . "/n/" . $name;
		return Saint_Model_Wysiwyg::get($name,$default);
	}
	
	/**
	 * Get code for CMS-editable WYSIWYG area specific to the current page.
	 * @param string $name Name of WYSIWYG label.
	 * @param string $default Default content to be inserted on first generation.
	 * @return string Code for WYSIWYG block.
	 */
	public static function getPageWysiwyg($name, $default = '') {
		$name = "page/" . Saint::getCurrentPage()->getName() . "/n/" . $name;
		return Saint_Model_Wysiwyg::get($name,$default);
	}
	
	/**
	 * Get code for CMS-editable WYSIWYG area general to the entire site.
	 * @param string $name Name of WYSIWYG label.
	 * @param string $default Default content to be inserted on first generation.
	 * @return string Code for WYSIWYG block.
	 */
	public static function getWysiwyg($name, $default = '') {
		return Saint_Model_Wysiwyg::get($name,$default);
	}
	
	/**
	 * Get code for CMS-editable text area specific to the current page.
	 * @param string $name Label name.
	 * @param string $default Label default content.
	 * @param boolean $container True to include wrapper div, false otherwise.
	 * @param string $lang Language to use for label. Defaults to current user's selected language.
	 * @param boolean $wysiwyg True to make label editor WYSIWYG, false by default.
	 * @return string Code for selected label.
	 */
	public static function getPageLabel($name, $default = '', $container = true, $lang = null, $wysiwyg = false, $revision = 0) {
		$name = "page/" . Saint::getCurrentPage()->getId() . "/n/" . $name;
		return Saint::getLabel($name,$default,$container,$lang,$wysiwyg,$revision);
	}
	
	/**
	 * Get code for CMS-editable text area specific to the given block.
	 * @param string $block Name of block in which the label is included.
	 * @param string $id Model ID of block in which the label is included.
	 * @param string $name Label name.
	 * @param string $default Label default content.
	 * @param boolean $container True to include wrapper div, false otherwise.
	 * @param string $lang Language to use for label. Defaults to current user's selected language.
	 * @param boolean $wysiwyg True to make label editor WYSIWYG, false by default.
	 * @return string Code for selected label.
	 */
	public static function getBlockLabel($block, $id, $name, $default = '', $container = true, $lang = null, $wysiwyg = false, $revision = 0) {
		$name = "block/" . $id . "/" . $block . "/n/" . $name;
		return Saint::getLabel($name,$default,$container,$lang,$wysiwyg,$revision);
	}
	
	/**
	 * Called from templates when one wishes to display editable text.
	 * Also master function for shells getBlockLabel and getPageLabel.
	 * @param string $name Name of label
	 * @param string $default Default text for label
	 * @param Saint_Model_Page $page Page label is being called from
	 * @param string $lang Request label in this language
	 */
	public static function getLabel($name, $default = '', $container = true, $lang = null, $wysiwyg = false, $revision = 0) {
		$page = Saint::getCurrentPage();
		$label = new Saint_Model_Label();
		if ($label->loadByName($name)) {
			return $label->getLabel($container,$default, $lang, $revision);
		} else {
			$label->setLabel($default, $lang);
			$label->save();
			return $label->getLabel($container, $lang);
		}
	}
	
	/**
	 * Get the current starting number for repeating block paging system.
	 * @param int $btid Block type ID.
	 * @param int $start Default starting number.
	 */
	public static function getStartingNumber($btid,$start = 0) {
		$args = Saint::getCurrentPage()->getArgs();
		if(isset($args['btid']) && isset($args['pnum'])) {
			if ($args['btid'] == $btid)
				return Saint::sanitize($args['pnum']);
			else
				return $start;
		} else
			return $start;
	}
	
	/**
	 * Get the location of the active theme dir for the current site.
	 * @return string Location of the active theme.
	 */
	public static function getThemeDir() {
		$themedir = SAINT_SITE_ROOT . "/themes/" . SAINT_THEME;
		if (file_exists($themedir))
			return $themedir;
		else
			return SAINT_SITE_ROOT . "/core";
	}
	
	/**
	 * Get the URL of the active theme dir for the current site.
	 * @return string URL of the active theme.
	 */
	public static function getThemeUrl() {
		$themedir = SAINT_SITE_ROOT . "/themes/" . SAINT_THEME;
		if (file_exists($themedir))
			return SAINT_URL . "/themes/" . SAINT_THEME;
		else
			return SAINT_URL . "/core";
	}
	
	/**
	 * Shortcut for Saint_Model_Block::includeRepeatingBlock($block, $arguments, $container, $view).
	 */
	public static function includeRepeatingBlock($block, $arguments = array()) {
		return Saint_Model_Block::includeRepeatingBlock($block, $arguments);
	}

	/**
	 * Shortcut for Saint_Model_Block::getBlockSetting($blockname,$blockid,$settingname).
	 */
	public static function getBlockSetting($blockname,$blockid,$settingname) {
		return Saint_Model_Block::getBlockSetting($blockname,$blockid,$settingname);
	}

	/**
	 * Shortcut for Saint_Model_Block::setBlockSetting($blockname,$blockid,$settingname,$newvalue).
	 */
	public static function setBlockSetting($blockname,$blockid,$settingname,$newvalue) {
		return Saint_Model_Block::setBlockSetting($blockname,$blockid,$settingname,$newvalue);
	}
	
	/**
	 * Shortcut for Saint_Model_Block::getBlockUrl($blockname,$blockid, $page).
	 */
	public static function getBlockUrl($blockname,$blockid,$page = null) {
		return Saint_Model_Block::getBlockUrl($blockname,$blockid, $page);
	}
	
	/**
	 * Get the URL used for the blog RSS feed.
	 * @todo Update blog feed URL to be CMS-editable.
	 * @return string URL for the RSS feed.
	 */
	public static function getBlogRssUrl() {
		return SAINT_URL . '/feed';
	}

	/**
	 * Shortcut for Saint_Model_Block::includeBlock($block,$container, $view).
	 */
	public static function includeBlock($block, $arguments = array()) {
		return Saint_Model_Block::includeBlock($block, $arguments);
	}
	
	/**
	 * Include a style of passed name with preference given to user directory.
	 * @param string $style Name of style file to include. 
	 */
	public static function includeStyle($style) {
		if ($style = Saint::sanitize($style,SAINT_REG_NAME)) {
			if (file_exists(Saint::getThemeDir() .  "/styles/".$style.".css"))
				echo '<link rel="stylesheet" type="text/css" href="'.Saint::getThemeUrl().'/styles/'.$style.'.css" />';
			elseif (file_exists(SAINT_SITE_ROOT .  "/core/styles/".$style.".css"))
				echo '<link rel="stylesheet" type="text/css" href="'.SAINT_URL.'/core/styles/'.$style.'.css" />';
			else
				Saint::logWarning("Cannot find style $style.");
		}
	}

	/**
	 * Include a script of passed name with preference given to user directory.
	 * @param string $script Name of script file to include. 
	 */
	public static function includeScript($script) {
		if ($script = Saint::sanitize($script,SAINT_REG_NAME)) {
			if (file_exists(Saint::getThemeDir() . "/scripts/".$script.".js"))
				echo '<script type="text/javascript" src="'.Saint::getThemeUrl().'/scripts/'.$script.'.js"></script>';
			elseif (file_exists(SAINT_SITE_ROOT .  "/core/scripts/".$script.".js"))
				echo '<script type="text/javascript" src="'.SAINT_URL.'/core/scripts/'.$script.'.js"></script>';
			else
				Saint::logWarning("Cannot find script $script.");
		}
	}
	
	/**
	 * Generates and returns a form field with the given parameters.
	 * @param string $name Name of input field.
	 * @param string $type Type of input field.
	 * @param string $label Optional label for input field.
	 * @param string[] $data Optional data for input field (value, select options, etc).
	 * @param string $rules Optional jQuery validation rules for input field.
	 */
	public static function genField ($name,$type,$label = '',$data = null,$rules = '') {
		$field = '';
		$label = Saint::getPageLabel("sff-".$name,$label);
		if (isset($data['value']))
			$val = $data['value'];
		else
			$val = '';
		if (isset($data['classes']))
			$classes = $data['classes'];
		else
			$classes = '';
		switch ($type) {
			case 'radio':
				$field .= $label . '<input type="radio" id="'.$name.'" name="'.$name.'" value="'.$val.'" class="'.$classes.'" />'."\n";
				break;
			case 'check':
				$field .= $label . '<input type="checkbox" id="'.$name.'" name="'.$name.'" class="'.$classes.'" />' . $label . "\n";
				break;
			case 'hidden':
				$field .= '<input type="hidden" id="'.$name.'" name="'.$name.'" class="'.$classes.'" value="'.$val.'" />' . "\n";
				break;
			case 'text':
				$field .= $label . '<input type="text" id="'.$name.'" name="'.$name.'" class="'.$classes.'" value="'.$val.'" />' . "\n";
				break;
			case 'textarea':
				$field .= $label . '<textarea id="'.$name.'" name="'.$name.'" class="'.$classes.'">'.$val.'</textarea>' . "\n";
				break;
			case 'select':
				if (!isset($data['options']))
					break;
				if (isset($data['selected'])) {
					if (is_array($data['selected']))
						$selected = $data['selected'];
					else
						$selected = array($data['selected']);
				} else
					$selected = array();
				if (isset($data['multiple']))
					$multiple = $data['multiple'];
				else
					$multiple = false;
				if ($multiple)
					$multiple = ' multiple="multiple"';
				else
					$multiple = '';
				$field .= $label . "\n";
				$selid = preg_replace('/\[\]$/','',$name);
				$field .= "<select id=\"$selid\" name=\"$name\" class=\"$rules\"$multiple>\n";
				foreach ($data['options'] as $opt=>$label) {
					if (in_array($opt,$selected))
						$sel = ' selected="selected"';
					else
						$sel = '';
					$field .= '<option value="'.$opt.'"'.$sel.'>'.$label.'</option>';
				}
				$field .= "</select>\n";
				break;
		}
		return $field;
	}
	
	/**
	 * Search the labels in the database for content.
	 * @todo Update search to include WYSIWYG labels and block settings.
	 * @param string $phrase Phrase for which to search.
	 * @return array[] Matching labels and URLs of pages on which the label can be found.
	 */
	public static function search($phrase) {
		$phrase = Saint::sanitize($phrase);
		$phrase = "%$phrase%";
		$results = array();
		try {
			$labels = Saint::getAll("SELECT `id`,`name` FROM `st_labels` WHERE `label` LIKE '$phrase'");
			foreach ($labels as $label) {
				try {
					$maxid = Saint::getOne("SELECT MAX(`id`) FROM `st_labels` WHERE `name`='$label[1]'");
					if ($label[0] == $maxid) {
						// Isolate block name from label name
						if (preg_match('/^block\/(\d*)\/(.*)\/n\/.*$/',$label[1],$matches)) {
							$bid = $matches[1];
							$bname = $matches[2];
							$result_block = new Saint_Model_Block();
							$result_block->load($bname,$bid);
							$resultpages = $result_block->getAllUrls();
							
							foreach ($resultpages as $rp) {
								if (!isset($results[$rp[0]]))
									$results[$rp[0]] = array($rp[1],array($label[1]));
								else
									$results[$rp[0]][1][] = $label[1];
							}
						} elseif (preg_match('/^page\/(\d*)\/n\/.*$/',$label[1],$matches)) {
							$pid = $matches[1];
							$result_page = new Saint_Model_Page();
							if ($result_page->loadById($pid)) {
								if (!isset($results[$pid]))
									$results[$pid] = array($result_page->getUrl(),array($label[1]));
								else
									$results[$pid][1][] = $label[1];
							}
						}
					}
				} catch (Exception $f) {
					if ($f->getCode()) {
						Saint::logError("Problem getting max id for label '$label[1]': ".$f->getMessage());
					}
				}
			}
			return $results;
		} catch (Exception $e) {
			if ($e->getCode()) {
				Saint::logError("Unable to select labels for search: ".$e->getMessage(),__FILE__,__LINE__);
			}
			return array();
		}
	}
}
