<?php
/**
 * Controller for a user shopping cart in the Saint store.
 * @author Preston St. Pierre
 * @package Saint
 */
class Saint_Controller_ShoppingCart {
	/**
	 * Add an item to the cart of the current user.
	 * @param int $itemid ID of item to add.
	 */
	public static function addToCart($itemid) {
		$sc = Saint::getShoppingCart();
		$page = Saint::getCurrentPage();
		$args = $page->getArgs();
		
		if (isset($args['num']))
			$number = $args['num'];
		else
			$number = 1;
			
		if ($sc && $sc->addItem($itemid, $number) && $sc->save()) {
			$url = SAINT_BASE_URL."shop/view.cart";
			if (isset($args['buynow']) && $args['buynow'] == 1) {
				$url .= "/buynow.1"; }
			Saint::addNotice("Added product to cart.");
			header("Location: ".$url);
		} else {
			Saint::logError("Unable to add item to cart; cart will not initiate.",__FILE__,__LINE__);
			$page = Saint::getCurrentPage();
			$page->error = "Problem adding item to cart. If this issue continues please contact the site administrator.";
			$page->setTempLayout("system/error");
		}
	}
	
	/**
	 * Remove an item from the cart of the current user.
	 * @param int $itemid ID of item to remove.
	 */
	public static function removeFromCart($itemid) {
		$sc = Saint::getShoppingCart();
		$page = Saint::getCurrentPage();
		$args = $page->getArgs();
		
		if (isset($args['num']))
			$number = $args['num'];
		else
			$number = 1;
		
		if ($sc && $sc->removeItem($itemid, $number) && $sc->save()) {
			Saint::addNotice("Removed product from cart.");
			header("Location: ".SAINT_BASE_URL."shop/view.cart");
		} else {
			Saint::logError("Unable to remove item from cart; cart will not initiate.",__FILE__,__LINE__);
			$page = Saint::getCurrentPage();
			$page->error = "Problem removing item from cart. If this issue continues please contact the site administrator.";
			$page->setTempLayout("system/error");
		}
	}
}
