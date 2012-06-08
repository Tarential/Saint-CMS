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
	public function __construct($id = 0) {
		if ($this->_id && $this->load($id)) {
			return 1;
		} else {
			parent::__construct("shop/product");
			$this->_name = '';
			$this->_sku = '';
			$this->_price = 0;
			$this->_file = '';
		}
	}
	
	/**
	 * Load information for product matching given ID from the database.
	 * @param int $id ID of product to load.
	 * @return boolean True on success, false otherwise.
	 */
	public function load($id, $name = null) {
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
	 * Get the name of the loaded product.
	 * @return string Product name.
	 */
	public function getName() {
		return $this->_settings['Name'];
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
		return Saint::getDiscounter()->getDiscountedPrice($this->_id, $this->_categories, $this->_price);
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
	 * Set name of loaded product.
	 * @param string $name New name for loaded product.
	 */
	public function setName($name) {
		$sname = Saint::sanitize($name);
		$this->_settings['Name'] = $sname;
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
}
