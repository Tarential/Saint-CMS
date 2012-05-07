<?php Saint::includeBlock("top",false); ?>

<?php Saint::includeBlock("middle",false); ?>

<?php if (isset($page->error)): ?>
	<h3 class="error"><?php echo $page->error; ?></h3>
<?php else: ?>
	<h3 class="error">There was a problem loading your page. Please <a href="/contact">contact us</a> for further information.</h3>
<?php endif; ?>

<?php Saint::includeBlock("bottom",false); ?>