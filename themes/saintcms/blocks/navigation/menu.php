			<nav>
				<ul class="toplevel">
					<li><a href="/"<?php if ($page->getName() == "home"): ?> class="current"<?php endif; ?>><?php echo Saint::getLabel("menu/home","Home"); ?></a></li>
					<li class="toplevel"><a href="/buy/saint" class="buy-now<?php if ($page->getName() == "buy"): ?> current<?php endif; ?>"><?php echo Saint::getLabel("menu/shop","Purchase"); ?></a></li>
					<li><a href="/blog"<?php if ($page->getName() == "blog"): ?> class="current"<?php endif; ?>><?php echo Saint::getLabel("menu/blog","Blog"); ?></a></li>
					<li><a href="http://demo.saintcms.com/" target="_blank"><?php echo Saint::getLabel("menu/demo","Demo"); ?></a></li>
					<li><a href="/documentation"<?php if ($page->getName() == "documentation"): ?> class="current"<?php endif; ?>><?php echo Saint::getLabel("menu/documentation","Documentation"); ?></a></li>
					<li><a href="/contact"<?php if ($page->getName() == "contact"): ?> class="current"<?php endif; ?>><?php echo Saint::getLabel("menu/contact","Contact"); ?></a></li>
				</ul>
			</nav>