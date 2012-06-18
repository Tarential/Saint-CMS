<?php if (Saint::getCurrentUser()->hasPermissionTo("admin-overlay")): ?>
<div class="saint-admin-code">
	<script type="text/javascript">
		var SAINT_URL = "<?php echo SAINT_URL; ?>";
	</script>
	<div class="blackout">&nbsp;</div>
	<div class="saint-admin-overlay">
		<div class="saint-ajax-indicator">&nbsp;</div>
		<div class="saint-logo">&nbsp;</div>
		
		<ul class="saint-admin-menu">
			<li class="link settings">Settings</li>
			<li class="link pages">Pages</li>
			<li class="link users">Users</li>
			<li class="link shop">Shop</li>
			<li class="link files">Files</li>
			<li class="link logout">Logout</li>
		</ul>
		
		<div class="saint-admin-options page-add hidden">
			<form>
				<ul>
					<li><?php echo Saint::genField("saint-add-page-title","text","Name: "); ?></li>
					<li><?php echo Saint::genField("saint-add-page-name","text","Identifier: "); ?></li>
					<?php
						$options = array();
						foreach (Saint::getLayoutNames() as $layout)
							$options[$layout] = ucfirst($layout);
					?>
					<li><?php echo Saint::genField("saint-add-page-layout","select","Layout: ",array('options'=>$options)); ?></li>
					<li><?php echo Saint::genField("saint-add-page-keywords","text","Keywords: "); ?></li>
					<li><?php echo Saint::genField("saint-add-page-description","textarea","Description: "); ?></li>
					<?php
						$options = array();
						foreach (Saint::getAllCategories() as $category)
							$options[$category] = $category;
					?>
					<li><?php echo Saint::genField("saint-add-page-categories[]","select","Categories: ",
						array('options'=>$options,'selected'=>array(),'multiple'=>true)); ?></li>
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
				<?php foreach (Saint::getAllPages() as $ipage): ?>
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
				<?php foreach (Saint::getAllUsers() as $iuser): ?>
				<li class="link" id="user-<?php echo $iuser->getId(); ?>"><?php echo $iuser->getUsername(); ?></li>
				<?php endforeach; ?>
			</ul>
		</div>
		
		<div class="saint-admin-options site-options hidden">
			<h4>Settings</h4>
			<form class="settings">
				<?php echo Saint::genField("saint-site-title","text","Title:",array("static"=>true,"value"=>Saint::getSiteTitle())); ?>
				<?php echo Saint::genField("saint-site-keywords","text","Keywords:",array("static"=>true,"value"=>Saint::getSiteKeywords())); ?>
				<?php echo Saint::genField("saint-site-description","textarea","Description:",array("static"=>true,"value"=>Saint::getSiteDescription())); ?>
				<?php $tp = new Saint_Model_Page(); if ($tp->loadById(Saint::getShopPageId())) $shopuri = $tp->getName(); else $shopuri = ""; ?>
				<?php echo Saint::genField("saint-shop-uri","text","Shop URI: (leave blank to disable)",array("static"=>true,"value"=>$shopuri)); ?>
				<?php if ($tp->loadById(Saint::getBlogPageId())) $bloguri = $tp->getName(); else $bloguri = ""; ?>
				<?php echo Saint::genField("saint-blog-uri","text","Blog URI: (leave blank to disable)",array("static"=>true,"value"=>$bloguri)); ?>
				<span class="submit link">Save Settings</span>
			</form>
			
			<h4>Categories</h4>
			<ul class="sublist category-list">
			<?php foreach (Saint::getAllCategories() as $iid=>$icat): ?>
				<li class="link category-edit cat-<?php echo $iid; ?>"><?php echo $icat; ?><span class="delete close-button">&nbsp;</span></li>
			<?php endforeach; ?>
			</ul>
			<form class="categories">
				<input type="hidden" value="0" name="saint-set-category-id" id="saint-set-category-id" />
				<input type="hidden" value="0" name="saint-delete-category" id="saint-delete-category" />
				<?php echo Saint::genField("saint-add-category","text","Name:",array('static'=>true))?>
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
					<li><?php echo Saint::genField("saint-edit-page-name","text","Identifier (URI): ",array('value'=> $page->getName(),'static'=>true)); ?></li>
					<?php
						if (!preg_match('/^.*\/.*$/',$page->getLayout())) {
						$options = array();
						foreach (Saint::getLayoutNames() as $layout)
							$options[$layout] = ucfirst($layout);
					?>
					<li><?php echo Saint::genField("saint-edit-page-layout","select","Layout: ",array('options'=>$options,'selected' => $page->getLayout(),'static'=>true)); ?></li>
					<?php } ?>
					<li><?php echo Saint::genField("saint-edit-page-keywords","text","Keywords: ",array('value'=> implode(',',$page->getMetaKeywords()),'static'=>true)); ?></li>
					<li><?php echo Saint::genField("saint-edit-page-description","textarea","Description: ",array('value'=> $page->getMetaDescription(),'static'=>true)); ?></li>
					<?php
						$options = array();
						foreach (Saint::getAllCategories() as $category)
							$options[$category] = $category;
					?>
					<li><?php echo Saint::genField("saint-edit-page-categories[]","select","Categories: ",
						array('options'=>$options,'selected'=>$page->getCategories(),'multiple'=>true,'static'=>true)); ?></li>
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
			<span class="link save">Save</span>
		</div>
	</div>
</div>