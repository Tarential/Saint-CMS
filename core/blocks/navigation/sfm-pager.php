<?php if ($page->sfmcurpage > 0): ?>
<span id="sfm-page-<?php echo $page->sfmcurpage-1?>" class="link previous">Previous</span>
<?php endif; ?>
<?php if ($page->sfmcurpage < $page->sfmnumpages-1): ?>
<span id="sfm-page-<?php echo $page->sfmcurpage+1?>" class="link next">Next</span>
<?php endif; ?>

<div class="sfm-page-numbers">
<?php for ($i = 0; $i < $page->sfmnumpages; $i++): ?>
<span id="sfm-page-<?php echo $i; ?>" class="link<?php if ($i == $page->sfmcurpage): ?> current<?php endif; ?>"><?php echo $i+1; ?></span>
<?php if ($page->sfmnumpages-1 > $i): ?> | <?php endif; ?>
<?php endfor; ?>
</div>