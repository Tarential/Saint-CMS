<?php $owner = new Saint_Model_User(); $owner->loadById($block->getOwner()); ?>
<div class="blog-post">
	<h3><a href="<?php echo $block->getUrl(); ?>"><?php echo $block->get("title"); ?></a></h3>
	<h6>Posted by <em><?php echo $owner->getUsername(); ?></em> on <?php echo date('F jS, Y \a\t g:ia',strtotime($block->getPostDate())); ?></h6>
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
	<div class="comments sbn-blog_comment saint-block repeating parent-block-<?php echo $block->getId(); ?>">
		<div class="add-button inline">Add New Comment</div>
		<?php $block->includeBlock("blog/comment",array('matches'=>array('enabled','1'),'repeat' => 100,'label'=>'')); ?>
	</div>
	<script type="text/javascript">
		var loaded = false;
		$(document).on({
			'click': function(event) {
				if (typeof window.Saint === "undefined" && loaded === false) {
					loaded = true;
					$.getScript(SAINT_URL+"/core/scripts/saint.js")
					.done(function(script, textStatus) {
					  $(event.currentTarget).trigger("click");
					})
					.fail(function(jqxhr, settings, exception) {
						loaded = false;
					  alert("Sorry, there was an error loading the Saint client. Please try again later.");
					});
				}
			}
		},'.saint-block .add-button');
	</script>
</div>
