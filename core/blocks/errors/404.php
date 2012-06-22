<?php
Saint::getCurrentPage()->error = 'The page you selected could not be found. Please try the menu or <a href="'.SAINT_URL.'/contact">contact us</a> for further information.';
Saint::includeBlock("errors/error");
?>