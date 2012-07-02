<?php
$owner = new Saint_Model_User();
$owner->loadByUsername(Saint::getSiteOwner());
?>

<h1><?php echo $page->getTitle(); ?></h1>

<div class="saint-contact-form">
<?php Saint::includeBlock("contact/form"); ?>
</div>