<?php
/**
 * The Front Controller for handling every request
 *
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link          https://cakephp.org CakePHP(tm) Project
 * @since         0.2.9
 * @license       MIT License (https://opensource.org/licenses/mit-license.php)
 */

// Fast, reliable static file server for production containers & PHP built-in server
$uriPath = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?? '';
$decodedPath = rawurldecode($uriPath);

if ($decodedPath !== '' && $decodedPath !== '/' && !str_contains($decodedPath, '..')) {
    $staticCandidates = [
        __DIR__ . $decodedPath,
        dirname(__DIR__) . '/webroot' . $decodedPath,
        dirname(__DIR__) . $decodedPath,
    ];

    foreach ($staticCandidates as $candidate) {
        if (is_file($candidate)) {
            $ext = strtolower(pathinfo($candidate, PATHINFO_EXTENSION));
            $mimes = [
                'css' => 'text/css; charset=UTF-8',
                'js' => 'application/javascript; charset=UTF-8',
                'mjs' => 'application/javascript; charset=UTF-8',
                'json' => 'application/json',
                'png' => 'image/png',
                'jpg' => 'image/jpeg',
                'jpeg' => 'image/jpeg',
                'gif' => 'image/gif',
                'svg' => 'image/svg+xml',
                'webp' => 'image/webp',
                'ico' => 'image/x-icon',
                'woff' => 'font/woff',
                'woff2' => 'font/woff2',
                'ttf' => 'font/ttf',
                'eot' => 'application/vnd.ms-fontobject',
                'otf' => 'font/otf',
                'pdf' => 'application/pdf',
                'xml' => 'application/xml',
                'txt' => 'text/plain; charset=UTF-8',
            ];
            $contentType = $mimes[$ext] ?? 'application/octet-stream';
            header('Content-Type: ' . $contentType);
            header('Content-Length: ' . filesize($candidate));
            header('Cache-Control: public, max-age=604800');
            readfile($candidate);
            exit;
        }
    }
}

require dirname(__DIR__) . '/vendor/autoload.php';

use App\Application;
use Cake\Http\Server;

// Bind your application to the server.
$server = new Server(new Application(dirname(__DIR__) . '/config'));

// Run the request/response through the application and emit the response.
$server->emit($server->run());
