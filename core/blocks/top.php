<!DOCTYPE HTML>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
		<meta name="robots" content="<?php if ($page->allowsRobots()): ?>index,follow<?php else: ?>noindex,nofollow<?php endif; ?>" />
		<meta name="keywords" content="<?php
			if (sizeof($page->getKeywords()) > 0) echo implode(',',$page->getKeywords());
			else echo implode(',',Saint::getSiteKeywords()); ?>" />
		<meta name="description" content="<?php
			if ($page->getDescription() != "") echo $page->getDescription();
			else echo Saint::getSiteDescription(); ?>" />
		<base href="<?php echo $page->getUrl(); ?>" />
		<link rel="canonical" href="<?php echo $page->getUrl(); ?>" />
		<title><?php echo $page->getTitle(); ?> - <?php echo Saint::getSiteTitle(); ?></title>
		<script type="text/javascript" src="https://www.google.com/jsapi"></script>
		<script type="text/javascript">
			google.load("jquery", "1");
		</script>
		<?php if (Saint::getCurrentUser()->getId() || $page->getName() == "register" || $page->getName() == "contact"): ?>
			<script type="text/javascript" src="<?php echo SAINT_URL; ?>/core/scripts/tinymce/jquery.tinymce.js"></script>
			<?php Saint::includeScript("jquery.validate.min"); ?>
			<?php Saint::includeScript("saint"); ?>
		<?php endif; ?>
		<?php if (Saint::getCurrentUser()->hasPermissionTo("admin-overlay")): ?>
			<!-- Third party scripts for BrowserPlus runtime, Plupload -->
			<script type="text/javascript" src="https://bp.yahooapis.com/2.4.21/browserplus-min.js"></script>
			<script type="text/javascript" src="<?php echo SAINT_URL; ?>/core/scripts/plupload/plupload.full.js"></script>
		<?php endif; ?>
		<?php Saint::includeStyle("saint"); ?>
		