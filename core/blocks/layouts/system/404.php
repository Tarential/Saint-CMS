<?php Saint::includeBlock("top",false); ?>

<?php Saint::includeBlock("middle",false); ?>

<?php if (isset($page->error)): ?>
	<h3 class="error"><?php echo $page->error; ?></h3>
<?php else: ?>
	<h3 class="error">The page you selected could not be found. Please try the menu or <a href="/contact">contact us</a> for further information.</h3>
<?php endif; ?>

<?php Saint::includeBlock("bottom",false); ?>