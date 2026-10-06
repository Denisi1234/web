<style>
/* FastNet Stays — Sharp, Clean Mobile-Responsive Booking Checkout */
.agoda-checkout-stepper{background:#fff;border-bottom:1px solid #cbd5e1;padding:12px 0}
.agoda-stepper-inner{max-width:1180px;margin:0 auto;padding:0 16px;display:flex;align-items:center;justify-content:space-between}
.agoda-steps{display:flex;align-items:center;gap:0;flex:1;max-width:620px;margin:0 auto}
.agoda-step{display:flex;align-items:center;gap:8px;font-size:13px;font-weight:600;white-space:nowrap;color:#64748b}
.agoda-step .num{width:22px;height:22px;border-radius:4px;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;border:1px solid #cbd5e1;background:#fff;color:#64748b}
.agoda-step.active{color:#0f62fe}
.agoda-step.active .num{background:#0f62fe;color:#fff;border-color:#0f62fe}
.agoda-step.done{color:#059669}
.agoda-step.done .num{background:#059669;color:#fff;border-color:#059669}
.agoda-step-line{flex:1;height:2px;background:#e2e8f0;margin:0 10px;border-radius:1px}
.agoda-step-line.filled{background:#059669}

.agoda-step-badge{background:#eff6ff;color:#0f62fe;font-size:12px;font-weight:700;padding:3px 8px;border-radius:4px;border:1px solid #bfdbfe}
.agoda-step-title{font-size:13px;font-weight:700;color:#0f172a}
.agoda-step-next{font-size:12px;color:#64748b;font-weight:500}

.agoda-timer-bar{background:#fffbeb;border-bottom:1px solid #fef3c7;padding:10px 16px;text-align:center;font-size:13px;color:#92400e;display:flex;align-items:center;justify-content:center;gap:8px}
.agoda-timer-bar b{color:#b45309;font-weight:700;display:inline-flex;align-items:center;gap:4px}

.agoda-checkout-wrap{max-width:1180px;margin:20px auto;padding:0 16px;display:grid;grid-template-columns:1fr 380px;gap:20px;align-items:start}
.agoda-card{background:#fff;border:1.5px solid #cbd5e1;border-radius:6px;padding:20px;box-shadow:0 1px 2px rgba(0,0,0,0.03)}
.agoda-card-title{font-size:16px;font-weight:700;color:#0f172a;margin:0 0 14px;display:flex;align-items:center;justify-content:space-between}

.agoda-welcome{display:flex;align-items:center;gap:10px;font-size:13px;color:#334155}
.agoda-welcome .icon{width:32px;height:32px;background:#eff6ff;color:#0f62fe;display:flex;align-items:center;justify-content:center;border-radius:4px;font-size:14px}

.agoda-input-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px}
.agoda-field-group{display:flex;flex-direction:column;gap:5px}
.agoda-field-label{font-size:12.5px;font-weight:600;color:#334155}
.agoda-input{width:100%;height:46px;border:1.5px solid #cbd5e1;border-radius:6px;padding:0 14px;font-size:14px;color:#0f172a;background:#fff;transition:all .15s ease}
.agoda-input:focus{outline:none;border-color:#0f62fe;box-shadow:0 0 0 2px rgba(15,98,254,0.15)}
.agoda-input::placeholder{color:#94a3b8}

.agoda-next-btn{background:#0f62fe;color:#fff;border:none;border-radius:6px;padding:14px 24px;font-size:15px;font-weight:700;width:100%;cursor:pointer;letter-spacing:0.01em;box-shadow:0 2px 6px rgba(15,98,254,0.2);transition:all 150ms ease}
.agoda-next-btn:hover{background:#0043ce;transform:translateY(-1px);box-shadow:0 4px 10px rgba(15,98,254,0.25)}
.agoda-not-charged{font-size:12px;color:#059669;text-align:center;font-weight:600;margin-top:8px;display:flex;align-items:center;justify-content:center;gap:4px}

/* Stay Summary Side Card */
.agoda-side-card{background:#fff;border:1.5px solid #cbd5e1;border-radius:6px;overflow:hidden;box-shadow:0 1px 2px rgba(0,0,0,0.03)}
.agoda-hotel-row{display:flex;gap:12px;padding:14px}
.agoda-hotel-thumb{width:80px;height:80px;border-radius:4px;object-fit:cover;flex:0 0 80px;background:#e2e8f0;border:1px solid #e2e8f0}
.agoda-hotel-title{font-size:14.5px;font-weight:700;color:#0f172a;line-height:1.3}
.agoda-stars{color:#f59e0b;font-size:12px;margin:2px 0}
.agoda-dates{display:flex;align-items:center;justify-content:space-between;font-size:12.5px;color:#334155;padding:12px 14px;background:#f8fafc;border-top:1px solid #e2e8f0;border-bottom:1px solid #e2e8f0}
.agoda-dates b{font-size:13.5px;color:#0f172a;display:block}
.agoda-room-box{padding:14px;border-bottom:1px solid #e2e8f0}
.agoda-room-title{font-size:13.5px;font-weight:700;color:#0f172a}
.agoda-room-meta{font-size:12px;color:#64748b;margin-top:2px}

.agoda-price-breakdown{padding:14px}
.agoda-price-row{display:flex;align-items:center;justify-content:space-between;font-size:13px;color:#475569;margin-bottom:8px}
.agoda-price-total{display:flex;align-items:center;justify-content:space-between;font-size:15px;font-weight:700;color:#0f172a;padding-top:10px;border-top:1.5px dashed #cbd5e1;margin-top:10px}
.agoda-price-total .total-amount{color:#0f62fe;font-size:18px;font-weight:800}

/* Mobile Responsiveness */
@media(max-width:992px){
  .agoda-checkout-wrap{grid-template-columns:1fr;gap:14px;margin:12px auto;padding:0 12px}
  .agoda-side-card-wrap{order:-1}
  .agoda-card{padding:16px;border-radius:6px}
}
@media(max-width:600px){
  .agoda-checkout-stepper{padding:8px 0}
  .agoda-stepper-inner{padding:0 12px}
  .agoda-checkout-wrap{padding:0 10px;margin:10px auto;gap:10px}
  .agoda-card{padding:14px;border-radius:6px}
  .agoda-input-grid{grid-template-columns:1fr;gap:10px}
  .agoda-input{font-size:16px;height:44px} /* 16px prevents iOS zoom */
  .agoda-hotel-thumb{width:68px;height:68px;flex:0 0 68px}
  .agoda-dates{padding:10px 12px;font-size:12px}
  .agoda-next-btn{padding:13px 18px;font-size:14.5px;border-radius:6px}
  .agoda-timer-bar{font-size:12px;padding:8px 12px}
}
</style>
