	</head>
	<body class="saint-page <?php echo $page->getName(); ?>">
		<?php Saint::includeBlock("admin/overlay"); ?>
		<?php if (sizeof($page->getErrors())): ?>
			<div class="saint-errors">
			<?php foreach ($page->getErrors() as $error): ?>
				<h3 class="error"><?php echo $error; ?></h3>
			<?php endforeach; ?>
			</div>
		<?php endif; ?>
		<?php Saint::includeBlock("navigation/top"); ?>
		<div id="container">
			<a href="<?php echo SAINT_URL; ?>" class="logo">&nbsp;</a>
			<?php Saint::includeBlock("sidebar"); ?>
			<div id="content">
