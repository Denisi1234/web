<?php

namespace App\Service;

use Cake\Http\ServerRequest;

/**
 * Preserves the visitor's intended account type across the signup journey.
 *
 * The header login modal always linked "Create Account" to a bare /signup, so a
 * prospective host who arrived via /join-us (or was already looking at the host
 * area) was silently dropped into the guest signup and landed on the customer
 * dashboard.
 *
 * Intent is resolved from, in order:
 *   1. an explicit ?role=owner / ?role=host query parameter
 *   2. a previously remembered intent (session)
 *   3. the referring page being a host-oriented one
 * and is remembered for the rest of the visit once established.
 */
class HostIntent
{
    public const ROLE_OWNER    = 'owner';
    public const ROLE_CUSTOMER = 'customer';

    private const SESSION_KEY = 'signup_intent_role';
    private const COOKIE      = 'fn_signup_role';

    /** Paths that mean "the visitor is on a host journey". */
    private const HOST_PATHS = [
        '/join-us',
        '/host',
        '/admin',
    ];

    /**
     * @return string self::ROLE_OWNER|self::ROLE_CUSTOMER
     */
    public static function resolve(ServerRequest $request, ?string $sessionRole = null): string
    {
        $requested = strtolower((string)($request->getQuery('role') ?? ''));
        if (in_array($requested, ['owner', 'host'], true)) {
            return self::ROLE_OWNER;
        }
        if ($requested === 'customer' || $requested === 'guest' || $requested === 'user') {
            return self::ROLE_CUSTOMER;
        }

        if ($sessionRole === self::ROLE_OWNER) {
            return self::ROLE_OWNER;
        }

        // Referring page on a host journey.
        $referer = (string)($request->getHeaderLine('Referer') ?? '');
        if ($referer !== '') {
            $path = (string)parse_url($referer, PHP_URL_PATH);
            foreach (self::HOST_PATHS as $hostPath) {
                if ($path === $hostPath || str_starts_with($path, $hostPath . '/')) {
                    return self::ROLE_OWNER;
                }
            }
        }

        // The page we are currently rendering is host-oriented.
        $current = (string)$request->getRequestTarget();
        $path = parse_url($current, PHP_URL_PATH) ?: $current;
        foreach (self::HOST_PATHS as $hostPath) {
            if ($path === $hostPath || str_starts_with($path, $hostPath . '/')) {
                return self::ROLE_OWNER;
            }
        }

        return self::ROLE_CUSTOMER;
    }

    /**
     * Signup URL for the current intent.
     *
     * @param mixed $url Cake's Url helper/instance
     */
    public static function signupUrl($url, ServerRequest $request, ?string $sessionRole = null): string
    {
        $role = self::resolve($request, $sessionRole);

        if ($role === self::ROLE_OWNER) {
            return $url->build('/signup?role=owner');
        }

        return $url->build('/signup');
    }

    /**
     * Where a freshly registered account should land.
     */
    public static function postSignupRedirect(ServerRequest $request, ?string $sessionRole = null): string
    {
        return self::resolve($request, $sessionRole) === self::ROLE_OWNER
            ? '/host/dashboard'
            : '/';
    }
}