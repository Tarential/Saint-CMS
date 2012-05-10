<?php
/**
 * Functions specific to image type file labels in the Saint framework.
 * @author Preston St. Pierre
 * @package Saint
 */
class Saint_Model_ImageLabel extends Saint_Model_FileLabel {
	/**
	 * Get an image model matching the given image label name and loaded with the given arguments.
	 * @param string $name Name of image label.
	 * @param string[] $arguments Optional arguments to load into model.
	 * @return Saint_Model_Image Image associated with given name.
	 */
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
