<div class="ssm-discount block-item">
	<div class="edit-button inline" id="<?php echo $block->getId(); ?>">
		<?php echo Saint::getBlockSetting($block->getName(), $block->getId(), "name"); ?>
		<?php if (Saint::getBlockSetting($block->getName(), $block->getId(), "type") == "flat"): ?>$<?php endif; ?>
		<?php echo Saint::getBlockSetting($block->getName(), $block->getId(), "amount"); ?>
		<?php if (Saint::getBlockSetting($block->getName(), $block->getId(), "type") == "percent"): ?>%<?php endif; ?> off!
		<span class="block-name hidden"><?php echo $block->getId(); ?></span>
	</div>
	<h6>Active: <?php echo Saint::getBlockSetting($block->getName(), $block->getId(), "startdate"); ?> - <?php echo Saint::getBlockSetting($block->getName(), $block->getId(), "enddate"); ?></h6>
	<h4><?php echo Saint::getBlockSetting($block->getName(), $block->getId(), "filters"); ?></h4>
</div>