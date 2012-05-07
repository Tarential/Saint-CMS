<?php
class Saint_Model_Shop {
	
	public static function createDownload($productid,$retries = 5, $timeout = 48) {
		$spid = Saint::sanitize($productid,SAINT_REG_ID);
		$sretries = Saint::sanitize($retries,SAINT_REG_ID);
		$linkid = md5(time().$productid.rand(100,1000));
		$expires = date( 'Y-m-d H:i:s',strtotime('+'.Saint::sanitize($timeout).' hours'));
		try {
			mysql_query("INSERT INTO `st_shop_downloads` (`productid`,`linkid`,`remaining`,`expires`) VALUES ('$spid','$linkid','$sretries','$expires')");
			return $linkid;
		} catch (Exception $e) {
			Saint::logError("Unable to create temporary download link: ".$e->getMessage(),__FILE__,__LINE__);
			return 0;
		}
	}
	
	public static function decrementLink($productid,$linkid) {
		$spid = Saint::sanitize($productid,SAINT_REG_ID);
		$slinkid = Saint::sanitize($linkid);
		try {
			$info = Saint::getRow("SELECT `remaining`,`expires` FROM `st_shop_downloads` WHERE `productid`='$spid' AND `linkid`='$slinkid'");
			$remaining = $info[0];
			$expires = strtotime($info[1]);
			if ($expires < time()) {
				try {
					Saint::query("DELETE FROM `st_shop_downloads` WHERE `productid`='$spid' AND `linkid`='$slinkid'");
				} catch (Exception $f) {
					Saint::logError("Unable to delete expired product download link: ".$f->getMessage(),__FILE__,__LINE__);
				}
				return 0;
			} else {
				if ($remaining > 1) {
					$query = "UPDATE `st_shop_downloads` SET `remaining`=`remaining`-1 WHERE `productid`='$spid' AND `linkid`='$slinkid'";
				} else {
					$query = "DELETE FROM `st_shop_downloads` WHERE `productid`='$spid' AND `linkid`='$slinkid'";
				}
				try {
					Saint::query($query);
					return 1;
				} catch (Exception $f) {
					Saint::logError("Unable to decrement product download link: ".$f->getMessage(),__FILE__,__LINE__);
					return 0;
				}
			}
		} catch (Exception $e) {
			if ($e->getCode()) {
				Saint::logError("Unable to select product download link with id '$spid' and linkid '$slinkid': ".$e->getMessage(),__FILE__,__LINE__);
			}
			return 0;
		}
	}
}
