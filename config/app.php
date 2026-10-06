<?php

use Cake\Cache\Engine\FileEngine;
use Cake\Database\Connection;
use Cake\Database\Driver\Postgres;
use Cake\Log\Engine\FileLog;
use Cake\Mailer\Transport\MailTransport;
use function Cake\Core\env;

    /**
     * Parse REDIS_URL (Railway plugin format) into Cake Redis engine config.
     */
    $redisConfig = (static function (string $prefix): array {
        $url = env('CACHE_URL', env('REDIS_URL', ''));
        if ($url === '') return [];
        $parts = parse_url($url);
        if (empty($parts['host'])) return [];
        $cfg = [
            'className' => 'Redis',
            'host' => $parts['host'],
            'port' => $parts['port'] ?? 6379,
            'prefix' => $prefix,
            'duration' => '+1 hour',
        ];
        if (!empty($parts['pass'])) $cfg['password'] = $parts['pass'];
        if (!empty($parts['path']) && trim($parts['path'], '/') !== '') {
            $db = (int)trim($parts['path'], '/');
            if ($db > 0) $cfg['database'] = $db;
        }
        return $cfg;
    });

return [
    /* Debug level (stock docs trimmed for the 300-line cap). */
    'debug' => filter_var(env('DEBUG', false), FILTER_VALIDATE_BOOLEAN),

    /* App base config (stock docs trimmed for the 300-line cap). */
    'App' => [
        'namespace' => 'App',
        'encoding' => env('APP_ENCODING', 'UTF-8'),
        'defaultLocale' => env('APP_DEFAULT_LOCALE', 'en_US'),
        'defaultTimezone' => env('APP_DEFAULT_TIMEZONE', 'UTC'),
        'base' => false,
        'dir' => 'src',
        'webroot' => 'webroot',
        'wwwRoot' => WWW_ROOT,
        //'baseUrl' => env('SCRIPT_NAME'),
        'fullBaseUrl' => env('APP_FULL_BASE_URL', false),
        'backendApiUrl' => env('BACKEND_API_URL', 'http://127.0.0.1:8000/api'),
        'mapboxToken' => env('MAPBOX_TOKEN', env('MAPBOX_ACCESS_TOKEN', env('MAPBOX_API_KEY', ''))),
        'mapboxStyle' => env('MAPBOX_STYLE', 'mapbox://styles/mapbox/streets-v12'),
        'imageBaseUrl' => 'img/',
        'cssBaseUrl' => 'css/',
        'jsBaseUrl' => 'js/',
        'paths' => [
            'plugins' => [ROOT . DS . 'plugins' . DS],
            'templates' => [ROOT . DS . 'templates' . DS],
            'locales' => [RESOURCES . 'locales' . DS],
        ],
    ],

    /* Security salt (keep secret!) (stock docs trimmed for the 300-line cap). */
    'Security' => [
        'salt' => (function () {
            $s = env('SECURITY_SALT', null);
            if (!empty($s)) return $s;
            if (filter_var(env('DEBUG', true), FILTER_VALIDATE_BOOLEAN)) {
                return 'dev-insecure-salt-' . bin2hex(random_bytes(16));
            }
            // Deploy parity: never white-screen production for a missing salt
            // (the classic "works local, 500 on Contabo"). Boot with an
            // ephemeral key so `bin/cake deploy:check` can run and report the
            // exact fix; sessions/cookies reset on restart until a real salt
            // is set via SECURITY_SALT env or `composer install`.
            error_log('[FastNet] SECURITY_SALT missing — booting with ephemeral key. Set SECURITY_SALT env or run `composer install` to bake a salt into config/app_local.php.');
            return 'ephemeral-' . bin2hex(random_bytes(32));
        })(),
    ],

    /* Asset timestamping (stock docs trimmed for the 300-line cap). */
    'Asset' => [
        //'timestamp' => true,
        // 'cacheTime' => '+1 year'
    ],

    /* Cache adapters (stock docs trimmed for the 300-line cap). */
    'Cache' => [
        // Redis when CACHE_URL/REDIS_URL is set (Railway plugin) — required for
        // multi-instance + shared portal caches. Falls back to files locally.
        'default' => (static function () use ($redisConfig) {
            $redis = $redisConfig('fastnet_');
            if ($redis !== []) return $redis;
            return [
                'className' => FileEngine::class,
                'path' => CACHE,
                'url' => env('CACHE_DEFAULT_URL', null),
            ];
        })(),

        // Backs shared sessions (SESSION_HANDLER=cache). Redis when available,
        // otherwise files (single-instance only).
        'session' => (static function () use ($redisConfig) {
            $redis = $redisConfig('fastnet_sess_');
            if ($redis !== []) {
                $redis['duration'] = '+8 hours';
                return $redis;
            }
            return [
                'className' => FileEngine::class,
                'path' => CACHE . 'sessions' . DS,
                'prefix' => 'phpsess_',
                'duration' => '+8 hours',
            ];
        })(),

        /* Cake core caches (stock docs trimmed for the 300-line cap). */
        '_cake_translations_' => [
            'className' => FileEngine::class,
            'prefix' => 'myapp_cake_translations_',
            'path' => CACHE . 'persistent' . DS,
            'serialize' => true,
            'duration' => '+1 years',
            'url' => env('CACHE_CAKECORE_URL', null),
        ],

        /* Model/schema caches (stock docs trimmed for the 300-line cap). */
        '_cake_model_' => [
            'className' => FileEngine::class,
            'prefix' => 'myapp_cake_model_',
            'path' => CACHE . 'models' . DS,
            'serialize' => true,
            'duration' => '+1 years',
            'url' => env('CACHE_CAKEMODEL_URL', null),
        ],
    ],

    /* Error handling (stock docs trimmed for the 300-line cap). */
    'Error' => [
        'errorLevel' => E_ALL,
        'skipLog' => [],
        'log' => true,
        'trace' => true,
        'ignoredDeprecationPaths' => [],
    ],

    /* Debugger (stock docs trimmed for the 300-line cap). */
    'Debugger' => [
        'editor' => 'phpstorm',
    ],

    /* Email transports (stock docs trimmed for the 300-line cap). */
    'EmailTransport' => [
        'default' => [
            'className' => MailTransport::class,
            /* SMTP options (stock docs trimmed for the 300-line cap). */
            'host' => 'localhost',
            'port' => 25,
            'timeout' => 30,
            /* SMTP credentials via env (stock docs trimmed for the 300-line cap). */
            //'username' => null,
            //'password' => null,
            'client' => null,
            'tls' => false,
            'url' => env('EMAIL_TRANSPORT_DEFAULT_URL', null),
        ],
    ],

    /* Email profiles (stock docs trimmed for the 300-line cap). */
    'Email' => [
        'default' => [
            'transport' => 'default',
            'from' => 'you@localhost',
            /* Default email charset (stock docs trimmed for the 300-line cap). */
            //'charset' => 'utf-8',
            //'headerCharset' => 'utf-8',
        ],
    ],

    /* Database connections (stock docs trimmed for the 300-line cap). */
    'Datasources' => [
        /* Encoding notes (stock docs trimmed for the 300-line cap). */
        'default' => [
            'className' => Connection::class,
            'driver' => Postgres::class,
            'persistent' => false,
            'timezone' => 'UTC',

            /* MySQL charset flags (stock docs trimmed for the 300-line cap). */
            'encoding' => 'utf8',

            /* MySQL init notes (stock docs trimmed for the 300-line cap). */
            'flags' => [],
            'cacheMetadata' => true,
            'log' => false,

            /* Metadata performance (stock docs trimmed for the 300-line cap). */
            'quoteIdentifiers' => false,

            /* Test connection (stock docs trimmed for the 300-line cap). */
            //'init' => ['SET GLOBAL innodb_stats_on_metadata = 0'],
        ],

        /* Logging (stock docs trimmed for the 300-line cap). */
        'test' => [
            'className' => Connection::class,
            'driver' => Postgres::class,
            'persistent' => false,
            'timezone' => 'UTC',
            'encoding' => 'utf8',
            'flags' => [],
            'cacheMetadata' => true,
            'quoteIdentifiers' => false,
            'log' => false,
            //'init' => ['SET GLOBAL innodb_stats_on_metadata = 0'],
        ],
    ],

    /* Query log (stock docs trimmed for the 300-line cap). */
    'Log' => [
        'debug' => [
            'className' => FileLog::class,
            'path' => LOGS,
            'file' => 'debug',
            'url' => env('LOG_DEBUG_URL', null),
            'scopes' => null,
            'levels' => ['notice', 'info', 'debug'],
        ],
        'error' => [
            'className' => FileLog::class,
            'path' => LOGS,
            'file' => 'error',
            'url' => env('LOG_ERROR_URL', null),
            'scopes' => null,
            'levels' => ['warning', 'error', 'critical', 'alert', 'emergency'],
        ],
        // To enable this dedicated query log, you need to set your datasource's log flag to true
        'queries' => [
            'className' => FileLog::class,
            'path' => LOGS,
            'file' => 'queries',
            'url' => env('LOG_QUERIES_URL', null),
            'scopes' => ['cake.database.queries'],
        ],
    ],

    /* Session (8h portal timeout; cache handler via SESSION_HANDLER) (stock docs trimmed for the 300-line cap). */
    'Session' => (static function () {
        $base = [
            'defaults' => 'php',
            // Portal users work long shifts: 8h idle timeout instead of PHP's 24min,
            // otherwise every portal click after a pause bounces to login.
            'timeout' => 480,
        ];
        // SESSION_HANDLER=cache → shared sessions via Cache::session (needs Redis).
        // Required when running 2+ instances behind a load balancer.
        if (env('SESSION_HANDLER', '') === 'cache') {
            $base['defaults'] = 'cache';
            $base['handler'] = ['config' => 'session'];
        }
        return $base;
    })(),

    /* DebugKit (stock docs trimmed for the 300-line cap). */
    'DebugKit' => [
        'forceEnable' => filter_var(env('DEBUG_KIT_FORCE_ENABLE', false), FILTER_VALIDATE_BOOLEAN),
        'safeTld' => env('DEBUG_KIT_SAFE_TLD', null),
        'ignoreAuthorization' => env('DEBUG_KIT_IGNORE_AUTHORIZATION', false),
    ],

    /* TestSuite (stock docs trimmed for the 300-line cap). */
    'TestSuite' => [
        'errorLevel' => null,
        'fixtureStrategy' => null,
    ],
];
