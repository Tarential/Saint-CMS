				</div>
			<div style="clear:both;">&nbsp;</div>
			<div class="saint-user-status">
				<?php if (Saint::getCurrentUser()->getUsername() == "guest"): ?>
					You are currently a guest user. Click <a href="<?php echo SAINT_URL; ?>/login">here</a> to log in or <a href="<?php echo SAINT_URL; ?>/register">here</a> to register.
				<?php else: ?>
					You are logged in as <?php echo Saint::getCurrentUser()->getUsername(); ?>. <a href="<?php echo SAINT_URL; ?>/system/?action=logout">Log out</a>.
				<?php endif; ?>
			</div>
			<div id="saint-powered">This site is powered by <a href="http://www.saintcms.com/">Saint CMS <?php echo SAINT_FRIENDLY_VERSION; ?></a></div>
		</div>
	</body>
</html> 
