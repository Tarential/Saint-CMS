<?php $options = Saint::getCurrentPage()->getEditBlock()->getInputSettings(); ?>
<?php echo Saint::genField($options['name'],$options['type'],$options['label'],$options['data']); ?>
<?php if (sizeof($options['details'])): ?>
	<span class="details">
	<?php foreach ($options['details'] as $detail): ?>
		<p><?php echo $detail; ?></p>
	<?php endforeach; ?>
	</span>
<?php endif; ?>
