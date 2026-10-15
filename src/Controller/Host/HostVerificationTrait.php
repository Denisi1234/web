<?php
declare(strict_types=1);

namespace App\Controller\Host;

use Cake\Http\Exception\BadRequestException;
use Cake\Http\Exception\UnauthorizedException;
use Cake\Http\Response;

/**
 * HostVerificationTrait — Owner KYC (identity) verification inside the
 * owner portal. Required before a host's first property goes live; the
 * team reviews documents manually, usually within one business day.
 */
trait HostVerificationTrait
{
    public function verification()
    {
        $userProfile = $this->cachedProfile();
        $sessionUser = $this->sessionUser();
        // Backend profile is source of truth; fall back to session status.
        $status = strtolower((string)($userProfile['status'] ?? $sessionUser['status'] ?? ''));
        $role = strtolower((string)($userProfile['role'] ?? $sessionUser['role'] ?? ''));
        $needsVerification = $role === 'owner' && $status === 'pending verification';
        $backendError = $this->backendError;
        $this->set(compact('userProfile', 'needsVerification', 'status', 'backendError'));

        return $this->render('/Pages/host-verification');
    }

    /**
     * Owner KYC submission — POST /verification/owner.
     *
     * Uploads any identity/business documents to POST /upload first (the
     * endpoint stores the file and returns a public URL), then submits those
     * URLs with the rest of the payload.
     */
    public function submitVerification(): Response
    {
        // The portal form posts via fetch expecting JSON — validation and
        // upload failures must come back as JSON with the REAL reason, never
        // as an HTML error page (which the form can only show as a generic
        // "Submission failed").
        $fail = function (string $message, int $status = 422): Response {
            if ($this->getRequest()->is('json')
                || strtolower((string)$this->getRequest()->getHeaderLine('X-Requested-With')) === 'xmlhttprequest') {
                return $this->response
                    ->withStatus($status)
                    ->withType('application/json')
                    ->withStringBody((string)json_encode(['status' => 'error', 'message' => $message]));
            }
            if ($status === 401) {
                throw new UnauthorizedException(__($message));
            }
            throw new BadRequestException(__($message));
        };

        if (!$this->getRequest()->is('post')) {
            return $fail('Method not allowed.', 405);
        }

        $token = $this->rawToken();
        if ($token === '') {
            return $fail('Sign in to submit verification.', 401);
        }
        $headers = ['Authorization' => 'Bearer ' . $token];

        $uploadOne = function (?string $field) use ($headers, $fail): ?string {
            $file = $this->getRequest()->getUploadedFile($field);
            if ($file === null) {
                return null;
            }
            if ($file->getError() !== UPLOAD_ERR_OK) {
                $msg = $file->getError() === UPLOAD_ERR_INI_SIZE || $file->getError() === UPLOAD_ERR_FORM_SIZE
                    ? __('That photo is too large. Please choose a file under 10 MB.')
                    : __('Could not read the uploaded file. Please try again.');
                $resp = $fail($msg, 422);
                throw new VerificationHaltException($resp);
            }
            if ($file->getSize() > 10 * 1024 * 1024) {
                $resp = $fail(__('Documents must be 10 MB or smaller.'), 422);
                throw new VerificationHaltException($resp);
            }

            $tmp = $file->getStream()->getMetadata('uri');
            if (!is_string($tmp) || !is_readable($tmp)) {
                $resp = $fail(__('Could not read the uploaded file. Please try again.'), 422);
                throw new VerificationHaltException($resp);
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
                // Forward the backend's own reason (e.g. "must be an image")
                // instead of swallowing it behind a generic message.
                $msg = (string)($res['message'] ?? $res['errors']['file'][0] ?? __('Document upload failed. Please try again.'));
                $resp = $fail($msg, 422);
                throw new VerificationHaltException($resp);
            }

            return (string)$res['url'];
        };

        try {
            $idDocUrl = $uploadOne('id_document');
        } catch (VerificationHaltException $e) {
            return $e->response;
        }
        if ($idDocUrl === null) {
            return $fail(__('A photo of your National ID is required.'), 422);
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

        try {
            $businessDocUrl = $uploadOne('business_document');
        } catch (VerificationHaltException $e) {
            return $e->response;
        }
        if ($businessDocUrl !== null) {
            $payload['business_document_url'] = $businessDocUrl;
        }

        $res = $this->apiClient->post('/verification/owner', $payload, $headers);

        if (empty($res) || !empty($res['_status'])) {
            // Forward backend validation detail (e.g. which field failed)
            // when present instead of a generic message.
            $errs = $res['errors'] ?? [];
            $detail = '';
            if (is_array($errs)) {
                foreach ($errs as $msgs) {
                    foreach ((array)$msgs as $m) {
                        if (is_string($m) && $m !== '') {
                            $detail = $m;
                            break 2;
                        }
                    }
                }
            }
            $msg = (string)($res['message'] ?? __('Could not submit verification. Please try again.'));
            if ($detail !== '' && $detail !== $msg) {
                $msg .= ' ' . $detail;
            }
            if ($this->getRequest()->is('json')) {
                return $this->response
                    ->withStatus(!empty($res['_status']) ? (int)$res['_status'] : 502)
                    ->withType('application/json')
                    ->withStringBody((string)json_encode(['status' => 'error', 'message' => $msg]));
            }
            $this->Flash->error(__($msg));

            return $this->redirect('/host/verification');
        }

        $this->Flash->success(__('Documents submitted. We will review them shortly.'));

        if ($this->getRequest()->is('json')) {
            return $this->response->withType('application/json')->withStringBody((string)json_encode([
                'status'  => 'success',
                'message' => __('Documents submitted. We will review them shortly.'),
            ]));
        }

        return $this->redirect('/host/onboarding');
    }
}
