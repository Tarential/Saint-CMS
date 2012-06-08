<?php
$post = new Saint_Model_BlogPost();
$post->load($id);
?>
<item>
<title><?php echo strip_tags(Saint::getBlockLabel($block,$id,"title",'',false)); ?></title>
<description><?php echo strip_tags(Saint::getBlockLabel($block,$id,"content",'',false)); ?></description>
<link><?php echo $post->getUrl(); ?></link>
<guid><?php echo $post->getUrl(); ?></guid>
<pubDate><?php echo date("D, d M Y H:i:s O", strtotime($post->getPostDate())); ?></pubDate>
</item>