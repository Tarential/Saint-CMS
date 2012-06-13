<div class="saint-product ssm-sku-<?php echo $block->getSku(); ?> preview">
	<h2>
		<span class="setting sbs-Name" title="Name"><?php
		if ($block->get("Name") == "") echo "Your product name will go here"; else echo $block->get("Name");
		?></span> - <span class="regprice">$<span class="setting sbs-Price" title="Price"><?php
		if ($block->getPrice() == "") echo "00.00"; else echo $block->getPrice();
		?></span></span>
	</h2>
	<div class="saint-image">
		<?php Saint::getBlockImage($block->getName(), $block->getId(), "main-image"); ?>
		<span class="details">Click this image to change it.</span>
	</div>
	<div class="content">
		<?php echo Saint::getBlockLabel($block->getName(),$block->getId(),"description","This is your product description. Click here to edit this text."); ?>
	</div>
</div>