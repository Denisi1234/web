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
        if (!$this->getRequest()->is('post')) {
            throw new BadRequestException(__('Method not allowed.'));
        }

        $token = $this->rawToken();
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
            $msg = (string)($res['message'] ?? __('Could not submit verification. Please try again.'));
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
