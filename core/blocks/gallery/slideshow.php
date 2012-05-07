<?php
$page = Saint::getCurrentPage();
if (isset($page->sfmarguments))
	$arguments = $page->sfmarguments;
else
	$arguments = array();
$args = $page->getArgs();
$files = Saint_Model_FileManager::getAllFiles($arguments);
if (isset($arguments['width']))
	$width = $arguments['width'];
else
	$width = '600';
if (isset($arguments['height']))
	$height = $arguments['height'];
else
	$height = '400';
if (isset($arguments['autoplay']))
	$autoplay = $arguments['autoplay'];
else
	$autoplay = '5000';
?>
<div id="slides">
  <div class="slides_container" style="display:block;width:<?php echo $width; ?>px;height:<?php echo $height; ?>px;">
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