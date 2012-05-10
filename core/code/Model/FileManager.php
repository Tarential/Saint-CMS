<?php
/**
 * Model for a file meta data editor in the Saint framework.
 * @author Preston St. Pierre
 * @package Saint
 * @todo Merge functionality with Saint_Model_File where applicable.
 */
class Saint_Model_FileManager {
	/**
	 * Get all files matching given arguments.
	 * @param string[] $arguments Arguments to match when selecting files.
	 * @return array[] Files matching given arguments.
	 */
	public static function getAllFiles($arguments = array()) {
		$where = '';
		if (SAINT_USE_CORE_MEDIA)
			$corefiles = true;
		else
			$corefiles = false;
		if (isset($arguments['includecoremedia']) && $arguments['includecoremedia'] != '')
			$corefiles = $arguments['includecoremedia'];
		if (isset($arguments['id']) && $arguments['id'] != 0)
			$where .= " AND `id` LIKE '%".Saint::sanitize($arguments['id'],SAINT_REG_ID)."%'";
		if (isset($arguments['location']) && $arguments['location'] != '')
			$where .= " AND `location` LIKE '%".Saint::sanitize($arguments['location'])."%'";
		if (isset($arguments['title']) && $arguments['title'] != '')
			$where .= " AND `title` LIKE '%".Saint::sanitize($arguments['title'])."%'";
		if (isset($arguments['description']) && $arguments['description'] != '')
			$where .= " AND `description` LIKE '%".Saint::sanitize($arguments['description'])."%'";
		if (isset($arguments['keywords']) && $arguments['keywords'] != '') {
			if (!is_array($arguments['keywords']))
				$arguments['keywords'] = array($arguments['keywords']);
			foreach ($arguments['keywords'] as $keyword)
				$where .= " AND `keywords` LIKE '%".Saint::sanitize($keyword)."%'";
		}
		if (!$corefiles) {
			$where .= " AND `user`='1'";
		}
		if (isset($arguments['categories']) && $arguments['categories'] != '') {
			if (!is_array($arguments['categories']))
				$arguments['categories'] = array($arguments['categories']);
		} else {
			$arguments['categories'] = 0;
		}
		try {
			$allfiles = Saint::getAll("SELECT `id`,`location`,`title`,`description`,`keywords` FROM `st_files` WHERE `enabled`='1'$where");
			$indexfiles = array();
			for ($i = 0; $i < sizeof($allfiles); $i++) {
				// Enter queried data into array
				$indexfiles[$i]['id'] = $allfiles[$i][0];
				$indexfiles[$i]['location'] = $allfiles[$i][1];
				$indexfiles[$i]['title'] = $allfiles[$i][2];
				$indexfiles[$i]['description'] = $allfiles[$i][3];
				$indexfiles[$i]['keywords'] = $allfiles[$i][4];
				
				// Get the categories for each file
				$filemodel = new Saint_Model_FileManager();
				if ($filemodel->load($indexfiles[$i]['id'])) {
					$indexfiles[$i]['categories'] = $filemodel->getCategories();
				} else {
					$indexfiles[$i]['categories'] = array();
				}
				
				// Filter by any categories we were given
				if ($arguments['categories']) {
					$validfile = false;
					foreach ($indexfiles[$i]['categories'] as $cat) {
						if (in_array($cat,$arguments['categories'])) {
							$validfile = true;
							break;
						}
					}
					// Remove files that aren't in matching categories
					if (!$validfile) {
						unset($indexfiles[$i]);
						$indexfiles = array_values($indexfiles);
					}
				}
			}
			return $indexfiles;
		} catch (Exception $e) {
			Saint::logError("Problem selecting files from database: ".$e->getMessage(),__FILE__,__LINE__);
			return array();
		}
	}
	
	/**
	 * Scan media directories for files and insert them into the database.
	 * @return boolean True on success, false otherwise.
	 */
	public static function processFiles() {
		global $saint_filetypes;
		$return = 1;
		$saintdir = SAINT_SITE_ROOT . "/core/images";
		$userdir = SAINT_SITE_ROOT . "/images";
		$uploaddir = SAINT_SITE_ROOT . "/media";
		$userfiles = array_merge(
			Saint_Model_Block::recursiveScan($userdir),
			Saint_Model_Block::recursiveScan($uploaddir)
		);
		
		$corefiles = Saint_Model_Block::recursiveScan($saintdir);
		
		# Note: Keys of array match boolean field, do not change
		$allfiles = array(
			0 => $corefiles,
			1 => $userfiles,
		);
		
		$len = strlen(SAINT_SITE_ROOT);
		foreach ($allfiles as $ftype=>$farray) {
			foreach ($farray as $key=>$val) {
				$allfiles[$key] = substr($val,$len);
				try {
					Saint::getOne("SELECT `id` FROM `st_files` WHERE `location`='$allfiles[$key]'");
				} catch (Exception $c) {
					try {
						$title = preg_replace('/^.*\/([^\/\.]*)\.{0,1}\w{0,4}$/','$1',$allfiles[$key]);
						$title = preg_replace('/[-_\.]+/',' ',$title);
						$extension = strtolower(preg_replace('/^.*\.([^\.]+)$/','$1',$allfiles[$key]));
						$type = 'misc';
						foreach ($saint_filetypes as $filetype=>$fileextensions) {
							if (in_array($extension,$fileextensions)) {
								$type = $filetype;
							}
						}
						$words = explode(' ',$title);
						$title = '';
						$keywords = '';
						foreach ($words as $word) {
							$title .= ucfirst($word) . " ";
							$keywords .= $word . ",";
						}
						$title = chop($title," ");
						$keywords = chop($keywords,",");
						$filesize = filesize(SAINT_SITE_ROOT.$allfiles[$key]);
						if ($type == "image") {
							list($width,$height) = getimagesize(SAINT_SITE_ROOT.$allfiles[$key]);
						} else {
							$height = 0;
							$width = 0;
						}
						Saint::query("INSERT INTO `st_files` (`location`,`title`,`keywords`,`extension`,`type`,`user`,`width`,`height`,`size`) ".
							"VALUES ('$allfiles[$key]','$title','$keywords','$extension','$type','$ftype','$width','$height','$filesize')");
					} catch (Exception $d) {
						Saint::logError("Error inserting file info into database: ",$d->getMessage(),__FILE__,__LINE__);
						$return = 0;
					}
				}
			}
		}
		
		foreach (Saint_Model_FileManager::getAllFiles() as $file) {
			if (!file_exists(SAINT_SITE_ROOT . $file['location'])) {
				Saint_Model_FileManager::removeFromDb($file['id']);
			}
		}
		
		return $return;
	}
	
	/**
	 * Remove file entry from database.
	 * @param int $fileid ID of file to remove.
	 * @return boolean True on success, false otherwise.
	 */
	public static function removeFromDb($fileid) {
		$sid = Saint::sanitize($fileid,SAINT_REG_ID);
		if ($sid) {
			try {
				Saint::query("DELETE FROM `st_files` WHERE `id`='$sid'");
				return 1;
			} catch (Exception $e) {
				Saint::logError("Unable to remove file with id '$sid': ".$e->getMessage(),__FILE__,__LINE__);
				return 0;
			}
		} else {
			Saint::logError("Invalid id: '$fileid'.",__FILE__,__LINE__);
			return 0;
		}
	}
	
	/**
	 * @todo Update icon functions to use dynamic files.
	 */
	
	/**
	 * Get URL of audio icon.
	 * @return string URL of icon to represent audio files.
	 */
	public static function getAudioIconUrl() {
		return "/core/images/audio.png";
	}
	
	/**
	 * Get URL of compressed icon.
	 * @return string URL of icon to represent compressed files.
	 */
	public static function getCompressedIconUrl() {
		return "/core/images/compressed.png";
	}
	
	/**
	 * Get URL of database icon.
	 * @return string URL of icon to represent database files.
	 */
	public static function getDatabaseIconUrl() {
		return "/core/images/database.png";
	}
	
	/**
	 * Get URL of disk icon.
	 * @return string URL of icon to represent disk files.
	 */
	public static function getDiskIconUrl() {
		return "/core/images/disk.png";
	}
	
	/**
	 * Get URL of document icon.
	 * @return string URL of icon to represent document files.
	 */
	public static function getDocumentIconUrl() {
		return "/core/images/document.png";
	}
	
	/**
	 * Get URL of executable icon.
	 * @return string URL of icon to represent executable files.
	 */
	public static function getExecutableIconUrl() {
		return "/core/images/executable.png";
	}
	
	/**
	 * Get URL of font icon.
	 * @return string URL of icon to represent font files.
	 */
	public static function getFontIconUrl() {
		return "/core/images/font.png";
	}
	
	/**
	 * Get URL of image icon.
	 * @return string URL of icon to represent image files.
	 */
	public static function getImageIconUrl() {
		return "/core/images/image.png";
	}
	
	/**
	 * Get URL of print icon.
	 * @return string URL of icon to represent a printer.
	 */
	public static function getPrintIconUrl() {
		return "/core/images/print.png";
	}
	
	/**
	 * Get URL of source icon.
	 * @return string URL of icon to represent source files.
	 */
	public static function getSourceIconUrl() {
		return "/core/images/source.png";
	}
	
	/**
	 * Get URL of spreadsheet icon.
	 * @return string URL of icon to represent spreadsheet files.
	 */
	public static function getSpreadsheetIconUrl() {
		return "/core/images/spreadsheet.png";
	}
	
	/**
	 * Get URL of system icon.
	 * @return string URL of icon to represent system files.
	 */
	public static function getSystemIconUrl() {
		return "/core/images/system.png";
	}
	
	/**
	 * Get URL of video icon.
	 * @return string URL of icon to represent video files.
	 */
	public static function getVideoIconUrl() {
		return "/core/images/video.png";
	}
	
	/**
	 * Get URL of web icon.
	 * @return string URL of icon to represent web files.
	 */
	public static function getWebIconUrl() {
		return "/core/images/web.png";
	}
	
	protected $_id;
	protected $_title;
	protected $_keywords;
	protected $_description;
	protected $_categories;
	protected $_location;
	
	/**
	 * Instantiate the model with blank data.
	 */
	public function __construct() {
		$this->_id = 0;
		$this->_title = '';
		$this->_keywords = '';
		$this->_description = '';
		$this->_categories = array();
	}
	
	/**
	 * Load file information from the database.
	 * @param int $id ID of file whose meta data to retrieve.
	 * @return boolean True on success, false on failure.
	 */
	public function load($id) {
		if ($id = Saint::sanitize($id,SAINT_REG_ID)) {
			try {
				$info = Saint::getRow("SELECT `title`,`keywords`,`description`,`location` FROM `st_files` WHERE id='$id'");
				$this->_id = $id;
				$this->_title=$info[0];
				$this->_keywords=$info[1];
				$this->_description=$info[2];
				$this->_location=$info[3];
				return 1;
			} catch (Exception $e) {
				Saint::logError("Cannot load File model from ID $id. Error: " . $e->getMessage(),__FILE__,__LINE__);
				return 0;
			}
		} else
			return 0;
	}

	/**
	 * Get ID of loaded model.
	 * @return int ID of loaded model.
	 */
	public function getId() {
		return $this->_id;
	}

	/**
	 * Get title of loaded file.
	 * @return string Title of loaded file.
	 */
	public function getTitle() {
		return $this->_title;
	}

	/**
	 * Get keywords of loaded file.
	 * @return string Keywords of loaded file.
	 */
	public function getKeywords() {
		return $this->_keywords;
	}

	/**
	 * Get description of loaded file.
	 * @return string Description of loaded file.
	 */
	public function getDescription() {
		return $this->_description;
	}
	
	/**
	 * Get location of loaded file.
	 * @return string Location of loaded file.
	 */
	public function getLocation() {
		return $this->_location;
	}
	
	/**
	 * Get name of loaded file.
	 * @return string Name of loaded file.
	 */
	public function getFileName() {
		return preg_replace('/^.*\/([^\/])$/','$1',$this->_location);
	}
	
	/**
	 * Get extension of loaded file.
	 * @return string Extension of loaded file.
	 */
	public function getFileExtension() {
		return $this->_extension;
	}
	
	/**
	 * Get type of loaded file.
	 * @return string Type of loaded file.
	 */
	public function getFileType() {
		return $this->_filetype;
	}
	
	/**
	 * Set the title for the loaded file.
	 * @param string $title New title for loaded file.
	 * @return boolean True on success, false otherwise.
	 */
	public function setTitle($title) {
		if ($title = Saint::sanitize($title)) {
			$this->_title = $title;
			return 1;
		} else
			return 0;
	}
	
	/**
	 * Set the keywords for the loaded file.
	 * @param string $keywords New keywords for loaded file.
	 * @return boolean True on success, false otherwise.
	 */
	public function setKeywords($keywords) {
		if ($keywords = Saint::sanitize($keywords)) {
			$this->_keywords = $keywords;
			return 1;
		} else
			return 0;
	}
	
	/**
	 * Set the description for the loaded file.
	 * @param string $description New description for loaded file.
	 * @return boolean True on success, false otherwise.
	 */
	public function setDescription($description) {
		if ($description = Saint::sanitize($description)) {
			$this->_description = $description;
			return 1;
		} else
			return 0;
	}
	
	/**
	 * Add the loaded file to the given category.
	 * @param string $category Category into which to add the file.
	 * @return boolean True on success, false otherwise.
	 */
	public function addToCategory($category) {
		$scategory = Saint::sanitize($category,SAINT_REG_NAME);
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
					Saint::query("INSERT INTO `st_filecats` (`catid`,`fileid`) VALUES ('$id','$this->_id')");
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
		$scategory = Saint::sanitize($category,SAINT_REG_NAME);
		if ($scategory) {
			$id = Saint_Model_Category::getId($scategory);
			if (!$id) {
				# No id... it can't be part of a category that doesn't exist, so our job is done.
				return 1;
			} else {
				try {
					Saint::query("DELETE FROM `st_filecats` WHERE `catid`='$id' AND `fileid`='$this->_id'");
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
			if (!in_array($cat,$newcats)) {
				$this->removeFromCategory($cat);
			}
		}
		foreach ($newcats as $newcat) {
			$this->addToCategory($newcat);
		}
	}
	
	/**
	 * Get categories for loaded file.
	 * @return string[] Category IDs (keys) and names (values) for loaded file.
	 */
	public function getCategories() {
		try {
			$getcats = Saint::getAll("SELECT `c`.`id`,`c`.`name` FROM `st_categories` as `c`,`st_filecats` as `p` WHERE `p`.`fileid`='$this->_id' AND `p`.`catid`=`c`.`id`");
			$cats = array();
			foreach ($getcats as $getcat) {
				$cats[$getcat[0]] = $getcat[1];
			}
			return $cats;
		} catch (Exception $e) {
			# No categories, return blank array
			return array();
		}
	}
	
	/**
	 * Save loaded model information to database.
	 * @return boolean True on success, false otherwise.
	 */
	public function save() {
		if ($this->_id) {
			$query = "UPDATE st_files SET ".
			"title='$this->_title',".
			"keywords='$this->_keywords',".
			"description='$this->_description'".
			" WHERE id='$this->_id'";
		} else {
			$query = "INSERT INTO `st_users` (`title`,`keywords`,`description`) VALUES ".
			"('$this->_title','$this->_keywords','$this->_description')";
		}
		try {
			Saint::query($query);
			return 1;
		} catch (Exception $e) {
			Saint::logError("Problem saving file: ".$e->getMessage(),__FILE__,__LINE__);
			return 0;
		}
	}
}