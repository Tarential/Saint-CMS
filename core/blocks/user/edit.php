<?php
if (is_a($page->get("user-to-edit"),'Saint_Model_User') || is_subclass_of($page->get("user-to-edit"),'Saint_Model_User'))
	$user = $page->get("user-to-edit");
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
<div class="user-edit-form <?php if ($user->getId()): ?>edit-user<?php else: ?>add-user<?php endif; ?>">
	<form method="post" action="<?php echo SAINT_URL; ?>/register">
		<input type="hidden" name="saint-edit-user-id" value="<?php echo $user->getId(); ?>" />
		<input type="hidden" name="saint-edit-user-original-username" id="saint-edit-user-original-username" value="<?php echo $user->getUsername(); ?>" />
		<?php if (Saint::getCurrentUser()->getId() === 0): ?>
		<input type="hidden" name="saint_client_nonce" value="" />
		<?php endif; ?>
		<ul>
			<li><?php if ($user->getId() === 0 || Saint::getCurrentUser()->hasPermissionTo("edit-user")) {
				echo Saint::genField("saint-edit-user-username","text","Username: ",
				array(
					'value'=>$username,
					'static'=>true,
					'classes' => 'saint-validate saint-validate-username',
					'rules'=>'required',
				)); ?><div class="hud username"<?php if ($user->getId()): ?> style="display:none;"<?php endif; ?>>Characters, digits, dashes, dots and underscores only.</div><?php
				} else {
					echo $username;				
				} ?></li>
			<li><?php echo Saint::genField("saint-edit-user-firstname","text","First Name: ",
				array(
					'value'=>$user->getFirstName(),
					'static'=>true,
				)); ?></li>
			<li><?php echo Saint::genField("saint-edit-user-lastname","text","Last Name: ",
				array(
					'value'=>$user->getLastName(),
					'static'=>true,
				)); ?></li>
			<li><?php echo Saint::genField("saint-edit-user-email","text","E-Mail Address: ",
				array(
					'value'=>$user->getEmail(),
					'static'=>true,
					'rules'=>'required email',
				)); ?></li>
			<?php if (!$user->getId()): ?>
			<li><?php echo Saint::genField("saint-edit-user-newpassone","text","Password: ",
				array(
					'value'=>'',
					'password'=>true,
					'static'=>true,
					'rules'=>'required',
				)); ?></li>
			<li><?php echo Saint::genField("saint-edit-user-newpasstwo","text","Confirm Password: ",
				array(
					'value'=>'',
					'password'=>true,
					'static'=>true,
					'rules'=>'required',
				)); ?></li>
			<?php endif; ?>
			<?php if (sizeof($options) > 0): ?>
			<li><?php echo Saint::genField("saint-edit-user-groups[]","select","Groups: ",
				array(
					'options'=>$options,
					'selected'=>$user->getGroups(),
					'multiple'=>true,
					'static'=>true,
				)); ?></li>
			<?php endif; ?>
		</ul>
		<?php if ($user->getId()): ?>
		<div class="password_change saint-list-contracted">
			<p class="link trigger">Change Password</p>
			<ul>
				<li><?php echo Saint::genField("saint-edit-user-oldpass","text","Current Password: ",array('password'=>true,'static'=>true)); ?></li>
				<li><?php echo Saint::genField("saint-edit-user-newpassone","text","New Password: ",array('password'=>true,'static'=>true)); ?></li>
				<li><?php echo Saint::genField("saint-edit-user-newpasstwo","text","Confirm New: ",array('password'=>true,'static'=>true)); ?></li>
			</ul>
		</div>
		<?php endif;?>
		<?php if ($user->getId()): ?>
		<?php echo Saint::genField("saint-edit-user-delete","select","Delete User: ",
				array(
					'options'=>array(0=>'No',1=>'Yes'),
					'selected'=>'No',
					'multiple'=>false,
					'static'=>true,
				)); ?>
		<input type="hidden" name="saint-edit-user-ajax" value="true" />
		<?php endif; ?>
		<?php if (Saint::getCurrentUser()->hasPermissionTo("admin-overlay")): ?>
		<input type="hidden" name="saint-edit-user-ajax" value="1" />
		<div class="error_display error submit">&nbsp;</div>
		<div class="save_options">
			<span class="link submit">Save</span> &nbsp;&nbsp;&nbsp;
			<span class="link cancel">Cancel</span>
		</div>
		<?php else: ?>
		<input type="submit" value="Register" />
		<?php endif; ?>
	</form>
</div>