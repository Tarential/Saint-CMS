<?php
$posts = $page->getPosts();
if (sizeof($posts) == 1) {
	$page->setTempUrl($posts[0]->getUrl());
} else {
	$page->setTempUrl(SAINT_URL . "/" . $page->getName());
}
Saint::includeBlock("top"); ?>
<link rel="alternate" type="application/rss+xml" title="RSS" href="<?php echo SAINT_URL . "/" . $page->getName() . "/feed"; ?>" />

<script type="text/javascript">
$(document).ready(function() {
	$.getScript("http://s7.addthis.com/js/300/addthis_widget.js");
});
</script>

<?php Saint::includeBlock("middle"); ?>

<?php Saint::includeBlock("blog/index"); ?>

<?php Saint::includeBlock("bottom"); ?>