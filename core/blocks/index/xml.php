<?php echo '<?xml version="1.0" encoding="UTF-8"?>'."\n"; ?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
 xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">
<?php foreach (Saint::getIndex() as $i): ?>
	<url>
		<loc><?php echo $i[0]; ?></loc>
		<lastmod><?php echo date("Y-m-d",strtotime($i[2])); ?></lastmod>
	</url>
<?php endforeach; ?>
</urlset>