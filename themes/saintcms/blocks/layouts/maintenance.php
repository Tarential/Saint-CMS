<!DOCTYPE HTML>
<html>
	<head>
		<title><?php echo $page->getTitle(); ?></title>
		<meta name="robots" content="<?php if ($page->allowsRobots()): ?>index,follow<?php else: ?>noindex,nofollow<?php endif; ?>" />
		<meta name="keywords" content="<?php echo implode(',',$page->getKeywords()); ?>" />
		<meta name="description" content="<?php echo $page->getDescription(); ?>" />
		<?php Saint::includeStyle("saint"); ?>
		<?php Saint::includeScript("saint"); ?>
		<?php Saint::includeStyle("aparadine"); ?>
		<?php Saint::includeScript("jquery-1.7.1.min"); ?>
	</head>
	<body>
		<div id="container">
			<div id="logo">&nbsp;</div>
			<div id="content">
				You've located the home of <em>Saint Content Management System</em> (<b>st.cms</b>). Our product is still under construction and is scheduled for an open beta testing second quarter 2012. Check back then if you're interested.
			</div>
			<div style="clear:both;">&nbsp;</div>
		</div>
	</body>
</html>