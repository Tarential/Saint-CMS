<?php
$cart = Saint::getCurrentUser()->getShoppingCart();

if ($block->getId()) { 
	$discount_price = $block->getDiscountPrice();
	if ($discount_price < $block->getPrice()) {
		$sale = true;
	} else {
		$sale = false;
	}
?>
<div id="saint-paypal-buynow">
	<form action="https://<?php echo SAINT_PAYPAL_URL; ?>/cgi-bin/webscr" method="post">
	<input type="hidden" name="cmd" value="_cart" />
	<input type="hidden" name="upload" value="1" />
	<input type="hidden" name="return" value="<?php echo SAINT_BASE_URL . "shop/view.thanks"; ?>" />
	<input type="hidden" name="cancel_return" value="<?php echo SAINT_BASE_URL . "shop/view.cart"; ?>" />
	<input type="hidden" name="notify_url" value="<?php echo SAINT_BASE_URL . "shop/view.ipn"; ?>" />
	<input type="hidden" name="custom" value="<?php echo $cart->getId(); ?>" />
	<input type="hidden" name="amount_1" value="<?php echo $block->getDiscountPrice(); ?>" />
	<input type="hidden" name="item_name_1" value="<?php echo $block->get("name"); ?>" />
	<input type="hidden" name="quantity_1" value="1" />
	<input type="hidden" name="item_number_1" value="<?php echo $block->getId(); ?>" />
	<input type="hidden" name="business" value="<?php echo SAINT_PAYPAL_EMAIL; ?>" />
	<input type="hidden" name="currency_code" value="USD" />
	<input type="image" src="http://www.paypal.com/en_US/i/btn/x-click-but01.gif" name="submit" alt="Make payments with PayPal - it's fast, free and secure!" />
	</form>
</div>
<a class="saint-cart-title buy-now link">Buy Now with PayPal</a>
<div class="saint-product<?php if ($sale) echo " sale"; ?>" id="ssm-sku-<?php echo $block->getSku(); ?>">
<?php if ($sale): ?>
	<h2><?php echo $block->get("name"); ?> - <span class="saleprice">$<?php echo $discount_price; ?></span></h2>
	<h6>Regular <span class="regprice">$<?php echo $block->getPrice(); ?></span></h6>
<?php else: ?>
	<h2><?php echo $block->get("name"); ?> - <span class="regprice">$<?php echo $block->getPrice(); ?></span></h2>
<?php endif; ?>
	<?php $block->includeImage("main-image",array('max-width'=>225)); ?>
	<?php echo $block->getLabel("description","Enter product description here..."); ?>
</div>
<?php } else { ?>
<h2>Product ID not found.</h2>
<?php } ?>