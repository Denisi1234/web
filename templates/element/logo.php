<?php
/**
 * FastNetStays — brand lockup: logo mark + Carbon wordmark (single source of truth).
 * IBM Plex Sans 600 wordmark beside the globe/pin/plane mark, no gradients/scripts.
 * Usage: <?= $this->element('logo') ?> or ['tone' => 'dark', 'size' => 18]
 */
$tone = $tone ?? 'light';
$size = (int)($size ?? 20);
if ($size < 14 || $size > 40) $size = 20;
$ink = $tone === 'dark' ? '#f4f4f4' : '#161616';
// Mark matches the wordmark em box (~1.4x cap height) for a balanced lockup.
$markSize = $size;
?>
<a class="fns-logo" href="<?= $this->Url->build('/'); ?>" title="fastnetstays.com" aria-label="fastnetstays.com home"><img class="fns-logo-mark" src="<?= $this->Url->build('/assets/img/logo-icon.png'); ?>" alt="" width="<?= $markSize ?>" height="<?= $markSize ?>" style="--fns-logo-mark:<?= $markSize ?>px" decoding="async"><span class="fns-logo-word" style="color:<?= $ink ?>;font-size:<?= $size ?>px;">fastnetstays.com</span></a>
