<?php $owner = new Saint_Model_User(); $owner->loadById($block->getOwner()); ?>
<div class="comment">
	<?php echo $block->getLabel("comment","Enter your comment here..."); ?>
	<h6>Posted by <em><?php echo $owner->getUsername(); ?></em> on <?php echo date('F jS, Y \a\t g:ia',strtotime($block->getPostDate())); ?></h6>
</div>