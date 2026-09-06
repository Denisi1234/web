<?php
/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link          https://cakephp.org CakePHP(tm) Project
 * @since         0.10.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 */

// Fast, reliable static file server for root entrypoint
$uriPath = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?? '';
$decodedPath = rawurldecode($uriPath);

if ($decodedPath !== '' && $decodedPath !== '/' && !str_contains($decodedPath, '..')) {
    $staticCandidates = [
        __DIR__ . '/webroot' . $decodedPath,
        __DIR__ . $decodedPath,
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

require __DIR__ . DIRECTORY_SEPARATOR . 'webroot' . DIRECTORY_SEPARATOR . 'index.php';
