<?php
if (isset($page->usertoedit) && is_a($page->usertoedit,'Saint_Model_User'))
	$user = $page->usertoedit;
else
	$user = new Saint_Model_User();
$options = array(); 
if (Saint::getCurrentUser()->hasPermissionTo("make-moderator"))
	$options['moderator'] = "Moderator";
if (Saint::getCurrentUser()->hasPermissionTo("make-administrator"))
	$options['administrator'] = "Administrator";
if ($user->getUsername() == "guest")
	$username = "";
else
	$username = $user->getUsername();
?>
<form>
	<input type="hidden" name="saint-edit-user-id" value="<?php echo $user->getId(); ?>" />
	<ul>
		<li><?php echo Saint::genField("saint-edit-user-username","text","Username: ",array('value'=>$username)); ?></li>
		<li><?php echo Saint::genField("saint-edit-user-firstname","text","First Name: ",array('value'=>$user->getFirstName())); ?></li>
		<li><?php echo Saint::genField("saint-edit-user-lastname","text","Last Name: ",array('value'=>$user->getLastName())); ?></li>
		<li><?php echo Saint::genField("saint-edit-user-email","text","E-Mail Address: ",array('value'=>$user->getEmail())); ?></li>
		<?php if (!$user->getId()): ?>
		<li><?php echo Saint::genField("saint-edit-user-newpassone","text","Password: ",array('value'=>'','password'=>true)); ?></li>
		<li><?php echo Saint::genField("saint-edit-user-newpasstwo","text","Confirm Password: ",array('value'=>'','password'=>true)); ?></li>
		<?php endif; ?>
		<?php if (sizeof($options) > 0): ?>
		<li><?php echo Saint::genField("saint-edit-user-groups[]","select","Groups: ",
			array('options'=>$options,'selected'=>$user->getGroups(),'multiple'=>true)); ?></li>
		<?php endif; ?>
	</ul>
	<?php if ($user->getId()): ?>
	<div class="password_change saint-list-contracted">
		<p class="link trigger">Change Password</p>
		<ul>
			<li><?php echo Saint::genField("saint-edit-user-oldpass","text","Current Password: ",array('value'=>'','password'=>true)); ?></li>
			<li><?php echo Saint::genField("saint-edit-user-newpassone","text","New Password: ",array('value'=>'','password'=>true)); ?></li>
			<li><?php echo Saint::genField("saint-edit-user-newpasstwo","text","Confirm New: ",array('value'=>'','password'=>true)); ?></li>
		</ul>
	</div>
	<?php endif;?>
	<?php if (Saint::getCurrentUser()->hasPermissionTo("admin-overlay")): ?>
	<?php echo Saint::genField("saint-edit-user-delete","select","Delete User: ",
			array('options'=>array(0=>'No',1=>'Yes'),'selected'=>'No','multiple'=>false)); ?>
	<input type="hidden" name="saint-edit-user-ajax" value="true" />
	<div class="save_options">
		<span id="saint_edit_user_submit" class="link">Save</span> &nbsp;&nbsp;&nbsp;
		<span id="saint_edit_user_cancel" class="link">Cancel</span>
	</div>
	<?php else: ?>
	<input type="submit" value="Register" />
	<?php endif; ?>
</form>