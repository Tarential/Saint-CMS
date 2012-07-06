<script type="text/javascript">
$(function() {
	var uploader = new plupload.Uploader({
		runtimes : 'gears,html5,flash,silverlight,browserplus',
		browse_button : 'pickfiles',
		container : 'container',
		max_file_size : '1990mb',
		chunk_size : '1mb',
		url : '/upload',
		flash_swf_url : '/core/scripts/plupload/plupload.flash.swf',
		silverlight_xap_url : '/core/scripts/plupload/plupload.silverlight.xap',
		filters : [
			{title : "Image files", extensions : "jpg,gif,png"},
			{title : "Compressed files", extensions : "zip,rar,gz"},
			{title : "PDF files", extensions : "pdf"}
		]
	});

	uploader.bind('Init', function(up, params) {
		$('#filelist').html("<div>Current runtime: " + params.runtime + "</div>");
	});

	$('#uploadfiles').click(function(e) {
		uploader.start();
		e.preventDefault();
	});

	uploader.init();

	uploader.bind('FilesAdded', function(up, files) {
		$.each(files, function(i, file) {
			$('#filelist').append(
				'<div id="' + file.id + '">' +
				file.name + ' (' + plupload.formatSize(file.size) + ') <b></b>' +
			'</div>');
		});

		up.refresh(); // Reposition Flash/Silverlight
	});

	uploader.bind('UploadProgress', function(up, file) {
		$('#' + file.id + " b").html(file.percent + "%");
	});

	uploader.bind('Error', function(up, err) {
		$('#filelist').append("<div>Error: " + err.code +
			", Message: " + err.message +
			(err.file ? ", File: " + err.file.name : "") +
			"</div>"
		);

		up.refresh(); // Reposition Flash/Silverlight
	});

	uploader.bind('FileUploaded', function(up, file) {
		$('#' + file.id + " b").html("100%");
	});
});
</script>
<div id="saint-add-block-sidebar" class="saint-add-block-sidebar">
	<div id="saint-uploader">
		<h3 class="link">Upload Files</h3>
		<div id="filelist">No runtime found.</div>
		<br />
		<a id="pickfiles" href="#">[Select files]</a>
		<a id="uploadfiles" href="#">[Upload files]</a>
	</div>
	<div id="saint-file-info">
		<form>
			<?php echo Saint::genField("saint-file-mode","hidden","",array('value'=>'search')); ?>
			<?php echo Saint::genField("saint-file-id","hidden"); ?>
			<?php echo Saint::genField("saint-file-label","hidden"); ?>
			<?php echo Saint::genField("saint-file-title","text","Title:",array('static'=>true)); ?>
			<?php echo Saint::genField("saint-file-keywords","text","Keywords:",array('static'=>true)); ?>
			<?php echo Saint::genField("saint-file-description","textarea","Description:",array('static'=>true)); ?>
			<?php
				$options = array();
				foreach (Saint::getCategories() as $category)
					$options[$category] = $category;
			?>
			<?php echo Saint::genField("saint-file-categories[]","select","Categories: ",
				array('options'=>$options,'selected'=>array(),'multiple'=>true,'static'=>true)); ?>
			<div class="form-options">
				<span class="form-submit link">Search</span>
				<span class="form-cancel link">Reset</span>
			</div>
		</form>
	</div>
	<div id="sfm-close-button" class="close-button link">&nbsp;</div>
</div>
<div id="saint-file-manager-data" class="saint-add-block-data">
<div class="saint-admin-block-overlay"></div>
<div class="saint-loadable-content">
	<?php Saint::includeBlock("file-manager/list"); ?>
</div>
</div>
