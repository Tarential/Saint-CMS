<?php Saint::includeBlock("top",false); ?>

<?php Saint::includeBlock("middle",false); ?>

<?php
$arguments = array(
	"categories" => array('Saint'),
	"width" => '400',
	"height" => '200',
	"label" => "This slideshow will display all images which are in the category 'Saint'. Open the file manager in the Saint menu to edit image categories.",
);
Saint::includeSlideshow($arguments);
?>

<?php Saint::includeBlock("bottom",false); ?>