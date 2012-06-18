<?php
/**
 * Blog settings and functions.
 * @author Preston St. Pierre
 * @package Saint
 */
class Saint_Model_Blog extends Saint_Model_Page {
	protected $_post_args;
	protected $_posts;
	
	/**
	 * Initialize blog settings.
	 */
	public function __construct() {
		$this->_post_args = array();
		$this->_posts = array();
		parent::__construct();
	}
	
	/**
	 * Process input settings.
	 * @see core/code/Controller/Saint_Controller_Blog::process()
	 */
	public function process() {
		Saint_Controller_Blog::process($this);
	}
	
	/**
	 * Get arguments for displaying posts.
	 * @return array Arguments for displaying posts.
	 */
	public function getPostArgs() {
		return $this->_post_args;
	}
	
	/**
	 * Set arguments for displaying posts.
	 * @param array $args New arguments for displaying posts.
	 */
	public function setPostArgs($args) {
		if (is_array($args)) {
			$this->_post_args = $args;
		} else {
			$this->_post_args = array($args);
		}
	}
	
	/**
	 * Get posts prepared for this page.
	 */
	public function getPosts() {
		return $this->_posts;
	}
	
	/**
	 * Set posts prepared for this page.
	 * @param array $posts Array of Saint_Model_BlogPost ready for use.
	 */
	public function setPosts($posts) {
		if (is_array($posts)) {
			$this->_posts = $posts;
		} else {
			$this->_posts = array($posts);
		}
	}
}
