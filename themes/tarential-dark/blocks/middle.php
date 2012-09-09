	<script type="text/javascript">
	
	  var _gaq = _gaq || [];
	  _gaq.push(['_setAccount', 'UA-32414555-4']);
	  _gaq.push(['_setDomainName', 'tarential.com']);
	  _gaq.push(['_trackPageview']);
	
	  (function() {
	    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
	    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
	    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
	  })();
	
	</script>
	</head>
	<body class="saint-page <?php echo $page->getName(); ?> dark">
		<?php Saint::includeBlock("admin/overlay"); ?>
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
