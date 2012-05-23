<?php if (isset($_POST['saint-search-phrase'])) $value = Saint::sanitize($_POST['saint-search-phrase']);
else $value = ''; ?>
<form id="saint-search" method="post" action="/search">
	<label for="saint-search-phrase"><?php echo Saint::getLabel("search-label","Search"); ?></label>
	<input name="saint-search-phrase" class="text" type="text" value="<?php echo $value; ?>" class="search-phrase" />
	<input name="saint-search-submit" class="submit" type="submit" value="Go" class="search-submit" />
</form> 
