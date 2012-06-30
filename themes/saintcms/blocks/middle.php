	<script type="text/javascript">
	var _gaq = _gaq || [];
	_gaq.push(['_setAccount', 'UA-32414555-1']);
	_gaq.push(['_setDomainName', 'saintcms.com']);
	_gaq.push(['_trackPageview']);
	(function() {
		var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
		ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
		var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
	})();
	</script>
	</head>
	<body class="<?php echo Saint::getCurrentPage()->getName(); ?>">
		<?php if (Saint::getCurrentUser()->hasPermissionTo("admin-overlay")) Saint::includeBlock("admin/overlay",false); ?>
		<div id="wrapper">
			<div id="header">
				<a href="http://www.saintcms.com/" id="logo">&nbsp;</a>
				<div class="tagline">
					<p>CMS and eCommerce Software</p>
					<p>Designed for Developers</p>
				</div>
				<?php Saint::includeBlock("navigation/menu",false); ?>
			</div>
			<div id="content">
				<div class="content">