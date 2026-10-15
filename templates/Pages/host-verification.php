<?php
$this->assign('title', 'Verify Your Identity');
$this->assign('portal_title', 'Verification');
$this->assign('page_actions', '<span style="font-size:13px;color:var(--p-text-2)">Required before your first property goes live</span>');
$status = strtolower((string)($status ?? ''));
?>
<?php if (!empty($backendError)): ?>
  <div class="p-card mb-3" style="border-left:3px solid #f1c21b">
    <div style="font-size:14px;font-weight:600">Couldn't reach the server</div>
    <div style="font-size:13px;color:var(--p-text-2)">Your verification status couldn't be loaded. You can still submit documents below.</div>
  </div>
<?php endif; ?>
<?php if (empty($needsVerification) && $status !== ''): ?>
  <div class="p-card mb-3" style="border-left:3px solid #24a148">
    <div style="font-size:14px;font-weight:600">Status: <?= h(ucwords($status)) ?></div>
    <div style="font-size:13px;color:var(--p-text-2)">Our team reviews documents manually — usually within one business day. You can resubmit below if anything was requested.</div>
  </div>
<?php endif; ?>
<div class="p-card" style="max-width:860px">
  <div style="font-size:13px;color:var(--p-text-2);margin-bottom:16px;line-height:1.6">Required before your first property goes live. Our team reviews documents manually — usually within one business day.</div>
  <?= $this->element('Host/verification-form') ?>
</div>
<?= $this->Html->script('/assets/js/host-verification.js?v=' . filemtime(WWW_ROOT . 'assets/js/host-verification.js')) ?>
