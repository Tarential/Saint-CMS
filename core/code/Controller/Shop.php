<?php
/**
 * Controller for the Saint shop.
 * @author Preston St. Pierre
 * @package Saint
 */
class Saint_Controller_Shop {
	/**
	 * Serve a file for the given product if the given link ID is active.
	 * @param int $productid ID of product whose related file to serve.
	 * @param string $linkid Link ID sent to customer after purchase confirmation.
	 */
	public static function download($productid,$linkid = null) {
		$page = Saint::getCurrentPage();
		if (Saint_Model_Shop::decrementLink($productid,$linkid)) {
			$filename = Saint::getBlockSetting("shop/product",$productid,"file");
			$file = SAINT_SITE_ROOT."/restricted/".$filename;
			if ($filename != "" && file_exists($file)) {
				$page->setTempLayout("system/system");
				header('Content-Type: application/force-download');
				header('Content-Length:' . filesize($file));
				header("Content-Disposition: inline; filename=\"".$filename."\"");
				$fp = fopen($file,"rb");
				fpassthru($fp);
				fclose($fp);
			} else {
				Saint::logError("Unable to find file '$filename'.",__FILE__,__LINE__);
				$page->error = "Unable to find file associated with the given ID. If you were linked to this file, please contact the site administrator.";
				$page->setTempLayout("system/error");
			}
		} else {
			$page->error = "Sorry, but that download link is inactive. If you were unable to download a product you purchased, please contact us for support.";
			$page->setTempLayout("system/error");
		}
	}
	

	/**
	 * Set up parameters for rendering a shop page.
	 * @param Saint_Model_Page $page Page to process.
	 */
	public static function process($page) {
		$args = $page->getArgs();
		$id = 0;
		$url = SAINT_URL . "/" . $page->getName();
		$category = null;
		
		if (!empty($args['subids'])) {
			if ($args['subids'][0] == "category" && isset($args['subids'][1]) && $args['subids'][1] != "") {
				$category = $args['subids'][1];
			} else {
				$product = new Saint_Model_Product();
				$product->loadByUri($args['subids'][0]);
				$id = $product->getId();
				if ($id) {
					$url = $product->getUrl();
				} else {
					# Looking to make a 404 page instead of defaulting to the index?
					# Uncomment this:
					# $product->setTempLayout("system/404");
				}
			}
		}
		
		if ($id) {
			$arguments = array(
				"matches" => array(
					array("enabled","1"),
					array("id",$id),
				),
				"repeat" => 1,
				"collection" => true,
			);
			$page->setTempTitle($product->get("name"));
			$page->setTempKeywords(explode(",",$product->get("keywords")));
			$page->setTempDescription($product->getLabel("description","",array('container'=>false)));
		} else {
			$arguments = array(
				"repeat" => 15,
				"order" => "DESC",
				"orderby" => "id",
				"paging" => true,
				"page_id" => $page->getId(),
				"matches" => array(
					array("enabled","1"),
				),
				"label" => "You haven't created any shop products yet. Click 'edit page' in the Saint admin menu then click 'Add New Product' to create a product.",
				"collection" => true,
			);
			if ($category != null) {
				$arguments['category'] = Saint_Model_Block::convertNameFromWeb($category);
			}
		}
		$products = Saint_Model_Block::getBlocks("shop/product",$arguments);
		$arguments['blocks'] = $products;
		$page->setProducts($products);
		$page->setProductArgs($arguments);
	}
}
