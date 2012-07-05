<?php
$args = $page->getArgs();
?>
<?php if ($block->get("page-number") > 0): ?>
	<span class="link previous">
		<a href="<?php echo $page->getUrl()."/?p=".($block->get("page-number")-1); ?>">Previous</a>
	</span>
<?php endif; ?>
<?php if ($block->get("page-number") < $block->get("number-of-pages")-1): ?>
	<span class="link next">
		<a href="<?php echo $page->getUrl()."/?p=".($block->get("page-number")+1); ?>">Next</a>
	</span>
<?php endif; ?>
<div class="sig-page-numbers">
<?php for ($i = 0; $i < $block->get("number-of-pages"); $i++): ?>
<span class="sig-page-<?php echo $i; ?> link<?php if ($i == $block->get("page-number")): ?> current<?php endif; ?>">
	<a href="<?php echo $page->getUrl()."/?p=".$i; ?>"><?php echo $i+1; ?></a>
</span>
<?php if ($block->get("number-of-pages")-1 > $i): ?> | <?php endif; ?>
<?php endfor; ?>
</div>