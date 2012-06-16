<?php if (isset($page->error)) echo "<p class=\"error\">".$page->error."</p>"; ?>
<?php echo $page->getLabel("login-intro","Enter your username/password to log in:"); ?>
<form id="saint-login-form" method="post" action="<?php echo SAINT_URL; ?>/">
	<label for="username">Username:</label>
	<input name="username" id="slf-username" class="focus" type="text" value="" />
	<label for="password">Password:</label>
	<input name="password" id="slf-password" type="password" value="" />
	<input type="checkbox" name="rememberme" value="true" checked />
	<input type="submit" value="Enter" />
</form> 
