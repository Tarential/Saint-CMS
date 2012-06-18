<div class="saint-pager">
	<?php if ($page->crbpstart >= 0): ?>
	<a href="<?php echo $page->crburl.$page->crbpstart; ?>" class="saint-left-arrow">&nbsp;</a>
	<?php endif; ?>
	
	<?php if ($page->crbmore): ?>
	<a href="<?php echo $page->crburl.$page->crbnstart; ?>" class="saint-right-arrow">&nbsp;</a>
	<?php endif; ?>
	
	<div class="saint-page-numbers">
	<?php for ($i = 0; $i < $page->crbnumpages; $i++): ?>
	<a href="<?php echo $page->crburl.$i; ?>"<?php if ($i == $page->crbnstart-1): ?> class="current"<?php endif; ?>><?php echo $i+1; ?></a>
	<?php if ($page->crbnumpages-1 > $i): ?> | <?php endif; ?>
	<?php endfor; ?>
	</div>
</div>