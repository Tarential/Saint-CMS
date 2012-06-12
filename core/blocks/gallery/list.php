<?php
$page = Saint::getCurrentPage();
if (isset($page->sfmarguments))
	$arguments = $page->sfmarguments;
else
	$arguments = array();
$args = $page->getArgs();
$files = array_values(Saint_Model_FileManager::getAllFiles($arguments));
$page->sfmtotal = sizeof($files);
if (isset($args['p']))
	$page->sfmcurpage = $args['p'];
else
	$page->sfmcurpage = 0;
if (isset($args['r']))
	$page->sfmperpage = $args['r'];
else
	$page->sfmperpage = 6;
$page->sfmnumpages = $page->sfmtotal / $page->sfmperpage;
?>
	<div class="sig-pager sig-top-pager">
		<?php Saint::includeBlock("gallery/pager",false); ?>
	</div>
	<div class="sig-preview-block">
		<div class="saint-new-block">
			<?php if (sizeof($files)): ?>
			<table id="saint-file-preview">
			<?php 
				$start = $page->sfmcurpage * $page->sfmperpage; 
				$finish = min($start + $page->sfmperpage,sizeof($files));
				$closed = true;
			?>
			<?php for ($i = $start; $i < $finish; $i++): ?>
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
				<a href="<?php echo $img->getUrl(); ?>" target="_blank"><img id="sig-<?php echo $files[$i]['id']; ?>" class="link" src="<?php echo $resizedurl ?>" /></a>
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
		<?php Saint::includeBlock("gallery/pager",false); ?>
	</div>