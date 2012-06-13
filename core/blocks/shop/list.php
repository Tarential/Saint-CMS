<?php
if ($block->getId()) {
	$sale = $block->getDiscountPrice() < $block->getPrice() ? 1 : 0;
?>
<div class="saint-product list<?php if ($sale) echo " sale"; ?>" id="ssm-sku-<?php echo $block->getSku(); ?>">
	<h4>
		<a href="<?php echo SAINT_URL; ?>/shop/pid.<?php echo $block->getId(); ?>/">
			<?php echo $block->get("Name"); ?>
			<span class="price">$<?php if ($sale) echo $block->getDiscountPrice(); else echo $block->getPrice(); ?></span>
		</a>
	</h4>
	<div class="ssm meta-links">
		<a href="<?php echo SAINT_URL; ?>/shop/addtocart.<?php echo $block->getId(); ?>" class="link add-to-cart">Add to Cart</a>
		<a href="<?php echo SAINT_URL; ?>/shop/addtocart.<?php echo $block->getId(); ?>/buynow.1" class="link buy-now">Buy Now</a>
	</div>
	<a href="<?php echo SAINT_URL; ?>/shop/pid.<?php echo $block->getId(); ?>/">
		<?php Saint::getBlockImage($block->getName(), $block->getId(), "main-image",array('link'=>false,'max-width'=>180,'max-height'=>200)); ?>
	</a>
	<?php if ($sale): ?>
	<h6>Down from $<?php echo $block->getPrice(); ?>. Save <span class="discount"><?php echo round((1-$block->getDiscountPrice()/$block->getPrice())*100,0); ?>%</span>!</h6>
	<?php endif; ?>
</div>
<?php } else { ?>
<h2>Product ID not found.</h2>
<?php } ?>