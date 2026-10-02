<?php
/**
 * api_direct element — exposes the backend API base URL + bearer token to
 * browser JS so portal/site forms can submit directly to the backend API
 * (e.g. https://api.fastnetstays.com/properties) instead of proxying
 * through CakePHP.
 *
 * SECURITY: the token is readable by any JS on the page. It is emitted only
 * when the server actually has one (authenticated session/cookie). Any XSS
 * on these pages could exfiltrate it — keep inline scripts minimal and
 * prefer this over scattering tokens per-template.
 */
$apiBase = rtrim((string)\Cake\Core\Configure::read('App.backendApiUrl', 'http://127.0.0.1:8000/api'), '/');
$apiToken = '';
try {
    $req = $this->getRequest();
    $sess = $req->getSession();
    $apiToken = trim((string)$sess->read('auth_token'));
    if (stripos($apiToken, 'Bearer ') === 0) $apiToken = trim(substr($apiToken, 7));
    if ($apiToken === '' && !$sess->read('is_logged_out')) {
        $cookies = $req->getCookieParams();
        $apiToken = trim((string)($cookies[\App\Service\AuthService::TOKEN_COOKIE] ?? ''));
        if (stripos($apiToken, 'Bearer ') === 0) $apiToken = trim(substr($apiToken, 7));
    }
    if (strlen($apiToken) < 10 || strlen($apiToken) > 4096) $apiToken = '';
} catch (\Throwable $e) {
    $apiToken = '';
}
?>
<meta name="api-base" content="<?= h($apiBase) ?>">
<?php if ($apiToken !== ''): ?><meta name="api-token" content="<?= h($apiToken) ?>"><?php endif; ?>
