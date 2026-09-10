<?php
/**
 * fastnetstays.com — Google Hotels Results Header
 * "near Dar es Salaam • 118 results" style subheader.
 */
$count    = $totalCount ?? count($properties ?? []);
$destName = !empty($queryParams['destination']) ? $queryParams['destination'] : 'Tanzania';
?>
<style>
.gh-m-price-toggle { display:none; gap:8px; margin-top:8px; overflow-x:auto; scrollbar-width:none; }
.gh-m-price-toggle::-webkit-scrollbar{display:none}
.gh-m-price-pill { display:inline-flex; align-items:center; gap:6px; padding:6px 12px; border-radius:16px; border:1px solid #dadce0; background:#fff; font-size:13px; font-weight:500; color:#3c4043; white-space:nowrap; font-family:'Google Sans',Roboto,sans-serif; }
.gh-m-price-pill.active { background:#e8f0fe; border-color:#aecbfa; color:#1967d2; }
@media(max-width:767px){ .gh-m-price-toggle{ display:flex; } .gh-results-count-row{ border-bottom:none !important; padding-bottom:0 !important; } .gh-near-part{ display:none; } }
</style>
<div class="gh-results-count-row d-flex align-items-center justify-content-between mb-1 flex-wrap gap-2" style="border-bottom:1px solid #e8eaed; padding-bottom:8px;">
    <div style="font-size:14px; color:#202124; font-family:'Google Sans',Roboto,sans-serif;">
        <span class="gh-near-part" style="color:#202124;">near <?= h($destName) ?> <span style="color:#5f6368; margin:0 4px;">·</span></span><span style="color:#202124;"><?= number_format($count) ?> results</span>
        <span style="color:#5f6368; margin-left:6px; display:inline-flex; vertical-align:middle;" title="About results"><i class="fa-regular fa-circle-question" style="font-size:12px;"></i></span>
        <?php if (!empty($queryParams['amenities'])): ?>
            <span class="badge ms-2" style="background:#e8f0fe; color:#1967d2; font-size:11px; font-weight:500; border-radius:12px; padding:2px 8px;">Filtered</span>
        <?php endif; ?>
    </div>
    <div class="d-flex align-items-center gap-2">
        <div class="dropdown d-none d-md-block">
            <button type="button" class="btn btn-sm bg-white border d-inline-flex align-items-center gap-1" data-bs-toggle="dropdown" aria-expanded="false" style="font-size:13px; font-weight:500; color:#3c4043; border-color:#dadce0 !important; border-radius:18px; padding:4px 10px; font-family:'Google Sans',Roboto,sans-serif;">
                <?php $sortLabel = ['recommended'=>'Recommended','price_asc'=>'Price: low to high','price_desc'=>'Price: high to low','rating'=>'Highest rating'][($queryParams['sort'] ?? 'recommended')] ?? 'Recommended'; echo h($sortLabel); ?>
                <i class="fa-solid fa-chevron-down" style="font-size:10px; color:#5f6368"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow-sm border rounded-3 p-1" style="min-width:200px;">
                <?php
                $sortOpts = ['recommended'=>'Recommended','price_asc'=>'Price: low to high','price_desc'=>'Price: high to low','rating'=>'Highest rating'];
                foreach($sortOpts as $k=>$lbl):
                  $p=$queryParams; $p['sort']=$k; $url='/?'.http_build_query($p);
                ?>
                <li><a class="dropdown-item rounded <?= ($queryParams['sort']??'recommended')===$k?'active':'' ?>" href="<?= h($url) ?>" style="font-size:13px;"><?= h($lbl) ?></a></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <button type="button" class="btn p-0 border-0 bg-transparent d-inline-flex align-items-center justify-content-center d-none d-md-inline-flex" style="width:28px;height:28px; color:#5f6368;" title="How payments affect ranking" aria-label="How payments affect ranking" data-bs-toggle="tooltip" data-bs-placement="top">
            <i class="fa-regular fa-circle-question" style="font-size:14px;"></i>
        </button>
    </div>
</div>
<div class="gh-m-price-toggle">
    <button type="button" class="gh-m-price-pill active" aria-pressed="true"><i class="fa-solid fa-xmark" style="font-size:11px;"></i> Nightly total price</button>
    <button type="button" class="gh-m-price-pill" aria-pressed="false">Stay total price</button>
</div>
