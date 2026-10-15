<?php
$this->assign('title', 'Messages');
$this->assign('portal_title', 'Messages');
$this->assign('page_actions', '<span style="font-size:13px;color:var(--p-text-2)">Guest conversations</span>');
?>
<?php if (!empty($backendError) && empty($threads) && empty($active['partner_id'])): ?>
  <div class="p-card mb-3" style="border-left:3px solid #f1c21b">
    <div style="font-size:14px;font-weight:600">Couldn't reach the server</div>
    <div style="font-size:13px;color:var(--p-text-2)">Messages couldn't be loaded right now — this is a connection issue, not an empty inbox.</div>
    <div style="margin-top:12px"><a href="<?= $this->Url->build('/host/messages') ?>" class="p-btn">Retry</a></div>
  </div>
<?php endif; ?>
<?= $this->element('Messaging/chat', [
    'threads' => $threads ?? [],
    'active' => $active ?? [],
    'initialMessages' => $initialMessages ?? [],
    'currentUserId' => (int)($currentUserId ?? 0),
    'isHost' => true,
    'backUrl' => '/host/dashboard',
    'sendEndpoint' => '/host/messages/send',
    'historyEndpoint' => '/host/messages/history',
    'threadBase' => '/host/messages',
    'threadParam' => 'partner_id',
]) ?>
