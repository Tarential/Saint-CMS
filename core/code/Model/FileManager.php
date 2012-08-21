<?php
/**
 * Model for a file meta data editor in the Saint framework.
 * @author Preston St. Pierre
 * @package Saint
 * @todo Merge functionality with Saint_Model_File where applicable.
 */
class Saint_Model_FileManager extends Saint_Model_Page {
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

		$catsel = "";
		$catwhere = "";
		if (isset($arguments['categories']) && $arguments['categories'] != '') {
			if (!is_array($arguments['categories'])) {
				$arguments['categories'] = array($arguments['categories']);
			}
			if (sizeof($arguments['categories'])) {
				$catsel = ", `st_categories` as `c`, `st_file_categories` as `fc`";
				$catwhere = " AND `c`.`id`=`fc`.`catid` AND `f`.`id`=`fc`.`fileid` AND `c`.`name` IN (";
				foreach ($arguments['categories'] as $cat) {
					$catwhere .= "'$cat',";
				}
				$catwhere = rtrim($catwhere,',') . ")";
			}
		} else {
			$arguments['categories'] = 0;
		}
		
		$limit = '';
		if (isset($arguments['results-per-page']) && (!isset($arguments['num-results-only']) || $arguments['num-results-only'] == false)) {
			$limit .= ' LIMIT '.$arguments['results-per-page'];
			if (isset($arguments['page-number'])) {
				$limit .= ' OFFSET '.($arguments['page-number'] * $arguments['results-per-page']);
			}
		}
		try {
			$query = "SELECT `f`.`id`,`f`.`location`,`f`.`title`,`f`.`description`,`f`.`keywords` FROM `st_files` as `f`$catsel WHERE `enabled`='1'$where$catwhere$limit";
			if (isset($arguments['num-results-only']) && $arguments['num-results-only'] === true) {
				$num_rows = Saint::getNumRows($query);
				return $num_rows;
			} else {
				Saint::logError($query);
				$allfiles = Saint::getAll($query);
				
				$indexfiles = array();
				for ($i = 0; $i < sizeof($allfiles); $i++) {
					// Enter queried data into array
					$indexfiles[$i]['id'] = $allfiles[$i][0];
					$indexfiles[$i]['location'] = $allfiles[$i][1];
					$indexfiles[$i]['title'] = $allfiles[$i][2];
					$indexfiles[$i]['description'] = $allfiles[$i][3];
					$indexfiles[$i]['keywords'] = $allfiles[$i][4];
				}
				return $indexfiles;
			}
		} catch (Exception $e) {
			if ($e->getCode()) {
				Saint::logError("Problem selecting files from database: ".$e->getMessage(),__FILE__,__LINE__);
			}

			if (isset($arguments['num-results-only']) && $arguments['num-results-only'] === true) {
				return 0;
			} else {
				return array();
			}
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
		$userdir = Saint::getThemeDir() . "/images";
		$uploaddir = SAINT_SITE_ROOT . "/media";
		if (file_exists($userdir)) {
			$userfiles = array_merge(
				Saint_Model_Block::recursiveScan($userdir),
				Saint_Model_Block::recursiveScan($uploaddir)
			);
		} else {
			$userfiles = Saint_Model_Block::recursiveScan($uploaddir);
		}
		
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
	
	protected $_files;
	protected $_status;
	
	/**
	 * Instantiate the model with blank data.
	 */
	public function __construct() {
		$this->_files = array();
		$this->_status = array();
		parent::__construct();
	}

	/**
	 * Get status message.
	 * @return array Status message(s).
	 */
	public function getStatus() {
		return $this->_status;
	}
		
	/**
	 * Set status messages.
	 * @param array $status New status message(s).
	 */
	public function setStatus($status) {
		if (!is_array($status))
			$status = array($status);
		$this->_status = $status;
	}

	/**
	 * Process page arguments.
	 * @see core/code/Model/Saint_Model_Page::process()
	 */
	public function process() {
		return Saint_Controller_FileManager::process();
	}
}