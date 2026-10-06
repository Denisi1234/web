<script>
(function () {
  // Single-source: FastnetLoading.bar. The floating capsule pill was removed.
  function load(on) {
    if (!window.FastnetLoading || !FastnetLoading.bar) return;
    if (on) FastnetLoading.bar.start();
    else FastnetLoading.bar.done();
  }
  load(true);
  function done() { load(false); }
  window.addEventListener('load', function () { setTimeout(done, 350); });
  setTimeout(done, 5000);
  window.addEventListener('pageshow', done);
  // room card Reserve → booking flow: bar while the system loads.
  // hold navigation briefly so it paints before unload.
  document.addEventListener('click', function (e) {
    if (e.defaultPrevented || (e.button !== undefined && e.button !== 0) || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;
    var a = e.target.closest ? e.target.closest('a[href*="/booking-page"]') : null;
    if (!a || (a.target && a.target !== '_self')) return;
    e.preventDefault();
    load(true);
    var href = a.href;
    // Wait one painted frame so the bar is visible before unload. This was
    // a flat 140ms hold on every booking click; a double rAF is ~16ms.
    requestAnimationFrame(function () {
      requestAnimationFrame(function () { window.location.href = href; });
    });
  });
})();
</script>

<!-- Property Details Modal — full about, policies, and rating in one dedicated place -->
<div class="modal fade" id="propertyDetailsModal" tabindex="-1" aria-labelledby="propertyDetailsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden" style="border:1px solid #dadce0;">
      <div class="modal-header px-4 py-3 bg-white" style="border-bottom:1px solid #e8eaed;">
        <h5 class="modal-title fw-bold" id="propertyDetailsModalLabel" style="color:#1a1d25;font-size:17px;"><?= h($propTitle ?? 'Stay details') ?></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body px-4 py-3" style="background:#fff;">
        <?php $modalDesc = trim((string)($propDesc ?? '')); ?>
        <?php if ($modalDesc !== ''): ?>
        <h6 class="fw-bold mb-2" style="color:#1a1d25;font-size:14px;">About this stay</h6>
        <div style="font-size:13.5px;color:#3c4043;line-height:1.65;"><?= nl2br(h($modalDesc)) ?></div>
        <?php endif; ?>
        <?php
          $modalPolicyLines = array_filter([
              !empty($property['check_in_time']) ? ['fa-clock', 'Check-in from ' . $property['check_in_time']] : null,
              !empty($property['check_out_time']) ? ['fa-right-from-bracket', 'Check-out until ' . $property['check_out_time']] : null,
              !empty($property['cancellation_policy']) ? ['fa-shield-check', (string)$property['cancellation_policy']] : null,
          ]);
        ?>
        <?php if (!empty($modalPolicyLines)): ?>
        <h6 class="fw-bold mt-4 mb-2" style="color:#1a1d25;font-size:14px;">Good to know</h6>
        <div style="display:flex;flex-direction:column;gap:8px;font-size:13.5px;color:#3c4043;">
          <?php foreach ($modalPolicyLines as [$modalIcon, $modalText]): ?>
          <div style="display:flex;align-items:flex-start;gap:10px;"><i class="fa-solid <?= $modalIcon ?>" style="font-size:13px;color:#0f62fe;margin-top:3px;"></i><span><?= h($modalText) ?></span></div>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
        <h6 class="fw-bold mt-4 mb-2" style="color:#1a1d25;font-size:14px;">Facilities &amp; Amenities</h6>
        <?= $this->element('Listing/Hotel/hotel-detail/amenities', ['fullList' => true]) ?>
        <h6 class="fw-bold mt-4 mb-2" style="color:#1a1d25;font-size:14px;">Rating</h6>
        <?php if (($reviewsCount ?? 0) > 0): ?>
        <div style="display:flex;align-items:center;gap:8px;font-size:13.5px;color:#1a1d25;font-weight:700;"><i class="fa-solid fa-star" style="color:#f59e0b;"></i> <?= h($score10Fmt ?? '') ?> <?= h($ratingLabel ?? '') ?> <span style="font-weight:500;color:#5f6368;">· <?= number_format((int)$reviewsCount) ?> verified reviews</span></div>
        <?php else: ?>
        <div style="font-size:13.5px;color:#5f6368;">No reviews yet — be the first verified guest to stay here.</div>
        <?php endif; ?>
      </div>
      <div class="modal-footer px-4 py-3" style="background:#f8f9fa;border-top:1px solid #e8eaed;">
        <button type="button" class="btn fw-bold px-4 py-2 rounded-pill" data-bs-dismiss="modal" style="background:#0f62fe;color:#fff;border:none;">Done</button>
      </div>
    </div>
  </div>
</div>

<!-- Lightbox & Map Modals -->
<div id="photo_lightbox_modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.9);z-index:4000;align-items:center;justify-content:center;padding:20px" onclick="closePhotoLightbox()">
  <div onclick="event.stopPropagation()" style="max-width:900px;width:100%;text-align:center">
    <img id="lightbox_main_img" src="<?= h($galleryImages[0]) ?>" style="max-width:100%;max-height:70vh;border-radius:8px">
    <div style="color:#fff;display:flex;justify-content:space-between;align-items:center;margin-top:12px"><span id="lightbox_counter">1 / <?= count($galleryImages) ?></span><div style="display:flex;gap:8px"><button onclick="prevLightboxPhoto()" style="background:rgba(255,255,255,0.2);color:#fff;border:none;border-radius:20px;padding:6px 14px">Previous</button><button onclick="nextLightboxPhoto()" style="background:rgba(255,255,255,0.2);color:#fff;border:none;border-radius:20px;padding:6px 14px">Next</button></div></div>
  </div>
</div>
<div id="hotel_map_modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.6);z-index:4000;align-items:center;justify-content:center;padding:12px" onclick="closeHotelMapModal()">
  <div onclick="event.stopPropagation()" style="background:#fff;border-radius:12px;max-width:860px;width:100%;padding:12px">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px"><b><?= h($propTitle) ?></b><button onclick="closeHotelMapModal()" style="border:none;background:#f1f3f4;border-radius:50%;width:44px;height:44px">✕</button></div>
    <div id="web1-hotel-detail-map" style="height:480px;background:#e8ecef;border-radius:12px;border:1px solid #e8eaed"></div>
  </div>
</div>

<script>
let currentLightboxIdx=0; const galleryImagesList=<?= json_encode($galleryImages) ?>; let detailMapInstance=null;
function openPhotoLightbox(i){currentLightboxIdx=i;updateLightboxImg();document.getElementById('photo_lightbox_modal').style.display='flex'}
function closePhotoLightbox(){document.getElementById('photo_lightbox_modal').style.display='none'}
function updateLightboxImg(){if(currentLightboxIdx<0)currentLightboxIdx=galleryImagesList.length-1;if(currentLightboxIdx>=galleryImagesList.length)currentLightboxIdx=0;const img=document.getElementById('lightbox_main_img');const c=document.getElementById('lightbox_counter');if(img)img.src=galleryImagesList[currentLightboxIdx];if(c)c.textContent=(currentLightboxIdx+1)+' / '+galleryImagesList.length}
function nextLightboxPhoto(){currentLightboxIdx++;updateLightboxImg()}
function prevLightboxPhoto(){currentLightboxIdx--;updateLightboxImg()}
// Professional viewer controls: Esc closes, arrows step, touch swipes.
document.addEventListener('keydown',function(e){
  var lb=document.getElementById('photo_lightbox_modal');
  if(!lb||lb.style.display!=='flex')return;
  if(e.key==='Escape')closePhotoLightbox();
  else if(e.key==='ArrowRight')nextLightboxPhoto();
  else if(e.key==='ArrowLeft')prevLightboxPhoto();
});
(function(){
  var lb=document.getElementById('photo_lightbox_modal');if(!lb)return;
  var sx=0;
  lb.addEventListener('touchstart',function(e){sx=e.touches[0].clientX},{passive:true});
  lb.addEventListener('touchend',function(e){
    var dx=e.changedTouches[0].clientX-sx;if(Math.abs(dx)<36)return;
    if(dx<0)nextLightboxPhoto();else prevLightboxPhoto();
  },{passive:true});
})();
// Mosaic map tile: real Mapbox static image once a genuine token is known.
// Otherwise the cell stays a clean neutral placeholder (pin + SEE MAP) —
// never a broken tile or a foreign street-map look.
(function(){
  var img=document.getElementById('gallery_map_img');if(!img)return;
  var lat=parseFloat(img.getAttribute('data-lat')),lng=parseFloat(img.getAttribute('data-lng'));
  if(!isFinite(lat)||!isFinite(lng))return;
  function tokenOk(t){return t&&t.indexOf('pk.')===0&&t.indexOf('your_real')===-1&&t.slice(-5)!=='.demo';}
  function paint(){
    var tok=window.MAPBOX_TOKEN||window.DEFAULT_MAPBOX_TOKEN||'';
    if(!tokenOk(tok))return;
    img.onerror=function(){img.style.display='none';};
    img.src='https://api.mapbox.com/styles/v1/mapbox/streets-v12/static/pin-l+e53935('+lng+','+lat+')/'+lng+','+lat+',13,0/600x360@2x?access_token='+encodeURIComponent(tok);
    img.style.display='block';
  }
  if(document.readyState==='complete')paint();
  else window.addEventListener('load',paint);
  setTimeout(paint,2500);
})();
function openHotelMapModal(){document.getElementById('hotel_map_modal').style.display='flex'; if(!detailMapInstance) initDetailMap(); else setTimeout(()=>detailMapInstance.resize(),200)}
function closeHotelMapModal(){document.getElementById('hotel_map_modal').style.display='none'}
document.querySelectorAll('.agoda-tab').forEach(btn=>{
  btn.addEventListener('click',()=>{
    document.querySelectorAll('.agoda-tab').forEach(b=>b.classList.remove('active'));
    btn.classList.add('active');
    const id=btn.dataset.tab;
    const map={overview:'overview-section',rooms:'rooms-section',trip:'rooms-section'};
    const target=document.getElementById(map[id]);
    if(target) target.scrollIntoView({behavior:'smooth',block:'start'});
  });
});
document.addEventListener('DOMContentLoaded',()=>{
  const inline=document.getElementById('hotel-detail-inline-map');
  if(inline && typeof mapboxgl!=='undefined'){
    setTimeout(()=>{
      try{
        const lat=<?= json_encode((float)($property['latitude'] ?? -6.7924)) ?>;
        const lng=<?= json_encode((float)($property['longitude'] ?? 39.2083)) ?>;
        mapboxgl.accessToken=window.MAPBOX_TOKEN||window.DEFAULT_MAPBOX_TOKEN||'';
        const m=new mapboxgl.Map({container:'hotel-detail-inline-map',style:'mapbox://styles/mapbox/streets-v12',center:[lng,lat],zoom:13});
        m.addControl(new mapboxgl.NavigationControl(),'top-right');
        new mapboxgl.Marker({color:'#e53935'}).setLngLat([lng,lat]).addTo(m);
      }catch(e){console.warn('inline map',e)}
    },600);
  }
});
function initDetailMap(){
  const lat=<?= json_encode((float)($property['latitude'] ?? -6.7924)) ?>;
  const lng=<?= json_encode((float)($property['longitude'] ?? 39.2083)) ?>;
  const container=document.getElementById('web1-hotel-detail-map');
  if(typeof mapboxgl==='undefined'||!container) return;
  mapboxgl.accessToken=window.MAPBOX_TOKEN||window.DEFAULT_MAPBOX_TOKEN||'';
  try{
    detailMapInstance=new mapboxgl.Map({container:'web1-hotel-detail-map',style:'mapbox://styles/mapbox/streets-v12',center:[lng,lat],zoom:14.5});
    detailMapInstance.addControl(new mapboxgl.NavigationControl(),'top-right');
    new mapboxgl.Marker({color:'#e53935'}).setLngLat([lng,lat]).addTo(detailMapInstance);
    setTimeout(()=>detailMapInstance.resize(),300);
  }catch(e){console.error(e)}
}

// Save to Recently Viewed in LocalStorage
(function() {
  try {
    const stayData = {
      id: <?= json_encode((int)$detailPropertyId) ?>,
      name: <?= json_encode((string)$propTitle) ?>,
      city: <?= json_encode((string)$propCity) ?>,
      area: <?= json_encode((string)$propArea) ?>,
      price: <?= json_encode((float)$propPrice) ?>,
      rating: <?= json_encode((float)$score10) ?>,
      ratingLabel: <?= json_encode((string)$ratingLabel) ?>,
      reviewsCount: <?= json_encode((int)$reviewsCount) ?>,
      image: <?= json_encode(!empty($galleryImages[0]) ? $galleryImages[0] : '') ?>,
      viewedAt: Date.now()
    };
    if (stayData.id > 0) {
      let recents = [];
      try { recents = JSON.parse(localStorage.getItem('fastnet_recently_viewed') || '[]'); } catch(e){}
      if (!Array.isArray(recents)) recents = [];
      recents = recents.filter(item => item && parseInt(item.id) !== stayData.id);
      recents.unshift(stayData);
      localStorage.setItem('fastnet_recently_viewed', JSON.stringify(recents.slice(0, 30)));
    }
  } catch(e) {}
})();
</script>
