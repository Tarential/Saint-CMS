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
		if (Saint::getCurrentUser()->hasPermissionTo('edit-block')) {
			$page = Saint::getCurrentPage();
			$page->addblockname = Saint_Model_Block::convertNameFromWeb($block);
			$page->addblock = new Saint_Model_Block();
			
			if (isset($_POST['blockid']) && Saint::sanitize($_POST['blockid'],SAINT_REG_ID)) {
				$page->addblock->load($page->addblockname,$_POST['blockid']);
			} else {
				$page->addblock->loadNew($page->addblockname);
			}

			if (!$page->addblock) {
				$page->setTempLayout("error");
				$page->error = "Failed to load block for editing. Check error logs for further details.";
				return 0;
			}
			
			$page->addblockid = $page->addblock->getId();
			$page->addblockarguments = array(
				"repeat" => 1,
				"start" => 0,
				"matches" => array("id",$page->addblockid,"="),
			);
			$page->setTempLayout("system/editblock");
		} else {
			Saint::logError("User ".Saint::getCurrentUsername()." attempted to edit block ".$block."-".$_POST['blockid'].
				" from IP $_SERVER[REMOTE_ADDR] but was denied access.");
			$page->setTempLayout("error");
			$page->error = "You do not have access to edit data which belongs to other users. This attempt has been logged.";
			return 0;
		}
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
		if (Saint::getCurrentUser()->hasPermissionTo('edit-block')) {
			$page = Saint::getCurrentPage();
			$args = $page->getArgs();
			$page->setTempLayout("system/json");
			$block = new Saint_Model_Block();
			if ($block->load(Saint_Model_Block::convertNameFromWeb($_POST['saint-block-setting-saintname']),$args['edit'])) {
				$allsettings = Saint_Model_Block::getSettings($_POST['saint-block-setting-saintname']);
				if (isset($_POST['saint-block-setting-enabled'])) {
					if ($_POST['saint-block-setting-enabled'])
						$block->enable();
					else
						$block->disable();
				}
			
				if (isset($_POST['saint_edit_block_categories']))
					$cats = $_POST['saint_edit_block_categories'];
				else
					$cats = array();
				$block->setCategories($cats);
				
				foreach ($allsettings as $setting) {
					$sname = "saint-block-setting-".$setting[0];
					if (isset($_POST[$sname]))
						$block->set($setting[0],$_POST[$sname]);
				}
				if ($block->save()) {
					$success = true; }
			}
		} else {
			Saint::logError("User ".Saint::getCurrentUsername()." attempted to edit block ".$_POST['block']."-".$_POST['blockid'].
				" from IP $_SERVER[REMOTE_ADDR] but was denied access.",__FILE__,__LINE__);
			$page->setLayout("error");
			$page->error = "You do not have access to edit data which belongs to other users. This attempt has been logged.";
		}
		$page->jsondata = array(
			'success' => $success,
			'actionlog' => Saint::getActionLog(),
		);
		return $success;
	}
}
