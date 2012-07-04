<?php
header("HTTP/1.0 404 Not Found");
$page->setTempTitle("404 - Page Not Found");
$page->addError('The page you requested could not be found. Please try the menu, see the <a href="'.SAINT_URL.'/sitemap">sitemap</a> or <a href="'.SAINT_URL.'/contact">contact us</a> for help.');
Saint::includeBlock("top");
?>

<?php Saint::includeBlock("middle"); ?>

<?php Saint::includeBlock("errors/404"); ?>

<?php Saint::includeBlock("bottom"); ?>