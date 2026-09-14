<?php $this->assign('title','My Properties | FastNetStays'); ?>
<?= $this->element('navbar') ?>
<style>.host-card{border:1px solid #e8eaed;border-radius:16px;overflow:hidden;box-shadow:0 4px 12px rgba(0,0,0,.04)}</style>
<div class="container py-4" style="max-width:1180px">
  <div class="d-flex justify-content-between align-items-center mb-3"><h1 style="font-size:20px;font-weight:800">My Properties</h1><a href="<?= $this->Url->build('/host/listings/add') ?>" class="btn" style="background:#2563EB;color:#fff;border-radius:30px;padding:8px 16px;font-weight:700">Add Property</a></div>
  <?php if(empty($properties)): ?><div class="host-card p-5 text-center" style="color:#5f6368">No listings yet — create your first property. Mirrors mobile manage_listings.dart empty state.</div>
  <?php else: ?><div class="row g-3"><?php foreach($properties as $p): $img=$p['image_url']??$p['primary_image_url']??'https://images.unsplash.com/photo-1631049307264-da0ec9d70304?w=600&h=400&fit=crop'; ?><div class="col-md-6 col-lg-4"><div class="host-card"><img src="<?= h($img) ?>" style="height:180px;width:100%;object-fit:cover"><div class="p-3"><div style="font-weight:700;color:#1a1d25"><?= h($p['name']??'Property') ?></div><div style="font-size:12px;color:#5f6368"><?= h($p['city']??'') ?></div><div style="font-weight:800;color:#C2410C" class="mt-1">TSh <?= number_format((float)($p['price_per_night']??0)) ?>/night</div></div></div></div><?php endforeach; ?></div><?php endif; ?>
</div>
<?= $this->element('footer',['skin'=>'skin-light-footer']) ?>
