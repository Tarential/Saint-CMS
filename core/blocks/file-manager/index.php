<div id="saint-add-block-sidebar" class="saint-add-block-sidebar">
	<div id="saint-uploader">
		<h3 class="link">Upload Files</h3>
		<div id="filelist">No runtime found.</div>
		<br />
		<a id="pickfiles" href="#">[Select files]</a>
	</div>
	<div id="saint-file-info">
		<form>
			<?php echo Saint::genField("saint-file-mode","hidden","",array('value'=>'search')); ?>
			<?php echo Saint::genField("saint-file-id","hidden"); ?>
			<?php echo Saint::genField("saint-file-label","hidden"); ?>
			<?php echo Saint::genField("saint-file-sle","hidden"); ?>
			<?php echo Saint::genField("saint-file-title","text","Title:",array('static'=>true)); ?>
			<?php echo Saint::genField("saint-file-keywords","text","Keywords:",array('static'=>true)); ?>
			<?php echo Saint::genField("saint-file-description","textarea","Description:",array('static'=>true)); ?>
			<?php
				$options = array();
				foreach (Saint::getCategories() as $category)
					$options[$category] = $category;
			?>
			<?php echo Saint::genField("saint-file-categories[]","select","Categories: ",
				array('options'=>$options,'selected'=>array(),'multiple'=>true,'static'=>true)); ?>
			<div class="form-options">
				<span class="form-submit link">Search</span>
				<span class="form-cancel link">Reset</span>
			</div>
		</form>
	</div>
	<div id="sfm-close-button" class="close-button link">&nbsp;</div>
</div>
<div id="saint-file-manager-data" class="saint-add-block-data">
<div class="saint-admin-block-overlay"></div>
<div class="saint-loadable-content">
	<?php Saint::includeBlock("file-manager/list"); ?>
</div>
</div>
