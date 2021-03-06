<?php
/**
 * Model of a Saint shop product.
 * @author Preston St. Pierre
 * @package Saint
 */
class Saint_Model_Product extends Saint_Model_Block {
	
	/**
	 * Create model with default data.
	 */
	public function __construct($id = 0, $name = null, $enabled = null, $settings = array()) {
		// Magic so it can accept the first two arguments in either order.
		if (!Saint::sanitize($id,SAINT_REG_ID) && Saint::sanitize($name,SAINT_REG_ID)) {
			$id = $name;
		}
		if (empty($settings)) {
			if ($this->_id && $this->load($id)) {
				return 1;
			}
		} else {
			parent::__construct("shop/product",$id,$enabled,$settings);
		}
	}
	
	/**
	 * Load information for product matching given ID from the database.
	 * @param int $id ID of product to load.
	 * @return boolean True on success, false otherwise.
	 */
	public function load($id, $name = null) {
		// Magic so it can accept the arguments in either order.
		if (!Saint::sanitize($id,SAINT_REG_ID) && Saint::sanitize($name,SAINT_REG_ID)) {
			$id = $name;
		}
		return parent::load("shop/product",$id);
	}
	
	/**
	 * Select ID for product matching given URI and load data into model.
	 * Function will first look for an exact match; if that fails it will try a fuzzy match.
	 * @param $uri URI to match.
	 * @return True on success, false otherwise.
	 */
	public function loadByUri($uri) {
		$suri = Saint::sanitize($uri);
		for ($i = 0; $i < 2; $i++) {
			try {
				$id = Saint::getOne("SELECT `id` FROM `st_blocks_shop_product` WHERE `uri` LIKE '$suri' ORDER BY `id` DESC");
				return $this->load($id);
			} catch (Exception $e) {
				if ($e->getCode()) {
					Saint::logError("Unable to select shop product via URI: ".$e->getMessage(),__FILE__,__LINE__); }
			}
			$suri = "%$suri%";
		}
		return 0;
	}
	
	/**
	 * Get the URL for the current product.
	 * @return string URL for current product.
	 */
	public function getUrl() {
		return $this->getPageUrl() . "/" . $this->_settings['uri'];
	}
	
	/**
	 * Get SKU of loaded product.
	 * @return string SKU of loaded product.
	 */
	public function getSku() {
		return $this->_settings['sku'];
	}
	
	/**
	 * Get price of loaded product.
	 * @return float Price of loaded product.
	 */
	public function getPrice() {
		return $this->_settings['price'];
	}
	
	/**
	 * Get file associated with loaded product.
	 * @return string File associated with loaded product.
	 */
	public function getFile() {
		return $this->_settings['file'];
	}
	
	/**
	 * Get product name.
	 * @return string Name of loaded product.
	 */
	public function getProductName() {
		return $this->_settings['name'];
	}
	
	/**
	 * Get discounted price of loaded product.
	 * @return float Discounted price of loaded product.
	 */
	public function getDiscountPrice() {
		if (is_array($this->getCategories()))
			$keys = array_keys($this->getCategories());
		else
			$keys = array();
		return Saint::getDiscounter()->getDiscountedPrice($this->_id, $keys, $this->_settings['price']);
	}
	
	/**
	 * Get tax rate applied to loaded product.
	 * @return float Tax rate applied to loaded product.
	 */
	public function getTaxRate() {
		// To be implemented at a later date
		return 1;
	}
	
	/**
	 * Get final price of loaded product after all discounts and taxes have been applied.
	 * @return float Final price of loaded product.
	 */
	public function getFinalPrice() {
		return $this->getDiscountPrice()*$this->getTaxRate();
	}

	/**
	 * Set sku of loaded product.
	 * @param string $name New sku for loaded product.
	 */
	public function setSku($sku) {
		$ssku = Saint::sanitize($sku);
		$this->_settings['sku'] = $ssku;
	}
	
	/**
	 * Set price of loaded product.
	 * @param string $name New price for loaded product.
	 */
	public function setPrice($price) {
		$sprice = Saint::sanitize($price);
		$this->_settings['price'] = $sprice;
	}
	
	/**
	 * Set file associated with loaded product.
	 * @param string $name New file to associate with loaded product.
	 */
	public function setFile($file) {
		$sfile = Saint::sanitize($file);
		$this->_settings['file'] = $sfile;
	}
	
	/**
	 * Specify custom field options for the product edit screen.
	 * @see core/code/Model/Saint_Model_Block::renderInput()
	 */
	public function renderInput($setting, $options = array()) {
			switch ($setting) {
			case "name":
				$options['classes'] = 'uri-indicator';
				parent::renderInput($setting,$options);
				break;
			case "file":
				parent::renderInput($setting,
					array(
						"details" => array("File associated with product. This file must be stored in the 'restricted' folder and will be delivered to users when they make a purchase."),
						"label" => "(Optional) File:",
					)
				);
				break;
			case "uri":
				$options['label'] = "URI:";
				$options['details'] = array($this->getPageUrl()."/[URI]");
				if ($this->get($setting) == "") {
					$options['details'][] = "(Leave blank to auto generate)";
				}
				parent::renderInput($setting,$options);
				break;
			default:
				parent::renderInput($setting);
		}
	}
	
	/**
	 * Assign a preview template to the product edit screen.
	 * @see core/code/Model/Saint_Model_Block::renderPreview()
	 */
	public function renderPreview($arguments = array()) {
		$arguments['view'] = "shop/product-preview";
		parent::renderPreview($arguments);
	}
}
