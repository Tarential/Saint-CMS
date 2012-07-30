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
<a href="/download" class="saint-cart-title">Download Now</a>
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