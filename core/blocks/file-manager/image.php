<?php
	$page = Saint::getCurrentPage();
	$image = $page->curfile;
?>
<div class="saint-image editable width-<?php echo $image->getWidth(); ?> height-<?php echo $image->getHeight();
?>" id="sfl-<?php echo Saint::convertNameToWeb($image->getName()); ?>">
	<?php if ($image->linkToFull()): ?><a href="<?php echo $image->getUrl(); ?>"><?php endif; ?>
	<img src="<?php echo $image->getResizedUrl(); ?>" id="sfid-<?php echo $image->getId(); ?>" class="sfl-image" />
	<?php if ($image->linkToFull()): ?></a><?php endif; ?>
	<?php if ($image->showTitle()): ?>
	<h6 class="title"><?php echo $image->getTitle(); ?></h6>
	<?php endif; ?>
</div>