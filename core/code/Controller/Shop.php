<?php
class Saint_Controller_Shop {
	
	public static function download($productid,$linkid = null) {
		$page = Saint::getCurrentPage();
		if (Saint_Model_Shop::decrementLink($productid,$linkid)) {
			$filename = Saint::getBlockSetting("shop/product",$productid,"File");
			$file = SAINT_SITE_ROOT."/restricted/".$filename;
			if (file_exists($file)) {
				$page->setTempLayout("system/system");
				header('Content-Type: application/force-download');
				header('Content-Length:' . filesize($file));
				header("Content-Disposition: inline; filename=\"".$filename."\"");
				$fp = fopen($file,"rb");
				fpassthru($fp);
				fclose($fp);
			} else {
				Saint::logError("Unable to find file '$filename'.",__FILE__,__LINE__);
				$page->error = "Unable to find file associated with the given ID. If you were linked to this file, please contact the site administrator.";
				$page->setTempLayout("system/error");
			}
		} else {
			$page->error = "Sorry, but that download link is inactive. If you were unable to download a product you purchased, please contact us for support.";
			$page->setTempLayout("system/error");
		}
	}
}
