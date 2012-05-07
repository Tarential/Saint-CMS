<?php

class Saint_Model_Page {
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
	 * Checks if page name is available for use
	 * @param string $name Name to test
	 * @global string SAINT_REG_NAME Pattern matching valid page names
	 * @return boolean True if available, false otherwise
	 */
	public static function nameAvailable($name) {
		if ($name = Saint::sanitize($name,SAINT_REG_NAME)) {
			try {
				$id = Saint::getOne("SELECT `id` FROM `st_pages` WHERE `name`='$name'");
				return 0;
			} catch (Exception $e) {
				return 1;
			}
		} else
			return 0;
	}
	
	public static function addPage($name,$layout,$title = '') {
		$sname = Saint::sanitize($name,SAINT_REG_NAME);
		$slayout = Saint::sanitize($layout,SAINT_REG_NAME);
		$stitle = Saint::sanitize($title);
		if ($sname && $slayout) {
			try {
				Saint::query("INSERT INTO st_pages (`name`,`layout`,`title`) VALUES ('$sname','$slayout','$stitle')");
				Saint::logEvent("Added page '$stitle' with url '$sname' and layout '$slayout'.",__FILE__,__LINE__);
				return 1;
			} catch (Exception $e) {
				Saint::logError("Problem inserting page into database: ".$e->getMessage(),__FILE__,__LINE__);
				return 0;
			}
		} else {
			Saint::logError("Invalid page or layout name '$name' and '$layout'. Check the manual for allowed names.",__FILE__,__LINE__);
			return 0;
		}
	}
	
	public static function deletePage($id) {
		$delpage = new Saint_Model_Page();
		if ($delpage->loadById($id)) {
			return $delpage->delete();
		} else
			return 0;
	}
	
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
	}
	
	/**
	 * Load page from database
	 * @param int $id
	 * @return boolean Success/failure
	 */
	public function loadById($id) {
		if ($id = Saint::sanitize($id,SAINT_REG_ID)) {
			try {
				$language = Saint::getCurrentUser()->getLanguage();
				$info = Saint::getRow("SELECT name,title,layout,meta_keywords,meta_description,allow_robots FROM st_pages WHERE id='$id'");
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
	 * Retrieve page ID based on name, then load by ID
	 * @param string $name
	 * @return boolean Success/failure
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
	 * Request page ID
	 * @return int Page ID
	 */
	public function getId() {
		return $this->_id;
	}
	
	/**
	 * Request page name
	 * @return string Page name
	 */
	public function getName() {
		return $this->_name;
	}
	
	/**
	 * Request page title
	 * @return string Name of label used as page title
	 */
	public function getTitle() {
		if ($this->_temptitle == null)
			return $this->_title;
		else
			return $this->_temptitle;
	}
	
	/**
	 * Request page layout
	 * @return string Name of layout used by this page
	 */
	public function getLayout() {
		if ($this->_templayout == null)
			return $this->_layout;
		else
			return $this->_templayout;
	}
	
	/**
	 * Request meta keywords
	 * @return string[] Page meta keywords
	 */
	public function getMetaKeywords() {
		if ($this->_temp_meta_keywords == null)
			return $this->_meta_keywords;
		else
			return $this->_temp_meta_keywords;
	}
	
	/**
	 * Request meta description
	 * @return string Page meta description
	 */
	public function getMetaDescription() {
		if ($this->_temp_meta_description == null)
			return $this->_meta_description;
		else
			return $this->_temp_meta_description;
	}
	
	/**
	 * Request page arguments
	 * @return string[] Page arguments
	 */
	public function getArgs() {
		return $this->_args;
	}
	
	/**
	 * Request page blocks
	 * @return string[] Block names used in page
	 */
	public function getBlocks() {
		return $this->_blocks;
	}

	/**
	 * Request page current block id
	 * @return int Current block id used in page
	 */
	public function getBlockId() {
		return $this->_bid;
	}
	
	/**
	 * Flag page as enabled
	 * @return boolean True if page enabled, false otherwise
	 */
	public function enable() {
		$this->_enabled = true;
		return 1;
	}
	
	/**
	 * Flag page as disabled
	 * @return boolean True if page disabled, false otherwise
	 */
	public function disable() {
		$this->_enabled = false;
		return 1;
	}

	/**
	 * Check if robots are allowed
	 * @return boolean True if robots allowed, false otherwise
	 */
	public function allowsRobots() {
		return $this->_allow_robots;
	}
	
	/**
	 * Flag robots as allowed
	 * @return boolean True if robots allowed, false otherwise
	 */
	public function enableRobots() {
		$this->_allow_robots = true;
		return 1;
	}
	
	/**
	 * Flag robots as disallowed
	 * @return boolean True if robots disallowed, false otherwise
	 */
	public function disableRobots() {
		$this->_disallow_robots = false;
		return 1;
	}

	/**
	 * Add block association to page
	 * @param $block Block name
	 * @return boolean Always true
	 */
	public function addBlock($block) {
		$this->_newblocks[] = $block;
		return 1;
	}

	/**
	 * Remove block from being associated with page
	 * @param $block Block name
	 * @return boolean Always true
	 */
	public function remBlock($block) {
		unset($this->_newblocks[array_search($block,$this->_newblocks)]);
		return 1;
	}
	
	/**
	 * Set the current block ID
	 * @param $bid New ID
	 * @return boolean Always true
	 */
	public function setBlockId($bid) {
		$this->_bid = $bid;
		return 1;
	}
	
	/**
	 * Set page title to a new label.
	 * Note: This is not the proper function for updating the title. Instead, use the associated Label functions.
	 * @param string $title Name of the associated label
	 * @return boolean Success/failure
	 */
	public function setTitle($title) {
		if ($title = Saint::sanitize($title)) {
			$this->_title = $title;
			return 1;
		} else
			return 0;
	}

	public function setTempTitle($title) {
		if ($title = Saint::sanitize($title)) {
			$this->_temptitle = $title;
			return 1;
		} else
			return 0;
	}
	
	/**
	 * Set a new page name
	 * @param string $name New name for page
	 * @return boolean Success/failure
	 */
	public function setName($name) {
		if ($name = Saint::sanitize($name,SAINT_REG_NAME)) {
			$this->_name = $name;
			return 1;
		} else
			return 0;
	}
	
	/**
	 * Set a new page layout
	 * @param string $layout Name of new layout for page
	 * @return boolean Success/failure
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
	 * Set a new page layout temporarily (doesn't save to db)
	 * @param string $layout Name of new layout for page
	 * @return boolean Success/failure
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
	 * Set a new page meta description
	 * @param string $description New meta description for page
	 * @return boolean Success/failure
	 */
	public function setMetaDescription($description) {
		if ($description = Saint::sanitize($description)) {
			$this->_meta_description = $description;
			return 1;
		} else
			return 0;
	}
	
	public function setTempMetaDescription($description) {
		if ($description = Saint::sanitize($description)) {
			$this->_temp_meta_description = $description;
			return 1;
		} else
			return 0;
	}
	
	/**
	 * Set page arguments
	 * @param string[] $args Set of arguments for page in form $key=>$val
	 * @return boolean True if successful, false otherwise
	 */
	public function setArgs($args) {
		if (is_array($args)) {
			$this->_args = $args;
			return 1;
		} else
			return 0;
	}
	
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
	
	public function setMetaKeywords($tags) {
		if (!is_array($tags))
			$tags = explode(',',$tags);
		
		foreach ($tags as $key=>$tag)
			$tags[$key] = Saint::sanitize($tag);
		
		$this->_meta_keywords = $tags;
		return 1;
	}
	
	public function setTempMetaKeywords($tags) {
		if (!is_array($tags))
			$tags = explode(',',$tags);
		
		foreach ($tags as $key=>$tag)
			$tags[$key] = Saint::sanitize($tag);
		
		$this->_temp_meta_keywords = $tags;
		return 1;
	}
	
	/**
	 * Add meta keyword(s)
	 * @param string[] $tags New meta tag(s)
	 * @return boolean Success/failure
	 */
	public function addMetaKeywords($tags) {
		if (!is_array($tags))
			$tags = array($tags);
		
		foreach ($tags as $key=>$tag)
			$tags[$key] = Saint::sanitize($tag);
		
		$this->_meta_keywords = array_unique(
		array_merge($this->_meta_keywords,$tags));
		return 1;
	}
	
	/**
	 * Remove meta keyword(s)
	 * @param string[] $tags New meta tag(s)
	 * @return boolean Success/failure
	 */
	public function remMetaKeywords($tags) {
		if (!is_array($tags))
			$tags = array($tags);
		foreach ($tags as $tag) {
			if (in_array($tag,$this->_meta_keywords)) {
				unset($this->_meta_keywords[array_search($tag,$this->_meta_keywords)]);
			}
		}
		return 1;
	}
	
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
	 * Save page details
	 * @return boolean True for success, false otherwise
	 */
	public function save($log = true) {
		if ($this->_id) {
			try {
				# Add any new blocks
				foreach ($this->_blocks as $block) {
					try {
						Saint::getAll("SELECT `id` FROM `st_pageblocks` WHERE `block`='$block'");
					} catch (Exception $f) {
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
					# Happens under normal conditions
					//Saint::logError("Problem selecting blocks for page '$this->_name': ".$n->getMessage(),__FILE__,__LINE__);
				}
				
				$query = "UPDATE st_pages SET ".
				"enabled='$this->_enabled',".
				"name='$this->_name',".
				"title='$this->_title',".
				"layout='$this->_layout',".
				"meta_keywords='".implode(',',$this->_meta_keywords)."',".
				"meta_description='$this->_meta_description',".
				"allow_robots='$this->_allow_robots' ".
				"WHERE id=$this->_id";
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
				$query = "INSERT INTO st_pages (name,title,layout,meta_keywords,meta_title,allow_robots,blocks) ".
				" VALUES ('$this->_name','$this->_title','$this->_layout','".implode(',',$this->_meta_keywords).
				"','$this->_meta_title','$this->_allow_robots','".implode(' ',$this->_blocks)."')";
				Saint::query($query);
				return 1;
			} catch (Exception $e) {
				Saint::logError("Problem saving page $this->_name. ".$e->getMessage(),__FILE__,__LINE__);
				return 0;
			}
		}
	}
	
	/**
	 * Allow the controller to process the page
	 * @return boolean True for success, false otherwise
	 */	
	public function process() {
		$controller = new Saint_Controller_Page($this);
		if ($controller->process())
			return 1;
		else
			return 0;
	}
	
	/**
	 * Render page
	 * @return boolean True for success, false otherwise
	 */
	public function render() {
		if (isset($this->_templayout))
			$lname = $this->_templayout;
		else
			$lname = $this->_layout;
		if ($layout = Saint::getLayout($lname)) {
			try {
				if ($layout->render($this)) {
					$this->_blocks = $this->_newblocks;
					$this->save(false);
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
