<?php
class Saint_Model_Transaction {
	protected $_id;
	protected $_ppid;
	protected $_user;
	protected $_cart;
	protected $_amount;
	protected $_date;
	protected $_paypaluser;
	
	public static function getTransactions($arguments = array()) {
		try {
			return Saint::getAll(Saint_Model_Transaction::makeQuery($arguments));
		} catch (Exception $e) {
			if ($e->getCode()) {
				Saint::logError("Unable to select transactions from database using query '$query': ".$e->getMessage()); }
			return array();
		}
	}
	
	public static function makeQuery($arguments = null) {
		# Default settings
		$start = 0;
		$repeat = 30;
		$order = "DESC";
		$orderby = "id";
		$category = '';
		$where = '';
		
		# Compile arguments
		if (is_array($arguments)) {
			if (isset($arguments['start']))
				$start = Saint::sanitize($arguments['start']);
			if (isset($arguments['repeat']))
				$repeat = Saint::sanitize($arguments['repeat']);
			if (isset($arguments['order']))
				$order = Saint::sanitize($arguments['order']);
			if (isset($arguments['orderby']))
				$orderby = Saint::sanitize($arguments['orderby']);
			if (!isset($arguments['search'])) {
				$arguments['search'] = array(); }
			if (isset($arguments['matches'])) {
				$where = Saint::compileMatches($arguments['matches'],$arguments['search']); }
		}
		
		if ($start == 0 && $repeat == 0)
			$ls = '';
		else
			$ls = "LIMIT $start,$repeat";
		$obs = "ORDER BY `$orderby` $order";
		
		return "SELECT `id` FROM `st_shop_transactions` $where $obs $ls";
	}
	
	public function __construct($userid = null, $cartid = null, $amount = null, $ppid = 0, $ppuser = '', $details = null) {
		if ($userid !== null && $cartid !== null && $amount !== null) {
			return $this->create($userid, $cartid, $amount, $ppid, $ppuser, $details);
		} else {
			$this->_id = 0;
			$this->_ppid = 0;
			$this->_user = 0;
			$this->_cart = 0;
			$this->_amount = 0;
			$this->_paypaluser = '';
			return 1;
		}
	}
	
	public function load($id) {
		if ($sid = Saint::sanitize($id,SAINT_REG_ID)) {
			try {
				$data = Saint::getRow("SELECT `userid`,`cartid`,`paypalid`,`amount`,`date`,`paypaluser` FROM `st_shop_transactions` WHERE `id`='$sid'");
				$this->_id = $sid;
				$this->_user = $data[0];
				$this->_cart = $data[1];
				$this->_ppid = $data[2];
				$this->_amount = $data[3];
				$this->_date = $data[4];
				$this->_paypaluser = $data[5];
				return 1;
			} catch (Exception $e) {
				if ($e->getCode() !== 0) {
					Saint::logError("Unable to select transaction with id '$sid': ".$e->getMessage(),__FILE__,__LINE__);
				}
				return 0;
			}
		} else {
			Saint::logError("Invalid transaction id '$id'.",__FILE__,__LINE__);
			return 0;
		}
	}
	
	public function loadByPayPalId($ppid = 0) {
		$sppid = Saint::sanitize($ppid);
		if ($sppid) {
			try {
				$id = Saint::getOne("SELECT `id` FROM `st_shop_transactions` WHERE `paypalid`='$sppid'");
				return $this->load($id);
			} catch (Exception $e) {
				if ($e->getCode() != 0) {
					Saint::logError("Unable to select transaction with PPID '$sppid': ".$e->getMessage(),__FILE__,__LINE__);
				}
				return 0;
			}
		} else {
			Saint::logError("Invalid PayPal ID: '$ppid'.");
			return 0;
		}
	}
	
	public function create($userid, $cartid, $amount, $ppid = 0, $ppuser = '', $details = null) {
		$suserid = Saint::sanitize($userid,SAINT_REG_ID);
		$scartid = Saint::sanitize($cartid,SAINT_REG_ID);
		$sppid = Saint::sanitize($ppid);
		$samount = Saint::sanitize($amount);
		$sppuser = Saint::sanitize($ppuser);
		$cart = new Saint_Model_ShoppingCart();
		if ($details !== null) {
			$sdetails = Saint::sanitize($details); }
		if ($cart->load($scartid)) {
			$cart->setPurchased(1);
			$cart->save(); }
		try {
			Saint::query("INSERT INTO `st_shop_transactions` (`userid`,`cartid`,`paypalid`,`amount`,`paypaluser`) VALUES ('$suserid','$scartid','$sppid','$samount','$sppuser')");
			$id = Saint::getLastInsertId();
			Saint_Controller_Contact::sendSaleNotice($id);
			if ($sppid && isset($sdetails) && $sdetails) {
				try {
					Saint::query("INSERT INTO `st_shop_paypal_details` (`paypalid`,`details`) VALUES ('$sppid','$sdetails')");
				} catch (Exception $f) {
					Saint::logError("Unable to save PayPal details: ".$f->getMessage(),__FILE__,__LINE__);
				}
			}
			return $this->load($id);
		} catch (Exception $e) {
			Saint::logError("Unable to save transaction details: ".$e->getMessage(),__FILE__,__LINE__);
			return 0;
		}
	}
	
	/* Function disabled until it becomes necessary to use
	public function setPayPalId($ppid, $overwrite = false) {
		# Ensure model info has been loaded from the DB
		if ($this->_id !== 0) {
			# Sanitize input
			$sppid = Saint::sanitize($ppid,SAINT_REG_ID);
			if ($sppid) {
				# Check if the PayPal ID has been previously set, and if so, whether this is intended
				if ($this->_ppid !== 0 && !$overwrite) {
					Saint::logError("To update the PayPal ID once it has been set pass a boolean true to setPayPalId as the second argument for confirmation of override.",__FILE__,__LINE__);
					return 0;
				} else {
					$this->_ppid = $sppid;
				}
			} else {
				Saint::logError("Invalid PayPal ID: '$ppid'.",__FILE__,__LINE__);
				return 0;
			}
		} else {
			Saint::logError("Before setting the PayPal ID you must load a transaction with the load or create functions.",__FILE__,__LINE__);
			return 0;
		}
	} */
	
	public function getPayPalUser() {
		return $this->_paypaluser;
	}
	
	public function getPayPalId() {
		return $this->_ppid;
	}
	
	public function getUserId() {
		return $this->_user;
	}
	
	public function getCartId() {
		return $this->_cart;
	}
	
	public function getId() {
		return $this->_id;
	}

	public function getAmount() {
		return $this->_amount;
	}
	
	public function getDate() {
		return $this->_date;
	}
}
