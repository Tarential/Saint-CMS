<?php Saint::includeBlock("top",false); ?>

<?php Saint::includeBlock("middle",false); ?>

<h1>Documentation</h1>

<p>Saint documentation comes in three parts:</p>

<ol>
	<li><a href="/documentation/?view=user-guide">User Guide (HTML)</a> <a href="/media/saint-userguide-1.3.pdf" target="_blank">(PDF)</a>: Site administration.</li>
	<li><a href="/documentation/?view=dev-guide">Developer Guide (HTML)</a> <a href="/media/saint-devguide-1.3.pdf" target="_blank">(PDF)</a>: Site creation.</li>
	<li><a href="http://docs.saintcms.com/" target="_blank">API Reference (HTML):</a> Full PHPDoc reference to Saint classes and functions.</li>
</ol>

<?php
if (Saint::getCurrentPage()->getArg('view') == "dev-guide") {
	Saint::includeBlock("documentation/dev-guide");
} else {
	Saint::includeBlock("documentation/user-guide");
}
?>

<?php Saint::includeBlock("bottom",false); ?>
