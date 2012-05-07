<?php

class Saint_Model_Label {
	protected $_name;
	protected $_owner;
	protected $_security_level;
	protected $_new_labels;

	public static function formatForDisplay($name) {
		$name = preg_replace('/[^\/]+\/(.*)$/','$1',$name);
		$name = preg_replace('/[\/-]/',' ',$name);
		$name = preg_replace('/\sn\s/',' ',$name);
		$names = explode(' ',$name);
		for ($i = 0; $i < sizeof($names); $i++)
			$names[$i] = ucfirst($names[$i]);
		$name = implode(' ',$names);
		return $name;
	}
	
	public function __construct() {
		$this->_name = "";
		$this->_owner = "";
		$this->_new_labels = array();
	}

	/**
	 * Load label from database by name
	 * @param string $name Name of label to load
	 * @global string Pattern to match valid item names
	 * @return boolean True if successful, false otherwise
	 */
	public function loadByName($name) {
		if ($name = Saint::sanitize($name,SAINT_REG_NAME)) {
			try {
				$labels = Saint::getRow("SELECT `owner` FROM `st_labels` WHERE `name`='$name' ORDER BY `revision` DESC LIMIT 1");
				$this->_name = $name;
				$this->_owner = $labels[0];
				return 1;
			} catch (Exception $e) {
				$this->_name = $name;
				return 0;
			}
		} else
			return 0;
	}
	
	/**
	 * Get the label name
	 * @return string Label name
	 */
	public function getName() {
		return $this->_name;
	}
	
	/**
	 * Get the label owner
	 * @return string Label owner's name
	 */
	public function getOwner() {
		// Loading of owner information moved to this function to optimize display times 
		try {
			return Saint::getOne("SELECT `owner` FROM `st_labels` WHERE `name`='$name' ORDER BY `revision` DESC LIMIT 1");
		} catch (Exception $e) {
			if ($e->getCode()) {
				Saint::logError("Unable to get label owner for name $this->_name:".$e->getMessage()); }
			return 0;
		}
	}
	
	/**
	 * Get the current revision
	 * @param string $lang Optional language, uses default if null
	 * @global string Pattern to match valid item names
	 * @return int Label max revision
	 */
	public function getRevision($lang = null) {
		if ($lang = Saint::sanitize($lang,SAINT_REG_NAME)) {
			if ($lang == null)
				$lang = Saint::getDefaultLanguage();
			try {
				return Saint::getOne("SELECT MAX(`revision`) FROM `st_labels` WHERE `name`='$this->_name' AND `language`='$lang'");
			} catch (Exception $e) {
				return 0;
			}
		} else
			return 0;
	}
	
	/**
	 * Get the label contents. Creates blank label container if not found.
	 * @param string $lang Optional name of language to retrieve (null uses default)
	 * @param int $revision Optional revision number to retrieve (null uses highest revision)
	 * @global string Pattern to match valid item names
	 * @global string Pattern to match revision numbers
	 * @return string Label contents
	 */
	public function getLabel($container = true, $default = '', $lang = null, $revision = null) {
		$label = '';
		if ($lang == null)
			$lang = Saint::getCurrentLanguage();
		else
			$lang = Saint::sanitize($lang,SAINT_REG_NAME);
		$revision = Saint::sanitize($revision,SAINT_REG_ID);
		if ($revision)
			$revcode = " AND `revision`='$revision'";
		else
			$revcode = " ORDER BY `revision` DESC";
		if ($lang && $revcode) {
			$query = "SELECT `label` FROM `st_labels` WHERE `name`='$this->_name' AND `language`='$lang'" . $revcode;
			try {
				$label = Saint::getOne($query);
			} catch (Exception $e) {
				# It's a new label, nothing to be alarmed about.
			}
		}
		if (Saint::getCurrentUser()->hasPermissionTo("edit-label"))
			$editable = " editable";
		else
			$editable = "";
		if ($container)
			$label = "<span id=\"saint_".preg_replace('/\//','_',$this->_name)."\" class=\"saint-label$editable\">" . stripslashes($label) . "</span>";
		else
			$label = stripslashes($label);
		return $label;
	}
	
	/**
	 * Set the label contents.
	 * @param string $label New contents for label
	 * @param string $lang Optional name of language to use (null uses default)
	 * @global string SAINT_REG_NAME Pattern to match valid language names
	 * @return string Label contents
	 */
	public function setLabel($label, $lang = null) {
		$label = Saint::sanitize($label);
		if ($lang == null)
			$lang = Saint::getDefaultLanguage();
		else
			$lang = Saint::sanitize($lang,SAINT_REG_NAME);
		if ($label && $lang) {
			$this->_new_labels[$lang] = $label;
			return 1;
		} else
			return 0;
	}
	
	/**
	 * Save the active label information to database.
	 * @return boolean True if successful, false otherwise
	 */
	public function save() {
		if ($this->_name) {
			if ($this->_owner == "") {
				$this->_owner = Saint::getSiteOwner(); }
			try {
				foreach($this->_new_labels as $lang=>$label) {
					if (Saint::sanitize($this->getLabel($lang)) != $label) {
						Saint::logError("Adding new entry $label for $lang");
						$this->newEntry($label,$lang); }
				}
				
				$query = "UPDATE `st_labels` SET ".
				"`owner`='$this->_owner' ".
				"WHERE `name`='$this->_name'";
				
				return 1;
			} catch (Exception $e) {
				Saint::logError("Could not save label $this->_name. ".$e->getMessage(),__FILE__,__LINE__);
				return 0;
			}
		} else {
			return 0;
		}
	}
	
	/**
	 * Create a new content revision
	 * @param string $label New content
	 * @param string $lang Optional name of language for new content (null uses default)
	 * @return boolean True for success, false otherwise
	 */
	protected function newEntry($label,$lang = null) {
		if ($lang == null)
			$lang = Saint::getDefaultLanguage();
		try {
			$revision = $this->getRevision($lang)+1;
			$query = "INSERT INTO `st_labels` (`name`,`owner`,`revision`,`language`,`label`) ".
			"VALUES ('$this->_name','$this->_owner','$revision','$lang','$label')";
			Saint::query($query);
			return 1;
		} catch (Exception $e) {
			Saint::logError("Cannot create new label revision. " . $e->getMessage(),__FILE__,__LINE__);
			return 0;
		}
	}
}

