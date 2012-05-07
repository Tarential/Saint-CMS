<?php Saint::includeBlock("top",false); ?>

<?php Saint::includeBlock("middle",false); ?>

<?php
$arguments = array(
	'categories' => array('Saint'),
);
Saint::includeGallery($arguments);
?>

<?php Saint::includeBlock("bottom",false); ?>