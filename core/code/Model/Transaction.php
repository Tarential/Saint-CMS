<?php
/**
 * Model for Saint shop transactions.
 * @author Preston St. Pierre
 * @package Saint
 */
class Saint_Model_Transaction {
	protected $_id;
	protected $_ppid;
	protected $_user;
	protected $_cart;
	protected $_amount;
	protected $_date;
	protected $_paypaluser;
	
	/**
	 * Get all shop transactions matching the given arguments.
	 * @param string[] $arguments Arguments to match.
	 * @return int[] IDs of all matching transactions.
	 */
	public static function getTransactions($arguments = array()) {
		try {
			return Saint::getAll(Saint_Model_Transaction::makeQuery($arguments));
		} catch (Exception $e) {
			if ($e->getCode()) {
				Saint::logError("Unable to select transactions from database using query '$query': ".$e->getMessage()); }
			return array();
		}
	}
	
	/**
	 * Generate transaction query based on given arguments.
	 * @param string[] $arguments Arguments used to create query.
	 * @return string Generated query.
	 */
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
	
	/**
	 * Create new transaction with the given parameters if passed, default data if not.
	 * @param int $userid ID of user who completed the transaction.
	 * @param int $cartid ID of shopping cart to which the transaction belongs.
	 * @param float $amount Total amount of transaction.
	 * @param string $ppid Optional PayPal transaction ID.
	 * @param string $ppuser Optional PayPal username.
	 * @param string $details Optional additional details about transaction.
	 * @return int ID of new transaction if created, 1 otherwise.
	 */
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
	
	/**
	 * Load transaction with given ID from the database.
	 * @param int $id ID of transaction to load.
	 * @return boolean True on success, false otherwise.
	 */
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
	
	/**
	 * Load transaction with the given PayPal transaction ID from database.
	 * @param string $ppid PayPal transaction ID.
	 * @return boolean True on success, false otherwise.
	 */
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
	
	/**
	 * Create a new transaction with the given information.
	 * @param int $userid ID of user who completed the transaction.
	 * @param int $cartid ID of shopping cart to which the transaction belongs.
	 * @param float $amount Total amount of transaction.
	 * @param string $ppid Optional PayPal transaction ID.
	 * @param string $ppuser Optional PayPal username.
	 * @param string $details Optional additional details about transaction.
	 * @return boolean True on success, false otherwise.
	 */
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
	
	/**
	 * Get PayPal username associated with loaded transaction.
	 * @return string PayPal username.
	 */
	public function getPayPalUser() {
		return $this->_paypaluser;
	}
	
	/**
	 * Get PayPal transaction ID associated with loaded transaction.
	 * @return string PayPal transaction ID.
	 */
	public function getPayPalId() {
		return $this->_ppid;
	}
	
	/**
	 * Get ID of user who completed the loaded transaction.
	 * @return int User ID.
	 */
	public function getUserId() {
		return $this->_user;
	}
	
	/**
	 * Get shopping cart ID associated with loaded transaction.
	 * @return int Cart ID.
	 */
	public function getCartId() {
		return $this->_cart;
	}
	
	/**
	 * Get ID of loaded transaction.
	 * @return int Transaction ID.
	 */
	public function getId() {
		return $this->_id;
	}

	/**
	 * Get the value of the loaded transaction.
	 * @return float Value of transaction.
	 */
	public function getAmount() {
		return $this->_amount;
	}
	
	/**
	 * Get date and time associated with loaded transaction.
	 * @return string Date and time of transaction.
	 */
	public function getDate() {
		return $this->_date;
	}
}
