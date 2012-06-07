<?php Saint::includeBlock("top",false); ?>

<?php Saint::includeBlock("middle",false); ?>

<?php
$page = Saint::getCurrentPage();
$args = $page->getArgs();
if (isset($args['view']) && $args['view'] == "thanks") {
	Saint::includeBlock("shop/thanks");
} else {
	if (isset($args['pid']) && $args['pid'] != '') {
		$arguments = array(
			'matches' => array(array('id',$args['pid']),array('enabled',1)),
			'repeat' => 1,
			"label" => "Couldn't find product with id $args[pid]. Please try the menu to find your product or contact us for support.",
		); ?>
		<div id="saint-product-individual">
		<?php Saint::includeRepeatingBlock("shop/product",$arguments); ?>
		</div>
	<?php
	} else {
		$arguments = array(
			'matches' => array('enabled',1),
			'repeat' => 15,
			"label" => "You haven't created any shop products yet. Click 'edit page' in the Saint admin menu then click 'Add New Product' to create a product.",
		);
		Saint::includeRepeatingBlock("shop/product",$arguments,true,"shop/list");
	}
}
?>

<?php Saint::includeBlock("bottom",false); ?>