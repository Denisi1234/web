<?php
declare(strict_types=1);

namespace App\Controller;

use App\Service\AuthService;
use App\Service\FastnetApiClient;
use App\Service\BookingQuoteService;
use App\Service\PaymentService;
use App\Controller\Bookings\BookingsCheckoutTrait;
use App\Controller\Bookings\BookingsFinishTrait;
use App\Controller\Bookings\BookingsPayStatusTrait;
use App\Controller\Bookings\BookingsQuoteTrait;
use App\Controller\Bookings\BookingsVerifyTrait;

/**
 * BookingsController
 * Modular checkout, price calculation, and reservation management controller.
 */
class BookingsController extends AppController
{
    protected FastnetApiClient $apiClient;
    protected BookingQuoteService $quoteService;
    protected PaymentService $paymentService;
    protected AuthService $authService;

    use BookingsCheckoutTrait;
    use BookingsFinishTrait;
    use BookingsPayStatusTrait;
    use BookingsQuoteTrait;
    use BookingsVerifyTrait;

    public function initialize(): void
    {
        parent::initialize();
        $this->apiClient = new FastnetApiClient();
        $this->authService = new AuthService($this->apiClient);
        $this->quoteService = new BookingQuoteService($this->apiClient);
        $this->paymentService = new PaymentService($this->apiClient);
    }
}
