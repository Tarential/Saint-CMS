<?php Saint::includeBlock("top"); ?>

<?php Saint::includeBlock("middle"); ?>

<?php echo $page->getLabel(
	"content",
	"<p>This is a blank content panel. Select 'edit page' in the Saint menu then click this text to change it.</p>",
	array(
		"wysiwyg" => true,
	)
); ?>

<?php Saint::includeBlock("bottom"); ?>