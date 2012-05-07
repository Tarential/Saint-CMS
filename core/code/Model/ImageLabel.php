<?php
class Saint_Model_ImageLabel extends Saint_Model_FileLabel {

	public static function getImage($name, $arguments = array()) {
		$fid = Saint_Model_FileLabel::getFileId($name, $arguments);
		if ($fid) {
			$image = new Saint_Model_Image();
			$image->setName($name);
			$image->load($fid,$arguments);
			$image->display();
		} else {
			Saint::logError("Error retrieving image information for '$name'.",__LINE__,__FILE__);
		}
	}
}
