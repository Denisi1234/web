<?php
declare(strict_types=1);

namespace App\Controller\Stays;

use App\Service\AuthService;
use Cake\Cache\Cache;
use Cake\Http\Response;

/**
 * SubmitReviewTrait — Guest write-a-review endpoint for the hotel detail page.
 *
 * POST /hotel-detail/review (JSON): forwards the signed-in guest's rating to
 * backend POST /properties/{id}/reviews, which enforces the real rules
 * (paid stay required, hosts can't review own lodge, one review per guest).
 * On success the stays review/property caches are busted so the new review
 * and rating show immediately instead of after the 300s TTL.
 */
trait SubmitReviewTrait
{
    public function submitReview(): Response
    {
        if (!$this->getRequest()->is('post')) {
            return $this->reviewFail('Method not allowed.', 405);
        }

        $data = (array)$this->getRequest()->getData();
        $propertyId = (int)($data['property_id'] ?? $data['propertyId'] ?? 0);
        $rating = (float)($data['rating'] ?? 0);
        $comment = trim((string)($data['comment'] ?? ''));

        if ($propertyId <= 0) {
            return $this->reviewFail('Property is required.', 422);
        }
        if ($rating < 1 || $rating > 5) {
            return $this->reviewFail('Please select a star rating from 1 to 5.', 422);
        }
        if (mb_strlen($comment) > 2000) {
            return $this->reviewFail('Review must be 2000 characters or fewer.', 422);
        }

        $session = $this->getRequest()->getSession();
        $token = trim((string)$session->read('auth_token'));
        if (stripos($token, 'Bearer ') === 0) {
            $token = trim(substr($token, 7));
        }
        if ($token === '') {
            try {
                $token = (new AuthService($this->apiClient))->readToken($this->getRequest());
            } catch (\Throwable $e) {
                $token = '';
            }
        }
        if ($token === '' || $session->read('is_logged_out')) {
            return $this->response
                ->withStatus(401)
                ->withType('application/json')
                ->withStringBody((string)json_encode([
                    'success' => false,
                    'error' => 'Sign in to write a review. Only guests who stayed can review.',
                    'loginUrl' => '/login?redirect=' . urlencode('/hotel-detail/' . $propertyId . '#reviews'),
                ]));
        }

        try {
            $res = $this->apiClient->post(
                '/properties/' . $propertyId . '/reviews',
                [
                    'property_id' => $propertyId,
                    'rating' => $rating,
                    'comment' => $comment,
                ],
                ['Authorization' => 'Bearer ' . $token]
            );
        } catch (\Throwable $e) {
            return $this->reviewFail('Could not reach the review server. Please try again.', 502);
        }

        if (!is_array($res)) {
            return $this->reviewFail('Could not reach the review server. Please try again.', 502);
        }
        $status = (int)($res['_status'] ?? 0);
        if ($status === 401) {
            return $this->reviewFail('Your session has expired. Please sign in again.', 401);
        }
        if ($status >= 400 || !empty($res['_error'])) {
            return $this->reviewFail(
                (string)($res['message'] ?? 'Could not save your review. Please try again.'),
                $status >= 400 ? $status : 422
            );
        }

        // Bust stays caches so the new review + rating render immediately.
        try {
            Cache::delete('stays_reviews_' . $propertyId . '_p1', 'default');
            Cache::delete('stays_prop_' . $propertyId, 'default');
        } catch (\Throwable $e) {
        }

        $review = [
            'id' => $res['id'] ?? null,
            'user_name' => $res['user_name'] ?? ($res['guest_name'] ?? ''),
            'guest_name' => $res['guest_name'] ?? ($res['user_name'] ?? ''),
            'rating' => $res['rating'] ?? $rating,
            'comment' => $res['comment'] ?? $comment,
            'created_at' => $res['created_at'] ?? date('c'),
        ];

        return $this->response
            ->withStatus(201)
            ->withType('application/json')
            ->withStringBody((string)json_encode([
                'success' => true,
                'message' => (string)($res['message'] ?? 'Review submitted successfully.'),
                'review' => $review,
            ]));
    }

    private function reviewFail(string $message, int $status): Response
    {
        return $this->response
            ->withStatus($status)
            ->withType('application/json')
            ->withStringBody((string)json_encode(['success' => false, 'error' => $message]));
    }
}
