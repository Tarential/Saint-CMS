<?php 
$args = Saint::getCurrentPage()->getArgs();
if (isset($args['single'])) {
	$arguments = array(
		"matches" => array(
			array("enabled","1"),
			array("id",$args['single']),
		),
	);
	Saint::getCurrentPage()->setTempTitle(Saint_Model_Block::getBlockSetting("blog/post",$args['single'],"seo-title"));
	Saint::getCurrentPage()->setTempMetaKeywords(explode(",",Saint_Model_Block::getBlockSetting("blog/post",$args['single'],"seo-keywords")));
	Saint::getCurrentPage()->setTempMetaDescription(Saint_Model_Block::getBlockSetting("blog/post",$args['single'],"seo-description"));
} else {
	$arguments = array(
		"repeat" => 2,
		"order" => "DESC",
		"orderby" => "postdate",
		"paging" => true,
		"matches" => array(
			array("enabled","1"),
		),
	);
	if (isset($args['category'])) {
		$arguments['category'] = Saint_Model_Block::convertNameFromWeb($args['category']);
	}
}
Saint::includeBlock("top",false);
?>
<script type="text/javascript" src="http://s7.addthis.com/js/300/addthis_widget.js"></script>
<?php Saint::includeBlock("middle",false); ?>

<?php

Saint::includeRepeatingBlock("blog/post",$arguments);
?>

<?php Saint::includeBlock("bottom",false); ?>