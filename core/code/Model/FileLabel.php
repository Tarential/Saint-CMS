<?php
class Saint_Model_FileLabel {
	protected $_name;
	
	public static function getFile($labelname, $arguments = array()) {
		$fid = Saint_Model_FileLabel::getFileId($labelname, $arguments);
		if ($fid) {
			$file = new Saint_Model_File();
			$file->setName($labelname);
			$file->load($fid);
			$file->display();
		}
	}
	
	public static function setFile($labelname, $arguments = array()) {
		Saint::logError("Name: ".$labelname);
		$sname = Saint::sanitize($labelname,SAINT_REG_NAME);
		if ($sname) {
			if (isset($arguments['fid'])) {
				# Check to make sure the fileid exists
				if (Saint_Model_File::idExists($arguments['fid'])) {
					try {
						Saint::query("UPDATE `st_filelabels` SET `fileid`='$arguments[fid]' WHERE `name`='$labelname'");
						return 1;
					} catch (Exception $e) {
						Saint::logError("Unable to set new file id for label '$labelname':".$e->getMessage(),__FILE__,__LINE__);
						return 0;
					}
				} else {
					Saint::logError("Attempting to set file label with non existent ID '$arguments[fid]'.",__FILE__,__LINE__);
				}
			}
		} else {
			Saint::logError("Invalid file name: '$labelname'.",__FILE__,__LINE__);
			return 0;
		}
	}
	
	public static function getFileId($name, $arguments = array()) {
		$sname = Saint::sanitize($name,SAINT_REG_NAME);
		if ($sname) {
			try {
				return Saint::getOne("SELECT `fileid` FROM `st_filelabels` WHERE `name`='$sname'");
			} catch (Exception $e) {
				# No file label yet assigned, that's ok we'll create one with the default
				if (isset($arguments['default']) && file_exists(Saint::getSiteRoot() . $arguments['default'])) {
					$fileloc = $arguments['default'];
				} else
					$fileloc = Saint_Model_FileManager::getImageIconUrl();
				return Saint_Model_FileLabel::createLabel($sname,$fileloc);
			}
		} else {
			Saint::logError("Invalid file name: '$name'.",__FILE__,__LINE__);
			return 0;
		}
	}
	
	public static function createLabel($name,$file) {
		$sname = Saint::sanitize($name,SAINT_REG_NAME);
		$slocation = Saint::sanitize($file);
		try {
			$fileid = Saint::getOne("SELECT `id` FROM `st_files` WHERE `location`='$slocation'");
			try {
				Saint::query("INSERT INTO `st_filelabels` (`name`,`fileid`) VALUES ('$sname','$fileid')");
				return $fileid;
			} catch (Exception $t) {
				Saint::logError("Couldn't auto insert new image label named '$name' for file id '$fileid': ".
					$n->getMessage(),__FILE__,__LINE__);
				return 0;
			}
		} catch (Exception $n) {
			Saint::logError("Couldn't get new label image's file id: ".$n->getMessage(),__FILE__,__LINE__);
			return 0;
		}
	}
}
