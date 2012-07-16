<li<?php if (sizeof($block->get("children"))): ?> class="toplevel"<?php endif; ?>>
	<?php if ($block->getId() != "0"): ?>
	<div class="block-item">
		<div class="sbid-<?php echo $block->getId(); ?> edit-button hidden">Edit <span class="block-name"><?php echo $block->get("title"); ?></span></div>
	<?php endif; ?>
		<a href="<?php echo $block->get("url"); ?>"<?php if ($page->getName() == $block->get("name")): ?> class="current"<?php endif; ?>><?php echo $block->get("title"); ?></a>
	<?php if ($block->getId() != "0"): ?>
	</div>
	<?php endif; ?>
	<?php if (sizeof($block->get("children"))): ?>
		<ul>
		<?php foreach ($block->get("children") as $child): ?>
			<li<?php if ($page->getName() == $child->get("name")): ?> class="current"<?php endif; ?>>
				<?php if ($child->getId() != "0"): ?>
				<div class="block-item">
					<div class="sbid-<?php echo $child->getId(); ?> edit-button hidden">Edit <span class="block-name"><?php echo $child->get("title"); ?></span></div>
				<?php endif; ?>
				<a href="<?php echo $child->get("url"); ?>"><?php echo $child->get("title"); ?></a>
				<?php if ($child->getId() != "0"): ?>
				</div>
				<?php endif; ?>
			</li>
		<?php endforeach; ?>
		</ul>
	<?php endif; ?>
</li>