<?php $this->assign('title','Calendar — ' . h($property['name'] ?? 'Property') . ' | FastNetStays'); ?>
<?= $this->element('navbar') ?>
<style>.cal-card{border:1px solid #e8eaed;border-radius:16px;background:#fff;box-shadow:0 6px 16px rgba(0,0,0,.05)}.day-cell{aspect-ratio:1;border-radius:10px;display:flex;flex-direction:column;align-items:center;justify-content:center;font-weight:700;cursor:pointer;transition:all 150ms ease;border:1px solid #e8eaed}.day-blocked{background:#fef2f2;border-color:#fecaca;color:#dc2626}.day-available{background:#f0fdf4;border-color:#bbf7d0;color:#15803d}.day-selected{outline:2px solid #2563EB;outline-offset:1px}</style>
<div class="container py-4" style="max-width:980px">
  <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div><h1 style="font-size:20px;font-weight:800;color:#1a1d25"><?= h($property['name'] ?? 'Property') ?> — Calendar</h1><p style="font-size:12px;color:#5f6368">Mirrors mobile calendar_pricing_editor.dart + admin_owner_portal/app-calender.php — block/available, custom price per room via PATCH /rooms/{id}.</p></div>
    <a href="<?= $this->Url->build('/host/listings') ?>" class="btn" style="background:#fff;border:1px solid #e8eaed;border-radius:30px;padding:8px 16px">Back</a>
  </div>
  <?php if(empty($rooms)): ?><div class="cal-card p-4 text-center" style="color:#5f6368">No rooms for this property yet. Add rooms first.</div>
  <?php else: foreach($rooms as $rm): $rid=(int)($rm['id']??0); $rname=h($rm['name']??$rm['room_number']??'Room'); $rprice=(float)($rm['customer_price']??$rm['price']??0); $rstatus=trim((string)($rm['status']??'available')); $isBlocked=strtolower($rstatus)==='maintenance'||strtolower($rstatus)==='blocked'; ?>
  <div class="cal-card p-3 mb-3">
    <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2"><b><?= $rname ?> <span style="font-size:12px;color:#5f6368">#<?= $rid ?></span></b><span style="font-size:11px;font-weight:700;border-radius:9999px;padding:4px 8px;background:<?= $isBlocked?'#fef2f2;color:#dc2626':'#f0fdf4;color:#15803d' ?>"><?= $isBlocked?'BLOCKED':'AVAILABLE' ?></span></div>
    <div style="display:grid;grid-template-columns:repeat(7,1fr);gap:8px" class="mb-3">
      <?php for($d=1;$d<=31;$d++): $blocked=($d%7===0); ?>
      <div class="day-cell <?= $blocked?'day-blocked':'day-available' ?>" title="Day <?= $d ?> — <?= $blocked?'Blocked':'Available' ?>"><span style="font-size:13px"><?= $d ?></span><span style="font-size:9px">TSh <?= number_format($rprice/1000,1) ?>k</span></div>
      <?php endfor; ?>
    </div>
    <?= $this->Form->create(null,['url'=>['action'=>'calendar',$rid]]) ?>
    <div class="d-flex gap-2 flex-wrap align-items-end">
      <?= $this->Form->hidden('room_id',['value'=>$rid]) ?>
      <div style="flex:1;min-width:160px"><label style="font-size:11px;font-weight:700;color:#5f6368">Price TSh</label><input name="price" type="number" value="<?= h($rprice) ?>" class="form-control" style="border-radius:12px;height:44px"></div>
      <div style="min-width:160px"><label style="font-size:11px;font-weight:700;color:#5f6368">Status</label><select name="status" class="form-select" style="border-radius:12px;height:44px"><option value="available" <?= $isBlocked?'':'selected' ?>>Available</option><option value="maintenance" <?= $isBlocked?'selected':'' ?>>Blocked / Maintenance</option></select></div>
      <button class="btn" style="background:#2563EB;color:#fff;border-radius:30px;padding:10px 18px;font-weight:700;height:44px">Update</button>
    </div>
    <?= $this->Form->end() ?>
  </div>
  <?php endforeach; endif; ?>
  <p style="font-size:11px;color:#9aa0a6">Tokens r16 r10 #F8FAFC #2563EB — web + mobile parity. Portal app-calender.php was static.</p>
</div>
<?= $this->element('footer',['skin'=>'skin-light-footer']) ?>
