<?php

class Saint {
	static protected $_pc;
	static protected $_user;
	
	/**
	 * Starts the installation process
	 */
	public static function runInstall() {
		include SAINT_SITE_ROOT . "/core/installer/install.php";
	}
	
	/**
	 * Sanitize input for use in database queries and filesystem traversal
	 * @param string $input Input to sanitize
	 * @param string $pattern Optional regex pattern to match
	 */
	public static function sanitize($input, $pattern = null) {
		$safe = mysql_real_escape_string($input);
		if (($pattern == null || preg_match($pattern,$safe)) && !preg_match('/\.\.\//',$safe))
			return $safe;
		else
			return 0;
	}
	
	/**
	 * @todo Add proper conflict testing and error reporting
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
	 * @return boolean True if there exists at least one administrative user in the database, false otherwise
	 */
	public static function siteHasAdmin() {
		try {
			Saint::getOne("SELECT `username` FROM `st_users` WHERE `access_level`=0 LIMIT 1");
			return 1;
		} catch (Exception $f) {
			Saint::logError($f->getMessage());
			return 0;
		}
	}
	
	public static function getSiteOwner() {
		try {
			$owner = Saint::getOne("SELECT `owner` FROM `st_config`");
			if ($owner == null)
				throw new Exception("Null owner.");
			return $owner;
		} catch (Exception $e) {
			try {
				return Saint::getOne("SELECT username FROM st_users WHERE access_level=0 LIMIT 1");
			} catch (Exception $f) {
				Saint::logError("Your site has no admin users... how did that happen? Reinstall the cms or see the documentation to add a user manually.",__FILE__,__LINE__);
				die();
			}
		}
	}
	
	public static function addNotice($notice) {
		if (isset($_SESSION['saint_notice']) && is_array($_SESSION['saint_notice'])) {
			$_SESSION['saint_notice'][] = $notice;
		} else {
			$_SESSION['saint_notice'] = array($notice);
		}
		return 1;
	}
	
	public static function removeNotice($notice) {
		$index = array_search($notice,$_SESSION['saint_notice']);
		if ($index !== false) {
			unset($notice,$_SESSION['saint_notice'][$index]);
		}
		return 1;
	}
	
	public static function clearNotices() {
		$_SESSION['saint_notice'] = array();
	}
	
	public static function getNotices() {
		if (!isset($_SESSION['saint_notice']))
			$_SESSION['saint_notice'] = array();
		return $_SESSION['saint_notice'];
	}
	
	public static function getCurrentUsername() {
		if (!isset($_SESSION['username']))
			$_SESSION['username'] = 'guest';
		return $_SESSION['username'];
	}
	
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

	public static function formatPhoneNumber($phone) {
		return preg_replace('/(\d{3})(\d{3})(\d{4})/','$1-$2-$3',$phone);
	}
	
	public static function getAllUsers() {
		return Saint_Model_User::getAllUsers();
	}
	
	public static function getAllPages() {
		return Saint_Model_Page::getAllPages();
	}
	
	public static function getAllCategories() {
		return Saint_Model_Category::getCategories();
	}
	
	public static function getActionLog() {
		if (!isset($_SESSION['actionlog']))
			$_SESSION['actionlog'] = array();
		return $_SESSION['actionlog'];
	}
	
	public static function addLogEntry($newentry) {
		if (!isset($_SESSION['actionlog']))
			$_SESSION['actionlog'] = array();
		array_unshift($_SESSION['actionlog'],$newentry);
		$_SESSION['actionlog'] = array_splice($_SESSION['actionlog'],0,25);
		return $_SESSION['actionlog'];
	}
	
	public static function removeLogEntry($key) {
		if (isset($_SESSION['actionlog'][$key]))
			unset($_SESSION['actionlog'][$key]);
		return $_SESSION['actionlog'];
	}
	
	public static function purgeActionLog() {
		$_SESSION['actionlog'] = array();
		return 1;
	}
	
	public static function getSiteTitle() {
		try {
			return Saint::getOne("SELECT title FROM st_config");
		} catch (Exception $e) {
			Saint::logWarning("Problem getting site title: ".$e->getMessage(),__FILE__,__LINE__);
			return '';
		}
	}

	public static function getSiteDescription() {
		try {
			return Saint::getOne("SELECT meta_description FROM st_config");
		} catch (Exception $e) {
			Saint::logWarning("Problem getting site description: ".$e->getMessage(),__FILE__,__LINE__);
			return '';
		}
	}
	
	public static function getDiscounter() {
		global $_pc;
		return $_pc->getDiscounter();
	}
	
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
	
	public static function getShoppingCart() {
		$cart = new Saint_Model_ShoppingCart();
		$cart->load(Saint::getShoppingCartId());
		return $cart;
	}
	
	public static function getCurrentLanguage() {
		return Saint_Model_Language::getCurrentLanguage();
	}
	
	public static function getDefaultLanguage() {
		return Saint_Model_Language::getDefaultLanguage();
	}
	
	public static function getLayout($name) {
		$layout = new Saint_Model_Layout();
		try {
			$layout->loadByName($name);
			return $layout;
		} catch (Exception $e) {
			return 0;
		}
	}
	
	public static function getLayoutNames() {
		$layouts = array();
		$user = glob(SAINT_SITE_ROOT."/blocks/layouts/*.php");
		$core = glob(SAINT_SITE_ROOT."/core/blocks/layouts/*.php");
		foreach (array_merge($user,$core) as $name) {
			$name = preg_replace('/^.*\/([^\/]*)\.php$/','$1',$name);
			if (!in_array($name,$layouts))
				$layouts[] = $name;
		}
		return $layouts;
	}
	
	public static function addPage($name,$layout,$title = '') {
		return Saint_Model_Page::addPage($name,$layout,$title);
	}
	
	public static function deletePage($id) {
		return Saint_Model_Page::deletePage($id);
	}
	
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

	public static function setCurrentPage($page) {
		global $_pc;
		return $_pc->setCurrentPage($page);
	}
	
	public static function getCurrentPage() {
		global $_pc;
		return $_pc->getCurrentPage();
	}
	
	public static function getSiteRoot() {
		return SAINT_SITE_ROOT;
	}
	
	public static function renderMaintenance() {
		Saint::includeBlock("layouts/maintenance");
	}
	
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

	public static function logWarning($message, $file = null, $line = null) {
		Saint::addLogEntry($message);
		if (SAINT_LOG_LEVEL >= 2) {
			$rootpattern = preg_replace('/\//','\/',SAINT_SITE_ROOT);
			$file = preg_replace("/$rootpattern/",'',$file);
			if (isset($line))
				$message = $line . " " . $message;
			if (isset($file))
				$message = $file . " " . $message;
			$fh = fopen(SAINT_WARN_FILE, 'a') or Saint::logError("Problem opening warning file (Dir: SAINT_LOG_DIR  File: SAINT_WARN_FILE) for writing. Check config.php and ensure the permissions on your log files and directories are correct (they should be 777).",__FILE__,__LINE__);
			fwrite($fh, "\n" . date('Y-m-d H:i:s') . ' ' . $message . "\n");
			fclose($fh);
		}
	}

	public static function logEvent($message, $file = null, $line = null) {
		Saint::addLogEntry($message);
		if (SAINT_LOG_LEVEL >= 3) {
			$rootpattern = preg_replace('/\//','\/',SAINT_SITE_ROOT);
			$file = preg_replace("/$rootpattern/",'',$file);
			if (isset($line))
				$message = $line . " " . $message;
			if (isset($file))
				$message = $file . " " . $message;
			$fh = fopen(SAINT_EVENT_FILE, 'a') or Saint::logError("Problem opening event file (Dir: SAINT_LOG_DIR  File: SAINT_EVENT_FILE) for writing. Check config.php and ensure the permissions on your log files and directories are correct (they should be 777).",__FILE__,__LINE__);
			fwrite($fh, "\n" . date('Y-m-d H:i:s') . ' ' . $message . "\n");
			fclose($fh);
		}
	}

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
	
	public static function getNumRows($query) {
		$result = @mysql_query($query);
		if (!$result)
			throw new Exception(mysql_error(),1);
		return mysql_num_rows($result);
	}

	public static function query($query) {
		$result = @mysql_query($query);
		if (!$result)
			throw new Exception(mysql_error(),1);
		else
			return 1;	
	}
	
	public static function getLastInsertId() {
		return mysql_insert_id();
	}
	
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
	
	public static function convertNameToWeb($name) {
		return Saint_Model_Block::convertNameToWeb($name);
	}
	
	public static function convertNameFromWeb($name) {
		return Saint_Model_Block::convertNameFromWeb($name);
	}
	
	public static function includeSlideshow($arguments = array()) {
		$page = Saint::getCurrentPage();
		$page->sfmarguments = $arguments;
		Saint::includeBlock("gallery/slideshow",false);
	}
	
	public static function includeGallery($arguments = array()) {
		$page = Saint::getCurrentPage();
		$page->sfmarguments = $arguments;
		Saint::includeBlock("gallery/list",false);
	}
	
	public static function getBlockImage($block, $id, $name, $arguments = array()) {
		$name = "b/" . $id . "/" . $block . "/n/" . $name;
		return Saint_Model_ImageLabel::getImage($name, $arguments);
	}
	
	public static function getPageImage($name, $arguments = array()) {
		$name = Saint::getCurrentPage()->getName()."/".$name;
		return Saint_Model_ImageLabel::getImage($name, $arguments);
	}
	
	public static function getImage($name, $arguments = array()) {
		return Saint_Model_ImageLabel::getImage($name, $arguments);
	}
	
	public static function getBlockWysiwyg($block, $id, $name, $default = '') {
		$name = "b/" . $id . "/" . $block . "/n/" . $name;
		return Saint_Model_Wysiwyg::get($name,$default);
	}
	
	public static function getPageWysiwyg($name, $default = '') {
		$name = Saint::getCurrentPage()->getName()."/".$name;
		return Saint_Model_Wysiwyg::get($name,$default);
	}
	
	public static function getWysiwyg($name, $default = '') {
		return Saint_Model_Wysiwyg::get($name,$default);
	}
	
	public static function getPageLabel($name, $default = '', $container = true, $lang = null, $wysiwyg = false) {
		$name .= "/p" . Saint::getCurrentPage()->getId();
		return Saint::getLabel($name,$default,$container,$lang,$wysiwyg);
	}
	
	public static function getBlockLabel($block, $id, $name, $default = '', $container = true, $lang = null, $wysiwyg = false) {
		$name = "block/" . $id . "/" . $block . "/n/" . $name;
		return Saint::getLabel($name,$default,$container,$lang,$wysiwyg);
	}
	
	/**
	 * Called from templates when one wishes to display editable text.
	 * Also master function for shells getBlockLabel and getPageLabel.
	 * @param string $name Name of label
	 * @param string $default Default text for label
	 * @param Saint_Model_Page $page Page label is being called from
	 * @param string $lang Request label in this language
	 */
	public static function getLabel($name, $default = '', $container = true, $lang = null, $wysiwyg = false) {
		$page = Saint::getCurrentPage();
		$label = new Saint_Model_Label();
		if ($label->loadByName($name)) {
			return $label->getLabel($container,$default, $lang);
		} else {
			$label->setLabel($default, $lang);
			$label->save();
			return $label->getLabel($container, $lang);
		}
	}
	
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
	
	public static function includeRepeatingBlock($block, $arguments = null, $container = true, $view = null) {
		return Saint_Model_Block::includeRepeatingBlock($block, $arguments, $container, $view);
	}

	public static function getBlockSetting($blockname,$blockid,$settingname) {
		return Saint_Model_Block::getBlockSetting($blockname,$blockid,$settingname);
	}

	public static function setBlockSetting($blockname,$blockid,$settingname,$newvalue) {
		return Saint_Model_Block::setBlockSetting($blockname,$blockid,$settingname,$newvalue);
	}
	
	public static function getBlockUrl($blockname,$blockid,$page = null) {
		return Saint_Model_Block::getBlockUrl($blockname,$blockid, $page);
	}
	
	public static function getBlogRssUrl() {
		return SAINT_BASE_URL . 'feed';
	}

	public static function includeBlock($block, $container = true, $view = null) {
		return Saint_Model_Block::includeBlock($block,$container, $view);
	}
	
	public static function includeStyle($style) {
		if ($style = Saint::sanitize($style,SAINT_REG_NAME)) {
			if (file_exists(SAINT_SITE_ROOT .  "/styles/".$style.".css"))
				echo '<link rel="stylesheet" type="text/css" href="/styles/'.$style.'.css" />';
			elseif (file_exists(SAINT_SITE_ROOT .  "/core/styles/".$style.".css"))
				echo '<link rel="stylesheet" type="text/css" href="/core/styles/'.$style.'.css" />';
			else
				Saint::logWarning("Cannot find style $style.");
		} else
			return 0;
	}

	public static function includeScript($script) {
		if ($script = Saint::sanitize($script,SAINT_REG_NAME)) {
			if (file_exists(SAINT_SITE_ROOT .  "/scripts/".$script.".js"))
				echo '<script type="text/javascript" src="/scripts/'.$script.'.js"></script>';
			elseif (file_exists(SAINT_SITE_ROOT .  "/core/scripts/".$script.".js"))
				echo '<script type="text/javascript" src="/core/scripts/'.$script.'.js"></script>';
			else
				Saint::logWarning("Cannot find script $script.");
		} else
			return 0;
	}
	
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
	
	public static function search($phrase) {
		$phrase = Saint::sanitize($phrase);
		$phrase = "%$phrase%";
		$results = array();
		try {
			$labels = Saint::getAll("SELECT `id`,`name` FROM `st_labels` WHERE `label` LIKE '$phrase'");
			print_r($labels);
			foreach ($labels as $label) {
				try {
					$maxid = Saint::getOne("SELECT MAX(`id`) FROM `st_labels` WHERE `name`='$label[1]'");
					if ($label[0] == $maxid) {
						// Isolate block name from label name 
						if (preg_match('/^(.*)\/n\/.*$/',$label[1],$matches))
							$bname = $matches[1];
						else
							$bname = '';
						try {
							$resultpages = Saint::getAll("SELECT `pageid`,`url` FROM `st_pageblocks` WHERE `block`='$bname'");
							foreach ($resultpages as $rp) {
								if (!isset($results[$rp[0]]))
									$results[$rp[0]] = array($rp[1],array($label[1]));
								else
									$results[$rp[0]][1][] = $label[1];
							}
						} catch (Exception $t) {
							Saint::logWarning("Problem selecting block URLs: ".$t->getMessage(),__FILE__,__LINE__);
						}
					}
				} catch (Exception $f) {
					Saint::logError("Problem getting max id for label '$label[1]': ".$f->getMessage());
				}
			}
			return $results;
		} catch (Exception $e) {
			return array();
		}
	}
}
