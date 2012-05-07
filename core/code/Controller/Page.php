<?php
/**
 * This is the main router for our controllers.
 * See the manual for info on creating a custom controller.
 * @author Preston St. Pierre
 */

class Saint_Controller_Page {
	protected $_page;
	protected $_discounter;
	
	public function __construct($name,$args = array()) {
		$this->_discounter = null;
		$this->_page = new Saint_Model_Page();
		$this->_page->setArgs($args);
		if (!$this->_page->loadByName($name))
			throw new Exception("Can't find page named $name.");
	}
	
	public function getCurrentPage() {
		return $this->_page;
	}

	public function setCurrentPage($page) {
		if (is_a($page,'Saint_Model_Page')) {
			$this->_page = $page;
			return 1;
		} else
			return 0;
	}
	
	public function getDiscounter() {
		if ($this->_discounter == null) {
			$this->_discounter = new Saint_Model_Discount();
		}
		return $this->_discounter;
	}
	
	public function process() {
		if(!SAINT_CACHING) {
			Saint_Model_Block::processSettings();
			if (Saint::getCurrentUser()->hasPermissionTo("manage-files")) {
				Saint_Model_FileManager::processFiles();
			} 
		}
		
		$args = $this->_page->getArgs();
		
		/**
		 * Page controls
		 */
		
		if(isset($_POST['saint_add_page_name']) && isset($_POST['saint_add_page_layout']) && isset($_POST['saint_add_page_title'])) {
			$this->_page->setTempLayout("system/json");
			
			if (Saint::addPage($_POST['saint_add_page_name'],$_POST['saint_add_page_layout'],$_POST['saint_add_page_title']))
				$success = true;
			else
				$success = false;
			
			$this->_page->jsondata = array(
				'success' => $success,
				'actionlog' => Saint::getActionLog(),
			);
		}
				
		if(isset($_POST['saint_edit_page_id']) && isset($_POST['saint_edit_page_title']) 
		&& isset($_POST['saint_edit_page_name']) && isset($_POST['saint_edit_page_layout'])
		&& isset($_POST['saint_edit_page_keywords']) && isset($_POST['saint_edit_page_description'])
		&& isset($_POST['saint_edit_page_categories'])) {
			$this->_page->setTempLayout("system/json");
			
			$spage = new Saint_Model_Page();
			
			if ($spage->loadById($_POST['saint_edit_page_id'])) {
				$spage->setName($_POST['saint_edit_page_name']);
				$spage->setTitle($_POST['saint_edit_page_title']);
				$spage->setLayout($_POST['saint_edit_page_layout']);
				$spage->setMetaKeywords($_POST['saint_edit_page_keywords']);
				$spage->setMetaDescription($_POST['saint_edit_page_description']);
				$spage->setCategories($_POST['saint_edit_page_categories']);
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

		/**
		 * Search controls
		 */
		
		if (isset($_POST['saint-search-phrase'])) {
			$this->_page->searchphrase = $_POST['saint-search-phrase'];
			$this->_page->searchresults = Saint::search($_POST['saint-search-phrase']);
		}
		
		/**
		 * Block controls
		 */
		
		if (isset($_POST['block']) && $_POST['block'] != "") {
			Saint_Controller_Block::loadBlock($_POST['block']);
		}
		
		if (isset($args['edit']) && $args['edit'] != '' && isset($_POST['saint-block-setting-saintname']) && $_POST['saint-block-setting-saintname'] != '') {
			Saint_Controller_Block::editBlock($args['edit'],$_POST['saint-block-setting-saintname']);
		}
		
		/**
		 * Label controls
		 */
		
		if (isset($_POST['label-name']) && isset($_POST['label-value'])) {
			Saint_Controller_Label::editLabel($_POST['label-name'],$_POST['label-value']);
		}
		
		/**
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
		
		/**
		 * Category controls
		 */
		
		if (isset($_POST['saint-add-category'])) {
			Saint_Controller_Category::addCategory($_POST['saint-add-category']);
		}
		
		if (isset($_POST['saint-delete-category']) && $_POST['saint-delete-category']) {
			Saint_Controller_Category::removeCategory($_POST['saint-set-category-id']);
		}
		
		if (isset($_POST['saint-set-category-id']) && isset($_POST['saint-set-category-name'])) {
			Saint_Controller_Category::removeCategory($_POST['saint-set-category-id'],$_POST['saint-set-category-name']);
		}
		
		/**
		 * WYSIWYG controls
		 */
		
		if (isset($_POST['saint-wysiwyg-name']) && isset($_POST['saint-wysiwyg-content'])) {
			Saint_Controller_Wysiwyg::setContent(
				Saint_Model_Block::convertNameFromWeb(preg_replace('/^wysiwyg_/','',$_POST['saint-wysiwyg-name']))
				,$_POST['saint-wysiwyg-content']);
		}
		
		/**
		 * Contact form controls
		 */
		
		if (isset($_POST['saint-contact-name']) && isset($_POST['saint-contact-email']) && isset($_POST['saint-contact-message'])) {
			Saint_Controller_Contact::emailAdmin(array(
				'Name' => $_POST['saint-contact-name'],
				'E-Mail' => $_POST['saint-contact-email'],
				'Message' => $_POST['saint-contact-message'],
			));
		}
		
		/**
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
					Saint_Model_FileLabel::setFile(Saint::convertNameFromWeb($_POST['saint-file-label']),
						array('fid'=>$_POST['saint-file-id']));
				}
			}
		}
		
		/**
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
		
		/**
		 * Parse arguments for potential commands
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
	
		$this->_page->render();
	}
}
