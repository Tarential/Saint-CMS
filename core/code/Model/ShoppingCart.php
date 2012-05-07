<?php
class Saint_Model_ShoppingCart {
	protected $_id;
	protected $_owner;
	protected $_items;
	protected $_purchased;
	
	public static function isActive($id) {
		$sid = Saint::sanitize($id,SAINT_REG_ID);
		try {
			Saint::getOne("SELECT `id` FROM `st_shop_carts` WHERE `id`='$sid' AND `purchased`='0'");
			return 1;
		} catch (Exception $e) {
			if ($e->getCode()) {
				Saint::logError("Unable to test cart activity: ".$e->getMessage()); }
			return 0;
		}
	}
	
	public static function cartExists($id) {
		$sid = Saint::sanitize($id,SAINT_REG_ID);
		try {
			Saint::getOne("SELECT `id` FROM `st_shop_carts` WHERE `id`='$sid'");
			return 1;
		} catch (Exception $e) {
			if ($e->getCode()) {
				Saint::logError("Unable to test if cart exists: ".$e->getMessage()); }
			return 0;
		}
	}
	
	public function __construct($id = 0) {
		if ($id && $this->load($id)) {
			
		} else {
			$this->_id = 0;
			$this->_owner = Saint::getCurrentUser()->getId();
			$this->_items = array();
			$this->_purchased = false;
		}
	}
	
	public function load($id) {
		if ($id == 0)
			return $this->loadNew();
		else {
			$sid = Saint::sanitize($id,SAINT_REG_ID);
			if ($sid) {
				
				if (!Saint_Model_ShoppingCart::cartExists($sid)) {
					return 0;
				}
				
				try {
					$items = Saint::getAll("SELECT `p`.`productid`,`p`.`number`,`c`.`owner`,`c`.`purchased` FROM `st_shop_carts` AS `c`,`st_shop_cart_products` as `p` WHERE `c`.`id`=`p`.`cartid` AND `c`.`id`='$sid'");
					$this->_owner = $items[0][2];
					$this->_purchased = $items[0][3];
					foreach ($items as $item) {
						$this->_items[$item[0]] = $item[1];
					}
					$this->_id = $sid;
					return 1;
				} catch (Exception $e) {
					if ($e->getCode()) {
						Saint::logError("Unable to get product items from database: ".$e->getMessage(),__FILE__,__LINE__);
						return 0;
					} else {
						$this->_id = $sid;
						$this->_items = array();
						return 1;
					}
				}
			} else {
				Saint::logError("Invalid product id '$id'. Must be digits 0-9 only. See manual for further info.",__FILE__,__LINE__);
				return 0;
			}
		}
	}

	public function loadNew() {
		try {
			Saint::query("INSERT INTO `st_shop_carts` (`owner`) VALUES ('".Saint::getCurrentUser()->getId()."')");
			$newid = Saint::getLastInsertId();
			$this->load($newid);
			return $newid;
		} catch (Exception $e) {
			Saint::logError("Unable to create new shopping cart: ".$e->getMessage(),__FILE__,__LINE__);
			return 0;
		}
	}
	
	public function setPurchased($boolean) {
		if ($sb = Saint::sanitize($boolean,SAINT_REG_BOOL)) {
			$this->_purchased = $sb;
			return 1;
		} else {
			return 0;
		}
	}
	
	public function getId() {
		return $this->_id;
	}

	public function getItems() {
		return $this->_items;
	}
	
	public function getOwner() {
		return $this->_owner;
	}

	public function isPurchased() {
		return $this->_purchased;
	}
	
	public function addItem($itemid,$number = 1) {
		$sid = Saint::sanitize($itemid,SAINT_REG_ID);
		$snum = Saint::sanitize($number,SAINT_REG_ID);
		if ($sid) {
			if (isset($this->_items[$sid]))
				$this->_items[$sid] += $snum;
			else
				$this->_items[$sid] = $snum;
			return 1;
		} else {
			Saint::logError("Invalid item id '$itemid'.",__FILE__,__LINE__);
			return 0;
		}
	}
	
	public function removeItem($itemid, $number = 1) {
		$snum = Saint::sanitize($number,SAINT_REG_ID);
		if (isset($this->_items[$itemid])) {
			if ($this->_items[$itemid] > $snum)
				$this->_items[$itemid] -= $snum;
			else
				unset($this->_items[$itemid]);
		}
		return 1;
	}
	
	public function clearItems() {
		$this->_items = array();
	}
	
	public function getTotal() {
		$total = 0;
		$product = new Saint_Model_Product();
		foreach ($this->_items as $id=>$number) {
			if ($product->load($id))
				$total += ($product->getFinalPrice() * $number);
		}
		return $total;
	}
	
	public function save() {
		if ($this->_id) {
			// First, update the "last edited" time of the cart
			try {
				Saint::query("UPDATE `st_shop_carts` SET `updated`=NOW(),`purchased`='$this->_purchased' WHERE `id`='$this->_id'");
			} catch (Exception $e) {
				Saint::logError("Unable to update shopping cart with id '$this->_id': ".$e->getMessage(),__FILE__,__LINE__);
				return 0;
			}
			
			// Then, clear stored products
			try {
				Saint::query("DELETE FROM `st_shop_cart_products` WHERE `cartid`='$this->_id'");
			} catch (Exception $e) {
				Saint::logError("Unable to clear cart items: ".$e->getMessage(),__FILE__,__LINE__);
				return 0;
			}
			
			// Finally, add the items currently in the cart
			foreach ($this->_items as $itemid=>$number) {
				try {
					Saint::query("INSERT INTO `st_shop_cart_products` (`cartid`,`productid`,`number`) VALUES ('$this->_id','$itemid','$number')");
				} catch (Exception $e) {
					Saint::logError("Unable to add product '$itemid' to cart '$this->_id': ".$e->getMessage(),__FILE__,__LINE__);
					return 0;
				}
			}
			return 1;
		} else {
			Saint::logError("You must use the load() or loadNew() function before saving your cart.");
			return 0;
		}
	}
}

