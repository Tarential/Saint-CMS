<?php $owner = new Saint_Model_User(); $owner->loadById($block->getOwner()); ?>
<div class="comment">
	<h6>Posted by: <?php if ($owner->getId()) echo $owner->getUsername(); else echo "Guest" ?></h6>
	<?php echo $block->getLabel("comment","Enter your comment here..."); ?>
</div>