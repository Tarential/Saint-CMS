<?php
/**
 * Parses discount rules and applies them to shop items.
 * @author Preston St. Pierre
 * @package Saint
 */
class Saint_Model_Discount {
	protected $_discounts;
	
	/**
	 * Load from database, parse and hold discount rules in memory.
	 * @return boolean True for success, false otherwise.
	 */
	public function __construct() {
		$this->_discounts = array();
		try {
			$discounts = Saint::getAll("SELECT `Name`,`Type`,`Amount`,`Filters` FROM `st_blocks_shop_admin_discount` WHERE `StartDate` < CURRENT_TIMESTAMP < `EndDate` AND `enabled`='1'");
			
			foreach ($discounts as $discount) {
				$dc = array_push($this->_discounts,array())-1;
				$this->_discounts[$dc]['name'] = $discount[0];
				$this->_discounts[$dc]['type'] = $discount[1];
				$this->_discounts[$dc]['amount'] = $discount[2];
				
				$filters = explode(';',$discount[3]);
				foreach ($filters as $filter) {
					$info = explode(':',$filter);
					if (isset($info[0]) && $info[0] != "") {
						if (isset($this->_discounts[$dc][$info[0]]) && is_array($this->_discounts[$dc][$info[0]])) {
							$this->_discounts[$dc][$info[0]][] = $info[1];
						} else {
							$this->_discounts[$dc][$info[0]] = array($info[1]);
						}
					}
				}
			}
			return 1;
		} catch (Exception $e) {
			if ($e->getCode()) {
				Saint::logError("There was a problem loading active discounts: ".$e->getMessage(),__FILE__,__LINE__);
				return 0;
			}
		}
		return 1;
	}
	
	/**
	 * Apply discounts which match the given parameters.
	 * @param int $productid Product ID to match.
	 * @param int[] $categories Array of category IDs to match.
	 * @param float $price Original price.
	 * @return float Discounted price.
	 */
	public function getDiscountedPrice($productid,$categories,$price) {
		if (empty($this->_discounts)) {
			return $price;
		} else {
			$applicable_discounts = array(
				'percent' => array(),
				'flat' => array(),
			);
			$finalprice = $price;
			foreach ($this->_discounts as $dc) {
				# Discount Control List
				# By default discounts apply to everything; this changes if a filter is added.
				# If filters are added it must match at least one to apply.
				$whitelist_discount_applies = true;
				$cat_discount_applies = false;
				$id_discount_applies = false;
				
				if (isset($dc['cat']) && is_array($dc['cat']) && !empty($dc['cat'])) {
					$whitelist_discount_applies = false;
					foreach ($categories as $cat) {
						if (in_array($cat,$dc['cat'])) {
							$cat_discount_applies = true; }
					}
				}
				
				if (!$cat_discount_applies && isset($dc['id']) && is_array($dc['id']) && !empty($dc['id'])) {
					$whitelist_discount_applies = false;
					if (in_array($productid,$dc['id'])) {
						$id_discount_applies = true; }
				}
				
				# Once we confirm that the discount applies we add it to the array for orderly processing.
				if ($whitelist_discount_applies || $cat_discount_applies || $id_discount_applies) {
					$applicable_discounts[$dc['type']][] = $dc;
				}
			}
			
			foreach ($applicable_discounts['flat'] as $flat) {
				$finalprice -= $flat['amount'];
			}
			
			foreach ($applicable_discounts['percent'] as $percent) {
				$finalprice *= (1-($percent['amount']/100));
			}
			return round($finalprice,2);
		}
	}
}
