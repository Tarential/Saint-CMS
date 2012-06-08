<?php

/**
 * Model of a content block for the Saint framework.
 * @author Preston St. Pierre
 * @package Saint
 */
class Saint_Model_Block {
	/**
	 * Get the ID number for the current block template.
	 * @param string $name Name of block template.
	 * @return int ID number of current block template or zero for failure.
	 */
	public static function getBlockTypeId($name) {
		$sname = Saint::sanitize($name,SAINT_REG_NAME);
		$btid = 0;
		if ($sname) {
			try {
				$btid = Saint::getOne("SELECT `id` FROM `st_blocktypes` WHERE `name`='$sname'");
			} catch (Exception $f) {
				try {
					Saint::query("INSERT INTO `st_blocktypes` (`name`) VALUES ('$sname')");
					$btid = Saint::getLastInsertId();
				} catch (Exception $g) {
					Saint::logError("Problem inserting block type '$sname' into DB: ".$g->getMessage(),__FILE__,__LINE__);
				}
			}
		} else {
			Saint::logError("Name '$name' did not match valid patterns.",__FILE__,__LINE__);
		}
		return $btid;
	}
	
	/**
	 * Get dynamic block's unique ID.
	 * 
	 * Each repeating block created is given a unique ID by which to identify it globally.
	 * This is different than the block's regular ID, which is only unique for that block's type.
	 * 
	 * @param string $name Name of the block.
	 * @param id $id Block's regular ID.
	 */
	public static function getBlockUid($name,$id) {
		$sname = Saint_Model_Block::convertNameFromWeb(Saint::sanitize($name));
		$sid = Saint::sanitize($id,SAINT_REG_ID);
		$uid = 0;
		if ($sname && $sid) {
			try {
				$query = "SELECT `b`.`id` FROM `st_blocks` as `b`,`st_blocktypes` as `t` WHERE".
				" `t`.`name`='$sname' AND `b`.`blocktypeid`=`t`.`id` AND `b`.`blockid`='$sid'";
				$uid = Saint::getOne($query);
			} catch (Exception $f) {
				Saint::logError("Problem getting unique block id for '$sname' with id '$sid': ".$f->getMessage(),__FILE__,__LINE__);
			}
		} else {
			Saint::logError("Name '$name' and/or id '$id' did not match valid patterns.",__FILE__,__LINE__);
		}
		return $uid;
	}
	
	/**
	 * Check if a given block exists.
	 * @param string $block Name of block.
	 * @return boolean True if exists, false otherwise.
	 */
	public static function inUse($block) {
		if ($block = Saint::sanitize($block,SAINT_REG_NAME)) {
			if (file_exists(Saint::getThemeDir() .  "/blocks/".$block.".php"))
				return 1;
			elseif (file_exists(SAINT_SITE_ROOT .  "/core/blocks/".$block.".php"))
				return 1;
		} else
			return 0;
	}
	
	/**
	 * Include block in page, prioritizing user directory.
	 * @param string $block Name of block.
	 * @return boolean True if successful, false otherwise.
	 */
	public static function includeBlock($block, $container = true, $view = null) {
		$page = Saint::getCurrentPage();
		$view = Saint::sanitize($view,SAINT_REG_NAME);
		$block = Saint::sanitize($block,SAINT_REG_NAME);
		if ($block) {
			$page->addBlock($block);
			$id = $page->getBlockId();
			if (!$view)
				$view = $block;
			if (file_exists(Saint::getThemeDir() .  "/blocks/".$view.".php"))
				$incfile = Saint::getThemeDir() .  "/blocks/".$view.".php";
			elseif (file_exists(SAINT_SITE_ROOT .  "/core/blocks/".$view.".php"))
				$incfile = SAINT_SITE_ROOT .  "/core/blocks/".$view.".php";
			else {
				Saint::logError("Cannot find view $view.",__FILE__,__LINE__); 
				return 0; }
			if (preg_match('/^layouts/',$view))
				include $incfile;
			else {
				if ($container) {
					echo "<div id=\"saint_".preg_replace('/\//','_',$block)."\" class=\"saint-block\">\n";
					include $incfile;
					echo "\n</div>";
				} else
					include $incfile;
			}
			return 1;
		} else {
			Saint::logError("Block name '$block' could not be validated.",__FILE__,__LINE__);
			return 0;
		}
	}
	
	/**
	 * Generate and return SQL query for selecting repeating blocks using given parameters.
	 * @param string $block Name of block to which the query will apply.
	 * @param array[] $arguments Optional arguments to filter the query.
	 * @return string SQL query.
	 */
	public static function makeQuery($block, $arguments = null) {
		if ($sblock = Saint::sanitize($block)) {
			# Default settings
			$start = 0;
			$paging = false;
			$repeat = 3;
			$order = "DESC";
			$orderby = "id";
			$where = '';
			$name = Saint_Model_Block::formatForTable($sblock);
			$category = '';
			
			# Compile arguments
			if (is_array($arguments)) {
				if (isset($arguments['category']))
					$category = Saint::sanitize($arguments['category']);
				if (isset($arguments['start']))
					$start = Saint::sanitize($arguments['start']);
				if (isset($arguments['paging']))
					$paging = Saint::sanitize($arguments['paging']);
				if (isset($arguments['repeat']))
					$repeat = Saint::sanitize($arguments['repeat']);
				if (isset($arguments['order']))
					$order = Saint::sanitize($arguments['order']);
				if (isset($arguments['orderby']))
					$orderby = Saint::sanitize($arguments['orderby']);
				if (isset($arguments['matches'])) {
					if (is_array($arguments['matches'][0])) {
						foreach ($arguments['matches'] as $match) {
							if(isset($match[2]))
								$eq = Saint::sanitize($match[2]);
							else
								$eq = "=";
							$lsm = Saint::sanitize($match[0]);
							$rsm = Saint::sanitize($match[1]);
							$where .= " `$lsm`$eq'$rsm' AND";
						}
					} else {
						if(isset($arguments['matches'][2]))
								$eq = Saint::sanitize($arguments['matches'][2]);
							else
								$eq = "=";
						$where .= " `".Saint::sanitize($arguments['matches'][0])."`$eq'".Saint::sanitize($arguments['matches'][1])."' AND";
					}
				}
				if (isset($arguments['search'])) {
					if (is_array($arguments['search'][0])) {
						foreach ($arguments['search'] as $match)
							$where .= " `".Saint::sanitize($match[0])."` LIKE '".Saint::sanitize($match[1])."' AND";
					} else
						$where .= " `".Saint::sanitize($arguments['search'][0])."` LIKE '".Saint::sanitize($arguments['search'][1])."' AND";
				}
			}
			
			# Set up query
			if ($where != '')
			$where = "WHERE ".preg_replace('/\s*AND$/','',$where);
			
			if ($paging)
				$sp = Saint::getStartingNumber(Saint_Model_Block::getBlockTypeId(Saint_Model_Block::convertNameFromWeb($name)),$start);
			else
				$sp = $start;
			$start = $sp * $repeat;
			if ($start == 0 && $repeat == 0)
				$ls = '';
			else
				$ls = "LIMIT $start,$repeat";
			$obs = "ORDER BY $orderby $order";
			
			try {
				# Check if this block has its own table
				$query = "SHOW TABLES LIKE 'st_blocks_$name'";
				$table = Saint::getAll($query);
			} catch (Exception $c) {
				# It doesn't, so we create one
				$ctq = <<<EOT
CREATE TABLE IF NOT EXISTS `st_blocks_$name` (
	`id` int UNSIGNED NOT NULL AUTO_INCREMENT,
	`enabled` boolean NOT NULL DEFAULT false,
	PRIMARY KEY (`id`)
) ENGINE=InnoDB;
EOT;
				try {
					$create = Saint::query($ctq);
				} catch (Exception $e) {
					Saint::logError("Couldn't create table st_blocks_$name " . $e->getMessage(),__FILE__,__LINE__);
				}
			}
			
			// Category filtering
			if ($category != '') {
				$cattables = ",`st_blocks` as `u`,`st_blocktypes` as `t`,`st_blockcats` as `bc`,`st_categories` as `c`";
				$catwhere = "AND `b`.`id`=`u`.`blockid` AND `u`.`id`=`bc`.`blockid` AND `u`.`blocktypeid`=`t`.`id` AND `t`.`name`='$sblock' AND `c`.`id`=`bc`.`catid` AND `c`.`name`='$category'";
			} else {
				$cattables = '';
				$catwhere = '';
			}
			
			return "SELECT `b`.`id` FROM `st_blocks_$name` as `b`$cattables $where $catwhere $obs $ls";
		} else {
			Saint::logError("Invalid block name '$block'.",__FILE__,__LINE__);
			return 0;
		}
	}
	
	/**
	 * Get blocks which match given arguments.
	 * @param string $block Name of block template.
	 * @param array[] $arguments Optional arguments to filter which blocks are selected.
	 * @return array[] Blocks matching the given arguments.
	 */
	public static function getBlocks($block, $arguments = null) {
			if (!is_array($arguments))
				$arguments = array();
			$query = Saint_Model_Block::makeQuery($block,$arguments);
			# Now check for block listings which match our arguments
			try {
				$saved_blocks = Saint::getAll($query);
			} catch (Exception $d) {
				# If we don't find any, use an empty array
				$saved_blocks = array();
			}
			return $saved_blocks;
	}
	
	/**
	 * Include multiple copies of a template block with dynamic data.
	 * @param string $block Name of block to include. Corresponds to filename in blocks directory.
	 * @param array[] $arguments Optional arguments for sorting/filtering blocks
	 * @param boolean $container Optional flag to wrap block in div container if true.
	 * @param string $view Optional name of block to use as a view template for selected block's data.
	 * @return boolean True for success, false otherwise 
	 */
	public static function includeRepeatingBlock($block, $arguments = null, $container = false, $view = null) {
		$sblock = Saint::sanitize($block);
		if ($sblock) {
			$saved_blocks = Saint_Model_Block::getBlocks($block,$arguments);
			$page = Saint::getCurrentPage();
			$args = $page->getArgs();
			$page->crb = $block;
			$page->crbmore = false;
			$name = Saint_Model_Block::formatForTable($sblock);
			
			if (isset($arguments['start']))
				$start = $arguments['start'];
			else
				$start = 0;
			if (isset($arguments['repeat']))
				$repeat = $arguments['repeat'];
			else
				$repeat = 3;
			if (isset($arguments['paging']))
				$paging = $arguments['paging'];
			else
				$paging = false;
			
			try {
				$arguments['start'] = 0;
				$arguments['repeat'] = 0;
				$query = Saint_Model_Block::makeQuery($block,$arguments);
				$numresults = Saint::getNumRows($query);
			} catch (Exception $r) {
				$numresults = 0;
				Saint::logError("Unable to select number of rows: ".$r->getMessage(),__FILE__,__LINE__);
			}
			
			if ($numresults > ($start+$repeat)) {
				$page->crbmore = true;
			}
			
			# Display the block
			if ($container) {
				echo "<div id=\"saint_".preg_replace('/\//','_',$sblock)."\" class=\"saint-block repeating\">\n";
				echo "<div class=\"add-button hidden\">Add New <span class=\"block-name\">".Saint_Model_Block::formatForDisplay($sblock)."</span></div>";
			}
			if (sizeof($saved_blocks) == 0) {
				if (isset($arguments['label'])) {
					echo "<p>$arguments[label]</p>";
				} else {
					echo "<p>Sorry, no blocks found to match selected criteria.</p>";
				}
			} else {
				foreach ($saved_blocks as $bid) {
					$nblock = new Saint_Model_Block();
					$nblock->load($name,$bid);
					$page->setBlockId($bid);
					$page->addBlock("block/".$bid."/".$block);
					if ($container) {
						echo '<div class="block-item">';
						echo "<div id=\"".$bid."\" class=\"edit-button hidden\">Edit ".Saint_Model_Block::formatForDisplay($sblock)." <span class=\"block-name\">".$bid."</span></div>"; }
					Saint::includeBlock($sblock, $container, $view);
					if ($container) {
						echo "</div>\n"; }
					$page->setBlockId(0);
				}
				if ($paging) {
					$url = "/".$page->getName();
					foreach ($page->getArgs() as $key=>$val) {
						if ($key != "pnum" && $key != "btid") {
							$url .= "/$key.$val";
						}
					}
					$btid = Saint_Model_Block::getBlockTypeId($page->crb);
					$page->crburl = chop($url,'/')."/btid.$btid/pnum.";
					$page->crbpstart = Saint::getStartingNumber($btid)-1;
					$page->crbnstart = Saint::getStartingNumber($btid)+1;
					$page->crbnumpages = ceil($numresults / $repeat);
					if ($page->crbnumpages > 1)
						Saint::includeBlock("navigation/pager");
				}
			}
			if ($container) {
				echo "</div>"; }
			return 1;
		} else {
			Saint::logError("Invalid block name '$block'.",__FILE__,__LINE__);
			return 0;
		}
	}

	/**
	 * Get specified setting value for given block name/id.
	 * @param string $blockname Name of block template.
	 * @param int $blockid ID of individual block. 
	 * @param string $settingname Name of setting to retrieve.
	 * @return string Value of setting.
	 */
	public static function getBlockSetting($blockname,$blockid,$settingname) {
		$block = new Saint_Model_Block($blockname,$blockid);
		if ($block)
			return $block->get($settingname);
		else
			return 0;
	}

	/**
	 * Change the specified setting value for given block name/id.
	 * @param string $blockname Name of block template.
	 * @param int $blockid ID of individual block. 
	 * @param string $settingname Name of setting to change.
	 * @param string $newvalue New value for setting.
	 * @return boolean True for success, false for failure.
	 */
	public static function setBlockSetting($blockname,$blockid,$settingname,$newvalue) {
		$block = new Saint_Model_Block($blockname,$blockid);
		if ($block) {
			if ($block->set($settingname,$newvalue) && $block->save())
				return 1;
			else
				return 0;
		} else
			return 0;
	}
	
	/**
	 * Format the given string for use in a database table name.
	 * @param string $name Name to format.
	 * @return string Formatted name.
	 */
	public static function formatForTable($name) {
		return substr(preg_replace('/\//','_',$name),-54);
	}
	
	/**
	 * Format the given string for display in the user interface.
	 * @param string $name Name to format.
	 * @return string Formatted name.
	 */
	public static function formatForDisplay($name) {
		$name = preg_replace('/^.*\/([^\/]+)$/','$1',$name);
		$name = preg_replace('/-/',' ',$name);
		$names = explode(' ',$name);
		for ($i = 0; $i < sizeof($names); $i++)
			$names[$i] = ucfirst($names[$i]);
		$name = implode(' ',$names);
		return $name;
	}

	/**
	 * Convert the given name into a valid HTML element ID.
	 * @param string $name Name to format.
	 * @return string Formatted name.
	 */
	public static function convertNameFromWeb($name) {
		return preg_replace('/_/','/',$name);
	}
	
	/**
	 * Revert the given name into standard format.
	 * @param string $name Name to revert.
	 * @return string Standard format name.
	 */
	public static function convertNameToWeb($name) {
		return preg_replace('/\//','_',$name);
	}
	
	/**
	 * Processes all XML files in block directories then updates the associated block tables if necessary.
	 * @return boolean True for success, false otherwise.
	 */
	public static function processSettings() {
		# Scan files in user and system directories
		$saintdir = SAINT_SITE_ROOT . "/core/blocks";
		$userdir = Saint::getThemeDir() . "/blocks";
		$allfiles = Saint_Model_Block::recursiveScan($saintdir,"xml");
		if ($userdir != $saintdir)
			$allfiles = array_merge($allfiles,Saint_Model_Block::recursiveScan($userdir,"xml"));
		
		# Parse the xml files
		foreach ($allfiles as $file) {
			$sxml = '';
			$file_handle = fopen($file, "r");
			if ($file_handle) {
				while (!feof($file_handle))
				   $sxml .= fgets($file_handle);
				fclose($file_handle);
			} else
				Saint::logError("Couldn't open settings file $file for reading.",__FILE__,__LINE__);
			
			$sparse = new SimpleXMLElement($sxml);
			$name = Saint_Model_Block::formatForTable($sparse->name);
			
			try {
				Saint::getAll("SHOW TABLES LIKE 'st_blocks_$name'");
				try {
					$columns = Saint::getAll("SHOW COLUMNS FROM `st_blocks_$name`");
					foreach ($sparse->setting as $setting) {
						$done = false;
						foreach ($columns as $column) {
							if ($column[0] == $setting[0])
								$done = true;						
						}
						if (!$done) {
							try {
								$att = $setting->attributes();
								if(isset($att['datatype'])) {
									$datatype = $att['datatype'];
								} else {
									$datatype = "varchar(255)";
								}
								if (isset($att['default'])) {
									if ($att['default'] == "now") {
										$default = "CURRENT_TIMESTAMP";
									} else {
										$default = "'$att[default]'";
									}
								} else {
									$default = "''";
								}
								Saint::query("ALTER TABLE `st_blocks_$name` ADD COLUMN `$setting[0]` $datatype NOT NULL DEFAULT $default");
							} catch (Exception $r) {
								Saint::logError("Couldn't add column $setting to st_blocks_$name:".$r->getMessage(),__FILE__,__LINE__);
							}
						}
					}
				} catch (Exception $g) {
					Saint::logError("Error selecting table columns for st_blocks_$name . ".$g->getMessage(),__FILE__,__LINE__);
				}
			} catch (Exception $e) {
				try {
					$ctq = <<<EOT
CREATE TABLE IF NOT EXISTS `st_blocks_$name` (
	`id` int UNSIGNED NOT NULL AUTO_INCREMENT,
	`enabled` boolean NOT NULL DEFAULT false,

EOT;
					foreach ($sparse->setting as $setting) {
						$att = $setting->attributes();
							if(isset($att['datatype']))
								$datatype = $att['datatype'];
							else
								$datatype = "varchar(255)";
					
							if (isset($att['default'])) {
								if ($att['default'] == "now") {
									$default = "CURRENT_TIMESTAMP";
								} else {
									$default = "'$att[default]'";
								}
							} else {
								$default = "''";
							}
						if ($safe = Saint::sanitize($setting,SAINT_REG_NAME)) {
							$ctq .= "\t`$safe` $datatype NOT NULL DEFAULT $default,\n";
						} else
							Saint::logError("$setting is not a valid block name.",__FILE__,__LINE__);
					}
					$ctq .= <<<EOT
	PRIMARY KEY (`id`)
) ENGINE=InnoDB;

EOT;
						Saint::query($ctq);
					} catch (Exception $f) {
						Saint::logError("Problem creating table st_blocks_".$sparse->name.": " . $f->getMessage(),__FILE__,__LINE__);
					}
			}
			
			foreach ($sparse->setting as $node)
				$settings[] = $node;
		}
	}
	
	/**
	 * Recursively scan directory for XML files.
	 * @param string $dir Directory to scan.
	 * @param int $limit Depth limit for recursion.
	 * @return string[] Matching files.
	 */
	public static function recursiveScan($dir, $extensions = null, $limit = 10, $counter = 0) {
		if ($counter >= $limit)
			return array();
		$matchingfiles = array();
		$files = scandir($dir);
		foreach ($files as $file) {
			$file = $dir."/".$file;
			if (is_dir($file)) {
				if (!preg_match('/\.$/',$file))
					$matchingfiles = array_merge($matchingfiles, Saint_Model_Block::recursiveScan($file,$extensions,$limit,$counter+1));
			} elseif ($extensions != null) {
				if (!is_array($extensions))
					$extensions = array($extensions);
				foreach ($extensions as $ext) {
					if (preg_match('/\.'.$ext.'$/',$file)) {
						$matchingfiles[] = $file;
						break;
					}
				}
			} else {
				$matchingfiles[] = $file;
			}
		}
		return $matchingfiles;
	}
	
	/**
	 * Get names of settings for given block.
	 * @param string $name Block name for which to retrieve settings.
	 * @return string[] Names of settings.
	 */
	public static function getSettings($name) {
		if ($name = Saint_Model_Block::formatForTable(Saint::sanitize($name))) {
			try {
				return Saint::getAll("SHOW COLUMNS FROM `st_blocks_$name`");
			} catch (Exception $g) {
				Saint::logError("Error selecting table columns for st_blocks_$name . ".$g->getMessage(),__FILE__,__LINE__);
				return array();
			}
		} else {
			Saint::logError("Invalid block name $name.",__FILE__,__LINE__);
			return array();
		}
	}
	
	protected $_id;
	protected $_uid;
	protected $_name;
	protected $_settings;
	protected $_settingnames;
	protected $_enabled;
	protected $_categories;
	protected $_cats_to_delete;
	protected $_cats_to_add;
	
	/**
	 * Create a dynamic block model.
	 * @param string $name Name of block template.
	 * @param int $id ID of individual block.
	 * @return boolean True if product is loaded, false otherwise.
	 */
	public function __construct($name = null, $id = null) {
		if ($name != null && $id != null) {
			if ($this->load($name,$id)) {
				return 1;
			} else {
				$this->_id = 0;
				$this->_uid = 0;
				$this->_settings = array();
				$this->_settingnames = array();
				$this->_categories = array();
				$this->_cats_to_add = array();
				$this->_cats_to_delete = array();
				return 0;
			}
		}
	}
	
	/**
	 * Load block information for given name/ID from the database.
	 * @param string $name Name of block template.
	 * @param int $id ID of individual block.
	 * @return boolean True if successful, false otherwise.
	 */
	public function load($name,$id) {
		$id = Saint::sanitize($id,SAINT_REG_ID);
		$name = Saint::sanitize($name);
		$settingnames = array();
		if ($id && $name) {
			$settings = Saint_Model_Block::getSettings($name);
			if (is_array($settings)) {
				$columns = '';
				foreach ($settings as $setting) {
					$columns .= "`$setting[0]`,";
					$settingnames[] = $setting[0];
				}
				$columns = chop($columns,',');
			} else
				$columns = "`id`,`enabled`";
			try {
				$bname = Saint_Model_Block::formatForTable($name);
				$info = Saint::getRow("SELECT $columns FROM `st_blocks_$bname` WHERE `id`='$id'");
				$this->_id = $id;
				$this->_enabled = $info[1];
				$this->_name = $name;
				$this->_settingnames = $settingnames;
				$this->_categories = array();
				$this->_cats_to_add = array();
				$this->_cats_to_delete = array();
				for ($i = 0; $i < sizeof($settingnames); $i++)
					$this->_settings[$settingnames[$i]]=$info[$i];
				
				// Get categories
				try {
					$getcats = Saint::getAll("SELECT `c`.`id`,`c`.`name` FROM `st_categories` as `c`,`st_blockcats` as `b` WHERE `b`.`blockid`='".$this->getUid()."' AND `b`.`catid`=`c`.`id`");
					$cats = array();
					foreach ($getcats as $getcat) {
						$cats[$getcat[0]] = $getcat[1];
					}
					$this->_categories = $cats;
				} catch (Exception $e) {
					if ($e->getCode()) {
						Saint::logError("Problem getting categories for block id '".$this->getUid()."': ".$e->getMessage(),__FILE__,__LINE__);
						return 0;
					}
				}
				return 1;
			} catch (Exception $e) {
				Saint::logError("Cannot load Block model with name $name and ID $id. Error: " . $e->getMessage(),__FILE__,__LINE__);
				return 0;
			}
		} else {
			Saint::logError("Invalid block name/id $name / $id.",__FILE__,__LINE__);
			return 0;
		}
	}
	
	/**
	 * Create a new dynamic block for given template and load it into the model.
	 * @param string $name Name of block template.
	 * @return boolean True for success, false otherwise.
	 */
	public function loadNew($name) {
		if ($name = Saint::sanitize($name)) {
			$fname = Saint_Model_Block::formatForTable($name);
			try {
				Saint::query("INSERT INTO `st_blocks_$fname` () VALUES ()");
				if ($this->load($name,Saint::getLastInsertId())) {
					$btid = Saint_Model_Block::getBlockTypeId($name);
					try {
						Saint::query("INSERT INTO `st_blocks` (`blocktypeid`,`blockid`) VALUES ('$btid','$this->_id')");
					} catch (Exception $h) {
						Saint::logError("Unable to add block: ".$h->getMessage(),__FILE__,__LINE__);
					}
					return 1;
				} else
					return 0;
			} catch (Exception $e) {
				Saint::logError("Failed to add new block named $fname because",$e->getMessage(),__FILE__,__LINE__);
				return 0;
			}
		}
	}
	
	/**
	 * Get the block ID.
	 * @return int ID for the loaded block.
	 */
	public function getId() {
		return $this->_id;
	}
	
	/**
	 * Get the block's globally unique ID.
	 * @return int Unique ID for the loaded block.
	 */
	public function getUid() {
		return Saint_Model_Block::getBlockUid($this->_name,$this->_id);
	}
	
	/**
	 * Get the name of the block template.
	 * @return string Template name for the loaded block.
	 */
	public function getName() {
		return $this->_name;
	}
	
	/**
	 * Get URL on which given block can be found.
	 * @param string $block Name of block template.
	 * @param int $id ID of individual block.
	 * @return string URL to view block.
	 */
	public function getUrl() {
		try {
			return Saint::getOne("SELECT `url` FROM `st_pageblocks` WHERE `block`='$this->_name'");
		} catch (Exception $t) {
			if ($t->getCode()) {
				Saint::logWarning("Problem selecting block URL: ".$t->getMessage(),__FILE__,__LINE__);
			}
			return SAINT_URL;
		}
	}
	
	/**
	 * Get all URLs which result in this block being rendered.
	 * @return array 2d array with inner containing page IDs and URLs.
	 */
	public function getAllUrls() {
		try {
			return Saint::getAll("SELECT `pageid`,`url` FROM `st_pageblocks` WHERE `block`='$this->_name'");
		} catch (Exception $t) {
			if ($t->getCode()) {
				Saint::logWarning("Problem selecting block URLs: ".$t->getMessage(),__FILE__,__LINE__);
			}
			return array();
		}
	}
	
	/**
	 * Get the setting names associated with the loaded block.
	 * @return string[] Setting names.
	 */
	public function getSettingNames() {
		return $this->_settingnames;
	}
	
	/**
	 * Get the setting names and values associated with the loaded block.
	 * @return string[] Setting names (keys) and values (values).
	 */
	public function getAllSettings() {
		return $this->_settings;
	}
	
	/**
	 * Get the value of the given setting name for the loaded block.
	 * @param string $setting Name of the requested setting.
	 * @return string Value of the requested setting.
	 */
	public function get($setting) {
		if (isset($this->_settings[$setting]))
			return $this->_settings[$setting];
		else {
			Saint::logError("No such setting $setting for block $this->_name.",__FILE__,__LINE__);
			return '';
		}
	}
	
	/**
	 * Change the value of the given setting name for the loaded block.
	 * @param string $setting Name of the setting.
	 * @return string Value to assign to the setting.
	 */
	public function set($setting,$value) {
		$setting = Saint::sanitize($setting,SAINT_REG_NAME);
		$value = Saint::sanitize($value);
		if (in_array($setting,$this->_settingnames)) {
			$this->_settings[$setting] = $value;
			return 1;
		} else
			return 0;
	}
	
	/**
	 * Add the loaded block to the given category.
	 * @param string $category Name of category to which the block is to be added.
	 * @return boolean True for success, false for failure.
	 */
	public function addToCategory($category) {
		$scategory = Saint::sanitize($category,SAINT_REG_NAME);
		if ($scategory) {
			// Add to categories array if not already present.
			if (!in_array($scategory,$this->_categories)) {
				$this->_categories[] = $scategory;
				// Notify system this is a change and must be added to the db when saved.
				if (!in_array($scategory,$this->_cats_to_add)) {
					$this->_cats_to_add[] = $scategory;
				}
			}
			// Notify the system not to delete this category (to nullify in case of delete->add->save)
			if (in_array($scategory,$this->_cats_to_delete)) {
				$index = array_search($scategory,$this->_cats_to_delete);
				unset($this->_cats_to_delete[$index]);
			}
			return 1;
		} else {
			Saint::logError("Invalid category name: '$category'.",__FILE__,__LINE__);
			return 0;
		}
	}
	
	/**
	 * Remove the loaded block from the given category.
	 * @param string $category Name of category from which the block is to be removed.
	 * @return boolean True for success, false for failure.
	 */
	public function removeFromCategory($category) {
		$scategory = Saint::sanitize($category,SAINT_REG_NAME);
		if ($scategory) {
			// Remove from categories array if present
			if (in_array($scategory,$this->_categories)) {
				$index = array_search($scategory,$this->_categories);
				unset($this->_categories[$index]);
				// Notify system this is a change and must be removed from the db when saved.
				if (!in_array($scategory,$this->_cats_to_delete)) {
					$this->_cats_to_delete[] = $scategory;
				}
			}
			// Notify the system not to add this category (to nullify in case of add->delete->save)
			if (in_array($scategory,$this->_cats_to_add)) {
				$index = array_search($scategory,$this->_cats_to_add);
				unset($this->_cats_to_add[$index]);
			}
			return 1;
			$this->_cats_to_delete[] = $scategory;
		} else {
			Saint::logError("Invalid category name: '$category'.",__FILE__,__LINE__);
			return 0;
		}
	}
	
	/**
	 * Set the loaded block's categories en masse.
	 * @param string[] $newcats Array of category names for the block.
	 */
	public function setCategories($newcats) {
		if (!is_array($newcats))
			$newcats = explode(',',$newcats);
		foreach (Saint::getAllCategories() as $cat) {
			if (in_array($cat,$newcats)) {
				$this->addToCategory($cat);
			} else {
				$this->removeFromCategory($cat);
			}
		}
	}
	
	/**
	 * Get the loaded block's categories.
	 * @return string[] Array of category names.
	 */
	public function getCategories() {
		return $this->_categories;
	}
	
	/**
	 * Check if loaded block is in at least one of the given categories.
	 * @param string[] $category Array of category names to check. Also accepts scalar category name.
	 * @return boolean True if block is in at least one of the given categories, false otherwise.
	 */
	public function inCategory($category) {
		if (!is_array($category))
			$category = array($category);
		foreach ($category as $cname) {
			if (in_array($cname,$this->_categories)) {
				return 1;
			}
		}
		return 0;
}
	
	/**
	 * Flag the loaded block as disabled.
	 */
	public function disable() {
		$this->_enabled = false;
	}
	
	/**
	 * Flag the loaded block as enabled.
	 */
	public function enable() {
		$this->_enabled = true;
	}
	
	/**
	 * Get enabled status of loaded block.
	 * @return boolean True if enabled, false otherwise.
	 */
	public function isEnabled() {
		return $this->_enabled;
	}
	
	/**
	 * Save the loaded information to the database.
	 * @return boolean True for success, false for failure.
	 */
	public function save() {
		if ($this->_id && $this->_name != "") {
			try {
				$name = Saint_Model_Block::formatForTable($this->_name);
				$id = $this->_id;
				$set = '';
				foreach ($this->_settings as $setting=>$value) {
					if ($setting != 'id') {
						$set .= "`$setting`='$value',";
					}
				}
				$set .= "enabled='".$this->_enabled."'";
				Saint::query("UPDATE `st_blocks_$name` SET $set WHERE `id`='$id'");
				foreach ($this->_cats_to_add as $cat) {
					$this->dbAddToCategory($cat); }
				foreach ($this->_cats_to_delete as $cat) {
					$this->dbRemoveFromCategory($cat); }
				return 1;
			} catch (Exception $e) {
				Saint::logError("Failed to save block $name with id $id because ".$e->getMessage(),__FILE__,__LINE__);
				return 0;
			}
		} else {
			Saint::logError("You must load a block before calling save.",__FILE__,__LINE__);
			return 0;
		}
	}
	

	/**
	 * Add the loaded block to the given category.
	 * @param string $category Name of category to which the block is to be added.
	 * @return boolean True for success, false for failure.
	 */
	private function dbAddToCategory($category) {
		$scategory = Saint::sanitize($category,SAINT_REG_NAME);
		if ($scategory) {
			$id = Saint_Model_Category::getId($scategory);
			if (!$id) {
				$id = Saint_Model_Category::addCategory($scategory);
			}
			if (!$id) {
				Saint::logError("Problem assigning block to category '$scategory'. Unable to get category ID.",__FILE__,__LINE__);
				return 0;
			} else {
				try {
					Saint::query("INSERT INTO `st_blockcats` (`catid`,`blockid`) VALUES ('$id','".$this->getUid()."')");
					return 1;
				} catch (Exception $e) {
					if ($e->getCode()) {
						Saint::logError("Problem adding block uid '".$this->getUid()."' to category id '$id': ".$e->getMessage(),__FILE__,__LINE__); }
					return 0;
				}
			}
		} else {
			Saint::logError("Invalid category name: '$category'.",__FILE__,__LINE__);
			return 0;
		}
	}
	
	/**
	 * Remove the loaded block from the given category.
	 * @param string $category Name of category from which the block is to be removed.
	 * @return boolean True for success, false for failure.
	 */
	private function dbRemoveFromCategory($category) {
		$scategory = Saint::sanitize($category,SAINT_REG_NAME);
		if ($scategory) {
			$id = Saint_Model_Category::getId($scategory);
			if (!$id) {
				# No id... it can't be part of a category that doesn't exist, so our job is done.
				return 1;
			} else {
				try {
					Saint::query("DELETE FROM `st_blockcats` WHERE `catid`='$id' AND `blockid`='".$this->getUid()."'");
					return 1;
				} catch (Exception $e) {
					Saint::logError("Problem removing block id '$this->_id' from category id '$id': ".$e->getMessage(),__FILE__,__LINE__);
					return 0;
				}
			}
		} else {
			Saint::logError("Invalid category name: '$category'.",__FILE__,__LINE__);
			return 0;
		}
	}
}
