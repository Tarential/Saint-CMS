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
			Saint_Model_Layout::updateLayouts();
			Saint_Model_Block::updateBlocks();
			if (Saint::getCurrentUser()->hasPermissionTo("manage-files")) {
				Saint_Model_FileManager::processFiles();
			} 
		}
		
		$args = $this->_page->getArgs();
		
		if (isset($_POST['saint_client_nonce'])) {
			if ($_POST['saint_client_nonce'] == Saint::getCurrentUser()->getNonce()) {
				
				# Page controls
				
				if (isset($_POST['saint-add-page-name']) && isset($_POST['saint-add-page-layout']) && isset($_POST['saint-add-page-title'])) {
					$this->_page->setTempLayout("system/json");
					if (Saint::getCurrentUser()->hasPermissionTo("add-page")) {
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
						$new_page_options = array(
							'layout' => $_POST['saint-add-page-layout'],
							'title' => $_POST['saint-add-page-title'],
							'keywords' => $keywords,
							'description' => $description,
							'categories' => $cats,
							'parent' => $_POST['saint-add-page-parent'],
						);
						
						if (Saint::addPage($_POST['saint-add-page-name'],$new_page_options))
							$success = true;
						else
							$success = false;
						
						$pages = Saint::getPages();
						$sp = array();
						foreach ($pages as $page) {
							$sp[] = array($page->getName(),$page->getTitle());
						}
					} else {
						$success = false;
						Saint::logError("User '".Saint::getCurrentUsername()."' attempted to add a page from IP $_SERVER[REMOTE_ADDR] but was denied access.",__FILE__,__LINE__);
					}
					
					$this->_page->setJsonData(array(
						'success' => $success,
						'pages' => $sp,
						'actionlog' => Saint::getActionLog(),
					));
				}
						
				if(isset($_POST['saint-edit-page-id']) && isset($_POST['saint-edit-page-title']) 
					&& isset($_POST['saint-edit-page-name'])) {
					
					$this->_page->setTempLayout("system/json");
					if (Saint::getCurrentUser()->hasPermissionTo("edit-page")) {
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
							if (isset($_POST['saint-edit-page-parent'])) {
								$spage->setParent($_POST['saint-edit-page-parent']); }
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
					} else {
						$success = false;
						Saint::logError("User '".Saint::getCurrentUsername()."' attempted to edit page '".$_POST['saint-edit-page-name']."' from IP $_SERVER[REMOTE_ADDR] but was denied access.",__FILE__,__LINE__);
					}
					
					$this->_page->setJsonData(array(
						'success' => $success,
						'actionlog' => Saint::getActionLog(),
					));
				}
				
				if (isset($args['delpage'])) {
					$this->_page->setTempLayout("system/json");
					if (Saint::getCurrentUser()->hasPermissionTo("delete-page")) {
						$success = Saint::deletePage($args['delpage']);
					} else {
						$success = false;
						Saint::logError("User '".Saint::getCurrentUsername()."' attempted to delete page id '".$args['delpage']."' from IP $_SERVER[REMOTE_ADDR] but was denied access.",__FILE__,__LINE__);
					}
					$this->_page->setJsonData(array(
						'success' => $success,
						'actionlog' => Saint::getActionLog(),
					));
				}
		
				# Setting controls
			
				if (isset($_POST['saint-site-title']) && $_POST['saint-site-title'] != "") {
					$this->_page->setTempLayout("system/json");
					
					if (Saint::getCurrentUser()->hasPermissionTo("edit-site")) {
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
					} else {
						$success = false;
						Saint::logError("User '".Saint::getCurrentUsername()."' attempted to edit site settings from IP $_SERVER[REMOTE_ADDR] but was denied access.",__FILE__,__LINE__);
					}
					
					$this->_page->setJsonData(array(
						"success" => $success,
						"actionlog" => Saint::getActionLog(),
					));
				}

				# Block controls
				
				if (isset($_POST['block']) && $_POST['block'] != "") {
					Saint_Controller_Block::loadBlock($_POST['block']);
				}
				
				if (isset($args['edit']) && $args['edit'] != '' && isset($_POST['saint-block-setting-saintname']) && $_POST['saint-block-setting-saintname'] != '') {
					Saint_Controller_Block::editBlock($args['edit'],$_POST['saint-block-setting-saintname']);
				}
				
				# Label controls
				
				if (isset($_POST['label-name']) && isset($_POST['label-value'])) {
					Saint_Controller_Label::editLabel($_POST['label-name'],$_POST['label-value']);
				}
				
				if (isset($args['getlabel']) && $args['getlabel'] != "" && Saint::getCurrentUser()->hasPermissionTo("edit-label")) {
					$this->_page->setTempLayout("system/json");
					if (isset($args['revision']) && $args['revision'] != "") {
						$revision = $args['revision'];
					} else {
						$revision = 0;
					}
					$labelargs = array(
						'rev'
					);
					$this->_page->setJsonData(array(
						'success' => true,
						'revision' => $revision,
						'label' => Saint::getLabel(
							Saint::convertNameFromWeb(preg_replace('/^saint_/','',$args['getlabel'])),
							'',
							array('revision'=>$revision,'container'=>false)
						),
					));
				}
				
				if (isset($args['getlabelnumrevs']) && $args['getlabelnumrevs'] != "" && Saint::getCurrentUser()->hasPermissionTo("edit-label")) {
					$this->_page->setTempLayout("system/json");
					$label = new Saint_Model_Label();
					$label->loadByName(Saint::convertNameFromWeb(preg_replace('/^saint_/','',$args['getlabelnumrevs'])));
					$this->_page->setJsonData(array(
						'success' => true,
						'revisions' => $label->getNumRevisions(),
					));
				}

				# User controls
				
				if (isset($_POST['saint-edit-user-id'])) {
					if (Saint::getCurrentUser()->hasPermissionTo("edit-user") || 
						(Saint::getCurrentUser()->getId() == $id && Saint::getCurrentUser()->hasPermissionTo("edit-self"))) {
						
						Saint_Controller_User::saveUser($_POST['saint-edit-user-id']);
					} else {
						Saint::logError("User ".Saint::getCurrentUsername()." attempted to edit user with id ".$_POST['saint-edit-user-id'].
							" from IP $_SERVER[REMOTE_ADDR] but was denied access.");
						$page->setTempLayout("system/error");
						$page->addError("You do not have access to edit data which belongs to other users. This attempt has been logged.");
					}
				}
				
				if ($this->_page->getName() == "user" && isset($args['view'])) {
					switch ($args['view']) {
						case 'edit':
							if (isset($args['id'])) {
								$edit_user = new Saint_Model_User();
								$edit_user->loadById($args['id']);
								$this->_page->set("user-to-edit",$edit_user);
							}
							$this->_page->setTempLayout("system/user-edit");
							break;
					}
				}
				
				if ($this->_page->getName() == "system") {
					$validation_targets = array("username","add-page-name","edit-page-name","category");
					
					foreach ($validation_targets as $vt) {
						if (isset($args['check-'.$vt])) {
							$this->_page->setTempLayout("system/json");
							$jsondata = array('success'=>true);
							
							switch ($vt) {
								case "username":
									$available = Saint_Model_User::nameAvailable($args['check-'.$vt]);
									if ($available) {
										$jsondata['message'] = "That username is available.";
									} else {
										$jsondata['message'] = "That username is unavailable. Please choose another.";
									}
									break;
								case "add-page-name":
								case "edit-page-name":
									$available = Saint_Model_Page::nameAvailable($args['check-'.$vt]);
									if ($available) {
										$jsondata['message'] = "That page name is available.";
									} else {
										$jsondata['message'] = "That page name is unavailable. Please choose another.";
									}
									break;
								case "category":
									$available = Saint_Model_Category::nameAvailable($args['check-'.$vt]);
									if ($available) {
										$jsondata['message'] = "That category name is available.";
									} else {
										$jsondata['message'] = "That category name is unavailable. Please choose another.";
									}
									break;
							}
							
							$jsondata['available'] = $available;
							$jsondata['setting'] = $vt;
							$this->_page->setJsonData($jsondata);
						}
					}
				}
				
				# Category controls
				
				if (isset($_POST['saint-add-category']) && (!isset($_POST['saint-set-category-id']) || $_POST['saint-set-category-id'] == "0")) {	
					if (Saint::getCurrentUser()->hasPermissionTo("add-category")) {
						Saint_Controller_Category::addCategory($_POST['saint-add-category']);
					} else {
						Saint::logError("User ".Saint::getCurrentUsername()." attempted to add a new category ".
							"'$category' from IP $_SERVER[REMOTE_ADDR] but was denied access.",__FILE__,__LINE__);
						$page->setTempLayout("system/error");
						$page->addError("You do not have permission to add new categories. This attempt has been logged.");
					}
				}
				
				if (isset($_POST['saint-delete-category']) && $_POST['saint-delete-category']) {
					if (Saint::getCurrentUser()->hasPermissionTo("delete-category")) {
						Saint_Controller_Category::removeCategory($_POST['saint-set-category-id']);
					} else {
						Saint::logError("User ".Saint::getCurrentUsername()." attempted to remove category with ID ".
							"'$catid' from IP $_SERVER[REMOTE_ADDR] but was denied access.",__FILE__,__LINE__);
						$page->setTempLayout("system/error");
						$page->addError("You do not have permission to delete categories. This attempt has been logged.");
					}
				}
				
				if (isset($_POST['saint-set-category-id']) && isset($_POST['saint-add-category']) && $_POST['saint-set-category-id'] != "0"
					&& !$_POST['saint-delete-category']) {
					if (Saint::getCurrentUser()->hasPermissionTo("edit-category")) {
						Saint_Controller_Category::setCategory($_POST['saint-set-category-id'],$_POST['saint-add-category']);
					} else {
						Saint::logError("User ".Saint::getCurrentUsername()." attempted to change name of category with ID '$id' to ".
							"'$category' from IP $_SERVER[REMOTE_ADDR] but was denied access.",__FILE__,__LINE__);
						$page->setTempLayout("system/error");
						$page->addError("You do not have permission to edit categories. This attempt has been logged.");
					}
				}

				# Contact form controls
				
				if (isset($_POST['saint-contact-name']) && isset($_POST['saint-contact-email']) && isset($_POST['saint-contact-message'])) {
					Saint_Controller_Contact::emailAdmin(array(
						'Name' => $_POST['saint-contact-name'],
						'E-Mail' => $_POST['saint-contact-email'],
						'Message' => $_POST['saint-contact-message'],
					));
				}
				
			} else {
				Saint::logError("There has been a possible hacking attempt on account '$this->_username'; client sent an invalid nonce.");
			}
		}
		
		/*
		 * The following section includes actions which don't require client authentication.
		 */
		
		if ($this->_page->getName() == "system") {
			
			# Clear notices
			
			if (isset($args['action'])) {
				Saint::clearNotices();
			}
			
			# Logout
			
			if (isset($args['action']) && $args['action'] == "logout") {
				Saint_Model_User::logout();
				header("Location: " . SAINT_URL);
			}
		}
		
		# Shop controls
		
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
		
		# Search controls
		
		if (isset($_POST['saint-search-phrase'])) {
			$this->_page->set("search-phrase",Saint::sanitize($_POST['saint-search-phrase']));
			$results = Saint::search($_POST['saint-search-phrase']);
			$this->_page->set("search-results",$results);
		}
		
	
		# User login
		
		if (isset($_POST['username']) && isset($_POST['password'])) {
			Saint_Controller_User::login($_POST['username'],$_POST['password']);
		}
		
		$this->_page->process();
		$this->_page->render();
	}
}
