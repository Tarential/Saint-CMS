<?php Saint::includeBlock("top"); ?>

<?php Saint::includeBlock("middle"); ?>

<?php if (isset($page->error)): ?>
	<h3 class="error"><?php echo $page->error; ?></h3>
<?php else: ?>
	<h3 class="error">The page you selected could not be found. Please try the menu or <a href="<?php echo SAINT_URL; ?>/contact">contact us</a> for further information.</h3>
<?php endif; ?>

<?php Saint::includeBlock("bottom"); ?>