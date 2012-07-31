<?php
class Saint_Controller_Download {
	public static function download($file_name) {
		$file = SAINT_SITE_ROOT."/downloads/".$file_name;
		if ($file_name != "" && file_exists($file)) {
			# Log the download then send the file
			new Saint_Model_Download($file_name,$_SERVER['REMOTE_ADDR'],$_SERVER['HTTP_USER_AGENT']);
			header('Content-Type: application/force-download');
			header('Content-Length:' . filesize($file));
			header("Content-Disposition: inline; filename=\"".$file_name."\"");
			$fp = fopen($file,"rb");
			fpassthru($fp);
			fclose($fp);
		} else {
			header("HTTP/1.0 404 Not Found");
			$page->addError("Unable to find file. If you were linked to this file, please contact the site administrator.");
			$page->setTempTitle("File Not Found");
			$page->setTempLayout("system/error");
			$page->render();
		}
	}
}
