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
<div class="saint-product<?php if ($sale) echo " sale"; ?>" id="ssm-sku-<?php echo $product->getSku(); ?>">
	<h2>
		<?php echo $product->getName(); ?> - <span class="regprice">$<?php echo $product->getPrice(); ?></span>
		<?php if ($sale): ?>on sale for <span class="saleprice">$<?php echo $discount_price; ?></span><?php endif; ?>
	</h2>
	<?php Saint::getBlockImage($block, $id, "main-image"); ?>
	<?php echo Saint::getBlockLabel($block,$id,"description","Enter product description here..."); ?>
	<div class="ssm meta-links">
		<a href="<?php echo SAINT_URL; ?>/shop/addtocart.<?php echo $product->getId(); ?>" class="link add-to-cart">Add to Cart</a>
		<a href="<?php echo SAINT_URL; ?>/shop/addtocart.<?php echo $product->getId(); ?>/buynow.1" class="link buy-now">Buy Now</a>
	</div>
</div>
<?php } else { ?>
<h2>Product ID not found.</h2>
<?php } ?>