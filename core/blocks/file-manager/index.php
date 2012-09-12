<div id="saint-add-block-sidebar" class="saint-add-block-sidebar">
	<div id="saint-uploader">
		<h3 class="link">Upload Files</h3>
		<div id="filelist">No runtime found.</div>
		<br />
		<a id="pickfiles" href="#">[Select files]</a>
	</div>
	<div id="saint-file-info">
		<h3 class="search">Search Files</h3>
		<h3 class="edit">Edit File</h3>
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
	<div id="sfm-bulk-actions">
		<h3>Edit Selected Files</h3>
		<p>I want to:</p>
		<form>
			<?php echo Saint::genField("sfm-bulk-ids","hidden"); ?>
			<div class="action">
				<?php echo Saint::genField("sfm-bulk-edit-title","check","Set the title",array('static'=>true)); ?>
				<div class="options">
					<?php echo Saint::genField("sfm-bulk-title","text","New title:",array('static'=>true)); ?>
				</div>
			</div>
			<div class="action">
				<?php echo Saint::genField("sfm-bulk-edit-description","check","Set the description",array('static'=>true)); ?>
				<div class="options">
					<?php echo Saint::genField("sfm-bulk-description","textarea","New description:",array('static'=>true)); ?>
				</div>
			</div>
			<div class="action">
				<?php echo Saint::genField("sfm-bulk-add-keys","check","Add keywords",array('static'=>true)); ?>
				<div class="options">
					<?php echo Saint::genField("sfm-bulk-add-keywords","text","Keywords to add:",array('static'=>true)); ?>
					<p><i>Comma separated values: example,key,words,here</i></p>
				</div>
			</div>
			<div class="action">
				<?php echo Saint::genField("sfm-bulk-remove-keys","check","Remove keywords",array('static'=>true)); ?>
				<div class="options">
					<?php echo Saint::genField("sfm-bulk-remove-keywords","text","Keywords to remove:",array('static'=>true)); ?>
					<p><i>Comma separated values: example,key,words,here</i></p>
				</div>
			</div>
			<div class="action">
				<?php echo Saint::genField("sfm-bulk-add-cats","check","Add categories",array('static'=>true)); ?>
				<div class="options">
					<?php echo Saint::genField("sfm-bulk-add-categories[]","select","Categories to add: ",
						array('options'=>$options,'selected'=>array(),'multiple'=>true,'static'=>true)); ?>
				</div>
			</div>
			<div class="action">
				<?php echo Saint::genField("sfm-bulk-remove-cats","check","Remove categories",array('static'=>true)); ?>
				<div class="options">
					<?php echo Saint::genField("sfm-bulk-remove-categories[]","select","Categories to remove: ",
						array('options'=>$options,'selected'=>array(),'multiple'=>true,'static'=>true)); ?>
				</div>
			</div>
			<button type="submit">Save Changes</button>
			<button type="reset">Reset Form</button>
		</form>
	</div>
</div>
<div id="sfm-selector">&nbsp;</div>
<div id="saint-file-manager-data" class="saint-add-block-data">
	<div class="saint-admin-block-overlay"></div>
	<div class="saint-loadable-content">
		<?php Saint::includeBlock("file-manager/list"); ?>
	</div>
</div>
