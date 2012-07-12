<?php
/**
 * Model for CMS-manageable files within the Saint framework.
 * @author Preston St. Pierre
 * @package Saint
 */
class Saint_Model_File {
	/**
	 * Check if file with given ID exists in database.
	 * @param int $fileid ID to check for.
	 * @return int ID of file on success, 0 on failure.
	 */
	public static function idExists($fileid) {
		$sid = Saint::sanitize($fileid,SAINT_REG_ID);
		if ($sid) {
			try {
				return Saint::getOne("SELECT `id` FROM `st_files` WHERE `id`='$sid'");
			} catch (Exception $e) {
				Saint::logError($e->getMessage(),__FILE__,__LINE__);
				return 0;
			}
		}
	}
	
	/**
	 * Check if a location in the filesystem is empty.
	 * @param string $location Path of file to check.
	 * @return boolean True if no file found, false otherwise.
	 */
	public static function locationIsAvailable($location) {
		if ($sloc = Saint::sanitize($location)) {
			if (file_exists($location))
				return 0;
			else
				return 1;
		} else {
			Saint::logError("Invalid location: '$location'. ",__FILE__,__LINE__);
			return 0;
		}
	}
	
	/**
	 * Remove file with given ID from the database.
	 * @param int $id ID of file to remove.
	 * @return boolean True on success, false otherwise.
	 */
	public static function removeFromDb($id) {
		if ($sid = Saint::sanitize($id,SAINT_REG_ID)) {
			try {
					Saint::query("DELETE FROM `st_files` WHERE `id`='$sid'");
					return 1;
				} catch (Exception $e) {
					Saint::logError("Problem removing file id '$sid' ".$e->getMessage(),__FILE__,__LINE__);
					return 1;
				}
		} else {
			Saint::logError("Invalid id: '$id'. ",__FILE__,__LINE__);
			return 0;
		}
	}
	
	/**
	 * Get location of file with given ID.
	 * @param int $id ID of file requested.
	 * @return string Path of file requested.
	 */
	public static function getFileLocation($id) {
		$sid = Saint::sanitize($id,SAINT_REG_ID);
		try {
			return Saint::getOne("SELECT `location` FROM `st_files` WHERE `id`='$sid'");
		} catch (Exception $e) {
			Saint::logError("Unable to select file with id '$sid'",__FILE__,__LINE__);
			return '/core/images/image.png';
		}
	}
	
	protected $_id;
	protected $_location;
	protected $_title;
	protected $_name;
	protected $_type;
	protected $_keywords;
	protected $_description;
	protected $_extension;
	protected $_width;
	protected $_height;
	protected $_size;
	protected $_url;
	protected $_categories;
	
	/**
	 * Attempt to load model with information from the database matching the given file ID; otherwise load blank data.
	 * @param int $id ID of file to load.
	 */
	public function __construct($id = null) {
		$this->_categories = null;
		if ($id == null || !$this->load($id)) {
			$this->_id = 0;
			$this->_location = '';
			$this->_title = '';
			$this->_name = null;
			$this->_keywords = '';
			$this->_description = '';
			$this->_extension = '';
			$this->_type = '';
			$this->_width = 0;
			$this->_height = 0;
			$this->_size = 0;
		}
	}
	
	/**
	 * Load model with information from the database matching the given file ID.
	 * @param int $id ID of file to load.
	 * @return boolean True for success, false otherwise.
	 */
	public function load($id) {
		if ($sid = Saint::sanitize($id,SAINT_REG_ID)) {
			try {
				$info = Saint::getRow("SELECT `location`,`title`,`keywords`,`description`,`extension`,`type`,`width`,`height`,`size`".
				 " FROM `st_files` WHERE `id`='$id'");
				$this->_id = $sid;
				$this->_location = SAINT_SITE_ROOT . $info[0];
				$this->_url = SAINT_URL . $info[0];
				$this->_title = $info[1];
				$this->_keywords = $info[2];
				$this->_description = $info[3];
				$this->_extension = $info[4];
				$this->_type = $info[5];
				$this->_width = $info[6];
				$this->_height = $info[7];
				$this->_size = $info[8];
				$this->_categories = null;
				return 1;
			} catch (Exception $e) {
				Saint::logError("Unable to load file with id '$sid': ".$e->getMessage(),__FILE__,__LINE__);
				return 0;
			}
		} else {
			Saint::logError("Invalid id '$id'.",__FILE__,__LINE__);
			return 0;
		}
	}
	
	/**
	 * Load model with information from the database matching the given file location.
	 * @param string $location Location of file to load.
	 * @return boolean True for success, false otherwise.
	 */
	public function loadByLocation($location) {
		$slocation = Saint::sanitize($location);
		if ($slocation) {
			try {
				$fileid = Saint::getOne("SELECT `id` FROM `st_files` WHERE `location`='$slocation'");
				if ($this->load($fileid))
					return 1;
				else
					return 0;
			} catch (Exception $e) {
				Saint::logError("Couldn't select file id for location '$slocation': ".$e->getMessage(),__FILE__,__LINE__);
				return 0;
			}
		} else {
			Saint::logError("Invalid location '$location'.",__FILE__,__LINE__);
			return 0;
		}
	}
	
	/**
	 * Get current file ID.
	 * @return int ID of loaded model.
	 */
	public function getId() {
		return $this->_id;
	}

	/**
	 * Get name of loaded file.
	 * @return string Name of loaded file.
	 */
	public function getFileName() {
		return preg_replace('/^.*\/([^\/])$/','$1',$this->_location);
	}
	
	/**
	 * Get current file location.
	 * @return string Location of loaded file.
	 */
	public function getLocation() {
		return $this->_location;
	}
	
	/**
	 * Get current file URL.
	 * @return string URL to access loaded file.
	 */
	public function getUrl() {
		return $this->_url;
	}
	
	/**
	 * Get current file name.
	 * @return string Name of loaded file.
	 */
	public function getName() {
		return $this->_name;
	}
	
	/**
	 * Get current file title.
	 * @return string Title of loaded file.
	 */
	public function getTitle() {
		return $this->_title;
	}
	
	/**
	 * Get current file keywords.
	 * @return string Keywords associated with loaded file.
	 */
	public function getKeywords() {
		return $this->_keywords;
	}
	
	/**
	 * Get current file description.
	 * @return string Description of loaded file.
	 */
	public function getDescription() {
		return $this->_description;
	}
	
	/**
	 * Get current file extension.
	 * @return string Extension of loaded file.
	 */
	public function getExtension() {
		return $this->_extension;
	}
	
	/**
	 * Get current file size.
	 * @return int Size of loaded file in bytes.
	 */
	public function getSize() {
		return $this->_size;
	}
	
	/**
	 * Get current file width; 0 for non-image files.
	 * @return int Width of loaded file in pixels.
	 */
	public function getWidth() {
		return $this->_width;
	}
	
	/**
	 * Get current file height; 0 for non-image files.
	 * @return int Height of loaded file in pixels.
	 */
	public function getHeight() {
		return $this->_height;
	}
	
	/**
	 * Get URL of icon associated with current file type.
	 * @return string URL of file icon.
	 */
	public function getIconUrl() {
		switch ($this->_type) {
			case "audio":
				return Saint_Model_FileManager::getAudioIconUrl();
				break;
			case "compressed":
				return Saint_Model_FileManager::getCompressedIconUrl();
				break;
			case "database":
				return Saint_Model_FileManager::getDatabaseIconUrl();
				break;
			case "disk":
				return Saint_Model_FileManager::getDiskIconUrl();
				break;
			case "document":
				return Saint_Model_FileManager::getDocumentIconUrl();
				break;
			case "executable":
				return Saint_Model_FileManager::getExecutableIconUrl();
				break;
			case "font":
				return Saint_Model_FileManager::getFontIconUrl();
				break;
			case "image":
				return Saint_Model_FileManager::getImageIconUrl();
				break;
			case "print":
				return Saint_Model_FileManager::getPrintIconUrl();
				break;
			case "source":
				return Saint_Model_FileManager::getSourceIconUrl();
				break;
			case "spreadsheet":
				return Saint_Model_FileManager::getSpreadsheetIconUrl();
				break;
			case "system":
				return Saint_Model_FileManager::getSystemIconUrl();
				break;
			case "video":
				return Saint_Model_FileManager::getVideoIconUrl();
				break;
			case "web":
				return Saint_Model_FileManager::getWebIconUrl();
				break;
		}
	}
	
	/**
	 * Set URL for loaded file.
	 * @param string $url New URL to associate with loaded file.
	 */
	public function setUrl($url) {
		$this->_url = $url;
	}

	/**
	 * Set new file name. 
	 * @param string $name New name to use.
	 * @return boolean True for success, false otherwise.
	 */
	public function setName($name) {
		$sname = Saint::sanitize($name,SAINT_REG_NAME);
		if ($sname) {
			$this->_name = $sname;
			return 1;
		} else {
			Saint::logError("Invalid name '$name'. See manual for further information.",__FILE__,__LINE__);
			return 0;
		}
	}
	
	/**
	 * Set new file title.
	 * @param string $title New title to use.
	 * @return boolean True for success, false otherwise.
	 */
	public function setTitle($title) {
		$stitle = Saint::sanitize($title);
		if ($stitle) {
			$this->_title = $stitle;
			return 1;
		} else {
			return 0;
		}
	}
	
	/**
	 * Set new file keywords.
	 * @param string $keywords New keywords to use.
	 * @return boolean True for success, false otherwise.
	 */
	public function setKeywords($keywords) {
		$skeywords = Saint::sanitize($keywords);
		if ($skeywords) {
			$this->_keywords = $skeywords;
			return 1;
		} else {
			return 0;
		}
	}
	
	/**
	 * Set new file description.
	 * @param string $description New description to use.
	 * @return boolean True for success, false otherwise.
	 */
	public function setDescription($description) {
		$sdescription = Saint::sanitize($description);
		if ($sdescription) {
			$this->_description = $sdescription;
			return 1;
		} else {
			return 0;
		}
	}
	
	/**
	 * Get categories for loaded file.
	 */
	public function getCategories() {
		if ($this->_categories == null) {
			$this->_categories = array();
			try {
				$cats = Saint::getAll("SELECT `c`.`id`,`c`.`name` FROM `st_categories` as `c`,`st_file_categories` as `fc` WHERE `fc`.`fileid`='$this->_id' AND `fc`.`catid`=`c`.`id`");
				foreach ($cats as $cat)
					$this->_categories[$cat[0]] = $cat[1];
			} catch (Exception $e) {
				if ($e->getCode()) {
					Saint::logError("Unable to load categories for file '$this->_name':".$e->getMessage(),__FILE__,__LINE__);
				}
			}
		}
		return $this->_categories;
	}
	

	/**
	 * Add the loaded file to the given category.
	 * @param string $category Category into which to add the file.
	 * @return boolean True on success, false otherwise.
	 */
	public function addToCategory($category) {
		$scategory = Saint::sanitize($category);
		if ($scategory) {
			$id = Saint_Model_Category::getId($scategory);
			if (!$id) {
				$id = Saint_Model_Category::addCategory($scategory);
			}
			if (!$id) {
				Saint::logError("Problem assigning file to category '$scategory'. Unable to get category ID.",__FILE__,__LINE__);
				return 0;
			} else {
				try {
					Saint::query("INSERT INTO `st_file_categories` (`catid`,`fileid`) VALUES ('$id','$this->_id')");
					return 1;
				} catch (Exception $e) {
					# We hit this regularly if the file is already in the specified category, so ignore it
					# Saint::logError("Problem adding file id '$this->_id' to category id '$id': ".$e->getMessage(),__FILE__,__LINE__);
					return 0;
				}
			}
		} else {
			Saint::logError("Invalid category name: '$category'.",__FILE__,__LINE__);
			return 0;
		}
	}
	
	/**
	 * Remove the loaded file to the given category.
	 * @param string $category Category from which to remove the file.
	 * @return boolean True on success, false otherwise.
	 */
	public function removeFromCategory($category) {
		Saint::logError("Category: ".$category);
		$scategory = Saint::sanitize($category);
		if ($scategory) {
			$id = Saint_Model_Category::getId($scategory);
			Saint::logError("DELETE FROM `st_file_categories` WHERE `catid`='$id' AND `fileid`='$this->_id'");
			if (!$id) {
				# No id... it can't be part of a category that doesn't exist, so our job is done.
				return 1;
			} else {
				try {
					Saint::query("DELETE FROM `st_file_categories` WHERE `catid`='$id' AND `fileid`='$this->_id'");
					return 1;
				} catch (Exception $e) {
					Saint::logError("Problem removing file id '$this->_id' from category id '$id': ".$e->getMessage(),__FILE__,__LINE__);
					return 0;
				}
			}
		} else {
			Saint::logError("Invalid category name: '$category'.",__FILE__,__LINE__);
			return 0;
		}
	}
	
	/**
	 * Change categories for loaded model en masse.
	 * @param string[] $newcats New categories for loaded file.
	 */
	public function setCategories($newcats) {
		if (!is_array($newcats))
			$newcats = explode(',',$newcats);
		
		foreach ($this->getCategories() as $cat) {
			Saint::logError("Testing: ".$cat);
			if (!in_array($cat,$newcats)) {
				$this->removeFromCategory($cat);
			}
		}
		foreach ($newcats as $newcat) {
			$this->addToCategory($newcat);
		}
	}
	
	/**
	 * Save loaded model information to database.
	 * @return boolean True on success, false otherwise.
	 */
	public function save($log = true) {
		if ($this->_id) {
			$query = "UPDATE `st_files` SET ".
			"`title`='$this->_title',".
			"`keywords`='$this->_keywords',".
			"`description`='$this->_description'".
			" WHERE `id`='$this->_id'";
		} else {
			$query = "INSERT INTO `st_files` (`title`,`keywords`,`description`) VALUES ".
			"('$this->_title','$this->_keywords','$this->_description')";
		}
		try {
			Saint::query($query);
			if ($log)
				Saint::logEvent("Saved details of file id '$this->_id' to database.",__FILE__,__LINE__);
			return 1;
		} catch (Exception $e) {
			Saint::logError("Problem saving file: ".$e->getMessage(),__FILE__,__LINE__);
			return 0;
		}
	}
	
	/**
	 * Display loaded file on the page using file-manager/file block template.
	 */
	public function display() {
		$page = Saint::getCurrentPage();
		$page->setFiles(array($this));
		Saint::includeBlock("file-manager/file",false);
	}
}
