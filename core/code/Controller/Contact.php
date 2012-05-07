<?php
class Saint_Controller_Contact {
	public static function emailAdmin($details) {
		$owner = new Saint_Model_User();
		$owner->loadByUsername(Saint::getSiteOwner());
		$page = Saint::getCurrentPage();
		if (isset($details['E-Mail']))
			$mailfrom = $details['E-Mail'];
		else
			$mailfrom = Saint::getCurrentUser()->getEmail();
		$mailsub = "Website Contact Form Submission";
		$mailcon = 'A user has submitted the following information to your contact form:';
		foreach ($details as $key=>$val) {
			$mailcon .= "$key: $val\n";
		}
		$mailcon = wordwrap($mailcon,70);
		$mailhead = "From: \"Website Form Submission\" <$mailfrom>\r\n" .
		    "Reply-To: $mailfrom\r\n" .
		    'X-Mailer: PHP/' . phpversion();
		
		$page->setTempLayout("system/error");
		if (mail($owner->getEmail(),$mailsub,$mailcon,$mailhead)) {
			$page->error = "Thank you for submitting your contact request. We will reply to you as soon as availability allows.";
		} else {
			Saint::logError("Cannot connect to the mail server. Check your host php settings and mail server status for more information.",__FILE__,__LINE__);
			$page->error = "We're sorry, but due to technical difficulties our contact form is not working. If you don't mind, please e-mail <a href=\"".$owner->getEmail()."\">".$owner->getEmail()."</a> and we will look into the problem as soon as possible.";
		}
	}
	
	public static function sendSaleNotice($transactionid) {
		$transaction = new Saint_Model_Transaction();
		if ($transaction->load($transactionid)) {
			
			$email_subject = "Sale Successful!";
			
			$cart_template = <<<EOT
Thank you for making a purchase from this Saint powered store! We have received your payment for $[total] and will start processing this transaction as soon as possible.
Your transaction ID is "[id]". Please save this for future reference.

EOT;
			$item_template = <<<EOT

Product ID: [id]
Name: [name]
Price: $[price]
Number: [number]

EOT;

			$link_template = <<<EOT
Download: [link]

EOT;
			
			$cart = new Saint_Model_ShoppingCart();
			$cid = $transaction->getCartId();
			
			$cart->load($cid);
			$items = $cart->getItems();
			
			$cart_vars = array(
				'total' => $transaction->getAmount(),
				'id' => $transaction->getPayPalId(),
			);
			
			$item_vars = array();
			
			foreach ($items as $itemid=>$number) {
				$item_vars[$itemid] = array(
					'id' => $itemid,
					'price' => Saint::getBlockSetting("shop/product",$itemid,"Price"),
					'name' => Saint::getBlockSetting("shop/product",$itemid,"Name"),
					'number' => $number,
					
				);
			if (Saint::getBlockSetting("shop/product",$itemid,"File") != '') {
					$linkid = Saint_Model_Shop::createDownload($itemid);
					$item_vars[$itemid]['link'] = SAINT_BASE_URL . "shop/view.download/id.$itemid/linkid.$linkid";
				}
			}
			
			$edited_cart_template = $cart_template;
			
			foreach ($cart_vars as $key=>$val) {
				$edited_cart_template = preg_replace('/\['.$key.'\]/',$val,$edited_cart_template);
			}
			
			$contents = $edited_cart_template;
			
			foreach ($item_vars as $item) {
				$edited_item_template = $item_template;
				foreach ($item as $key=>$val) {
					$edited_item_template = preg_replace('/\['.$key.'\]/',$val,$edited_item_template);
				}
				$contents .= $edited_item_template;
				
				if (isset($item['link'])) {
					$contents .= preg_replace('/\[link\]/',$item['link'],$link_template);
				}
			}
			
			$owner = new Saint_Model_User();
			$owner->loadByUsername(Saint::getSiteOwner());
			$mailfrom = $owner->getEmail();
			
			$recipients = array( $mailfrom, $transaction->getPayPalUser() );
			
			//Saint::logError("Contents:\n$contents",__FILE__,__LINE__);
			//$contents = wordwrap($contents,70);
			$mailhead = "From: \"".SAINT_SITE_TITLE."\" <$mailfrom>\r\n" .
	    "Reply-To: $mailfrom\r\n" .
	    'X-Mailer: PHP/' . phpversion();
			
			foreach ($recipients as $recipient) {
				if (!mail($recipient,$email_subject,$contents,$mailhead)) {
					Saint::logError("Unable to establish a connection with the mail server. Check your host php settings and mail server status for more information.",__FILE__,__LINE__);
				}
			}
			
		} else {
			return 0;
		}
	}
}
