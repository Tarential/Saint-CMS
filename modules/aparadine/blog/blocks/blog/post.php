<div class="blog-post">
	<h3><a href="<?php echo $block->getUrl(); ?>"><?php echo $block->get("title"); ?></a></h3>
	<h6>Posted on <?php echo $block->getPostDate(); ?></h6>
	<div class="content"><?php echo $block->getLabel("content","This is your post content. Click here to edit this text."); ?></div>
	<!-- AddThis Button BEGIN -->
	<div class="addthis_toolbox addthis_default_style " addthis:url="<?php echo $block->getUrl(); ?>">
	<a href="http://www.addthis.com/bookmark.php?v=300&amp;pubid=xa-4f03d10a66bfde1c" class="addthis_button_compact">Share</a>
	<span class="addthis_separator">|</span>
	<a class="addthis_button_preferred_1"></a>
	<a class="addthis_button_preferred_2"></a>
	<a class="addthis_button_preferred_3"></a>
	<a class="addthis_button_preferred_4"></a>
	<a class="addthis_button_preferred_5"></a>
	<a class="addthis_button_preferred_6"></a>
	<a class="addthis_button_preferred_7"></a>
	<a class="addthis_button_preferred_8"></a>
	</div>
	<!-- AddThis Button END -->
</div>
