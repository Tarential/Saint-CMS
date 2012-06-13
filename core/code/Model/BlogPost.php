<?php
class Saint_Model_BlogPost extends Saint_Model_Block {
	
	public function load($id, $name=null) {
		// Magic so it can accept the arguments in either order.
		if (!Saint::sanitize($id,SAINT_REG_ID) && Saint::sanitize($name,SAINT_REG_ID)) {
			$id = $name;
		}
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
	
	public function renderInput($setting) {
		switch ($setting) {
			case "uri":
				parent::renderInput($setting);
				if ($this->get($setting) == "") {
					echo "(leave blank to auto generate)"; }
				break;
			default:
				parent::renderInput($setting);
		}
	}
	
	public function save() {
		if ($this->_settings['uri'] == "") {
			$this->_settings['uri'] = $this->_settings['title'];
		}
		$newuri = preg_replace('/\s/','-',strtolower($this->_settings['uri']));
		$newuri = preg_replace('/[^\w\d-]/','',$newuri);
		$this->_settings['uri'] = $newuri;
		return parent::save();
	}
}
