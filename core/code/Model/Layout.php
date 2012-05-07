<?php

class Saint_Model_Layout {
	protected $_name;
	protected $_page; // Saint_Model_Page
	protected $_saved;
	protected $_content;
	
	public static function inUse($layout) {
		if ($layout = Saint::sanitize($layout,SAINT_REG_NAME)) {
			if (Saint_Model_Block::inUse("layouts/".$layout)) {
				return 1;
			}
		}
		return 0;
	}
	
	public function __construct() {
		$this->_id = 0;
		$this->_name = '';
		$this->_page = null;
		$this->_saved = false;
		$this->_content = '';
	}
	
	public function loadByName($name) {
		if ($name = Saint::sanitize($name,SAINT_REG_NAME)) {
			if (Saint_Model_Layout::inUse($name)) {
				$this->_name = $name;
				return 1;
			} else {
				Saint::logError("Cannot find layout with name $name.",__FILE__,__LINE__);
				return 0;
			}
		} else
			return 0;
	}
	
	public function render($page) {
		$this->_page = $page;
		if ($this->_name == "")
			Saint::includeBlock("layouts/default");
		else
			Saint::includeBlock("layouts/".$this->_name);
		return 1;
	}
}
