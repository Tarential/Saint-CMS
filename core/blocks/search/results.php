<div class="search-results">
<h1>Search Results</h1>
<?php
if (isset($page->searchresults) && sizeof($page->searchresults)) {
	foreach ($page->searchresults as $pid=>$result) {
		$rp = new Saint_Model_Page();
		if ($rp->loadById($pid)) {
			echo "<h3><a href=\"".$result[0]."\">".$rp->getTitle()."</a></h3>";
			foreach ($result[1] as $label) {
				echo "<div>".preg_replace('/('.$page->searchphrase.')/i','<span class="match">$1</span>',strip_tags(Saint::getLabel($label)))."</div>\n";
			}
		}
	}
} else { ?>
<p>Sorry, no results found. Try making your search phrase less specific or use the menu to find what you need.</p>
<?php } ?>
</div>