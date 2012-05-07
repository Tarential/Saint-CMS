<?php $notices = Saint::getNotices(); ?>
<?php if (sizeof($notices)): ?>
<div class="saint-notices">
<?php foreach ($notices as $notice): ?>
<span><?php echo $notice; ?></span>
<?php endforeach; ?>
</div>
<?php Saint::clearNotices(); ?>
<?php endif; ?>