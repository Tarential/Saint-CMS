<?php
$product = new Saint_Model_Product();
if ($product->load($id)) {
	$discount_price = $product->getDiscountPrice();
	if ($discount_price < $product->getPrice()) {
		$sale = true;
	} else {
		$sale = false;
	}
?>
<div class="saint-product list<?php if ($sale) echo " sale"; ?>" id="ssm-sku-<?php echo $product->getSku(); ?>">
	<h4>
		<a href="<?php echo SAINT_URL; ?>shop/pid.<?php echo $product->getId(); ?>/">
			<?php echo $product->getName(); ?>
			<span class="price">$<?php if ($sale) echo $discount_price; else echo $product->getPrice(); ?></span>
		</a>
	</h4>
	<div class="ssm meta-links">
		<a href="<?php echo SAINT_URL; ?>shop/addtocart.<?php echo $product->getId(); ?>" class="link add-to-cart">Add to Cart</a>
		<a href="<?php echo SAINT_URL; ?>shop/addtocart.<?php echo $product->getId(); ?>/buynow.1" class="link buy-now">Buy Now</a>
	</div>
	<a href="<?php echo SAINT_URL; ?>shop/pid.<?php echo $product->getId(); ?>/">
		<?php Saint::getBlockImage($block, $id, "main-image",array('link'=>false,'max-width'=>180,'max-height'=>200)); ?>
	</a>
	<?php if ($sale): ?>
	<h6>Down from $<?php echo $product->getPrice(); ?>. Save <span class="discount"><?php echo round((1-$discount_price/$product->getPrice())*100,0); ?>%</span>!</h6>
	<?php endif; ?>
</div>
<?php } else { ?>
<h2>Product ID not found.</h2>
<?php } ?>