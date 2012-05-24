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
		); ?>
		<div id="saint-product-individual">
		<?php Saint::includeRepeatingBlock("shop/product",$arguments); ?>
		</div>
	<?php
	} else {
		$arguments = array(
			'matches' => array('enabled',1),
			'repeat' => 15,
		);
		Saint::includeRepeatingBlock("shop/product",$arguments,true,"shop/list");
	}
}
?>

<?php Saint::includeBlock("bottom",false); ?>