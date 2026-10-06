<?php
declare(strict_types=1);

namespace App\Controller\Host;

use Cake\Http\Response;

/**
 * HostProfileTrait — Host profile, avatar upload, and file upload.
 */
trait HostProfileTrait
{
    /**
     * Upload the submitted avatar via POST /profile/photo and return its URL.
     * Returns null when nothing was chosen or the upload failed, so the rest
     * of the profile save still proceeds.
     */
    private function uploadAvatar(array $headers): ?string
    {
        $file = $this->getRequest()->getUploadedFile('avatarFile');
        if ($file === null || $file->getError() !== UPLOAD_ERR_OK) {
            return null;
        }

        if ($file->getSize() > 5 * 1024 * 1024) {
            $this->Flash->error(__('Profile photo must be 5 MB or smaller.'));
            return null;
        }

        $tmp = $file->getStream()->getMetadata('uri');
        if (!is_string($tmp) || !is_readable($tmp)) {
            return null;
        }

        $res = $this->apiClient->uploadFile(
            '/profile/photo',
            'photo',
            $tmp,
            $file->getClientFilename(),
            $file->getClientMediaType() ?: 'image/jpeg',
            $headers
        );

        if (empty($res) || !empty($res['_status']) || empty($res['photo_url'])) {
            $this->Flash->error(__('Could not upload your photo — please try a different image.'));
            return null;
        }

        return (string)$res['photo_url'];
    }

    public function profile()
    {
        $headers = $this->hostHeaders();
        $userProfile = $this->cachedProfile();
        $me = $userProfile;

        // Try /me for fuller data
        $meRes = $this->apiClient->get('/me', [], $headers);
        if (!empty($meRes) && empty($meRes['_status'])) {
            $meCand = $meRes['data'] ?? $meRes['user'] ?? $meRes;
            if (is_array($meCand) && !empty($meCand['email'])) $me = array_merge($me, $meCand);
        }

        if ($this->getRequest()->is(['post','put','patch'])) {
            $data = (array)$this->getRequest()->getData();
            $payload = array_filter([
                'name' => trim((string)($data['name'] ?? '')),
                'email' => trim((string)($data['email'] ?? '')),
                'phone' => trim((string)($data['phone'] ?? $data['phone_number'] ?? '')),
                'phone_number' => trim((string)($data['phone'] ?? $data['phone_number'] ?? '')),
                'address' => trim((string)($data['address'] ?? '')),
                'bio' => trim((string)($data['bio'] ?? '')),
                // Only a server-issued URL is ever accepted. The form used to
                // base64-encode the whole image into this field and post it as
                // if it were a URL, storing a multi-megabyte data URI in the
                // user record. The avatar now goes through POST /profile/photo.
                'profile_photo_url' => $this->uploadAvatar($headers),
            ], fn($v) => $v !== '');
            if (!empty($payload)) {
                // Backend source of truth: PATCH /profile (auth:sanctum)
                $res = $this->apiClient->patch('/profile', $payload, $headers);
                if ($bounce = $this->bounceOnUnauth($res, '/host/profile')) return $bounce;
                if ($res === null) {
                    $this->Flash->error(__('Could not update profile — service unavailable.'));
                } elseif (!empty($res['_status']) && (int)$res['_status'] >= 400) {
                    $this->Flash->error(__($res['message'] ?? 'Could not update profile.'));
                } else {
                    $this->Flash->success(__('Profile updated.'));
                    // Invalidate session profile so next read is fresh from backend
                    $this->getRequest()->getSession()->delete('UserProfile');
                    $this->getRequest()->getSession()->delete('UserProfileTs');
                    $userProfile = $this->cachedProfile();
                    $me = $userProfile;
                }
            }
        }

        // Stats for header (properties/rooms) — owner-scoped
        $properties = $this->myProperties($headers);
        $rRes = $this->apiClient->get('/rooms', [], $headers);
        $rooms = $rRes['data'] ?? (isset($rRes[0]) ? $rRes : []);
        if (!is_array($rooms)) $rooms = [];

        // Real verification state for the portfolio tab. Null means the host
        // has never submitted documents — the page must say so instead of
        // asserting a fabricated "100% compliant".
        $verificationStatus = null;
        try {
            $vRes = $this->apiClient->get('/verification/owner', [], $headers);
            $verificationStatus = strtolower((string)(
                $vRes['verification']['status'] ?? ''
            )) ?: null;
        } catch (\Throwable $e) {
            $verificationStatus = null;
        }

        $stats = ['properties' => count($properties), 'rooms' => count($rooms)];
        $this->set(compact('userProfile', 'me', 'stats', 'verificationStatus'));
        return $this->render('/Pages/host-profile');
    }

    /**
     * Proxy upload endpoint for onboarding / host portal.
     * Uploads file to fastnet backend /upload with host authentication.
     */
    public function upload(): Response
    {
        $this->autoRender = false;
        $headers = $this->hostHeaders();
        $file = $this->getRequest()->getUploadedFile('file');
        // NOTE: CakePHP 5 UploadedFile (laminas-diactoros) has NO isValid()
        // method — getError() is the only validity check. Calling isValid()
        // fataled every valid upload (500 on /host/upload, Oct 2026).
        if ($file === null || $file->getError() !== UPLOAD_ERR_OK) {
            $code = $file ? $file->getError() : UPLOAD_ERR_NO_FILE;
            $msg = match ($code) {
                UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'Photo is too large (max 10 MB).',
                UPLOAD_ERR_PARTIAL => 'Upload was interrupted — please try again.',
                UPLOAD_ERR_NO_FILE => 'No photo selected.',
                default => 'No valid file uploaded.',
            };
            return $this->response
                ->withType('application/json')
                ->withStatus(400)
                ->withStringBody((string)json_encode(['ok' => false, 'message' => $msg]));
        }
        // Match the wizard JS (10 MB) so Contabo php.ini slips give a clear message, not a silent fail.
        if ($file->getSize() > 10 * 1024 * 1024) {
            return $this->response
                ->withType('application/json')
                ->withStatus(400)
                ->withStringBody((string)json_encode(['ok' => false, 'message' => 'Photo is too large (max 10 MB).']));
        }
        $mime = $file->getClientMediaType() ?: 'image/jpeg';
        if (!str_starts_with($mime, 'image/')) {
            return $this->response
                ->withType('application/json')
                ->withStatus(400)
                ->withStringBody((string)json_encode(['ok' => false, 'message' => 'Only image files please.']));
        }
        $tmp = $file->getStream()->getMetadata('uri');
        if (!is_string($tmp) || !is_readable($tmp)) {
            return $this->response
                ->withType('application/json')
                ->withStatus(400)
                ->withStringBody((string)json_encode(['ok' => false, 'message' => 'Could not read uploaded file.']));
        }
        $res = $this->apiClient->uploadFile(
            '/upload',
            'file',
            $tmp,
            $file->getClientFilename(),
            $mime,
            $headers,
            30
        );
        if ($res === null || (!empty($res['_status']) && (int)$res['_status'] >= 400)) {
            try {
                \Cake\Log\Log::error(sprintf(
                    '[Host upload] backend %s -> %s for %s (%s, %d bytes)',
                    $this->apiClient->getBaseUrl() . '/upload',
                    (string)($res['_status'] ?? 'no-response'),
                    $file->getClientFilename(),
                    $mime,
                    (int)$file->getSize()
                ));
            } catch (\Throwable $e) {
            }
            $status = (!empty($res['_status']) && (int)$res['_status'] >= 400) ? (int)$res['_status'] : 502;
            $msg = (string)($res['message'] ?? 'Upload service unavailable. Check BACKEND_API_URL and try again.');
            return $this->response
                ->withType('application/json')
                ->withStatus($status)
                ->withStringBody((string)json_encode(['ok' => false, 'message' => $msg]));
        }
        return $this->response
            ->withType('application/json')
            ->withStatus(200)
            ->withStringBody((string)json_encode($res));
    }
}
