<?php
$totalCount = $totalCount ?? count($properties ?? []);
$apiCount = $totalCount;
/**
 * FastNet Stays — Enterprise Search Bar (Spec Authoritative)
 * - Desktop >=992px: Unified pill with vertical dividers, #E5E7EB border, 0 4px 20px rgba(0,0,0,.08) -> hover 0 8px 30px rgba(0,0,0,.12)
 * - Palette: #1A73E8 primary / #2563EB active border / #FFFFFF bg / #1F2937 text / #6B7280 secondary
 * - Typography: Inter / Google Sans 400/500/600
 * - Segments: Where to? | Check-in | Check-out (badge nights) | Guests & Rooms | Search CTA
 * - Mobile <992px: floating chip + bottom-sheet stepped modal
 * - a11y: Tab/Shift+Tab, Enter/Space open, Escape close, ArrowUp/Down suggestions, aria
 * - Defensive: 1-night min, past dates disabled, empty city -> All Tanzanian Destinations, skeleton trigger
 */
$today     = date('Y-m-d');
$checkIn   = $queryParams['checkin'] ?? $queryParams['checkIn'] ?? date('Y-m-d', strtotime('+7 days'));
$checkOut  = $queryParams['checkout'] ?? $queryParams['checkOut'] ?? date('Y-m-d', strtotime($checkIn . ' +1 day'));
$adults    = (int)($queryParams['adults']   ?? 2);
$children  = (int)($queryParams['children'] ?? 0);
$rooms     = (int)($queryParams['rooms']    ?? 1);
$destVal   = $queryParams['city'] ?? $queryParams['destination'] ?? 'Dar es Salaam';
if ($destVal === '' || $destVal === null) $destVal = '';

$adults = max(1, min(10, $adults));
$children = max(0, min(6, $children));
$rooms = max(1, min(5, $rooms));
if (strtotime($checkIn)  < strtotime($today)) $checkIn  = date('Y-m-d', strtotime('+7 days'));
if (strtotime($checkOut) <= strtotime($checkIn)) $checkOut = date('Y-m-d', strtotime($checkIn . ' +1 day'));
$nights = max(1, (int)round((strtotime($checkOut)-strtotime($checkIn))/86400));
$guestText = $adults . ' adult' . ($adults!==1?'s':'') . ' · ' . $rooms . ' room' . ($rooms!==1?'s':'');
if ($children>0) $guestText = $adults . ' adults · ' . $children . ' child' . ($children!==1?'ren':'') . ' · ' . $rooms . ' room' . ($rooms!==1?'s':'');
$fmtCI = date('D, M j', strtotime($checkIn));
$fmtCO = date('D, M j', strtotime($checkOut));
$mSummary = h($destVal ?: 'All Tanzanian Destinations') . ' • ' . date('M j', strtotime($checkIn)) . '–' . date('j', strtotime($checkOut)) . ' • ' . $adults . ' guest' . ($adults!==1?'s':'');
?>
<style>
/* ── SPEC DESIGN TOKENS ── */
:root{--fns-blue:#1A73E8;--fns-blue-active:#2563EB;--fns-border:#E5E7EB;--fns-text:#1F2937;--fns-text-sec:#6B7280;--fns-bg:#FFFFFF;--fns-pill-shadow:0 4px 20px rgba(0,0,0,.08);--fns-pill-shadow-hover:0 8px 30px rgba(0,0,0,.12);}
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
.fns-cta{flex:0 0 auto;align-self:center;display:inline-flex;align-items:center;gap:8px;background:var(--fns-blue);color:#fff;border:none;border-radius:9999px;padding:0 20px;height:44px;font-family:'Inter','Google Sans',Roboto,sans-serif;font-size:14px;font-weight:600;cursor:pointer;transition:transform .12s ease, filter .15s, background .15s;white-space:nowrap;box-shadow:0 1px 2px rgba(0,0,0,.08);}
.fns-cta:hover{filter:brightness(1.06);background:#1765CC;}
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
.fns-btn-apply:hover{background:#1765CC;}
/* ── Mobile: enterprise-grade chip + bottom sheet (parity with desktop) ── */
.fns-mobile-chip{display:none;position:relative;background:#fff;border:1px solid var(--fns-border);border-radius:9999px;box-shadow:var(--fns-pill-shadow);align-items:center;gap:10px;cursor:pointer;transition:box-shadow .18s, transform .12s;width:100%;}
.fns-mobile-chip:hover{box-shadow:var(--fns-pill-shadow-hover);}
.fns-mobile-chip:active{transform:scale(.98);}
.fns-mobile-chip-icon{width:36px;height:36px;border-radius:50%;background:var(--fns-blue);color:#fff;display:flex;align-items:center;justify-content:center;font-size:15px;flex-shrink:0;box-shadow:0 1px 3px rgba(0,0,0,.12);}
.fns-mobile-chip-text{font-size:14px;font-weight:500;color:var(--fns-text);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;font-family:'Inter','Google Sans',Roboto,sans-serif;flex:1;text-align:left;line-height:1.2;}
.fns-mobile-chip-chevron{width:32px;height:32px;border-radius:50%;background:#F9FAFB;color:var(--fns-text-sec);display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:12px;}
.fns-sheet-backdrop{display:none;position:fixed;inset:0;background:rgba(17,24,39,.38);backdrop-filter:blur(2px);-webkit-backdrop-filter:blur(2px);z-index:2999;opacity:0;transition:opacity .22s;}
.fns-sheet-backdrop.open{display:block;opacity:1;}
.fns-mobile-sheet{position:fixed;left:0;right:0;bottom:0;z-index:3000;background:#fff;display:flex;flex-direction:column;overscroll-behavior:contain;border-radius:20px 20px 0 0;box-shadow:0 -12px 40px rgba(0,0,0,.18);max-height:94vh;max-height:94dvh;transform:translateY(100%);transition:transform .34s cubic-bezier(.32,.72,0,1);pointer-events:none;visibility:hidden;}
@media (min-width:768px) and (max-width:991px){.fns-mobile-sheet{left:50%;right:auto;width:min(640px,92vw);transform:translateX(-50%) translateY(100%);border-radius:20px;bottom:16px;max-height:86vh;max-height:86dvh} .fns-mobile-sheet.open{transform:translateX(-50%) translateY(0)} .fns-sheet-backdrop{backdrop-filter:blur(4px)}}
@media (max-width:767px) and (orientation:landscape){.fns-mobile-sheet{max-height:92dvh} .fns-sheet-step{padding:10px 12px}}
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
/* ── Responsive ── */
@media(max-width:991px){
  .fns-search-wrap{top:56px;padding:0;background:#fff;border-bottom:1px solid #E5E7EB;position:sticky;z-index:902;}
  .fns-search-wrap .container-fluid:first-child{padding-left:16px !important;padding-right:16px !important;}
  .fns-pill{display:none !important;}
  .fns-divider{display:none !important;}
  .fns-pop{position:fixed !important;inset:auto 12px 12px 12px !important;top:auto !important;left:12px !important;right:12px !important;transform:none !important;width:auto !important;max-width:none !important;max-height:72vh;overflow-y:auto;}
  .fns-pop-cal{left:12px !important;right:12px !important;transform:none !important;width:auto !important;}
  .fns-pop-cal.open{transform:none !important;}
  .fns-mobile-chip{display:flex !important;height:56px;padding:8px 16px;box-shadow:0 4px 20px rgba(0,0,0,.08);border-color:#E5E7EB;background:#fff;}
  .fns-mobile-chip.hidden{display:none !important;}
  .fns-pill-mobile-hidden{display:none !important;}
  /* filter chips stay below chip */
  #fns_chips_wrap{top:68px !important;}
  /* reduce desktop-only spacing */
  .gh-left-fixed .fns-search-wrap{padding:0 !important;}
}
@media(min-width:992px){
  .fns-mobile-chip{display:none !important;}
  .fns-mobile-sheet,.fns-sheet-backdrop{display:none !important;}
  .fns-search-wrap{padding:0;}
}
@media(max-width:380px){
  .fns-mobile-chip-text{font-size:13px;}
  .fns-sheet-step{padding:12px 12px 16px;}
}
/* a11y helper */
.sr-only{position:absolute;width:1px;height:1px;padding:0;margin:-1px;overflow:hidden;clip:rect(0,0,0,0);white-space:nowrap;border:0;}
</style>

<div class="fns-search-wrap" id="fns_search_wrap">
  <div class="container-fluid px-1 px-lg-2" style="max-width:100%;margin:0 auto;">
    <form action="<?= $this->Url->build('/') ?>" method="GET" autocomplete="off" id="gh_search_form" role="search" aria-label="Find stays" novalidate>
      <input type="hidden" name="lat" id="gh_lat" value="<?= h($queryParams['lat'] ?? '') ?>">
      <input type="hidden" name="lng" id="gh_lng" value="<?= h($queryParams['lng'] ?? '') ?>">
      <input type="hidden" name="checkin" id="gh_ci" value="<?= h($checkIn) ?>">
      <input type="hidden" name="checkout" id="gh_co" value="<?= h($checkOut) ?>">
      <!-- legacy aliases for backward compat -->
      <input type="hidden" name="checkIn" id="gh_ci_legacy" value="<?= h($checkIn) ?>">
      <input type="hidden" name="checkOut" id="gh_co_legacy" value="<?= h($checkOut) ?>">
      <input type="hidden" name="city" id="gh_city" value="<?= h($destVal) ?>">
      <input type="hidden" name="adults" id="gh_ad" value="<?= $adults ?>">
      <input type="hidden" name="children" id="gh_ch" value="<?= $children ?>">
      <input type="hidden" name="rooms" id="gh_rm" value="<?= $rooms ?>">

      <div class="fns-pill" id="fns_pill" role="group" aria-label="Search stays">
        <!-- Where -->
        <div class="fns-seg" id="fns_seg_where" role="combobox" aria-expanded="false" aria-haspopup="dialog" aria-controls="fns_pop_dest" tabindex="0" aria-label="Destination">
          <span class="fns-seg-label">Where to?</span>
          <input type="text" id="gh_dest" name="destination" class="fns-seg-input" value="<?= h($destVal) ?>" placeholder="City, landmark, or hotel name" autocomplete="off" aria-autocomplete="list" aria-controls="fns_dd_list" aria-label="Destination input" onfocus="fnsDestFocus()" oninput="fnsDestInput(this.value)" onkeydown="fnsDestKey(event)">
          <button type="button" class="fns-clear" id="fns_clear" onclick="fnsDestClear()" aria-label="Clear destination" tabindex="-1"><i class="fa-solid fa-xmark" style="font-size:12px;"></i></button>
          <!-- Destination popover -->
          <div class="fns-pop fns-pop-dest" id="fns_pop_dest" role="dialog" aria-label="Destination suggestions" onclick="event.stopPropagation()">
            <div id="fns_dd_popular"></div>
            <div id="fns_dd_recent" class="fns-recent"></div>
          </div>
        </div>
        <div class="fns-divider" aria-hidden="true"></div>
        <!-- Check-in -->
        <div class="fns-seg" id="fns_seg_ci" role="button" tabindex="0" aria-label="Check-in date" aria-haspopup="dialog" aria-expanded="false" aria-controls="fns_pop_cal" onclick="fnsOpenCal('ci')" onkeydown="if(event.key==='Enter'||event.key===' ') {event.preventDefault(); fnsOpenCal('ci');}">
          <span class="fns-seg-label">Check-in</span>
          <span class="fns-seg-value" id="fns_ci_lbl"><?= h($fmtCI) ?></span>
          <span class="sr-only" id="fns_ci_placeholder">Add date</span>
        </div>
        <div class="fns-divider" aria-hidden="true"></div>
        <!-- Check-out -->
        <div class="fns-seg" id="fns_seg_co" role="button" tabindex="0" aria-label="Check-out date" aria-haspopup="dialog" aria-expanded="false" aria-controls="fns_pop_cal" onclick="fnsOpenCal('co')" onkeydown="if(event.key==='Enter'||event.key===' ') {event.preventDefault(); fnsOpenCal('co');}">
          <span class="fns-seg-label">Check-out</span>
          <span style="display:flex;align-items:center;gap:0;flex-wrap:nowrap;">
            <span class="fns-seg-value" id="fns_co_lbl"><?= h($fmtCO) ?></span>
            <span class="fns-nights-badge" id="fns_nights_badge"><?= $nights ?> night<?= $nights!==1?'s':'' ?></span>
          </span>
        </div>
        <div class="fns-divider" aria-hidden="true"></div>
        <!-- Guests -->
        <div class="fns-seg" id="fns_seg_guests" role="button" tabindex="0" aria-haspopup="dialog" aria-expanded="false" aria-controls="fns_pop_guests" onclick="fnsGuestsToggle(event)" onkeydown="if(event.key==='Enter'||event.key===' ') {event.preventDefault(); fnsGuestsToggle(event);}">
          <span class="fns-seg-label">Guests &amp; Rooms</span>
          <span class="fns-seg-value" id="fns_guests_lbl"><?= h($guestText) ?></span>
        </div>
        <!-- CTA -->
        <div style="display:flex;align-items:center;padding:6px 6px 6px 4px;">
          <button type="submit" class="fns-cta" id="fns_search_btn" aria-label="Search stays" aria-busy="false">
            <span class="fns-cta-icon"><i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i></span>
            <span class="fns-cta-text">Search</span>
            <span class="fns-cta-spinner" aria-hidden="true"></span>
          </button>
        </div>

        <!-- Calendar popover (anchored to pill) -->
        <div class="fns-pop fns-pop-cal" id="fns_pop_cal" role="dialog" aria-modal="false" aria-label="Choose dates" onclick="event.stopPropagation()">
          <div class="fns-cal-head">
            <button type="button" class="fns-cal-nav" onclick="fnsNavCal(-1)" aria-label="Previous month"><i class="fa-solid fa-chevron-left" style="font-size:12px;"></i></button>
            <div class="fns-cal-titles"><span id="fns_cal_t1"></span><span id="fns_cal_t2"></span></div>
            <button type="button" class="fns-cal-nav" onclick="fnsNavCal(1)" aria-label="Next month"><i class="fa-solid fa-chevron-right" style="font-size:12px;"></i></button>
          </div>
          <div class="fns-cal-presets" id="fns_cal_presets" role="group" aria-label="Date presets">
            <button type="button" class="fns-preset active" data-preset="custom" onclick="fnsPreset('custom')">Custom</button>
            <button type="button" class="fns-preset" data-preset="weekend" onclick="fnsPreset('weekend')">Weekend</button>
            <button type="button" class="fns-preset" data-preset="week" onclick="fnsPreset('week')">1 Week</button>
          </div>
          <div class="fns-cal-grid-wrap">
            <div><div class="fns-cal-weekdays"><span>Su</span><span>Mo</span><span>Tu</span><span>We</span><span>Th</span><span>Fr</span><span>Sa</span></div><div class="fns-cal-days" id="fns_cal_m1"></div></div>
            <div><div class="fns-cal-weekdays"><span>Su</span><span>Mo</span><span>Tu</span><span>We</span><span>Th</span><span>Fr</span><span>Sa</span></div><div class="fns-cal-days" id="fns_cal_m2"></div></div>
          </div>
          <div class="fns-cal-footer">
            <span class="fns-cal-summary" id="fns_cal_summary"><strong><?= $nights ?> night<?= $nights!==1?'s':'' ?></strong> · <?= h($fmtCI) ?> – <?= h($fmtCO) ?></span>
            <div style="display:flex;gap:8px;">
              <button type="button" class="fns-btn-reset" onclick="fnsCalReset()">Reset</button>
              <button type="button" class="fns-btn-apply" onclick="fnsCalDone()">Done</button>
            </div>
          </div>
        </div>

        <!-- Guests popover -->
        <div class="fns-pop fns-pop-guests" id="fns_pop_guests" role="dialog" aria-label="Guests and rooms" onclick="event.stopPropagation()">
          <div class="fns-g-row">
            <div><div class="fns-g-label">Adults</div><div class="fns-g-sub">Ages 13+</div></div>
            <div class="fns-stepper">
              <button type="button" class="fns-step-btn" id="fns_ad_m" aria-label="Decrease adults" onclick="fnsG('adults',-1)">−</button>
              <span class="fns-step-val" id="fns_ad_v" aria-live="polite"><?= $adults ?></span>
              <button type="button" class="fns-step-btn" id="fns_ad_p" aria-label="Increase adults" onclick="fnsG('adults',1)">+</button>
            </div>
          </div>
          <div class="fns-g-row">
            <div><div class="fns-g-label">Children</div><div class="fns-g-sub">Ages 0–12</div></div>
            <div class="fns-stepper">
              <button type="button" class="fns-step-btn" id="fns_ch_m" aria-label="Decrease children" onclick="fnsG('children',-1)">−</button>
              <span class="fns-step-val" id="fns_ch_v" aria-live="polite"><?= $children ?></span>
              <button type="button" class="fns-step-btn" id="fns_ch_p" aria-label="Increase children" onclick="fnsG('children',1)">+</button>
            </div>
          </div>
          <div class="fns-g-row">
            <div><div class="fns-g-label">Rooms</div><div class="fns-g-sub">Max 5</div></div>
            <div class="fns-stepper">
              <button type="button" class="fns-step-btn" id="fns_rm_m" aria-label="Decrease rooms" onclick="fnsG('rooms',-1)">−</button>
              <span class="fns-step-val" id="fns_rm_v" aria-live="polite"><?= $rooms ?></span>
              <button type="button" class="fns-step-btn" id="fns_rm_p" aria-label="Increase rooms" onclick="fnsG('rooms',1)">+</button>
            </div>
          </div>
          <div class="fns-apply-bar">
            <button type="button" class="fns-btn-reset" onclick="fnsGuestsClose()">Close</button>
            <button type="button" class="fns-btn-apply" onclick="fnsGuestsApply()">Apply</button>
          </div>
        </div>
      </div>
    </form>
    <!-- Mobile: sticky chip (visible only <992px, replaces desktop pill) -->
    <div class="container-fluid px-1 px-lg-2 d-lg-none" style="max-width:100%;margin:6px auto 0;padding-left:16px !important;padding-right:16px !important;">
      <div class="fns-mobile-chip" id="fns_mobile_chip" role="button" tabindex="0" aria-label="Open search, current: <?= h($mSummary) ?>" onclick="fnsOpenMobile()" onkeydown="if(event.key==='Enter'||event.key===' ') {event.preventDefault(); fnsOpenMobile();}">
        <span class="fns-mobile-chip-icon" aria-hidden="true"><i class="fa-solid fa-magnifying-glass"></i></span>
        <span class="fns-mobile-chip-text" id="fns_chip_text"><?= h($mSummary) ?></span>
        <span class="fns-mobile-chip-chevron" aria-hidden="true"><i class="fa-solid fa-sliders"></i></span>
      </div>
    </div>
  </div>
</div>
<!-- shared backdrop -->
<div class="fns-sheet-backdrop" id="fns_sheet_backdrop" aria-hidden="true" onclick="fnsCloseMobile()"></div>
<!-- Mobile: bottom sheet stepped modal -->
<div class="fns-mobile-sheet" id="fns_mobile_sheet" role="dialog" aria-modal="true" aria-label="Search stays">
  <div class="fns-sheet-drag" aria-hidden="true"><span></span></div>
  <div class="fns-sheet-header">
    <div class="fns-sheet-header-title">Search stays</div>
    <button type="button" class="fns-sheet-back" onclick="fnsCloseMobile()" aria-label="Close search"><i class="fa-solid fa-xmark"></i></button>
  </div>
  <div class="fns-sheet-tabs" role="tablist">
    <button type="button" class="fns-sheet-tab active" data-step="where" role="tab" aria-selected="true" onclick="fnsSheetGo('where')">Where</button>
    <button type="button" class="fns-sheet-tab" data-step="when" role="tab" onclick="fnsSheetGo('when')">When</button>
    <button type="button" class="fns-sheet-tab" data-step="who" role="tab" onclick="fnsSheetGo('who')">Who</button>
  </div>
  <div class="fns-sheet-step" id="fns_sheet_body">
    <!-- Where -->
    <div class="fns-sheet-accordion" id="fns_acc_where">
      <button type="button" class="fns-acc-head active" aria-expanded="true" onclick="fnsAccToggle('where')">
        <span class="fns-acc-title">Where to?</span>
        <span class="fns-acc-value" id="fns_m_where_val"><?= h($destVal ?: 'All Tanzanian Destinations') ?></span>
        <i class="fa-solid fa-chevron-down fns-acc-chevron" aria-hidden="true"></i>
      </button>
      <div class="fns-acc-body open" id="fns_acc_where_body">
        <div class="fns-m-input-wrap" style="margin-bottom:12px;">
          <i class="fa-solid fa-magnifying-glass" style="color:var(--fns-text-sec);font-size:15px;flex-shrink:0;" aria-hidden="true"></i>
          <input type="text" id="fns_m_input" class="fns-m-input" value="<?= h($destVal) ?>" placeholder="City, landmark, or hotel name" autocomplete="off" aria-label="Destination" oninput="fnsMDestInput(this.value)" enterkeyhint="search">
          <button type="button" class="fns-m-clear" onclick="fnsMClear()" aria-label="Clear destination"><i class="fa-solid fa-xmark" style="font-size:12px;"></i></button>
        </div>
        <div id="fns_m_list"></div>
      </div>
    </div>
    <!-- When -->
    <div class="fns-sheet-accordion" id="fns_acc_when">
      <button type="button" class="fns-acc-head" aria-expanded="false" onclick="fnsAccToggle('when')">
        <span class="fns-acc-title">When?</span>
        <span class="fns-acc-value" id="fns_m_when_val"><?= h($fmtCI) ?> – <?= h($fmtCO) ?> · <?= $nights ?>n</span>
        <i class="fa-solid fa-chevron-down fns-acc-chevron" aria-hidden="true"></i>
      </button>
      <div class="fns-acc-body" id="fns_acc_when_body">
        <div style="display:flex;gap:8px;margin-bottom:12px;">
          <div class="fns-m-cal-box" id="fns_m_ci_box"><button type="button" onclick="fnsStep('ci',-1)" aria-label="Previous check-in day"><i class="fa-solid fa-chevron-left" style="font-size:10px;"></i></button><span id="fns_m_ci_lbl" style="font-weight:700;font-family:'Inter',Roboto,sans-serif;font-size:13.5px;"><?= h($fmtCI) ?></span><button type="button" onclick="fnsStep('ci',1)" aria-label="Next check-in day"><i class="fa-solid fa-chevron-right" style="font-size:10px;"></i></button></div>
          <div class="fns-m-cal-box" id="fns_m_co_box"><span id="fns_m_co_lbl" style="font-weight:700;font-family:'Inter',Roboto,sans-serif;font-size:13.5px;"><?= h($fmtCO) ?></span><span class="fns-nights-badge" id="fns_m_nights_badge"><?= $nights ?>n</span></div>
        </div>
        <div class="fns-m-weekdays" aria-hidden="true"><span>S</span><span>M</span><span>T</span><span>W</span><span>T</span><span>F</span><span>S</span></div>
        <div id="fns_m_grid" style="max-height:52vh;overflow-y:auto;-webkit-overflow-scrolling:touch;overscroll-behavior:contain;"></div>
        <div style="display:flex;gap:8px;margin-top:12px;flex-wrap:wrap;" role="group" aria-label="Date presets">
          <button type="button" class="fns-preset" onclick="fnsPreset('weekend')">Weekend</button>
          <button type="button" class="fns-preset" onclick="fnsPreset('week')">1 Week</button>
          <button type="button" class="fns-preset" onclick="fnsPreset('custom')">Custom</button>
        </div>
      </div>
    </div>
    <!-- Who -->
    <div class="fns-sheet-accordion" id="fns_acc_who">
      <button type="button" class="fns-acc-head" aria-expanded="false" onclick="fnsAccToggle('who')">
        <span class="fns-acc-title">Who's coming?</span>
        <span class="fns-acc-value" id="fns_m_who_val"><?= h($guestText) ?></span>
        <i class="fa-solid fa-chevron-down fns-acc-chevron" aria-hidden="true"></i>
      </button>
      <div class="fns-acc-body" id="fns_acc_who_body">
        <div class="fns-g-row mob"><div><div class="fns-g-label">Adults</div><div class="fns-g-sub">Ages 13+</div></div><div class="fns-stepper"><button type="button" class="fns-step-btn mob" id="fns_m_ad_m" onclick="fnsMG('adults',-1)" aria-label="Decrease adults">−</button><span class="fns-step-val" id="fns_m_ad_v" style="min-width:28px;font-size:16px;"><?= $adults ?></span><button type="button" class="fns-step-btn mob" id="fns_m_ad_p" onclick="fnsMG('adults',1)" aria-label="Increase adults">+</button></div></div>
        <div class="fns-g-row mob"><div><div class="fns-g-label">Children</div><div class="fns-g-sub">Ages 0–12</div></div><div class="fns-stepper"><button type="button" class="fns-step-btn mob" id="fns_m_ch_m" onclick="fnsMG('children',-1)" aria-label="Decrease children">−</button><span class="fns-step-val" id="fns_m_ch_v" style="min-width:28px;font-size:16px;"><?= $children ?></span><button type="button" class="fns-step-btn mob" id="fns_m_ch_p" onclick="fnsMG('children',1)" aria-label="Increase children">+</button></div></div>
        <div class="fns-g-row mob"><div><div class="fns-g-label">Rooms</div><div class="fns-g-sub">1–5 rooms</div></div><div class="fns-stepper"><button type="button" class="fns-step-btn mob" id="fns_m_rm_m" onclick="fnsMG('rooms',-1)" aria-label="Decrease rooms">−</button><span class="fns-step-val" id="fns_m_rm_v" style="min-width:28px;font-size:16px;"><?= $rooms ?></span><button type="button" class="fns-step-btn mob" id="fns_m_rm_p" onclick="fnsMG('rooms',1)" aria-label="Increase rooms">+</button></div></div>
      </div>
    </div>
  </div>
  <div class="fns-sheet-footer">
    <button type="button" class="fns-btn-reset" onclick="fnsClearAllMobile()">Clear all</button>
    <button type="button" class="fns-btn-apply" onclick="fnsMobileSearch()">Search stays <span id="fns_m_search_count" style="opacity:.9;font-weight:600;">· <?= (int)$apiCount ?> stays</span></button>
  </div>
</div>

<script>
(function(){
'use strict';
var _ci='<?= h($checkIn) ?>', _co='<?= h($checkOut) ?>';
var _ad=<?= $adults ?>, _ch=<?= $children ?>, _rm=<?= $rooms ?>;
var _calOff=0, _picking='ci', _ddIdx=-1;

// ── Popular Tanzanian Destinations (spec) ──
var POPULAR=[
  {id:'arusha', label:'Arusha', sub:'Gateway to Serengeti & Ngorongoro', icon:'fa-mountain-sun'},
  {id:'zanzibar', label:'Zanzibar', sub:'Stone Town & Beaches', icon:'fa-umbrella-beach'},
  {id:'dar', label:'Dar es Salaam', sub:'Commercial capital', icon:'fa-city'},
  {id:'kilimanjaro', label:'Kilimanjaro', sub:'Mount Kilimanjaro region', icon:'fa-mountain'},
  {id:'serengeti', label:'Serengeti', sub:'National Park', icon:'fa-paw'},
  {id:'mwanza', label:'Mwanza', sub:'Lake Victoria', icon:'fa-water'}
];
var RECENT_KEY='fns_recent_searches';
function getRecent(){ try{return JSON.parse(localStorage.getItem(RECENT_KEY)||'[]');}catch(e){return [];} }
function pushRecent(val){
  if(!val || val==='All Tanzanian Destinations') return;
  var arr=getRecent().filter(function(v){return v.toLowerCase()!==val.toLowerCase();});
  arr.unshift(val); arr=arr.slice(0,5);
  try{localStorage.setItem(RECENT_KEY, JSON.stringify(arr));}catch(e){}
}
function renderDest(q){
  var popularEl=document.getElementById('fns_dd_popular');
  var recentEl=document.getElementById('fns_dd_recent');
  if(!popularEl) return;
  var ql=(q||'').toLowerCase().trim();
  var filtered= POPULAR.filter(function(p){ return !ql || p.label.toLowerCase().indexOf(ql)!==-1 || p.sub.toLowerCase().indexOf(ql)!==-1; });
  var html='<div class="fns-dd-section"><i class="fa-solid fa-fire" style="margin-right:6px;"></i>Popular Tanzanian Destinations</div>';
  if(filtered.length===0) html+='<div style="padding:10px 16px;color:var(--fns-text-sec);font-size:13px;">No matches — try Arusha, Zanzibar, …</div>';
  filtered.forEach(function(p,i){
    var active=i===_ddIdx?' highlight':'';
    html+='<div class="fns-dd-item'+active+'" data-value="'+p.label+'" data-idx="'+i+'" onclick="fnsPickDest(\''+p.label.replace(/'/g,"\\'")+'\')" onmouseenter="fnsHlDest('+i+')"><span class="fns-dd-icon"><i class="fa-solid '+p.icon+'"></i></span><span><div class="fns-dd-label">'+p.label+'</div><div class="fns-dd-sub">'+p.sub+'</div></span></div>';
  });
  popularEl.innerHTML=html;
  // recent
  var recent=getRecent();
  if(recent.length){
    var rh='<div class="fns-dd-section"><i class="fa-regular fa-clock" style="margin-right:6px;"></i>Recent Searches</div>';
    recent.forEach(function(r){
      if(ql && r.toLowerCase().indexOf(ql)===-1) return;
      rh+='<div class="fns-dd-item" onclick="fnsPickDest(\''+r.replace(/'/g,"\\'")+'\')"><span class="fns-dd-icon"><i class="fa-solid fa-clock-rotate-left"></i></span><span><div class="fns-dd-label">'+r+'</div><div class="fns-dd-sub">Recent</div></span></div>';
    });
    recentEl.innerHTML=rh;
  } else recentEl.innerHTML='';
  // also sync mobile list (thumb-friendly 56px rows)
  var mList=document.getElementById('fns_m_list');
  if(mList){
    if(window.innerWidth<=991 || document.getElementById('fns_mobile_sheet').classList.contains('open')){
      var mh=''; filtered.forEach(function(p){ mh+='<div class="fns-dd-item mob" role="option" tabindex="0" onclick="fnsPickDest(\''+p.label.replace(/'/g,"\\'")+'\');fnsSheetGo(\'when\');" onkeydown="if(event.key===\'Enter\') this.click()"><span class="fns-dd-icon" aria-hidden="true"><i class="fa-solid '+p.icon+'"></i></span><span><div class="fns-dd-label">'+p.label+'</div><div class="fns-dd-sub">'+p.sub+'</div></span><span style="margin-left:auto;color:#9CA3AF;"><i class="fa-solid fa-chevron-right" style="font-size:11px;"></i></span></div>'; });
      if(recent.length){
        mh+='<div class="fns-dd-section" style="margin-top:8px;">Recent</div>';
        recent.forEach(function(r){ mh+='<div class="fns-dd-item mob" role="option" tabindex="0" onclick="fnsPickDest(\''+r.replace(/'/g,"\\'")+'\');fnsSheetGo(\'when\');" onkeydown="if(event.key===\'Enter\') this.click()"><span class="fns-dd-icon" aria-hidden="true"><i class="fa-solid fa-clock-rotate-left"></i></span><span><div class="fns-dd-label">'+r+'</div><div class="fns-dd-sub">Recent search</div></span></div>'; });
      }
      if(!filtered.length && !recent.length) mh='<div style="padding:16px;color:var(--fns-text-sec);font-size:14px;text-align:center;">No matches — try Arusha, Zanzibar, Mwanza</div>';
      mList.innerHTML=mh;
    }
  }
}
window.fnsPickDest=function(val){
  document.getElementById('gh_dest').value=val;
  document.getElementById('gh_city').value=val;
  document.getElementById('fns_m_input').value=val;
  pushRecent(val);
  fnsClosePopovers();
  document.getElementById('fns_m_where_val').textContent=val;
  if(window.FastNetState) FastNetState.replaceState({city:val, destination:val});
  updateClear();
};
window.fnsHlDest=function(i){ _ddIdx=i; renderDest(document.getElementById('gh_dest').value); };

function updateClear(){
  var v=document.getElementById('gh_dest').value.trim();
  var c=document.getElementById('fns_clear');
  if(c) c.classList.toggle('show', v.length>0);
  var mWhere=document.getElementById('fns_m_where_val');
  if(mWhere) mWhere.textContent= v || 'All Tanzanian Destinations';
  var chip=document.getElementById('fns_chip_text');
  if(chip){
    var ciLbl=document.getElementById('fns_ci_lbl')?document.getElementById('fns_ci_lbl').textContent:'';
    var coLbl=document.getElementById('fns_co_lbl')?document.getElementById('fns_co_lbl').textContent:'';
    // short format
    try{
      var a=new Date(_ci+'T00:00:00'), b=new Date(_co+'T00:00:00');
      var s=a.toLocaleDateString('en-US',{month:'short',day:'numeric'});
      var e=b.toLocaleDateString('en-US',{day:'numeric'});
      chip.textContent=(v||'All Tanzanian Destinations')+' • '+s+'–'+e+' • '+_ad+' guest'+(_ad!==1?'s':'');
    }catch(e){ chip.textContent=(v||'All Tanzanian Destinations')+' • '+_ad+' guests';}
  }
}
window.fnsDestFocus=function(){
  if(window.innerWidth<=991){ fnsOpenMobile(); return; }
  document.getElementById('fns_seg_where').classList.add('active');
  document.getElementById('fns_seg_where').setAttribute('aria-expanded','true');
  document.getElementById('fns_pop_dest').classList.add('open');
  renderDest(document.getElementById('gh_dest').value);
};
window.fnsDestInput=function(v){
  updateClear();
  _ddIdx=-1;
  renderDest(v);
  document.getElementById('gh_city').value=v;
  if(window.FastNetState) FastNetState.replaceState({city:v, destination:v});
  var pop=document.getElementById('fns_pop_dest');
  if(pop && !pop.classList.contains('open')){ pop.classList.add('open'); document.getElementById('fns_seg_where').setAttribute('aria-expanded','true');}
};
window.fnsDestKey=function(e){
  var items=document.querySelectorAll('#fns_dd_popular .fns-dd-item, #fns_dd_recent .fns-dd-item');
  if(e.key==='ArrowDown'){ e.preventDefault(); _ddIdx=Math.min(_ddIdx+1, items.length-1); renderDest(e.target.value); items[_ddIdx]?.scrollIntoView({block:'nearest'});}
  else if(e.key==='ArrowUp'){ e.preventDefault(); _ddIdx=Math.max(_ddIdx-1,0); renderDest(e.target.value);}
  else if(e.key==='Enter'){ e.preventDefault(); if(_ddIdx>=0 && items[_ddIdx]){ items[_ddIdx].click(); } else { fnsClosePopovers(); document.getElementById('gh_search_form').requestSubmit(); } }
  else if(e.key==='Escape'){ fnsClosePopovers(); }
};
window.fnsDestClear=function(){
  document.getElementById('gh_dest').value='';
  document.getElementById('gh_city').value='';
  document.getElementById('fns_m_input').value='';
  updateClear(); renderDest('');
  document.getElementById('gh_dest').focus();
  if(window.FastNetState) FastNetState.replaceState({city:'', destination:''});
};

// ── Date helpers ──
function syncDate(){
  document.getElementById('gh_ci').value=_ci; document.getElementById('gh_co').value=_co;
  document.getElementById('gh_ci_legacy').value=_ci; document.getElementById('gh_co_legacy').value=_co;
  var fmt=function(s){ try{return new Date(s+'T00:00:00').toLocaleDateString('en-US',{weekday:'short',month:'short',day:'numeric'});}catch(e){return s;}};
  var ciLbl=document.getElementById('fns_ci_lbl'), coLbl=document.getElementById('fns_co_lbl');
  if(ciLbl) ciLbl.textContent=fmt(_ci);
  if(coLbl) coLbl.textContent=fmt(_co);
  var nights=Math.max(1, Math.round((new Date(_co+'T00:00:00')-new Date(_ci+'T00:00:00'))/86400000));
  var badge=document.getElementById('fns_nights_badge'); if(badge) badge.textContent=nights+' night'+(nights!==1?'s':'');
  var mCi=document.getElementById('fns_m_ci_lbl'), mCo=document.getElementById('fns_m_co_lbl');
  if(mCi) mCi.textContent=fmt(_ci); if(mCo) mCo.textContent=fmt(_co);
  var mBadge=document.getElementById('fns_m_nights_badge'); if(mBadge) mBadge.textContent=nights+'n';
  var mWhen=document.getElementById('fns_m_when_val'); if(mWhen) mWhen.textContent=fmt(_ci)+' – '+fmt(_co)+' · '+nights+'n';
  var sum=document.getElementById('fns_cal_summary');
  if(sum) sum.innerHTML='<strong>'+nights+' night'+(nights!==1?'s':'')+'</strong> · '+fmt(_ci)+' – '+fmt(_co);
  updateClear();
  // highlight active picking box
  var ciBox=document.getElementById('fns_m_ci_box'), coBox=document.getElementById('fns_m_co_box');
  if(ciBox && coBox){ ciBox.style.borderColor=_picking==='ci'?'var(--fns-blue)':'transparent'; coBox.style.borderColor=_picking==='co'?'var(--fns-blue)':'transparent'; ciBox.style.background=_picking==='ci'?'#EFF6FF':'#F9FAFB'; coBox.style.background=_picking==='co'?'#EFF6FF':'#F9FAFB';}
  if(window.FastNetState) window.FastNetState.replaceState({checkin:_ci, checkout:_co, checkIn:_ci, checkOut:_co});
  if(document.getElementById('fns_pop_cal').classList.contains('open')) renderCal();
  if(document.getElementById('fns_mobile_sheet').classList.contains('open')) renderMobileCal();
}
window.fnsStep=function(f,d){
  var dt=new Date((f==='ci'?_ci:_co)+'T00:00:00'); dt.setDate(dt.getDate()+d);
  var iso=dt.toISOString().slice(0,10);
  var today=new Date().toISOString().slice(0,10);
  if(iso < today) return;
  if(f==='ci'){
    _ci=iso;
    if(_co <= _ci){ var c=new Date(dt); c.setDate(c.getDate()+1); _co=c.toISOString().slice(0,10); }
  } else {
    if(iso <= _ci) return;
    _co=iso;
  }
  syncDate();
};
function renderCal(){
  var now=new Date(); now.setDate(1); now.setMonth(now.getMonth()+_calOff);
  renderMonth('fns_cal_m1','fns_cal_t1',now.getFullYear(), now.getMonth());
  var nxt=new Date(now.getFullYear(), now.getMonth()+1, 1);
  renderMonth('fns_cal_m2','fns_cal_t2',nxt.getFullYear(), nxt.getMonth());
}
function renderMonth(gid,tid,yr,mo){
  var moNames=['January','February','March','April','May','June','July','August','September','October','November','December'];
  var t=document.getElementById(tid); if(t) t.textContent=moNames[mo]+' '+yr;
  var g=document.getElementById(gid); if(!g) return; g.innerHTML='';
  var first=new Date(yr,mo,1).getDay(), days=new Date(yr,mo+1,0).getDate();
  var today=new Date().toISOString().slice(0,10);
  for(var i=0;i<first;i++){ var b=document.createElement('div'); g.appendChild(b); }
  for(var d=1;d<=days;d++){
    var iso=yr+'-'+String(mo+1).padStart(2,'0')+'-'+String(d).padStart(2,'0');
    var btn=document.createElement('button'); btn.type='button'; btn.className='fns-day';
    btn.textContent=d; btn.setAttribute('data-iso',iso);
    if(iso<today){ btn.disabled=true; btn.classList.add('past');}
    if(iso===_ci && iso===_co) btn.classList.add('selected');
    else if(iso===_ci) btn.classList.add('range-start');
    else if(iso===_co) btn.classList.add('range-end');
    else if(iso>_ci && iso<_co) btn.classList.add('in-range');
    (function(s,el){
      el.addEventListener('mouseenter', function(){
        if(_picking==='co' && s>_ci && s!==_co){
          document.querySelectorAll('.fns-day[data-iso]').forEach(function(day){
            var di=day.getAttribute('data-iso'); if(di>_ci && di<=s && di!==_ci && di!==_co) day.classList.add('preview');
          });
        }
      });
      el.addEventListener('mouseleave', function(){ document.querySelectorAll('.fns-day.preview').forEach(function(e){e.classList.remove('preview');}); });
      el.addEventListener('click', function(){
        if(_picking==='ci' || s<=_ci){ _ci=s; _picking='co'; }
        else { _co=s; _picking='ci'; document.getElementById('fns_pop_cal').classList.remove('open'); fnsCloseSegActive(); if(window.FastNetState) window.FastNetState.pushState({checkin:_ci, checkout:_co, checkIn:_ci, checkOut:_co});}
        syncDate(); renderCal();
      });
    })(iso, btn);
    g.appendChild(btn);
  }
}
window.fnsNavCal=function(d){ _calOff+=d; renderCal(); };
window.fnsOpenCal=function(which){
  if(window.innerWidth<=991){ fnsOpenMobile(); fnsSheetGo('when'); return; }
  _picking=which||'ci';
  var pop=document.getElementById('fns_pop_cal');
  var isOpen=pop.classList.contains('open');
  fnsClosePopovers();
  if(!isOpen){
    pop.classList.add('open');
    document.getElementById('fns_seg_'+(which==='co'?'co':'ci')).classList.add('active');
    document.getElementById('fns_seg_'+(which==='co'?'co':'ci')).setAttribute('aria-expanded','true');
    renderCal(); syncDate();
  }
};
window.fnsPreset=function(preset){
  document.querySelectorAll('.fns-preset').forEach(function(c){ c.classList.toggle('active', c.getAttribute('data-preset')===preset);});
  var t=new Date(); t.setHours(0,0,0,0);
  if(preset==='weekend'){ var dy=t.getDay(); var fri=new Date(t); fri.setDate(t.getDate()+ (5 - dy + 7)%7 ); _ci=fri.toISOString().slice(0,10); var sun=new Date(fri); sun.setDate(fri.getDate()+2); _co=sun.toISOString().slice(0,10); }
  else if(preset==='week'){ _ci=t.toISOString().slice(0,10); var wk=new Date(t); wk.setDate(t.getDate()+7); _co=wk.toISOString().slice(0,10); }
  else if(preset==='custom') { /* keep */ }
  _picking='ci'; syncDate(); renderCal(); if(window.FastNetState) window.FastNetState.pushState({checkin:_ci, checkout:_co, checkIn:_ci, checkOut:_co});
};
window.fnsCalReset=function(){ var t=new Date(); _ci=t.toISOString().slice(0,10); var c=new Date(t); c.setDate(c.getDate()+1); _co=c.toISOString().slice(0,10); _picking='ci'; syncDate(); renderCal(); };
window.fnsCalDone=function(){ if(window.FastNetState) window.FastNetState.pushState({checkin:_ci, checkout:_co, checkIn:_ci, checkOut:_co}); document.getElementById('fns_pop_cal').classList.remove('open'); fnsCloseSegActive(); };

// ── Guests ──
function syncGuests(){
  document.getElementById('gh_ad').value=_ad; document.getElementById('gh_ch').value=_ch; document.getElementById('gh_rm').value=_rm;
  var vals=['fns_ad_v','fns_ch_v','fns_rm_v','fns_m_ad_v','fns_m_ch_v','fns_m_rm_v'];
  var ids={'fns_ad_v':_ad,'fns_ch_v':_ch,'fns_rm_v':_rm,'fns_m_ad_v':_ad,'fns_m_ch_v':_ch,'fns_m_rm_v':_rm};
  Object.keys(ids).forEach(function(id){ var el=document.getElementById(id); if(el) el.textContent=ids[id]; });
  // buttons disabled
  document.getElementById('fns_ad_m').disabled=_ad<=1; document.getElementById('fns_ch_m').disabled=_ch<=0; document.getElementById('fns_rm_m').disabled=_rm<=1;
  document.getElementById('fns_m_ad_m').disabled=_ad<=1; document.getElementById('fns_m_ch_m').disabled=_ch<=0; document.getElementById('fns_m_rm_m').disabled=_rm<=1;
  document.getElementById('fns_ad_p').disabled=_ad>=10; document.getElementById('fns_ch_p').disabled=_ch>=6; document.getElementById('fns_rm_p').disabled=_rm>=5;
  document.getElementById('fns_m_ad_p').disabled=_ad>=10; document.getElementById('fns_m_ch_p').disabled=_ch>=6; document.getElementById('fns_m_rm_p').disabled=_rm>=5;
  var txt=_ad+' adult'+(_ad!==1?'s':'')+' · '+_rm+' room'+(_rm!==1?'s':'');
  if(_ch>0) txt=_ad+' adults · '+_ch+' child'+(_ch!==1?'ren':'')+' · '+_rm+' room'+(_rm!==1?'s':'');
  document.getElementById('fns_guests_lbl').textContent=txt;
  var mWho=document.getElementById('fns_m_who_val'); if(mWho) mWho.textContent=txt;
  if(window.FastNetState) window.FastNetState.replaceState({adults:String(_ad), children:String(_ch), rooms:String(_rm)});
  updateClear();
}
window.fnsG=function(f,d){
  if(f==='adults') _ad=Math.max(1, Math.min(10, _ad+d));
  if(f==='children') _ch=Math.max(0, Math.min(6, _ch+d));
  if(f==='rooms') _rm=Math.max(1, Math.min(5, _rm+d));
  syncGuests();
};
window.fnsMG=function(f,d){ fnsG(f,d); };
window.fnsGuestsToggle=function(e){
  e.stopPropagation();
  if(window.innerWidth<=991){ fnsOpenMobile(); fnsSheetGo('who'); return; }
  var pop=document.getElementById('fns_pop_guests');
  var isOpen=pop.classList.contains('open');
  fnsClosePopovers();
  if(!isOpen){ pop.classList.add('open'); document.getElementById('fns_seg_guests').classList.add('active'); document.getElementById('fns_seg_guests').setAttribute('aria-expanded','true'); }
};
window.fnsGuestsClose=function(){ document.getElementById('fns_pop_guests').classList.remove('open'); fnsCloseSegActive(); };
window.fnsGuestsApply=function(){ if(window.FastNetState) window.FastNetState.pushState({adults:String(_ad), children:String(_ch), rooms:String(_rm)}); fnsGuestsClose(); document.getElementById('gh_search_form').requestSubmit(); };

// ── Popover helpers ──
function fnsCloseSegActive(){ document.querySelectorAll('.fns-seg.active').forEach(function(el){ el.classList.remove('active'); el.setAttribute('aria-expanded','false'); }); }
function fnsClosePopovers(){
  document.getElementById('fns_pop_dest').classList.remove('open');
  document.getElementById('fns_pop_cal').classList.remove('open');
  document.getElementById('fns_pop_guests').classList.remove('open');
  fnsCloseSegActive();
}

// ── Mobile sheet (enterprise) ──
window.fnsOpenMobile=function(){
  var sheet=document.getElementById('fns_mobile_sheet');
  var backdrop=document.getElementById('fns_sheet_backdrop');
  sheet.classList.add('open'); if(backdrop) backdrop.classList.add('open');
  document.body.style.overflow='hidden';
  // prevent background scroll jump
  document.documentElement.style.overflow='hidden';
  renderDest(document.getElementById('gh_dest').value);
  renderMobileCal(); syncGuests();
  fnsSheetGo('where');
  setTimeout(function(){ try{ document.getElementById('fns_m_input').focus(); }catch(e){} }, 280);
};
window.fnsCloseMobile=function(){
  var sheet=document.getElementById('fns_mobile_sheet');
  var backdrop=document.getElementById('fns_sheet_backdrop');
  sheet.classList.remove('open'); if(backdrop) backdrop.classList.remove('open');
  document.body.style.overflow=''; document.documentElement.style.overflow='';
};
window.fnsSheetGo=function(step){
  document.querySelectorAll('.fns-sheet-tab').forEach(function(t){ t.classList.toggle('active', t.getAttribute('data-step')===step); t.setAttribute('aria-selected', t.getAttribute('data-step')===step ? 'true':'false'); });
  document.querySelectorAll('.fns-acc-head').forEach(function(h){ h.classList.remove('active'); h.setAttribute('aria-expanded','false'); });
  document.querySelectorAll('.fns-acc-body').forEach(function(b){ b.classList.remove('open'); });
  var head=document.querySelector('#fns_acc_'+step+' .fns-acc-head');
  var body=document.getElementById('fns_acc_'+step+'_body');
  if(head) { head.classList.add('active'); head.setAttribute('aria-expanded','true'); }
  if(body) body.classList.add('open');
  if(step==='when'){ renderMobileCal(); }
};
window.fnsAccToggle=function(step){
  var head=document.querySelector('#fns_acc_'+step+' .fns-acc-head');
  var body=document.getElementById('fns_acc_'+step+'_body');
  var isOpen=body.classList.contains('open');
  // close others
  document.querySelectorAll('.fns-acc-body').forEach(function(b){ b.classList.remove('open'); });
  document.querySelectorAll('.fns-acc-head').forEach(function(h){ h.classList.remove('active'); h.setAttribute('aria-expanded','false'); });
  if(!isOpen){ head.classList.add('active'); head.setAttribute('aria-expanded','true'); body.classList.add('open'); if(step==='when') renderMobileCal(); }
};
window.fnsMDestInput=function(v){
  document.getElementById('gh_dest').value=v; document.getElementById('gh_city').value=v;
  updateClear(); renderDest(v);
  if(window.FastNetState) FastNetState.replaceState({city:v, destination:v});
};
window.fnsMClear=function(){ document.getElementById('fns_m_input').value=''; document.getElementById('gh_dest').value=''; document.getElementById('gh_city').value=''; updateClear(); renderDest(''); if(window.FastNetState) FastNetState.replaceState({city:'', destination:''}); };
function renderMobileCal(){
  var grid=document.getElementById('fns_m_grid'); if(!grid) return;
  grid.innerHTML='';
  var start=new Date(_ci+'T00:00:00'); start.setDate(1);
  for(var m=0;m<6;m++){
    var cur=new Date(start.getFullYear(), start.getMonth()+m, 1);
    var yr=cur.getFullYear(), mo=cur.getMonth();
    var moNames=['January','February','March','April','May','June','July','August','September','October','November','December'];
    var wrap=document.createElement('div'); wrap.className='fns-m-month';
    var title=document.createElement('div'); title.className='fns-m-month-title'; title.textContent=moNames[mo]+' '+yr; wrap.appendChild(title);
    var daysWrap=document.createElement('div'); daysWrap.className='fns-m-days';
    var first=new Date(yr,mo,1).getDay(), days=new Date(yr,mo+1,0).getDate();
    var today=new Date().toISOString().slice(0,10);
    for(var i=0;i<first;i++){ var b=document.createElement('div'); daysWrap.appendChild(b); }
    for(var d=1;d<=days;d++){
      var iso=yr+'-'+String(mo+1).padStart(2,'0')+'-'+String(d).padStart(2,'0');
      var btn=document.createElement('button'); btn.type='button'; btn.className='fns-m-day'; btn.textContent=d; btn.setAttribute('data-iso',iso);
      btn.setAttribute('aria-label', iso);
      if(iso<today){ btn.disabled=true; btn.classList.add('past'); btn.setAttribute('aria-disabled','true');}
      if(iso===_ci && iso===_co) btn.classList.add('selected');
      else if(iso===_ci) btn.classList.add('range-start');
      else if(iso===_co) btn.classList.add('range-end');
      else if(iso>_ci && iso<_co) btn.classList.add('in-range');
      (function(s, el){
        el.addEventListener('click', function(){
          // haptic
          if(navigator.vibrate) try{ navigator.vibrate(12); }catch(e){}
          if(_picking==='ci' || s<=_ci){ _ci=s; _picking='co'; }
          else { _co=s; _picking='ci'; }
          syncDate(); renderMobileCal(); renderCal();
        });
      })(iso, btn);
      daysWrap.appendChild(btn);
    }
    wrap.appendChild(daysWrap); grid.appendChild(wrap);
  }
  // auto-scroll to selected month
  try{
    var firstSel=grid.querySelector('.fns-m-day.range-start, .fns-m-day.selected');
    if(firstSel) firstSel.scrollIntoView({block:'nearest', inline:'nearest'});
  }catch(e){}
}
window.fnsClearAllMobile=function(){
  document.getElementById('fns_m_input').value=''; document.getElementById('gh_dest').value=''; document.getElementById('gh_city').value='';
  var t=new Date(); _ci=t.toISOString().slice(0,10); var c=new Date(t); c.setDate(c.getDate()+1); _co=c.toISOString().slice(0,10);
  _ad=2; _ch=0; _rm=1;
  syncDate(); syncGuests(); renderDest(''); renderMobileCal();
  if(window.FastNetState) FastNetState.replaceState({city:'', destination:'', checkin:_ci, checkout:_co, adults:'2', children:'0', rooms:'1'});
};
window.fnsMobileSearch=function(){
  document.getElementById('gh_city').value=document.getElementById('fns_m_input').value || document.getElementById('gh_dest').value;
  var v=document.getElementById('gh_city').value; if(v) pushRecent(v);
  fnsCloseMobile();
  if(window.FastNetState) window.FastNetState.pushState({city:v, destination:v, checkin:_ci, checkout:_co, checkIn:_ci, checkOut:_co, adults:String(_ad), children:String(_ch), rooms:String(_rm)});
  // trigger skeleton then submit via FastNetState hydrate or form
  if(typeof ghTriggerShimmer==='function') ghTriggerShimmer();
  document.getElementById('gh_search_form').requestSubmit();
};

// Global close handlers
document.addEventListener('click', function(e){
  if(!document.getElementById('fns_seg_where').contains(e.target)) { document.getElementById('fns_pop_dest').classList.remove('open'); document.getElementById('fns_seg_where').classList.remove('active'); document.getElementById('fns_seg_where').setAttribute('aria-expanded','false'); }
  if(!document.getElementById('fns_pop_cal').contains(e.target) && !document.getElementById('fns_seg_ci').contains(e.target) && !document.getElementById('fns_seg_co').contains(e.target)) { document.getElementById('fns_pop_cal').classList.remove('open'); document.getElementById('fns_seg_ci').classList.remove('active'); document.getElementById('fns_seg_co').classList.remove('active'); document.getElementById('fns_seg_ci').setAttribute('aria-expanded','false'); document.getElementById('fns_seg_co').setAttribute('aria-expanded','false'); }
  if(!document.getElementById('fns_pop_guests').contains(e.target) && !document.getElementById('fns_seg_guests').contains(e.target)) { document.getElementById('fns_pop_guests').classList.remove('open'); document.getElementById('fns_seg_guests').classList.remove('active'); document.getElementById('fns_seg_guests').setAttribute('aria-expanded','false'); }
});
document.addEventListener('keydown', function(e){
  if(e.key==='Escape'){ fnsClosePopovers(); fnsCloseMobile(); }
  if(e.key==='Tab' && document.getElementById('fns_mobile_sheet').classList.contains('open')){
    // trap focus inside sheet
    var sheet=document.getElementById('fns_mobile_sheet');
    var focusable=sheet.querySelectorAll('button, input, [tabindex]:not([tabindex="-1"])');
    if(!focusable.length) return;
    var first=focusable[0], last=focusable[focusable.length-1];
    if(e.shiftKey && document.activeElement===first){ e.preventDefault(); last.focus(); }
    else if(!e.shiftKey && document.activeElement===last){ e.preventDefault(); first.focus(); }
  }
});
/* desktop: no scroll toggle needed — mobile pill is now always visible via CSS */
document.addEventListener('DOMContentLoaded', function(){
  updateClear(); renderDest(document.getElementById('gh_dest').value);
  renderCal(); syncGuests();
  // spec: graceful empty state — if city blank, try geolocated city (client-side)
  try{
    var destInput=document.getElementById('gh_dest');
    var isEmpty=!destInput.value.trim() || destInput.value.trim()==='All Tanzanian Destinations';
    var hasNoCityParam=!new URLSearchParams(window.location.search).has('city') && !new URLSearchParams(window.location.search).has('destination');
    if(isEmpty && hasNoCityParam && navigator.geolocation && window.MAPBOX_TOKEN){
      navigator.geolocation.getCurrentPosition(function(pos){
        var lat=pos.coords.latitude, lng=pos.coords.longitude;
        fetch('https://api.mapbox.com/geocoding/v5/mapbox.places/'+lng+','+lat+'.json?types=place&access_token='+window.MAPBOX_TOKEN)
          .then(function(r){return r.json();}).then(function(d){
            var feat=d.features&&d.features[0];
            if(feat && feat.text){
              var city=feat.text;
              // only autofill if user hasn't typed meanwhile
              if(!document.getElementById('gh_dest').value.trim() || document.getElementById('gh_dest').value.trim()==='All Tanzanian Destinations'){
                document.getElementById('gh_dest').value=city;
                document.getElementById('gh_city').value=city;
                document.getElementById('fns_m_input').value=city;
                updateClear(); renderDest(city);
                if(window.FastNetState) FastNetState.replaceState({city:city, destination:city, lat:String(lat), lng:String(lng)});
              }
            }
          }).catch(function(){});
      }, function(){}, {timeout:4000, maximumAge:600000});
    }
  }catch(e){}
  // keep mobile search count in sync with hydrate
  window.addEventListener('fastnet:markers-update', function(e){
    var cnt=Array.isArray(e.detail)?e.detail.length: (e.detail&&e.detail.totalCount) || <?= (int)$apiCount ?>;
    var el=document.getElementById('fns_m_search_count'); if(el) el.textContent='· '+cnt+' stays';
  });
  // spring physics for sheet drag (handle + sheet)
  var sheet=document.getElementById('fns_mobile_sheet');
  var handle=sheet.querySelector('.fns-sheet-drag');
  var startY=0, curY=0, dragging=false;
  function onStart(e){ var y=(e.touches?e.touches[0].clientY:e.clientY); startY=y; dragging=true; sheet.style.transition='none'; }
  function onMove(e){ if(!dragging) return; var y=(e.touches?e.touches[0].clientY:e.clientY); curY=y-startY; if(curY>0){ sheet.style.transform='translateY('+curY+'px)'; var backdrop=document.getElementById('fns_sheet_backdrop'); if(backdrop) backdrop.style.opacity=Math.max(0,1-curY/400); } else { sheet.style.transform=''; } }
  function onEnd(){ if(!dragging) return; dragging=false; sheet.style.transition=''; var backdrop=document.getElementById('fns_sheet_backdrop'); if(backdrop) backdrop.style.opacity=''; if(curY>120){ fnsCloseMobile(); } else { sheet.style.transform=''; } curY=0; }
  (handle||sheet).addEventListener('touchstart', onStart, {passive:true});
  sheet.addEventListener('touchmove', onMove, {passive:true});
  sheet.addEventListener('touchend', onEnd, {passive:true});
  // mouse drag for desktop testing
  handle && handle.addEventListener('mousedown', function(e){ onStart(e); var mm=function(ev){ onMove(ev); }; var mu=function(){ document.removeEventListener('mousemove',mm); document.removeEventListener('mouseup',mu); onEnd(); }; document.addEventListener('mousemove',mm); document.addEventListener('mouseup',mu); });
  // ensure chip is visible and pill hidden via CSS — no JS scroll toggle
});
// legacy aliases
window.ghDestFocus=fnsDestFocus; window.ghDestInput=fnsDestInput; window.ghDestKey=fnsDestKey; window.ghDestClear=fnsDestClear;
window.ghOpenCal=fnsOpenCal; window.ghNavCal=fnsNavCal; window.ghPreset=fnsPreset; window.ghCalReset=fnsCalReset; window.ghCalDone=fnsCalDone;
window.ghGuestsToggle=fnsGuestsToggle; window.ghGuestsClose=fnsGuestsClose; window.ghG=fnsG; window.ghStep=fnsStep; window.ghCloseAll=fnsClosePopovers;
})();
</script>
