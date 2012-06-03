<div id="saint-add-block-sidebar" class="saint-add-block-sidebar">
	<h1></h1>
	<ul class="options">
		<li id="saint-add-block-save" class="link">Save</li>
		<li id="saint-add-block-delete" class="link">Delete</li>
		<li id="saint-add-block-cancel" class="link">Cancel</li>
	</ul>
	<form id="saint-add-block-settings">
		<input type="hidden" name="saint-block-setting-saintname" id="saint-block-setting-saintname" value="<?php echo $page->addblockname; ?>" />
		<input type="hidden" name="saint-block-setting-enabled" id="saint-block-setting-enabled" value="1" />
		<?php
			$options = array();
			foreach (Saint::getAllCategories() as $category)
				$options[$category] = $category;
		?>
		<div>
		<label for="saint_edit_block_categories[]">Categories:</label>
		<?php echo Saint::genField("saint_edit_block_categories[]","select","Categories: ",
			array('options'=>$options,'selected'=>$page->addblock->getCategories(),'multiple'=>true)); ?></div>
		<?php foreach ($page->addblock->getAllSettings() as $key=>$val): ?>
			<?php if ($key == "id"): ?>
				<input type="hidden" name="saint-block-setting-id" id="saint-block-setting-id" value="<?php echo $val; ?>" />
			<?php elseif ($key != "enabled"): ?>
				<?php $name = "saint-block-setting-$key";?>
				<div><label for="<?php echo $name; ?>"><?php echo ucfirst($key); ?>:</label><input type="text" name="<?php echo $name; ?>" id="<?php echo $name; ?>" value="<?php echo $val; ?>" /></div>
			<?php endif; ?>
		<?php endforeach; ?>
	</form>
</div>
<div id="saint-add-block-data" class="saint-add-block-data">
	<div class="saint-new-block">
		<?php Saint::includeRepeatingBlock($page->addblockname,$page->addblockarguments); ?>
	</div>
</div>