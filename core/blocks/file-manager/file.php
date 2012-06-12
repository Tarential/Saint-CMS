<?php
	$page = Saint::getCurrentPage();
	$file = $page->curfile;
?>
<div class="saint-file editable sfl-<?php echo Saint::convertNameToWeb($file->getName()); ?>">
	<a href="<?php echo $file->getLocation(); ?>">
	<img src="<?php echo $file->getIconUrl(); ?>" class="sfid-<?php echo $file->getId(); ?>" />
	</a>
	<h6><?php echo $file->getTitle(); ?></h6>
</div>