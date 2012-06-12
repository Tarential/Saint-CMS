	</head>
	<body>
		<?php if (Saint::getCurrentUser()->hasPermissionTo("admin-overlay")) Saint::includeBlock("admin/overlay"); ?>
		<?php Saint::includeBlock("navigation/menu"); ?>
		<div id="container">
			<div id="logo">&nbsp;</div>
			<?php Saint::includeBlock("sidebar"); ?>
			<div id="content">