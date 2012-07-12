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
			Saint::getOne("SELECT `u`.`id` FROM `st_users` as `u`,`st_user_groups` as `g` WHERE `g`.`group`='administrator' AND `g`.`userid`=`u`.`id` LIMIT 1");
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
	 * Shortcut for Saint_Model_User::getUsers().
	 * @return Saint_Model_User[] Array of site users.
	 */
	public static function getUsers($filters = array()) {
		return Saint_Model_User::getUsers($filters);
	}
	
	/**
	 * Shortcut for Saint_Model_Page::getPages().
	 * @return Saint_Model_Page[] Array of all site pages.
	 */
	public static function getPages($filters = array()) {
		return Saint_Model_Page::getPages($filters);
	}
	
	/**
	 * Return array with index of public site pages.
	 * @return array Index of public site pages.
	 */
	public static function getIndex() {
		$index = array();
		$main_pages = Saint::getPages(array(
			'layout' => array(
				'logical_operator' => 'AND',
				'comparison_operator' => 'NOT LIKE',
				'match_all' => 'system/%',
			),
			'allow_robots' => array(
				'logical_operator' => 'AND',
				'comparison_operator' => '!=',
				'match_all' => 0,
			),
			'parent' => 0,
		));
		foreach ($main_pages as $mp) {
			$index[] = array($mp->getUrl(),$mp->getTitle(),$mp->getLastModified(),$mp->getIndex());
		}
		return $index;
	}
	
	/**
	 * Display site index.
	 * @param boolean $xml Flag true to display in XML format, false by default for HTML format.
	 */
	public static function includeIndex($xml = false) {
		if ($xml) {
			Saint::includeBlock("index/xml");
		} else {
			Saint::includeBlock("index/index");
		}
	}
	
	/**
	 * Shortcut for Saint_Model_Category::getCategories()
	 * @return string[] Array with keys matching category ID and values matching category name.
	 */
	public static function getCategories($filters = array()) {
		return Saint_Model_Category::getCategories($filters);
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
			return Saint::getOne("SELECT `description` FROM `st_config`");
		} catch (Exception $e) {
			Saint::logWarning("Problem getting site description: ".$e->getMessage(),__FILE__,__LINE__);
			return '';
		}
	}
	
	/**
	 * Get the site keywords.
	 * @return string Site keywords as comma separated values.
	 */
	public static function getSiteKeywords() {
		try {
			$keywords = Saint::getOne("SELECT `keywords` FROM `st_config`");
			if ($keywords == "")
				$keywords = array();
			else
				$keywords = explode(',',$keywords);
			return $keywords;
		} catch (Exception $e) {
			Saint::logWarning("Problem getting site keywords: ".$e->getMessage(),__FILE__,__LINE__);
			return '';
		}
	}
	
	/**
	 * Set the site title.
	 * @return Boolean true on success, false otherwise.
	 */
	public static function setSiteTitle($title) {
		$stitle = Saint::sanitize($title);
		try {
			Saint::query("UPDATE `st_config` SET `title`='$stitle'");
			return 1;
		} catch (Exception $e) {
			Saint::logWarning("Problem setting site title: ".$e->getMessage(),__FILE__,__LINE__);
			return 0;
		}
	}
	
	/**
	 * Set the site description.
	 * @return Boolean true on success, false otherwise.
	 */
	public static function setSiteDescription($description) {
		$sdescription = Saint::sanitize($description);
		try {
			Saint::query("UPDATE `st_config` SET `description`='$sdescription'");
			return 1;
		} catch (Exception $e) {
			Saint::logWarning("Problem setting site description: ".$e->getMessage(),__FILE__,__LINE__);
			return 0;
		}
	}

	/**
	 * Set the site keywords.
	 * @return Boolean true on success, false otherwise.
	 */
	public static function setSiteKeywords($keywords) {
		$skeywords = Saint::sanitize($keywords);
		try {
			Saint::query("UPDATE `st_config` SET `keywords`='$skeywords'");
			return 1;
		} catch (Exception $e) {
			Saint::logWarning("Problem setting site keywords: ".$e->getMessage(),__FILE__,__LINE__);
			return 0;
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
	 * Shortcut for creating collections with Saint_Model_Block::getBlocks().
	 */
	public static function getCollection($block,$arguments = array()) {
		$arguments['collection'] = true;
		return Saint_Model_Block::getBlocks($block,$arguments);
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
		return Saint_Model_Layout::getLayoutNames();
	}
	
	/**
	 * Add a page to the site; shortcut for Saint_Model_Page::addPage.
	 */
	public static function addPage($name,$options = array()) {
		return Saint_Model_Page::addPage($name,$options);
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
		$result = Saint::queryDb($query);
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
		$result = Saint::queryDb($query);
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
		$result = Saint::queryDb($query);
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
		$result = Saint::queryDb($query);
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
		$result = Saint::queryDb($query);
		if (!$result)
			throw new Exception(mysql_error(),1);
		else
			return 1;	
	}
	
	/**
	 * Wrapper for mysql_query with profiling code added.
	 * @param string $query
	 */
	private static function queryDb($query) {
		if (SAINT_PROFILING) {
			$mtime = microtime();
			$mtime = explode(" ",$mtime);
			$mtime = $mtime[1] + $mtime[0];
			$starttime = $mtime;
		}
		$result = @mysql_query($query);
		if (SAINT_PROFILING) {
			global $profiling_events;
			global $script_start;
			$mtime = microtime();
			$mtime = explode(" ",$mtime);
			$mtime = $mtime[1] + $mtime[0];
			$endtime = $mtime;
			$totaltime = ($endtime - $starttime)*1000;
			$profiling_events[] = $query . "\n" . "Executed in $totaltime ms from ".($starttime-$script_start)*1000 . " ms to " . ($endtime-$script_start)*1000 . " ms";
		}
		return $result;
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
		$block = new Saint_Model_Block();
		$block->setFiles(Saint_Model_FileManager::getAllFiles($arguments));
		if (isset($arguments['width']))
			$block->set("width",$arguments['width']);
		else
			$block->set("width",null);
		if (isset($arguments['height']))
			$block->set("height",$arguments['height']);
		else
			$block->set("height",null);
		Saint::includeBlock("slideshow/slideshow",array('repeat'=>1,'blocks'=>array($block),'container'=>false));
	}
	
	/**
	 * Includes a gallery block with images matching the given arguments.
	 * @param string[] $arguments Optional gallery arguments.
	 */
	public static function includeGallery($arguments = array()) {
		$page = Saint::getCurrentPage();
		$block = new Saint_Model_Block();
		#$arguments['results-per-page'] = 2;
		if (isset($arguments['results-per-page']) && $arguments['results-per-page'] != "") {
			$block->set("results-per-page",$arguments['results-per-page']);
		} else {
			$block->set("results-per-page",15);
		}
		if (isset($args['p']) && $args['p'] != "") {
			$block->set("page-number",$args['p']);
		} else {
			$block->set("page-number",0);
		}
		$arguments['page-number'] = $block->get("page-number");
		
		$files = Saint_Model_FileManager::getAllFiles($arguments);
		$page->setFiles($files);
		$block->set("number-of-results",sizeof($files));

		$block->set("number-of-pages",$block->get("number-of-results") / $block->get("results-per-page"));
		Saint::includeBlock("gallery/gallery",array('repeat'=>1,'blocks'=>array($block),'container'=>false));
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
	 * Called from templates when one wishes to display editable text.
	 * Also master function for shells getBlockLabel and getPageLabel.
	 * @param string $name Name of label
	 * @param string $default Default text for label
	 * @param Saint_Model_Page $page Page label is being called from
	 * @param string $lang Request label in this language
	 */
	public static function getLabel($name, $default = '', $options = array()) {
		$options['default'] = $default;
		$label = new Saint_Model_Label();
		$label->loadByName($name);
		return $label->getLabel($options);
	}
	
	/**
	 * Wrapper function to auto-output labels.
	 */
	public static function includeLabel($name, $default = '', $options = array()) {
		echo Saint::getLabel($name, $default, $options);
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
		if (SAINT_THEME != "" && file_exists($themedir))
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
		if (SAINT_THEME != "" && file_exists($themedir))
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
	 * Shortcut for Saint_Model_Block::includeBlock($block,$container, $view).
	 */
	public static function includeBlock($block, $arguments = array()) {
		return Saint_Model_Block::includeBlock($block, $arguments);
	}
	
	/**
	 * Shortcut function to give command to buffer output to string and return value.
	 */
	public static function getBlock($block, $arguments = array()) {
		$arguments['get'] = true;
		return Saint_Model_Block::includeBlock($block,$arguments);
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
	 * Match given filters against available option types to create the conditional part of an SQL query.
	 * @param array $filters Conditions to match.
	 * @param array $options Allowable settings to filter by.
	 * @param string $table Table alias to which to apply conditions.
	 * @return string Conditional statement in SQL.
	 */
	public static function makeConditions($filters = array(), $options = array(), $table = '') {
		/* Example of possible filter content.
		$filters = array(
			'model' => 'Saint_Model_Page',
			'updated' => array(
				'logical_operator' => 'OR',
				'comparison_operator' => '>=',
				'match_all' => '2012-06-20 13:18:15',
				'match_one' => array('2012-06-20 13:18:15','2052-06-20 13:18:15'),
			),
		); */
		$sql = '';
		$and_results = array();
		$or_results = array();
		
		if ($table == "")
			$pre = "";
		else
			$pre = "`".Saint::sanitize($table)."`.";
		
		# Cycle through available options and add check each for matching filters.
		foreach ($options as $opt) {
			
			# If a filter is set, generate an SQL condition
			if (isset($filters[$opt])) {
				$co = '=';
				$lo = 'AND';
				
				# If our filter is an array there optional settings to use.
				if (is_array($filters[$opt])) {
					
					# Choose the logical operator (AND, OR, AND NOT, OR NOT)
					if (isset($filters[$opt]['logical_operator'])) {
						$lo = strtoupper(Saint::sanitize($filters[$opt]['logical_operator']));
					}
					
					# Choose the comparison operator (>, =, <, IS, LIKE, NOT LIKE, etc)
					if (isset($filters[$opt]['comparison_operator'])) {
						$co = Saint::sanitize($filters[$opt]['comparison_operator']);
					}
					
					# Match all of the given match_all values.
					if (isset($filters[$opt]['match_all'])) {
						if (is_array($filters[$opt]['match_all'])) {
							$match_all = $filters[$opt]['match_all'];
						} else {
							$match_all = array($filters[$opt]['match_all']);
						}
					} else {
						$match_all = array();
					}
					
					# Match at least one of the given match_one values.
					if (isset($filters[$opt]['match_one'])) {
						if (is_array($filters[$opt]['match_one'])) {
							$match_one = $filters[$opt]['match_one'];
						} else {
							$match_one = array($filters[$opt]['match_one']);
						}
					} else {
						$match_one = array();
					}
					
				} else {
					# Since no sub-array of options was passed we simply use the given scalar to match.
					$match_one = array($filters[$opt]);
					$match_all = array();
				}
				
				if (preg_match('/AND/',$lo)) {
					$and_results[] = array($opt,$co,$lo,$match_one,$match_all);
				} else {
					$or_results[] = array($opt,$co,$lo,$match_one,$match_all);
				}
			}
		}
		
		$results = array($and_results,$or_results);
		
		for ($r = 0; $r < 2; $r++) {
			foreach ($results[$r] as $cond) {
				$opt = $cond[0];
				$co = $cond[1];
				$lo = $cond[2];
				$match_one = $cond[3];
				$match_all = $cond[4];
				
				# Add a wrapper if there are values to match.
				if (sizeof($match_one) || sizeof($match_all)) {
					$sql .= " $lo (";
					
					$i = 0;
					
					# Now we add each value to the query.
					foreach ($match_one as $val) {
						if ($i != 0) {
							$sql .= " OR ";
						}
						$sql .= "$pre`$opt` $co '$val'";
						$i++;
					}
					foreach ($match_all as $val) {
						if ($i != 0) {
							$sql .= " AND ";
						}
						$sql .= "$pre`$opt` $co '$val'";
						$i++;
					}
					
					$sql .= ")";
				}
			}
		}
		
		# If conditions were added we'll need to clean up the edges of the query.
		if (strlen($sql) > 0) {
			$sql = preg_replace('/^\s+(AND|OR)\s*(.*)$/',' WHERE $2',$sql);
		}
	
		return $sql;
	}

	/**
	 * Get SQL order statements based on given filters.
	 * @param array $filters Order by column and sort order options.
	 * @return string SQL code matching filters.
	 */
	public static function getOrder($filters = array()) {
		if (isset($filters['orderby'])) {
			if (isset($filters['order'])) {
				$order = Saint::sanitize($filters['order']);
			} else {
				$order = "DESC";
			}
			return " ORDER BY `".Saint::sanitize($filters['orderby'])."` $order";
		} else {
			return '';
		}
	}
	
	/**
	 * Shortcut function for Saint_Model_Page::rankPages($pages).
	 */
	public static function rankPages($pages = array()) {
		return Saint_Model_Page::rankPages($pages);
	}
	
	/**
	 * Generates and returns a form field with the given parameters.
	 * @param string $name Name of input field.
	 * @param string $type Type of input field.
	 * @param string $label Optional label for input field.
	 * @param string[] $data Optional data for input field (value, select options, etc).
	 */
	public static function genField ($name,$type,$label = '',$data = null) {
		$field = '';
		if (!isset($data['static']) || $data['static'] == false)
			$label = Saint::getCurrentPage()->getLabel("sff-".preg_replace('/[\[\]]*/','',$name),$label);
		else
			$label = '<span class="saint-label">'.$label.'</span>';
		if (isset($data['value']))
			$val = $data['value'];
		else
			$val = '';
		if (isset($data['classes']))
			$classes = $data['classes'];
		else
			$classes = '';
		if (isset($data['rules']))
			$classes .= " " . $data['rules'];
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
				if (isset($data['password']) && $data['password']) {
					$text = "password";
				} else {
					$text = "text";
				}
				$field .= $label . '<input type="'.$text.'" id="'.$name.'" name="'.$name.'" class="'.$classes.'" value="'.$val.'" />' . "\n";
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
				$field .= "<select id=\"$selid\" name=\"$name\" class=\"$classes\"$multiple>\n";
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
	 * @param string $phrase Phrase for which to search.
	 * @return array[] Matching labels and URLs of pages on which the label can be found.
	 */
	public static function search($phrase) {
		$sans_phrase = Saint::sanitize($phrase);
		$phrase = "%$sans_phrase%";
		$results = array();
		try {
			$labels = Saint::getAll("SELECT `id`,`name` FROM `st_labels` WHERE `label` LIKE '$phrase'");
			foreach ($labels as $label) {
				try {
					$maxid = Saint::getOne("SELECT MAX(`id`) FROM `st_labels` WHERE `name`='$label[1]'");
					if ($label[0] == $maxid) {
						$result_value = Saint::getLabel($label[1],array('container'=>false));
						
						# Case independent highlighting
				    $lbl_length = strlen($sans_phrase);
				    $lbl_start = 0;
				    $done = false;
						while (!$done) {
							$lbl_start = stripos($result_value, $sans_phrase, $lbl_start);
							if ($lbl_start !== false) {
								$result_value = substr($result_value, 0, $lbl_start) . "<b>" . substr($result_value, $lbl_start, $lbl_length) . "</b>" .
								substr($result_value, $lbl_start + $lbl_length);
								$lbl_start = $lbl_start + $lbl_length;
							} else $done = true;
						}
				    
				    # Isolate block name from label name
						if (preg_match('/^block\/(\d*)\/(.*)\/n\/.*$/',$label[1],$matches)) {
							$bid = $matches[1];
							$bname = $matches[2];
							$bmodel = Saint_Model_Block::getBlockModel($bname);
							$result_block = new $bmodel();
							if ($result_block->load($bname,$bid)) {
								$rp = $result_block->getUrl();
								$results[$result_block->getUrl()] = array($result_block->get("title"),array($result_value));
							}
								
						} elseif (preg_match('/^page\/(\d*)\/n\/.*$/',$label[1],$matches)) {
							$pid = $matches[1];
							$result_page = new Saint_Model_Page();
							if ($result_page->loadById($pid)) {
								$results[$result_page->getUrl()] = array($result_page->getTitle(),array($result_value));
							}
						}
					}
				} catch (Exception $f) {
					if ($f->getCode()) {
						Saint::logError("Problem getting max id for label '$label[1]': ".$f->getMessage());
					}
				}
			}
		} catch (Exception $e) {
			if ($e->getCode()) {
				Saint::logError("Unable to select labels for search: ".$e->getMessage(),__FILE__,__LINE__);
			}
		}
		
		return $results;
	}
}
