<?php
$this->assign('title', 'Support');
$this->assign('portal_title', 'Support');
$this->assign('page_actions', '<span style="font-size:13px;color:var(--p-text-2)">' . count($tickets ?? []) . ' tickets · ' . count($threads ?? []) . ' threads</span>');
?>
<div class="row g-3">
  <div class="col-lg-6">
    <div class="p-card">
      <h3>Tickets (<?= count($tickets ?? []) ?>)</h3>
      <div class="sub">Reply or update status inline</div>
      <input id="ticketSearchInput" placeholder="Search tickets…" class="form-control mt-2" style="min-height:40px" oninput="filterTickets(this.value)">
      <div id="ticketList" style="display:grid;gap:8px;margin-top:12px;max-height:60vh;overflow:auto">
        <?php if (empty($tickets)): ?><div class="p-empty">No tickets.</div>
        <?php else: foreach ($tickets as $t):
          $tid = $t['id'] ?? '';
          $st = strtolower((string)($t['status'] ?? 'open'));
          $badge = $st === 'open' ? 'yellow' : ($st === 'resolved' ? 'green' : 'blue');
        ?>
        <div class="ticket-card" data-search="<?= h(strtolower(($t['issue'] ?? $t['subject'] ?? '') . ' ' . ($t['message'] ?? ''))) ?>" style="border:1px solid var(--p-border);padding:12px;background:#fff">
          <div style="display:flex;justify-content:space-between;align-items:center;gap:8px"><strong style="font-size:13px">#<?= h($tid) ?> <?= h($t['issue'] ?? $t['subject'] ?? '—') ?></strong><span class="p-badge <?= $badge ?>"><?= h($st) ?></span></div>
          <div style="font-size:12px;color:var(--p-text-2);margin-top:4px"><?= h(substr((string)($t['message'] ?? $t['description'] ?? ''), 0, 140)) ?></div>
          <div style="display:flex;gap:6px;margin-top:8px;flex-wrap:wrap">
            <?= $this->Form->create(null, ['url' => ['controller' => 'AdminOwner', 'action' => 'support'], 'style' => 'display:flex;gap:6px', 'data-api' => 'PATCH /tickets/' . $tid . '/status', 'data-api-strip' => 'action,ticket_id', 'data-api-ok' => 'Ticket updated.', 'data-opt' => 'patch', 'data-opt-scope' => 'closest:.ticket-card', 'data-opt-badge' => '.p-badge', 'data-opt-badgesrc' => 'status', 'data-opt-badgemap' => 'ticket', 'data-opt-badgetext' => 'lower', 'data-opt-bust' => '_tickets', 'data-api-go' => '/admin/cache-bust?scope=_tickets&go=' . urlencode('/admin/support')]) ?>
              <?= $this->Form->hidden('action', ['value' => 'status']) ?><?= $this->Form->hidden('ticket_id', ['value' => $tid]) ?>
              <select name="status" class="form-select form-select-sm" style="min-height:32px;width:auto">
                <?php foreach (['Open', 'In Progress', 'Resolved'] as $opt): ?><option value="<?= $opt ?>" <?= $st === strtolower($opt) ? 'selected' : '' ?>><?= $opt ?></option><?php endforeach; ?>
              </select>
              <button class="p-btn" style="min-height:32px;font-size:12px">Update</button>
            <?= $this->Form->end() ?>
          </div>
          <?= $this->Form->create(null, ['url' => ['controller' => 'AdminOwner', 'action' => 'support'], 'style' => 'display:flex;gap:6px;margin-top:6px', 'data-api' => 'POST /tickets/' . $tid . '/reply', 'data-api-strip' => 'action,ticket_id', 'data-api-ok' => 'Reply sent.', 'data-opt' => 'refresh', 'data-opt-bust' => '_tickets', 'data-api-go' => '/admin/cache-bust?scope=_tickets&go=' . urlencode('/admin/support')]) ?>
            <?= $this->Form->hidden('action', ['value' => 'reply']) ?><?= $this->Form->hidden('ticket_id', ['value' => $tid]) ?>
            <input name="message" placeholder="Reply…" class="form-control form-control-sm" style="min-height:32px" required><button class="p-btn" style="min-height:32px;font-size:12px">Send</button>
          <?= $this->Form->end() ?>
        </div>
        <?php endforeach; endif; ?>
      </div>
    </div>
  </div>
  <div class="col-lg-6">
    <div class="p-card">
      <h3>Message threads (<?= count($threads ?? []) ?>)</h3>
      <div class="sub">Latest conversations</div>
      <div style="display:grid;gap:8px;margin-top:12px;max-height:60vh;overflow:auto">
        <?php if (empty($threads)): ?><div class="p-empty">No threads.</div>
        <?php else: foreach ($threads as $th): ?>
        <div style="border:1px solid var(--p-border);padding:12px;background:#fff">
          <div style="font-weight:600;font-size:13px"><?= h($th['partner_name'] ?? $th['name'] ?? 'Thread') ?> <span style="font-size:11px;font-weight:400;color:var(--p-text-2)">#<?= h($th['partner_id'] ?? $th['id'] ?? '') ?></span></div>
          <div style="font-size:12px;color:var(--p-text-2)"><?= h(substr((string)($th['last_message'] ?? $th['text'] ?? ''), 0, 120)) ?></div>
        </div>
        <?php endforeach; endif; ?>
      </div>
    </div>
  </div>
</div>
<script>function filterTickets(v){const q=v.toLowerCase();document.querySelectorAll('.ticket-card').forEach(c=>{c.style.display=c.dataset.search.includes(q)?'':'none'})}</script>
