<?php function displayItem($item) { ?>
	<h4><a href="<?php echo SAINT_URL . $item[0]; ?>"><?php echo $item[1]; ?></a></h4>
	<?php if (sizeof($item[3])): ?>
		<div class="subindex">
		<?php foreach ($item[3] as $subitem) {
			 displayItem($subitem);
		} ?>
		</div>
	<?php endif; ?>
<?php } ?>
<?php foreach (Saint::getIndex() as $i) {
	displayItem($i);
} ?>