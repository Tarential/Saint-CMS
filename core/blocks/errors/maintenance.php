<!DOCTYPE HTML>
<html>
	<head>
		<title><?php echo $page->getTitle(); ?></title>
		<meta name="robots" content="<?php if ($page->allowsRobots()): ?>index,follow<?php else: ?>noindex,nofollow<?php endif; ?>" />
		<meta name="keywords" content="<?php echo implode(',',$page->getMetaKeywords()); ?>" />
		<meta name="description" content="<?php echo $page->getMetaDescription(); ?>" />
		<?php Saint::includeStyle("saint"); ?>
		<?php Saint::includeScript("saint"); ?>
	</head>
	<body>
		<div id="container">
			<div id="logo">&nbsp;</div>
			<div id="maintenance">
				Welcome. Our site is currently under construction but we hope you'll check back soon.
			</div>
			<div style="clear:both;">&nbsp;</div>
		</div>
	</body>
</html>