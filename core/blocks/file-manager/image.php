<?php
	$page = Saint::getCurrentPage();
	$image = $page->curfile;
?>
<div class="saint-image editable" id="sfl-<?php echo Saint::convertNameToWeb($image->getName()); ?>">
	<?php if ($image->linkToFull()): ?><a href="<?php echo $image->getUrl(); ?>"><?php endif; ?>
	<img src="<?php echo $image->getIconUrl(); ?>" id="sfid-<?php echo $image->getId(); ?>" class="sfl-image" />
	<?php if ($image->linkToFull()): ?></a><?php endif; ?>
	<h6 class="title"><?php echo $image->getTitle(); ?></h6>
</div>