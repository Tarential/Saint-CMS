<!DOCTYPE HTML>
<html>
	<head>
		<title>Saint - <?php echo $page->getTitle(); ?></title>
		<meta name="robots" content="<?php if ($page->allowsRobots()): ?>index,follow<?php else: ?>noindex,nofollow<?php endif; ?>" />
		<meta name="keywords" content="<?php echo implode(',',$page->getMetaKeywords()); ?>" />
		<meta name="description" content="<?php echo $page->getMetaDescription(); ?>" />
		<script type="text/javascript" src="http://www.google.com/jsapi"></script>
		<script type="text/javascript">
			google.load("jquery", "1");
		</script>
		<?php if (Saint::getCurrentUser()->hasPermissionTo("admin-overlay")): ?>
			<script type="text/javascript" src="/core/scripts/tinymce/jquery.tinymce.js"></script>
			<!-- Third party scripts for BrowserPlus runtime, Plupload -->
			<script type="text/javascript" src="http://bp.yahooapis.com/2.4.21/browserplus-min.js"></script>
			<script type="text/javascript" src="/core/scripts/plupload/plupload.full.js"></script>
		<?php endif; ?>
		<?php Saint::includeStyle("saint"); ?>
		<?php Saint::includeScript("saint"); ?>
		<?php Saint::includeScript("slides.min.jquery"); ?>