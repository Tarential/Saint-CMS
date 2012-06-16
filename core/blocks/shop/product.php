<?php
if ($block->getId()) {
	$sale = $block->getDiscountPrice() < $block->getPrice() ? 1 : 0;
?>
<div class="saint-product<?php if ($sale) echo " sale"; ?> ssm-sku-<?php echo $block->getSku(); ?>">
	<h2>
		<?php echo $block->get("name"); ?> - <span class="regprice">$<?php echo $block->getPrice(); ?></span>
		<?php if ($sale): ?>on sale for <span class="saleprice">$<?php echo $discount_price; ?></span><?php endif; ?>
	</h2>
	<?php $block->includeImage("main-image"); ?>
	<?php echo $block->getLabel("description","Enter product description here..."); ?>
	<div class="ssm meta-links">
		<a href="<?php echo SAINT_URL; ?>/shop/addtocart.<?php echo $block->getId(); ?>" class="link add-to-cart">Add to Cart</a>
		<a href="<?php echo SAINT_URL; ?>/shop/addtocart.<?php echo $block->getId(); ?>/buynow.1" class="link buy-now">Buy Now</a>
	</div>
</div>
<?php } else { ?>
<h2>Product ID not found.</h2>
<?php } ?>