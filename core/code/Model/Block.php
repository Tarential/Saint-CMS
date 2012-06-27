<?php

/**
 * Model of a content block for the Saint framework.
 * @author Preston St. Pierre
 * @package Saint
 */
class Saint_Model_Block {
	/**
	 * Get the ID number for the current block template; create entry if not found.
	 * @param string $name Name of block template.
	 * @param array $options Options for new block type.
	 * @return int ID number of current block template or zero for failure.
	 */
	public static function getBlockTypeId($name, $options = array()) {
		$sname = Saint::sanitize($name,SAINT_REG_NAME);
		if ($sname) {
			try {
				return Saint::getOne("SELECT `id` FROM `st_blocktypes` WHERE `name`='$sname'");
			} catch (Exception $f) {
				if ($f->getCode()) {
					Saint::logError("Problem selecting block type ID: ".$f->getMessage(),__FILE__,__LINE__);
				}
				return Saint_Model_Block::createBlockType($name,$options);
			}
		} else {
			Saint::logError("Name '$name' did not match valid patterns.",__FILE__,__LINE__);
			return 0;
		}
	}
	
	/**
	 * Create block type entry in database for given name and model.
	 * Not meant to be called directly. Call getBlockTypeId instead; if no ID is found it will be created.
	 * @param string $name Name of new block type.
	 * @param array $options Options for new block type.
	 * @return int $id ID of new block type or 0 on failure.
	 */
	private static function createBlockType($name,$options = array()) {
		$sname = Saint::sanitize($name,SAINT_REG_NAME);
		if (isset($options['model']) && $options['model'] != null) {
			$modname = ',`model`';
			$modval = ",'".Saint::sanitize($options['model'])."'";
		} else {
			$modname = '';
			$modval = '';
		}
		try {
			Saint::query("INSERT INTO `st_blocktypes` (`name`$modname) VALUES ('$sname'$modval)");
			return Saint::getLastInsertId();
		} catch (Exception $g) {
			Saint::logError("Problem inserting block type '$sname' into DB: ".$g->getMessage(),__FILE__,__LINE__);
		}
	}
	
	/**
	 * Get the name of the model to use for given block type.
	 * @param string $name Name of block type
	 * @return string Name of model to use.
	 */
	public static function getBlockModel($name) {
		$sname = Saint::sanitize($name,SAINT_REG_NAME);
		$default = "Saint_Model_Block";
		try {
			$model = Saint::getOne("SELECT `model` FROM `st_blocktypes` WHERE `name`='$sname'");
			if (class_exists($model) && ($model == $default || is_subclass_of($model,$default))) {
				return $model;
			}
		} catch (Exception $e) {
			if ($e->getCode()) {
				Saint::logError("Problem selecting block type ID: ".$f->getMessage(),__FILE__,__LINE__);
			}
		}
		return $default;
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
	public static function includeBlock($block_name, $arguments = array()) {
		if (isset($arguments['repeat']) && $arguments['repeat'] > 0) {
			return Saint::includeRepeatingBlock($block_name,$arguments);
		} else {
			if (isset($arguments['container']) && $arguments['container'] != "")
				$container = $arguments['container'];
			else
				$container = false;
			if (isset($arguments['view']) && $arguments['view'] != "")
				$view = Saint::sanitize($arguments['view'],SAINT_REG_NAME);
			else
				$view = false;
			if (isset($arguments['get']) && $arguments['get'] != "")
				$get = Saint::sanitize($arguments['get'],SAINT_REG_BOOL);
			else
				$get = false;
			$page = Saint::getCurrentPage();
			$block_name = Saint::sanitize($block_name,SAINT_REG_NAME);
			$block_model = Saint_Model_Block::getBlockModel($block_name);
			if ($block_name) {
				$id = $page->getBlockId();
				if (isset($arguments['block'])) {
					$block = $arguments['block'];
				} else {
					$block = new $block_model();
				}
				if (!$view)
					$view = $block_name;
				if (file_exists(Saint::getThemeDir() .  "/blocks/".$view.".php"))
					$incfile = Saint::getThemeDir() .  "/blocks/".$view.".php";
				elseif (file_exists(SAINT_SITE_ROOT .  "/core/blocks/".$view.".php"))
					$incfile = SAINT_SITE_ROOT .  "/core/blocks/".$view.".php";
				else {
					Saint::logError("Cannot find view $view.",__FILE__,__LINE__); 
					return 0; }
				if ($get) {
					ob_start(); }
				if (preg_match('/^layouts/',$view))
					include $incfile;
				else {
					if ($container) {
						echo "<div id=\"saint_".preg_replace('/\//','_',$block_name)."\" class=\"saint-block\">\n";
						include $incfile;
						echo "\n</div>";
					} else
						include $incfile;
				}
				if ($get) {
					$return = ob_get_clean();
				} else {
					$return = 1;
				}
				return $return;
			} else {
				Saint::logError("Block name '$block_name' could not be validated.",__FILE__,__LINE__);
				return 0;
			}
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
			$orderby = "`b`.`id`";			
			$name = Saint_Model_Block::formatForTable($sblock);
			$where = "WHERE `b`.`id`=`u`.`blockid` AND `u`.`blocktypeid`=`t`.`id` AND `t`.`name`='$sblock'";
			$category = '';
			$collection = false;
			
			# Compile arguments
			if (is_array($arguments)) {
				if (isset($arguments['collection']))
					$collection = $arguments['collection'];
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
							$where .= " AND `b`.`$lsm`$eq'$rsm'";
						}
					} else {
						if(isset($arguments['matches'][2]))
								$eq = Saint::sanitize($arguments['matches'][2]);
							else
								$eq = "=";
						$where .= " AND `b`.`".Saint::sanitize($arguments['matches'][0])."`$eq'".Saint::sanitize($arguments['matches'][1])."'";
					}
				}
				if (isset($arguments['search'])) {
					if (is_array($arguments['search'][0])) {
						foreach ($arguments['search'] as $match)
							$where .= " AND `b`.`".Saint::sanitize($match[0])."` LIKE '".Saint::sanitize($match[1])."'";
					} else
						$where .= " AND `b`.`".Saint::sanitize($arguments['search'][0])."` LIKE '".Saint::sanitize($arguments['search'][1])."'";
				}
			}
			
			# Set up query
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
			
			if ($collection) {
				$sel = "*";
			} else {
				$sel = "`id`";
			}
			$tables = "`st_blocks_$name` as `b`,`st_blocks` as `u`,`st_blocktypes` as `t`";
			# Category filtering
			if ($category != '') {
				$tables .= ",`st_blockcats` as `bc`,`st_categories` as `c`";
				$where .= " AND `u`.`id`=`bc`.`blockid` AND `c`.`id`=`bc`.`catid` AND `c`.`name`='$category'";
			}
			
			return "SELECT `b`.$sel,`u`.`id`,`u`.`page_id` FROM $tables $where $obs $ls";
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
			if (isset($arguments['collection']) && $arguments['collection'] == true) {
				$real_blocks = array();
				$setting_names = Saint_Model_Block::getSettings($block);
				$model = Saint_Model_Block::getBlockModel($block);
				foreach ($saved_blocks as $sb) {
					$settings = array();
					for ($i = 2; $i < sizeof($setting_names); $i++) {
						$settings[$setting_names[$i][0]] = $sb[$i];
					}
					$settings['uid'] = $sb[sizeof($setting_names)];
					$settings['page_id'] = $sb[sizeof($setting_names)+1];
					$real_blocks[] = new $model($block,$sb[0],$sb[1],$settings);
				}
				$saved_blocks = $real_blocks;
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
	public static function includeRepeatingBlock($block, $arguments = array()) {
		$sblock = Saint::sanitize($block);
		if ($sblock) {
			$arguments['collection'] = true;
			// Allow the blocks to be passed as an argument with the request
			// to avoid repeating the query if it has been made already.
			if (isset($arguments['blocks'])) {
				$saved_blocks = $arguments['blocks'];
			} else {
				$saved_blocks = Saint_Model_Block::getBlocks($block,$arguments);
			}
			
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
			if (isset($arguments['get']))
				$get = $arguments['get'];
			else
				$get = false;
			if (isset($arguments['container']))
				$container = $arguments['container'];
			else
				$container = true;
			if (isset($arguments['view']) && $arguments['view'] != "")
				$view = Saint::sanitize($arguments['view'],SAINT_REG_NAME);
			else
				$view = false;
			
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
	
			# If requested, cache the block output for return
			if ($get) {
			 ob_start(); }
			
			# Display the block
			if ($container) {
				echo "<div class=\"saint-block repeating sbn-".preg_replace('/\//','_',$sblock)."\">\n";
				echo "<div class=\"add-button hidden\">Add New <span class=\"block-name\">".Saint_Model_Block::formatForDisplay($sblock)."</span></div>";
			}
			if (sizeof($saved_blocks) == 0) {
				if (isset($arguments['label'])) {
					echo "<p>$arguments[label]</p>";
				} else {
					echo "<p>Sorry, no blocks found to match selected criteria.</p>";
				}
			} else {
				foreach ($saved_blocks as $block) {
					$page->setBlockId($block->getId());
					//$page->addBlock("block/".$bid."/".$block);
					if ($container) {
						echo '<div class="block-item">';
						echo "<div class=\"sbid-".$block->getId()." edit-button hidden\">Edit ".Saint_Model_Block::formatForDisplay($sblock)." <span class=\"block-name\">".$block->getId()."</span></div>"; }
					Saint::includeBlock($sblock,array('view'=>$view,'block'=>$block));
					if ($container) {
						echo "</div>\n"; }
					$page->setBlockId(0);
				}
				if ($paging) {
					$args = $page->getArgs();
					$url = SAINT_URL."/".$page->getName();
					if (isset($args["subids"])) {
						foreach ($args["subids"] as $subid) {
							$url .= "/" . $subid;
						}
					}
					$cur = 0;
					$mark = "?";
					foreach ($args as $key=>$val) {
						if ($key != "pnum" && $key != "btid" && $key != "subids" && $key != "edit") {
							$url .= $mark."$key=$val";
							$mark = "&";
						}
					}
					$btid = Saint_Model_Block::getBlockTypeId($page->crb);
					$page->crburl = chop($url,'/').$mark."btid=$btid&pnum=";
					$page->crbpstart = Saint::getStartingNumber($btid)-1;
					$page->crbnstart = Saint::getStartingNumber($btid)+1;
					$page->crbnumpages = ceil($numresults / $repeat);
					if ($page->crbnumpages > 1)
						Saint::includeBlock("navigation/pager");
				}
			}

			if ($container) {
				echo "</div>"; }
			if ($get) {
				$return = ob_get_clean();
			} else {
				$return = 1;
			}
			return $return;
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
	public static function updateBlocks() {
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
			// Requesting the block type ID will create one if it doesn't exist.
			if (isset($sparse->model) && $sparse->model != "") {
				Saint_Model_Block::getBlockTypeId($sparse->name,array("model"=>$sparse->model));
			}
			
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
								if (strtoupper($att['default']) == "NOW") {
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
	 * Recursively scan directory for files matching given extension.
	 * @param string $dir Directory to scan.
	 * @param array $extensions Extensions to match.
	 * @param int $limit Depth limit for recursion.
	 * @param int $counter Current depth of recursion.
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
	protected $_page_id;
	
	/**
	 * Create a dynamic block model.
	 * By passing initial settings to the constructor we can load fixed values (such as ID) directly into the model.
	 * @param string $name Name of block template.
	 * @param int $id ID of individual block.
	 * @return boolean True if product is loaded, false otherwise.
	 */
	public function __construct($name = null, $id = null, $enabled = null, $settings = array()) {
		if ($name != null && $id != null) {
			if (empty($settings)) {
				if ($this->load($name,$id)) {
					return 1;
				}
			} else {
				$this->_id = Saint::sanitize($id,SAINT_REG_ID);
				$this->_enabled = Saint::sanitize($enabled,SAINT_REG_BOOL);
				$this->_name = Saint::sanitize($name,SAINT_REG_NAME);
				$this->_settingnames = array();
				$this->_categories = null;
				if (isset($settings['page_id'])) {
					$this->_page_id = $settings['page_id'];
				} else {
					$this->_page_id = 0;
				}
				if (isset($settings['uid'])) {
					$this->_uid = $settings['uid'];
				} else {
					$this->_uid = 0;
				}
				foreach ($settings as $key=>$val) {
					$sname = Saint::sanitize($key);
					$this->_settingnames[] = $sname;
					$this->_settings[$sname] = Saint::sanitize($val);
				}
				return 1;
			}
		} else {
			$this->_id = 0;
			$this->_uid = 0;
			$this->_page_id = 0;
			$this->_settings = array();
			$this->_settingnames = array();
			$this->_categories = array();
			$this->_cats_to_add = array();
			$this->_cats_to_delete = array();
			return 0;
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
					$columns .= "`b`.`$setting[0]`,";
					$settingnames[] = $setting[0];
				}
				$columns = chop($columns,',');
			} else
				$columns = "`b`.`id`,`b`.`enabled`";
			try {
				$bname = Saint_Model_Block::formatForTable($name);
				$info = Saint::getRow("SELECT `u`.`id`,`u`.`page_id`,$columns FROM `st_blocks_$bname` as `b`, `st_blocks` as `u`, `st_blocktypes` as `t` WHERE `b`.`id`='$id' AND `u`.`blockid`=`b`.`id` AND `u`.`blocktypeid`=`t`.`id` AND `t`.`name`='$name'");
				$this->_id = $id;
				$this->_uid = $info[0];
				$this->_page_id = $info[1];
				$this->_enabled = $info[3];
				$this->_name = $name;
				$this->_settingnames = $settingnames;
				$this->_categories = null;
				$this->_cats_to_add = array();
				$this->_cats_to_delete = array();
				for ($i = 0; $i < sizeof($settingnames); $i++)
					$this->_settings[$settingnames[$i]]=$info[$i+2];
				
				return 1;
			} catch (Exception $e) {
				Saint::logError("Cannot load Block model with name '$name' and ID '$id'. Error: " . $e->getMessage(),__FILE__,__LINE__);
				return 0;
			}
		} else {
			Saint::logError("Invalid block name/id '$name' / '$id'.",__FILE__,__LINE__);
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
	 * Category loading called only as needed to increase performance.
	 */
	private function loadCategories() {
		if ($this->_id) {
			try {
				$getcats = Saint::getAll("SELECT `c`.`id`,`c`.`name` FROM `st_categories` as `c`,`st_blockcats` as `b` WHERE `b`.`blockid`='".$this->getUid()."' AND `b`.`catid`=`c`.`id`");
				$cats = array();
				foreach ($getcats as $getcat) {
					$cats[$getcat[0]] = $getcat[1];
				}
				$this->_categories = $cats;
				return 1;
			} catch (Exception $e) {
				$this->_categories = array();
				if ($e->getCode()) {
					Saint::logError("Problem getting categories for block id '".$this->getUid()."': ".$e->getMessage(),__FILE__,__LINE__);
					return 0;
				} else {
					return 1;
				}
			}
		} else {
			return 0;
		}
	}
	
	/**
	 * Get label of given name unique to this block.
	 * @param string $name Name of label to retrieve.
	 * @return string Contents of label.
	 */
	public function getLabel($name, $default = '', $options = array()) {
		$name = "block/" . $this->_id . "/" . $this->_name . "/n/" . $name;
		return Saint::getLabel($name,$default,$options);
	}
	
	/**
	 * Get image label of given name unique to this block.
	 * @param string $name Name of the image label.
	 * @param string $options Options for displaying image.
	 */
	public function includeImage($name, $options = array()) {
		$name = "block/" . $this->_id . "/" . $this->_name . "/n/" . $name;
		Saint_Model_ImageLabel::includeImage($name, $options);
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
		return $this->_uid;
	}
	
	/**
	 * Get the name of the block template.
	 * @return string Template name for the loaded block.
	 */
	public function getName() {
		return $this->_name;
	}
	
	/**
	 * Get URL on which block can be found.
	 * Until overridden by a child class, this function uses the base page URL.
	 * @return string URL to access block.
	 */
	public function getUrl() {
		return $this->getPageUrl();
	}
	
	/**
	 * Get URL for page on which block was added.
	 * @return string URL for page.
	 */
	public function getPageUrl() {
		if ($this->_page_id) {
			$bp = new Saint_Model_Page();
			if ($bp->loadById($this->_page_id)) {
				return $bp->getUrl();
			}
		}
		return SAINT_URL;
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
		if ($this->_categories == null) {
			$this->loadCategories(); }
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
		if ($this->_categories == null) {
			$this->loadCategories(); }
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
		foreach (Saint::getCategories() as $cat) {
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
		if ($this->_categories == null) {
			$this->loadCategories(); }
		return $this->_categories;
	}
	
	/**
	 * Check if loaded block is in at least one of the given categories.
	 * @param string[] $category Array of category names to check. Also accepts scalar category name.
	 * @return boolean True if block is in at least one of the given categories, false otherwise.
	 */
	public function inCategory($category) {
		if ($this->_categories == null) {
			$this->loadCategories(); }
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
	 * Get settings for currently rendering input.
	 * @return array Settings for currently rendering input.
	 */
	public function getInputSettings() {
		return $this->_input_settings;
	}
	
	/**
	 * Render form input for given setting using given options.
	 * Override this function in your child class to customize input types. See Saint_Model_BlogPost.
	 * @param string $setting Field name.
	 * @param array $options Options to use when generating the field.
	 */
	public function renderInput($setting, $options = array()) {
		$page = Saint::getCurrentPage();
		$name = "saint-block-setting-$setting";
		$type = "text";
		$details = array();
		$label = ucfirst($setting).":";
		$data = array(
			"value" => $this->get($setting),
			"static" => true,
		);
		
		if (isset($options['type']))
			$type = $options['type'];
		
		if (isset($options['label']))
			$label = $options['label'];
		
		if ($setting == "id") {
			$type = "hidden";
		}
		
		if (isset($options['details'])) {
			$details = $options['details'];
		}
		
		$this->_input_settings = array(
			"name" => $name,
			"type" => $type,
			"label" => $label,
			"data" => $data,
			"details" => $details,
		);
		
		if ($setting != "enabled")
			Saint::includeBlock("admin/setting-input");
	}
	
	/**
	 * Render a preview for editing the current block.
	 * In this default method the standard view is used. Override it in your child class to customize the preview.
	 * See Saint_Model_BlogPost for an example.
	 */
	public function renderPreview($arguments = array()) {
		if (!isset($arguments['repeat'])) {
			$arguments['repeat'] = 1;
		}
		if (!isset($arguments['matches'])) {
			$arguments['matches'] = array(
				array("id",$this->_id,"="),
			);
		}
		Saint::includeBlock($this->_name,$arguments);
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
				
				$bp = new Saint_Model_Page();
				if (!$bp->loadById($this->_page_id) && Saint::getCurrentPage()->getId() != 0) {
					Saint::query("UPDATE `st_blocks` SET `page_id`='".Saint::getCurrentPage()->getId()."' WHERE `id`='$this->_uid'");
				}
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
