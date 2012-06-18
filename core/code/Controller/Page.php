<?php
/**
 * Central input controller for the Saint framework.
 * @author Preston St. Pierre
 * @package Saint
 */

class Saint_Controller_Page {
	protected $_page;
	protected $_discounter;
	
	/**
	 * Create a model for the current page and apply given arguments.
	 * @param string $name Name of page to load.
	 * @param array $args Arguments to pass.
	 * @return boolean True on success, false otherwise.
	 */
	public function __construct($name,$args = array()) {
		$this->_discounter = null;
		$page_model = Saint_Model_Page::getModel($name);
		$this->_page = new $page_model();
		$this->_page->setArgs($args);
		return $this->_page->loadByName($name);
	}
	
	/**
	 * Get the model of the currently running page.
	 * @return Saint_Model_Page Page being run.
	 */
	public function getCurrentPage() {
		return $this->_page;
	}

	/**
	 * Change the active page.
	 * @param Saint_Model_Page $page New page to hold running information.
	 * @return boolean True on success, false otherwise.
	 */
	public function setCurrentPage($page) {
		if (is_a($page,'Saint_Model_Page') || is_subclass_of('Saint_Model_Page')) {
			$this->_page = $page;
			return 1;
		} else
			return 0;
	}
	
	/**
	 * Get a model to apply active discounts; cache it after first call.
	 * @return Saint_Model_Discount Model with active discounts loaded from the database.
	 */
	public function getDiscounter() {
		if ($this->_discounter == null) {
			$this->_discounter = new Saint_Model_Discount();
		}
		return $this->_discounter;
	}
	
	/**
	 * Process user input, scan managed directories for changes, then start the page render process.
	 */
	public function process() {
		if(!SAINT_CACHING) {
			Saint_Model_Block::processSettings();
			if (Saint::getCurrentUser()->hasPermissionTo("manage-files")) {
				Saint_Model_FileManager::processFiles();
			} 
		}
		
		$args = $this->_page->getArgs();
		
		/*
		 * Page controls
		 */
		
		if(isset($_POST['saint-add-page-name']) && isset($_POST['saint-add-page-layout']) && isset($_POST['saint-add-page-title'])) {
			if (isset($_POST['saint-add-page-keywords']))
				$keywords = $_POST['saint-add-page-keywords'];
			else
				$keywords = '';
			
			if (isset($_POST['saint-add-page-description']))
				$description = $_POST['saint-add-page-description'];
			else
				$description = '';
			
			if (isset($_POST['saint-add-page-categories']))
				$cats = $_POST['saint-add-page-categories'];
			else
				$cats = array();
			$this->_page->setTempLayout("system/json");
			if (Saint::addPage($_POST['saint-add-page-name'],$_POST['saint-add-page-layout'],$_POST['saint-add-page-title'],
				$keywords,$description,$cats))
				$success = true;
			else
				$success = false;
			
			$pages = Saint::getAllPages();
			$sp = array();
			foreach ($pages as $page) {
				$sp[] = array($page->getName(),$page->getTitle());
			}
			
			$this->_page->jsondata = array(
				'success' => $success,
				'pages' => $sp,
				'actionlog' => Saint::getActionLog(),
			);
		}
				
		if(isset($_POST['saint-edit-page-id']) && isset($_POST['saint-edit-page-title']) 
			&& isset($_POST['saint-edit-page-name'])) {
			$this->_page->setTempLayout("system/json");
			
			$spage = new Saint_Model_Page();
			
			if (isset($_POST['saint-edit-page-categories']))
				$cats = $_POST['saint-edit-page-categories'];
			else
				$cats = array();
			
			if (isset($_POST['saint-edit-page-keywords']))
				$keywords = $_POST['saint-edit-page-keywords'];
			else
				$keywords = '';
			
			if (isset($_POST['saint-edit-page-description']))
				$description = $_POST['saint-edit-page-description'];
			else
				$description = '';
			
			if ($spage->loadById($_POST['saint-edit-page-id'])) {
				$spage->setName($_POST['saint-edit-page-name']);
				$spage->setTitle($_POST['saint-edit-page-title']);
				if (isset($_POST['saint-edit-page-layout'])) {
					$spage->setLayout($_POST['saint-edit-page-layout']); }
				$spage->setKeywords($keywords);
				$spage->setDescription($description);
				$spage->setCategories($cats);
				if ($spage->save())
					$success = true;
				else
					$success = false;
			} else {
				$success = false;
			}
			
			$this->_page->jsondata = array(
				'success' => $success,
				'actionlog' => Saint::getActionLog(),
			);
		}

		/*
		 * Setting controls
		 */
	
		if (isset($_POST['saint-site-title']) && $_POST['saint-site-title'] != "") {
			$this->_page->setTempLayout("system/json");
			$success = true;
			$errors = array();
			
			if (!Saint::setSiteTitle($_POST['saint-site-title'])) {
				$success = false;
				$errors[] = "Invalid title.";
			}
			
			if (isset($_POST['saint-site-description']) && !Saint::setSiteDescription($_POST['saint-site-description'])) {
				$success = false;
				$errors[] = "Invalid description.";
			}
			
			if (isset($_POST['saint-site-keywords']) && !Saint::setSiteKeywords($_POST['saint-site-keywords'])) {
				$success = false;
				$errors[] = "Invalid keywords.";
			}
			
			if (isset($_POST['saint-shop-uri'])) {
				$prev_page = new Saint_Model_Page();
				$prev_page->loadById(Saint::getShopPageId());
				Saint::logError("Shop Start");
				if ($prev_page->getName() != $_POST['saint-shop-uri']) {
					Saint::logError("Working");
					if ($_POST['saint-shop-uri'] == "") {
						Saint::logError("Deleting");
						$prev_page->delete();
						Saint::setShopPageId(0);
						Saint::logEvent("Disabled Saint shop.");
					} elseif (!Saint_Model_Page::nameAvailable($_POST['saint-shop-uri'])) {
						Saint::logError("Name taken.");
						$success = false;
						$errors[] = "Name is already in use.";
						Saint::logEvent("The page name you requested to use for the shop is already taken. Either rename or delete the page then try again.");
					} elseif ($prev_page->getId()) {
						Saint::logError("Altering existing shop page.");
						if ($prev_page->setName($_POST['saint-shop-uri'])) {
							$prev_page->save();
						} else {
							$success = false;
							$errors[] = "Unable to change the name of the shop page.";
							Saint::logEvent("Unable to change the name of the shop page. See the error log for more details.");
						}
					} else {
						Saint::logError("Adding new shop page.");
						$new_page = new Saint_Model_Page();
						$new_page->setName($_POST['saint-shop-uri']);
						$new_page->setLayout("shop/shop");
						$new_page->setTitle(Saint::getSiteTitle());
						$new_page->setKeywords(Saint::getSiteKeywords());
						$new_page->setDescription(Saint::getSiteDescription());
						$new_page->setModel("Saint_Model_Shop");
						$new_page->save(true);
						Saint::setShopPageId($new_page->getId());
						Saint::logEvent("Added shop page at URI ".$prev_page->getName());
					}
				}
			}
			
			if (isset($_POST['saint-blog-uri'])) {
				$prev_page = new Saint_Model_Page();
				$prev_page->loadById(Saint::getBlogPageId());
				Saint::logError("Blog Start");
				if ($prev_page->getName() != $_POST['saint-blog-uri']) {
					Saint::logError("Working");
					if ($_POST['saint-blog-uri'] == "") {
						Saint::logError("Deleting");
						$prev_page->delete();
						Saint::setBlogPageId(0);
						Saint::logEvent("Disabled Saint blog.");
					} elseif (!Saint_Model_Page::nameAvailable($_POST['saint-blog-uri'])) {
						Saint::logError("Name taken.");
						$success = false;
						$errors[] = "Name is already in use.";
						Saint::logEvent("The page name you requested to use for the blog is already taken. Either rename or delete the page then try again.");
					} elseif ($prev_page->getId()) {
						Saint::logError("Altering existing blog page.");
						if ($prev_page->setName($_POST['saint-blog-uri'])) {
							$prev_page->save();
						} else {
							$success = false;
							$errors[] = "Unable to change the name of the blog page.";
							Saint::logEvent("Unable to change the name of the blog page. See the error log for more details.");
						}
					} else {
						Saint::logError("Adding new blog page.");
						$new_page = new Saint_Model_Page();
						$new_page->setName($_POST['saint-blog-uri']);
						$new_page->setLayout("blog/index");
						$new_page->setTitle(Saint::getSiteTitle());
						$new_page->setKeywords(Saint::getSiteKeywords());
						$new_page->setDescription(Saint::getSiteDescription());
						$new_page->setModel("Saint_Model_Blog");
						$new_page->save(true);
						Saint::setBlogPageId($new_page->getId());
						Saint::logEvent("Added blog page at URI ".$prev_page->getName());
					}
				}
			}
			
			$this->_page->jsondata = array(
				"success" => $success,
				"actionlog" => Saint::getActionLog(),
			);
		}
		
		/*
		 * Search controls
		 */
		
		if (isset($_POST['saint-search-phrase'])) {
			$this->_page->searchphrase = $_POST['saint-search-phrase'];
			$this->_page->searchresults = Saint::search($_POST['saint-search-phrase']);
		}
		
		/*
		 * Block controls
		 */
		
		if (isset($_POST['block']) && $_POST['block'] != "") {
			Saint_Controller_Block::loadBlock($_POST['block']);
		}
		
		if (isset($args['edit']) && $args['edit'] != '' && isset($_POST['saint-block-setting-saintname']) && $_POST['saint-block-setting-saintname'] != '') {
			Saint_Controller_Block::editBlock($args['edit'],$_POST['saint-block-setting-saintname']);
		}
		
		/*
		 * Label controls
		 */
		
		if (isset($_POST['label-name']) && isset($_POST['label-value'])) {
			Saint_Controller_Label::editLabel($_POST['label-name'],$_POST['label-value']);
		}
		
		if (isset($args['getlabel']) && $args['getlabel'] != "") {
			if (Saint::getCurrentUser()->hasPermissionTo("edit-label")) {
				$this->_page->setTempLayout("system/json");
				if (isset($args['revision']) && $args['revision'] != "") {
					$revision = $args['revision'];
				} else {
					$revision = 0;
				}
				$this->_page->jsondata = array(
					'success' => true,
					'revision' => $revision,
					'label' => Saint::getLabel(
						Saint::convertNameFromWeb(preg_replace('/^saint_/','',$args['getlabel'])),
						'',
						false,
						null,
						false,
						$revision
					)
				);
			}
		}
		
		if (isset($args['getlabelnumrevs']) && $args['getlabelnumrevs'] != "") {
			if (Saint::getCurrentUser()->hasPermissionTo("edit-label")) {
				$this->_page->setTempLayout("system/json");
				$label = new Saint_Model_Label();
				$label->loadByName(Saint::convertNameFromWeb(preg_replace('/^saint_/','',$args['getlabelnumrevs'])));
				$this->_page->jsondata = array(
					'success' => true,
					'revisions' => $label->getNumRevisions(),
				);
			}
		}
		
		/*
		 * User controls
		 */
		
		if (isset($_POST['saint-edit-user-id'])) {
			Saint_Controller_User::saveUser($_POST['saint-edit-user-id']);
		}
		
		if (isset($_POST['username']) && isset($_POST['password'])) {
			Saint_Controller_User::login($_POST['username'],$_POST['password']);
		}
		
		if ($this->_page->getName() == "user" && isset($args['view'])) {
			switch ($args['view']) {
				case 'edit':
					if (isset($args['id'])) {
						$this->_page->usertoedit = new Saint_Model_User();
						$this->_page->usertoedit->loadById($args['id']);
					}
					$this->_page->setTempLayout("user/edit");
					break;
			}
		}
		
		/*
		 * Category controls
		 */
		
		if (isset($_POST['saint-add-category']) && (!isset($_POST['saint-set-category-id']) || $_POST['saint-set-category-id'] == "0")) {
			Saint_Controller_Category::addCategory($_POST['saint-add-category']);
		}
		
		if (isset($_POST['saint-delete-category']) && $_POST['saint-delete-category']) {
			Saint_Controller_Category::removeCategory($_POST['saint-set-category-id']);
		}
		
		if (isset($_POST['saint-set-category-id']) && isset($_POST['saint-add-category']) && $_POST['saint-set-category-id'] != "0"
			&& !$_POST['saint-delete-category']) {
			Saint_Controller_Category::setCategory($_POST['saint-set-category-id'],$_POST['saint-add-category']);
		}
		
		/*
		 * WYSIWYG controls
		 */
		
		if (isset($_POST['saint-wysiwyg-name']) && isset($_POST['saint-wysiwyg-content'])) {
			Saint_Controller_Wysiwyg::setContent(
				Saint_Model_Block::convertNameFromWeb(preg_replace('/^wysiwyg_/','',$_POST['saint-wysiwyg-name']))
				,$_POST['saint-wysiwyg-content']);
		}
		
		/*
		 * Contact form controls
		 */
		
		if (isset($_POST['saint-contact-name']) && isset($_POST['saint-contact-email']) && isset($_POST['saint-contact-message'])) {
			Saint_Controller_Contact::emailAdmin(array(
				'Name' => $_POST['saint-contact-name'],
				'E-Mail' => $_POST['saint-contact-email'],
				'Message' => $_POST['saint-contact-message'],
			));
		}
		
		/*
		 * File manager controls
		 */
		
		if (isset($args['view']) && $args['view'] == 'file-list') {
			if (Saint::getCurrentUser()->hasPermissionTo("manage-files")) {
				$this->_page->setTempLayout("file-manager/list");
			}
		}
		
		if (isset($_POST['saint-file-mode']) && isset($_POST['saint-file-id']) && isset($_POST['saint-file-title'])
			&& isset($_POST['saint-file-keywords']) && isset($_POST['saint-file-description'])) {
			if (isset($_POST['saint-file-categories']))
				$categories = $_POST['saint-file-categories'];
			else
				$categories = array();
			if ($_POST['saint-file-mode'] == "search") {
				Saint_Controller_FileManager::filterFileDetails($_POST['saint-file-id'],
					$_POST['saint-file-title'],$_POST['saint-file-keywords'],
					$_POST['saint-file-description'],$categories);
			} else {
				Saint_Controller_FileManager::saveFileDetails($_POST['saint-file-id'],
					$_POST['saint-file-title'],$_POST['saint-file-keywords'],
					$_POST['saint-file-description'],$categories);
				if (isset($_POST['saint-file-label']) && $_POST['saint-file-label']) {
					if (Saint_Model_FileLabel::setFile(
						Saint::convertNameFromWeb($_POST['saint-file-label']),
						array('fid'=>$_POST['saint-file-id']))) {
						$success = true;
					} else {
						$success = false;
					}
					
					$this->_page->setTempLayout("system/json");
					$this->_page->jsondata = array(
						"success" => $success,
						"sfl" => $_POST['saint-file-label'],
						"sfid" => $_POST['saint-file-id'],
					);
					if (isset($_POST['saint-file-label-width']) && $_POST['saint-file-label-width'] != "0" 
						&& isset($_POST['saint-file-label-height']) && $_POST['saint-file-label-height'] != "0" ) {
						$arguments = array(
							"max-width" => $_POST['saint-file-label-width'],
							"max-height" => $_POST['saint-file-label-height'],
						);
						$img = new Saint_Model_Image($_POST['saint-file-id']);
						$this->_page->jsondata['url'] = $img->getResizedUrl($arguments);
					}
				}
			}
		}
		
		/*
		 * Shop controls
		 */
			
		if (isset($args['addtocart']) && $args['addtocart'] != '') {
			Saint_Controller_ShoppingCart::addToCart($args['addtocart']);
		}
			
		if (isset($args['remfromcart']) && $args['remfromcart'] != '') {
			Saint_Controller_ShoppingCart::removeFromCart($args['remfromcart']);
		}
		
		if ($this->_page->getName() == "shop" && isset($args['view'])) {
			switch ($args['view']) {
				case 'cart':
					$this->_page->setTempLayout("shop/cart");
					break;
				case 'checkout':
					$this->_page->setTempLayout("shop/checkout");
					break;
				case 'transactions':
					if (Saint::getCurrentUser()->hasPermissionTo("view-transactions")) {
						$this->_page->setTempLayout("shop/admin/transactions");
					} else {
						Saint::logError("A user without permission has attempted to view the transactions page from IP $_SERVER[REMOTE_ADDR]."); }
					break;
				case 'discounts':
					if (Saint::getCurrentUser()->hasPermissionTo("view-discounts")) {
						$this->_page->setTempLayout("shop/admin/discounts");
					} else {
						Saint::logError("A user without permission has attempted to view the discounts page from IP $_SERVER[REMOTE_ADDR]."); }
					break;
				case 'ipn':
					Saint_Controller_Transaction::verifyIpn();
					break;
				case 'download':
					if (isset($args['id'])) {
						if (isset($args['linkid'])) {
							$linkid = $args['linkid'];
						} else {
							$linkid = null; }
						Saint_Controller_Shop::download($args['id'],$linkid); }
					break;
			}
		}
		
		/*
		 * Parse arguments for potential commands
		 * @todo Split this section into functional categories.
		 */
		foreach ($this->_page->getArgs() as $key=>$val) {
			switch ($key) {
				case "delpage":
					$this->_page->setTempLayout("system/json");
					if (Saint::getCurrentUser()->hasPermissionTo("delete-page")) {
						$success = Saint::deletePage($val);
					} else {
						$success = false;
					}
					$this->_page->jsondata = array(
						'success' => $success,
						'actionlog' => Saint::getActionLog(),
					);
					break;
				case "action":
					if ($val == "logout") {
						Saint_Model_User::logout();
						header("Location: " .SAINT_BASE_URL);
					}
					break;
			}
		}
		
		$this->_page->process();
		$this->_page->render();
	}
}
