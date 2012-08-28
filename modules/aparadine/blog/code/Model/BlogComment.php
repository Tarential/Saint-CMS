<?php
/**
 * Blog comments are a bit different than default blocks; you'll find the customizations here.
 * @author Preston St. Pierre
 * @package Saint
 */
class Saint_Model_BlogComment extends Saint_Model_Block {
	
	/**
	 * Override the loading function to make passing of block name optional.
	 * @see core/code/Model/Saint_Model_Block::load()
	 */
	public function load($id, $name=null) {
		// Magic so it can accept the arguments in either order.
		if (!Saint::sanitize($id,SAINT_REG_ID) && Saint::sanitize($name,SAINT_REG_ID)) {
			$id = $name;
		}
		return parent::load("blog/comment",$id);
	}
	
	/**
	 * Wrapper to get block setting "postdate".
	 * @return timestamp Posted time setting of the loaded post.
	 */
	public function getPostDate() {
		return $this->get("postdate");
	}
	
	/**
	 * Blog comments don't have categories :)
	 */
	public function renderCategories($arguments = array()) {
		return '';
	}
	
	/**
	 * Customizations for input fields associated with blog posts.
	 * @see core/code/Model/Saint_Model_Block::renderInput()
	 */
	public function renderInput($setting, $options = array()) {
		switch ($setting) {
			case "postdate":
				echo '<span class="saint-label">Posted on:</span>';
				echo '<p>'.$this->get($setting).'</p>';
				break;
			case "post":
				echo "Associated post: ".$this->getParent();
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
		$arguments['view'] = "blog/comment-preview";
		parent::renderPreview($arguments);
	}
	
	/**
	 * Save comment settings.
	 * @see core/code/Model/Saint_Model_Block::save()
	 */
	public function save() {
		return parent::save();
	}
}
