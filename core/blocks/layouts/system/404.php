<?php
header("HTTP/1.0 404 Not Found");
Saint::includeBlock("top");
?>

<?php Saint::includeBlock("middle"); ?>

<?php Saint::includeBlock("errors/404"); ?>

<?php Saint::includeBlock("bottom"); ?>