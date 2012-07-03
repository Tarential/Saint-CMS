<?php Saint::includeStyle("aparadine"); ?>
<script type="text/javascript">
  var _gaq = _gaq || [];
  _gaq.push(['_setAccount', 'UA-32414555-2']);
  _gaq.push(['_trackPageview']);

  (function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
  })();
</script>
	</head>
	<body class="dark <?php echo $page->getName(); ?>">
		<?php if (Saint::getCurrentUser()->hasPermissionTo("admin-overlay")) Saint::includeBlock("admin/overlay"); ?>
		<div id="header">
			<?php Saint::includeBlock("navigation/menu"); ?>
			<a href="<?php echo SAINT_URL; ?>" class="logo" title="Aparadine Software">&nbsp;</a>
		</div>
		<div id="container">
			<div id="content">