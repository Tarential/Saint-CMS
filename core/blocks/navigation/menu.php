			<nav>
				<div id="menu-center">
					<ul class="toplevel">
						<?php $menu_items = Saint::rankPages(Saint::getPages(array('categories'=>'Main Menu','orderby'=>'weight','order'=>'ASC'))); ?>
						<?php foreach ($menu_items as $item): ?>
							<?php $parent = $item[0]; $children = $item[1]; ?>
							<li<?php if (sizeof($children)): ?> class="toplevel"<?php endif; ?>>
								<a href="<?php echo SAINT_URL."/".$parent->getName(); ?>"<?php if ($page->getName() == $parent->getName()): ?> class="current"<?php endif; ?>><?php echo $parent->getTitle(); ?></a>
								<?php if (sizeof($children)): ?>
									<ul>
									<?php foreach ($children as $child): ?>
										<li<?php if ($page->getName() == $child->getName()): ?> class="current"<?php endif; ?>>
											<a href="<?php echo SAINT_URL."/".$child->getName(); ?>"><?php echo $child->getTitle(); ?></a>
										</li>
									<?php endforeach; ?>
									</ul>
								<?php endif; ?>
							</li>
						<?php endforeach; ?>
					</ul>
				</div>
			</nav>