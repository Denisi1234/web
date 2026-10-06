<?php
declare(strict_types=1);

namespace App\Controller\Pages;

use App\Service\AuthService;
use App\Service\FastnetApiClient;
use Cake\Http\Exception\BadRequestException;
use Cake\Http\Exception\UnauthorizedException;
use Cake\Http\Response;

/**
 * PagesOwnerTrait — Host onboarding and owner verification submission.
 */
trait PagesOwnerTrait
{
    public function joinUs()
    {
        $session = $this->getRequest()->getSession();
        $sessionUser = $session->read('User');
        $rawToken = trim((string)$session->read('auth_token'));
        if (stripos($rawToken, 'Bearer ') === 0) {
            $rawToken = trim(substr($rawToken, 7));
        }
        if ($rawToken === '') {
            $rawToken = $this->authService->readToken($this->getRequest());
        }
        $isLoggedIn = !$session->read('is_logged_out') && !empty($sessionUser);
        $userRole = strtolower((string)($sessionUser['role'] ?? ''));
        $headers = $rawToken !== '' ? ['Authorization' => 'Bearer ' . $rawToken] : [];

        // Seamless guest-to-host upgrade. Any registered user can activate host mode
        // on their existing account without needing a second email or account.
        if ($this->getRequest()->is('post')) {
            $data = (array)$this->getRequest()->getData();
            if (($data['action'] ?? '') === 'become_host' && $isLoggedIn) {
                $sessionUser['role'] = 'owner';
                $session->write('User', $sessionUser);
                if ($rawToken !== '') {
                    try {
                        $this->apiClient->post('/user/personal-details', ['role' => 'owner'], $headers);
                    } catch (\Throwable $e) {}
                    try {
                        \Cake\Cache\Cache::delete('auth_role_' . md5($rawToken), 'default');
                        \Cake\Cache\Cache::write('auth_role_' . md5($rawToken), ['exp' => time() + 3600, 'role' => 'owner'], 'default');
                    } catch (\Throwable $e) {}
                }
                $this->Flash->success(__('Host mode activated! Welcome to FastNet Stays Hosting.'));
                return $this->redirect('/host/onboarding');
            }
        }

        // For owners/admins: fetch my properties for status section (real data).
        // Owner → ?mine=1 (own only); admin → /admin/properties slice.
        // Never fall back to other hosts' lodges: empty stays empty.
        $myProperties = [];
        if ($isLoggedIn && $rawToken !== '' && in_array($userRole, ['owner', 'admin'], true)) {
            try {
                if ($userRole === 'admin') {
                    $pRes = $this->apiClient->get('/admin/properties', ['per_page' => 6], $headers);
                } else {
                    $pRes = $this->apiClient->get('/properties', ['mine' => 1, 'per_page' => 6], $headers);
                }
                $all = $pRes['data'] ?? (isset($pRes[0]) ? $pRes : []);
                if (is_array($all)) $myProperties = array_values(array_slice($all, 0, 6));
            } catch (\Throwable $e) {
                $myProperties = [];
            }
        }

        $this->set(compact('isLoggedIn', 'userRole', 'myProperties', 'sessionUser'));
        return $this->render('/Pages/join-us');
    }
    // ── Bookings Forwarders (Backward Compatibility) ───────────────────────

    /**
     * Internal safe redirect: only relative portal paths, no protocol tricks
     * (backslashes, //host, control chars). Returns '' when unsafe.
     */
    /**
     * Owner KYC submission — POST /verification/owner.
     *
     * Uploads any identity/business documents to POST /upload first (the
     * endpoint stores the file and returns a public URL), then submits those
     * URLs with the rest of the payload. Without this an upgraded host is
     * stuck at "Pending Verification" with no way to submit documents.
     */
    public function submitOwnerVerification(): Response
    {
        if (!$this->getRequest()->is('post')) {
            throw new BadRequestException(__('Method not allowed.'));
        }

        $token = $this->currentBearerToken();
        if ($token === '') {
            throw new UnauthorizedException(__('Sign in to submit verification.'));
        }
        $headers = ['Authorization' => 'Bearer ' . $token];

        $uploadOne = function (?string $field) use ($headers): ?string {
            $file = $this->getRequest()->getUploadedFile($field);
            if ($file === null) {
                return null;
            }
            if ($file->getError() !== UPLOAD_ERR_OK) {
                throw new BadRequestException(__('Could not read the uploaded file. Please try again.'));
            }
            if ($file->getSize() > 10 * 1024 * 1024) {
                throw new BadRequestException(__('Documents must be 10 MB or smaller.'));
            }

            $tmp = $file->getStream()->getMetadata('uri');
            if (!is_string($tmp) || !is_readable($tmp)) {
                throw new BadRequestException(__('Could not read the uploaded file. Please try again.'));
            }

            $res = $this->apiClient->uploadFile(
                '/upload',
                'file',
                $tmp,
                $file->getClientFilename(),
                $file->getClientMediaType() ?? 'application/octet-stream',
                $headers
            );

            if (empty($res) || !empty($res['_status']) || empty($res['url'])) {
                throw new BadRequestException(__('Document upload failed. Please try again.'));
            }

            return (string)$res['url'];
        };

        $idDocUrl = $uploadOne('id_document');
        if ($idDocUrl === null) {
            throw new BadRequestException(__('A photo of your National ID is required.'));
        }

        $payload = [
            'full_name'       => trim((string)$this->getRequest()->getData('full_name')),
            'phone_number'    => trim((string)$this->getRequest()->getData('phone_number')),
            'id_number'       => trim((string)$this->getRequest()->getData('id_number')),
            'id_document_url' => $idDocUrl,
        ];

        foreach (['business_registration_number', 'payout_bank_name', 'payout_account_number', 'payout_account_name'] as $opt) {
            $v = trim((string)$this->getRequest()->getData($opt));
            if ($v !== '') {
                $payload[$opt] = $v;
            }
        }

        $businessDocUrl = $uploadOne('business_document');
        if ($businessDocUrl !== null) {
            $payload['business_document_url'] = $businessDocUrl;
        }

        $res = $this->apiClient->post('/verification/owner', $payload, $headers);

        if (empty($res) || !empty($res['_status'])) {
            $msg = $res['message'] ?? __('Could not submit verification. Please try again.');
            $this->Flash->error(__($msg));

            if ($this->getRequest()->is('json')) {
                return $this->response
                    ->withStatus(!empty($res['_status']) ? (int)$res['_status'] : 502)
                    ->withType('application/json')
                    ->withStringBody(json_encode(['status' => 'error', 'message' => $msg]));
            }

            return $this->redirect('/join-us');
        }

        $this->Flash->success(__('Documents submitted. We will review them shortly.'));

        if ($this->getRequest()->is('json')) {
            return $this->response->withType('application/json')->withStringBody(json_encode([
                'status'  => 'success',
                'message' => __('Documents submitted. We will review them shortly.'),
            ]));
        }

        return $this->redirect('/host/onboarding');
    }

    /** Current bearer token from session or persistent cookie. */
    private function currentBearerToken(): string
    {
        $session = $this->getRequest()->getSession();
        $token = trim((string)$session->read('auth_token'));
        if (stripos($token, 'Bearer ') === 0) {
            $token = trim(substr($token, 7));
        }
        if ($token === '') {
            $token = trim((string)$this->authService->readToken($this->getRequest()));
            if (stripos($token, 'Bearer ') === 0) {
                $token = trim(substr($token, 7));
            }
        }

        return $token;
    }

    private function safeRedirect(string $url): string
    {
        $url = trim($url);
        if ($url === '' || strlen($url) > 500) return '';
        if (!str_starts_with($url, '/') || str_starts_with($url, '//')) return '';
        if (str_contains($url, '\\') || preg_match('/[\r\n\t<>"]/', $url)) return '';
        if (!preg_match('#^/[A-Za-z0-9/_\-.?=&%#+]*$#', $url)) return '';
        return $url;
    }

    /**
     * Brute-force guard: max 10 login attempts per IP per 5 minutes.
     */
    private function loginRateLimited(string $ip): bool
    {
        $key = 'login_rate_' . md5($ip);
        $rec = \Cake\Cache\Cache::read($key, 'default');
        $now = time();
        $count = (is_array($rec) && isset($rec['exp']) && $rec['exp'] > $now) ? (int)$rec['count'] : 0;
        if ($count >= 10) return true;
        \Cake\Cache\Cache::write($key, ['count' => $count + 1, 'exp' => $now + 300]);
        return false;
    }
}
