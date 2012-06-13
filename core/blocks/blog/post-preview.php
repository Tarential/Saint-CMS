<div class="blog-post preview">
	<h3>
		<span class="setting sbs-title block" title="Title">
			<?php if ($block->get("title") != "") echo $block->get("title"); else echo "Edit this title in the form to the right."; ?>
		</span>
	</h3>
	<h6>Posted on <span class="setting sbs-postdate" title="Post Date"><?php echo $block->getPostDate(); ?></span></h6>
	<div class="content"><?php echo Saint::getBlockLabel($block->getName(),$block->getId(),"content","This is your post content. Click here to edit this text."); ?></div>
</div>