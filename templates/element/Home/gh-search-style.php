<style>
/* ── SPEC DESIGN TOKENS ── */
:root{--fns-blue:#0f62fe;--fns-blue-active:#0f62fe;--fns-border:#E5E7EB;--fns-text:#1F2937;--fns-text-sec:#6B7280;--fns-bg:#FFFFFF;--fns-pill-shadow:0 4px 20px rgba(0,0,0,.08);--fns-pill-shadow-hover:0 8px 30px rgba(0,0,0,.12);}
*{scrollbar-width:thin}
@media (prefers-reduced-motion:reduce){*{animation-duration:.01ms !important;transition-duration:.01ms !important;scroll-behavior:auto !important}}
.fns-search-wrap{position:sticky;top:64px;z-index:910;background:transparent;padding:0;}
/* any-device: clamp fluid type + touch coarseness */
@media (pointer:coarse){.fns-seg,.fns-chip,.fns-step-btn{min-height:44px} .fns-cta{min-height:48px}}
/* Unified pill — desktop — pixel grid 4px */
.fns-pill{position:relative;display:flex;align-items:stretch;background:var(--fns-bg);border:1px solid var(--fns-border);border-radius:9999px;box-shadow:var(--fns-pill-shadow);transition:box-shadow .2s ease,border-color .15s ease;overflow:visible;height:64px;padding:4px;gap:0;}
.fns-pill:hover{box-shadow:var(--fns-pill-shadow-hover);border-color:#D1D5DB;}
.fns-pill:focus-within{border-color:var(--fns-blue-active);box-shadow:0 0 0 3px rgba(37,99,235,.12), var(--fns-pill-shadow-hover);}
.fns-seg{position:relative;flex:1;display:flex;flex-direction:column;justify-content:center;padding:8px 16px;cursor:pointer;border-radius:9999px;transition:background .15s, box-shadow .15s, transform .12s;min-width:0;outline:none;margin:0;}
.fns-seg:hover{background:#F9FAFB;}
.fns-seg.active{background:#FFFFFF;box-shadow:0 0 0 1px var(--fns-border), 0 4px 12px rgba(0,0,0,.06);z-index:2;}
.fns-seg:active{transform:scale(.98);}
.fns-seg:focus-visible{box-shadow:0 0 0 2px var(--fns-blue-active);}
.fns-seg-label{font-family:'Inter','Google Sans',Roboto,sans-serif;font-size:11px;font-weight:600;letter-spacing:.08em;text-transform:uppercase;color:var(--fns-text-sec);line-height:12px;margin-bottom:4px;white-space:nowrap;}
.fns-seg-value{font-family:'Inter','Google Sans',Roboto,sans-serif;font-size:14px;font-weight:500;color:var(--fns-text);line-height:16px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.fns-seg-value.placeholder{color:#9CA3AF;font-weight:400;}
.fns-seg-input{border:none;outline:none;background:transparent;font-family:'Inter','Google Sans',Roboto,sans-serif;font-size:14px;font-weight:500;color:var(--fns-text);width:100%;padding:0;line-height:16px;}
.fns-seg-input::placeholder{color:#9CA3AF;font-weight:400;}
.fns-divider{width:1px;align-self:center;height:32px;background:var(--fns-border);flex-shrink:0;}
.fns-nights-badge{display:inline-flex;align-items:center;justify-content:center;min-height:20px;font-size:11px;font-weight:600;color:var(--fns-blue);background:#EFF6FF;border:1px solid #DBEAFE;border-radius:9999px;padding:0 8px;margin-left:8px;white-space:nowrap;line-height:1;}
.fns-cta{flex:0 0 auto;align-self:center;display:inline-flex;align-items:center;gap:8px;background:var(--fns-blue);color:#fff;border:none;border-radius:9999px;padding:0 24px;height:44px;min-height:48px;font-family:'Inter','Google Sans',Roboto,sans-serif;font-size:15px;font-weight:700;cursor:pointer;transition:transform .12s ease, filter .15s, background .15s;white-space:nowrap;box-shadow:0 4px 12px rgba(26,115,232,.3),0 1px 2px rgba(0,0,0,.08);}
.fns-cta:hover{filter:brightness(1.06);background:#0353e9;}
.fns-cta:active{transform:scale(.97);}
.fns-cta:focus-visible{outline:2px solid var(--fns-blue-active);outline-offset:2px;}
.fns-cta[aria-busy="true"]{pointer-events:none;opacity:.9}
.fns-cta-spinner{display:none;width:16px;height:16px;border:2px solid rgba(255,255,255,.4);border-top-color:#fff;border-radius:50%;animation:fnsSpin .7s linear infinite;flex-shrink:0}
.fns-cta[aria-busy="true"] .fns-cta-spinner{display:inline-block}
.fns-cta[aria-busy="true"] .fns-cta-icon{opacity:.6}
@keyframes fnsSpin{to{transform:rotate(360deg)}}
@media (prefers-reduced-motion:reduce){.fns-cta-spinner{animation:none;border-top-color:rgba(255,255,255,.9)}}
.fns-clear{position:absolute;right:8px;top:50%;transform:translateY(-50%);width:28px;height:28px;border-radius:50%;border:none;background:#F3F4F6;color:var(--fns-text-sec);display:none;align-items:center;justify-content:center;cursor:pointer;}
.fns-clear:hover{background:#E5E7EB;color:var(--fns-text);}
.fns-clear.show{display:flex;}
.fns-icon{color:var(--fns-blue);font-size:14px;flex-shrink:0;}
/* Popovers — desktop */
.fns-pop{position:absolute;top:calc(100% + 10px);left:0;background:#fff;border:1px solid var(--fns-border);border-radius:16px;box-shadow:0 8px 30px rgba(0,0,0,.12),0 4px 16px rgba(0,0,0,.08);z-index:1200;display:none;overflow:hidden;}
.fns-pop.open{display:block;animation:fnsPopIn .18s cubic-bezier(.32,.72,0,1);}
@keyframes fnsPopIn{from{opacity:0;transform:translateY(4px)}to{opacity:1;transform:translateY(0)}}
.fns-pop-dest{width:380px;max-width:92vw;}
.fns-pop-cal{left:50%;transform:translateX(-50%);width:660px;max-width:96vw;padding:16px;}
.fns-pop-cal.open{transform:translateX(-50%);}
.fns-pop-guests{right:0;left:auto;width:340px;padding:18px 18px 14px;}
/* Destination dropdown content */
.fns-dd-section{padding:12px 16px 6px;font-size:11px;font-weight:600;letter-spacing:.06em;text-transform:uppercase;color:var(--fns-text-sec);font-family:'Inter','Google Sans',Roboto,sans-serif;}
.fns-dd-item{display:flex;align-items:center;gap:12px;padding:10px 16px;cursor:pointer;transition:background .12s;}
.fns-dd-item:hover,.fns-dd-item.highlight{background:#F9FAFB;}
.fns-dd-item:active{background:#EFF6FF;}
.fns-dd-icon{width:36px;height:36px;border-radius:10px;background:#F3F4F6;display:flex;align-items:center;justify-content:center;color:var(--fns-text-sec);font-size:14px;flex-shrink:0;}
.fns-dd-label{font-size:14px;font-weight:500;color:var(--fns-text);font-family:'Inter','Google Sans',Roboto,sans-serif;line-height:1.2;}
.fns-dd-sub{font-size:12px;color:var(--fns-text-sec);}
.fns-recent{border-top:1px solid #F3F4F6;}
/* Calendar */
.fns-cal-head{display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;}
.fns-cal-nav{width:36px;height:36px;border-radius:50%;border:none;background:#fff;color:var(--fns-text-sec);display:flex;align-items:center;justify-content:center;cursor:pointer;}
.fns-cal-nav:hover{background:#F3F4F6;}
.fns-cal-nav:focus-visible{outline:2px solid var(--fns-blue);outline-offset:1px;}
.fns-cal-titles{flex:1;display:flex;justify-content:space-around;font-family:'Inter','Google Sans',Roboto,sans-serif;font-size:14px;font-weight:600;color:var(--fns-text);}
.fns-cal-grid-wrap{display:grid;grid-template-columns:1fr 1fr;gap:20px;}
@media(max-width:640px){.fns-cal-grid-wrap{grid-template-columns:1fr}}
.fns-cal-weekdays{display:grid;grid-template-columns:repeat(7,1fr);text-align:center;margin-bottom:6px;}
.fns-cal-weekdays span{font-size:11px;font-weight:600;color:var(--fns-text-sec);padding:6px 0;font-family:'Inter',Roboto,sans-serif;}
.fns-cal-days{display:grid;grid-template-columns:repeat(7,1fr);gap:2px;}
.fns-day{aspect-ratio:1;border:none;background:#fff;border-radius:50%;font-size:13px;color:var(--fns-text);cursor:pointer;display:flex;flex-direction:column;align-items:center;justify-content:center;position:relative;transition:background .12s;min-height:36px;font-family:'Inter','Google Sans',Roboto,sans-serif;}
.fns-day:hover:not(:disabled):not(.past){background:#F3F4F6;}
.fns-day.past{color:#D1D5DB;cursor:not-allowed;}
.fns-day.selected{background:var(--fns-blue);color:#fff;font-weight:600;}
.fns-day.in-range{background:#EFF6FF;color:var(--fns-blue);border-radius:0;}
.fns-day.range-start{background:var(--fns-blue);color:#fff;border-radius:50% 0 0 50%;}
.fns-day.range-end{background:var(--fns-blue);color:#fff;border-radius:0 50% 50% 0;}
.fns-day.preview{background:#F1F5F9;box-shadow:inset 0 0 0 1px #BFDBFE;}
.fns-day-price{font-size:10px;line-height:1;color:var(--fns-text-sec);}
.fns-day.selected .fns-day-price,.fns-day.range-start .fns-day-price,.fns-day.range-end .fns-day-price{color:#fff;}
.fns-cal-presets{display:flex;gap:8px;margin:10px 0;padding:10px 0;border-top:1px solid var(--fns-border);border-bottom:1px solid var(--fns-border);flex-wrap:wrap;}
.fns-preset{border:1px solid var(--fns-border);background:#fff;border-radius:9999px;padding:6px 14px;font-size:12px;font-weight:600;color:var(--fns-text);cursor:pointer;font-family:'Inter',Roboto,sans-serif;transition:all .15s;}
.fns-preset:hover{background:#F9FAFB;border-color:#D1D5DB;}
.fns-preset.active{background:#EFF6FF;border-color:#BFDBFE;color:var(--fns-blue-active);}
.fns-cal-footer{display:flex;align-items:center;justify-content:space-between;padding-top:12px;margin-top:12px;border-top:1px solid var(--fns-border);}
.fns-cal-summary{font-size:13px;color:var(--fns-text);font-family:'Inter',Roboto,sans-serif;}
/* Guests */
.fns-g-row{display:flex;align-items:center;justify-content:space-between;padding:12px 0;border-bottom:1px solid #F3F4F6;}
.fns-g-row:last-of-type{border:none;}
.fns-g-label{font-size:14px;font-weight:600;color:var(--fns-text);font-family:'Inter','Google Sans',Roboto,sans-serif;}
.fns-g-sub{font-size:12px;color:var(--fns-text-sec);}
.fns-stepper{display:flex;align-items:center;gap:12px;}
.fns-step-btn{width:32px;height:32px;border-radius:50%;border:1.5px solid var(--fns-border);background:#fff;color:var(--fns-text-sec);display:flex;align-items:center;justify-content:center;cursor:pointer;transition:all .15s;}
.fns-step-btn:hover:not(:disabled){border-color:var(--fns-blue);color:var(--fns-blue);background:#EFF6FF;}
.fns-step-btn:disabled{opacity:.35;cursor:not-allowed;}
.fns-step-btn:focus-visible{outline:2px solid var(--fns-blue);outline-offset:1px;}
.fns-step-val{font-size:15px;font-weight:600;color:var(--fns-text);min-width:20px;text-align:center;font-family:'Inter',Roboto,sans-serif;}
.fns-apply-bar{margin-top:14px;display:flex;justify-content:space-between;align-items:center;}
.fns-btn-reset{border:none;background:transparent;color:var(--fns-text-sec);font-size:13px;font-weight:600;cursor:pointer;padding:8px 12px;border-radius:9999px;}
.fns-btn-reset:hover{background:#F9FAFB;}
.fns-btn-apply{background:var(--fns-blue);color:#fff;border:none;border-radius:9999px;padding:9px 20px;font-size:14px;font-weight:600;cursor:pointer;}
.fns-btn-apply:hover{background:#0353e9;}
/* ── Mobile: enterprise-grade chip + bottom sheet (parity with desktop) ── */
.fns-mobile-chip{display:none;position:relative;background:#fff;border:1px solid var(--fns-border);border-radius:9999px;box-shadow:var(--fns-pill-shadow);align-items:center;gap:10px;cursor:pointer;transition:box-shadow .18s, transform .12s;width:100%;}
.fns-mobile-chip:hover{box-shadow:var(--fns-pill-shadow-hover);}
.fns-mobile-chip:active{transform:scale(.98);}
.fns-mobile-chip-icon{width:36px;height:36px;border-radius:50%;background:var(--fns-blue);color:#fff;display:flex;align-items:center;justify-content:center;font-size:15px;flex-shrink:0;box-shadow:0 1px 3px rgba(0,0,0,.12);}
.fns-mobile-chip-text{font-size:14px;font-weight:500;color:var(--fns-text);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;font-family:'Inter','Google Sans',Roboto,sans-serif;flex:1;text-align:left;line-height:1.2;}
.fns-mobile-chip-chevron{width:32px;height:32px;border-radius:50%;background:#F9FAFB;color:var(--fns-text-sec);display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:12px;}
.fns-sheet-backdrop{display:none;position:fixed;inset:0;background:rgba(17,24,39,.38);backdrop-filter:blur(2px);-webkit-backdrop-filter:blur(2px);z-index:2999;opacity:0;transition:opacity .22s;}
.fns-sheet-backdrop.open{display:block;opacity:1;}
@media(max-width:991px){.fns-sheet-backdrop{display:none !important;} .fns-sheet-header{padding-top:calc(10px + env(safe-area-inset-top, 0px));}}
.fns-mobile-sheet{position:fixed;inset:0;top:0;left:0;right:0;bottom:0;z-index:3000;background:#fff;display:flex;flex-direction:column;overscroll-behavior:contain;border-radius:0;box-shadow:none;width:100%;height:100vh;height:100dvh;max-height:none;transform:translateY(100%);transition:transform .34s cubic-bezier(.32,.72,0,1);pointer-events:none;visibility:hidden;}
@media (min-width:768px) and (max-width:991px){.fns-mobile-sheet{left:0;right:0;width:100%;transform:translateY(100%);border-radius:0;bottom:0;max-height:none;height:100vh;height:100dvh} .fns-mobile-sheet.open{transform:translateY(0)} .fns-sheet-backdrop{display:none}}
@media (max-width:767px) and (orientation:landscape){.fns-sheet-step{padding:10px 12px}}
.fns-mobile-sheet.open{transform:translateY(0);pointer-events:auto;visibility:visible;}
.fns-sheet-drag{width:100%;display:flex;justify-content:center;padding:10px 0 6px;cursor:grab;touch-action:none;flex-shrink:0;}
.fns-sheet-drag span{width:38px;height:4px;background:#E5E7EB;border-radius:9999px;display:block;}
.fns-sheet-header{position:sticky;top:0;background:#fff;padding:2px 16px 12px;display:flex;align-items:center;justify-content:space-between;gap:12px;z-index:2;flex-shrink:0;}
.fns-sheet-header-title{font-family:'Inter','Google Sans',Roboto,sans-serif;font-weight:700;color:var(--fns-text);font-size:17px;letter-spacing:-.01em;}
.fns-sheet-back{width:44px;height:44px;border-radius:50%;border:1px solid var(--fns-border);background:#fff;color:var(--fns-text);display:flex;align-items:center;justify-content:center;cursor:pointer;flex-shrink:0;transition:background .15s;}
.fns-sheet-back:hover{background:#F9FAFB;}
.fns-sheet-back:focus-visible{outline:2px solid var(--fns-blue-active);outline-offset:2px;}
.fns-sheet-tabs{display:flex;gap:6px;padding:0 16px 0;border-bottom:1px solid var(--fns-border);background:#fff;flex-shrink:0;}
.fns-sheet-tab{flex:1;padding:13px 6px 12px;font-size:13.5px;font-weight:600;color:var(--fns-text-sec);background:transparent;border:none;border-bottom:2.5px solid transparent;cursor:pointer;font-family:'Inter','Google Sans',Roboto,sans-serif;position:relative;top:1px;transition:all .15s;}
.fns-sheet-tab.active{color:var(--fns-blue-active);border-bottom-color:var(--fns-blue-active);font-weight:700;}
.fns-sheet-step{flex:1;overflow-y:auto;overflow-x:hidden;padding:14px 16px 16px;background:#F9FAFB;overscroll-behavior:contain;-webkit-overflow-scrolling:touch;}
.fns-sheet-accordion{border:1px solid var(--fns-border);border-radius:16px;overflow:hidden;margin-bottom:12px;background:#fff;box-shadow:0 1px 3px rgba(0,0,0,.04);}
.fns-acc-head{width:100%;display:flex;align-items:center;justify-content:space-between;padding:16px 16px;background:#fff;border:none;cursor:pointer;text-align:left;min-height:56px;transition:background .15s;}
.fns-acc-head.active{background:#F9FAFB;}
.fns-acc-head:focus-visible{outline:2px solid var(--fns-blue-active);outline-offset:-2px;}
.fns-acc-title{font-size:15px;font-weight:700;color:var(--fns-text);font-family:'Inter','Google Sans',Roboto,sans-serif;}
.fns-acc-value{font-size:13px;color:var(--fns-text-sec);font-weight:500;max-width:55%;text-align:right;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.fns-acc-chevron{margin-left:8px;color:var(--fns-text-sec);font-size:11px;transition:transform .18s;flex-shrink:0;}
.fns-acc-head.active .fns-acc-chevron{transform:rotate(180deg);}
.fns-acc-body{display:none;padding:14px 16px 16px;background:#fff;border-top:1px solid #F3F4F6;}
.fns-acc-body.open{display:block;animation:fnsAccIn .18s ease;}
@keyframes fnsAccIn{from{opacity:0;transform:translateY(-4px)}to{opacity:1;transform:translateY(0)}}
.fns-m-input-wrap{display:flex;align-items:center;gap:10px;border:1.5px solid var(--fns-border);border-radius:14px;padding:0 12px;background:#fff;transition:border-color .15s, box-shadow .15s;min-height:52px;}
.fns-m-input-wrap:focus-within{border-color:var(--fns-blue-active);box-shadow:0 0 0 3px rgba(37,99,235,.12);}
.fns-m-input{flex:1;border:none;outline:none;font-size:16px;font-family:'Inter',Roboto,sans-serif;color:var(--fns-text);background:transparent;padding:14px 0;line-height:1.2;min-width:0}
.fns-seg-input{font-size:clamp(13px,1.8vw,14px)}
.fns-mobile-chip-text{font-size:clamp(12px,3.2vw,14px)}
.fns-m-input::placeholder{color:#9CA3AF;}
.fns-m-clear{width:36px;height:36px;border-radius:50%;border:none;background:#F3F4F6;color:var(--fns-text-sec);display:flex;align-items:center;justify-content:center;flex-shrink:0;cursor:pointer;}
.fns-m-clear:hover{background:#E5E7EB;}
.fns-dd-item.mob{padding:12px 14px;min-height:56px;border-radius:12px;margin-bottom:4px;}
.fns-dd-item.mob:hover{background:#F9FAFB;}
.fns-m-cal-box{flex:1;border:1.5px solid #E5E7EB;border-radius:14px;padding:10px 8px;display:flex;align-items:center;justify-content:space-between;background:#fff;min-height:44px;transition:all .15s;}
.fns-m-cal-box.active{border-color:var(--fns-blue-active);background:#EFF6FF;box-shadow:0 0 0 3px rgba(37,99,235,.08);}
.fns-m-cal-box button{width:44px;height:44px;border-radius:50%;border:none;background:#F9FAFB;color:var(--fns-text-sec);display:flex;align-items:center;justify-content:center;cursor:pointer;flex-shrink:0;transition:background .15s;}
.fns-m-cal-box button:hover{background:#E5E7EB;}
.fns-m-cal-box button:focus-visible{outline:2px solid var(--fns-blue-active);outline-offset:1px;}
.fns-m-weekdays{display:grid;grid-template-columns:repeat(7,1fr);text-align:center;padding:8px 0 6px;font-size:12px;color:var(--fns-text-sec);font-weight:600;position:sticky;top:0;background:#F9FAFB;z-index:1;}
.fns-m-month{padding:12px 0 8px;border-bottom:1px solid #E5E7EB;}
.fns-m-month-title{text-align:center;font-size:15px;font-weight:700;color:var(--fns-text);margin:6px 0 10px;font-family:'Inter','Google Sans',Roboto,sans-serif;}
.fns-m-days{display:grid;grid-template-columns:repeat(7,1fr);gap:4px;}
.fns-m-day{aspect-ratio:1;border:none;background:#fff;border-radius:50%;font-size:14px;font-weight:500;color:var(--fns-text);display:flex;align-items:center;justify-content:center;min-height:44px;min-width:44px;cursor:pointer;transition:background .12s, color .12s;font-family:'Inter','Google Sans',Roboto,sans-serif;}
.fns-m-day:hover:not(:disabled):not(.past){background:#F3F4F6;}
.fns-m-day.past{color:#D1D5DB;cursor:not-allowed;background:transparent;}
.fns-m-day.selected{background:var(--fns-blue);color:#fff;font-weight:700;box-shadow:0 2px 8px rgba(26,115,232,.3);}
.fns-m-day.range-start{background:var(--fns-blue);color:#fff;font-weight:700;}
.fns-m-day.range-end{background:var(--fns-blue);color:#fff;font-weight:700;}
.fns-m-day.in-range{background:#EFF6FF;color:var(--fns-blue-active);border-radius:8px;font-weight:600;}
.fns-g-row.mob{padding:18px 0;min-height:68px;}
.fns-step-btn.mob{width:44px;height:44px;border-radius:12px;border:1.5px solid var(--fns-border);font-size:18px;font-weight:600;}
.fns-step-btn.mob:hover:not(:disabled){background:#EFF6FF;border-color:var(--fns-blue-active);color:var(--fns-blue-active);}
.fns-sheet-footer{position:sticky;bottom:0;background:#fff;border-top:1px solid var(--fns-border);padding:12px 16px calc(12px + env(safe-area-inset-bottom));display:flex;align-items:center;justify-content:space-between;gap:12px;flex-shrink:0;}
.fns-sheet-footer .fns-btn-apply{flex:1;min-height:48px;font-size:15px;font-weight:700;box-shadow:0 4px 12px rgba(37,99,235,.2);}
.fns-sheet-footer .fns-btn-apply:active{transform:scale(.98);}
.fns-sheet-footer .fns-btn-reset{min-height:48px;padding:0 18px;font-size:14px;}
/* ── Mobile Google-Hotels stacked search (screenshot parity) ── */
.fns-m-google{display:none;}
/* ── Responsive ── */
@media(max-width:991px){
  /* UX: search chip scrolls away on mobile; only filter chips stay sticky under fixed header */
  .fns-search-wrap{position:static !important;top:auto !important;padding:0;background:#fff;border-bottom:1px solid #E5E7EB;z-index:auto;}
  .fns-search-wrap .container-fluid:first-child{padding-left:12px !important;padding-right:12px !important;}
  .fns-pill{display:none !important;}
  .fns-divider{display:none !important;}
  .fns-pop{position:fixed !important;inset:auto 12px 12px 12px !important;top:auto !important;left:12px !important;right:12px !important;transform:none !important;width:auto !important;max-width:none !important;max-height:72vh;overflow-y:auto;}
  .fns-pop-cal{left:12px !important;right:12px !important;transform:none !important;width:auto !important;}
  .fns-pop-cal.open{transform:none !important;}
  .fns-mobile-chip{display:none !important;}
  .fns-mobile-chip.hidden{display:none !important;}
  .fns-pill-mobile-hidden{display:none !important;}
  /* Google-style stacked search */
  .fns-m-google{display:block !important;padding:12px 4px 10px;background:#fff;}
  .fns-g-searchbox{display:flex;align-items:center;gap:12px;width:100%;height:56px;background:#fff;border:1px solid #dadce0;border-radius:12px;padding:0 16px;cursor:text;text-align:left;transition:border-color .15s, box-shadow .15s;-webkit-tap-highlight-color:transparent;}
  .fns-g-searchbox:active{border-color:#1a73e8;}
  .fns-g-searchbox .fns-g-search-icon{color:#1a73e8;font-size:20px;flex-shrink:0;width:24px;text-align:center;}
  .fns-g-searchbox .fns-g-search-text{flex:1;min-width:0;font-family:Roboto,'Google Sans',Arial,sans-serif;font-size:16px;font-weight:400;color:#5f6368;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;line-height:20px;}
  .fns-g-searchbox .fns-g-search-text.has-value{color:#202124;}
  .fns-g-row2{display:flex;gap:8px;margin-top:8px;}
  .fns-g-dates{flex:1;display:flex;align-items:center;background:#fff;border:2px solid #1a73e8;border-radius:12px;height:52px;padding:0 6px 0 12px;cursor:pointer;min-width:0;-webkit-tap-highlight-color:transparent;}
  .fns-g-dates:active{background:#f8faff;}
  .fns-g-dates .fns-g-cal-icon{color:#1a73e8;font-size:19px;flex-shrink:0;margin-right:8px;}
  .fns-g-date{font-family:Roboto,'Google Sans',Arial,sans-serif;font-size:15px;font-weight:500;color:#1a73e8;white-space:nowrap;line-height:20px;}
  .fns-g-date-co{flex:1;text-align:center;}
  .fns-g-vdiv{width:1px;height:24px;background:#dadce0;margin:0 10px;flex-shrink:0;}
  .fns-g-guests{flex:0 0 96px;display:flex;align-items:center;justify-content:center;gap:8px;background:#fff;border:1px solid #dadce0;border-radius:12px;height:52px;cursor:pointer;-webkit-tap-highlight-color:transparent;}
  .fns-g-guests:active{background:#f8f9fa;}
  .fns-g-guests .fns-g-person-icon{color:#1a73e8;font-size:19px;}
  .fns-g-guests .fns-g-guest-count{font-family:Roboto,'Google Sans',Arial,sans-serif;font-size:16px;font-weight:400;color:#202124;line-height:20px;}
  /* chips wrap top is managed by gh-filter-chips.php — do NOT override here */
  /* reduce desktop-only spacing */
  .gh-left-fixed .fns-search-wrap{padding:0 !important;}
}
@media(min-width:992px){
  .fns-mobile-chip{display:none !important;}
  .fns-m-google{display:none !important;}
  .fns-mobile-sheet,.fns-sheet-backdrop{display:none !important;}
  .fns-search-wrap{padding:0;}
}
@media(max-width:380px){
  .fns-mobile-chip-text{font-size:13px;}
  .fns-sheet-step{padding:12px 12px 16px;}
  .fns-g-date{font-size:13.5px;}
  .fns-g-guests{flex-basis:84px;}
}
/* a11y helper */
.sr-only{position:absolute;width:1px;height:1px;padding:0;margin:-1px;overflow:hidden;clip:rect(0,0,0,0);white-space:nowrap;border:0;}
</style>
