<div id="get-details" class="info-block">
	<h3>You're almost done!</h3>
	<p>Enter your chosen administrative username/password below.</p>
	<?php if (isset($error)): ?>
		<p><?php echo $error; ?></p>
	<?php endif; ?>
	<form method="post">
		<div>
			<label for="admin_username">Username:</label>
			<input type="text" name="admin_username" id="admin_username" value="<?php if(isset($_POST['admin_username'])) echo $_POST['admin_username']; ?>" />
		</div>
		<div>
			<label for="admin_password">Password:</label>
			<input type="password" name="admin_password" id="admin_password" value="<?php if(isset($_POST['admin_password'])) echo $_POST['admin_password']; ?>" />
		</div>
		<div>
			<label for="admin_password_confirm">Confirm Password:</label>
			<input type="password" name="admin_password_confirm" id="admin_password_confirm" value="<?php if(isset($_POST['admin_password_confirm'])) echo $_POST['admin_password_confirm']; ?>" />
		</div>
		<div>
			<label for="admin_email">E-Mail Address</label>
			<input type="text" name="admin_email" id="admin_email" value="<?php if(isset($_POST['admin_email'])) echo $_POST['admin_email']; ?>" />
		</div>
		<div>
			<input class="submit" type="submit" value="Submit" />
		</div>
	</form>
</div>
