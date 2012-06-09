<?php
$post = new Saint_Model_BlogPost();
$post->load($id);
?>
<h4><a href="<?php echo $post->getUrl(); ?>"><?php echo Saint::getBlockLabel($block,$id,"title"); ?></a></h4>