<!-- FastNet Stays AI Concierge Floating Assistant -->
<style>
.fastnet-ai-pill {
    position: fixed;
    bottom: 24px;
    right: 24px;
    z-index: 9999;
    background: linear-gradient(135deg, #006CE4 0%, #004fb0 100%);
    color: #ffffff;
    border-radius: 50px;
    padding: 10px 18px;
    display: flex;
    align-items: center;
    gap: 10px;
    cursor: pointer;
    box-shadow: 0 8px 24px rgba(0, 108, 228, 0.35);
    transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    font-weight: 600;
    font-size: 14px;
    border: 1px solid rgba(255, 255, 255, 0.2);
    user-select: none;
}
.fastnet-ai-pill:hover {
    transform: translateY(-3px) scale(1.02);
    box-shadow: 0 12px 28px rgba(0, 108, 228, 0.45);
    background: linear-gradient(135deg, #0056b8 0%, #003e8c 100%);
    color: #ffffff;
}
.fastnet-ai-pill .ai-sparkle {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 28px;
    height: 28px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 50%;
    font-size: 14px;
    animation: aiPulse 2s infinite ease-in-out;
}
@keyframes aiPulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.12); }
}

/* AI Modal Chat Window */
.fastnet-ai-window {
    position: fixed;
    bottom: 85px;
    right: 24px;
    width: 380px;
    max-width: calc(100vw - 32px);
    height: 520px;
    max-height: calc(100vh - 120px);
    background: #ffffff;
    border-radius: 20px;
    box-shadow: 0 16px 48px rgba(15, 23, 42, 0.2);
    border: 1px solid rgba(226, 232, 240, 0.9);
    display: none;
    flex-direction: column;
    z-index: 10000;
    overflow: hidden;
    animation: aiSlideUp 0.3s cubic-bezier(0.16, 1, 0.3, 1);
}
@keyframes aiSlideUp {
    from { opacity: 0; transform: translateY(20px) scale(0.96); }
    to { opacity: 1; transform: translateY(0) scale(1); }
}
.fastnet-ai-header {
    background: linear-gradient(135deg, #006CE4 0%, #004fb0 100%);
    color: #ffffff;
    padding: 16px 20px;
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.fastnet-ai-body {
    flex: 1;
    overflow-y: auto;
    padding: 16px;
    background: #f8fafc;
    display: flex;
    flex-direction: column;
    gap: 12px;
}
.fastnet-ai-msg {
    max-width: 85%;
    padding: 10px 14px;
    border-radius: 14px;
    font-size: 13.5px;
    line-height: 1.45;
}
.fastnet-ai-msg.bot {
    background: #ffffff;
    color: #1e293b;
    border: 1px solid #e2e8f0;
    align-self: flex-start;
    border-bottom-left-radius: 4px;
}
.fastnet-ai-msg.user {
    background: #006CE4;
    color: #ffffff;
    align-self: flex-end;
    border-bottom-right-radius: 4px;
}
.fastnet-ai-chips {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    margin-top: 6px;
}
.fastnet-ai-chip {
    background: #eff6ff;
    color: #006CE4;
    border: 1px solid #bfdbfe;
    border-radius: 20px;
    padding: 4px 10px;
    font-size: 12px;
    cursor: pointer;
    transition: all 0.15s ease;
}
.fastnet-ai-chip:hover {
    background: #006CE4;
    color: #ffffff;
}
.fastnet-ai-footer {
    padding: 12px 16px;
    background: #ffffff;
    border-top: 1px solid #e2e8f0;
    display: flex;
    gap: 8px;
}
.fastnet-ai-input {
    flex: 1;
    border: 1px solid #cbd5e1;
    border-radius: 24px;
    padding: 8px 14px;
    font-size: 13.5px;
    outline: none;
}
.fastnet-ai-input:focus {
    border-color: #006CE4;
}
.fastnet-ai-send {
    background: #006CE4;
    color: #ffffff;
    border: none;
    border-radius: 50%;
    width: 38px;
    height: 38px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
}

@media (max-width: 768px) {
    .fastnet-ai-pill {
        bottom: 75px;
        right: 16px;
        padding: 8px 14px;
        font-size: 13px;
    }
    .fastnet-ai-window {
        bottom: 130px;
        right: 16px;
        width: calc(100vw - 32px);
        height: 460px;
    }
}
</style>

<!-- Floating Pill Trigger -->
<div class="fastnet-ai-pill" id="fastnet_ai_pill" onclick="toggleFastnetAi()">
    <div class="ai-sparkle"><i class="fa-solid fa-wand-magic-sparkles"></i></div>
    <span>AI Concierge</span>
</div>

<!-- AI Chat Window -->
<div class="fastnet-ai-window" id="fastnet_ai_window">
    <div class="fastnet-ai-header">
        <div class="d-flex align-items-center gap-2">
            <div class="ai-sparkle"><i class="fa-solid fa-robot"></i></div>
            <div>
                <div class="fw-bold" style="font-size: 14px;">FastNet Stays AI</div>
                <div style="font-size: 11px; opacity: 0.85;">24/7 Smart Travel Assistant</div>
            </div>
        </div>
        <button type="button" class="btn btn-sm text-white p-0" onclick="toggleFastnetAi()" style="background: none; border: none; font-size: 18px;">
            <i class="fa-solid fa-xmark"></i>
        </button>
    </div>

    <div class="fastnet-ai-body" id="fastnet_ai_body">
        <div class="fastnet-ai-msg bot">
            👋 <strong>Jambo! I'm your FastNet Stays AI Concierge.</strong><br>
            I can help you find luxury beach resorts, safari lodges, or answer any booking and payment questions across Tanzania.
            
            <div class="fastnet-ai-chips mt-2">
                <span class="fastnet-ai-chip" onclick="askFastnetAi('Best beach resorts in Zanzibar')">🏝️ Zanzibar Stays</span>
                <span class="fastnet-ai-chip" onclick="askFastnetAi('Safari lodges in Serengeti and Arusha')">🦁 Serengeti Safaris</span>
                <span class="fastnet-ai-chip" onclick="askFastnetAi('How to pay with M-Pesa / Tigo Pesa')">💳 Mobile Money Payment</span>
                <span class="fastnet-ai-chip" onclick="askFastnetAi('City hotels in Dar es Salaam')">🏙️ Dar es Salaam</span>
            </div>
        </div>
    </div>

    <form class="fastnet-ai-footer" onsubmit="handleAiSubmit(event)">
        <input type="text" id="fastnet_ai_input" class="fastnet-ai-input" placeholder="Ask about stays, pricing, areas..." autocomplete="off">
        <button type="submit" class="fastnet-ai-send">
            <i class="fa-solid fa-paper-plane" style="font-size: 13px;"></i>
        </button>
    </form>
</div>

<script>
function toggleFastnetAi() {
    const win = document.getElementById('fastnet_ai_window');
    const isVisible = win.style.display === 'flex';
    win.style.display = isVisible ? 'none' : 'flex';
    if (!isVisible) {
        document.getElementById('fastnet_ai_input').focus();
    }
}

function askFastnetAi(query) {
    document.getElementById('fastnet_ai_input').value = query;
    handleAiSubmit(new Event('submit'));
}

function handleAiSubmit(e) {
    e.preventDefault();
    const input = document.getElementById('fastnet_ai_input');
    const text = input.value.trim();
    if (!text) return;

    const body = document.getElementById('fastnet_ai_body');

    // Add user message
    const userMsg = document.createElement('div');
    userMsg.className = 'fastnet-ai-msg user';
    userMsg.innerText = text;
    body.appendChild(userMsg);
    input.value = '';
    body.scrollTop = body.scrollHeight;

    // Simulate smart AI response with instant direct links
    setTimeout(() => {
        const botMsg = document.createElement('div');
        botMsg.className = 'fastnet-ai-msg bot';
        
        const qLower = text.toLowerCase();
        let reply = '';

        if (qLower.includes('zanzibar') || qLower.includes('beach') || qLower.includes('island')) {
            reply = '🌴 <strong>Zanzibar Beach Escapes:</strong> We feature luxury beachfront villas, Nungwi resorts, and historic Stone Town boutique hotels with instant confirmation.<br><br><a href="/hotel-list-01?destination=Zanzibar" class="btn btn-sm btn-primary rounded-pill mt-1" style="font-size: 12px;">Browse Zanzibar Stays &rarr;</a>';
        } else if (qLower.includes('safari') || qLower.includes('serengeti') || qLower.includes('arusha') || qLower.includes('ngorongoro')) {
            reply = '🦁 <strong>Safari & Nature Lodges:</strong> Explore verified safari lodges, luxury tented camps, and wildlife retreats around Arusha, Serengeti, and Ngorongoro.<br><br><a href="/hotel-list-01?destination=Arusha" class="btn btn-sm btn-primary rounded-pill mt-1" style="font-size: 12px;">Explore Safari Lodges &rarr;</a>';
        } else if (qLower.includes('pay') || qLower.includes('money') || qLower.includes('mpesa') || qLower.includes('tigo') || qLower.includes('airtel') || qLower.includes('card')) {
            reply = '💳 <strong>Flexible & Secure Payments:</strong> You can pay easily using local Mobile Money (M-Pesa, Tigo Pesa, Airtel Money, Halopesa) or Visa/Mastercard. All transactions are protected with instant SMS/Email confirmation.';
        } else if (qLower.includes('dar') || qLower.includes('salaam') || qLower.includes('masaki') || qLower.includes('city')) {
            reply = '🏙️ <strong>Dar es Salaam Hotels:</strong> Choose from executive city hotels, Masaki boutique apartments, and coastal stays in Mbezi Beach and Kigamboni.<br><br><a href="/hotel-list-01?destination=Dar+es+Salaam" class="btn btn-sm btn-primary rounded-pill mt-1" style="font-size: 12px;">View Dar es Salaam Hotels &rarr;</a>';
        } else if (qLower.includes('list') || qLower.includes('host') || qLower.includes('owner') || qLower.includes('partner')) {
            reply = '🏨 <strong>List Your Property:</strong> Are you a hotel or lodge owner? You can list your property on FastNet Stays for free and start receiving verified bookings with fast payouts.<br><br><a href="/join-us" class="btn btn-sm btn-primary rounded-pill mt-1" style="font-size: 12px;">Join as a Host &rarr;</a>';
        } else {
            reply = '✨ I can help you search hotels, luxury resorts, and lodges across Tanzania. Where would you like to stay?<br><br><a href="/hotels" class="btn btn-sm btn-primary rounded-pill mt-1" style="font-size: 12px;">Search All Stays &rarr;</a>';
        }

        botMsg.innerHTML = reply;
        body.appendChild(botMsg);
        body.scrollTop = body.scrollHeight;
    }, 450);
}
</script>
