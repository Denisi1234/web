<?php
/**
 * Hotel overview description — shared by desktop and mobile (one component,
 * one behavior, like the mobile app's Overview section).
 *
 * 150-char preview + Read more / Read less toggle. Real text only: a blank
 * description renders nothing — never invented marketing copy.
 */
$descText = trim((string)($text ?? ''));
if ($descText !== ''):
    $descId = 'hotel_desc_' . substr(md5($descText), 0, 8);
    $descPreview = mb_substr($descText, 0, 150);
    $descTruncatable = mb_strlen($descText) > 150;
    ?>
<div style="font-size:13.5px;color:#3c4043;line-height:1.6;margin-top:12px;padding-top:12px;border-top:1px solid #f1f3f4">
  <span id="<?= h($descId) ?>_preview"><?= nl2br(h($descTruncatable ? $descPreview . '… ' : $descText)) ?></span><span id="<?= h($descId) ?>_full"<?php if ($descTruncatable): ?> style="display:none"<?php endif; ?>><?= nl2br(h($descTruncatable ? mb_substr($descText, 150) : '')) ?></span><?php if ($descTruncatable): ?> <a href="javascript:void(0)" id="<?= h($descId) ?>_toggle" style="color:#0f62fe;font-weight:700;font-size:13.5px;text-decoration:none" onclick="(function(){var f=document.getElementById('<?= h($descId) ?>_full'),p=document.getElementById('<?= h($descId) ?>_preview'),t=document.getElementById('<?= h($descId) ?>_toggle');var open=f.style.display!=='none';f.style.display=open?'none':'';p.style.display=open?'':'none';t.textContent=open?'Read more':'Read less';})();">Read more</a><?php endif; ?>
</div>
<?php endif; ?>
