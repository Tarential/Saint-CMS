<div class="saint-search-form">
	<?php if (isset($_POST['saint-search-phrase'])) {
		$value = Saint::sanitize($_POST['saint-search-phrase']);
	 	$class = '';
	} else {
		$value = 'Search...';
		$class = 'reset';
	} ?>
	<form class="saint-search" method="post" action="<?php echo SAINT_URL; ?>/search">
		<?php echo Saint::genField("saint-search-phrase","text","Search",array("value"=>$value,'classes'=>$class)); ?>
		<input name="saint-search-submit" class="submit" type="submit" value="Go" />
	</form>
</div>
<script type="text/javascript">
$(document).on({
	'click': function(event) {
		if ($(event.currentTarget).hasClass("reset")) {
			$(event.currentTarget).val("").removeClass("reset");
		}
		return true;
	},
	'focusout': function(event) {
		if ($(event.currentTarget).val() == "") {
			$(event.currentTarget).val($(event.currentTarget)[0].defaultValue).addClass("reset");
		}
	}
},'#saint-search-phrase');
</script>