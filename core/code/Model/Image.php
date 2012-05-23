<?php
/**
 * Model for an image, a special case of a file, in the Saint framework.
 * @author Preston St. Pierre
 * @package Saint
 */
class Saint_Model_Image extends Saint_Model_File {
	protected $_linktofull;
	protected $_imgfile;
	protected $_imgtype;
	protected $_arguments;
	
	/**
	 * Attempt to load image matching given ID; load blank model on failure.
	 * @param int $id Optional ID of image to load.
	 * @param string[] $arguments Optional arguments to load into model.
	 * @see core/code/Model/Saint_Model_File::__construct()
	 */
	public function __construct($id = null,$arguments = array()) {
		parent::__construct($id);
		$this->_arguments = $arguments;
		if (isset($arguments['link'])) {
			$this->_linktofull = $arguments['link'];
		} else {
			$this->_linktofull = false;
		}
	}
	
	/**
	 * Load image matching given ID from database.
	 * @param int $id ID of image to load.
	 * @param string[] $arguments Arguments to load into model.
	 * @see core/code/Model/Saint_Model_File::load()
	 */
	public function load($id, $arguments = array()) {
		$this->_arguments = $arguments;
		if (isset($arguments['link'])) {
			$this->_linktofull = $arguments['link'];
		} else {
			$this->_linktofull = false;
		}
		parent::load($id);
	}
	
	/**
	 * Get a URL for the image which matches given arguments.
	 * 
	 * This function will automatically resize and cache images if the available size doesn't match the given arguments.
	 * 
	 * @param string[] $arguments Arguments for image matching URL.
	 * 
	 * Argument options include: height, width, max-height, and max-width.
	 */
	public function getResizedUrl($arguments = null) {
		if ($arguments == null)
			$arguments = $this->_arguments;
		$size = $this->calcResize($arguments);
		if ($size['width'] >= $this->_width && $size['height'] >= $this->_height) {
			return $this->_url;
		}
		
		if (preg_match('/^(.*)(\.[^\.]*)$/',$this->_location,$matches)) {
			$newname = substr($matches[1],strlen(SAINT_SITE_ROOT)+1)."-".$size['width']."x".$size['height'].$matches[2];
			$newname = "/" . preg_replace('/\//','_',$newname);
			$fullpath = SAINT_CACHE_DIR . $newname;
			$newurl = substr($fullpath,strlen(SAINT_SITE_ROOT));
			
			if (file_exists($fullpath))
				return $newurl;
			else {
				$this->loadFile($this->_location);
				$this->resizeFile($size['width'],$size['height']);
				$this->saveFile($fullpath,$this->_imgtype);
				return $newurl;
			}
		} else {
			Saint::logError("Unable to parse filename for resizing.",__FILE__,__LINE__);
			return $this->_url;
		}
	}
	
	/**
	 * Get image icon URL; automatically generates thumbnail.
	 * @return string URL of image icon.
	 */
	public function getIconUrl() {
		$arguments = array(
			'max-height' => 128,
			'max-width' => 128,
		);
		return $this->getResizedUrl($arguments);
	}
	
	/**
	 * Check if the title is to be displayed.
	 * @return boolean True if title is flagged for display, false otherwise.
	 */
	public function showTitle() {
		if (isset($this->_arguments['show_title'])) {
			return $this->_arguments['show_title'];
		} else {
			return 0;
		}
	}
	
	/**
	 * Check if image is flagged to automatically link to the full version when it is displayed resized.
	 * @return boolean True if flagged for automatic linking, false otherwise.
	 */
	public function linkToFull() {
		return $this->_linktofull;
	}
	
	/**
	 * Include image in the rendering page.
	 */
	public function display() {
		$page = Saint::getCurrentPage();
		$page->curfile = $this;
		Saint::includeBlock("file-manager/image",false);
	}

	/**
	 * Calculate final height and width for image resizing based on given parameters.
	 * @param string[] $arguments Options for calculating new image size.
	 * @return string[] Resized height and width values assigned to keys matching their names.
	 */
	private function calcResize($arguments) {
		$newheight = $this->_height;
		$newwidth = $this->_width;
		
		if (isset($arguments['height']) && isset($arguments['width'])) {
			$newheight = floor($arguments['height']);
			$newwidth = floor($arguments['width']);
		} elseif (isset($arguments['height'])) {
			$ratio = $arguments['height'] / $this->_height;
			$newwidth = floor($this->_width * $ratio);
			$newheight = floor($arguments['height']);
		} elseif (isset($arguments['width'])) {
			$ratio = $arguments['width'] / $this->_width;
			$newheight = floor($this->_height * $ratio);
			$newwidth = floor($arguments['width']);
		}
		
		if (isset($arguments['max-width']) && $newwidth > $arguments['max-width']) {
			$ratio = $arguments['max-width'] / $this->_width;
			$newheight = floor($this->_height * $ratio);
			$newwidth = floor($arguments['max-width']);
		} 
		if (isset($arguments['max-height']) && $newheight > $arguments['max-height']) {
			$ratio = $arguments['max-height'] / $this->_height;
			$newwidth = floor($this->_width * $ratio);
			$newheight = floor($arguments['max-height']);
		}
		$size = array(
			'width' => $newwidth,
			'height' => $newheight,
		);
		return $size;
	}
	
	/**
	 * Load an image into memory.
	 * @param string $filename Path to image.
	 * @return boolean True on success, false otherwise.
	 */
	private function loadFile($filename) {
		$details = getimagesize($filename);
		if (is_array($details)) {
			$this->_imgtype = $details[2];
			if( $this->_imgtype == IMAGETYPE_JPEG ) { 
				$this->_imgfile = imagecreatefromjpeg($filename);
			} elseif( $this->_imgtype == IMAGETYPE_GIF ) { 
				$this->_imgfile = imagecreatefromgif($filename);
			} elseif( $this->_imgtype == IMAGETYPE_PNG ) { 
				$this->_imgfile = imagecreatefrompng($filename);
			}
			return 1;
		} else {
			return 0;
		}
	}
	
	/**
	 * Resize the loaded image.
	 * @param int $width New width in pixels.
	 * @param int $height New height in pixels.
	 */
	private function resizeFile($width, $height) {
		$newfile = imagecreatetruecolor($width, $height);
		
		if($this->_imgtype == IMAGETYPE_GIF or $this->_imgtype == IMAGETYPE_PNG) {
			imagecolortransparent($newfile, imagecolorallocatealpha($newfile, 0, 0, 0, 127));
			imagealphablending($newfile, false);
			imagesavealpha($newfile, true);
		}
		
		imagecopyresampled($newfile, $this->_imgfile, 0, 0, 0, 0, $width, $height, $this->_width, $this->_height);
		$this->_imgfile = $newfile;
	}
	
	/**
	 * Save the loaded image to disk.
	 * @param string $filename Path to image save file.
	 * @param int $image_type Optional PHP image type constant
	 * @param int $compression Optional compression level for image; only applies to JPEG format.
	 * @param string $permissions Optional permissions for image file. 
	 */
	private function saveFile($filename, $image_type=IMAGETYPE_JPEG, $compression=80, $permissions=null) {
		if( $image_type == IMAGETYPE_JPEG ) {
			imagejpeg($this->_imgfile,$filename,$compression);
		} elseif( $image_type == IMAGETYPE_GIF ) { 
			imagegif($this->_imgfile,$filename);
		} elseif( $image_type == IMAGETYPE_PNG ) { 
			imagepng($this->_imgfile,$filename);
		}
		if( $permissions != null) { 
			chmod($filename,$permissions);
		}
	}
}
