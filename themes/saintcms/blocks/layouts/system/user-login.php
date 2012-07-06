<?php
if ($_SERVER['SERVER_PORT'] != '443') {
	header('Location: https://saintcms.com/login');
}
Saint::includeBlock("top"); ?>

<?php Saint::includeBlock("middle"); ?>

<?php Saint::includeBlock("user/login"); ?>

<?php Saint::includeBlock("bottom"); ?>