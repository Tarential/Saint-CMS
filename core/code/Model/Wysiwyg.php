<?php
class Saint_Model_Wysiwyg {
	protected $_id;
	protected $_name;
	protected $_content;
	
	public static function get($name, $default = '') {
		$wysiwyg = new Saint_Model_Wysiwyg($name);
		if ($wysiwyg->getContent() == "") {
			$wysiwyg->setContent($default); }
		$content = '<div id="wysiwyg_'.Saint_Model_Block::convertNameToWeb($name).'" class="saint-wysiwyg">';
		$content .=  $wysiwyg->getContent();
		$content .= '</div>';
		return $content;
	}
	
	public function __construct($name = null) {
		if ($name != null) {
			if (!$this->load($name)) {
				$this->_id = 0;
				$this->_name = '';
				$this->_content = '';
			}
		}
	}
	
	public function getContent() {
		return $this->_content;
	}
	
	public function setContent($content) {
		$this->_content = $content;
		return 1;
	}
	
	public function load($name) {
		$sname = Saint::sanitize($name,SAINT_REG_NAME);
		if ($sname) {
			try {
				$results = Saint::getRow("SELECT `id`,`content` FROM `st_wysiwyg` WHERE `name`='$name'");
				$this->_name = $name;
				$this->_id = $results[0];
				$this->_content = $results[1];
				return 1;
			} catch (Exception $e) {
				$this->_id = 0;
				$this->_name = $name;
				$this->_content = '';
				return 1;
			}
		} else {
			Saint::logError("Failed loading WYSIWYG content: Name '$name' did not match valid patterns.",__FILE__,__LINE__);
			return 0;
		}
	}
	
	public function save() {
		if ($this->_id) {
			try {
				$scontent = Saint::sanitize($this->_content);
				$sname = Saint::sanitize($this->_name,SAINT_REG_NAME);
				Saint::query("UPDATE `st_wysiwyg` SET `content`='$scontent' WHERE `name`='$sname'");
				return 1;
			} catch (Exception $e) {
				Saint::logError("Unable to save WYSIWYG content '$this->_name': ".$e->getMessage(),__FILE__,__LINE__);
				return 0;
			}
		} else {
			try {
				Saint::query("INSERT INTO `st_wysiwyg` (`name`,`content`)  VALUES ('$this->_name','$this->_content')");
				$this->_id = Saint::getLastInsertId();
				return 1;
			} catch (Exception $e) {
				Saint::logError("Unable to save WYSIWYG content '$this->_name': ".$e->getMessage(),__FILE__,__LINE__);
				return 0;
			}
		}
	}
}

