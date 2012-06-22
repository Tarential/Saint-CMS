<?php
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
		<div id="saint-product-individual" class="saint-block">
		<?php Saint::includeBlock("shop/product",$arguments); ?>
		</div>
	<?php
	} else {
		$arguments = array(
			'matches' => array('enabled',1),
			'repeat' => 15,
			'view' => "shop/list",
			"label" => "You haven't created any shop products yet. Click 'edit page' in the Saint admin menu then click 'Add New Product' to create a product.",
		);
		Saint::includeBlock("shop/product",$arguments);
	}
}
?>