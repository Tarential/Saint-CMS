<?php
/**
 * Shop functions and page model for the Saint framework.
 * @author Preston St. Pierre
 * @package Saint
 */
class Saint_Model_Shop extends Saint_Model_Page {
	
	/**
	 * Create new temporary file access link.
	 * @param int $productid ID of product whose associated file to link.
	 * @param int $retries Optional number of times the download link may be used.
	 * @param int $timeout Optional number of hours until the download link expires.
	 * @return string URL of file access link or 0 on failure. 
	 */
	public static function createDownload($productid,$retries = 5, $timeout = 48) {
		$spid = Saint::sanitize($productid,SAINT_REG_ID);
		$sretries = Saint::sanitize($retries,SAINT_REG_ID);
		$linkid = md5(time().$productid.rand(100,1000));
		$expires = date( 'Y-m-d H:i:s',strtotime('+'.Saint::sanitize($timeout).' hours'));
		try {
			Saint::query("INSERT INTO `st_shop_downloads` (`productid`,`linkid`,`remaining`,`expires`) VALUES ('$spid','$linkid','$sretries','$expires')");
			return $linkid;
		} catch (Exception $e) {
			Saint::logError("Unable to create temporary download link for product '$spid': ".$e->getMessage(),__FILE__,__LINE__);
			return 0;
		}
	}
	
	/**
	 * Decrement the number of times a link may be accessed.
	 * @param int $productid ID of product to which the file is associated.
	 * @param int $linkid ID of temporary access link.
	 * @return boolean True if download link is available, false otherwise. 
	 */
	public static function decrementLink($productid,$linkid) {
		$spid = Saint::sanitize($productid,SAINT_REG_ID);
		$slinkid = Saint::sanitize($linkid);
		try {
			$info = Saint::getRow("SELECT `remaining`,`expires` FROM `st_shop_downloads` WHERE `productid`='$spid' AND `linkid`='$slinkid'");
			$remaining = $info[0];
			$expires = strtotime($info[1]);
			if ($expires < time()) {
				try {
					Saint::query("DELETE FROM `st_shop_downloads` WHERE `productid`='$spid' AND `linkid`='$slinkid'");
				} catch (Exception $f) {
					Saint::logError("Unable to delete expired product download link: ".$f->getMessage(),__FILE__,__LINE__);
				}
				return 0;
			} else {
				if ($remaining > 1) {
					$query = "UPDATE `st_shop_downloads` SET `remaining`=`remaining`-1 WHERE `productid`='$spid' AND `linkid`='$slinkid'";
				} else {
					$query = "DELETE FROM `st_shop_downloads` WHERE `productid`='$spid' AND `linkid`='$slinkid'";
				}
				try {
					Saint::query($query);
					return 1;
				} catch (Exception $f) {
					Saint::logError("Unable to decrement product download link: ".$f->getMessage(),__FILE__,__LINE__);
					return 0;
				}
			}
		} catch (Exception $e) {
			if ($e->getCode()) {
				Saint::logError("Unable to select product download link with id '$spid' and linkid '$slinkid': ".$e->getMessage(),__FILE__,__LINE__);
			}
			return 0;
		}
	}
	
	protected $_product_args;
	protected $_products;
	
	/**
	 * Process input settings.
	 * @see core/code/Controller/Saint_Controller_Shop::process()
	 */
	public function process() {
		Saint_Controller_Shop::process($this);
	}
	
	/**
	 * Get arguments for displaying products.
	 * @return array Arguments for displaying products.
	 */
	public function getProductArgs() {
		return $this->_product_args;
	}
	
	/**
	 * Set arguments for displaying products.
	 * @param array $args New arguments for displaying products.
	 */
	public function setProductArgs($args) {
		if (is_array($args)) {
			$this->_product_args = $args;
		} else {
			$this->_product_args = array($args);
		}
	}
	
	/**
	 * Get products prepared for this page.
	 */
	public function getProducts() {
		return $this->_products;
	}
	
	/**
	 * Set products prepared for this page.
	 * @param array $products Array of Saint_Model_BlogProduct ready for use.
	 */
	public function setProducts($products) {
		if (is_array($products)) {
			$this->_products = $products;
		} else {
			$this->_products = array($products);
		}
	}
	
	/**
	 * Get index of all blog posts / child pages.
	 * @return array Index of all descendants of current page including blog posts.
	 */
	public function getIndex() {
		$index = parent::getIndex();
		if ($this->_id == 0) {
			return $index;
		} else {
			$products = Saint_Model_Block::getBlocks("shop/product",array(
				'enabled' => true,
				'page_id' => $this->_id,
				'collection' => true,
			));
			
			foreach ($products as $product) {
				$index[] = array($product->getUrl(),$product->get("name"),$product->getUpdatedTime(),array());
			}
			return $index;
		}
	}
}
