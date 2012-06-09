<?php 
$args = Saint::getCurrentPage()->getArgs();
$page = Saint::getCurrentPage();
$id = 0;

if (!empty($args['subids'])) {
	if ($args['subids'][0] == "feed") {
		$page->setTempLayout("blog/rss");
		$page->render();
		exit();
	}
	$post = new Saint_Model_BlogPost();
	$post->loadByUri($args['subids'][0]);
	$id = $post->getId();
}

if ($id) {
	$arguments = array(
		"matches" => array(
			array("enabled","1"),
			array("id",$id),
		),
	);
	$page->setTempTitle($post->get("title"));
	$page->setTempKeywords(explode(",",$post->get("keywords")));
	$page->setTempDescription($post->get("description"));
} else {
	$arguments = array(
		"repeat" => 2,
		"order" => "DESC",
		"orderby" => "postdate",
		"paging" => true,
		"matches" => array(
			array("enabled","1"),
		),
		"label" => "You haven't created any blog posts yet. Click 'edit page' in the Saint admin menu then click 'Add New Post' to create a post.",
	);
	if (isset($args['category'])) {
		$arguments['category'] = Saint_Model_Block::convertNameFromWeb($args['category']);
	}
}
Saint::includeBlock("top",false);
?>

<link rel="alternate" type="application/rss+xml" title="RSS" href="<?php echo SAINT_URL . "/" . $page->getName() . "/feed"; ?>" />
<script type="text/javascript" src="http://s7.addthis.com/js/300/addthis_widget.js"></script>
<?php Saint::includeBlock("middle",false); ?>

<?php Saint::includeRepeatingBlock("blog/post",$arguments); ?>

<?php Saint::includeBlock("bottom",false); ?>