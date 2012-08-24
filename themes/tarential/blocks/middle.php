	</head>
	<body>
		<?php if (Saint::getCurrentUser()->hasPermissionTo("admin-overlay")) Saint::includeBlock("admin/overlay"); ?>
		<?php if (sizeof($page->getErrors())): ?>
			<div class="saint-errors">
			<?php foreach ($page->getErrors() as $error): ?>
				<h3 class="error"><?php echo $error; ?></h3>
			<?php endforeach; ?>
			</div>
		<?php endif; ?>
		<?php /*<a href="<?php echo SAINT_URL; ?>" class="logo">&nbsp;</a>*/ ?>
		<?php Saint::includeBlock("navigation/top"); ?>
		<div class="nav-bg">&nbsp;</div>
		<div id="container">
			<div id="content">
