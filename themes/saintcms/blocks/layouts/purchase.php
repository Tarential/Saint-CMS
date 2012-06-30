<?php
$cart = Saint::getCurrentUser()->getShoppingCart();
$cart->clearItems();
$cart->addItem(2,1);
$cart->save();
$page->setArg('pid',2);
$page->setTempLayout('shop/index');
$page->render();
