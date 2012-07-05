<?php
$args = $page->getArgs();
$files = $block->getFiles();
if (isset($arguments['width']))
	$width = $arguments['width'];
else
	$width = null;
if (isset($arguments['height']))
	$height = $arguments['height'];
else
	$height = null;
if (isset($arguments['autoplay']))
	$autoplay = $arguments['autoplay'];
else
	$autoplay = '5000';
?>
<?php if (sizeof($files)): ?>
<div id="slides">
  <div class="slides_container" style="display:block;<?php 
  if ($width != null) echo "width:".$width."px;";
  if ($height != null) echo "height:".$height."px;";?>">
  	<?php foreach ($files as $curfile): ?>
  	<?php
		$img = new Saint_Model_Image();
		$img->loadByLocation($curfile['location']);
		$resizeargs = array(
			'max-height' => $height,
			'max-width' => $width,
		);
		$resizedurl = $img->getResizedUrl($resizeargs);
		?>
    <div>
    	<img src="<?php echo $resizedurl; ?>" alt="<?php echo $curfile['title']; ?>" />
      <span><?php echo $curfile['description']; ?></span>
    </div>
    <?php endforeach; ?>
  </div>
</div>

<script type="text/javascript">
  $(function(){
    $("#slides").slides({
			play: <?php echo $autoplay; ?>,
  	});
  });
</script>
<?php elseif (isset($arguments['label'])): ?>
<?php echo $arguments['label']; ?>
<?php else: ?>
	<p>Sorry, no files matched your selected criteria.</p>
<?php endif; ?>