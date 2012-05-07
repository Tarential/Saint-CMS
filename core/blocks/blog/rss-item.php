<item>
<title><?php echo strip_tags(Saint::getBlockLabel($block,$id,"title",'',false)); ?></title>
<description><?php echo strip_tags(Saint::getBlockLabel($block,$id,"content",'',false)); ?></description>
<link><?php echo Saint::getBlockUrl($block,$id,"blog"); ?></link>
<guid><?php echo Saint::getBlockUrl($block,$id,"blog"); ?></guid>
<pubDate><?php echo date("D, d M Y H:i:s O", strtotime(Saint::getBlockSetting($block, $id, "postdate"))); ?></pubDate>
</item>