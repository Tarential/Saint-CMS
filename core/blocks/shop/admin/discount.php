<div class="ssm-discount block-item">
	<div class="edit-button inline" id="<?php echo $id; ?>">
		<?php echo Saint::getBlockSetting($block, $id, "Name"); ?>
		<?php if (Saint::getBlockSetting($block, $id, "Type") == "flat"): ?>$<?php endif; ?>
		<?php echo Saint::getBlockSetting($block, $id, "Amount"); ?>
		<?php if (Saint::getBlockSetting($block, $id, "Type") == "percent"): ?>%<?php endif; ?> off!
		<span class="block-name hidden"><?php echo $id; ?></span>
	</div>
	<h6>Active: <?php echo Saint::getBlockSetting($block, $id, "StartDate"); ?> - <?php echo Saint::getBlockSetting($block, $id, "EndDate"); ?></h6>
	<h4><?php echo Saint::getBlockSetting($block, $id, "Filters"); ?></h4>
</div>