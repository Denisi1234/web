<style>
/* Agoda Checkout — exact to screenshot */
.agoda-checkout-header{background:#fff;border-bottom:1px solid #e8eaed;min-height:64px;display:flex;align-items:center;position:sticky;top:0;z-index:100;padding:14px 0}
.agoda-checkout-header-inner{max-width:1180px;margin:0 auto;padding:0 16px;width:100%;display:flex;align-items:center;justify-content:space-between;gap:16px}
.agoda-logo{font-size:22px;font-weight:800;letter-spacing:-0.02em;display:flex;align-items:center;gap:4px;text-decoration:none!important}
.agoda-logo .dot{width:10px;height:10px;border-radius:50%;display:inline-block}
.agoda-logo b{font-weight:800;color:#202124}
.agoda-steps{display:flex;align-items:center;gap:0;flex:1;max-width:560px;margin:0 24px}
.agoda-step{display:flex;align-items:center;gap:8px;font-size:13px;font-weight:600;white-space:nowrap}
.agoda-step .num{width:22px;height:22px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:700;border:1px solid #dadce0;background:#fff;color:#5f6368}
.agoda-step.active .num{background:#0f62fe;color:#fff;border-color:#0f62fe}
.agoda-step.done .num{background:#0f62fe;color:#fff;border-color:#0f62fe}
.agoda-step span{color:#5f6368}
.agoda-step.active span{color:#0f62fe}
.agoda-step-line{flex:1;height:2px;background:#e8eaed;margin:0 8px;border-radius:1px}
.agoda-step-line.filled{background:#0f62fe}
.agoda-user{font-size:13px;color:#202124;display:flex;align-items:center;gap:8px;white-space:nowrap}
.agoda-user .avatar{width:32px;height:32px;border-radius:50%;background:#7c6af0;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:13px}
.agoda-timer-bar{background:#fef3e8;border-bottom:1px solid #fde8cc;padding:10px 16px;text-align:center;font-size:13px;color:#202124;display:flex;align-items:center;justify-content:center;gap:8px}
.agoda-timer-bar b{color:#e53935;font-weight:700;display:flex;align-items:center;gap:6px}
.agoda-checkout-wrap{max-width:1180px;margin:14px auto;padding:0 16px;display:grid;grid-template-columns:1fr 360px;gap:16px;align-items:start}
.agoda-card{background:#fff;border:1px solid #e8eaed;border-radius:16px;padding:16px;box-shadow:0 6px 16px rgba(0,0,0,0.05)}
.agoda-card-title{font-size:16px;font-weight:800;color:#202124;margin:0 0 10px}
.agoda-welcome{display:flex;align-items:center;gap:12px;font-size:13px;color:#202124}
.agoda-welcome .icon{width:44px;height:32px;background:#3576f6;color:#fff;display:flex;align-items:center;justify-content:center;border-radius:2px}
.agoda-lead-card{background:#F8FAFC;border:1px solid #e8eaed;border-radius:14px;padding:14px 16px;display:grid;grid-template-columns:1fr auto;gap:8px 16px}
.agoda-lead-name{font-size:14px;font-weight:700;color:#202124;display:flex;align-items:center;gap:6px}
.agoda-lead-meta{font-size:13px;color:#202124}
.agoda-edit{color:#0f62fe;font-size:13px;font-weight:700;text-decoration:none;display:flex;align-items:center;gap:4px;align-self:end}
.agoda-pref-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px;background:#F8FAFC;border:1px solid #e8eaed;border-radius:14px;padding:14px 16px}
.agoda-pref-col b{font-size:13px;color:#202124;display:block;margin-bottom:8px}
.agoda-radio{display:flex;align-items:center;gap:8px;font-size:13px;color:#202124;margin:8px 0}
.agoda-radio input{width:18px;height:18px;accent-color:#0f62fe}
.agoda-link{color:#0f62fe;font-size:13px;font-weight:600;text-decoration:none}
.agoda-link:hover{text-decoration:underline}
.agoda-benefit{display:flex;align-items:center;gap:12px;background:var(--cds-gray-10);border-radius:8px;padding:14px}
.agoda-benefit .free-badge{background:#0f7a2b;color:#fff;font-size:11px;font-weight:800;padding:4px 8px;border-radius:4px}
.agoda-next-btn{background:#0f62fe;color:#fff;border:none;border-radius:30px;padding:14px 24px;font-size:15px;font-weight:800;width:100%;cursor:pointer;letter-spacing:0.02em;box-shadow:0 4px 12px rgba(15,98,254,0.18);transition:background 150ms ease}
.agoda-next-btn:hover{background:#0353e9}
.agoda-not-charged{font-size:12px;color:#0f7a2b;text-align:center;font-weight:600;margin-top:6px}
.agoda-side-card{background:#fff;border:1px solid #e8eaed;border-radius:16px;overflow:hidden;box-shadow:0 4px 12px rgba(0,0,0,0.04)}
.agoda-side-card-inner{padding:14px}
.agoda-dates{display:flex;align-items:center;justify-content:space-between;font-size:13px;color:#202124;padding:12px 14px;border-bottom:1px solid #f1f3f4}
.agoda-dates b{font-size:14px;color:#202124}
.agoda-hotel-row{display:flex;gap:12px}
.agoda-hotel-thumb{width:72px;height:72px;border-radius:8px;object-fit:cover;flex:0 0 72px}
.agoda-hotel-title{font-size:14px;font-weight:800;color:#202124;line-height:1.3}
.agoda-stars{color:#e67e22;font-size:11px}
.agoda-rating{font-size:13px;color:#202124}
.agoda-rating b{color:#0d4a7a}
.agoda-room-box{background:#F8FAFC;border:1px solid #e8eaed;border-radius:14px;padding:12px;display:flex;gap:12px}
.agoda-room-thumb{width:72px;height:72px;border-radius:8px;object-fit:cover;flex:0 0 72px}
.agoda-room-title{font-size:13px;font-weight:700;color:#202124}
.agoda-room-meta{font-size:12px;color:#202124;line-height:1.6}
.agoda-amenities{font-size:12px;color:#0f7a2b;line-height:1.8}
.agoda-amenities span{margin-right:10px;white-space:nowrap}
.agoda-green-banner{background:#e6f4ea;border:1px solid #c8e6c9;border-radius:8px;padding:10px 12px;font-size:12px;color:#202124;display:flex;gap:8px;align-items:center}
.agoda-green-banner b{color:#137333}
.agoda-input{width:100%;height:44px;border:1px solid #dadce0;border-radius:12px;padding:0 12px;font-size:13px;transition:border-color 150ms ease,box-shadow 150ms ease}
.agoda-input:focus{outline:none;border-color:#0f62fe;box-shadow:0 0 0 2px rgba(15,98,254,0.15)}
@media(max-width:992px){
  .agoda-checkout-header{height:auto;padding:10px 0}
  .agoda-checkout-header-inner{flex-wrap:wrap;gap:10px}
  .agoda-steps{order:3;max-width:none;width:100%;margin:0;justify-content:space-between}
  .agoda-checkout-wrap{grid-template-columns:1fr;gap:12px;padding:0 12px}
  .agoda-side-card{order:-1}
}
@media(max-width:600px){
  .agoda-checkout-header-inner{padding:0 12px}
  .agoda-steps{gap:4px}
  .agoda-step{font-size:11px;gap:4px}
  .agoda-step .num{width:18px;height:18px;font-size:10px}
  .agoda-timer-bar{font-size:12px;padding:8px 12px}
  .agoda-card{padding:12px}
  .agoda-pref-grid{grid-template-columns:1fr;gap:12px}
  .agoda-hotel-title{font-size:13px}
  .agoda-dates{padding:10px 12px;font-size:12px}
  .agoda-dates b{font-size:13px}
  .agoda-user{display:none!important}
}
@media(max-width:768px){
  html,body{max-width:100%;overflow-x:hidden}
  .agoda-user{display:none!important}
  .agoda-checkout-wrap{padding:0 8px;gap:12px}
  .agoda-card{border-radius:10px}
  .agoda-timer-bar{flex-wrap:wrap;gap:4px}
  .agoda-lead-card{grid-template-columns:1fr;gap:6px}
  .agoda-hotel-row{flex-direction:row;gap:10px}
  .agoda-room-box{flex-direction:row}
  .agoda-next-btn{padding:14px 24px;font-size:15px}
}
@media(max-width:480px){
  .agoda-checkout-header-inner{padding:0 8px;gap:8px}
  .agoda-steps{gap:3px}
  .agoda-step{font-size:10px;gap:3px}
  .agoda-step .num{width:16px;height:16px;font-size:9px}
  .agoda-checkout-wrap{padding:0 8px}
  .agoda-card{padding:10px}
  .agoda-welcome{font-size:12px}
  .agoda-lead-name{font-size:13px}
  .agoda-lead-meta{font-size:12px}
  .agoda-pref-grid{padding:12px}
  .agoda-benefit{padding:12px;gap:10px}
  .agoda-benefit i{font-size:24px!important}
  .agoda-dates{flex-wrap:wrap;gap:8px}
  .agoda-hotel-thumb,.agoda-room-thumb{width:60px;height:60px;flex:0 0 60px}
  .agoda-amenities{font-size:11px}
  .agoda-green-banner{font-size:11px;padding:8px 10px}
}
@media(max-width:375px){
  .agoda-checkout-header{padding:6px 0}
  .agoda-steps{gap:2px}
  .agoda-step{font-size:9.5px}
  .agoda-timer-bar{font-size:11px;padding:6px 8px}
  .agoda-card{padding:8px}
  .agoda-next-btn{font-size:14px;padding:12px 16px}
  .agoda-input{height:38px;font-size:13px}
}
</style>
