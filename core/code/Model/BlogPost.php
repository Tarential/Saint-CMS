<?php
class Saint_Model_BlogPost extends Saint_Model_Block {
	
	public function getPostDate() {
		return $this->get("postdate");
	}
	
	public function getUrl() {
		return SAINT_URL.'/blog/single.'.$this->_id;
	}
}
