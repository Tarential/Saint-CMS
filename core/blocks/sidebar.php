<div id="sidebar">
	<?php Saint::includeBlock("search/form"); ?>
	<?php if (Saint::getCurrentPage()->getName() == "blog") Saint::includeBlock("blog/nav"); ?>
</div>