<?php $this->assign('title', 'Support | FastNetStays'); ?>
<?= $this->element('navbar') ?>
<style>.host-card{border:1px solid #e8eaed;border-radius:16px;box-shadow:0 6px 16px rgba(0,0,0,.05);background:#fff}</style>
<div class="container py-4" style="max-width:1180px">
  <h1 style="font-size:20px;font-weight:800">Support & Messages</h1><p style="font-size:12px;color:#5f6368">Port of support-tickets.php + email-compose.php — GET /tickets, PATCH /tickets/{id}/status, POST /tickets/{id}/reply, GET /messages/threads</p>

  <div class="row g-3">
    <div class="col-lg-6">
      <div class="host-card p-3">
        <b style="font-size:14px">Tickets (<?= count($tickets ?? []) ?>)</b>
        <input id="ticketSearchInput" placeholder="Search tickets..." class="form-control mt-2" style="border-radius:12px;height:44px" oninput="filterTickets(this.value)">
        <div id="ticketList" class="d-grid gap-2 mt-3" style="max-height:60vh;max-height:60dvh;overflow:auto">
          <?php if (empty($tickets)): ?><div style="font-size:13px;color:#5f6368" class="py-3 text-center">No tickets.</div>
          <?php else: foreach ($tickets as $t):
            $tid=$t['id'] ?? '';
            $st=strtolower((string)($t['status'] ?? 'open'));
          ?><div class="ticket-card" data-search="<?= h(strtolower(($t['subject'] ?? '') . ' ' . ($t['message'] ?? ''))) ?>" style="border:1px solid #e8eaed;border-radius:12px;padding:12px">
            <div class="d-flex justify-content-between align-items-center"><b style="font-size:13px">#<?= h($tid) ?> <?= h($t['subject'] ?? '—') ?></b><span style="background:#F0F3FF;color:#2563EB;border-radius:9999px;padding:3px 8px;font-size:11px"><?= h($st) ?></span></div>
            <div style="font-size:12px;color:#5f6368" class="mt-1"><?= h(substr((string)($t['message'] ?? $t['description'] ?? ''),0,120)) ?></div>
            <div class="d-flex gap-1 mt-2 flex-wrap">
              <?= $this->Form->create(null, ['url'=>['controller'=>'AdminOwner','action'=>'support']]) ?>
                <?= $this->Form->hidden('action',['value'=>'status']) ?><?= $this->Form->hidden('ticket_id',['value'=>$tid]) ?><select name="status" class="form-select form-select-sm" style="border-radius:9999px;width:auto;display:inline-block"><option value="open" <?= $st==='open'?'selected':'' ?>>Open</option><option value="closed" <?= $st==='closed'?'selected':'' ?>>Closed</option><option value="resolved" <?= $st==='resolved'?'selected':'' ?>>Resolved</option></select><button class="btn btn-sm" style="background:#2563EB;color:#fff;border-radius:9999px">Update</button>
              <?= $this->Form->end() ?>
            </div>
            <?= $this->Form->create(null, ['url'=>['controller'=>'AdminOwner','action'=>'support']]) ?>
              <?= $this->Form->hidden('action',['value'=>'reply']) ?><?= $this->Form->hidden('ticket_id',['value'=>$tid]) ?><div class="d-flex gap-1 mt-2"><input name="message" placeholder="Reply..." class="form-control form-control-sm" style="border-radius:9999px" required><button class="btn btn-sm" style="background:#15803d;color:#fff;border-radius:9999px">Send</button></div>
            <?= $this->Form->end() ?>
          </div><?php endforeach; endif; ?>
        </div>
      </div>
    </div>
    <div class="col-lg-6">
      <div class="host-card p-3">
        <b style="font-size:14px">Message Threads (<?= count($threads ?? []) ?>)</b>
        <div class="d-grid gap-2 mt-3" style="max-height:60vh;max-height:60dvh;overflow:auto">
          <?php if (empty($threads)): ?><div style="font-size:13px;color:#5f6368" class="py-3 text-center">No threads.</div>
          <?php else: foreach ($threads as $th): ?><div style="border:1px solid #e8eaed;border-radius:12px;padding:12px">
            <div style="font-weight:700;font-size:13px"><?= h($th['partner_name'] ?? $th['name'] ?? 'Thread') ?> <span style="font-size:11px;color:#5f6368">#<?= h($th['partner_id'] ?? $th['id'] ?? '') ?></span></div>
            <div style="font-size:12px;color:#5f6368"><?= h(substr((string)($th['last_message'] ?? $th['text'] ?? ''),0,100)) ?></div>
          </div><?php endforeach; endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>
<script>function filterTickets(v){const q=v.toLowerCase();document.querySelectorAll('.ticket-card').forEach(c=>{c.style.display=c.dataset.search.includes(q)?'':'none'})}</script>
<?= $this->element('footer', ['skin'=>'skin-light-footer']) ?>
