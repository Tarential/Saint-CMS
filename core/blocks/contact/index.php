<?php
$owner = new Saint_Model_User();
$owner->loadByUsername(Saint::getSiteOwner());
?>

<h1><?php echo $page->getTitle(); ?></h1>
<div id="saint-contact-info">
<h3><?php echo Saint::getSiteTitle(); ?></h3>
<?php if ($owner->getFirstName() != "" || $owner->getLastName() != ""): ?>
<p><?php echo $owner->getFirstName() . " " . $owner->getLastName(); ?></p>
<?php endif; ?>
<?php if ($owner->getPhoneNumber() != ""): ?>
<p><?php echo $page->getLabel("phone","Call:"); ?> <?php echo Saint::formatPhoneNumber($owner->getPhoneNumber()); ?></p>
<?php endif; ?>
<div><?php echo $page->getLabel("email","Email:"); ?> <a href="mailto:<?php echo $owner->getEmail(); ?>"><?php echo $owner->getEmail(); ?></a></div>
</div>

<?php Saint::includeBlock("contact/form"); ?>