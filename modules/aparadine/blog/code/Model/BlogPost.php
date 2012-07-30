<?php
/**
 * Blog posts are a bit different than default blocks; you'll find the customizations here.
 * @author Preston St. Pierre
 * @package Saint
 */
class Saint_Model_BlogPost extends Saint_Model_Block {
	
	/**
	 * Override the loading function to make passing of block name optional.
	 * @see core/code/Model/Saint_Model_Block::load()
	 */
	public function load($id, $name=null) {
		// Magic so it can accept the arguments in either order.
		if (!Saint::sanitize($id,SAINT_REG_ID) && Saint::sanitize($name,SAINT_REG_ID)) {
			$id = $name;
		}
		return parent::load("blog/post",$id);
	}
	
	/**
	 * Select ID for post matching given URI and load data into model.
	 * Function will first look for an exact match; if that fails it will try a fuzzy match.
	 * @param $uri URI to match.
	 * @return True on success, false otherwise.
	 */
	public function loadByUri($uri) {
		$suri = Saint::sanitize($uri);
		for ($i = 0; $i < 2; $i++) {
			try {
				$id = Saint::getOne("SELECT `id` FROM `st_blocks_blog_post` WHERE `uri` LIKE '$suri' ORDER BY `postdate` DESC");
				return $this->load($id);
			} catch (Exception $e) {
				if ($e->getCode()) {
					Saint::logError("Unable to select blog post via URI: ".$e->getMessage(),__FILE__,__LINE__); }
			}
			$suri = "%$suri%";
		}
		return 0;
	}
	
	/**
	 * Wrapper to get block setting "postdate".
	 * @return timestamp Posted time setting of the loaded post.
	 */
	public function getPostDate() {
		return $this->get("postdate");
	}
	
	/**
	 * Get URL for loaded blog post.
	 * @return string URL for loaded post.
	 * @see core/code/Model/Saint_Model_Block::getUrl()
	 */
	public function getUrl() {
		return $this->getPageUrl() . '/' . $this->_settings['uri'];
	}
	
	/**
	 * Customizations for input fields associated with blog posts.
	 * @see core/code/Model/Saint_Model_Block::renderInput()
	 */
	public function renderInput($setting, $options = array()) {
		switch ($setting) {
			case "title":
				$options['classes'] = 'uri-indicator';
				parent::renderInput($setting,$options);
				break;
			case "uri":
				$options['label'] = "URI:";
				$options['details'] = array($this->getPageUrl()."/[URI]");
				if ($this->get($setting) == "") {
					$options['details'][] = "(Leave blank to auto generate)";
				}
				parent::renderInput($setting,$options);
				break;
			case "description":
				$options['type'] = "textarea";
				parent::renderInput($setting,$options);
				break;
			case "keywords":
				$options['details'] = array("Comma separated values (key,words,etc).");
				parent::renderInput($setting,$options);
				break;
			case "postdate":
				$options['label'] = "Posted on:";
				$options['details'] = array("Format: YYYY-MM-DD HH:MM:SS");
				parent::renderInput($setting,$options);
				break;
			default:
				parent::renderInput($setting,$options);
		}
	}
	
	/**
	 * Set a preview template for editing blog posts.
	 * @see core/code/Model/Saint_Model_Block::renderPreview()
	 */
	public function renderPreview($arguments = array()) {
		$arguments['view'] = "blog/post-preview";
		parent::renderPreview($arguments);
	}
	
	/**
	 * Save post settings.
	 * Function will auto-generate post URI if field is blank.
	 * @see core/code/Model/Saint_Model_Block::save()
	 */
	public function save() {
		if ($this->_settings['uri'] == "") {
			$this->_settings['uri'] = $this->_settings['title'];
		}
		$newuri = preg_replace('/\s/','-',strtolower($this->_settings['uri']));
		$newuri = preg_replace('/[^\w\d-]/','',$newuri);
		$this->_settings['uri'] = $newuri;
		return parent::save();
	}
}
