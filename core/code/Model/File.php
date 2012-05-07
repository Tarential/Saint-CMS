<?php
class Saint_Model_File {
	public static function nameIsAvailable($name) {
		if ($sname = Saint::sanitize($name,SAINT_REG_NAME)) {
			try {
					$id = Saint::getOne("SELECT `id` FROM `st_filelabels` WHERE `name`='$sname'");
					return 0;
				} catch (Exception $e) {
					return 1;
				}
		} else {
			Saint::logError("Invalid file name: '$name'. ",__FILE__,__LINE__);
			return 0;
		}
	}
	
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
	
	public static function setFile($name,$id) {
		$sname = Saint::sanitize($name,SAINT_REG_NAME);
		try {
			$id = Saint::getOne("SELECT `fileid` FROM `st_filelabels` WHERE `name`='$sname'");
		} catch (Exception $e) {
			# No file label yet assigned, that's ok we'll create one with the default
			if (isset($arguments['default']) && file_exists(Saint::getSiteRoot() . $arguments['default'])) {
				$imgfile = $arguments['default'];
			}
			Saint_Model_FileLabel::createLabel($sname,$imgfile);
		}
	}
	
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
	
	public function __construct($id = null) {
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
	
	public function load($id) {
		if ($sid = Saint::sanitize($id,SAINT_REG_ID)) {
			try {
				$info = Saint::getRow("SELECT `location`,`title`,`keywords`,`description`,`extension`,`type`,`width`,`height`,`size`".
				 " FROM `st_files` WHERE `id`='$id'");
				$this->_id = $sid;
				$this->_location = Saint::getSiteRoot() . $info[0];
				$this->_url = $info[0];
				$this->_title = $info[1];
				$this->_keywords = $info[2];
				$this->_description = $info[3];
				$this->_extension = $info[4];
				$this->_type = $info[5];
				$this->_width = $info[6];
				$this->_height = $info[7];
				$this->_size = $info[8];
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
	
	public function getId() {
		return $this->_id;
	}
	
	public function getLocation() {
		return $this->_location;
	}
	
	public function getUrl() {
		return $this->_url;
	}
	
	public function getName() {
		return $this->_name;
	}
	
	public function getTitle() {
		return $this->_title;
	}
	
	public function getKeywords() {
		return $this->_keywords;
	}
	
	public function getDescription() {
		return $this->_description;
	}
	
	public function getExtension() {
		return $this->_extension;
	}
	
	public function getSize() {
		return $this->_size;
	}
	
	public function getWidth() {
		return $this->_width;
	}
	
	public function getHeight() {
		return $this->_height;
	}
	
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
	
	public function setUrl($url) {
		$this->_url = $url;
	}

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
	
	public function setTitle($title) {
		$stitle = Saint::sanitize($title);
		if ($stitle) {
			$this->_title = $stitle;
			return 1;
		} else {
			return 0;
		}
	}
	
	public function setKeywords($keywords) {
		$skeywords = Saint::sanitize($keywords);
		if ($skeywords) {
			$this->_keywords = $skeywords;
			return 1;
		} else {
			return 0;
		}
	}
	
	public function setDescription($description) {
		$sdescription = Saint::sanitize($description);
		if ($sdescription) {
			$this->_description = $sdescription;
			return 1;
		} else {
			return 0;
		}
	}
	
	public function save($log = true) {
		if ($this->_id) {
			try {
				$query = "UPDATE st_pages SET ".
				"title='$this->_title',".
				"keywords='$this->_keywords',".
				"description='$this->_description',".
				"WHERE id=$this->_id";
				Saint::query($query);
				if ($log)
					Saint::logEvent("Saved details for file id '$this->_id'.");
				return 1;
			} catch (Exception $e) {
				Saint::logError("Couldn't save file details for id '$this->_id': ".$e->getMessage(),__FILE__,__LINE__);
				return 0;
			}
		} else {
			return 0;
		}
	}
	
	public function display() {
		$page = Saint::getCurrentPage();
		$page->curfile = $this;
		Saint::includeBlock("file-manager/file",false);
	}
}
