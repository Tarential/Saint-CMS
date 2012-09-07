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
		if (Saint::getCurrentUser()->hasPermissionTo('edit-block')) {
			switch ($setting) {
				case "post":
					echo "Associated post: ".$this->getParent();
					break;
				case "finalized":
					echo Saint::genField("saint-block-setting-finalized","select","Enabled",
						array('options'=>array('1'=>'Yes','0'=>'No'),'selected'=>$this->get("finalized")));
					break;
				default:
					parent::renderInput($setting,$options);
			}
		} else {
			switch ($setting) {
				case "postdate":
					echo '<span class="saint-label">Posted on:</span>';
					echo '<p>'.$this->get($setting).'</p>';
					break;
				case "post":
					$ap = new Saint_Model_BlogPost();
					$ap->load($this->getParent());
					echo '<span class="saint-label">In reply to:</span>';
					echo '<p>'.$ap->get("title").'</p>';
					break;
				case "finalized":
					break;
				default:
					parent::renderInput($setting,$options);
			}
		}
	}
	
	/**
	 * Custom permissions for blog comments.
	 * @param Saint_Model_User $user User requesting action to be performed.
	 * @param string $action Action being requested.
	 * @return boolean True grants permission, false otherwise.
	 */
	public function hasPermissionTo($user, $action) {
		if ($user->hasPermissionTo('edit-block')) {
			return 1;
		} elseif ($user->getId() == $this->getOwner()) {
			# Guest users can't edit posts once saved (to avoid security problems).
			if ($user->getId() == 0) {
				if ($this->get('finalized') === '0') {
					return 1;
				}
				return 0;
			}
			return 1;
		} else {
			return 0;
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
		if (Saint::getCurrentUser()->hasPermissionTo('edit-block')) {
			return parent::save();
		} else {
			Saint::addNotice("Thank you for your comment. It will appear on the site after it has been approved by a moderator.");
			$this->set('finalized',1);
			$this->disable();
			$this->set('postdate',date('Y-m-d H:i:s'));
			Saint_Controller_Contact::emailAdmin(array('Message' => "There is a new comment awaiting moderation on your Saint blog."));
			return parent::save();
		}
	}
}
