<?php
$products = $page->getFiles();
if (sizeof($products) == 1) {
	$page->setTempUrl($products[0]->getUrl());
} else {
	$page->setTempUrl(SAINT_URL . "/" . $page->getName());
}
Saint::includeBlock("top"); ?>

<?php Saint::includeBlock("middle"); ?>

<?php Saint::includeBlock("shop/index"); ?>

<?php Saint::includeBlock("bottom"); ?>