<?php
/**
 * Controller to handle the input for blog posts.
 * @author Preston St. Pierre
 * @package Saint
 */
class Saint_Controller_Blog {
	
	/**
	 * Set up parameters for rendering a blog page.
	 * @param Saint_Model_Page $page Page to process.
	 */
	public static function process($page) {
		$args = $page->getArgs();
		$id = 0;
		$url = SAINT_URL . "/" . $page->getName();
		$category = null;
		
		if (!empty($args['subids'])) {
			if ($args['subids'][0] == "feed") {
				Saint::includeBlock("blog/rss-feed");
				exit();
			} elseif ($args['subids'][0] == "category" && isset($args['subids'][1]) && $args['subids'][1] != "") {
				$category = urldecode($args['subids'][1]);
			} else {
				$post = new Saint_Model_BlogPost();
				$post->loadByUri($args['subids'][0]);
				$id = $post->getId();
				if ($id) {
					$url = $post->getUrl();
				} else {
					# Looking to make a 404 page instead of defaulting to the index?
					# Uncomment this:
					$page->setTempLayout("system/404");
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
				"collection" => true,
			);
			$page->setTempTitle($post->get("title"));
			$page->setTempKeywords(explode(",",$post->get("keywords")));
			$page->setTempDescription($post->get("description"));
		} else {
			if (Saint::getCurrentUser()->hasPermissionTo("edit-block")) {
				$label = "You haven't added any blog posts here yet! Click 'edit page' in the Saint admin menu then click 'Add New Post' to create a post.";
			} else {
				$label = "No blog posts found. Come back later and maybe there will be some!";
			}
			$arguments = array(
				"repeat" => 2,
				"order" => "DESC",
				"orderby" => "postdate",
				"paging" => true,
				"page_id" => $page->getId(),
				"matches" => array(
					array("enabled","1"),
				),
				"label" => $label,
				"collection" => true,
			);
			if ($category != null) {
				$arguments['category'] = Saint_Model_Block::convertNameFromWeb($category);
			}
		}
		$posts = Saint_Model_Block::getBlocks("blog/post",$arguments);
		$arguments['blocks'] = $posts;
		$page->setPosts($posts);
		$page->setPostArgs($arguments);
	}
}