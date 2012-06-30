<?php
if ($_SERVER['SERVER_PORT'] != '443') {
	header('Location: https://saintcms.com/login');
}
?><?php Saint::includeBlock("top",false); ?>

<?php Saint::includeBlock("middle",false); ?>

<?php Saint::includeBlock("user/login"); ?>

<?php Saint::includeBlock("bottom",false); ?>