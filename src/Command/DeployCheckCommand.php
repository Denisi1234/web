<?php
declare(strict_types=1);

namespace App\Command;

use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use function Cake\Core\env;

/**
 * DeployCheckCommand — `bin/cake deploy:check`
 *
 * Proves a Contabo/VPS deployment matches the local working build.
 * Exit 0 = boot-critical checks pass. Exit 1 = something will 500 or
 * silently break (backend proxy, uploads, sessions). Warnings never fail.
 */
class DeployCheckCommand extends Command
{
    public static function defaultName(): string
    {
        return 'deploy:check';
    }

    public function execute(Arguments $args, ConsoleIo $io): ?int
    {
        $fail = 0;
        $ok = function (string $msg) use ($io): void {
            $io->out('  <success>[OK]</success> ' . $msg);
        };
        $bad = function (string $msg) use ($io, &$fail): void {
            $fail++;
            $io->out('  <error>[FAIL]</error> ' . $msg);
        };
        $warn = function (string $msg) use ($io): void {
            $io->out('  <warning>[WARN]</warning> ' . $msg);
        };

        $io->out('FastNet deploy check — ' . date('Y-m-d H:i:s'));
        $io->out('');

        // 1. PHP runtime
        $io->out('Runtime:');
        if (version_compare(PHP_VERSION, '8.1.0', '>=')) {
            $ok('PHP ' . PHP_VERSION . ' (>= 8.1)');
        } else {
            $bad('PHP ' . PHP_VERSION . ' — upgrade to >= 8.1 (composer requires it)');
        }
        foreach (['intl', 'mbstring', 'pdo_pgsql', 'openssl'] as $ext) {
            if (extension_loaded($ext)) {
                $ok('ext-' . $ext);
            } else {
                $bad('ext-' . $ext . ' missing — install php8.x-' . $ext . ' (upload/map/i18n break without it)');
            }
        }

        // 2. Install artifacts (git-ignored: must exist on the server)
        $io->out('');
        $io->out('Install:');
        if (file_exists(ROOT . DS . 'vendor' . DS . 'autoload.php')) {
            $ok('vendor/autoload.php (composer install ran)');
        } else {
            $bad('vendor/ missing — run `composer install --no-dev --optimize-autoloader` on the server');
        }
        $appLocal = CONFIG . 'app_local.php';
        if (file_exists($appLocal)) {
            $ok('config/app_local.php present');
            $raw = (string)@file_get_contents($appLocal);
            if (str_contains($raw, '__SALT__')) {
                $bad('config/app_local.php still has __SALT__ placeholder — re-run `composer install` so Installer bakes a real salt');
            }
        } else {
            $bad('config/app_local.php missing — run `composer install` (postInstall creates it from app_local.example.php)');
        }

        // 3. Secrets / env parity with local
        $io->out('');
        $io->out('Config:');
        $envSalt = (string)env('SECURITY_SALT', '');
        if ($envSalt !== '' && $envSalt !== '__SALT__') {
            $ok('SECURITY_SALT set via env');
        } elseif (file_exists($appLocal) && !str_contains((string)@file_get_contents($appLocal), '__SALT__')) {
            $ok('salt baked into config/app_local.php by composer install');
        } else {
            $bad('no usable SECURITY_SALT — export SECURITY_SALT or run `composer install` (app boots ephemeral until then: logins/CSRF reset on restart)');
        }
        $debug = filter_var(env('DEBUG', false), FILTER_VALIDATE_BOOLEAN);
        $io->out('  <info>[INFO]</info> DEBUG=' . ($debug ? 'true (dev — never use on Contabo)' : 'false (production)'));
        $backend = (string)env('BACKEND_API_URL', 'http://127.0.0.1:8000/api');
        $isLoopbackBackend = str_contains($backend, '127.0.0.1') || str_contains($backend, 'localhost');
        $frontHost = (string)parse_url((string)env('APP_FULL_BASE_URL', ''), PHP_URL_HOST);
        $isLocalFront = $frontHost === '' || $frontHost === 'localhost' || $frontHost === '127.0.0.1';
        if ($isLoopbackBackend && !$isLocalFront) {
            $bad('BACKEND_API_URL=' . $backend . ' points at loopback while APP_FULL_BASE_URL is a real domain — host portal, uploads and search proxy will fail on Contabo. Export the real backend URL.');
        } elseif ($isLoopbackBackend) {
            $ok('BACKEND_API_URL=' . $backend . ' (local dev loop — set the real URL on Contabo)');
        } else {
            $ok('BACKEND_API_URL=' . $backend);
        }

        // 4. Writable dirs
        $io->out('');
        $io->out('Filesystem:');
        foreach (['tmp', 'logs', 'tmp/cache', 'tmp/sessions'] as $dir) {
            $path = ROOT . DS . str_replace('/', DS, $dir);
            if (is_dir($path) && is_writable($path)) {
                $ok($dir . '/ writable');
            } else {
                $bad($dir . '/ missing or not writable — `mkdir -p ' . $dir . ' && chown -R www-data:www-data tmp logs && chmod -R 775 tmp logs`');
            }
        }

        // 5. Reachability (warnings only — informational)
        $io->out('');
        $io->out('Services (warnings only):');
        $mapToken = (string)env('MAPBOX_TOKEN', (string)env('MAPBOX_ACCESS_TOKEN', ''));
        if ($mapToken !== '' && str_starts_with($mapToken, 'pk.')) {
            $ok('MAPBOX_TOKEN present (premium map style)');
        } else {
            $warn('MAPBOX_TOKEN missing — maps use free OpenStreetMap fallback (works, plainer style)');
        }
        if ((string)env('DATABASE_URL', '') !== '') {
            $ok('DATABASE_URL set');
        } else {
            $warn('DATABASE_URL missing — ok only if the app never touches the local DB (backend API is source of truth)');
        }
        $probe = rtrim($backend, '/') . '/map-config';
        $ctx = stream_context_create(['http' => ['method' => 'GET', 'timeout' => 4, 'ignore_errors' => true]]);
        $res = @file_get_contents($probe, false, $ctx);
        if ($res !== false) {
            $ok('backend reachable (' . $probe . ')');
        } else {
            $warn('backend NOT reachable at ' . $probe . ' — host portal data/uploads will show retry states. Start/fix the backend or correct BACKEND_API_URL.');
        }

        $io->out('');
        if ($fail > 0) {
            $io->error('deploy:check FAILED with ' . $fail . ' blocking issue(s) — fix above, redeploy, re-run.');
            return static::CODE_ERROR;
        }
        $io->success('deploy:check passed — deployment matches the local working build.');
        return static::CODE_SUCCESS;
    }
}
