<?php
header('Content-type: application/json');
/*
if (!isset($page->jsondata['actionlog'])) {
	$page->jsondata['actionlog'] = Saint::getActionLog();
}*/
echo json_encode($page->jsondata);

