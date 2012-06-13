<?php
/**
 * Model of a Saint shop product.
 * @author Preston St. Pierre
 * @package Saint
 */
class Saint_Model_Product extends Saint_Model_Block {
	protected $_sku;
	protected $_price;
	protected $_file;
	
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
			if (isset($settings['Price'])) {
				$this->_price = $settings['Price'];
			} else {
				$this->_price = 0;
			}
			if (isset($settings['SKU'])) {
				$this->_sku = $settings['SKU'];
			} else {
				$this->_sku = '';
			}
			if (isset($settings['File'])) {
				$this->_file = $settings['File'];
			} else {
				$this->_file = '';
			}
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
		if (parent::load("shop/product",$id)) {
			$this->_sku = $this->_settings['SKU'];
			$this->_price = $this->_settings['Price'];
			$this->_file = $this->_settings['File'];
			return 1;
		} else {
			return 0;
		}
	}
	
	/**
	 * Get SKU of loaded product.
	 * @return string SKU of loaded product.
	 */
	public function getSku() {
		return $this->_sku;
	}
	
	/**
	 * Get price of loaded product.
	 * @return float Price of loaded product.
	 */
	public function getPrice() {
		return $this->_price;
	}
	
	/**
	 * Get file associated with loaded product.
	 * @return string File associated with loaded product.
	 */
	public function getFile() {
		return $this->_file;
	}
	
	/**
	 * Get discounted price of loaded product.
	 * @return float Discounted price of loaded product.
	 */
	public function getDiscountPrice() {
		return Saint::getDiscounter()->getDiscountedPrice($this->_id, $this->getCategories(), $this->_price);
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
		$this->_sku = $ssku;
	}
	
	/**
	 * Set price of loaded product.
	 * @param string $name New price for loaded product.
	 */
	public function setPrice($price) {
		$sprice = Saint::sanitize($price);
		$this->_price = $sprice;
	}
	
	/**
	 * Set file associated with loaded product.
	 * @param string $name New file to associate with loaded product.
	 */
	public function setFile($file) {
		$sfile = Saint::sanitize($file);
		$this->_file = $sfile;
	}
	
	/**
	 * Specify custom field options for the product edit screen.
	 * @see core/code/Model/Saint_Model_Block::renderInput()
	 */
	public function renderInput($setting, $options = array()) {
			switch ($setting) {
			case "File":
				parent::renderInput($setting,
					array(
						"details" => "File associated with product. This file must be stored in the 'restricted' folder and will be delivered to users when they make a purchase.",
						"label" => "(Optional) File:",
					)
				);
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
