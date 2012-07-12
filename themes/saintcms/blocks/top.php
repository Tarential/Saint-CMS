<!DOCTYPE HTML>
<html>
	<head>
		<title><?php echo $page->getTitle(); ?> - <?php echo Saint::getSiteTitle(); ?></title>
		<meta name="robots" content="<?php if ($page->allowsRobots()): ?>index,follow<?php else: ?>noindex,nofollow<?php endif; ?>" />
		<meta name="keywords" content="<?php echo implode(',',$page->getKeywords()); ?>" />
		<meta name="description" content="<?php echo $page->getDescription(); ?>" />
		<base href="<?php echo $page->getUrl(); ?>" />
		<link rel="canonical" href="<?php echo $page->getUrl(); ?>" />
		<script type="text/javascript" src="http://www.google.com/jsapi"></script>
		<script type="text/javascript">
			google.load("jquery", "1");
			var CLIENT_NONCE = '<?php echo Saint::getCurrentUser()->getNonce(); ?>';
		</script>
		<?php if (Saint::getCurrentUser()->hasPermissionTo("admin-overlay")) {
			Saint::includeScript("tinymce/jquery.tinymce");
			Saint::includeScript("saint"); 
		}
		Saint::includeStyle("saint");
		Saint::includeStyle("saintcms");
		Saint::includeScript("saintcms");
		?>