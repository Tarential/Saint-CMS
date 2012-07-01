	</head>
	<body class="dark <?php echo $page->getName(); ?>">
		<?php if (Saint::getCurrentUser()->hasPermissionTo("admin-overlay")) Saint::includeBlock("admin/overlay"); ?>
		<div id="header">
			<?php Saint::includeBlock("navigation/menu"); ?>
			<a href="<?php echo SAINT_URL; ?>" class="logo" title="Aparadine Software">&nbsp;</a>
		</div>
		<div id="container">
			<div id="content">