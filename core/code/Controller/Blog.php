<?php
class Saint_Controller_Blog {
	public static function process($page) {
		$args = $page->getArgs();
		$id = 0;
		$url = SAINT_URL . "/" . $page->getName();
		$category = null;
		
		if (!empty($args['subids'])) {
			if ($args['subids'][0] == "feed") {
				$page->setTempLayout("blog/rss");
				$page->render();
				exit();
			} elseif ($args['subids'][0] == "category" && isset($args['subids'][1]) && $args['subids'][1] != "") {
				$category = $args['subids'][1];
			} else {
				$post = new Saint_Model_BlogPost();
				$post->loadByUri($args['subids'][0]);
				$id = $post->getId();
				if ($id) {
					$url = $post->getUrl();
				} else {
					# Looking to make a 404 page instead of defaulting to the index?
					# Uncomment this:
					# $page->setTempLayout("system/404");
				}
			}
		}
		
		if ($id) {
			$arguments = array(
				"matches" => array(
					array("enabled","1"),
					array("id",$id),
				),
				"repeat" => 1,
			);
			$page->setTempTitle($post->get("title"));
			$page->setTempKeywords(explode(",",$post->get("keywords")));
			$page->setTempDescription($post->get("description"));
		} else {
			$arguments = array(
				"repeat" => 2,
				"order" => "DESC",
				"orderby" => "postdate",
				"paging" => true,
				"matches" => array(
					array("enabled","1"),
				),
				"label" => "You haven't created any blog posts yet. Click 'edit page' in the Saint admin menu then click 'Add New Post' to create a post.",
			);
			if ($category != null) {
				$arguments['category'] = Saint_Model_Block::convertNameFromWeb($category);
			}
		}
		$page->setPostArgs($arguments);
	}
}