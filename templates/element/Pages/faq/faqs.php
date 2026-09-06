<?php
$faqItems = [
    [
        'id' => 'flush-collapseOne',
        'question' => 'How do I book a stay or lodge on Fastnetstays.com?',
        'answer' => 'Search for your destination (e.g. Zanzibar, Dar es Salaam, Arusha, or Dodoma), select your check-in and check-out dates, choose your preferred room type, and click "Book Now". You can complete your reservation securely using local Mobile Money (Vodacom M-Pesa, Tigo Pesa, Airtel Money, HaloPesa).',
        'show' => true
    ],
    [
        'id' => 'flush-collapseTwo',
        'question' => 'What payment methods are accepted for bookings?',
        'answer' => 'Fastnetstays.com exclusively uses Tanzania local Mobile Money payments. We support all major Tanzanian mobile money operators: Vodacom M-Pesa, Tigo Pesa, Airtel Money, and HaloPesa (Halotel).',
        'show' => false
    ],
    [
        'id' => 'flush-collapseThree',
        'question' => 'Can I pay upon arrival at the property?',
        'answer' => 'Payment terms depend on the specific property host. Properties displaying the "Pay at Property" badge allow payment upon check-in, while others require pre-payment online to guarantee instant room reservation.',
        'show' => false
    ],
    [
        'id' => 'flush-collapseFour',
        'question' => 'What is the cancellation and refund policy?',
        'answer' => 'Each stay specifies its cancellation policy (Free Cancellation up to 48 hours before check-in, Flexible, or Non-Refundable). If you cancel within the free window, refunds are automatically returned to your original payment method.',
        'show' => false
    ],
    [
        'id' => 'flush-collapseFive',
        'question' => 'How can property owners list their lodge or hotel on Fastnetstays?',
        'answer' => 'Property owners and hosts can register instantly through our Owner Portal at admin_owner_portal/page-login.php. Simply create an account, upload property photos, configure room rates, and go live across Tanzania.',
        'show' => false
    ]
];
?>

<div class="accordion accordion-flush" id="accordionFlushExample">
    <?php foreach ($faqItems as $item): ?>
        <div class="accordion-item border rounded-2 mb-3">
            <h2 class="accordion-header">
                <button class="accordion-button <?= $item['show'] ? '' : 'collapsed' ?> fw-bold text-dark" type="button" data-bs-toggle="collapse" data-bs-target="#<?= h($item['id']) ?>" aria-expanded="<?= $item['show'] ? 'true' : 'false' ?>" aria-controls="<?= h($item['id']) ?>">
                    <?= h($item['question']) ?>
                </button>
            </h2>
            <div id="<?= h($item['id']) ?>" class="accordion-collapse collapse <?= $item['show'] ? 'show' : '' ?>" data-bs-parent="#accordionFlushExample">
                <div class="accordion-body text-slate-700 lh-relaxed">
                    <?= h($item['answer']) ?>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>