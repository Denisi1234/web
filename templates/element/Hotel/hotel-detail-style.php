<style>
/* Agoda exact detail — matches screenshot */
.agoda-breadcrumb{background:#fff !important;border-bottom:1px solid #e8eaed !important;padding:10px 0 !important;font-size:13px !important;color:#5f6368 !important;display:block !important;width:100% !important;margin:0 !important}
.agoda-breadcrumb a{color:#0f62fe !important;text-decoration:none !important;font-weight:500 !important}
.agoda-breadcrumb a:hover{color:#174ea6 !important;text-decoration:underline !important}
.agoda-breadcrumb .agoda-bc-sep{color:#9aa0a6 !important;margin:0 4px !important;user-select:none}
.agoda-breadcrumb .agoda-bc-count{color:#5f6368 !important;font-weight:400 !important}
.agoda-breadcrumb .agoda-bc-current{color:#202124 !important;font-weight:600 !important}
.agoda-breadcrumb .agoda-bc-seeall{color:#0f62fe !important;font-weight:700 !important;white-space:nowrap !important;text-decoration:none !important}
.agoda-breadcrumb .agoda-bc-seeall:hover{text-decoration:underline !important}
.agoda-breadcrumb .agoda-bc-inner{max-width:1180px !important;margin:0 auto !important;padding:0 12px !important;display:flex !important;align-items:center !important;justify-content:space-between !important;gap:12px !important;flex-wrap:wrap !important}
.agoda-breadcrumb .agoda-bc-trail{display:flex !important;align-items:center !important;gap:6px !important;flex-wrap:wrap !important;font-size:13px !important;line-height:1.4 !important}
@media(min-width:769px){.agoda-breadcrumb-mobile{display:none !important}}
@media(max-width:768px){.agoda-breadcrumb-desktop{display:none !important}}
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
.agoda-tab.active{color:#0f62fe;border-bottom-color:#0f62fe;font-weight:700}
.agoda-tab:hover{color:#202124}
.agoda-deal-cta{margin-left:auto;display:flex;align-items:center;gap:12px;padding-left:12px;white-space:nowrap}
.agoda-deal-price{font-size:13px;color:#70757a}
.agoda-deal-price b{color:#e53935;font-size:22px;font-weight:800;margin-left:4px}
.agoda-view-deal{background:#0f62fe;color:#fff;border:none;border-radius:24px;padding:10px 20px;font-weight:800;font-size:13px;letter-spacing:0.02em;cursor:pointer}
.agoda-view-deal:hover{background:#0353e9}
.agoda-detail-grid{max-width:1180px;margin:24px auto;display:grid;grid-template-columns:1fr 360px;gap:24px;padding:0 20px;align-items:start}
.agoda-rooms-header{margin:24px 0 16px !important;padding:0 4px}
.agoda-card{padding:20px !important}
#rooms-section + .agoda-card{padding:20px !important}
@media(min-width:993px){
  #rooms-section{padding:0 8px !important}
  #rooms-section + .agoda-card{padding-left:16px !important;padding-right:16px !important}
  .agoda-room-card{margin-left:0 !important;margin-right:0 !important}
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
.agoda-address a{color:#0f62fe;text-decoration:none;font-weight:700}
/* Facilities - PC 4 cols, responsive handled in breakpoints above */
.agoda-free-badge{background:#e6f4ea;color:#137333;font-size:11px;font-weight:700;padding:3px 8px;border-radius:6px;margin-left:4px}
.agoda-see-all-link{color:#0f62fe;font-size:14px;font-weight:700;text-decoration:none}
.agoda-see-all-link:hover{text-decoration:underline}
/* High demand */
/* Right column cards */
.agoda-rating-card{background:#fff;border:1px solid #e0e6ef;border-radius:12px;padding:16px}
.agoda-rating-big{display:flex;align-items:center;gap:10px;margin-bottom:12px}
.agoda-rating-score{background:#0d4a7a;color:#fff;border-radius:10px 10px 10px 0;padding:10px 12px;font-size:24px;font-weight:800;line-height:1}
.agoda-rating-label b{font-size:16px;color:#0d4a7a}
.agoda-rating-label span{font-size:13px;color:#5f6368;display:block}
.agoda-see-all-reviews{color:#0f62fe;font-size:13px;font-weight:600;text-decoration:none;margin-left:auto}
.agoda-bar-row{display:flex;align-items:center;justify-content:space-between;font-size:13px;color:#202124;margin:8px 0 4px}
.agoda-bar-row span:last-child{color:#202124;font-weight:600}
.agoda-bar-bg{height:6px;background:#e8eaed;border-radius:99px;overflow:hidden;margin-bottom:8px;display:flex;gap:0}
.agoda-bar-fill{height:100%;background:#0f62fe;border-radius:99px}
.agoda-review-scroll{display:flex;gap:12px;overflow-x:auto;scrollbar-width:none;padding-bottom:2px;margin-top:12px}
.agoda-review-scroll::-webkit-scrollbar{display:none}
.agoda-review-snippet{border:1px solid #e8eaed;border-radius:10px;padding:12px 12px;font-size:13px;color:#202124;line-height:1.4;background:#fff;min-width:280px;max-width:280px;flex:0 0 85%}
.agoda-review-author{font-size:12px;color:#5f6368;margin-top:8px;display:flex;align-items:center;gap:6px}
.agoda-review-arrow{position:absolute;right:10px;top:50%;transform:translateY(-50%);background:#fff;border:1px solid #e8eaed;box-shadow:0 2px 8px rgba(0,0,0,0.12);border-radius:50%;width:44px;height:44px;display:flex;align-items:center;justify-content:center;cursor:pointer}
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
.agoda-price-match{font-size:13px;color:#0f62fe;font-weight:700;display:flex;align-items:center;gap:6px;white-space:nowrap}
/* Anchor sections land below the sticky tabs bar, never under it */
#rooms-section,#overview-section{scroll-margin-top:60px}
/* Title card: zero wasted top space */
#overview-section{padding-top:12px !important}
/* Sparse gallery (<5 photos): full-width hero + thumb strip, no empty cells */
.agoda-gallery-sparse{max-width:1180px;margin:12px auto;padding:0 12px}
.agoda-gallery-sparse .agoda-gallery-hero{position:relative;overflow:hidden;border-radius:12px;background:#e8ecef;height:340px;cursor:pointer}
.agoda-gallery-sparse .agoda-gallery-hero img{width:100%;height:100%;object-fit:cover;display:block}
.agoda-gallery-strip{display:grid;grid-auto-flow:column;grid-auto-columns:200px;gap:8px;overflow-x:auto;margin-top:8px;padding-bottom:4px;scrollbar-width:none}
.agoda-gallery-strip::-webkit-scrollbar{display:none}
.agoda-gallery-thumb{position:relative;overflow:hidden;border-radius:10px;background:#e8ecef;height:130px;cursor:pointer;flex:none}
.agoda-gallery-thumb img{width:100%;height:100%;object-fit:cover;display:block}
@media(max-width:768px){
  .agoda-gallery-sparse{padding:0 8px;margin:8px auto}
  .agoda-gallery-sparse .agoda-gallery-hero{height:230px;border-radius:10px}
  .agoda-gallery-strip{grid-auto-columns:150px}
  .agoda-gallery-thumb{height:100px}
}
/* Wider cards on desktop: 1280px canvas, slimmer sidebar (main column gains ~140px) */
@media(min-width:993px){
  .agoda-gallery,.agoda-tabs-wrap,.agoda-detail-grid,.agoda-breadcrumb .agoda-bc-inner{max-width:1280px !important}
  .agoda-detail-grid{grid-template-columns:1fr 330px !important}
}

/* ========== FREE MOBILE REDESIGN — hide secondary, keep primary ========== */
@media(max-width:768px){
  /* Hide desktop-only chrome on mobile — keep full on desktop */
  .agoda-detail-grid > div:last-child .agoda-rating-card,
  .agoda-map-card { display:none !important; }
  /* Simplify gallery to hero only on mobile */
  .agoda-gallery .agoda-gallery-map { display:none !important; }
  /* Facilities: show 4, hide rest */
  /* Hide trip recommendations tab on mobile */
  .agoda-tab[data-tab="trip"] { display:none !important; }
  /* Right column is empty on mobile (rating + map cards hidden) — drop it */
  .agoda-detail-grid > div:last-child { display:none !important; }
  /* Sticky bar removed: the title "View Rooms" button is mobile's jump link */
  .agoda-title-rooms-cta { display:inline-block !important;min-height:44px !important; }
}

/* ========== MOBILE TIGHTENING — less chrome, less space, same content ========== */
@media(max-width:768px){
  /* Grid: single column already — cut the desktop air out of it */
  .agoda-detail-grid{gap:12px !important;margin:12px auto !important;padding:0 12px !important}
  .agoda-detail-grid > div:first-child{display:flex;flex-direction:column;gap:12px !important}
  /* Cards: slimmer padding, tighter corners */
  .agoda-card{padding:14px !important;border-radius:12px !important}
  #rooms-section + .agoda-card{padding:12px !important}
  /* Title block: smaller type, shorter description, tighter rows */
  .agoda-title{font-size:19px !important;line-height:1.25 !important}
  .agoda-address{font-size:12.5px !important}
  #overview-section > div[style*="line-height:1.6"]{font-size:13px !important;line-height:1.5 !important}
  /* Gallery + tabs sit closer to content */
  .agoda-gallery{margin:8px auto !important;gap:6px !important}
  .agoda-tabs-wrap{margin:0 auto !important}
  /* Section headers: smaller, closer */
  .agoda-rooms-header{margin:8px 0 10px !important}
  .agoda-rooms-header h2{font-size:18px !important}
  /* Facilities heading + rows breathe less */
  /* Room rows: collapse side padding so cards use the full width */
  .agoda-card .room-card-title{font-size:16px !important}
  /* Offer price block is full-width now: left-align on mobile */
  .room-offer-price{align-items:flex-start !important;text-align:left !important}
  /* Footer breathing room at page end on mobile */
  footer{margin-bottom:8px !important}
}

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
  .agoda-review-arrow{width:44px;height:44px;right:6px}
}
</style>
