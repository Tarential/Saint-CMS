<div class="saint-pager">
	<?php if ($page->get("crb-pstart") >= 0): ?>
	<a href="<?php echo $page->get("crb-url").$page->get("crb-pstart"); ?>" class="saint-left-arrow">&nbsp;</a>
	<?php endif; ?>
	
	<?php if ($page->get("crb-more")): ?>
	<a href="<?php echo $page->get("crb-url").$page->get("crb-nstart"); ?>" class="saint-right-arrow">&nbsp;</a>
	<?php endif; ?>
	
	<div class="saint-page-numbers">
	<?php for ($i = 0; $i < $page->get("crb-number-of-pages"); $i++): ?>
	<a href="<?php echo $page->get("crb-url").$i; ?>"<?php if ($i == $page->get("crb-nstart")-1): ?> class="current"<?php endif; ?>><?php echo $i+1; ?></a>
	<?php if ($page->get("crb-number-of-pages")-1 > $i): ?> | <?php endif; ?>
	<?php endfor; ?>
	</div>
</div>