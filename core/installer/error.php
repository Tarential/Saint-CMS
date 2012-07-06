 <div id="error" class="info-block">
 	<?php if(isset($error)): ?>
 		<p><?php echo $error; ?></p>
 	<?php else:?>
 		<p>There has been an error of unknown cause. In fact, you shouldn't be seeing this message unless someone forgot to set the error.</p>
 	<?php endif; ?>
 </div>