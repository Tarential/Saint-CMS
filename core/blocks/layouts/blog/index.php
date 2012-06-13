<?php
$page = Saint::getCurrentPage();
Saint::includeBlock("top");
?>
<link rel="alternate" type="application/rss+xml" title="RSS" href="<?php echo SAINT_URL . "/" . $page->getName() . "/feed"; ?>" />
<base href="<?php echo SAINT_URL . "/" . $page->getName(); ?>" />
<script type="text/javascript" src="http://s7.addthis.com/js/300/addthis_widget.js"></script>

<?php Saint::includeBlock("middle"); ?>

<?php Saint::includeBlock("blog/post",$page->getPostArgs()); ?>

<?php Saint::includeBlock("bottom"); ?>