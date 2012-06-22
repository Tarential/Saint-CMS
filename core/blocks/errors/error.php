<?php if (isset($page->error)): ?>
	<h3 class="error"><?php echo $page->error; ?></h3>
<?php else: ?>
	<h3 class="error">There was a problem loading the page. Please try our menu or <a href="<?php echo SAINT_URL; ?>/contact">contact us</a> for assistance.</h3>
<?php endif; ?>