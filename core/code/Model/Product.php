<?php
class Saint_Model_Product {
	protected $_id;
	protected $_enabled;
	protected $_name;
	protected $_sku;
	protected $_price;
	protected $_file;
	protected $_categories;
	
	public function __construct() {
		$this->_id = 0;
		$this->_enabled = 0;
		$this->_name = '';
		$this->_sku = '';
		$this->_price = 0;
		$this->_file = '';
		$this->_categories = array();
	}
	
	public function load($id) {
		$sid = Saint::sanitize($id,SAINT_REG_ID);
		if ($sid) {
			try {
				$info = Saint::getRow("SELECT `enabled`,`name`,`sku`,`price`,`file` FROM `st_blocks_shop_product` WHERE `id`='$sid'");
				$this->_id = $sid;
				$this->_enabled = $info[0];
				$this->_name = $info[1];
				$this->_sku = $info[2];
				$this->_price = $info[3];
				$this->_file = $info[4];
				try {
					$this->_categories = Saint::getAll("SELECT `cat`.`id` FROM `st_categories` as `cat`, `st_blockcats` as `bc`, `st_blocks` as `block` WHERE `bc`.`catid`=`cat`.`id` AND `bc`.`blockid`=`block`.`id` AND `block`.`blockid`='$sid'");
				} catch (Exception $f) {
					if ($f->getCode()) {
						Saint::logError("Unable to load product categories: ".$f->getMessage(),__FILE__,__LINE__);
					}
				}
				return 1;
			} catch (Exception $e) {
				if ($e->getCode()) {
					Saint::logError("Unable to load product: ".$e->getMessage(),__FILE__,__LINE__);
				} else {
					Saint::logError("Unable to find matching product with id '$sid'.",__FILE__,__LINE__);
				}
				return 0;
			}
		} else {
			Saint::logError("Invalid product id '$id'.",__FILE__,__LINE__);
			return 0;
		}
	}

	public function getId() {
		return $this->_id;
	}
	
	public function getEnabled() {
		return $this->_enabled;
	}
	
	public function getName() {
		return $this->_name;
	}
	
	public function getSku() {
		return $this->_sku;
	}
	
	public function getPrice() {
		return $this->_price;
	}
	
	public function getFile() {
		return $this->_file;
	}
	
	public function getCategories() {
		return $this->_categories;
	}
	
	public function getDiscountPrice() {
		return Saint::getDiscounter()->getDiscountedPrice($this->_id, $this->_categories, $this->_price);
	}
	
	public function getTaxRate() {
		// To be implemented at a later date
		return 1;
	}
	
	public function getFinalPrice() {
		return $this->getDiscountPrice()*$this->getTaxRate();
	}
	
	public function setEnabled($enabled) {
		$senabled = Saint::sanitize($enabled,SAINT_REG_BOOL);
		$this->_enabled = $senabled;
		return 1;
	}
	
	public function setName($name) {
		$sname = Saint::sanitize($name);
		$this->_name = $sname;
		return 1;
	}
	
	public function setSku($sku) {
		$ssku = Saint::sanitize($sku);
		$this->_sku = $ssku;
		return 1;
	}
	
	public function setPrice($price) {
		$sprice = Saint::sanitize($price);
		$this->_price = $sprice;
		return 1;
	}
	
	public function setFile($file) {
		$sfile = Saint::sanitize($file);
		$this->_file = $sfile;
		return 1;
	}
	
	public function save() {
		if ($this->_id) {
			try {
				Saint::query("UPDATE `st_blocks_shop_product` SET `enabled`='$this->_enabled',`name`='$this->_name',`sku`='$this->_sku',`price`='$this->_price',`file`='$this->_file' WHERE `id`='$this->_id'");
				return 1;
			} catch (Exception $e) {
				Saint::logError("Unable to save product: ".$e->getMessage(),__FILE__,__LINE__);
				return 0;
			}
		} else {
			Saint::logError("You must load a product from the database before saving.",__FILE__,__LINE__);
			return 0;
		}
	}
}
