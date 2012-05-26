<?php
echo '<?xml version="1.0" encoding="ISO-8859-1"?>'; ?>
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
<channel>
<title><?php echo Saint::getSiteTitle(); ?></title>
<link><?php echo SAINT_URL; ?></link>
<atom:link href="<?php echo Saint::getBlogRssUrl(); ?>" rel="self" type="application/rss+xml" />
<description><?php echo Saint::getSiteDescription(); ?></description>
<language><?php echo SAINT_BLOG_LANG; ?></language>
	<copyright><?php echo SAINT_SITE_COPYRIGHT; ?></copyright><?php
$arguments = array(
	"repeat" => 5,
	"order" => "DESC",
	"orderby" => "id",
	"matches" => array(
		array("enabled","1"),
	),
);
Saint::includeRepeatingBlock("blog/post",$arguments,0,"blog/rss-item");
?>
</channel>
</rss>