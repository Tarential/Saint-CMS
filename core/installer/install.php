<?php
global $args;
function setInstalled($owner) {
	try {
		if (mysql_num_rows(mysql_query("SELECT `id` FROM `st_config`"))) {
			try {
				Saint::query("UPDATE `st_config` SET `version`=".SAINT_VERSION.",`owner`='$owner'");
			} catch (Exception $e) {
				Saint::logError($e->getMessage(),__FILE__,__LINE__);
			}
		} else {
			try {
				Saint::query("INSERT INTO `st_config` (`version`,`owner`) VALUES (".SAINT_VERSION.",'$owner')");
			} catch (Exception $e) {
				Saint::logError($e->getMessage(),__FILE__,__LINE__);
			}
		}
		include SAINT_SITE_ROOT . "/core/installer/complete.php";
	} catch (Exception $e) {
		$error = "Couldn't complete installation: " . $e->getMessage();
		include SAINT_SITE_ROOT . "/core/installer/error.php";
	}
}

$installing = true;

include SAINT_SITE_ROOT . "/core/installer/header.php";

if (phpversion() < 5) {
	echo "Sorry, Saint requires PHP version 5.0 or greater and we detected PHP version ".phpversion().".";
} else {

	if (isset($args['saint-eula-accept']) && $args['saint-eula-accept'] == "yes") {
		$_SESSION['saint-eula-accept'] = "yes";
	}
	
	if (!isset($_SESSION['saint-eula-accept']) || $_SESSION['saint-eula-accept'] != "yes") {
		include SAINT_SITE_ROOT . "/core/installer/eula.php";
	} else {
		
		if (!isset($st_link) || !$st_link || !$st_linkdb) {
			$st_link = mysql_connect(SAINT_DB_HOST,SAINT_DB_USER,SAINT_DB_PASS);
			$st_linkdb = mysql_select_db(SAINT_DB_NAME);
		}
		
		if ($st_link && $st_linkdb) {
			if(!mysql_num_rows(mysql_query("SHOW TABLES LIKE 'st_config'"))) {
				include SAINT_SITE_ROOT . "/core/installer/db-create.php";
			}		
			if(mysql_num_rows(mysql_query("SHOW TABLES LIKE 'st_config'"))) {
				if (Saint::siteHasAdmin()) {
					setInstalled(Saint::getSiteOwner());
				} else {
					if (isset($_POST['admin_username']) && isset($_POST['admin_password']) && isset($_POST['admin_password_confirm']) && isset($_POST['admin_email'])) {
						if ($_POST['admin_password'] == $_POST['admin_password_confirm']) {
							if (Saint::addUser($_POST['admin_username'],$_POST['admin_password'],$_POST['admin_email'])) {
								$user = new Saint_Model_User();
								$user->loadByUsername($_POST['admin_username']);
								$user->addToGroup("administrator");
								$user->save();

								$blog = new Saint_Model_Page();
								$blog->setName("blog");
								$blog->setTitle("Blog");
								$blog->setLayout("blog/index");
								$blog->setModel("Saint_Model_Blog");
								$blog->save();
								
								$shop = new Saint_Model_Page();
								$shop->setName("shop");
								$shop->setTitle("Shop");
								$shop->setLayout("shop/index");
								$shop->setModel("Saint_Model_Shop");
								$shop->save();
								
								setInstalled($_POST['admin_username']);
								Saint::setShopPageId($shop->getId());
								Saint::setBlogPageId($blog->getId());
							} else {
								$error = "There was a problem adding your user info. Please check the logs for more info and try again.";
								include SAINT_SITE_ROOT . "/core/installer/get-details.php";
							}
						} else {
							$error = "Your passwords did not match. Please try entering them again.";
							include SAINT_SITE_ROOT . "/core/installer/get-details.php";
						}
					} else
						include SAINT_SITE_ROOT . "/core/installer/get-details.php";
				}
			}
		}
	}
}
include SAINT_SITE_ROOT . "/core/installer/footer.php";

