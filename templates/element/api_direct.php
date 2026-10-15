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
$req = $this->getRequest();
$apiBase = rtrim((string)\Cake\Core\Configure::read('App.backendApiUrl', 'http://127.0.0.1:8000/api'), '/');
// Browser-safe base: the backend URL above is for SERVER-side calls and is
// often an internal docker hostname (http://backend/...) that browsers can
// neither resolve nor call over https (mixed content). Browser JS must use
// the public API origin instead — explicit env wins, otherwise upgrade an
// internal/loopback base to the public API origin on https pages.
$publicBase = trim((string)\Cake\Core\env('PUBLIC_BACKEND_API_URL', ''));
if ($publicBase === '') {
    $host = strtolower((string)parse_url($apiBase, PHP_URL_HOST));
    $isPageHttps = ($req->getUri()->getScheme() ?? '') === 'https'
        || strtolower((string)$req->getHeaderLine('X-Forwarded-Proto')) === 'https';
    if ($isPageHttps && ($host === '' || in_array($host, ['backend', 'localhost', '127.0.0.1', '::1'], true))) {
        $publicBase = 'https://api.fastnetstays.com/api';
    } else {
        $publicBase = $apiBase;
    }
}
$apiBase = rtrim($publicBase, '/');
$apiToken = '';
try {
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
