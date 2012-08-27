<?php if (Saint::getCurrentUser()->hasPermissionTo("admin-overlay")): ?>
<div class="saint-admin-code">
	<script type="text/javascript">
		var SAINT_URL = "<?php echo SAINT_URL; ?>";
		var SAINT_BASE_URL = "<?php echo SAINT_BASE_URL; ?>";
		var SAINT_CLIENT_NONCE = '<?php echo Saint::getCurrentUser()->getNonce(); ?>';
	</script>
	<div class="blackout">&nbsp;</div>
	<div class="saint-admin-overlay">
		<div class="saint-ajax-indicator">&nbsp;</div>
		<div class="saint-logo">&nbsp;</div>
		<div class="saint-pin" title="Pin this overlay open">&nbsp;</div>
		
		<ul class="saint-admin-menu">
			<li class="link settings">Settings</li>
			<li class="link pages">Pages</li>
			<li class="link users">Users</li>
			<?php if (sizeof(Saint::getPages(array('layout'=>'shop/index')))): ?>
			<li class="link shop">Shop</li>
			<?php endif; ?>
			<li class="link files">Files</li>
			<li class="link logout">Logout</li>
		</ul>
		
		<div class="saint-admin-options page-add hidden">
			<form>
				<ul>
					<li><?php echo Saint::genField("saint-add-page-title","text","Name: ", array(
						'static' => true,
					)); ?></li>
					<li><?php echo Saint::genField("saint-add-page-name","text","Identifier: ", array(
						'static' => true,
						'rules' => 'required',
						'classes' => 'saint-validate saint-validate-add-page-name',
					)); ?><div class="hud error add-page-name">Characters, digits, dashes, dots and underscores only.</div></li>
					<?php
						$options = array();
						foreach (Saint::getLayoutNames() as $name=>$title)
							$options[$name] = $title;
					?>
					<li><?php echo Saint::genField("saint-add-page-layout","select","Layout: ", array(
						'options' => $options,
						'static' => true,
					)); ?></li>
					<li><?php echo Saint::genField("saint-add-page-keywords","text","Keywords: ", array(
						'static' => true,
					)); ?></li>
					<li><?php echo Saint::genField("saint-add-page-description","textarea","Description: ", array(
						'static' => true,
					)); ?></li>
					<?php
						$options = array();
						foreach (Saint::getCategories() as $category)
							$options[$category] = $category;
					?>
					<li><?php echo Saint::genField("saint-add-page-categories[]","select","Categories: ", array(
						'options' => $options,
						'selected' => array(),
						'multiple' => true,
						'static' => true,
					)); ?></li>
					<?php $page_filters = array(
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
					?>
					<li><?php echo Saint::genField("saint-add-page-parent","select","Parent: ", array(
						'options' => $parent_options,
						'selected' => 0,
						'static' => true,
					)); ?></li>
				</ul>
			</form>
			<ul>
				<li><div class="link add">Add Page</div></li>
				<li><div class="link back">Back to Page Options</div></li>
			</ul>
		</div>
		
		<div class="saint-admin-options page-options hidden">
			<ul>
				<li class="link add">Add New Page</li>
				<li class="link edit">Edit This Page</li>
				<li class="link delete">Delete This Page</li>
			</ul>
			<h4>Pages:</h4>
			<ul class="page-list">
				<?php $page_filters = array(
					'layout' => array(
						'comparison_operator' => 'NOT LIKE',
						'match_all' => array('system/%'),
					),
				); ?>
				<?php foreach (Saint::getPages($page_filters) as $ipage): ?>
				<li><a href="<?php echo SAINT_URL . "/" . $ipage->getName(); ?>" class="sublist"><?php echo $ipage->getTitle(); ?></a></li>
				<?php endforeach; ?>
			</ul>
		</div>
	
		<div class="saint-admin-options user-options hidden">
			<ul>
				<li class="link add">Add New User</li>
			</ul>
			<h4>Users:</h4>
			<ul class="user-list sublist">
				<?php foreach (Saint::getUsers() as $iuser): ?>
				<li class="link" id="user-<?php echo $iuser->getId(); ?>"><?php echo $iuser->getUsername(); ?></li>
				<?php endforeach; ?>
			</ul>
		</div>
		
		<div class="saint-admin-options site-options hidden">
			<h4>Settings</h4>
			<form class="settings">
				<?php echo Saint::genField("saint-site-title","text","Title:",array(
					"static" => true,
					"value" => Saint::getSiteTitle(),
				)); ?>
				<?php echo Saint::genField("saint-site-keywords","text","Keywords:",array(
					"static" => true,
					"value" => implode(',',Saint::getSiteKeywords()),
				)); ?>
				<?php echo Saint::genField("saint-site-description","textarea","Description:",array(
					"static" => true,
					"value" => Saint::getSiteDescription(),
				)); ?>
				<span class="submit link">Save Settings</span>
			</form>
			
			<h4>Categories</h4>
			<ul class="sublist category-list">
			<?php foreach (Saint::getCategories() as $iid=>$icat): ?>
				<li class="link category-edit cat-<?php echo $iid; ?>"><?php echo $icat; ?><span class="delete close-button">&nbsp;</span></li>
			<?php endforeach; ?>
			</ul>
			<form class="categories">
				<input type="hidden" value="0" name="saint-set-category-id" id="saint-set-category-id" />
				<input type="hidden" value="0" name="saint-delete-category" id="saint-delete-category" />
				<?php echo Saint::genField("saint-add-category","text","Name:",array(
					'static' => true,
					'classes' => 'saint-validate saint-validate-category',
				)); ?>
				<div class="hud error category" style="display:none;"></div>
				<span id="saint-add-category-submit" class="link">Add</span>
				<span id="saint-add-category-cancel" class="link hidden">Cancel</span>
			</form>
		</div>
		
		<div class="saint-admin-options dynamic hidden">&nbsp;</div>
		
		<div class="saint-action-log">
		<?php foreach(Saint::getActionLog() as $action): ?>
		<p><?php echo $action; ?></p>
		<?php endforeach; ?>
		</div>
	</div>
	<?php endif; ?>
	<div class="saint-templates">
		<?php if (Saint::getCurrentUser()->hasPermissionTo("edit-page")): ?>
		<div class="saint-admin-options current-page hidden">
			<span class="close-button">&nbsp;</span>
			<form>
				<input type="hidden" name="saint-edit-page-id" value="<?php echo $page->getId(); ?>" />
				<ul>
					<li><?php echo Saint::genField("saint-edit-page-title","text","Name: ",array('value'=> $page->getTitle(),'static'=>true)); ?></li>
					<li><?php echo Saint::genField("saint-edit-page-name","text","Identifier (URI): ",array(
						'value'=> $page->getName(),
						'static'=>true,
						'classes' => 'saint-validate saint-validate-edit-page-name',
					)); ?><div class="hud error edit-page-name" style="display:none;"></div></li>
					<?php
						$options = array();
						foreach (Saint::getLayoutNames() as $name=>$title)
							$options[$name] = $title;
					?>
					<li><?php echo Saint::genField("saint-edit-page-layout","select","Layout: ",array('options'=>$options,'selected' => $page->getLayout(),'static'=>true)); ?></li>
					<li><?php echo Saint::genField("saint-edit-page-keywords","text","Keywords: ",array('value'=> implode(',',$page->getKeywords()),'static'=>true)); ?></li>
					<li><?php echo Saint::genField("saint-edit-page-description","textarea","Description: ",array('value'=> $page->getDescription(),'static'=>true)); ?></li>
					<?php
						$options = array();
						foreach (Saint::getCategories() as $category)
							$options[$category] = $category;
					?>
					<li><?php echo Saint::genField("saint-edit-page-categories[]","select","Categories: ",
						array('options'=>$options,'selected'=>$page->getCategories(),'multiple'=>true,'static'=>true)); ?></li>
					<?php if (isset($parent_options[$page->getId()])) unset($parent_options[$page->getId()]); ?>
					<li><?php echo Saint::genField("saint-edit-page-parent","select","Parent: ", array(
						'options' => $parent_options,
						'selected' => $page->getParent(),
						'static' => true,
					)); ?></li>
				</ul>
				<span class="link save">Save</span>
			</form>
		</div>
		<?php endif; ?>
		
		<div class="saint-admin-block add-block">
			<div class="overlay">&nbsp;</div>
			<div class="load">&nbsp;</div>
		</div>
		
		<div class="saint-admin-block file-manager">
			<div class="overlay">&nbsp;</div>
			<div class="load">&nbsp;</div>
		</div>
		
		<div class="saint-admin-block shop-manager">
			<div class="overlay">&nbsp;</div>
			<div class="load">&nbsp;</div>
			<?php Saint::includeBlock("shop/admin/nav"); ?>
		</div>
		
		<div class="sle template hidden">
			<div class="blackout">&nbsp;</div>
			<span class="cache hidden" style="display:none;"></span>
			<div class="wysiwyg">
				<div class="toolbar">
					<button class="link save" title="Save">&nbsp;</button>
					<button class="link switch source" title="View Source">&nbsp;</button>
					<button class="link bold" title="Bold">B</button>
					<button class="link italic" title="Italic">I</button>
					<button class="link underline" title="Save">U</button>
					<button class="link a" title="Link">&nbsp;</button>
					<button class="link ul" title="Unordered List">&nbsp;</button>
					<button class="link ol" title="Ordered List">&nbsp;</button>
					<button class="link img" title="Image">&nbsp;</button>
					<select name="heading" class="link heading">
						<option selected="selected" value="none">Heading</option>
						<option value="p">P</option>
						<option value="h1">H1</option>
						<option value="h2">H2</option>
						<option value="h3">H3</option>
						<option value="h4">H4</option>
						<option value="h5">H5</option>
						<option value="h6">H6</option>
					</select>
					<select name="revision" class="link revision">
						<option selected="selected" value="load" class="null">Loading...</option>
					</select>
					<div class="link close-button" title="Close">&nbsp;</div>
				</div>
				<div class="label-value">&nbsp;</div>
			</div>
			<div class="source">
				<div class="toolbar">
					<button class="link save" title="Save">&nbsp;</button>
					<button class="link switch visual" title="View Rich Text">&nbsp;</button>
					<select name="revision" class="link revision">
						<option selected="selected" value="load" class="null">Loading...</option>
					</select>
					<div class="link close-button" title="Close">&nbsp;</div>
				</div>
				<form>
					<input type="hidden" name="label-name" value="" />
					<textarea class="label-value" name="label-value"></textarea>
				</form>
			</div>
		</div>
		
	</div>
</div>