<?php $block = $page->getEditBlock(); ?>
<div id="saint-add-block-sidebar" class="saint-add-block-sidebar">
	<ul class="options">
		<li id="saint-add-block-save" class="link">Save</li>
		<li id="saint-add-block-delete" class="link">Delete</li>
		<li id="saint-add-block-cancel" class="link">Cancel</li>
	</ul>
	<form id="saint-add-block-settings">
		<?php echo Saint::genField("saint-block-setting-saintname","hidden","",array("value"=>$block->getName())); ?>
		<?php echo Saint::genField("saint-block-setting-enabled","hidden","",array("value"=>1)); ?>
		<?php /*
			$options = array();
			foreach (Saint::getCategories() as $category)
				$options[$category] = $category;
			$data = array(
				"options" => $options,
				"static" => true,
				"multiple" => true,
			); */
		?>
		<?php echo $block->renderCategories(); ?>
		<?php foreach ($block->getAllSettings() as $key=>$val): ?>
			<?php echo $block->renderInput($key); ?>
		<?php endforeach; ?>
	</form>
</div>
<div id="saint-add-block-data" class="saint-add-block-data editing">
	<div class="saint-new-block">
		<?php $block->renderPreview(); ?>
	</div>
</div>