<?php if (Saint_Model_User::failedLoginAttempts() <= 10): ?>
<?php echo $page->getLabel("login-intro","Enter your username/password to log in:"); ?>
<form id="saint-login-form" method="post" action="<?php echo SAINT_URL; ?>/">
	<label for="username">Username:</label>
	<input name="username" id="slf-username" class="focus" autofocus="autofocus" type="text" value="" />
	<label for="password">Password:</label>
	<input name="password" id="slf-password" type="password" value="" />
	<input type="checkbox" name="rememberme" value="true" checked="checked" />
	<input type="submit" value="Enter" />
</form> 
<?php else: ?>
<p>Sorry, but the maximum number of login attempts has been exceeded. Please wait an hour to try again.</p>
<?php endif; ?>