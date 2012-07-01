	</head>
	<body>
		<?php if (Saint::getCurrentUser()->hasPermissionTo("admin-overlay")) Saint::includeBlock("admin/overlay"); ?>
		<?php Saint::includeBlock("navigation/menu"); ?>
		<div id="container">
			<a href="<?php echo SAINT_URL; ?>" class="logo">&nbsp;</a>
			<?php Saint::includeBlock("sidebar"); ?>
			<div id="content">