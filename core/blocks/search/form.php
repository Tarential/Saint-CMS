<div class="saint-search-form">
	<?php if (isset($_POST['saint-search-phrase'])) $value = Saint::sanitize($_POST['saint-search-phrase']); else $value = ''; ?>
	<form class="saint-search" method="post" action="<?php echo SAINT_URL; ?>/search">
		<?php echo Saint::genField("saint-search-phrase","text","Search",array("value"=>$value)); ?>
		<input name="saint-search-submit" class="submit" type="submit" value="Go" />
	</form>
</div>