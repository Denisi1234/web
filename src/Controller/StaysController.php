<?php
declare(strict_types=1);

namespace App\Controller;

use App\Service\FastnetApiClient;
use App\Service\StaysService;
use App\Controller\Stays\StaysDetailTrait;
use App\Controller\Stays\StaysSearchTrait;

/**
 * StaysController
 * Modular controller managing accommodations search, filtering, and property detail pages.
 * 100% dynamic - Zero hardcoded mock arrays.
 */
class StaysController extends AppController
{
    protected StaysService $staysService;

    use StaysDetailTrait;
    use StaysSearchTrait;
    protected FastnetApiClient $apiClient;

    public function initialize(): void
    {
        parent::initialize();
        $this->apiClient = new FastnetApiClient();
        $this->staysService = new StaysService($this->apiClient);
    }

    /**
     * Stays search and listing — consolidated to home (/) per prompt
     * Legacy /hotel-list-01, /hotels, /stays now redirect to home with query params preserved
     */
    public function index()
    {
        return $this->redirect('/?' . http_build_query($this->getRequest()->getQueryParams()));
    }
}
