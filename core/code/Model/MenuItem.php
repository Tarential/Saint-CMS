<?php
/**
 * Model for menu items within the Saint framework.
 * @author Preston St. Pierre
 *
 */
class Saint_Model_MenuItem extends Saint_Model_Block {
	
	/**
	 * Customizations for input fields associated with menu items.
	 * @see core/code/Model/Saint_Model_Block::renderInput()
	 */
	public function renderInput($setting, $options = array()) {
		switch ($setting) {
			case "parent":
				$page_filters = array(
					'layout' => array(
						'comparison_operator' => 'NOT LIKE',
						'match_all' => array('system/%'),
					),
					'parent' => array(
						'comparison_operator' => '=',
						'match_all' => array(0),
					),
				);
				$parents = Saint_Model_Page::getPages($page_filters);
				$parent_options = array(0=>'None');
				foreach ($parents as $parent) {
					$parent_options[$parent->getId()] = $parent->getTitle();
				}
				
				$options['details'] = array("(Optional) Parent item for link.");
				$options['data'] = array('options'=>$parent_options,'static'=>true,'selected'=>$this->get("parent"));
				$options['type'] = "select";
				
				parent::renderInput($setting,$options);
				break;
			case "weight":
				$options['type'] = "select";
				$data = array();
				for ($i = -10; $i <= 10; $i++) {
					$data[$i] = $i;
				}
				$options['data'] = array('options'=>$data,'static'=>true,'selected'=>$this->get("weight"));
				$options['type'] = "select";
				parent::renderInput($setting,$options);
				break;
			default:
				parent::renderInput($setting,$options);
		}
	}
	
	/**
	 * Menu items need no preview.
	 */
	public function renderPreview($arguments = array()) {
		
	}
}
