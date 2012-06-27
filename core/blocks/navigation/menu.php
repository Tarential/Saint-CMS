			<nav>
				<div id="menu-center">
					<ul class="toplevel">
						<?php $menu_items = Saint::getPages(array('categories'=>'main-menu')); ?>
						<?php foreach ($menu_items as $item): ?>
							<li><a href="/<?php echo $item->getName(); ?>"><?php echo $item->getTitle(); ?></a></li>
						<?php endforeach; ?>
						<?php /* ?>
						<li><a href="<?php echo SAINT_URL; ?>/"<?php if ($page->getName() == "home"): ?> class="current"<?php endif; ?>><?php echo Saint::getLabel("menu/home","Home"); ?></a></li>
						<li><a href="<?php echo SAINT_URL; ?>/blog"<?php if ($page->getName() == "blog"): ?> class="current"<?php endif; ?>><?php echo Saint::getLabel("menu/blog","Blog"); ?></a></li>
						<li class="toplevel"><a href="<?php echo SAINT_URL; ?>/shop"<?php if ($page->getName() == "shop"): ?> class="current"<?php endif; ?>><?php echo Saint::getLabel("menu/shop","Shop"); ?></a>
							<ul>
								<li><a href="<?php echo SAINT_URL; ?>/shop/view.cart"<?php if ($page->getName() == "cart"): ?> class="current"<?php endif; ?>><?php echo Saint::getLabel("menu/cart","Shopping&nbsp;Cart"); ?></a></li>
								<?php if (sizeof(Saint::getShoppingCart()->getItems())): ?>
								<li><a href="<?php echo SAINT_URL; ?>/shop/view.cart/buynow.1/"<?php if ($page->getName() == "cart"): ?> class="current"<?php endif; ?>><?php echo Saint::getLabel("menu/checkout","Checkout"); ?></a></li>
								<?php endif; ?>							
							</ul>
						</li>
						<li class="toplevel"><a href="<?php echo SAINT_URL; ?>/gallery"<?php if ($page->getName() == "gallery"): ?> class="current"<?php endif; ?>><?php echo Saint::getLabel("menu/gallery","Gallery"); ?></a>
							<ul>
								<li><a href="<?php echo SAINT_URL; ?>/slideshow"<?php if ($page->getName() == "slideshow"): ?> class="current"<?php endif; ?>><?php echo Saint::getLabel("menu/slideshow","Slideshow"); ?></a></li>
							</ul>
						</li>
						
						<li><a href="<?php echo SAINT_URL; ?>/contact"<?php if ($page->getName() == "contact"): ?> class="current"<?php endif; ?>><?php echo Saint::getLabel("menu/contact","Contact"); ?></a></li>
						<?php */ ?>
					</ul>
				</div>
			</nav>