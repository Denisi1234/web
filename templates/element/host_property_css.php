<?php
/**
 * Shared IBM Carbon form + guest-preview styles for host property forms.
 * Used by host-listing-form (add) and host-lodge-form (edit) so both align
 * with each other and with the frontend stay card.
 */
?>
<style>
/* ── Carbon form layer (scoped) ── */
.cds-pform{display:grid;gap:20px;margin-top:16px}
.cds-pform .cds-row{display:grid;gap:20px;grid-template-columns:1fr 1fr}
@media(max-width:640px){.cds-pform .cds-row{grid-template-columns:1fr}}
.cds-field{display:flex;flex-direction:column;gap:8px;min-width:0}
.cds-field>label{font-size:12px;font-weight:600;letter-spacing:.02em;color:#525252}
.cds-field>label .req{color:#da1e28}
.cds-field .cds-input{background:#f4f4f4;border:none;border-bottom:1px solid #8d8d8d;border-radius:0;min-height:40px;padding:8px 16px;font-size:14px;color:#161616;width:100%;outline:none;transition:box-shadow .15s ease}
.cds-field .cds-input::placeholder{color:#a8a8a8}
.cds-field .cds-input:focus{box-shadow:0 0 0 2px #0f62fe;background:#fff}
.cds-field textarea.cds-input{min-height:96px;resize:vertical}
.cds-helper{font-size:12px;color:#6f6f6f}
.cds-actions{display:flex;gap:12px;flex-wrap:wrap;margin-top:4px}
.cds-actions .p-btn{flex:1;justify-content:center;min-height:48px}
/* Carbon tags (amenities / rooms) */
.cds-tags{display:flex;flex-wrap:wrap;gap:8px;margin:4px 0 0}
.cds-tag{display:inline-flex;align-items:center;gap:6px;background:#e0e0e0;color:#161616;font-size:12px;font-weight:400;padding:4px 6px 4px 12px;border-radius:9999px;text-decoration:none}
a.cds-tag:hover{background:#c6c6c6;color:#161616}
.cds-tag button,.cds-tag a.rm{background:transparent;border:none;color:#161616;font-size:14px;font-weight:600;cursor:pointer;padding:0 6px;border-radius:50%;line-height:1;text-decoration:none}
.cds-tag button:hover,.cds-tag a.rm:hover{background:#8d8d8d;color:#fff}
.cds-tag.blue{background:#d0e2ff;color:#0043ce}
.cds-quick{display:flex;gap:6px;flex-wrap:wrap;margin-top:10px}
.cds-addrow{display:flex;gap:8px}
.cds-addrow .cds-input{flex:1}
/* ── Guest preview: mirrors the frontend gh-card ── */
.pf-preview{border:1px solid #e0e0e0;background:#fff;display:grid;grid-template-columns:220px 1fr;overflow:hidden}
.pf-preview .pf-photo{background:#e8ecef;min-height:170px;position:relative}
.pf-preview .pf-photo img{position:absolute;inset:0;width:100%;height:100%;object-fit:cover}
.pf-preview .pf-body{padding:16px 20px;display:flex;flex-direction:column;gap:6px;min-width:0}
.pf-preview .pf-name{font-size:18px;font-weight:600;color:#161616;margin:0}
.pf-preview .pf-loc{font-size:13px;color:#525252}
.pf-preview .pf-rating{display:flex;align-items:center;gap:8px;font-size:13px}
.pf-preview .pf-stars{color:#f1a21b;letter-spacing:2px;font-size:13px}
.pf-preview .pf-score{background:#0e6027;color:#fff;font-weight:600;font-size:12px;padding:2px 8px;border-radius:4px}
.pf-preview .pf-amens{display:flex;flex-wrap:wrap;gap:6px;font-size:12px;color:#525252;margin-top:2px}
.pf-preview .pf-foot{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-top:auto;padding-top:10px;border-top:1px solid #f4f4f4;flex-wrap:wrap}
.pf-preview .pf-price{text-align:right}
.pf-preview .pf-price .v{font-size:20px;font-weight:700;color:#161616}
.pf-preview .pf-price .u{font-size:12px;color:#6f6f6f}
.pf-preview .pf-cta{display:flex;gap:8px}
@media(max-width:640px){.pf-preview{grid-template-columns:1fr}.pf-preview .pf-photo{min-height:180px}}
</style>
