<?php
foreach ($page->searchresults as $pid=>$result) {
	$rp = new Saint_Model_Page();
	if ($rp->loadById($pid)) {
		echo "<h2><a href=\"".$result[0]."\">".$rp->getTitle()."</a></h2>";
		foreach ($result[1] as $label) {
			echo "<div>".preg_replace('/('.$page->searchphrase.')/i','<span class="match">$1</span>',strip_tags(Saint::getLabel($label)))."</div>\n";
		}
	}
}
