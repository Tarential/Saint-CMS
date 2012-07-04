<?php foreach (Saint::getIndex() as $i): ?>
	<h4><a href="<?php echo SAINT_URL . $i[0]; ?>"><?php echo $i[1]; ?></a></h4>
	<?php if (sizeof($i[3])): ?>
	<div class="subindex">
	<?php foreach ($i[3] as $si): ?>
		<h4><a href="<?php echo SAINT_URL . $si[0]; ?>"><?php echo $si[1]; ?></a></h4>
	<?php endforeach; ?>
	</div>
	<?php endif; ?>
<?php endforeach; ?>