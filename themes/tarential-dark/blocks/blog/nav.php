<a href="<?php echo SAINT_URL . "/" . $page->getName(); ?>/feed" class="rss-feed-link">Subscribe via RSS</a>

<div class="about">
	<h4>Who is Tarential?</h4>
	<p>Tarential is Preston St. Pierre, a programmer from the Fraser Valley of beautiful British Columbia, Canada. He runs a web development company called <a href="http://aparadine.com" target="_blank">Aparadine</a>.</p>
	
	<h4>What does Tarential code?</h4>
	<p>Tarential codes websites for a day job, which means a lot of CSS and jQuery. He also makes the PHP/MySQL based rapid development framework and CMS <a href="http://saintcms.com" target="_blank">Saint</a>. He is currently learning Ruby on Rails.</p>
	
	<h4>What's with the dirt bikes?</h4>
	<p>I ride them. Sometimes they ride me, but that's usually only when disaster strikes.</p>
	
	<h4>Why do I care?</h4>
	<p>You probably don't. It's ok, I'm not offended. This is my website, though, so I'll keep ranting.</p>
</div>

<div id="blog-by-month-nav" class="nav">

	<?php
	$posts = Saint::getCollection("blog/post",array(
			"repeat" => 20,
			"order" => "DESC",
			"orderby" => "postdate",
			"paging" => false,
			"page_id" => $page->getId(),
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
		
		<h4><a href="<?php echo $post->getUrl(); ?>"><?php echo $post->get("title"); ?></a></h4>
			
	<?php endforeach; ?>
	
	<div id="blog-by-cat-nav" class="nav">
		<h3>Categories</h3>
		<?php foreach (Saint::getCategories() as $category): ?>
			<?php $blocks_in_cat = Saint_Model_Block::getBlocks("blog/post",array('category'=>$category)); ?>
			<?php if (sizeof($blocks_in_cat)): ?>
				<h4><a href="<?php echo SAINT_URL . "/" . $page->getName() . "/category/" . urlencode($category); ?>/"><?php echo $category; ?></a></h4>
			<?php endif; ?>
		<?php endforeach; ?>
	</div>

</div>