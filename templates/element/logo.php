<?php
/**
 * FastNetStays — Carbon wordmark (single source of truth).
 * IBM Plex Sans 600, blue square mark, no gradients/scripts.
 * Usage: <?= $this->element('logo') ?> or ['tone' => 'dark', 'size' => 18]
 */
$tone = $tone ?? 'light';
$size = (int)($size ?? 20);
if ($size < 14 || $size > 40) $size = 20;
$ink = $tone === 'dark' ? '#f4f4f4' : '#161616';
?>
<a class="fns-logo" href="<?= $this->Url->build('/'); ?>" title="fastnetstays.com" aria-label="fastnetstays.com home"><span class="fns-logo-word" style="color:<?= $ink ?>;font-size:<?= $size ?>px;">fastnetstays.com</span></a>
