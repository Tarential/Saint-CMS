<?php if (Saint::getCurrentUser()->hasPermissionTo("admin-overlay")): ?>
<script type="text/javascript">
	var SAINT_URL = "<?php echo SAINT_URL; ?>";
</script>
<div id="saint_blackout">&nbsp;</div>
<div id="saint_admin_overlay">
	<div id="saint_ajax_indicator">&nbsp;</div>
	<div id="saint-logo">&nbsp;</div>
	
	<ul id="saint_admin_menu">
		<li id="saint_menu_link_pages" class="link saint_menu_link_pages">Pages</li>
		<li id="saint_menu_link_users" class="link">Users</li>
		<li id="saint_menu_link_categories" class="link">Categories</li>
		<li id="saint_menu_link_shop" class="link">Shop</li>
		<li id="saint_menu_link_files" class="link">Files</li>
		<li id="saint_menu_link_logout" class="link">Logout</li>
	</ul>
	
	<div id="saint_admin_page_add" class="saint_admin_options hidden">
		<form>
			<ul>
				<li><?php echo Saint::genField("saint_add_page_title","text","Name: "); ?></li>
				<li><?php echo Saint::genField("saint_add_page_name","text","Identifier: "); ?></li>
				<?php
					$options = array();
					foreach (Saint::getLayoutNames() as $layout)
						$options[$layout] = ucfirst($layout);
				?>
				<li><?php echo Saint::genField("saint_add_page_layout","select","Layout: ",array('options'=>$options)); ?></li>
				<li><?php echo Saint::genField("saint_edit_page_keywords","text","Keywords: ",array('value'=> implode(',',$page->getMetaKeywords()))); ?></li>
				<li><?php echo Saint::genField("saint_edit_page_description","textarea","Description: ",array('value'=> $page->getMetaDescription())); ?></li>
				<?php
					$options = array();
					foreach (Saint::getAllCategories() as $key=>$category)
						$options[$key] = $category;
				?>
				<li><?php echo Saint::genField("saint_edit_page_categories[]","select","Categories: ",
					array('options'=>$options,'selected'=>array(),'multiple'=>true)); ?></li>
			</ul>
		</form>
		<ul>
			<li><div id="saint_admin_page_add_submit" class="link">Add Page</div></li>
			<li><div class="link saint_menu_link_pages">Back to Page Options</div></li>
		</ul>
	</div>
	
	<div id="saint_admin_page_options" class="saint_admin_options hidden">
		<ul>
			<li id="saint_admin_po_add" class="link">Add New Page</li>
			<li id="saint_admin_po_edit" class="link">Edit This Page</li>
			<li id="saint_admin_po_delete" class="link">Delete This Page</li>
		</ul>
		<ul id="saint_admin_page_list">
			<?php foreach (Saint::getAllPages() as $ipage): ?>
			<li><a href="<?php echo SAINT_URL . "/" . $ipage->getName(); ?>" class="sublist"><?php echo $ipage->getTitle(); ?></a></li>
			<?php endforeach; ?>
		</ul>
	</div>

	<div id="saint_admin_user_options" class="saint_admin_options hidden">
		<ul>
			<li id="saint_admin_uo_add" class="link">Add New User</li>
		</ul>
		<ul id="saint_admin_user_list" class="sublist">
			<?php foreach (Saint::getAllUsers() as $iuser): ?>
			<li class="link" id="user-<?php echo $iuser->getId(); ?>"><?php echo $iuser->getUsername(); ?></li>
			<?php endforeach; ?>
		</ul>
	</div>
	
	<div id="saint_admin_category_options" class="saint_admin_options hidden">
		<form>
			<input type="hidden" value="0" name="saint-set-category-id" id="saint-set-category-id" />
			<input type="hidden" value="0" name="saint-delete-category" id="saint-delete-category" />
			<input type="text" value="" name="saint-add-category" id="saint-add-category" />
			<span id="saint-add-category-submit" class="link">Add</span>
			<span id="saint-add-category-cancel" class="link hidden">Cancel</span>
		</form>
		<ul id="saint_categories" class="sublist">
		<?php foreach (Saint::getAllCategories() as $iid=>$icat): ?>
			<li class="link category-edit" id="cat-<?php echo $iid; ?>"><?php echo $icat; ?><span class="delete close-button">&nbsp;</span></li>
		<?php endforeach; ?>
		</ul>
	</div>
	
	<div id="saint_admin_dynamic_options" class="saint_admin_options hidden">&nbsp;</div>
	
	<div id="saint_admin_event_log">
	<?php foreach(Saint::getActionLog() as $action): ?>
	<p><?php echo $action; ?></p>
	<?php endforeach; ?>
	</div>
</div>
<?php endif; ?>
<div id="saint_ajax_templates">
	<?php if (Saint::getCurrentUser()->hasPermissionTo("edit-page")): ?>
	<div id="saint-admin-page-options" class="hidden">
		<span id="saint-page-options-close" class="close-button">&nbsp;</span>
		<form>
			<input type="hidden" name="saint_edit_page_id" value="<?php echo $page->getId(); ?>" />
			<ul>
				<li><?php echo Saint::genField("saint_edit_page_title","text","Name: ",array('value'=> $page->getTitle())); ?></li>
				<li><?php echo Saint::genField("saint_edit_page_name","text","Identifier: ",array('value'=> $page->getName())); ?></li>
				<?php
					$options = array();
					foreach (Saint::getLayoutNames() as $layout)
						$options[$layout] = ucfirst($layout);
				?>
				<li><?php echo Saint::genField("saint_edit_page_layout","select","Layout: ",array('options'=>$options,'selected' => $page->getLayout())); ?></li>
				<li><?php echo Saint::genField("saint_edit_page_keywords","text","Keywords: ",array('value'=> implode(',',$page->getMetaKeywords()))); ?></li>
				<li><?php echo Saint::genField("saint_edit_page_description","textarea","Description: ",array('value'=> $page->getMetaDescription())); ?></li>
				<?php
					$options = array();
					foreach (Saint::getAllCategories() as $category)
						$options[$category] = $category;
				?>
				<li><?php echo Saint::genField("saint_edit_page_categories[]","select","Categories: ",
					array('options'=>$options,'selected'=>$page->getCategories(),'multiple'=>true)); ?></li>
			</ul>
			<span id="saint-page-options-save" class="link">Save</span>
		</form>
	</div>
	<?php endif; ?>
	
	<div id="saint-admin-add-block" class="saint-admin-block">
		<div id="saint-add-block-overlay" class="saint-admin-block-overlay">&nbsp;</div>
		<div id="saint-add-block-load" class="saint-add-block-load">&nbsp;</div>
	</div>
	
	<div id="saint-admin-file-manager" class="saint-admin-block">
		<div id="saint-file-manager-overlay" class="saint-admin-block-overlay">&nbsp;</div>
		<div id="saint-file-manager-load" class="saint-add-block-load">&nbsp;</div>
	</div>
	
	<?php if (Saint::getCurrentUser()->hasPermissionTo("admin-overlay")): ?>
	<div id="saint-admin-shop-manager" class="saint-admin-block">
		<div id="saint-shop-manager-overlay" class="saint-admin-block-overlay">&nbsp;</div>
		<div id="saint-shop-manager-load" class="saint-add-block-load">&nbsp;</div>
		<?php Saint::includeBlock("shop/admin/nav"); ?>
	</div>
	<?php endif; ?>
	
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
			<div class="label-value" contenteditable="true">&nbsp;</div>
		</div>
		<div class="source">
			<div class="toolbar">
				<button class="link save" title="Save">&nbsp;</button>
				<button class="link switch visual" title="View Rich Text">&nbsp;</button>
				<div class="link close-button" title="Close">&nbsp;</div>
			</div>
			<form>
				<input type="hidden" name="label-name" value="" />
				<textarea class="label-value" name="label-value"></textarea>
			</form>
		</div>
	</div>

	<div class="template wysiwyg-form hidden">
		<form>
			<input type="hidden" name="saint-wysiwyg-name" value="" />
			<textarea name="saint-wysiwyg-content" class="wysiwyg-editable"></textarea>
		</form>
		<span class="link" id="saint-save-wysiwyg">Save</span>
	</div>
</div>