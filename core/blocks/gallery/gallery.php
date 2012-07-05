<?php $files = $page->getFiles(); ?>
	<div class="sig-pager sig-top-pager">
		<?php Saint::includeBlock("gallery/pager",array('repeat'=>1,'blocks'=>array($block),'container'=>false)); ?>
	</div>
	<div class="sig-preview-block">
		<div class="saint-new-block">
			<?php if (sizeof($files)): ?>
			<table id="saint-file-preview">
			<?php $closed = true; for ($i = 0; $i < sizeof($files); $i++): ?>
			<?php if ($i % 3 == 0): $closed = false; ?><tr><?php endif; ?>
			<td>
				<?php
				$img = new Saint_Model_Image();
				$img->loadByLocation($files[$i]['location']);
				$resizeargs = array(
					'max-height' => 250,
					'max-width' => 180,
				);
				$resizedurl = $img->getResizedUrl($resizeargs);
				?>
				<a href="<?php echo $img->getUrl(); ?>" target="_blank">
					<img id="sig-<?php echo $files[$i]['id']; ?>" class="link" src="<?php echo $resizedurl ?>" alt="<?php echo $img->getTitle(); ?>" title="<?php echo $img->getDescription(); ?>" />
				</a>
				<h3><?php echo $files[$i]['title']; ?></h3>
			</td>
			<?php if ($i % 3 == 2): $closed = true; ?></tr><?php endif; ?>
			<?php endfor; ?>
			<?php if (!$closed): ?></tr><?php endif; ?>
			</table>
			<?php elseif (isset($arguments['label'])): ?>
			<?php echo $arguments['label']; ?>
			<?php else: ?>
				<p>Sorry, no files matched your selected criteria.</p>
			<?php endif; ?>
		</div>
	</div>
	<div class="sig-pager sig-bottom-pager">
		<?php Saint::includeBlock("gallery/pager",array('repeat'=>1,'blocks'=>array($block),'container'=>false)); ?>
	</div>