<?php
class Saint_Model_Image extends Saint_Model_File {
	protected $_linktofull;
	protected $_imgfile;
	protected $_imgtype;
	
	public function __construct($id = null,$arguments = array()) {
		parent::__construct($id);
		if (isset($arguments['link'])) {
			$this->_linktofull = $arguments['link'];
		} else {
			$this->_linktofull = false;
		}
	}
	
	public function load($id, $arguments = array()) {
		if (isset($arguments['link'])) {
			$this->_linktofull = $arguments['link'];
		} else {
			$this->_linktofull = false;
		}
		parent::load($id);
	}
	
	public function getResizedUrl($arguments) {
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
	
	public function getIconUrl() {
		$arguments = array(
			'max-height' => 128,
			'max-width' => 128,
		);
		return $this->getResizedUrl($arguments);
	}
	
	public function linkToFull() {
		return $this->_linktofull;
	}
	
	public function display($label = null) {
		$page = Saint::getCurrentPage();
		$page->curfile = $this;
		Saint::includeBlock("file-manager/image",false);
	}

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

	private function loadFile($filename) {
		$details = getimagesize($filename);
		$this->_imgtype = $details[2];
		if( $this->_imgtype == IMAGETYPE_JPEG ) { 
			$this->_imgfile = imagecreatefromjpeg($filename);
		} elseif( $this->_imgtype == IMAGETYPE_GIF ) { 
			$this->_imgfile = imagecreatefromgif($filename);
		} elseif( $this->_imgtype == IMAGETYPE_PNG ) { 
			$this->_imgfile = imagecreatefrompng($filename);
		}
	}
	
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
