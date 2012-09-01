<?php $files = $page->getFiles(); ?>
	<div id="sfm-top-pager" class="sfm-pager">
		<?php Saint::includeBlock("file-manager/pager"); ?>
	</div>
	<div id="sfm-message">
	<?php foreach ($page->getStatus() as $status=>$message): ?>
		<p><?php echo $message; ?></p>
	<?php endforeach; ?>
	</div>
	<div id="sfm-status" class="hidden"><?php foreach ($page->getStatus() as $status=>$message) echo $status; ?></div>
	<div id="sfm-preview-block">
		<div class="saint-new-block">
			<?php if (sizeof($files)): ?>
			<div class="saint-file-preview">
			<?php $closed = true;	$img = new Saint_Model_Image();	?>
			<?php for ($i = 0; $i < sizeof($files); $i++): $img->loadByLocation($files[$i]['location']); ?>
				<div class="saint-file">
					<div class="sfm-editblock hidden">
						<div class="sfm-editblock-inner">
							<img src="<?php echo $files[$i]['location']; ?>" />
						</div>
					</div>
					<div class="details hidden">
						<p class="id"><?php echo $files[$i]['id']; ?></p>
						<p class="title"><?php echo $files[$i]['title']; ?></p>
						<p class="description"><?php echo $files[$i]['description']; ?></p>
						<p class="keywords"><?php echo $files[$i]['keywords']; ?></p>
						<p class="categories"><?php echo implode(',',$img->getCategories()); ?></p>
					</div>
					<?php
					$resizeargs = array(
						'max-height' => 250,
						'max-width' => 180,
					);
					$resizedurl = $img->getResizedUrl($resizeargs);
					?>
					<img id="sfm-<?php echo $files[$i]['id']; ?>" class="link" src="<?php echo $resizedurl ?>" />
					<h3><?php echo $files[$i]['title']; ?></h3>
				</div>
			<?php endfor; ?>
			</div>
			<?php else: ?>
				<p>Sorry, no files matched your selected criteria.</p>
			<?php endif; ?>
		</div>
	</div>
	<div id="sfm-bottom-pager" class="sfm-pager">
		<?php Saint::includeBlock("file-manager/pager"); ?>
	</div>