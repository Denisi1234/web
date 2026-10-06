<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= $this->fetch('title') ? h($this->fetch('title')) . ' · FastNet Portal' : 'FastNet Portal' ?></title>
  <meta name="robots" content="noindex, nofollow">
  <meta name="csrfToken" content="<?= $this->request->getAttribute('csrfToken'); ?>">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="preconnect" href="https://api.mapbox.com">
  <link rel="preconnect" href="https://unpkg.com" crossorigin>
  <link rel="dns-prefetch" href="https://tile.openstreetmap.org">
  <link rel="dns-prefetch" href="https://nominatim.openstreetmap.org">
  <link rel="preload" as="style" href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@400;500;600;700&display=swap">
  <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@400;500;600;700&display=swap" rel="stylesheet" media="print" onload="this.media='all'">
  <noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@400;500;600;700&display=swap"></noscript>
  <style>
    /* Portal PJAX content swap. The progress bar itself is styled by
       loading.css (.fn-progress) - #pBar and its green/blue gradient were
       removed when the portal moved onto the shared indicator. */
    #main-content{transition:opacity .18s ease}
    #main-content.pjax-swap{animation:pFade .15s ease}
    /* Professional shift state: content softly dims while the next page
       loads (kicks in only if the fetch outlives ~120ms, so instant
       cache swaps never flicker). Non-blocking — no overlay, no spinner. */
    #main-content.pjax-pending{opacity:.55;pointer-events:none}
    /* Sidebar press feedback: instant tactile nudge on every nav tap */
    .p-side a{transition:background-color .12s ease,transform .12s ease}
    .p-side a:active{transform:translateX(2px)}
    @keyframes pFade{from{opacity:.4}to{opacity:1}}
    /* Button micro-dots loader lives in fastnet-dots.css (single source). */
    .p-btn[disabled]{opacity:.8;cursor:wait}
    @media (prefers-reduced-motion:reduce){
      #main-content,#main-content.pjax-swap{animation:none;transition:none}.p-side a{transition:none}.p-side a:active{transform:none}
    }
  </style>
  <?= $this->Html->css(['/assets/css/bootstrap.min.css', '/assets/css/fontawesome.css', '/assets/css/portal.css']) ?>
  <?= $this->Html->css(['/assets/css/loading-01.css', '/assets/css/loading-02.css']) ?>
  <?= $this->Html->css('/assets/css/app-loader.css') ?>
  <?= $this->Html->css('/assets/css/fastnet-dots.css') ?>
  <?= $this->Html->css('/assets/css/shimmer.css') ?>
  <?= $this->element('api_direct') ?>
  <?= $this->fetch('meta') ?>
  <?= $this->fetch('css') ?>
</head>
<body class="portal">
<!-- Canonical progress element. loading.js adopts this node and adds the
     fn-progress class, so the portal and the public site share one indicator.
     The inline display:none that used to sit here would have hidden the
     adopted bar entirely - visibility is driven by opacity in loading.css. -->
<div id="fastnet-top-progress" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-label="Loading"></div>
<div id="fastnet-bg-loader" role="status" aria-live="polite" aria-atomic="true"></div>
<div id="fns-confirm-overlay" role="dialog" aria-modal="true" aria-labelledby="fns-confirm-title" style="display:none">
    <div class="fns-confirm-card">
        <div class="fns-confirm-icon" aria-hidden="true">⚠</div>
        <p class="fns-confirm-title" id="fns-confirm-title">Are you sure?</p>
        <p class="fns-confirm-msg"   id="fns-confirm-msg">This action cannot be undone.</p>
        <div class="fns-confirm-actions">
            <button class="fns-confirm-cancel" id="fns-confirm-cancel" type="button">Cancel</button>
            <button class="fns-confirm-ok"     id="fns-confirm-ok"     type="button">Confirm</button>
        </div>
    </div>
</div>
<div class="p-shell" id="pShell">
  <?= $this->element('portal_sidebar', $this->viewVars) ?>
  <div class="p-scrim" id="pScrim"></div>
  <div class="p-main">
    <?= $this->element('portal_topbar', $this->viewVars) ?>
    <div class="p-flash"><?= $this->Flash->render() ?></div>
    <?php if (!empty($backendDown)): ?>
    <div class="p-flash" style="margin-top:-4px">
      <div class="alert alert-warning d-flex align-items-center gap-2" role="alert" style="font-size:13px;margin:0 0 12px">
        <i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i>
        <span><strong>Backend admin API is failing right now.</strong> Lists may be stale and Approve / Reject cannot be saved until it recovers. Your clicks are reaching the server — the backend answers with an error.</span>
      </div>
    </div>
    <?php endif; ?>
    <main class="p-content" id="main-content"><?= $this->fetch('content') ?></main>
  </div>
</div>
<?= $this->Html->script(['/assets/js/popper.min.js', '/assets/js/bootstrap.min.js'], ['defer' => true]) ?>
<?= $this->Html->script('/assets/js/app-loader-core.js?v=' . filemtime(WWW_ROOT . 'assets/js/app-loader-core.js')) ?>
<?= $this->Html->script('/assets/js/app-loader-dialog.js?v=' . filemtime(WWW_ROOT . 'assets/js/app-loader-dialog.js')) ?>
<?= $this->Html->script('/assets/js/loading-core.js?v=' . filemtime(WWW_ROOT . 'assets/js/loading-core.js')) ?>
<?= $this->Html->script('/assets/js/loading-ui.js?v=' . filemtime(WWW_ROOT . 'assets/js/loading-ui.js')) ?>
<?= $this->Html->script('/assets/js/fastnet-api-core.js?v=' . filemtime(WWW_ROOT . 'assets/js/fastnet-api-core.js')) ?>
<?= $this->Html->script('/assets/js/fastnet-api-opt.js?v=' . filemtime(WWW_ROOT . 'assets/js/fastnet-api-opt.js')) ?>
<?= $this->Html->script('/assets/js/fastnet-api-submit.js?v=' . filemtime(WWW_ROOT . 'assets/js/fastnet-api-submit.js')) ?>
<?= $this->Html->script('/assets/js/portal-pjax-core.js?v=' . filemtime(WWW_ROOT . 'assets/js/portal-pjax-core.js')) ?>
<?= $this->Html->script('/assets/js/portal-pjax-nav.js?v=' . filemtime(WWW_ROOT . 'assets/js/portal-pjax-nav.js')) ?>
<?= $this->fetch('script') ?>
</body>
</html>
