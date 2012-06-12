<div id="sidebar">
	<?php Saint::includeBlock("search/form"); ?>
		<?php if (Saint::getCurrentPage()->getName() == "blog") { ?>
		<a href="<?php echo SAINT_URL; ?>/blog/feed" id="rss-feed-link">Subscribe via RSS</a>
		<div id="blog-by-cat-nav" class="nav">
			<h3>Categories</h3>
			<?php foreach (Saint::getAllCategories() as $category): ?>
			<h4><a href="<?php echo SAINT_URL . "/" .$page->getName(); ?>/category.<?php echo $category; ?>/"><?php echo $category; ?></a></h4>
			<?php endforeach; ?>
		</div>
		<div id="blog-by-month-nav" class="nav">
		<?php
			for ($i = 12; $i >= 1; $i--) {
				$start = date("Y-m-d H-i-s",strtotime(date("Y")."-$i-1 00:00:01"));
				$end = date("Y-m-d H-i-s",strtotime(date("Y")."-".($i+1)."-1 00:00:00"));
	
				$arguments = array(
					"repeat" => 2,
					"order" => "DESC",
					"orderby" => "postdate",
					"paging" => false,
					"matches" => array(
						array("enabled","1"),
						array("postdate",$start,">="),
						array("postdate",$end,"<"),
					),
					"view" => "navigation/blog-posts",
					"container" => false,
				);
				$blocks = Saint_Model_Block::getBlocks("blog/post",$arguments);
				if (is_array($blocks) && sizeof($blocks) > 0) {
					echo "<h3>".date("F",strtotime(date("Y")."-$i-1 00:00:01"))."</h3>";
					Saint::includeBlock("blog/post",$arguments);
				}
			} ?>
		</div>
		<?php } ?>
</div>