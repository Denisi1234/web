<?php
declare(strict_types=1);

/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link      https://cakephp.org CakePHP(tm) Project
 * @since     3.3.0
 * @license   https://opensource.org/licenses/mit-license.php MIT License
 */
namespace App;

use Cake\Core\Configure;
use Cake\Core\ContainerInterface;
use Cake\Datasource\FactoryLocator;
use Cake\Error\Middleware\ErrorHandlerMiddleware;
use Cake\Http\BaseApplication;
use Cake\Http\Middleware\BodyParserMiddleware;
use Cake\Http\Middleware\CsrfProtectionMiddleware;
use Cake\Http\MiddlewareQueue;
use Cake\ORM\Locator\TableLocator;
use Cake\Routing\Middleware\AssetMiddleware;
use Cake\Routing\Middleware\RoutingMiddleware;

/**
 * Application setup class.
 *
 * This defines the bootstrapping logic and middleware layers you
 * want to use in your application.
 *
 * @extends \Cake\Http\BaseApplication<\App\Application>
 */
class Application extends BaseApplication
{
    /**
     * Load all the application configuration and bootstrap logic.
     *
     * @return void
     */
    public function bootstrap(): void
    {
        // Call parent to load bootstrap from files.
        parent::bootstrap();

        if (PHP_SAPI !== 'cli') {
            // The bake plugin requires fallback table classes to work properly
            FactoryLocator::add('Table', (new TableLocator())->allowFallbackClass(false));
        }
    }

    /**
     * Setup the middleware queue your application will use.
     *
     * @param \Cake\Http\MiddlewareQueue $middlewareQueue The middleware queue to setup.
     * @return \Cake\Http\MiddlewareQueue The updated middleware queue.
     */
    public function middleware(MiddlewareQueue $middlewareQueue): MiddlewareQueue
    {
        $middlewareQueue
            // Catch any exceptions in the lower layers,
            // and make an error page/response
            ->add(new ErrorHandlerMiddleware(Configure::read('Error'), $this))

            // Handle plugin/theme assets like CakePHP normally does.
            ->add(new AssetMiddleware([
                'cacheTime' => Configure::read('Asset.cacheTime'),
            ]))

            // Add routing middleware.
            // If you have a large number of routes connected, turning on routes
            // caching in production could improve performance.
            // See https://github.com/CakeDC/cakephp-cached-routing
            ->add(new RoutingMiddleware($this))

            // Parse various types of encoded request bodies so that they are
            // available as array through $request->getData()
            // https://book.cakephp.org/5/en/controllers/middleware.html#body-parser-middleware
            ->add(new BodyParserMiddleware());

        // Cross Site Request Forgery (CSRF) Protection Middleware
        // https://book.cakephp.org/5/en/security/csrf.html#cross-site-request-forgery-csrf-middleware
        $csrf = new CsrfProtectionMiddleware([
            'httponly' => true,
        ]);
        $csrf->skipCheckCallback(function ($request) {
            $path = $request->getPath();
            // Exact matches only — str_contains is fragile (e.g. /my-login would bypass). Only these legacy endpoints skip CSRF.
            if (
                $path === '/login' ||
                $path === '/logout' ||
                $path === '/contact/submit' ||
                $path === '/bookingpage-02' ||
                $path === '/bookingpage-03' ||
                $path === '/booking-page' ||
                $path === '/booking-payment/dispatch' ||
                $path === '/booking-payment/status' ||
                $path === '/my-profile' ||
                $path === '/security' ||
                $path === '/my-booking/find' ||
                $path === '/my-booking/cancel' ||
                $path === '/recently-viewed/clear' ||
                str_starts_with($path, '/recently-viewed/remove') ||
                $path === '/wishlist' ||
                str_starts_with($path, '/wishlist/') ||
                $path === '/wishlist-lists' ||
                $path === '/api/bookings/create' ||
                str_starts_with($path, '/api/')
            ) {
                return true;
            }
            return false;
        });

        $middlewareQueue->add($csrf);

        // Security headers — HSTS, X-Frame-Options, etc. (prod hardening)
        $middlewareQueue->add(function ($request, $handler) {
            $response = $handler->handle($request);
            return $response
                ->withHeader('X-Frame-Options', 'SAMEORIGIN')
                ->withHeader('X-Content-Type-Options', 'nosniff')
                ->withHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
                ->withHeader('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload')
                ->withHeader('Permissions-Policy', 'geolocation=(), microphone=(), camera=()');
        });

        return $middlewareQueue;
    }

    /**
     * Register application container services.
     *
     * @param \Cake\Core\ContainerInterface $container The Container to update.
     * @return void
     * @link https://book.cakephp.org/5/en/development/dependency-injection.html#dependency-injection
     */
    public function services(ContainerInterface $container): void
    {
    }
}
