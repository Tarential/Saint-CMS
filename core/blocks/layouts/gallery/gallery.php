<?php Saint::includeBlock("top"); ?>

<?php Saint::includeBlock("middle"); ?>

<?php
$arguments = array(
	"categories" => array('Saint'),
	"label" => "This gallery will display all images which are in the category 'Saint'. Open the file manager in the Saint menu to edit image categories.",
);
Saint::includeGallery($arguments);
?>

<?php Saint::includeBlock("bottom"); ?>