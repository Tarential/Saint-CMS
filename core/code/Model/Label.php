<?php
/**
 * Model of a CMS-editable text label within the Saint framework.
 * @author Preston St. Pierre
 * @package Saint
 */
class Saint_Model_Label {
	protected $_name;
	protected $_owner;
	protected $_security_level;
	protected $_new_labels;

	/**
	 * Format given name for display on screen.
	 * @param string $name Name to format.
	 * @return string Formatted name.
	 */
	public static function formatForDisplay($name) {
			# Clean up the dashes and slashes
			$name = preg_replace('/[\/-]/',' ',$name);
			
			# Capitalize the first letter of each word in the name
			$names = explode(' ',$name);
			$capitalized = '';
			foreach ($names as $curname) {
				$capitalized .= ucfirst($curname) . " ";
			}
			$name = trim($capitalized);
			return $name;
	}
	
	
	public static function parseName($name) {
		$data = array();
		# Check if it is a global, page or block label
		if (preg_match('/^page\/(\d)+\/n\/(.*)$/',$name,$matches)) {
			# Page labels
			$data['label_name'] = $matches[2];
			$data['page_id'] = $matches[1];
		} elseif (preg_match('/^block\/(\d)+\/(.*)\/n\/(.*)$/',$name,$matches)) {
			# Block labels
			$data['block_id'] = $matches[1];
			$data['block_name'] = $matches[2];
			$data['label_name'] = $matches[3];
		} else {
			$data['label_name'] = $name;
		}
		
		return $data;
	}
	
	/**
	 * Load label model with blank data.
	 */
	public function __construct() {
		$this->_name = "";
		$this->_owner = "";
		$this->_new_labels = array();
	}

	/**
	 * Set the name of the label whose data to load when other functions are called.
	 * @param string $name Name of label whose data to load.
	 * @return boolean True on success, false otherwise.
	 */
	public function loadByName($name) {
		if ($name = Saint::sanitize($name,SAINT_REG_NAME)) {
			$this->_name = $name;
			return 1;
		} else
			return 0;
	}
	
	/**
	 * Get the name of the loaded label.
	 * @return string Name of loaded label.
	 */
	public function getName() {
		return $this->_name;
	}
	
	/**
	 * Get the owner of the loaded label.
	 * @return string Name of the owner of the loaded label.
	 */
	public function getOwner() {
		# Loading of owner information moved to this function to optimize display times
		if (!isset($this->_owner) || $this->_owner == "") {
			try {
				return Saint::getOne("SELECT `owner` FROM `st_labels` WHERE `name`='$name' ORDER BY `revision` DESC LIMIT 1");
			} catch (Exception $e) {
				if ($e->getCode()) {
					Saint::logError("Unable to get label owner for name $this->_name:".$e->getMessage()); }
				return 0;
			}
		}
		return $this->_owner;
	}
	
	/**
	 * Get the current revision number of the loaded label.
	 * @param string $lang Optional language version to select.
	 * @return int Current revision number.
	 */
	public function getRevision($lang = null) {
		if ($lang = Saint::sanitize($lang,SAINT_REG_NAME)) {
			if ($lang == null)
				$lang = Saint::getDefaultLanguage();
			try {
				return Saint::getOne("SELECT MAX(`revision`) FROM `st_labels` WHERE `name`='$this->_name' AND `language`='$lang'");
			} catch (Exception $e) {
				if ($e->getCode()) {
					Saint::logError("Unable to get the highest revision for current label: ".$e->getMessage(),__FILE__,__LINE__);
				}
				return 0;
			}
		} else
			return 0;
	}
	
	/**
	 * Get the number of saved revisions for the loaded label.
	 * @return int Number of saved revisions for the loaded label.
	 */
	public function getNumRevisions($lang = null) {
		if ($lang == null)
			$lang = Saint::getDefaultLanguage();
		try {
			return Saint::getNumRows("SELECT `revision` FROM `st_labels` WHERE `name`='$this->_name' AND `language`='$lang'");
		} catch (Exception $e) {
			if ($e->getCode()) {
				Saint::logError("Unable to get number of revisions for current label: ".$e->getMessage(),__FILE__,__LINE__);
			}
			return 0;
		}
	}
	
	/**
	 * Get the label contents. Creates label with default content if not found.
	 * @param string $lang Optional name of language to retrieve (null uses default).
	 * @param int $revision Optional previous revision to use (null/0 uses highest revision, 1 is previous, 2 is 2 back, etc).
	 * @return string Label code.
	 */
	public function getLabel($options = array()) {
		$label = '';
		if (isset($options['default'])) {
			$default = $options['default'];
		} else {
			$default = 'This is a blank label.';
		}
		if (isset($options['container'])) {
			$container = $options['container'];
		} else {
			$container = true;
		}
		if (isset($options['lang']) && $options['lang'] != null) {
			$lang = Saint::sanitize($options['lang'],SAINT_REG_NAME);
		} else {
			$lang = Saint::getCurrentLanguage();
		}
		if (isset($options['wysiwyg'])) {
			$wysiwyg = $options['wysiwyg'];
		} else {
			$wysiwyg = false;
		}
		if (isset($options['revision'])) {
			$revision = Saint::sanitize($options['revision'],SAINT_REG_ID);
		} else {
			$revision = 0;
		}
		$revcode = " ORDER BY `revision` DESC";
		if ($revision) {
			$revision = $this->getNumRevisions() - $revision;
			if ($revision < 1)
				$revision = 1;
			$revcode = " AND `revision`='$revision'";
		}
		
		try {
			$label = Saint::getOne("SELECT `label` FROM `st_labels` WHERE `name`='$this->_name' AND `language`='$lang'" . $revcode);
		} catch (Exception $e) {
			if ($e->getCode()) {
				Saint::logError("Unable to select label '$this->_name': ".$e->getMessage());
			}
			$label = $default;
		}
		
		$styles = "";
		if (Saint::getCurrentUser()->hasPermissionTo("edit-label") || Saint::getCurrentUsername() == $this->_owner)
			$styles .= " editable";
		if ($wysiwyg)
			$styles .= " wysiwyg";
		if ($container)
			$label = '<div class="sln-'.preg_replace('/\//','_',$this->_name).' saint-label'.$styles.'">'.stripslashes($label).'</div>';
		else
			$label = stripslashes($label);
		return $label;
	}
	
	/**
	 * Set the label contents.
	 * @param string $label New contents for label.
	 * @param string $lang Optional name of language in which to set the label (null uses default).
	 * @return boolean True on success, false otherwise.
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
	 * @return boolean True on success, false otherwise.
	 */
	public function save() {
		if ($this->_name) {
			if ($this->_owner == "") {
				$this->_owner = Saint::getSiteOwner(); }
			try {
				foreach($this->_new_labels as $lang=>$label) {
					if (Saint::sanitize($this->getLabel($lang)) != $label) {
						$this->newEntry($label,$lang);
						# Saint::logEvent("Added new label entry for '$this->_name'.");
					}
				}
				
				Saint::query("UPDATE `st_labels` SET `owner`='$this->_owner' WHERE `name`='$this->_name'");
				
				if (preg_match('/^page\/([\d]+)\/n\//',$this->_name,$matches)) {
					try {
						Saint::query("UPDATE `st_pages` AS `p` SET `p`.`updated`=NOW() WHERE `p`.`id`='$matches[1]'");
					} catch (Exception $f) {
						if ($f->getCode()) {
							Saint::logError("Unable to update page(s) associated with block '$this->_name': ".$f->getMessage(),__FILE__,__LINE__);
						}
					}
				}
				
				# Strip the delimiters off the config name pattern to match block names within the label name.
				$spn = trim(rtrim(SAINT_REG_NAME,'/'),'/');
				$spn = trim(rtrim($spn,'$'),'^');
				$block_pattern = '/^block\/[\d]+\/('.$spn.')\/n\//';
				if (preg_match($block_pattern,$this->_name,$matches)) {
					try {
						Saint::query("UPDATE `st_pages` AS `p`,`st_pageblocks` AS `b` SET `p`.`updated`=NOW() WHERE `b`.`block`='$matches[1]' AND `b`.`pageid`=`p`.`id`");
					} catch (Exception $g) {
						if ($g->getCode()) {
							Saint::logError("Unable to update page(s) associated with block '$this->_name': ".$g->getMessage(),__FILE__,__LINE__);
						}
					}
				}
				
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
	 * Create a new content revision. Used internally during saving.
	 * @param string $label New revision content.
	 * @param string $lang Optional name of language in which to set new content (null uses default).
	 * @return boolean True on success, false otherwise.
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

