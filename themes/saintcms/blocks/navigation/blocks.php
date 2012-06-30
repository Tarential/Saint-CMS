<?php

$saint = new Saint_Model_Product();
$saint->load(2);
$price = $saint->getPrice();
$discount_price = $saint->getDiscountPrice();
$name = $saint->getProductName();

if ($discount_price < $price) {
	$price_block = <<<EOT
			<div class="special">
				<span class="title"><a href="/purchase" class="buy-now">Special Offer</a></span>
				<span class="special-price"><a href="/purchase" class="buy-now">\$$discount_price</a></span>
				<span class="price"><a href="/purchase" class="buy-now">Regular \$$price</a></span>
			</div>
EOT;
} else {
	$price_block = <<<EOT
			<span class="title"><a href="/purchase" class="buy-now">Get Saint Now</a></span>
			<span class="price"><a href="/purchase" class="buy-now">\$$price</a></span>
EOT;
}

$primary_links = <<<EOT
	<div class="nav-block buy-now">
		<h2><a href="/purchase" class="buy-now">Buy Now</a></h2>
		<a href="/purchase" class="buy-now"><img src="/themes/saintcms/images/saint-box-left-small.jpg" /></a>
		<div class="info">
$price_block
			<p><a href="/purchase" class="buy-now">Buy $name</a></p>
		</div>
	</div>
	<div class="nav-block demo">
		<h2><a href="http://demo.saintcms.com/login" target="_blank">Try Saint</a></h2>
		<img src="/images/saint-demo.jpg" />
		<div class="content">
			<ul>
				<li class="title"><a href="http://demo.saintcms.com/" target="_blank">View the Demo</a></li>
				<li class="title"><a href="http://demo.saintcms.com/login" target="_blank">Login as Admin</a></li>
				<li>Username: demo</li>
				<li>Password: demo</li>
			</ul>
			<h6>Have any questions about Saint?</h6>
			<h6>Try the <a href="/documentation">docs</a>.</h6>
		</div>
	</div>
	<div class="nav-block documentation">
		<h2><a href="/documentation">Documentation</a></h2>
		<img src="/images/saint-docs.jpg" />
		<div class="content">
			<p>Start with the: <a href="/documentation/view.user-guide">User Guide.</a></p>
			<p>Want to learn more? Read the: <a href="/documentation/view.dev-guide">Developer Guide.</a></p>
			<p>Ready to create a Saint powered site? Here's the: <a href="http://docs.saintcms.com/" target="_blank">API reference.</a></p>
		</div>
	</div>
EOT;
$secondary_links = <<<EOT
	<div class="nav-block labels secondary">
		<h2><a href="/documentation/view.dev-guide#magicparlabel-15">Labels</a></h2>
		<div class="content">
			<ul>
				<li class="title">Labels are your friends.</li>
				<li><a href="/documentation/view.dev-guide#magicparlabel-15">Labels</a> are CMS-editable text areas which can be placed <em>anywhere within your template</em>.</li>
				<li>Label text can stick to <em>blocks</em>, <em>pages</em> or the <em>entire site</em>.</li>
				<li><em>Multilanguage support</em> is enabled for all label text.</li>
			</ul>
		</div>
	</div>
	<div class="nav-block nav-blocks secondary">
		<h2><a href="/documentation/view.dev-guide#magicparlabel-46">Blocks</a></h2>
		<div class="content">
			<ul>
				<li class="title">Repeating blocks make life easy.</li>
				<li>1. <a href="/documentation/view.dev-guide#magicparlabel-46">Create a template</a> for your data.</li>
				<li>2. Use <a href="/documentation/view.dev-guide#magicparlabel-15">Saint labels</a> instead of text.</li>
				<li>3. <a href="/documentation/view.dev-guide#magicparlabel-66">Insert the block</a> into your site.</li>
				<li>Now you can create new instances of your block template through the Saint interface.</li>
			</ul>
		</div>
	</div>
	<div class="nav-block shop secondary">
		<h2><a href="/documentation/view.user-guide#magicparlabel-20">Shop</a></h2>
			<div class="content">
				<ul>
					<li class="title">Have a product for sale?</li>
					<li>Saint comes with a <a href="/documentation/view.user-guide#magicparlabel-20">built in shop</a>.</li>
					<li>Offer your customers <a href="/documentation/view.user-guide#magicparlabel-24">limited time discounts</a>.</li>
					<li><a href="/documentation/view.user-guide#magicparlabel-22">Automatic electronic delivery</a> for purchased software products.</li>
					<li>Accept a wide variety of payment methods using <a href="http://www.paypal.com/" target="_blank">PayPal</a>.</li>
				</ul>
			</div>
	</div>
EOT;
?>
<div class="nav-column one">
<?php if (Saint::getCurrentPage()->getName() == "blog") { ?>
	<div id="blog-sidebar" class="nav-block">
		<h2><a href="/blog">Blog</a></h2>
		<div class="content">
			<?php Saint::includeBlock("search/form"); ?>
			<?php Saint::includeBlock("blog/nav"); ?>
		</div>
	</div>
<?php } else {
	echo $primary_links;
} ?>
</div>
<div class="nav-column two">
<?php if (Saint::getCurrentPage()->getName() == "blog") {
	echo $primary_links;
} else {
	echo $secondary_links;
} ?>
</div>
<?php if ($page->getName() == "purchase"): ?>
<script type="text/javascript">
$(document).on({
	'click': function(event) {
		$("#saint-paypal-buynow form").submit();
		return false;
	}
},'a.buy-now');
</script>
<?php endif; ?>