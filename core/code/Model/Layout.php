<?php
/**
 * Model of a page layout within the Saint framework.
 * @author Preston St. Pierre
 * @package Saint
 */
class Saint_Model_Layout {
	/**
	 * Check if the given layout name has an available template file.
	 * @param string $layout Layout name to check.
	 * @return boolean True if layout is active, false otherwise.
	 */
	public static function inUse($layout) {
		if ($layout = Saint::sanitize($layout,SAINT_REG_NAME)) {
			if (Saint_Model_Block::inUse("layouts/".$layout)) {
				return 1;
			}
		}
		return 0;
	}
	
	/**
	 * Get a list of in-use layout names.
	 * @return string[] Array of layout names.
	 */
	public static function getLayoutNames() {
		$layouts = array();
		try {
			$layout_data = Saint::getAll("SELECT `name`,`title` FROM `st_layouts`");
			foreach ($layout_data as $data) {
				$layouts[$data[0]] = $data[1];
			}
		} catch (Exception $e) {
			if ($e->getCode()) {
				Saint::logError("Unable to select layouts: ".$e->getMessage(),__FILE__,__LINE__);
			}
		}
		return $layouts;
	}
	
	/**
	 * Scan layout directories and update database.
	 */
	public static function updateLayouts() {
		$layouts = array();
		$core_dir = SAINT_SITE_ROOT . "/core/blocks/layouts";
		$theme_dir = Saint::getThemeDir() . "/blocks/layouts";
		$layout_config_files = Saint_Model_Block::recursiveScan($core_dir,"xml");
		$root_layouts = Saint_Model_Block::recursiveScan($core_dir,"php",1);
		if ($theme_dir != $core_dir) {
			$layout_config_files = array_merge($layout_config_files,Saint_Model_Block::recursiveScan($theme_dir,"xml"));
			$root_layouts = array_merge($root_layouts,Saint_Model_Block::recursiveScan($theme_dir,"php",1));
		}
		
		# Start by adding the root layouts from plain PHP files.
		foreach ($root_layouts as $layout) {
			if (preg_match('/\/([^\/]*)\.php$/',$layout,$matches)) {
				$layouts[$matches[1]] = array(
					'title' => ucfirst($matches[1]),
				);
			}
		}
		
		# Then parse the xml files for further info.
		foreach ($layout_config_files as $file) {
			$sxml = '';
			$file_handle = fopen($file, "r");
			if ($file_handle) {
				while (!feof($file_handle))
				   $sxml .= fgets($file_handle);
				fclose($file_handle);
			} else
				Saint::logError("Couldn't open settings file $file for reading.",__FILE__,__LINE__);
			
			$sparse = new SimpleXMLElement($sxml);
			$name = (string)$sparse->name;
			if ($name != "") {
				$layouts[$name] = array(
					'model' => (string)$sparse->model,
					'title' => (string)$sparse->title,
				);
			}
		}
		
		foreach ($layouts as $name=>$data) {
			$layout = new Saint_Model_Layout();
			if ($layout->loadByName($name)) {
				if (isset($data['title']) && $data['title'] != "") {
					$layout->setTitle($data['title']);
				}
				if (isset($data['model']) && $data['model'] != "") {
					$layout->setModel($data['model']);
				}
				$layout->save();
			} else {
				Saint_Model_Layout::create($name,$data);
			}
		}
	}
	
	/**
	 * Insert a new layout into the database with the given information.
	 * @param string $name New layout name.
	 * @param array $data Data for layout.
	 */
	public static function create($name, $data = array()) {
		$sname = Saint::sanitize($name,SAINT_REG_NAME);
		$keys = '';
		$vals = '';
		if (isset($data['model']) && $data['model'] != "") {
			$keys .= ",`model`";
			$vals .= ",'".Saint::sanitize($data['model'])."'";
		}
		if (isset($data['title']) && $data['title'] != "") {
			$keys .= ",`title`";
			$vals .= ",'".Saint::sanitize($data['title'])."'";
		}
		try {
			Saint::query("INSERT INTO `st_layouts` (`name`$keys) VALUES ('$sname'$vals)");
		} catch (Exception $e) {
			Saint::logError("Unable to create new layout '$sname': ".$e->getMessage());
		}
	}
	
	protected $_name;
	protected $_page;
	protected $_title;
	protected $_model;
	protected $_saved;
	protected $_content;
	
	/**
	 * Create a layout with blank data.
	 */
	public function __construct() {
		$this->_id = 0;
		$this->_name = '';
		$this->_page = null;
		$this->_title = '';
		$this->_model = '';
		$this->_saved = false;
		$this->_content = '';
	}
	
	/**
	 * Load the given layout.
	 * @param string $name Name of layout to load.
	 * @return boolean True if layout is active, false otherwise.
	 */
	public function loadByName($name) {
		$sname = Saint::sanitize($name,SAINT_REG_NAME);
		if (preg_match("/^system\//",$sname)) {
			$this->_id = 0;
			$this->_name = $sname;
			$this->_title = '';
			$this->_model = 'Saint_Model_Page';
		} else {
			if ($sname) {
				try {
					$data = Saint::getRow("SELECT `id`,`title`,`model` FROM `st_layouts` WHERE `name`='$sname'");
					$this->_id = $data[0];
					$this->_name = $sname;
					$this->_title = $data[1];
					$this->_model = $data[2];
					return 1;
				} catch (Exception $e) {
					if ($e->getCode()) {
						Saint::logError("Unable to select layout: ".$e->getMessage(),__FILE__,__LINE__);
					} else {
						Saint::logError("Cannot find layout with name $sname.",__FILE__,__LINE__);
					}
					return 0;
				}
			} else {
				Saint::logError("Invalid layout name: '$name'.",__FILE__,__LINE__);
				return 0;
			}
		}
	}
	
	/**
	 * Get ID for loaded layout.
	 * @return int ID for loaded layout.
	 */
	public function getId() {
		return $this->_id;
	}
	
	/**
	 * Get name for loaded layout.
	 * @return string Name for loaded layout.
	 */
	public function getName() {
		return $this->_name;
	}

	/**
	 * Get title for loaded layout.
	 * @return string Title for loaded layout.
	 */
	public function getTitle() {
		return $this->_title;
	}
	
	/**
	 * Get model for loaded layout.
	 * @return string Model for loaded layout.
	 */
	public function getModel() {
		return $this->_model;
	}
	
	/**
	 * Set new title for loaded layout.
	 * @param string $title New title for loaded layout.
	 */
	public function setTitle($title) {
		$this->_title = Saint::sanitize($title);
	}

	/**
	 * Set new model for loaded layout.
	 * @param string $model New model for loaded layout.
	 */
	public function setModel($model) {
		$this->_model = Saint::sanitize($model);
	}
	
	/**
	 * Save loaded layout information to database.
	 */
	public function save() {
		try {
			Saint::query("UPDATE `st_layouts` SET `title`='$this->_title',`model`='$this->_model' WHERE `id`='$this->_id'");
		} catch (Exception $e) {
			Saint::logError("Unable to save layout information to database: ".$e->getMessage(),__FILE__,__LINE__);
		}
	}
	
	/**
	 * Output selected page to the client.
	 * @param string $page Name of page to render.
	 * @return boolean True if page found, false otherwise.
	 */
	public function render($page, $options = array()) {
		$this->_page = $page;
		if (isset($options['get'])) {
			$get = $options['get'];
		} else {
			$get = false;
		}
		if ($this->_name == "") {
			Saint::includeBlock("layouts/system/404");
			return 0;
		} else {
			if ($get) {
				return Saint::getBlock("layouts/".$this->_name);
			} else {
				Saint::includeBlock("layouts/".$this->_name);
				return 1;
			}
		}
	}
}
