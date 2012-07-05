<div class="search-results">
<h1>Search Results</h1>
<?php $results = $page->get("search-results"); if ($results == "") $num_results = 0; else $num_results = sizeof($results); ?>
<?php if ($num_results): ?>
	<h2>Found <?php echo $num_results; ?> results for '<?php echo $page->get("search-phrase"); ?>'.</h2>
	<?php foreach ($results as $url=>$details): ?>
		<h3><a href="<?php echo $url; ?>"><?php echo $details[0]; ?></a></h3>
		<p><?php echo $details[1][0]; ?></p>
	<?php endforeach; ?>
<?php else: ?>
	<p>Sorry, no results found for '<?php echo $page->get("search-phrase"); ?>'. Try making your search phrase less specific or use the menu to find what you need.</p>
<?php endif; ?>
</div>