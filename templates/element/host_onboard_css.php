<?php
/**
 * Shared IBM Carbon wizard styles (host onboarding + lodge edit).
 * Extracted verbatim from host-onboarding.php — edit here, both pages follow.
 */
?>
<style>
/* ═══════════════════════════════════════════════════════════════════
   IBM Carbon v11 · White theme · Host onboarding
   Tokens → components → nothing inline. Productive type, spacing scale.
   ═══════════════════════════════════════════════════════════════════ */
.obx{
  --cds-blue-60:#0f62fe; --cds-blue-70:#0043ce; --cds-blue-10:#edf5ff; --cds-blue-20:#d0e2ff;
  --cds-gray-100:#161616; --cds-gray-70:#525252; --cds-gray-60:#6f6f6f;
  --cds-gray-30:#c6c6c6; --cds-gray-20:#e0e0e0; --cds-gray-10:#f4f4f4; --cds-white:#ffffff;
  --cds-red-60:#da1e28; --cds-green-50:#0e6027; --cds-green-10:#defbe6; --cds-yellow-10:#fcf4d6;
  --cds-space-03:.5rem; --cds-space-04:.75rem; --cds-space-05:1rem; --cds-space-06:1.5rem;
  --cds-field-h:2.5rem; --cds-field-h-lg:2.75rem;
  font-family:'IBM Plex Sans','Inter',Roboto,Arial,sans-serif; color:var(--cds-gray-100);
  max-width:780px;
}
/* progress */
.ob-steps{display:flex;margin:var(--cds-space-05) 0;border:1px solid var(--cds-gray-20);background:var(--cds-white)}
.ob-steps a{flex:1;display:flex;align-items:center;gap:var(--cds-space-03);padding:.625rem .75rem;font-size:.75rem;font-weight:600;color:var(--cds-gray-70);text-decoration:none;border-right:1px solid var(--cds-gray-20)}
.ob-steps a:last-child{border-right:none}
.ob-steps a.cur{background:var(--cds-blue-10);color:var(--cds-blue-70)}
.ob-steps a.done{color:var(--cds-gray-100)}
.ob-steps .n{width:1.5rem;height:1.5rem;flex:none;display:inline-flex;align-items:center;justify-content:center;font-size:.75rem;background:var(--cds-gray-10);border:1px solid var(--cds-gray-20)}
.ob-steps a.cur .n{background:var(--cds-blue-60);border-color:var(--cds-blue-60);color:#fff}
.ob-steps a.done .n{background:var(--cds-green-10);border-color:#a7e8b7;color:var(--cds-green-50)}
@media(max-width:640px){.ob-steps a span.t{display:none}.ob-steps a{justify-content:center}}
/* form primitives */
.cds-form{display:grid;gap:var(--cds-space-04)}
.cds-label{display:block;font-size:.75rem;font-weight:600;letter-spacing:.02em;color:var(--cds-gray-70);margin-bottom:6px}
.cds-label.sm{font-size:11px}
.cds-hint{font-size:.75rem;color:var(--cds-gray-70);margin-bottom:10px}
.cds-field{min-height:var(--cds-field-h)}
.cds-field-lg{min-height:var(--cds-field-h-lg)}
.cds-area{min-height:90px;padding:10px 14px}
.cds-num{max-width:200px}
.obx .form-control:focus,.obx .form-select:focus{border-color:var(--cds-blue-60);box-shadow:0 0 0 2px var(--cds-white),0 0 0 4px var(--cds-blue-60);outline:none}
.cds-geo{display:flex;gap:var(--cds-space-03);flex-wrap:wrap;align-items:center}
.cds-geo-msg{font-size:.75rem;color:var(--cds-gray-70)}
.cds-err{font-size:.75rem;color:var(--cds-red-60);margin-top:4px;display:none}
input[readonly].locked{background:var(--cds-gray-10);color:var(--cds-gray-70)}
/* photo dropzone */
#obDrop{border:1.5px dashed var(--cds-gray-20);background:var(--cds-gray-10);padding:var(--cds-space-06);text-align:center;cursor:pointer;transition:border-color .15s ease,background .15s ease}
#obDrop.over{border-color:var(--cds-blue-60);background:var(--cds-blue-10)}
#obDrop input{display:none}
.ob-drop-ic{width:2.5rem;height:2.5rem;margin:0 auto var(--cds-space-03);display:flex;align-items:center;justify-content:center;border:1px solid var(--cds-gray-20);background:var(--cds-white)}
.ob-drop-t{font-size:.875rem;font-weight:600}
.ob-drop-s{font-size:.75rem;color:var(--cds-gray-70)}
#obPrev{display:flex;gap:var(--cds-space-03);flex-wrap:wrap;margin-top:12px}
#obPrev .ph{position:relative;width:160px;height:120px;overflow:hidden;border:1px solid var(--cds-gray-20);background:var(--cds-white)}
#obPrev img{width:100%;height:100%;object-fit:cover;display:block}
#obPrev .rm{position:absolute;top:4px;right:4px;width:24px;height:24px;border:none;border-radius:50%;background:var(--cds-gray-100);color:#fff;font-size:14px;line-height:1;cursor:pointer}
#obPrev .cov{position:absolute;left:4px;bottom:4px;background:var(--cds-blue-60);color:#fff;font-size:10px;font-weight:700;padding:2px 6px}
#obUpBar{display:none;height:4px;background:var(--cds-gray-20);margin-top:12px}
#obUpBar i{display:block;height:100%;width:0;background:var(--cds-blue-60);transition:width .2s ease}
/* map */
#obMap{height:260px;border:1px solid var(--cds-gray-20);background:var(--cds-gray-10);position:relative;overflow:hidden}
#obStatic{position:absolute;inset:0;width:100%;height:100%;object-fit:cover}
@media(min-width:992px){#obMap{height:320px}}
#obMap.loading{background:linear-gradient(90deg,#e0e0e0 25%,#f4f4f4 50%,#e0e0e0 75%);background-size:200% 100%;animation:pShimmer 1.2s ease infinite}
@keyframes pShimmer{from{background-position:200% 0}to{background-position:-200% 0}}
#obMapWrap{position:relative}
#obSearchList{position:absolute;top:100%;left:0;right:0;z-index:20;background:var(--cds-white);border:1px solid var(--cds-gray-20);border-top:none;display:none;max-height:220px;overflow:auto}
#obSearchList button{display:block;width:100%;text-align:left;background:none;border:none;padding:10px 12px;font-size:13px;cursor:pointer;border-bottom:1px solid var(--cds-gray-10)}
#obSearchList button:hover{background:var(--cds-blue-10)}
#obSearchList button small{display:block;color:var(--cds-gray-70);font-size:11px}
/* category cards + review */
.ob-sec-t{font-size:13px;font-weight:600;margin-bottom:4px}
.ob-sec-s{font-size:12px;color:var(--cds-gray-70);margin-bottom:12px}
.ob-room{border:1px solid var(--cds-gray-20);border-left:3px solid var(--cds-blue-60);background:var(--cds-white);padding:14px;margin-bottom:12px}
.ob-room-h{display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;gap:8px;flex-wrap:wrap}
.ob-room-h b{font-size:14px}
.ob-room-rm{background:none;border:1px solid var(--cds-gray-20);font-size:12px;padding:4px 10px;cursor:pointer;color:#a2191f}
.ob-numline{display:flex;gap:8px;margin-bottom:8px}
.ob-rphotos{display:flex;gap:8px;flex-wrap:wrap;margin-top:8px}
.ob-rphotos .ph{position:relative;width:96px;height:72px;overflow:hidden;border:1px solid var(--cds-gray-20)}
.ob-rphotos img{width:100%;height:100%;object-fit:cover;display:block}
.ob-rphotos .rm{position:absolute;top:2px;right:2px;width:20px;height:20px;border:none;border-radius:50%;background:var(--cds-gray-100);color:#fff;font-size:12px;line-height:1;cursor:pointer}
/* Failed optional photo → inline retry chip (room form shares .ph markup).
   Amber, tappable, impossible to mistake for a fatal error. */
.ob-rphotos .ph-retry,.ob-rphotos.ph-retry,button.ph-retry{width:auto !important;min-width:150px;max-width:100%;height:auto !important;min-height:56px;padding:10px 14px;display:flex;flex-direction:column;align-items:flex-start;justify-content:center;gap:4px;background:#fcf4d6 !important;border:1px dashed #f1c21b !important;border-radius:10px;cursor:pointer;text-align:left;font-family:inherit}
.ph-retry span{font-size:12px;color:#5f4800;line-height:1.4}
.ph-retry b{font-size:13px;color:#0f62fe}
.ob-review{display:grid;gap:0;border:1px solid var(--cds-gray-20);background:var(--cds-white)}
.ob-review>div{display:flex;gap:12px;padding:10px 14px;border-bottom:1px solid var(--cds-gray-10);font-size:13px}
.ob-review>div:last-child{border-bottom:none}
.ob-review dt{width:130px;flex:none;color:var(--cds-gray-70);font-weight:600;font-size:12px}
.ob-review dd{margin:0;font-weight:600}
.ob-review img{width:120px;height:80px;object-fit:cover;border:1px solid var(--cds-gray-20)}
.ob-sec-h{font-size:13px;font-weight:600;margin:4px 0 8px}
.ob-muted{font-size:12px;color:var(--cds-gray-70)}
.ob-nav{display:flex;gap:8px;margin-top:16px;flex-wrap:wrap}
.cds-btn-flex{flex:1;justify-content:center}
.cds-btn-flex2{flex:2;justify-content:center}
.cds-btn-sm{min-height:36px;font-size:12px}
.cds-btn-ic{min-height:40px}
.cds-hint{font-size:.75rem;color:var(--cds-gray-70);margin-bottom:10px}
.cds-geo-msg{font-size:.75rem;color:var(--cds-gray-70)}
.ob-rooms-line{font-size:13px}
.ob-more{font-size:11px;color:var(--cds-gray-70)}
.ob-dim{font-size:12px;color:var(--cds-gray-70)}
.ob-dim-nm{font-weight:400}
.ob-tight{margin-bottom:10px}
.cds-err{font-size:.75rem;color:var(--cds-red-60);margin-top:4px;display:none}
.field-err{font-size:.75rem;color:var(--cds-red-60);margin-top:4px;display:none}
.cds-ta{min-height:90px;padding:10px 14px}
@media(max-width:640px){.ob-room{padding:12px}.ob-nav .p-btn{flex:1 1 100%;justify-content:center}}
@media (prefers-reduced-motion:reduce){.obx *{animation:none!important;transition:none!important}}
</style>
