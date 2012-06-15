<form method="post" name="saint-contact-form" id="saint-contact-form">
	<ul>
		<li><?php echo Saint::genField("saint-contact-name","text","Your Name: "); ?></li>
		<li><?php echo Saint::genField("saint-contact-email","text","Your E-Mail: "); ?></li>
		<li><?php echo Saint::genField("saint-contact-message","textarea","Your Message: "); ?></li>
	</ul>
	<div class="link submit"><?php echo Saint::getPageLabel("submit","Submit Message"); ?></div>
</form>
<script type="text/javascript">
$(document).on({
	'click': function(event) {
		$('#saint-contact-form').submit();
	}
},'#saint-contact-form .link.submit');
</script>
<?php
/* To be enabled once problem with jquery-validate and Saint label system is resolved
<script type="text/javascript">
$(document).ready(function() {
	var submitEnabled = true;
	var rules = {
		rules: {
			"saint-contact-name": {
				required: true,
				minlength: 1
			},
			"saint-contact-email": {
				required: true,
				email: true
			},
			"saint-contact-message": {
				required: true,
				minlength: 1
			}
		},
		messages: {
			"saint-contact-name": {
				required: "Please enter your name.",
				minlength: "Please enter your name."
			},
			"saint-contact-email": {
				required: "Please enter your e-mail address.",
				email: "Please enter a valid e-mail address."
			},
			"saint-contact-message": {
				required: "We'd really like to hear from you. Please send us a message.",
				minlength: "We'd really like to hear from you. Please send us a message."
			}
		},
		submitHandler: function(form) {
			submitEnabled = false;
			form.submit();
		}
	};
	$('#saint-contact-form').validate(rules);
	
	$(document).on({
		'click': function(event) {
			if (submitEnabled)
				$('#saint-contact-form').submit();
		}
	},'#saint-contact-submit');
});
</script>
*/ ?>