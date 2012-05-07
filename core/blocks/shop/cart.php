<?php
$cart = Saint::getShoppingCart();
$items = $cart->getItems();
Saint::includeBlock("notices",false);
$page = Saint::getCurrentPage();
$args = $page->getArgs();
$products = array();
foreach ($items as $id=>$number) {
	$product = new Saint_Model_Product();
	if ($product->load($id)) {
		$products[] = array($product,$number);
	}
}
?>
<?php if (sizeof($products)): ?>
<div id="saint-paypal-buynow">
	<form action="https://<?php echo SAINT_PAYPAL_URL; ?>/cgi-bin/webscr" method="post"<?php if (isset($args['buynow']) && $args['buynow'] == 1): ?> class="buynow"<?php endif; ?>>
	<input type="hidden" name="cmd" value="_cart" />
	<input type="hidden" name="upload" value="1" />
	<input type="hidden" name="return" value="<?php echo SAINT_BASE_URL . "shop/view.thanks"; ?>" />
	<input type="hidden" name="cancel_return" value="<?php echo SAINT_BASE_URL . "shop/view.cart"; ?>" />
	<input type="hidden" name="notify_url" value="<?php echo SAINT_BASE_URL . "shop/view.ipn"; ?>" />
	<input type="hidden" name="custom" value="<?php echo $cart->getId(); ?>" />
	<?php $i = 1; foreach ($products as $product): ?>
	<input type="hidden" name="amount_<?php echo $i; ?>" value="<?php echo $product[0]->getDiscountPrice(); ?>" />
	<input type="hidden" name="item_name_<?php echo $i; ?>" value="<?php echo $product[0]->getName(); ?>" />
	<input type="hidden" name="quantity_<?php echo $i; ?>" value="<?php echo $product[1]; ?>" />
	<input type="hidden" name="item_number_<?php echo $i; ?>" value="<?php echo $product[0]->getId(); ?>" />
	<?php $i++; endforeach; ?>
	<input type="hidden" name="business" value="<?php echo SAINT_PAYPAL_EMAIL; ?>" />
	<input type="hidden" name="currency_code" value="USD" />
	<input type="image" src="http://www.paypal.com/en_US/i/btn/x-click-but01.gif" name="submit" alt="Make payments with PayPal - it's fast, free and secure!" />
	</form>
</div>
<span class="saint-cart-title link">Checkout Now with PayPal</span>
<span class="saint-cart-total">Total: $<?php echo $cart->getTotal(); ?></span>
<?php else: ?>
<span class="saint-cart-total">You have no items in your cart. <a href="<?php echo SAINT_BASE_URL; ?>/shop">Continue Shopping.</a></span>
<?php endif; ?>
<div class="saint-cart-items">
<ul>
<?php foreach ($products as $product): ?>
<?php
$discount_price = $product[0]->getDiscountPrice();
if ($discount_price < $product[0]->getPrice()) {
	$sale = true;
} else {
	$sale = false;
}
?>
<li<?php if ($sale) echo ' class="sale"'; ?>>
	<span class="product-name"><?php echo $product[0]->getName(); ?></span>
	<span class="product-meta">
		<span class="product-price">$<?php echo number_format(round($discount_price,2),2); ?></span>
		<span class="product-number">(x<?php echo $product[1]; ?>)</span>
		<span class="remove-from-cart">
			<a href="<?php echo SAINT_BASE_URL; ?>shop/remfromcart.<?php echo $product[0]->getId(); ?>/num.1/" class="link">(X)</a>
		</span>
	</span>
</li>
<?php endforeach; ?>
</ul>
</div>