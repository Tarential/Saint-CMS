<?php
/**
 * Repeating block controller.
 * @author Preston St. Pierre
 * @package Saint
 */
class Saint_Controller_Block {
	/**
	 * Load block into admin interface for editing.
	 * @param string $block Name of block to load for editing.
	 * @return boolean True on success, false otherwise.
	 */
	public static function loadBlock($block) {
		$page = Saint::getCurrentPage();
		$blockname = Saint_Model_Block::convertNameFromWeb($block);
		$model = Saint_Model_Block::getBlockModel($blockname);
		$edit_block = new $model();
		$page->setEditBlock($edit_block);
		
		if (isset($_POST['blockid']) && Saint::sanitize($_POST['blockid'],SAINT_REG_ID)) {
			$edit_block->load($blockname,$_POST['blockid']);
		} else {
			$edit_block->loadNew($blockname);
		}

		if (!$edit_block) {
			$page->setTempLayout("system/error");
			$page->addError("Failed to load block for editing. Check error logs for further details.");
			return 0;
		}
		
		$page->setTempLayout("system/block-edit");
			
		return 1;
	}
	/**
	 * Modify block based on input data.
	 * @param int $editid ID of block to edit.
	 * @param string $blockname Name of block to edit.
	 * @return boolean True on success, false otherwise.
	 */
	public static function editBlock($editid,$blockname) {
		$success = false;
	
		$page = Saint::getCurrentPage();
		$args = $page->getArgs();
		$bname = Saint_Model_Block::convertNameFromWeb($_POST['saint-block-setting-saintname']);
		$model = Saint_Model_Block::getBlockModel($bname);
		$block = new $model();
		if ($block->load($bname,$args['edit'])) {
			$allsettings = Saint_Model_Block::getSettings($_POST['saint-block-setting-saintname']);
			if (isset($_POST['saint-block-setting-enabled'])) {
				if ($_POST['saint-block-setting-enabled']) {
					$block->enable();
				} else {
					$block->disable();
				}
			}
		
			if (isset($_POST['saint-edit-block-categories'])) {
				$newcats = $_POST['saint-edit-block-categories'];
			} else {
				$newcats = array();
			}
			$block->setCategories($newcats);
			
			foreach ($allsettings as $setting) {
				$sname = "saint-block-setting-".$setting[0];
				if (isset($_POST[$sname]))
					$val = $_POST[$sname];
				else
					$val = "";
				if (Saint::getCurrentUser()->isInGroup("administrator")) {
					$block->set($setting[0],$val);
				} else {
					$sval = htmlspecialchars($val);
					$block->set($setting[0],$sval);
				}
			}
			if ($block->save()) {
				$success = true; }
		}
		
		$webname = Saint_Model_Block::convertNameToWeb($bname);
		$page->process();
		$output = $page->render(array('get'=>true));
		$str_to_match = '<div class="saint-block repeating sbn-'.$webname.'">';
		$init_pos = strpos($output,$str_to_match);
		if ($init_pos === false) {
			$output = false;
		} else {
			$output = substr($output,$init_pos+strlen($str_to_match));
			$done = false;
			$cur_pos = 0;
			$num_divs = 0;
			while (!$done) {
				$end_pos = strpos($output,'</div>',$cur_pos);
				$start_pos = strpos($output,'<div',$cur_pos);
				if ($end_pos === false) {
					$done = true;
				} else {
					if ($start_pos === false || $start_pos > $end_pos) {
						if ($num_divs <= 0) {
							$output = substr($output,0,$end_pos);
							$done = true;
						} else {
							$num_divs -= 1;
							$cur_pos = $end_pos+6;
						}
					} else {
						$num_divs += 1;
						$cur_pos = $start_pos+5;
					}
				}
			}
		}

		$page->setTempLayout("system/json");
		$page->setJsonData(array(
			'success' => $success,
			'actionlog' => Saint::getActionLog(),
			'data' => $output,
			'block' => $webname,
		));
		return $success;
	}
}
