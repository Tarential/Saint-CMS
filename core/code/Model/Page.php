<?php
/**
 * Model of a single page within the Saint framework.
 * @author Preston St. Pierre
 * @package Saint
 */
class Saint_Model_Page {
	/**
	 * Get all the page names from the database.
	 * @return string[] Names of all matching pages in the database.
	 */
	public static function getPageNames($filters = array()) {
		$options = array('id','enabled','name','title','layout','model','meta_keywords','meta_description','allow_robots','created','updated','weight','parent');
		$order = Saint::getOrder($filters);
		$page_where = Saint::makeConditions($filters,$options,'p');
		$cat_where = '';
		$cat_sel = '';
		if (isset($filters['categories'])) {
			$filters = array('name'=>$filters['categories']);
			$options = array('name');
			$cat_where = "WHERE `c`.`id`=`pc`.`catid` AND `p`.`id`=`pc`.`pageid`" . preg_replace('/^\s*WHERE\s*/',' AND ',Saint::makeConditions($filters,$options,'c'));
			$cat_sel = ", `st_categories` as `c`, `st_page_categories` as `pc`";
		}
		try {
			return Saint::getAll("SELECT `p`.`name` FROM `st_pages` as `p`$cat_sel $page_where$cat_where$order");
		} catch (Exception $e) {
			if ($e->getCode()) {
				Saint::logError("Unable to select pages from the database: ".$e->getMessage(),__FILE__,__LINE__);
			}
			return array();
		}
	}
	
	/**
	 * Get all the pages from the database.
	 * @param $filters Filters to match.
	 * @return Saint_Model_Page All matching pages.
	 */
	public static function getPages($filters = array()) {
		$pagenames = Saint_Model_Page::getPageNames($filters);
		$pages = array();
		foreach ($pagenames as $curpage) {
			$page_model = Saint_Model_Page::getModel($curpage);
			$newpage = new $page_model();
			if ($newpage->loadByName($curpage))
				$pages[] = $newpage;
		}
		return $pages;
	}
	
	/**
	 * Organize given pages by hierarchy.
	 * @param array $pages Pages to sort.
	 * @return array Sorted array of Saint_Model_Page.
	 */
	public static function rankPages($pages = array()) {
		$sorted_pages = array();
		$sub_pages = array();
		# Add main pages
		foreach ($pages as $page) {
			if ($page->getParent() == 0) {
				if (!isset($sorted_pages[$page->getId()])) {
					$sorted_pages[$page->getId()] = array($page,array());
				} else {
					$sorted_pages[$page->getId()][0] = $page;
				}
			} else {
				$sub_pages[] = $page;
			}
		}
		# Add sub pages
		foreach ($sub_pages as $page) {
			if (isset($sorted_pages[$page->getParent()])) {
				$sorted_pages[$page->getParent()][1][] = $page;
			}
		}
		return $sorted_pages;
	}
	
	/**
	 * Get the name of the model to use for page of given name.
	 * @param $name Name of page to check.
	 * @return string Name of model to use for given page.
	 */
	public static function getModel($name) {
		$default = "Saint_Model_Page";
		if ($sname = Saint::sanitize($name,SAINT_REG_PAGE_NAME)) {
			try {
				$model = Saint::getOne("SELECT `l`.`model` FROM `st_pages` as `p`, `st_layouts` as `l` WHERE `p`.`name`='$sname' AND `p`.`layout`=`l`.`name`");
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
		if ($sname = Saint::sanitize($name,SAINT_REG_PAGE_NAME)) {
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
	 * @param array $options Additional page settings.
	 * @return boolean True on success, false otherwise.
	 */
	public static function addPage($name,$options = array()) {
		$page = new Saint_Model_Page();
		$page->setName($name);
		if (isset($options['layout'])) {
			$page->setLayout($options['layout']);
		}
		if (isset($options['title'])) {
			$page->setTitle($options['title']);
		}
		if (isset($options['keywords'])) {
			$page->setKeywords($options['keywords']);
		}
		if (isset($options['description'])) {
			$page->setDescription($options['description']);
		}
		if (isset($options['categories'])) {
			$page->setCategories($options['categories']);
		}
		if (isset($options['parent'])) {
			$page->setParent($options['parent']);
		}
		return $page->save();
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
	protected $_edit_block; # Block currently being edited on this page
	protected $_model;
	protected $_json_data;
	protected $_parent;
	protected $_weight;
	protected $_categories;
	protected $_cats_to_delete;
	protected $_cats_to_add;
	protected $_temp_url;
	protected $_modified;
	protected $_created;
	protected $_errors;
	protected $_files;
	
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
		$this->_edit_block = null;
		#$this->_model = "Saint_Model_Page";
		$this->_json_data = array();
		$this->_parent = 0;
		$this->_weight = 0;
		$this->_categories = array();
		$this->_cats_to_add = array();
		$this->_cats_to_delete = array();
		$this->_temp_url = null;
		$this->_modified = null;
		$this->_created = null;
		$this->_errors = array();
		$this->_files = array();
		$this->_settings = array();
		$this->_active_modules = array();
		$this->_inactive_modules = array();
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
				$info = Saint::getRow("SELECT `name`,`title`,`layout`,`meta_keywords`,`meta_description`,`allow_robots`,`parent`,`weight`,`updated`,`created` FROM `st_pages` WHERE `id`='$id'");
				$this->_id = $id;
				$this->_name = $info[0];
				$this->_title = $info[1];
				$this->_layout = $info[2];
				if ($info[3] == "")
					$this->_meta_keywords = array();
				else
					$this->_meta_keywords = explode(',',$info[3]);
				$this->_meta_description = $info[4];
				$this->_allow_robots = $info[5];
				$this->_parent = $info[6];
				$this->_weight = $info[7];
				$this->_modified = $info[8];
				$this->_created = $info[9];
				$this->_categories = null;
				#$this->_model=Saint_Model_Page::getModel($this->_name);
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
		if ($name = Saint::sanitize($name,SAINT_REG_PAGE_NAME)) {
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
	 * Get the value of the given temporary setting name for the loaded page.
	 * @param string $setting Name of the requested setting.
	 * @return string Value of the requested setting.
	 */
	public function get($setting) {
		if (isset($this->_settings[$setting]))
			return $this->_settings[$setting];
		else {
			Saint::logWarning("No such setting $setting for page $this->_name.",__FILE__,__LINE__);
			return '';
		}
	}
	
	/**
	 * Change the value of the given temporary setting name for the loaded page.
	 * @param string $setting Name of the setting.
	 */
	public function set($setting,$value) {
		$this->_settings[$setting] = $value;
	}
	
	/**
	 * Get page files.
	 * @return array Files associated with loaded page.
	 */
	public function getFiles() {
		return $this->_files;
	}
		
	/**
	 * Set page files.
	 * @param array $file New files to be associated with loaded page.
	 */
	public function setFiles($files) {
		$this->_files = $files;
	}
	
	/**
	 * Get label of given name unique to this page.
	 * @param string $name Name of label to retrieve.
	 * @param string $default Default contents for label.
	 * @param array $options Options to apply to label.
	 * @return string Contents of label.
	 */
	public function getLabel($name, $default = '', $options = array()) {
		$name = "page/" . $this->_id . "/n/" . $name;
		return Saint::getLabel($name,$default,$options);
	}

	/**
	 * Include blocks of given name unique to this page.
	 * @param string $name Name of blocks to retrieve.
	 * @param array $options Options to apply to block inclusion.
	 */
	public function includeBlock($name, $options = array()) {
		$options['page_id'] = Saint::getCurrentPage()->getId();
		Saint_Model_Block::includeBlock($name,$options);
	}
	
	/**
	 * Get image label of given name unique to this page.
	 * @param string $name Name of the image label.
	 * @param string $options Options for displaying image.
	 */
	public function includeImage($name, $options = array()) {
		$name = "page/" . $this->_id . "/n/" . $name;
		Saint_Model_ImageLabel::includeImage($name, $options);
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
		if ($this->_temp_url != null) {
			return $this->_temp_url;
		} else {
			if ($this->_name == "home")
				$name = "";
			else
				$name = $this->_name;
			return SAINT_URL . "/" . $name;
		}
	}
	
	/**
	 * Temporarily set URL for running page.
	 * @param string $url URL to set for running page.
	 */
	public function setTempUrl($url) {
		$this->_temp_url = $url;
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
	public function getKeywords() {
		if ($this->_temp_meta_keywords == null)
			return $this->_meta_keywords;
		else
			return $this->_temp_meta_keywords;
	}
	
	/**
	 * Request page description.
	 * @return string Page description.
	 */
	public function getDescription() {
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
	 * Request page blocks.
	 * @return string[] Block names used in page.
	 */
	public function getBlocks() {
		return $this->_blocks;
	}

	/**
	 * Request page current block ID.
	 * Each time a block is called the page model stores the block ID for reference.
	 * @return int Current block ID running in page.
	 */
	public function getBlockId() {
		return $this->_bid;
	}
	
	/**
	 * Get AJAX reply data associated with running page.
	 * @return array Reply data to be sent to Saint client.
	 */
	public function getJsonData() {
		return $this->_json_data;
	}
	
	/**
	 * Set AJAX reply data associated with running page.
	 * @param array $data New reply data to be sent to client.
	 */
	public function setJsonData($data) {
		$this->_json_data = $data;
	}
	
	/**
	 * Get page weight (used for sorting).
	 * @return int Page weight.
	 */
	public function getWeight() {
		return $this->_weight;
	}
	
	/**
	 * Set page weight.
	 * @param int $weight New page weight.
	 */
	public function setWeight($weight) {
		$this->_weight = Saint::sanitize($weight,SAINT_REG_ID);
	}
	
	/**
	 * Get page parent (used for sub-pages).
	 * @return int Parent page ID.
	 */
	public function getParent() {
		return $this->_parent;
	}
	
	/**
	 * Set page parent.
	 * @param int $id New page parent.
	 * @return boolean True on success if parent page exists, false otherwise.
	 */
	public function setParent($id) {
		$np = new Saint_Model_Page();
		if ($id == 0 || ($id != $this->_id && $np->loadById($id))) {
			$this->_parent = $np->getId();
			return 1;
		} else {
			return 0;
		}
	}
	
	/**
	 * Get the block which is currently being edited on this page.
	 * @return Saint_Model_Block Block which is currently being edited.
	 */
	public function getEditBlock() {
		if ($this->_edit_block == null)
			$this->_edit_block = new Saint_Model_Block();
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
	 * Get last modified date for page.
	 * @return timestamp Last modified date.
	 */
	public function getLastModified() {
		return $this->_modified;
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
		if ($name = Saint::sanitize($name,SAINT_REG_PAGE_NAME)) {
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
			if ($layout = Saint::sanitize($layout,SAINT_REG_PAGE_NAME)) {
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
			if ($layout = Saint::sanitize($layout,SAINT_REG_BLOCK_NAME)) {
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
	 * Add error message for current page.
	 * @param string Error to add to current page.
	 */
	public function addError($error) {
		if (is_array($error))
			$this->_errors = array_merge($this->_errors,$errors);
		else
			$this->_errors[] = $error;
	}
	
	/**
	 * Get errors for current page.
	 * @return array Errors for current page.
	 */
	public function getErrors() {
		return $this->_errors;
	}
	
	/**
	 * Add the loaded block to the given category.
	 * @param string $category Name of category to which the block is to be added.
	 * @return boolean True for success, false for failure.
	 */
	public function addToCategory($category) {
		if ($this->_categories == null) {
			$this->loadCategories(); }
		$scategory = Saint::sanitize($category);
		if ($scategory) {
			// Add to categories array if not already present.
			if (!in_array($scategory,$this->_categories)) {
				$this->_categories[] = $scategory;
				// Notify system this is a change and must be added to the db when saved.
				if (!in_array($scategory,$this->_cats_to_add)) {
					$this->_cats_to_add[] = $scategory;
				}
			}
			// Notify the system not to delete this category (to nullify in case of delete->add->save)
			if (in_array($scategory,$this->_cats_to_delete)) {
				$index = array_search($scategory,$this->_cats_to_delete);
				unset($this->_cats_to_delete[$index]);
			}
			return 1;
		} else {
			Saint::logError("Invalid category name: '$category'.",__FILE__,__LINE__);
			return 0;
		}
	}
	
	/**
	 * Remove the loaded block from the given category.
	 * @param string $category Name of category from which the block is to be removed.
	 * @return boolean True for success, false for failure.
	 */
	public function removeFromCategory($category) {
		if ($this->_categories == null) {
			$this->loadCategories(); }
		$scategory = Saint::sanitize($category);
		if ($scategory) {
			// Remove from categories array if present
			if (in_array($scategory,$this->_categories)) {
				$index = array_search($scategory,$this->_categories);
				unset($this->_categories[$index]);
				// Notify system this is a change and must be removed from the db when saved.
				if (!in_array($scategory,$this->_cats_to_delete)) {
					$this->_cats_to_delete[] = $scategory;
				}
			}
			// Notify the system not to add this category (to nullify in case of add->delete->save)
			if (in_array($scategory,$this->_cats_to_add)) {
				$index = array_search($scategory,$this->_cats_to_add);
				unset($this->_cats_to_add[$index]);
			}
			return 1;
			$this->_cats_to_delete[] = $scategory;
		} else {
			Saint::logError("Invalid category name: '$category'.",__FILE__,__LINE__);
			return 0;
		}
	}
	
	/**
	 * Set the loaded page's categories en masse.
	 * @param string[] $newcats Array of category names for the page.
	 */
	public function setCategories($newcats) {
		if (!is_array($newcats))
			$newcats = explode(',',$newcats);
		foreach (Saint::getCategories() as $cat) {
			if (in_array($cat,$newcats)) {
				$this->addToCategory($cat);
			} else {
				$this->removeFromCategory($cat);
			}
		}
	}
	
	/**
	 * Get the loaded page's categories.
	 * @return string[] Array of category names.
	 */
	public function getCategories() {
		if ($this->_categories == null) {
			$this->loadCategories(); }
		return $this->_categories;
	}
	
	/**
	 * Check if loaded page is in at least one of the given categories.
	 * @param string[] $category Array of category names to check. Also accepts scalar category name.
	 * @return boolean True if page is in at least one of the given categories, false otherwise.
	 */
	public function inCategory($category) {
		if ($this->_categories == null) {
			$this->loadCategories(); }
		if (!is_array($category))
			$category = array($category);
		foreach ($category as $cname) {
			if (in_array($cname,$this->_categories)) {
				return 1;
			}
		}
		return 0;
}
	
	/**
	 * Set keywords for loaded page.
	 * @param string[] $tags New tags for loaded page.
	 */
	public function setKeywords($tags) {
		if (!is_array($tags))
			$tags = explode(',',$tags);
		
		$stags = array();
		foreach ($tags as $key=>$tag)
			$stags[$key] = Saint::sanitize(trim($tag));
		
		$this->_meta_keywords = $stags;
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
				"`allow_robots`='$this->_allow_robots',".
				"`parent`='$this->_parent',".
				"`weight`='$this->_weight' ".
				"WHERE `id`='$this->_id'";
				Saint::query($query);
				foreach ($this->_cats_to_add as $cat) {
					$this->dbAddToCategory($cat); }
				foreach ($this->_cats_to_delete as $cat) {
					$this->dbRemoveFromCategory($cat); }
				if ($log)
					Saint::logEvent("Saved details for page '$this->_name'.");
				return 1;
			} catch (Exception $e) {
				Saint::logError("Could not save Page. ".$e->getMessage(),__FILE__,__LINE__);
				return 0;
			}
		} else {
			try {
				$query = "INSERT INTO `st_pages` (`name`,`title`,`layout`,`meta_keywords`,`meta_description`,`allow_robots`,`created`,`parent`,`weight`) ".
					" VALUES ('$this->_name','$this->_title','$this->_layout','".implode(',',$this->_meta_keywords).
					"','$this->_meta_description','$this->_allow_robots',NOW(),'$this->_parent','$this->_weight')";
				Saint::query($query);
				$this->_id = Saint::getLastInsertId();
				foreach ($this->_cats_to_add as $cat) {
					$this->dbAddToCategory($cat); }
				foreach ($this->_cats_to_delete as $cat) {
					$this->dbRemoveFromCategory($cat); }
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
	 * Get index of all children of the loaded page.
	 * @return array Index of all descendants of current page.
	 */
	public function getIndex() {
		if ($this->_id == 0) {
			return array();
		} else {
			$index = array();
			$sub_pages = Saint::getPages(array(
				'layout' => array(
					'logical_operator' => 'AND',
					'comparison_operator' => 'NOT LIKE',
					'match_all' => 'system/%',
				),
				'allow_robots' => array(
					'logical_operator' => 'AND',
					'comparison_operator' => '!=',
					'match_all' => 0,
				),
				'parent' => $this->_id,
			));
			foreach ($sub_pages as $sp) {
				$index[] = array($sp->getUrl(),$sp->getTitle(),$sp->getLastModified(),$sp->getIndex());
			}
			return $index;
		}
	}
	
	/**
	 * Render the loaded page.
	 * @param boolean Default true to reindex block usage, false to retain cached data.
	 * @return boolean True on success, false otherwise.
	 */
	public function render($options = array()) {
		if (isset($this->_templayout))
			$lname = $this->_templayout;
		else
			$lname = $this->_layout;
		if ($layout = Saint::getLayout($lname)) {
			if ($result = $layout->render($this, $options)) {
				return $result;
			} else {
				Saint::logError("Unable to render page $this->_name.",__FILE__,__LINE__);
				return 0;
			}
		} else {
			Saint::logError("Layout not found: $this->_layout",__FILE__,__LINE__);
			return 0;
		}
	}
	
	
	/**
	 * Add loaded page to given category.
	 * @param string $category Name of category to which to add the page.
	 * @return boolean True on success, false otherwise.
	 */
	private function dbAddToCategory($category) {
		$scategory = Saint::sanitize($category);
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
					Saint::query("INSERT INTO `st_page_categories` (`catid`,`pageid`) VALUES ('$id','$this->_id')");
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
	private function dbRemoveFromCategory($category) {
		$scategory = Saint::sanitize($category);
		if ($scategory) {
			$id = Saint_Model_Category::getId($scategory);
			if (!$id) {
				# No id... it can't be part of a category that doesn't exist, so our job is done.
				return 1;
			} else {
				try {
					Saint::query("DELETE FROM `st_page_categories` WHERE `catid`='$id' AND `pageid`='$this->_id'");
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
	 * Load categories for the current page; called on demand for increased performance.
	 */
	private function loadCategories() {
		$this->_categories = array();
		try {
			$getcats = Saint::getAll("SELECT `c`.`id`,`c`.`name` FROM `st_categories` as `c`,`st_page_categories` as `p` WHERE `p`.`pageid`='$this->_id' AND `p`.`catid`=`c`.`id`");
			foreach ($getcats as $getcat) {
				$this->_categories[$getcat[0]] = $getcat[1];
			}
		} catch (Exception $e) {
			if ($e->getCode()) {
				Saint::logError("Unable to load page categories: ".$e->getMessage(),__FILE__,__LINE__);
			}
		}
	}
}
