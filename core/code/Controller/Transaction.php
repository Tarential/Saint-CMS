<?php
/**
 * Controller for transactions managed by the Saint shop.
 * @author Preston St. Pierre
 * @package Saint
 */
class Saint_Controller_Transaction {
	/**
	 * Verify an IPN message sent by PayPal.
	 * 
	 * Reposts given data to PayPal and logs transaction details.
	 */
	public static function verifyIpn() {
		Saint::logError("Verifying IPN",__FILE__,__LINE__);
		Saint::getCurrentPage()->setTempLayout("system/system");
		
		// read the post from PayPal system and add 'cmd'
		$req = 'cmd=_notify-validate';
		
		foreach ($_POST as $key => $value) {
			Saint::logError($key . ": " . $value);
			$value = urlencode(stripslashes($value));
			$req .= "&$key=$value";
		}
		
		// post back to PayPal system to validate
		$header = "POST /cgi-bin/webscr HTTP/1.0\r\n";
		$header .= "Content-Type: application/x-www-form-urlencoded\r\n";
		$header .= "Content-Length: " . strlen($req) . "\r\n\r\n";
		$fp = fsockopen ('ssl://'.SAINT_PAYPAL_URL, 443, $errno, $errstr, 30);
		
		// assign posted variables to local variables
		$item_name = $_POST['item_name'];
		$item_number = $_POST['item_number'];
		$payment_status = $_POST['payment_status'];
		$payment_amount = $_POST['mc_gross'];
		$payment_currency = $_POST['mc_currency'];
		$txn_id = $_POST['txn_id'];
		$receiver_email = $_POST['receiver_email'];
		$payer_email = $_POST['payer_email'];
		$cartid = Saint::sanitize($_POST['custom'],SAINT_REG_ID);
		
		if (!$fp) {
			// HTTP ERROR
			Saint::logError("HTTP ERROR: $fp",__FILE__,__LINE__);
		} else {
			fputs ($fp, $header . $req);
			while (!feof($fp)) {
				$res = fgets ($fp, 1024);
				if (strcmp ($res, "VERIFIED") == 0) {
					// check the payment_status is Completed
					// check that txn_id has not been previously processed
					// check that receiver_email is your Primary PayPal email
					// check that payment_amount/payment_currency are correct
					// process payment
					# Check if payment is complete
					if ($payment_status === "Completed") {
						Saint::logError("Completed",__FILE__,__LINE__);
						$transaction = new Saint_Model_Transaction();
						
						# Check if transaction has already been processed
						if ($transaction->loadByPayPalId($txn_id)) {
							Saint::logError("PayPal sent an IPN for an already-processed PayPal transaction with id '$txn_id'.",__FILE__,__LINE__);
						} else {
							
							# Ensure you are the target of the payment
							if ($receiver_email === SAINT_PAYPAL_EMAIL) {
								$cart = new Saint_Model_ShoppingCart();
								
								# Check if cart exists
								if ($cart->load($cartid)) {
									$details = '';
									foreach ($_POST as $key => $value) {
										$details .= $key . ":" . $value . ";"; }
									
									$transaction->create($cart->getOwner(),$cart->getId(),$payment_amount,$txn_id,$payer_email,$details);
									
									# Check to ensure the prices match the payment
									if ($payment_amount == $cart->getTotal()) {
										Saint::logError("Success! Received payment matching cart ID '$cartid' for amount '$payment_amount'.",__FILE__,__LINE__);
									} else {
										Saint::logError("The received payment does not match the shopping cart total. ".$payment_amount.":".$cart->getTotal(),__FILE__,__LINE__);
									}
								} else {
									
									# It seems we have no shopping cart for this user. Strange. Log it.
									Saint::logError("User id '$userid' sent a payment from e-mail '$payer_email', but there is no matching cart.",__FILE__,__LINE__);
								}
							} else {
								Saint::logError("Someone sent an IPN but the target of the transaction does not appear to match your PayPal e-mail address.",__FILE__,__LINE__);
							}
						}
					} else {
						Saint::logError("Received notification of incomplete IPN. Why? I don't know. Logging it.",__FILE__,__LINE__);
					}
				} else if (strcmp ($res, "INVALID") == 0) {
					// log for manual investigation
					Saint::logError("Invalid IPN request detected from $_SERVER[REMOTE_ADDR].");
				}
			}
			fclose ($fp);
		}
	}

}
