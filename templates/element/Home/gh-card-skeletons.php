<!-- Shimmer skeletons — any-device (320→2560), dvh-safe, reduced-motion -->
<div id="gh-shimmer-container" style="display:none;" aria-hidden="true" aria-live="polite" aria-busy="true">
    <div class="fns-shimmer-chips show" id="fns_shimmer_chips" aria-hidden="true">
        <?php for($i=0;$i<6;$i++): ?><div class="gh-shim-block fns-shimmer-chip" style="width:<?= 84+($i%3)*18 ?>px"></div><?php endfor; ?>
    </div>
    <?php for ($s=0;$s<4;$s++): ?>
    <div class="gh-shimmer" role="presentation">
        <div class="gh-shim-block" style="flex:0 0 185px;height:180px;" aria-hidden="true"></div>
        <div style="flex:1;padding:12px 16px;display:flex;flex-direction:column;gap:8px;min-width:0">
            <div style="display:flex;justify-content:space-between;gap:8px;align-items:center">
                <div class="gh-shim-block" style="height:16px;flex:0 0 55%;max-width:55%"></div>
                <div class="gh-shim-block" style="height:16px;width:88px;flex-shrink:0"></div>
            </div>
            <div class="gh-shim-block" style="height:12px;width:120px;max-width:60%"></div>
            <div style="display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:8px;">
                <?php for($i=0;$i<9;$i++): ?><div class="gh-shim-block" style="height:12px;"></div><?php endfor; ?>
            </div>
            <div style="display:flex;justify-content:flex-end;">
                <div class="gh-shim-block" style="height:36px;width:128px;border-radius:9999px;"></div>
            </div>
        </div>
    </div>
    <?php endfor; ?>
</div>
