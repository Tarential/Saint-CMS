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
		<?php if (Saint::getCurrentUser()->hasPermissionTo("admin-overlay")) {
			Saint::includeScript("tinymce/jquery.tinymce");
			Saint::includeScript("saint"); 
		}
		Saint::includeStyle("saint");
		Saint::includeStyle("aparadine");
		Saint::includeScript("aparadine");
		?>