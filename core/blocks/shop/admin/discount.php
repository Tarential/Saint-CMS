<div class="ssm-discount block-item">
	<div class="edit-button inline" id="<?php echo $block->getId(); ?>">
		<?php echo Saint::getBlockSetting($block->getName(), $block->getId(), "Name"); ?>
		<?php if (Saint::getBlockSetting($block->getName(), $block->getId(), "Type") == "flat"): ?>$<?php endif; ?>
		<?php echo Saint::getBlockSetting($block->getName(), $block->getId(), "Amount"); ?>
		<?php if (Saint::getBlockSetting($block->getName(), $block->getId(), "Type") == "percent"): ?>%<?php endif; ?> off!
		<span class="block-name hidden"><?php echo $block->getId(); ?></span>
	</div>
	<h6>Active: <?php echo Saint::getBlockSetting($block->getName(), $block->getId(), "StartDate"); ?> - <?php echo Saint::getBlockSetting($block->getName(), $block->getId(), "EndDate"); ?></h6>
	<h4><?php echo Saint::getBlockSetting($block->getName(), $block->getId(), "Filters"); ?></h4>
</div>