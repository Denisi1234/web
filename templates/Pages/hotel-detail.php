<?php
/**
 * fastnetstays.com — Agoda Hotel Detail — Exact match to screenshot reference
 */
$propTitle = $property['name'] ?? 'Divi Village Golf and Beach Resort';
$propCity = $property['city'] ?? 'Oranjestad';
$propArea = $property['area'] ?? $propCity;
$propStars = !empty($property['star_rating']) ? max(1, min(5, (int)$property['star_rating'])) : 4;
$propRating = !empty($property['rating']) ? number_format((float)$property['rating'], 1) : '8.4';
$reviewsCount = !empty($property['reviews_count']) ? (int)$property['reviews_count'] : (count($reviews ?? []) >0 ? count($reviews) : 737);
$propPrice = (float)($property['starting_price'] ?? ($property['price_per_night'] ?? ($property['price'] ?? 275)));
$propPrice = $propPrice > 0 ? $propPrice : 275;
$firstRoomId = !empty($rooms[0]['id']) ? $rooms[0]['id'] : null;
$detailPropertyId = (int)($propertyId ?? 0);
$propAddress = trim((string)($property['address'] ?? 'Plot 123, Msasani Peninsula, Dar es Salaam, Tanzania'));
$propDesc = $property['description'] ?? '';
$propertyAmenities = $property['amenities'] ?? [];
if (is_string($propertyAmenities)) {
    $d = json_decode($propertyAmenities, true);
    $propertyAmenities = is_array($d) ? $d : array_filter(array_map('trim', explode(',', $propertyAmenities)));
}
$galleryImages = [];
if (!empty($property['image_url'])) $galleryImages[] = $property['image_url'];
if (!empty($property['primary_image_url'])) $galleryImages[] = $property['primary_image_url'];
if (!empty($property['cover_image'])) $galleryImages[] = $property['cover_image'];
if (!empty($property['images']) && is_array($property['images'])) {
    foreach ($property['images'] as $img) { $u = is_array($img) ? ($img['url'] ?? $img['image_url'] ?? '') : $img; if($u && !in_array($u,$galleryImages)) $galleryImages[]=$u; }
}
if (!empty($rooms) && is_array($rooms)) {
    foreach ($rooms as $r) {
        $ph = $r['photos'] ?? []; if(is_string($ph)) $ph=json_decode($ph,true);
        if(is_array($ph)) foreach($ph as $p){ $u=is_array($p)?($p['url']??$p['image_url']??''):$p; if($u && !in_array($u,$galleryImages)) $galleryImages[]=$u; }
    }
}
if (count($galleryImages) < 8) {
    $demo = [
        'https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?w=1200&h=800&fit=crop',
        'https://images.unsplash.com/photo-1520250497591-112f2f40a3f4?w=600&h=400&fit=crop',
        'https://images.unsplash.com/photo-1566073771259-6a8506099945?w=600&h=400&fit=crop',
        'https://images.unsplash.com/photo-1571896349842-33c89424de2d?w=600&h=400&fit=crop',
        'https://images.unsplash.com/photo-1582719508461-905c673771fd?w=600&h=400&fit=crop',
        'https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?w=600&h=400&fit=crop',
        'https://images.unsplash.com/photo-1551882547-b79c417633b0?w=600&h=400&fit=crop',
        'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?w=600&h=400&fit=crop',
    ];
    foreach($demo as $d) if(!in_array($d,$galleryImages)) $galleryImages[]=$d;
    $galleryImages = array_slice($galleryImages,0,8);
}
$qp = $queryParams ?? [];
$checkInVal = $qp['checkin'] ?? $qp['checkIn'] ?? '2026-10-12';
$checkOutVal = $qp['checkout'] ?? $qp['checkOut'] ?? '2026-10-18';

$this->assign('title', h($propTitle) . ' | fastnetstays.com');
$this->assign('description', h(mb_strimwidth(strip_tags($propDesc),0,155,'...')) . ' — Book on FastNet Stays');
?>
<style>
/* Agoda exact detail — matches screenshot */
.agoda-breadcrumb{background:#fff !important;border-bottom:1px solid #e8eaed !important;padding:10px 0 !important;font-size:13px !important;color:#5f6368 !important;display:block !important;width:100% !important;margin:0 !important}
.agoda-breadcrumb a{color:#3264ff !important;text-decoration:none !important;font-weight:500 !important}
.agoda-breadcrumb a:hover{color:#174ea6 !important;text-decoration:underline !important}
.agoda-breadcrumb .agoda-bc-sep{color:#9aa0a6 !important;margin:0 4px !important;user-select:none}
.agoda-breadcrumb .agoda-bc-count{color:#5f6368 !important;font-weight:400 !important}
.agoda-breadcrumb .agoda-bc-current{color:#202124 !important;font-weight:600 !important}
.agoda-breadcrumb .agoda-bc-seeall{color:#3264ff !important;font-weight:700 !important;white-space:nowrap !important;text-decoration:none !important}
.agoda-breadcrumb .agoda-bc-seeall:hover{text-decoration:underline !important}
.agoda-breadcrumb .agoda-bc-inner{max-width:1180px !important;margin:0 auto !important;padding:0 12px !important;display:flex !important;align-items:center !important;justify-content:space-between !important;gap:12px !important;flex-wrap:wrap !important}
.agoda-breadcrumb .agoda-bc-trail{display:flex !important;align-items:center !important;gap:6px !important;flex-wrap:wrap !important;font-size:13px !important;line-height:1.4 !important}
.agoda-gallery{max-width:1180px;margin:12px auto;display:grid;grid-template-columns:1.55fr 0.85fr 0.85fr 0.85fr;grid-template-rows:180px 180px;gap:8px;padding:0 12px}
.agoda-gallery-hero{grid-row:1 / span 2;grid-column:1;position:relative;overflow:hidden;border-radius:12px;background:#e8ecef}
.agoda-gallery-hero img{width:100%;height:100%;object-fit:cover;display:block}
.agoda-gallery-item{position:relative;overflow:hidden;border-radius:12px;background:#e8ecef}
.agoda-gallery-item img{width:100%;height:100%;object-fit:cover;display:block}
.agoda-see-all{position:absolute;left:50%;bottom:14px;transform:translateX(-50%);background:rgba(255,255,255,0.96);border:none;border-radius:20px;padding:7px 14px;font-size:13px;font-weight:600;color:#202124;display:flex;align-items:center;gap:6px;box-shadow:0 2px 8px rgba(0,0,0,0.15)}
.agoda-gallery-item .agoda-video-pause{position:absolute;left:10px;bottom:10px;background:rgba(255,255,255,0.9);border-radius:50%;width:32px;height:32px;display:flex;align-items:center;justify-content:center;font-size:12px}
.agoda-tabs-wrap{background:#fff;border:1px solid #e8eaed;border-radius:8px;max-width:1180px;margin:14px auto;padding:0 8px 0 8px;display:flex;align-items:center;gap:0;height:58px;overflow-x:auto;scrollbar-width:none}
.agoda-tabs-wrap::-webkit-scrollbar{display:none}
.agoda-tab{padding:0 14px;height:56px;display:flex;align-items:center;font-size:14px;font-weight:500;color:#5f6368;border-bottom:2px solid transparent;white-space:nowrap;text-decoration:none;cursor:pointer;background:none;border-left:none;border-right:none;border-top:none}
.agoda-tab.active{color:#3264ff;border-bottom-color:#3264ff;font-weight:700}
.agoda-tab:hover{color:#202124}
.agoda-deal-cta{margin-left:auto;display:flex;align-items:center;gap:12px;padding-left:12px;white-space:nowrap}
.agoda-deal-price{font-size:13px;color:#70757a}
.agoda-deal-price b{color:#e53935;font-size:22px;font-weight:800;margin-left:4px}
.agoda-view-deal{background:#3576f6;color:#fff;border:none;border-radius:24px;padding:10px 20px;font-weight:800;font-size:13px;letter-spacing:0.02em;cursor:pointer}
.agoda-view-deal:hover{background:#2a5ad6}
.agoda-detail-grid{max-width:1180px;margin:24px auto;display:grid;grid-template-columns:1fr 360px;gap:24px;padding:0 20px;align-items:start}
.agoda-rooms-header{margin:24px 0 16px !important;padding:0 4px}
.agoda-card{padding:20px !important}
#rooms-section + .agoda-card{padding:20px !important}
@media(min-width:993px){
  #rooms-section{padding:0 8px !important}
  #rooms-section + .agoda-card{padding-left:16px !important;padding-right:16px !important}
  .agoda-room-card{margin-left:0 !important;margin-right:0 !important}
  #facilities-section{padding-left:0 !important;padding-right:0 !important}
}
/* ========== Mobile responsiveness — keep PC 10/10 intact ========== */
@media(max-width:992px){
  .agoda-gallery{grid-template-columns:1fr 1fr;grid-template-rows:240px 140px 140px}
  .agoda-gallery-hero{grid-column:1 / span 2;grid-row:1}
  .agoda-detail-grid{grid-template-columns:1fr}
  .agoda-deal-cta{flex-shrink:0}
  .agoda-bc-inner{flex-direction:column!important;align-items:flex-start!important;gap:6px!important}
}
@media(max-width:768px){
  /* Breadcrumb — hotel-detail mobile: reduced 2× */
  .agoda-breadcrumb{position:relative !important;top:auto !important;z-index:1 !important;display:block !important;visibility:visible !important;padding:0 !important;background:#fff !important;border-bottom:1px solid #e8eaed !important;overflow:visible !important;min-height:0 !important;margin:24px 0 !important}
  .agoda-bc-inner{flex-direction:column!important;align-items:stretch!important;gap:0!important;padding:16px 16px !important;padding-left:calc(20px + env(safe-area-inset-left,0px)) !important}
  .agoda-bc-trail{flex-wrap:nowrap!important;overflow-x:auto!important;overflow-y:hidden !important;white-space:nowrap!important;max-width:100%!important;scrollbar-width:none;-webkit-overflow-scrolling:touch;padding:4px 0 !important;gap:12px !important;scroll-snap-type:x proximity;font-size:12px !important}
  .agoda-gallery{padding:0 16px !important;gap:16px !important;margin:20px auto !important}
  .agoda-detail-grid{padding:0 16px !important;gap:20px !important;margin:20px auto !important}
  .agoda-tabs-wrap{margin:20px auto !important;padding:0 16px !important}
  .agoda-bc-trail::-webkit-scrollbar{display:none}
  .agoda-bc-trail a,.agoda-bc-trail span{scroll-snap-align:start}
  .agoda-bc-seeall{display:none !important}
  /* Gallery: hero full width + 2-col thumbs below */
  .agoda-gallery{grid-template-columns:1fr 1fr;grid-template-rows:260px 130px 130px;gap:6px;padding:0 8px;margin:8px auto}
  .agoda-gallery-hero{grid-column:1 / span 2;grid-row:1;border-radius:10px}
  .agoda-gallery-item{border-radius:10px}
  .agoda-gallery-item:not(.agoda-gallery-map):nth-child(n+5){display:none} /* show only hero + 3 thumbs on tablet */
  .agoda-gallery-map{grid-column:1 / span 2 !important;display:flex !important;min-height:130px}
  /* Tabs: horizontal scroll, sticky under navbar */
  .agoda-tabs-wrap{position:sticky;top:0;z-index:90;border-radius:0;border-left:none;border-right:none;margin:0 auto;height:48px;padding:0 0 0 8px;gap:0;overflow-x:auto;overflow-y:hidden;-webkit-overflow-scrolling:touch;scrollbar-width:none}
  .agoda-tabs-wrap::-webkit-scrollbar{display:none}
  .agoda-tab{height:46px;font-size:13px;padding:0 12px;flex:0 0 auto;border-bottom-width:2px}
  .agoda-deal-cta{display:none} /* avoid crowding; price CTA moves to bottom bar on mobile if needed */
  /* Grid stacking with comfortable gaps — rebalanced more breathing */
  .agoda-detail-grid{padding:0 16px;gap:20px;margin:20px auto}
  .agoda-card{padding:18px;border-radius:12px}
  .agoda-title{font-size:20px}
  .agoda-address{font-size:12.5px}
  .agoda-facilities-grid{grid-template-columns:1fr 1fr;gap:10px 12px}
  .agoda-rating-card{padding:14px}
  .agoda-rooms-header h2{font-size:19px}
}
@media(max-width:600px){
  .agoda-gallery{grid-template-columns:1fr 1fr;grid-template-rows:220px 110px 110px;gap:6px;padding:0 8px}
  .agoda-gallery-hero{grid-column:1 / span 2;border-radius:10px}
  .agoda-gallery-item{border-radius:10px}
  .agoda-gallery-item:not(.agoda-gallery-map):nth-child(n+4){display:none} /* mobile: hero + 2 thumbs only = clean */
  .agoda-gallery-map{grid-column:1 / span 2 !important;display:flex !important;min-height:110px}
  .agoda-see-all{font-size:12px;padding:6px 12px;bottom:10px}
  .agoda-detail-grid{padding:0 12px}
  .agoda-tabs-wrap{height:46px;padding:0 0 0 8px}
  .agoda-tab{font-size:12.5px;padding:0 10px;height:44px}
  .agoda-card{padding:12px}
  .agoda-highlights-title{font-size:16px}
  .agoda-highlight{padding:8px 0}
  .agoda-highlight-thumb{width:46px;height:42px;flex:0 0 46px}
  .agoda-highlight-text b{font-size:13px}
  .agoda-highlight-text span{font-size:12px}
  .agoda-facilities-grid{grid-template-columns:1fr 1fr;gap:8px 10px}
  .agoda-fac-item{font-size:12.5px}
  .agoda-high-demand{padding:12px 14px}
  .agoda-high-demand b{font-size:14px}
  .agoda-rating-big{gap:8px}
  .agoda-rating-score{font-size:21px;padding:8px 10px}
  .agoda-bar-row{font-size:12px}
  .agoda-review-snippet{min-width:260px;max-width:260px;padding:10px}
  .agoda-map-thumb{height:140px}
  .agoda-location-score{padding:12px 14px}
  .agoda-landmarks{padding:12px 14px}
  .agoda-landmark-row{font-size:12.5px}
  .agoda-rooms-header{margin:12px 0 8px}
  .agoda-rooms-header h2{font-size:18px}
}
@media(max-width:480px){
  .agoda-gallery{grid-template-columns:1fr;grid-template-rows:200px 100px 100px;gap:6px}
  .agoda-gallery-hero{grid-column:1;grid-row:1}
  .agoda-gallery-item{grid-column:1}
  .agoda-gallery-item:not(.agoda-gallery-map):nth-child(2){display:block} /* hero + 2 stacked thumbs */
  .agoda-gallery-item:not(.agoda-gallery-map):nth-child(3){display:block}
  .agoda-gallery-item:not(.agoda-gallery-map):nth-child(n+4){display:none}
  .agoda-gallery-map{grid-column:1 !important;display:flex !important;min-height:100px}
  .agoda-tabs-wrap{height:44px}
  .agoda-tab{font-size:12px;padding:0 8px;height:42px}
  .agoda-title{font-size:18px;line-height:1.3}
  .agoda-breadcrumb{font-size:12px}
  .agoda-facilities-grid{grid-template-columns:1fr;gap:8px} /* single column for tiny phones */
  .agoda-rating-card div[style*="grid-template-columns:1fr 1fr"]{grid-template-columns:1fr!important;gap:2px!important}
  .agoda-review-snippet{min-width:240px;max-width:240px}
  .agoda-card{border-radius:10px}
}
@media(max-width:375px){
  .agoda-gallery{grid-template-rows:190px 90px 90px}
  .agoda-tabs-wrap{padding-left:2px}
  .agoda-tab{padding:0 7px;font-size:11.5px}
  .agoda-title{font-size:17px}
}
.agoda-card{background:#fff;border:1px solid #e0e6ef;border-radius:12px;padding:16px 16px}
.agoda-badge-bestseller{background:#e53935;color:#fff;border-radius:6px;padding:6px 10px;font-size:12px;font-weight:700;display:inline-block;line-height:1}
.agoda-badge-agoda-pref{display:flex;align-items:center;gap:6px;font-size:13px;color:#5f6368}
.agoda-title{font-size:23px;font-weight:800;color:#1a1d25;margin:12px 0 6px;line-height:1.25;letter-spacing:-0.01em}
.agoda-stars{color:#b7791f;font-size:13px;letter-spacing:2px;vertical-align:middle;margin-left:6px}
.agoda-address{font-size:13px;color:#5f6368;margin-top:4px;line-height:1.4}
.agoda-address a{color:#3264ff;text-decoration:none;font-weight:700}
.agoda-highlights-title{font-size:18px;font-weight:800;color:#1a1d25;margin:2px 0 12px}
.agoda-highlight{display:flex;gap:12px;padding:10px 0;border-bottom:1px solid #f1f3f4;align-items:flex-start}
.agoda-highlight:last-child{border:none}
.agoda-highlight-thumb{width:52px;height:48px;border-radius:8px;overflow:hidden;flex:0 0 52px;background:#f1f3f4;position:relative}
.agoda-highlight-thumb img{width:100%;height:100%;object-fit:cover}
.agoda-thumb-check{position:absolute;right:-2px;bottom:-2px;background:#0ab21b;color:#fff;border-radius:50%;width:20px;height:20px;display:flex;align-items:center;justify-content:center;font-size:10px;border:2px solid #fff}
.agoda-highlight-text b{font-size:14px;color:#202124;display:block;line-height:1.2}
.agoda-highlight-text span{font-size:13px;color:#5f6368;display:block;margin-top:3px;line-height:1.35}
/* Facilities - PC 4 cols, responsive handled in breakpoints above */
.agoda-facilities-grid{display:grid;grid-template-columns:1fr 1fr 1fr 1fr;gap:8px 16px;margin-top:12px}
.agoda-fac-item{display:flex;align-items:center;gap:8px;font-size:13px;color:#202124}
.agoda-fac-item i{font-size:12px}
.agoda-free-badge{background:#e6f4ea;color:#137333;font-size:11px;font-weight:700;padding:3px 8px;border-radius:6px;margin-left:4px}
.agoda-see-all-link{color:#3264ff;font-size:14px;font-weight:700;text-decoration:none}
.agoda-see-all-link:hover{text-decoration:underline}
/* High demand */
.agoda-high-demand{background:#fef2f2;border:1px solid #fee2e2;border-radius:10px;padding:16px 18px;margin-top:12px}
.agoda-high-demand b{color:#e53935;font-size:15px}
.agoda-high-demand span{color:#202124;font-size:13px;display:block;margin-top:4px}
/* Right column cards */
.agoda-rating-card{background:#fff;border:1px solid #e0e6ef;border-radius:12px;padding:16px}
.agoda-rating-big{display:flex;align-items:center;gap:10px;margin-bottom:12px}
.agoda-rating-score{background:#0d4a7a;color:#fff;border-radius:10px 10px 10px 0;padding:10px 12px;font-size:24px;font-weight:800;line-height:1}
.agoda-rating-label b{font-size:16px;color:#0d4a7a}
.agoda-rating-label span{font-size:13px;color:#5f6368;display:block}
.agoda-see-all-reviews{color:#3264ff;font-size:13px;font-weight:600;text-decoration:none;margin-left:auto}
.agoda-bar-row{display:flex;align-items:center;justify-content:space-between;font-size:13px;color:#202124;margin:8px 0 4px}
.agoda-bar-row span:last-child{color:#202124;font-weight:600}
.agoda-bar-bg{height:6px;background:#e8eaed;border-radius:99px;overflow:hidden;margin-bottom:8px;display:flex;gap:0}
.agoda-bar-fill{height:100%;background:#3264ff;border-radius:99px}
.agoda-review-scroll{display:flex;gap:12px;overflow-x:auto;scrollbar-width:none;padding-bottom:2px;margin-top:12px}
.agoda-review-scroll::-webkit-scrollbar{display:none}
.agoda-review-snippet{border:1px solid #e8eaed;border-radius:10px;padding:12px 12px;font-size:13px;color:#202124;line-height:1.4;background:#fff;min-width:280px;max-width:280px;flex:0 0 85%}
.agoda-review-author{font-size:12px;color:#5f6368;margin-top:8px;display:flex;align-items:center;gap:6px}
.agoda-review-arrow{position:absolute;right:10px;top:50%;transform:translateY(-50%);background:#fff;border:1px solid #e8eaed;box-shadow:0 2px 8px rgba(0,0,0,0.12);border-radius:50%;width:32px;height:32px;display:flex;align-items:center;justify-content:center;cursor:pointer}
.agoda-map-card{background:#fff;border:1px solid #e0e6ef;border-radius:12px;overflow:hidden}
.agoda-map-thumb{height:150px;background:#e8ecef;position:relative;overflow:hidden}
.agoda-map-thumb img{width:100%;height:100%;object-fit:cover;opacity:1}
.agoda-map-pin{position:absolute;left:60%;top:46%;transform:translate(-50%,-50%);background:#e53935;color:#fff;border-radius:50% 50% 50% 0;transform:translate(-50%,-50%) rotate(-45deg);width:30px;height:30px;display:flex;align-items:center;justify-content:center;box-shadow:0 2px 6px rgba(0,0,0,0.25)}
.agoda-map-pin i{transform:rotate(45deg);font-size:14px}
.agoda-map-see{position:absolute;left:50%;bottom:16px;transform:translateX(-50%);background:#fff;border:1px solid #e8eaed;border-radius:8px;padding:6px 14px;font-size:13px;font-weight:700;color:#202124;box-shadow:0 2px 6px rgba(0,0,0,0.1)}
.agoda-location-score{padding:14px 16px;border-bottom:1px solid #f1f3f4}
.agoda-location-score b{font-size:16px;color:#202124}
.agoda-location-score span{font-size:13px;color:#70757a;margin-top:2px;display:block}
.agoda-excellent-loc{padding:10px 16px;display:flex;align-items:center;gap:8px;font-size:13px;font-weight:700;color:#202124;border-bottom:1px solid #f1f3f4}
.agoda-parking-row{padding:12px 16px;display:flex;align-items:center;justify-content:space-between;border-bottom:1px solid #f1f3f4}
.agoda-landmarks{padding:14px 16px}
.agoda-landmarks h4{font-size:13px;font-weight:800;color:#202124;margin:0 0 12px}
.agoda-landmark-row{display:flex;align-items:center;justify-content:space-between;font-size:13px;color:#202124;padding:5px 0}
.agoda-landmark-row span:last-child{color:#5f6368}
.agoda-rooms-header{display:flex;align-items:center;justify-content:space-between;margin:16px 0 10px;gap:12px;flex-wrap:wrap}
.agoda-rooms-header h2{font-size:22px;font-weight:800;color:#1a1d25;margin:0}
.agoda-price-match{font-size:13px;color:#3264ff;font-weight:700;display:flex;align-items:center;gap:6px;white-space:nowrap}
/* Mobile sticky bar */
.agoda-mobile-bar{display:none;position:fixed;bottom:0;left:0;right:0;z-index:999;background:#fff;border-top:1px solid #e0e6ef;padding:10px 12px calc(10px + env(safe-area-inset-bottom,0px));align-items:center;justify-content:space-between;gap:12px;box-shadow:0 -4px 16px rgba(0,0,0,0.08)}
.agoda-mobile-bar-price{display:flex;flex-direction:column;line-height:1}
.agoda-mobile-bar-price span{font-size:11px;color:#5f6368}
.agoda-mobile-bar-price b{font-size:16px;color:#e53935;font-weight:800}
@media(max-width:768px){.agoda-mobile-bar{display:flex} body{padding-bottom:68px} } /* prevent content hidden behind bar */

/* Global mobile safeguards */
html,body{max-width:100%;overflow-x:hidden}
img{max-width:100%;height:auto}
.agoda-detail-grid *{min-width:0}
@media(max-width:768px){
  /* Prevent horizontal scroll from long address */
  .agoda-address{word-break:break-word;overflow-wrap:anywhere}
  /* Make rooms card content scrollable, not overflowing */
  .agoda-card table,.agoda-card .table{width:100%;display:block;overflow-x:auto;white-space:nowrap;-webkit-overflow-scrolling:touch}
  /* Rating review scroll snap for smooth swipe */
  .agoda-review-scroll{scroll-snap-type:x mandatory;-webkit-overflow-scrolling:touch}
  .agoda-review-snippet{scroll-snap-align:start}
  .agoda-review-arrow{width:28px;height:28px;right:6px}
}
</style>
<?= $this->element('navbar') ?>

<!-- Breadcrumb — dynamic + scoped classes for CSS isolation -->
<nav class="agoda-breadcrumb">
  <div class="agoda-bc-inner">
    <div class="agoda-bc-trail">
      <a href="/">Home</a> <span class="agoda-bc-sep">›</span>
      <a href="/?destination=<?= urlencode($propCity) ?>"><?= h($propCity) ?> Hotels</a> <span class="agoda-bc-count">(<?= h($propCity === 'Dar es Salaam' ? '520' : '326') ?>)</span> <span class="agoda-bc-sep">›</span>
      <a href="/?destination=<?= urlencode($propCity) ?>&amp;property_type=Resort"><?= h($propCity) ?> Resorts</a> <span class="agoda-bc-count">(6)</span> <span class="agoda-bc-sep">›</span>
      <span class="agoda-bc-current">Book <?= h($propTitle) ?></span>
    </div>
    <a href="/?destination=<?= urlencode($propCity) ?>" class="agoda-bc-seeall">See all <?= h($propCity === 'Dar es Salaam' ? '520' : '326') ?> properties in <?= h($propCity) ?></a>
  </div>
</nav>

<!-- Gallery — 8 photos mosaic -->
<div class="agoda-gallery" id="agoda_gallery">
  <div class="agoda-gallery-hero" onclick="openPhotoLightbox(0)" style="cursor:pointer">
    <img src="<?= h($galleryImages[0]) ?>" alt="<?= h($propTitle) ?>" loading="lazy" width="600" height="400">
    <button class="agoda-see-all" onclick="event.stopPropagation();openPhotoLightbox(0)"><i class="fa-solid fa-images"></i> See all photos</button>
  </div>
  <?php foreach(array_slice($galleryImages,1,5) as $i => $g): ?>
  <div class="agoda-gallery-item" onclick="openPhotoLightbox(<?= $i+1 ?>)" style="cursor:pointer">
    <img src="<?= h($g) ?>" alt="<?= h($propTitle) ?> photo <?= $i+2 ?>" loading="lazy" width="300" height="200">
    <?php if($i===1): ?><div class="agoda-video-pause"><i class="fa-solid fa-pause"></i></div><?php endif; ?>
    <?php if($i===1): ?><div style="position:absolute;bottom:8px;right:8px;display:flex;gap:4px"><span style="width:6px;height:6px;border-radius:50%;background:#fff;opacity:0.9"></span><span style="width:6px;height:6px;border-radius:50%;background:#fff;opacity:0.5"></span><span style="width:6px;height:6px;border-radius:50%;background:#fff;opacity:0.5"></span><span style="width:6px;height:6px;border-radius:50%;background:#fff;opacity:0.3"></span></div><?php endif; ?>
  </div>
  <?php endforeach; ?>
  <!-- Fill last unused cell with map (balances desktop, no white gap) -->
  <div class="agoda-gallery-item agoda-gallery-map" onclick="openHotelMapModal()" style="cursor:pointer;background:#e8ecef;position:relative;overflow:hidden;display:flex;align-items:center;justify-content:center">
    <div style="position:absolute;inset:0;background:linear-gradient(135deg,#e8f0f8 0%,#f0e8d8 50%,#e8ecef 100%)"></div>
    <svg style="position:absolute;inset:0;width:100%;height:100%" viewBox="0 0 300 180" preserveAspectRatio="none"><path d="M0 90 L300 50" stroke="#f5c542" stroke-width="5" fill="none"/><path d="M0 120 L300 80" stroke="#fff" stroke-width="7" fill="none"/><path d="M70 0 L120 180" stroke="#fff" stroke-width="5" fill="none"/></svg>
    <div style="position:absolute;left:50%;top:42%;transform:translate(-50%,-50%);background:#e53935;color:#fff;border-radius:50% 50% 50% 0;transform:translate(-50%,-50%) rotate(-45deg);width:28px;height:28px;display:flex;align-items:center;justify-content:center;box-shadow:0 2px 6px rgba(0,0,0,.25)"><i class="fa-solid fa-location-dot" style="transform:rotate(45deg);font-size:13px"></i></div>
    <span style="position:relative;z-index:1;background:#fff;border-radius:8px;padding:6px 12px;font-size:12px;font-weight:700;color:#202124;box-shadow:0 2px 6px rgba(0,0,0,.12);margin-top:18px">SEE MAP</span>
  </div>
</div>

<!-- Tabs — Overview active — matches screenshot -->
<div class="agoda-tabs-wrap" id="agoda_tabs">
  <button class="agoda-tab active" data-tab="overview">Overview</button>
  <button class="agoda-tab" data-tab="rooms">Rooms</button>
  <button class="agoda-tab" data-tab="trip">Trip recommendations</button>
  <button class="agoda-tab" data-tab="facilities">Facilities</button>
  <button class="agoda-tab" data-tab="reviews">Reviews</button>
  <button class="agoda-tab" data-tab="location">Location</button>
  <button class="agoda-tab" data-tab="policies">Policies</button>
  <div class="agoda-deal-cta">
    <span class="agoda-deal-price">from <b>TSh <?= number_format($propPrice) ?></b></span>
    <button class="agoda-view-deal" onclick="document.getElementById('rooms-section')?.scrollIntoView({behavior:'smooth'})">VIEW THIS DEAL</button>
  </div>
</div>

<!-- Mobile sticky price bar — visible only <768px -->
<div class="agoda-mobile-bar" id="agoda_mobile_bar" aria-hidden="true">
  <div class="agoda-mobile-bar-price"><span>from</span><b>TSh <?= number_format($propPrice) ?></b></div>
  <button class="agoda-view-deal" onclick="document.getElementById('rooms-section')?.scrollIntoView({behavior:'smooth'})">VIEW THIS DEAL</button>
</div>

<!-- Main grid — LEFT + RIGHT as screenshot -->
<div class="agoda-detail-grid">
  <!-- LEFT COLUMN -->
  <div style="display:flex;flex-direction:column;gap:14px">
    <!-- Title card -->
    <div class="agoda-card" id="overview-section">
      <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:center">
        <span class="agoda-badge-bestseller">Best seller</span>
        <button style="margin-left:auto;background:none;border:1px solid #e8eaed;border-radius:50%;width:36px;height:36px;display:flex;align-items:center;justify-content:center;cursor:pointer" onclick="toggleWishlist(<?= $detailPropertyId ?>,this)" aria-label="Save"><i class="fa-regular fa-heart" style="color:#5f6368;font-size:16px"></i></button>
      </div>
      <div class="agoda-title"><?= h($propTitle) ?> <span class="agoda-stars"><?= str_repeat('★', $propStars) ?><?= $propStars<5 ? str_repeat('☆',5-$propStars) : '' ?></span></div>
      <div class="agoda-address"><?= h($propAddress) ?></div>
      <div style="margin-top:10px;display:flex;gap:8px;flex-wrap:wrap">
        <button type="button" onclick="openHotelMapModal()" style="background:#fff;border:1px solid #dadce0;border-radius:9999px;padding:7px 14px;font-size:13px;font-weight:600;color:#1A73E8;display:inline-flex;align-items:center;gap:6px;cursor:pointer"><i class="fa-solid fa-location-dot"></i> SEE MAP</button>
        <button type="button" class="agoda-view-deal" onclick="document.getElementById('rooms-section')?.scrollIntoView({behavior:'smooth'})" style="padding:7px 16px;font-size:13px">View Room</button>
      </div>
    </div>

    <!-- Facilities — 8 items + See all -->
    <div class="agoda-card" id="facilities-section">
      <div style="display:flex;align-items:center;justify-content:space-between">
        <div style="font-size:18px;font-weight:800;color:#1a1d25">Facilities</div>
        <a href="javascript:void(0)" class="agoda-see-all-link" onclick="document.getElementById('facilities-section')?.scrollIntoView({behavior:'smooth'})">See all</a>
      </div>
      <div class="agoda-facilities-grid">
        <div class="agoda-fac-item"><i class="fa-solid fa-check" style="color:#202124"></i> Beach</div>
        <div class="agoda-fac-item"><i class="fa-solid fa-check" style="color:#202124"></i> Free Wi-Fi <span class="agoda-free-badge">Free</span></div>
        <div class="agoda-fac-item"><i class="fa-solid fa-check" style="color:#202124"></i> Swimming pool</div>
        <div class="agoda-fac-item"><i class="fa-solid fa-check" style="color:#202124"></i> Free parking <span class="agoda-free-badge">Free</span></div>
        <div class="agoda-fac-item"><i class="fa-solid fa-check" style="color:#202124"></i> Spa</div>
        <div class="agoda-fac-item"><i class="fa-solid fa-check" style="color:#202124"></i> Front desk [24-hour]</div>
        <div class="agoda-fac-item"><i class="fa-solid fa-check" style="color:#202124"></i> Fitness center <span class="agoda-free-badge">Free</span></div>
        <div class="agoda-fac-item"><i class="fa-solid fa-check" style="color:#202124"></i> Restaurants</div>
      </div>
      <?= $this->element('Listing/Hotel/hotel-detail/amenities'); ?>
    </div>

    <!-- Select your room header -->
    <div class="agoda-rooms-header" id="rooms-section">
      <h2>Select your room</h2>
      <span class="agoda-price-match"><i class="fa-solid fa-badge-check" style="font-size:16px"></i> We price match!</span>
    </div>
    <div class="agoda-card" style="padding:14px">
      <?= $this->element('Listing/Hotel/hotel-detail/rooms') ?>
    </div>

    <!-- Reviews / Policies anchors for tabs -->
    <div id="reviews-section" class="agoda-card" style="margin-top:2px">
      <div style="font-size:16px;font-weight:800;color:#202124;margin-bottom:10px">Reviews</div>
      <?= $this->element('Listing/Hotel/hotel-detail/guests-reviews') ?>
    </div>
    <div id="policies-section" class="agoda-card">
      <div style="font-size:16px;font-weight:800;color:#202124;margin-bottom:10px">Policies</div>
      <div style="font-size:13px;color:#5f6368;line-height:1.6">
        Check-in from 2:00 PM · Check-out until 11:00 AM<br>
        Free cancellation until 24h before check-in. Pets allowed on request.
      </div>
    </div>
  </div>

  <!-- RIGHT COLUMN — rating + map + landmarks as screenshot -->
  <div style="display:flex;flex-direction:column;gap:14px">
    <!-- Rating card -->
    <div class="agoda-rating-card">
      <div class="agoda-rating-big">
        <div class="agoda-rating-score"><?= h($propRating) ?></div>
        <div class="agoda-rating-label"><b>Excellent</b><span><?= number_format($reviewsCount) ?> reviews</span></div>
        <a href="#reviews-section" class="agoda-see-all-reviews">See all</a>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:4px 24px">
        <div>
          <div class="agoda-bar-row"><span>Cleanliness</span><span style="color:#3264ff">8.5</span></div>
          <div class="agoda-bar-bg"><div class="agoda-bar-fill" style="width:85%"></div></div>
        </div>
        <div>
          <div class="agoda-bar-row"><span>Facilities</span><span style="color:#3264ff">8.4</span></div>
          <div class="agoda-bar-bg"><div class="agoda-bar-fill" style="width:84%"></div></div>
        </div>
        <div>
          <div class="agoda-bar-row"><span>Service</span><span style="color:#3264ff">8.4</span></div>
          <div class="agoda-bar-bg"><div class="agoda-bar-fill" style="width:84%"></div></div>
        </div>
        <div>
          <div class="agoda-bar-row"><span>Value for money</span><span style="color:#3264ff">8.1</span></div>
          <div class="agoda-bar-bg"><div class="agoda-bar-fill" style="width:81%"></div></div>
        </div>
      </div>
      <div style="position:relative;margin-top:14px">
        <div class="agoda-review-scroll" id="agoda_review_scroll">
          <div class="agoda-review-snippet">
            The beautiful beach was a favorite and it was never crowded.
            <div class="agoda-review-author"><span style="font-size:14px">🇺🇸</span> <b>Julie</b> <span style="color:#9aa0a6">|</span> United States</div>
          </div>
          <div class="agoda-review-snippet">
            The facilities were great and the staff were very friendly.
            <div class="agoda-review-author"><span style="font-size:14px">🇺🇸</span> <b>dave</b> <span style="color:#9aa0a6">|</span> United States</div>
          </div>
        </div>
        <button class="agoda-review-arrow" onclick="document.getElementById('agoda_review_scroll').scrollBy({left:280,behavior:'smooth'})"><i class="fa-solid fa-chevron-right" style="font-size:12px;color:#202124"></i></button>
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
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px"><b><?= h($propTitle) ?></b><button onclick="closeHotelMapModal()" style="border:none;background:#f1f3f4;border-radius:50%;width:32px;height:32px">✕</button></div>
    <div id="web1-hotel-detail-map" style="height:480px;background:#e8ecef;border-radius:8px"></div>
  </div>
</div>

<script>
let currentLightboxIdx=0; const galleryImagesList=<?= json_encode($galleryImages) ?>; let detailMapInstance=null;
function openPhotoLightbox(i){currentLightboxIdx=i;updateLightboxImg();document.getElementById('photo_lightbox_modal').style.display='flex'}
function closePhotoLightbox(){document.getElementById('photo_lightbox_modal').style.display='none'}
function updateLightboxImg(){if(currentLightboxIdx<0)currentLightboxIdx=galleryImagesList.length-1;if(currentLightboxIdx>=galleryImagesList.length)currentLightboxIdx=0;const img=document.getElementById('lightbox_main_img');const c=document.getElementById('lightbox_counter');if(img)img.src=galleryImagesList[currentLightboxIdx];if(c)c.textContent=(currentLightboxIdx+1)+' / '+galleryImagesList.length}
function nextLightboxPhoto(){currentLightboxIdx++;updateLightboxImg()}
function prevLightboxPhoto(){currentLightboxIdx--;updateLightboxImg()}
function openHotelMapModal(){document.getElementById('hotel_map_modal').style.display='flex'; if(!detailMapInstance) initDetailMap(); else setTimeout(()=>detailMapInstance.resize(),200)}
function closeHotelMapModal(){document.getElementById('hotel_map_modal').style.display='none'}
document.querySelectorAll('.agoda-tab').forEach(btn=>{
  btn.addEventListener('click',()=>{
    document.querySelectorAll('.agoda-tab').forEach(b=>b.classList.remove('active'));
    btn.classList.add('active');
    const id=btn.dataset.tab;
    const map={overview:'overview-section',rooms:'rooms-section',trip:'rooms-section',facilities:'facilities-section',reviews:'reviews-section',location:'location-section',policies:'policies-section'};
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
</script>
<div class="d-none d-lg-block">
<?= $this->element('footer', ['skin' => 'skin-light-footer']) ?>
</div>
