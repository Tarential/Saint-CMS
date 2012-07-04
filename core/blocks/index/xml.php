<?php echo '<?xml version="1.0" encoding="UTF-8"?>'."\n"; ?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
 xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">
<?php function displayItem($item) { ?>
	<url>
		<loc><?php echo $item[0]; ?></loc>
		<lastmod><?php echo date("Y-m-d",strtotime($item[2])); ?></lastmod>
	</url>
	<?php if (sizeof($item[3])) {
		foreach ($item[3] as $subitem) {
		 displayItem($subitem);
		}
	}
} ?>
<?php foreach (Saint::getIndex() as $i) {
	displayItem($i);
} ?>
</urlset>