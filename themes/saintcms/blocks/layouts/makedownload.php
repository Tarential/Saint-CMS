<?php if (Saint::getCurrentUser()->hasPermissionTo()): ?>
<?php Saint::includeBlock("top",false); ?>
<?php Saint::includeBlock("middle",false); ?>

http://saintcms.com/shop/view.download/id.2/linkid.<?php echo Saint_Model_Shop::createDownload('2','100','72'); ?>

<?php Saint::includeBlock("bottom",false); ?> 
<?php else: ?>
<?php
$page = Saint::getCurrentPage();
$page->setTempLayout("system/404");
$page->render(); ?>
<?php endif; ?>