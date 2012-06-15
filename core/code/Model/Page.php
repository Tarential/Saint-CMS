<?php
/**
 * Model of a single page within the Saint framework.
 * @author Preston St. Pierre
 * @package Saint
 */
class Saint_Model_Page {
	/**
	 * Get all the page names from the database.
	 * @return string[] Names of all pages in the database.
	 */
	public static function getAllPages() {
		try {
			$pagenames = Saint::getAll("SELECT `name` FROM `st_pages`");
			if ($pagenames == null)
				throw new Exception("No pages.");
			$pages = array();
			foreach ($pagenames as $curpage) {
				$newpage = new Saint_Model_Page();
				if ($newpage->loadByName($curpage))
					$pages[] = $newpage;
			}
			return $pages;
		} catch (Exception $e) {
			Saint::logError("Your site has no pages... how did that happen? Reinstall the cms or see the documentation to add a page manually.",__FILE__,__LINE__);
			return array();
		}
	}
	
	/**
	 * Get the name of the model to use for page of given name.
	 * @param $name Name of page to check.
	 * @return string Name of model to use for given page.
	 */
	public static function getModel($name) {
		$default = "Saint_Model_Page";
		if ($sname = Saint::sanitize($name,SAINT_REG_NAME)) {
			try {
				$model = Saint::getOne("SELECT `model` FROM `st_pages` WHERE `name`='$sname'");
				if (class_exists($model) && ($model == $default || is_subclass_of($model,$default))) {
					return $model;
				}
			} catch (Exception $e) {
				if ($e->getCode()) {
					Saint::logError("Unable to select page model from database: ".$e->getMessage,__FILE__,__LINE__);
				}
				return $default;
			}
		} else {
			Saint::logError("Invalid page name: '$name'.",__FILE__,__LINE__);
			return $default;
		}
	}
	
	/**
	 * Checks if page name is available for use (ie not in database).
	 * @param string $name Name to test.
	 * @return boolean True if available, false otherwise.
	 */
	public static function nameAvailable($name) {
		if ($sname = Saint::sanitize($name,SAINT_REG_NAME)) {
			try {
				$id = Saint::getOne("SELECT `id` FROM `st_pages` WHERE `name`='$sname'");
				return 0;
			} catch (Exception $e) {
				if ($e->getCode()) {
					Saint::logError("Unable to select page ID from database: ".$e->getMessage,__FILE__,__LINE__);
				}
				return 1;
			}
		} else {
			Saint::logError("Invalid page name: '$name'.",__FILE__,__LINE__);
			return 0;
		}
	}
	
	/**
	 * Add a new page to the database.
	 * @param string $name URL-friendly identifier for new page.
	 * @param string $layout Name of layout to use for page.
	 * @param string $title Title for page.
	 * @param string $keywords Keywords for page.
	 * @param string $description Description for page.
	 * @return boolean True on success, false otherwise.
	 */
	public static function addPage($name,$layout,$title='',$keywords='',$description='',$cats=array()) {
		$page = new Saint_Model_Page();
		$page->setLayout($layout);
		$page->setName($name);
		$page->setTitle($title);
		$page->setKeywords($keywords);
		$page->setDescription($description);
		$success = $page->save();
		$page->setCategories($cats);
		return $success;
	}
	
	/**
	 * Delete page with given ID from database.
	 * @param int $id ID of page to delete.
	 * @return boolean True on success, false otherwise.
	 */
	public static function deletePage($id) {
		$delpage = new Saint_Model_Page();
		if ($delpage->loadById($id)) {
			return $delpage->delete();
		} else
			return 0;
	}
	
	protected $_id;
	protected $_bid;
	protected $_name;
	protected $_enabled;
	protected $_args;
	protected $_title;
	protected $_temptitle;
	protected $_layout;
	protected $_templayout;
	protected $_meta_description;
	protected $_temp_meta_description;
	protected $_meta_keywords;
	protected $_temp_meta_keywords;
	protected $_allow_robots;
	protected $_blocks;
	protected $_newblocks; # Blocks are recalculated at each rendering
	protected $_edit_block; # Block currently being edited by admin
	
	/**
	 * Load page model with blank data.
	 */
	public function __construct() {
		$this->_id = 0;
		$this->_bid = 0;
		$this->_name = '';
		$this->_enabled = true;
		$this->_args = array();
		$this->_title = '';
		$this->_temptitle = null;
		$this->_layout = '';
		$this->_templayout = null;
		$this->_meta_description = '';
		$this->_temp_meta_description = null;
		$this->_meta_keywords = array();
		$this->_temp_meta_keywords = null;
		$this->_allow_robots = true;
		$this->_blocks = array();
		$this->_newblocks = array();
		$this->_edit_block = new Saint_Model_Block();
	}
	
	/**
	 * Load page model with information from the database matching the given ID.
	 * @param int $id ID of page to load.
	 * @return boolean True on success, false otherwise.
	 */
	public function loadById($id) {
		if ($id = Saint::sanitize($id,SAINT_REG_ID)) {
			try {
				$language = Saint::getCurrentUser()->getLanguage();
				$info = Saint::getRow("SELECT `name`,`title`,`layout`,`meta_keywords`,`meta_description`,`allow_robots` FROM `st_pages` WHERE `id`='$id'");
				$this->_id = $id;
				$this->_name=$info[0];
				$this->_title=$info[1];
				$this->_layout=$info[2];
				$this->_meta_keywords=explode(',',$info[3]);
				$this->_meta_description=$info[4];
				$this->_allow_robots=$info[5];
				return 1;
			} catch (Exception $e) {
				Saint::logError("Cannot load Page model from ID $id. Error: " . $e->getMessage(),__FILE__,__LINE__);
				return 0;
			}
		} else
			return 0;
	}

	/**
	 * Retrieve page ID based on name, then load by ID.
	 * @param string $name Name of page to load.
	 * @return boolean True on success, false otherwise.
	 */
	public function loadByName($name) {
		if ($name = Saint::sanitize($name,SAINT_REG_NAME)) {
			try {
				$id = Saint::getOne("SELECT `id` FROM `st_pages` WHERE `name`='$name'");
				return $this->loadById($id);
			} catch (Exception $e) {
				Saint::logError("Cannot find page $name. Error: " . $e->getMessage(),__FILE__,__LINE__);
				return 0;
			}
		} else
			return 0;
	}
	
	/**
	 * Request page ID.
	 * @return int Page ID.
	 */
	public function getId() {
		return $this->_id;
	}
	
	/**
	 * Request page name.
	 * @return string Page name.
	 */
	public function getName() {
		return $this->_name;
	}
	
	/**
	 * Request page URL.
	 * @return string Page URL.
	 */
	public function getUrl() {
		return SAINT_URL . "/" . $this->_name;
	}
	
	/**
	 * Request page title.
	 * @return string Page title.
	 */
	public function getTitle() {
		if ($this->_temptitle == null)
			return $this->_title;
		else
			return $this->_temptitle;
	}
	
	/**
	 * Request page layout.
	 * @return string Name of layout used by this page.
	 */
	public function getLayout() {
		if ($this->_templayout == null)
			return $this->_layout;
		else
			return $this->_templayout;
	}
	
	/**
	 * Request page keywords.
	 * @return string[] Page keywords.
	 */
	public function getMetaKeywords() {
		if ($this->_temp_meta_keywords == null)
			return $this->_meta_keywords;
		else
			return $this->_temp_meta_keywords;
	}
	
	/**
	 * Request page description.
	 * @return string Page description.
	 */
	public function getMetaDescription() {
		if ($this->_temp_meta_description == null)
			return $this->_meta_description;
		else
			return $this->_temp_meta_description;
	}
	
	/**
	 * Request page arguments.
	 * @return string[] Page arguments.
	 */
	public function getArgs() {
		return $this->_args;
	}
	
	/**
	 * Request value of page argument for given key.
	 * @param string Key to match.
	 * @return string Page argument value.
	 */
	public function getArg($key) {
		if (isset($this->_args[$key]))
			return $this->_args[$key];
		else
			return '';
	}
	
	/**
	 * Set value of page argument for given key.
	 * @param string Key whose value to set.
	 * @param string New value for key.
	 * @return boolean True on success, false otherwise.
	 */
	public function setArg($key,$value) {
		$sval = Saint::sanitize($value);
		if ($value == 0 || $sval != 0) {
			$this->_args[$key] = $sval;
			return 1;
		} else {
			return 0;
		}
	}
	
	/**
	 * Set new model for loaded page.
	 * @param string $newmodel Model descended from (or equal to) Saint_Model_Page.
	 * @return boolean True on success, false otherwise.
	 */
	public function setModel($newmodel) {
		$newmodel = Saint::sanitize($newmodel);
		if (is_a($newmodel,"Saint_Model_Page") || is_subclass_of($newmodel,"Saint_Model_Page")) {
			try {
				Saint::query("UPDATE `st_pages` SET `model`='$newmodel' WHERE `id`='$this->_id'");
				return 1;
			} catch (Exception $e) {
				Saint::logError("Unable to set page model: ".$e->getMessage(),__FILE__,__LINE__);
			}
		} else {
			Saint::logError("Error: '$newmodel' is not a valid page model.",__FILE__,__LINE__);
		}
		return 0;
	}
	
	/**
	 * Request page blocks.
	 * @return string[] Block names used in page.
	 */
	public function getBlocks() {
		return $this->_blocks;
	}

	/**
	 * Request page current block ID.
	 * 
	 * Each time a block is called the page model stores the block ID for reference.
	 * 
	 * @return int Current block ID running in page.
	 */
	public function getBlockId() {
		return $this->_bid;
	}
	
	/**
	 * Get the block which is currently being edited on this page.
	 * @return Saint_Model_Block Block which is currently being edited.
	 */
	public function getEditBlock() {
		return $this->_edit_block;
	}
	
	/**
	 * Set the block which is currently being edited on this page.
	 * @param Saint_Model_Block $block Block being edited. Will also accept children of Block.
	 * @return boolean True on success, false otherwise.
	 */
	public function setEditBlock($block) {
		if (is_a($block,'Saint_Model_Block') || is_subclass_of($block,'Saint_Model_Block')) {
			$this->_edit_block = $block;
			return 1;
		} else {
			Saint::logError("Given element was not of class Saint_Model_Block, nor was it a child class.",__FILE__,__LINE__);
			return 0;
		}
	}
	
	/**
	 * Flag page as enabled.
	 * @return boolean True if page enabled, false otherwise.
	 */
	public function enable() {
		$this->_enabled = true;
		return 1;
	}
	
	/**
	 * Flag page as disabled.
	 * @return boolean True if page disabled, false otherwise.
	 */
	public function disable() {
		$this->_enabled = false;
		return 1;
	}

	/**
	 * Check if robots are allowed.
	 * @return boolean True if robots allowed, false otherwise.
	 */
	public function allowsRobots() {
		return $this->_allow_robots;
	}
	
	/**
	 * Flag robots as allowed.
	 * @return boolean True if robots allowed, false otherwise.
	 */
	public function enableRobots() {
		$this->_allow_robots = true;
		return 1;
	}
	
	/**
	 * Flag robots as disallowed.
	 * @return boolean True if robots disallowed, false otherwise.
	 */
	public function disableRobots() {
		$this->_disallow_robots = false;
		return 1;
	}

	/**
	 * Add block association to page.
	 * 
	 * To avoid the necessity of developers registering which blocks are used in which page the system tracks them automatically.
	 * 
	 * @param string $block Block name.
	 */
	public function addBlock($block) {
		$this->_newblocks[] = $block;
	}

	/**
	 * Remove block from being associated with page.
	 * @param string $block Block name.
	 */
	public function remBlock($block) {
		unset($this->_newblocks[array_search($block,$this->_newblocks)]);
	}
	
	/**
	 * Set the current block ID.
	 * @param int $bid New ID.
	 */
	public function setBlockId($bid) {
		$this->_bid = $bid;
	}
	
	/**
	 * Set page title.
	 * @param string $title New title for page.
	 * @return boolean True on success, false otherwise.
	 */
	public function setTitle($title) {
		if ($title = Saint::sanitize($title)) {
			$this->_title = $title;
			return 1;
		} else
			return 0;
	}

	/**
	 * Set temporary title which will not be saved to database.
	 * @param string $title Temporary title for page.
	 * @return boolean True on success, false otherwise.
	 */
	public function setTempTitle($title) {
		if ($title = Saint::sanitize($title)) {
			$this->_temptitle = $title;
			return 1;
		} else
			return 0;
	}
	
	/**
	 * Set a new page name.
	 * @param string $name New name for page.
	 * @return boolean True on success, false otherwise.
	 */
	public function setName($name) {
		if ($name = Saint::sanitize($name,SAINT_REG_NAME)) {
			$this->_name = $name;
			return 1;
		} else
			return 0;
	}
	
	/**
	 * Set a new page layout.
	 * @param string $layout Name of new layout for page.
	 * @return boolean True on success, false otherwise.
	 */
	public function setLayout($layout) {
		if (Saint_Model_Layout::inUse($layout)) {
			if ($layout = Saint::sanitize($layout,SAINT_REG_NAME)) {
				$this->_layout = $layout;
				return 1;
			} else {
				Saint::logError("Cannot set layout $layout, the name does not pass validation. ",__FILE__,__LINE__);
				return 0;
			}
		} else {
			Saint::logError("Cannot set layout $layout, the name is not in use. ",__FILE__,__LINE__);
			return 0;
		}
	}

	
	/**
	 * Set a temporary page layout which will not be saved to the database.
	 * @param string $layout Name of new layout for page.
	 * @return boolean True on success, false otherwise.
	 */
	public function setTempLayout($layout) {
		if (Saint_Model_Layout::inUse($layout)) {
			if ($layout = Saint::sanitize($layout,SAINT_REG_NAME)) {
				$this->_templayout = $layout;
				return 1;
			} else {
				Saint::logError("Cannot set temp layout $layout, the name does not pass validation. ",__FILE__,__LINE__);
				return 0;
			}
		} else {
			Saint::logError("Cannot set temp layout $layout, the name is not in use. ",__FILE__,__LINE__);
			return 0;
		}
	}
	
	/**
	 * Set a new page description.
	 * @param string $description New description for page.
	 * @return boolean True on success, false otherwise.
	 */
	public function setDescription($description) {
		if ($description == "" || $description = Saint::sanitize($description)) {
			$this->_meta_description = $description;
			return 1;
		} else
			return 0;
	}
	
	/**
	 * Set a temporary page description which will not be saved to the database.
	 * @param string $description New description for page.
	 * @return boolean True on success, false otherwise.
	 */
	public function setTempDescription($description) {
		if ($description = Saint::sanitize($description)) {
			$this->_temp_meta_description = $description;
			return 1;
		} else
			return 0;
	}
	
	/**
	 * Set page arguments.
	 * @param string[] $args Arguments passed to current page.
	 * @return boolean True on success, false otherwise.
	 */
	public function setArgs($args) {
		if (is_array($args)) {
			$this->_args = $args;
			return 1;
		} else
			return 0;
	}
	
	/**
	 * Add loaded page to given category.
	 * @param string $category Name of category to which to add the page.
	 * @return boolean True on success, false otherwise.
	 */
	public function addToCategory($category) {
		$scategory = Saint::sanitize($category,SAINT_REG_NAME);
		if ($scategory) {
			$id = Saint_Model_Category::getId($scategory);
			if (!$id) {
				$id = Saint_Model_Category::addCategory($scategory);
			}
			if (!$id) {
				Saint::logError("Problem assigning page to category '$scategory'. Unable to get category ID.",__FILE__,__LINE__);
				return 0;
			} else {
				try {
					Saint::query("INSERT INTO `st_pagecats` (`catid`,`pageid`) VALUES ('$id','$this->_id')");
					return 1;
				} catch (Exception $e) {
					Saint::logError("Problem adding page id '$this->_id' to category id '$id': ".$e->getMessage(),__FILE__,__LINE__);
					return 0;
				}
			}
		} else {
			Saint::logError("Invalid category name: '$category'.",__FILE__,__LINE__);
			return 0;
		}
	}
	
	/**
	 * Remove loaded page from given category.
	 * @param string $category Name of category from which to remove the page.
	 * @return boolean True on success, false otherwise.
	 */
	public function removeFromCategory($category) {
		$scategory = Saint::sanitize($category,SAINT_REG_NAME);
		if ($scategory) {
			$id = Saint_Model_Category::getId($scategory);
			if (!$id) {
				# No id... it can't be part of a category that doesn't exist, so our job is done.
				return 1;
			} else {
				try {
					Saint::query("DELETE FROM `st_pagecats` WHERE `catid`='$id' AND `pageid`='$this->_id'");
					return 1;
				} catch (Exception $e) {
					Saint::logError("Problem removing page id '$this->_id' from category id '$id': ".$e->getMessage(),__FILE__,__LINE__);
					return 0;
				}
			}
		} else {
			Saint::logError("Invalid category name: '$category'.",__FILE__,__LINE__);
			return 0;
		}
	}
	
	/**
	 * Set categories for loaded page en masse.
	 * @param string[] $newcats New categories for loaded page.
	 */
	public function setCategories($newcats) {
		if (!is_array($newcats))
			$newcats = explode(',',$newcats);
		
		foreach ($this->getCategories() as $cat) {
			if (!in_array($cat,$newcats)) {
				$this->removeFromCategory($cat);
			}
		}
		foreach ($newcats as $newcat) {
			$this->addToCategory($newcat);
		}
	}
	
	/**
	 * Get categories for the loaded page.
	 * @return string[] Categories for loaded page.
	 */
	public function getCategories() {
		try {
			$getcats = Saint::getAll("SELECT `c`.`id`,`c`.`name` FROM `st_categories` as `c`,`st_pagecats` as `p` WHERE `p`.`pageid`='$this->_id' AND `p`.`catid`=`c`.`id`");
			$cats = array();
			foreach ($getcats as $getcat) {
				$cats[$getcat[0]] = $getcat[1];
			}
			return $cats;
		} catch (Exception $e) {
			# No categories found, so we return a blank array
			return array();
		}
	}
	
	/**
	 * Set keywords for loaded page.
	 * @param string[] $tags New tags for loaded page.
	 */
	public function setKeywords($tags) {
		if (!is_array($tags))
			$tags = explode(',',$tags);
		
		foreach ($tags as $key=>$tag)
			$tags[$key] = Saint::sanitize($tag);
		
		$this->_meta_keywords = $tags;
		return 1;
	}
	
	/**
	 * Set temporary keywords which will not be saved to the database.
	 * @param string[] $tags New tags for loaded page.
	 */
	public function setTempKeywords($tags) {
		if (!is_array($tags))
			$tags = explode(',',$tags);
		
		foreach ($tags as $key=>$tag)
			$tags[$key] = Saint::sanitize($tag);
		
		$this->_temp_meta_keywords = $tags;
	}
	
	/**
	 * Add keyword(s).
	 * @param string[] $tags New meta tag(s).
	 */
	public function addKeywords($tags) {
		if (!is_array($tags))
			$tags = array($tags);
		
		foreach ($tags as $key=>$tag)
			$tags[$key] = Saint::sanitize($tag);
		
		$this->_meta_keywords = array_unique(
		array_merge($this->_meta_keywords,$tags));
	}
	
	/**
	 * Remove keyword(s).
	 * @param string[] $tags New meta tag(s).
	 */
	public function remKeywords($tags) {
		if (!is_array($tags))
			$tags = array($tags);
		foreach ($tags as $tag) {
			if (in_array($tag,$this->_meta_keywords)) {
				unset($this->_meta_keywords[array_search($tag,$this->_meta_keywords)]);
			}
		}
	}
	
	/**
	 * Remove loaded page from the database.
	 * @return string[] Categories for loaded page.
	 */
	public function delete() {
		if ($this->_id) {
			try {
				Saint::query("DELETE FROM `st_pages` WHERE `id`='$this->_id'");
				Saint::logEvent("Deleted page with id '$this->_id' from database.");
				return 1;
			} catch (Exception $e) {
				Saint::logError("Problem deleting page with id '$this->_id': ".$e->getMessage(),__FILE__,__LINE__);
				return 0;
			}
		}
	}
	
	/**
	 * Save index of used blocks to DB.
	 */
	public function saveBlocks() {
		if ($this->_id) {
			# Add any new blocks
			foreach ($this->_blocks as $block) {
				try {
					Saint::getAll("SELECT `id` FROM `st_pageblocks` WHERE `block`='$block'");
				} catch (Exception $f) {
					if ($f->getCode()) {
						Saint::logError("Problem selecting page's block IDs: ".$f->getError());
					}
					try {
						$url = Saint::sanitize($_SERVER['REQUEST_URI']);
						Saint::query("INSERT INTO `st_pageblocks` (`pageid`,`block`,`url`) VALUES ('$this->_id','$block','$url')");
					} catch (Exception $g) {
						Saint::logError("Problem adding block '$block' to page '$this->_name': ".$g->getMessage(),__FILE__,__LINE__);
					}
				}
			}
			# Remove any obsolete blocks
			try {
				$dbblocks = Saint::getAll("SELECT `id`,`block` FROM `st_pageblocks` WHERE `pageid`='$this->_id'");
				foreach ($dbblocks as $curblock) {
					if (!in_array($curblock[1],$this->_blocks)) {
						try {
							Saint::query("DELETE FROM `st_pageblocks` WHERE `id`='$curblock[0]'");
						} catch (Exception $g) {
							Saint::logError("Problem deleting block with id '$curblock[0]': ".$g->getMessage(),__FILE__,__LINE__);
						}
					}
				}
			} catch (Exception $n) {
				if ($n->getCode()) {
					Saint::logError("Unable to select page block information: ".$n->getMessage(),__FILE__,__LINE__);
				}
			}
		}
	}
	
	/**
	 * Save model details to database.
	 * @param boolean Default true to log save event, false otherwise.
	 * @return boolean True on success, false otherwise.
	 */
	public function save($log = true) {
		if ($this->_id) {
			try {	
				$query = "UPDATE `st_pages` SET ".
				"`enabled`='$this->_enabled',".
				"`name`='$this->_name',".
				"`title`='$this->_title',".
				"`layout`='$this->_layout',".
				"`meta_keywords`='".implode(',',$this->_meta_keywords)."',".
				"`meta_description`='$this->_meta_description',".
				"`allow_robots`='$this->_allow_robots' ".
				"WHERE `id`=$this->_id";
				Saint::query($query);
				if ($log)
					Saint::logEvent("Saved details for page '$this->_name'.");
				return 1;
			} catch (Exception $e) {
				Saint::logError("Could not save Page. ".$e->getMessage(),__FILE__,__LINE__);
				return 0;
			}
		} else {
			try {
				$query = "INSERT INTO `st_pages` (`name`,`title`,`layout`,`meta_keywords`,`meta_description`,`allow_robots`,`created`) ".
					" VALUES ('$this->_name','$this->_title','$this->_layout','".implode(',',$this->_meta_keywords).
					"','$this->_meta_description','$this->_allow_robots',NOW())";
				Saint::query($query);
				$this->_id = Saint::getLastInsertId();
				return 1;
			} catch (Exception $e) {
				Saint::logError("Problem creating page $this->_name. ".$e->getMessage(),__FILE__,__LINE__);
				return 0;
			}
		}
	}
	
	/**
	 * Process input using a custom controller. Skeleton only; used in child classes.
	 */
	public function process() {
		
	}
	
	/**
	 * Render the loaded page.
	 * @param boolean Default true to reindex block usage, false to retain cached data.
	 * @return boolean True on success, false otherwise.
	 */
	public function render($indexblocks = true) {
		if (isset($this->_templayout))
			$lname = $this->_templayout;
		else
			$lname = $this->_layout;
		if ($layout = Saint::getLayout($lname)) {
			try {
				if ($layout->render($this)) {
					if ($indexblocks) {
						$this->_blocks = $this->_newblocks;
						$this->saveBlocks();
					}
					return 1;
				} else {
					return 0;
				}
			}
			catch (Exception $e) {
				Saint::logError("Could not render page $this->_name: " . $e->getMessage(),__FILE__,__LINE__);
				return 0;
			}
		} else {
			Saint::logError("Layout not found: $this->_layout",__FILE__,__LINE__);
			return 0;
		}
	}
}
