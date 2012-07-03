<?php Saint::includeBlock("top",false); ?>

<?php Saint::includeBlock("middle",false); ?>

<div class="header-container">

	<img class="background" src="/themes/saintcms/images/mt.jpg" />
	
	<img class="logo" src="/themes/saintcms/images/saint-logo.png" />

</div>

<?php echo $page->getLabel("content","Homepage content goes here."); ?>

<?php Saint::includeBlock("bottom",false); ?>
