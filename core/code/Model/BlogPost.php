<?php
class Saint_Model_BlogPost extends Saint_Model_Block {
	
	public function load($id, $name=null) {
		return parent::load("blog/post",$id);
	}
	
	public function loadByUri($uri) {
		$suri = Saint::sanitize($uri);
		try {
			return $this->load(Saint::getOne("SELECT `id` FROM `st_blocks_blog_post` WHERE `uri` LIKE '%$suri%' ORDER BY `postdate` DESC"));
		} catch (Exception $e) {
			if ($e->getCode()) {
				Saint::logError("Unable to select blog post via URI: ".$e->getMessage(),__FILE__,__LINE__); }
			return 0;
		}
	}
	
	public function getPostDate() {
		return $this->get("postdate");
	}
	
	public function getUrl() {
		return SAINT_URL.'/blog/'.$this->_settings['uri'];
	}
	
	public function save() {
		if ($this->_settings['uri'] == "") {
			$this->_settings['uri'] = $this->_settings['title'];
		}
		$newuri = preg_replace('/\s/','-',$this->_settings['uri']);
		$newuri = preg_replace('/[^\w\d]/','',$newuri);
		$this->_settings['uri'] == $newuri;
	}
}
