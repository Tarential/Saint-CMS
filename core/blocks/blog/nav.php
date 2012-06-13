<a href="<?php echo SAINT_URL; ?>/blog/feed" id="rss-feed-link">Subscribe via RSS</a>

<div id="blog-by-month-nav" class="nav">

	<?php
	$posts = Saint::getCollection("blog/post",array(
			"repeat" => 20,
			"order" => "DESC",
			"orderby" => "postdate",
			"paging" => false,
			"matches" => array(
				array("enabled","1"),
			),
		)
	);
	$lastmonth = 13;
	?>
	
	<?php foreach ($posts as $post): $curmonth = date('m',strtotime($post->get("postdate"))); ?>
	
		<?php if ($curmonth < $lastmonth): $lastmonth = $curmonth; ?>
		
			<h3><?php echo date('F',strtotime($post->get("postdate"))); ?></h3>
		
		<?php endif; ?>
		
		<h5><a href="<?php echo $post->getUrl(); ?>"><?php echo $post->get("title"); ?></a></h5>
			
	<?php endforeach; ?>
	
	<div id="blog-by-cat-nav" class="nav">
		<h3>Categories</h3>
		<?php foreach (Saint::getAllCategories() as $category): ?>
			<h5><a href="<?php echo SAINT_URL . "/" .$page->getName(); ?>/category.<?php echo $category; ?>/"><?php echo $category; ?></a></h5>
		<?php endforeach; ?>
	</div>

</div>