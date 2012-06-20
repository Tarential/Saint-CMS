<?php
/**
 * Model of a page layout within the Saint framework.
 * @author Preston St. Pierre
 * @package Saint
 */
class Saint_Model_Layout {
	protected $_name;
	protected $_page;
	protected $_saved;
	protected $_content;
	
	/**
	 * Check if the given layout name has an available template file.
	 * @param string $layout Layout name to check.
	 * @return boolean True if layout is active, false otherwise.
	 */
	public static function inUse($layout) {
		if ($layout = Saint::sanitize($layout,SAINT_REG_NAME)) {
			if (Saint_Model_Block::inUse("layouts/".$layout)) {
				return 1;
			}
		}
		return 0;
	}
	
	/**
	 * Create a layout with blank data.
	 */
	public function __construct() {
		$this->_id = 0;
		$this->_name = '';
		$this->_page = null;
		$this->_saved = false;
		$this->_content = '';
	}
	
	/**
	 * Set the name of the layout to use.
	 * @param string $name Name of layout to use.
	 * @return boolean True if layout is active, false otherwise.
	 */
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
	
	/**
	 * Output selected page to the client.
	 * @param string $page Name of page to render.
	 * @return boolean True if page found, false otherwise.
	 */
	public function render($page, $options = array()) {
		$this->_page = $page;
		if (isset($options['get'])) {
			$get = $options['get'];
		} else {
			$get = false;
		}
		if ($this->_name == "") {
			Saint::includeBlock("layouts/system/404");
			return 0;
		} else {
			if ($get) {
				return Saint::getBlock("layouts/".$this->_name);
			} else {
				Saint::includeBlock("layouts/".$this->_name);
				return 0;
			}
		}
	}
}
