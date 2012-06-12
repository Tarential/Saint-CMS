<?php Saint::includeBlock("top"); ?>
<?php Saint::includeScript("jquery.validate.min"); ?>
<?php Saint::includeBlock("middle"); ?>

<?php 
$owner = new Saint_Model_User();
$owner->loadByUsername(Saint::getSiteOwner());
?>

<h1><?php echo Saint::getPageLabel("title","Contact Us"); ?></h1>
<div id="saint-contact-info">
<h3><?php echo SAINT_SITE_TITLE; ?></h3>
<?php if ($owner->getFirstName() != "" || $owner->getLastName() != ""): ?>
<p><?php echo $owner->getFirstName() . " " . $owner->getLastName(); ?></p>
<?php endif; ?>
<?php if ($owner->getPhoneNumber() != ""): ?>
<p><?php echo Saint::getPageLabel("phone","Call:"); ?> <?php echo Saint::formatPhoneNumber($owner->getPhoneNumber()); ?></p>
<?php endif; ?>
<p><?php echo Saint::getPageLabel("email","Email:"); ?> <a href="mailto:<?php echo $owner->getEmail(); ?>"><?php echo $owner->getEmail(); ?></a></p>
</div>

<?php Saint::includeBlock("contact/form"); ?>

<?php Saint::includeBlock("bottom"); ?>