<?php
/**
 * Download logging model for the Saint framework.
 * @author Preston St. Pierre
 */
class Saint_Model_Download {
	protected $_id;
	protected $_client_ip;
	protected $_client_user_agent;
	
	public function __construct($file_name, $client_ip, $client_user_agent) {
		$this->_id = 0;
		$this->_file_name = Saint::sanitize($file_name);
		$this->_client_ip = Saint::sanitize($client_ip);
		$this->_client_user_agent = Saint::sanitize($client_user_agent);
		$this->save();
	}
	
	public function save() {
		if ($id) {
			Saint::logError("You can only save a download log once.",__FILE__,__LINE__);
		} else {
			$file_id = 0;
			try {
				$file_id = Saint::getOne("SELECT `id` FROM `st_public_files` WHERE `name`='$this->_file_name'");
			} catch (Exception $e) {
				if ($e->getCode()) {
					Saint::logError("Unable to get public file ID: ".$e->getMessage(),__FILE__,__LINE__);
				}
				try {
					Saint::query("INSERT INTO `st_public_files` (`name`) VALUES ('$this->_file_name')");
					$file_id = Saint::getLastInsertId();
				} catch (Exception $f) {
					Saint::logError("Unable to insert file into public table: ".$f->getMessage(),__FILE__,__LINE__);
				}
			}
			if ($file_id) {
				try {
					Saint::query("INSERT INTO `st_public_downloads` (`file`,`ip`,`user_agent`) VALUES ('$file_id','$this->_client_ip','$this->_client_user_agent')");
					return 1;
				} catch (Exception $g) {
					Saint::logError("Unable to save download log: ".$g->getMessage(),__FILE__,__LINE__);
				}
			}
		}
		return 0;
	}
}
