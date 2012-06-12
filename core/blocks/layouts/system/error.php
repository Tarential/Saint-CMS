<?php Saint::includeBlock("top"); ?>

<?php Saint::includeBlock("middle"); ?>

<?php if (isset($page->error)): ?>
	<h3 class="error"><?php echo $page->error; ?></h3>
<?php else: ?>
	<h3 class="error">There was a problem loading your page. Please <a href="<?php echo SAINT_URL; ?>/contact">contact us</a> for further information.</h3>
<?php endif; ?>

<?php Saint::includeBlock("bottom"); ?>