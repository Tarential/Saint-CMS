<?php if ($page->get("sfm-page-number") > 0): ?>
<span id="sfm-page-<?php echo $page->get("sfm-page-number")-1?>" class="link previous">Previous</span>
<?php endif; ?>
<?php if ($page->get("sfm-page-number") < $page->get("sfm-number-of-pages")-1): ?>
<span id="sfm-page-<?php echo $page->get("sfm-page-number")+1?>" class="link next">Next</span>
<?php endif; ?>

<div class="sfm-page-numbers">
<?php for ($i = 0; $i < $page->get("sfm-number-of-pages"); $i++): ?>
<span id="sfm-page-<?php echo $i; ?>" class="link<?php if ($i == $page->get("sfm-page-number")): ?> current<?php endif; ?>"><?php echo $i+1; ?></span>
<?php if ($page->get("sfm-number-of-pages")-1 > $i): ?> | <?php endif; ?>
<?php endfor; ?>
</div>