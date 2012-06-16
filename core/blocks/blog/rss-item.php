<item>
<title><?php echo strip_tags($block->get("title")); ?></title>
<description><?php echo strip_tags($block->getLabel("content",'',false)); ?></description>
<link><?php echo $block->getUrl(); ?></link>
<guid><?php echo $block->getUrl(); ?></guid>
<pubDate><?php echo date("D, d M Y H:i:s O", strtotime($block->getPostDate())); ?></pubDate>
</item>