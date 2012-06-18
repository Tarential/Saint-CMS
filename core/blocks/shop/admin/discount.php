<div class="ssm-discount block-item">
	<div class="edit-button inline" id="<?php echo $block->getId(); ?>">
		<?php echo $block->get("name"); ?>
		<?php if ($block->get("type") == "flat"): ?>$<?php endif; ?>
		<?php echo $block->get("amount"); ?>
		<?php if ($block->get("type") == "percent"): ?>%<?php endif; ?> off!
		<span class="block-name hidden"><?php echo $block->getId(); ?></span>
	</div>
	<h6>Active: <?php echo $block->get("startdate"); ?> - <?php echo $block->get("enddate"); ?></h6>
	<h4><?php echo $block->get("filters"); ?></h4>
</div>