<?php Saint::includeBlock("top",false); ?>

<?php Saint::includeBlock("middle",false); ?>

<?php
$arguments = array(
	'categories' => array('Saint'),
	'width' => '400',
	'height' => '200',
);
Saint::includeSlideshow($arguments);
?>

<?php Saint::includeBlock("bottom",false); ?>