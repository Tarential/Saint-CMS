<?php
$page = Saint::getCurrentPage();
if (isset($page->sfmarguments))
	$arguments = $page->sfmarguments;
else
	$arguments = array();
$args = $page->getArgs();
if (isset($args['fid']) && $args['fid'] != 0) {
	$arguments['id'] = $args['fid'];
}
$files = Saint_Model_FileManager::getAllFiles($arguments);
$page->sfmtotal = sizeof($files);
if (isset($args['sfmcurpage']))
	$page->sfmcurpage = $args['sfmcurpage'];
else
	$page->sfmcurpage = 0;
if (isset($args['sfmperpage']))
	$page->sfmperpage = $args['sfmperpage'];
else
	$page->sfmperpage = 15;
$page->sfmnumpages = $page->sfmtotal / $page->sfmperpage;
?>
	<div id="sfm-top-pager" class="sfm-pager">
		<?php Saint::includeBlock("navigation/sfm-pager"); ?>
	</div>
	<div id="sfm-message"><?php if (isset($page->sfmmessage)) echo $page->sfmmessage; ?></div>
	<div id="sfm-status" class="hidden"><?php if (isset($page->sfmstatus)) echo $page->sfmstatus; ?></div>
	<div id="sfm-preview-block">
		<div class="saint-new-block">
			<?php if (sizeof($files)): ?>
			<table class="saint-file-preview">
			<?php 
				$start = $page->sfmcurpage * $page->sfmperpage; 
				$finish = min($start + $page->sfmperpage,sizeof($files));
				$closed = true;
			?>
			<?php for ($i = $start; $i < $finish; $i++): ?>
			<?php if ($i % 3 == 0): $closed = false; ?><tr><?php endif; ?>
			<td>
				<div class="sfm-editblock hidden">
					<img src="<?php echo $files[$i]['location']; ?>" />
				</div>
				<div class="details hidden">
					<p class="id"><?php echo $files[$i]['id']; ?></p>
					<p class="title"><?php echo $files[$i]['title']; ?></p>
					<p class="description"><?php echo $files[$i]['description']; ?></p>
					<p class="keywords"><?php echo $files[$i]['keywords']; ?></p>
					<p class="categories"><?php echo implode(',',$files[$i]['categories']); ?></p>
				</div>
				<?php
				$img = new Saint_Model_Image();
				$img->loadByLocation($files[$i]['location']);
				$resizeargs = array(
					'max-height' => 250,
					'max-width' => 180,
				);
				$resizedurl = $img->getResizedUrl($resizeargs);
				?>
				<img id="sfm-<?php echo $files[$i]['id']; ?>" class="link" src="<?php echo $resizedurl ?>" />
				<h3><?php echo $files[$i]['title']; ?></h3>
			</td>
			<?php if ($i % 3 == 2): $closed = true; ?></tr><?php endif; ?>
			<?php endfor; ?>
			<?php if (!$closed): ?></tr><?php endif; ?>
			</table>
			<?php else: ?>
				<p>Sorry, no files matched your selected criteria.</p>
			<?php endif; ?>
		</div>
	</div>
	<div id="sfm-bottom-pager" class="sfm-pager">
		<?php Saint::includeBlock("navigation/sfm-pager"); ?>
	</div>