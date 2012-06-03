<?php
$page = Saint::getCurrentPage();
$args = $page->getArgs();
$url = SAINT_URL . "/" . $page->getName(); 
if (isset($args['r']))
	$url .= "/r.".$args['r'];
?>
<?php if ($page->sfmcurpage > 0): ?>
	<span class="link previous">
		<a href="<?php echo $url."/p.".($page->sfmcurpage-1); ?>">Previous</a>
	</span>
<?php endif; ?>
<?php if ($page->sfmcurpage < $page->sfmnumpages-1): ?>
	<span class="link next">
		<a href="<?php echo $url."/p.".($page->sfmcurpage+1); ?>">Next</a>
	</span>
<?php endif; ?>
<div class="sig-page-numbers">
<?php for ($i = 0; $i < $page->sfmnumpages; $i++): ?>
<span class="sig-page-<?php echo $i; ?>" class="link<?php if ($i == $page->sfmcurpage): ?> current<?php endif; ?>">
	<a href="<?php echo $url."/p.".$i; ?>"><?php echo $i+1; ?></a>
</span>
<?php if ($page->sfmnumpages-1 > $i): ?> | <?php endif; ?>
<?php endfor; ?>
</div>