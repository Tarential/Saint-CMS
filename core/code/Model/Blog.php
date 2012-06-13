<?php
class Saint_Model_Blog extends Saint_Model_Page {
	protected $_post_args;
	
	public function __construct() {
		$this->_post_args = array();
		parent::__construct();
	}
	
	public function process() {
		Saint_Controller_Blog::process($this);
	}
	
	public function getPostArgs() {
		return $this->_post_args;
	}
	
	public function setPostArgs($args) {
		if (is_array($args)) {
			$this->_post_args = $args;
		}
	}
}
