<?php
/**
 * Routes configuration.
 */

use Cake\Routing\Route\DashedRoute;
use Cake\Routing\RouteBuilder;

return function (RouteBuilder $routes): void {
    
    $routes->setRouteClass(DashedRoute::class);

    $routes->scope('/', function (RouteBuilder $builder): void {
        
        // Home
        $builder->connect('/', ['controller' => 'Pages', 'action' => 'index']);

        // Stays / Accommodations (Modular StaysController)
        $builder->connect('/hotel-list-01', ['controller' => 'Stays', 'action' => 'index']);
        $builder->connect('/hotels', ['controller' => 'Stays', 'action' => 'index']);
        $builder->connect('/stays', ['controller' => 'Stays', 'action' => 'index']);
        $builder->connect('/hotel-detail', ['controller' => 'Stays', 'action' => 'detail']);
        $builder->connect('/hotel-detail/{id}', 
            ['controller' => 'Stays', 'action' => 'detail'], 
            ['pass' => ['id'], 'id' => '\d+']
        );

        // Destinations
        $builder->connect('/destination-01', ['controller' => 'Stays', 'action' => 'destination01']);
        $builder->connect('/destinations', ['controller' => 'Stays', 'action' => 'destination01']);
        $builder->connect('/destination-detail', ['controller' => 'Destination', 'action' => 'detail']);
        $builder->connect('/destination-detail/{slug}', 
            ['controller' => 'Destination', 'action' => 'detail'], 
            ['pass' => ['slug'], 'slug' => '[^/]+']
        );

        // Host / Property Onboarding
        $builder->connect('/join-us', ['controller' => 'Pages', 'action' => 'joinUs']);
        $builder->connect('/add-listing', ['controller' => 'Pages', 'action' => 'addListing']);
        $builder->connect('/add-listing-step-02', ['controller' => 'Pages', 'action' => 'addListingStep02']);
        $builder->connect('/add-listing-step-03', ['controller' => 'Pages', 'action' => 'addListingStep03']);

        // Booking Checkout Flow (Modular BookingsController + Pages Aliases)
        $builder->connect('/booking-page', ['controller' => 'Bookings', 'action' => 'bookingPage']);
        $builder->connect('/bookingpage-02', ['controller' => 'Bookings', 'action' => 'bookingpage02']);
        $builder->connect('/bookingpage-03', ['controller' => 'Bookings', 'action' => 'bookingpage03']);
        $builder->connect('/bookingpage-success', ['controller' => 'Bookings', 'action' => 'bookingpageSuccess']);
        $builder->connect('/booking-payment', ['controller' => 'Bookings', 'action' => 'paymentPending']);
        $builder->connect('/booking-payment/status', ['controller' => 'Bookings', 'action' => 'paymentStatus'], ['_method' => 'GET']);
        $builder->connect('/booking-page', ['controller' => 'Pages', 'action' => 'bookingPage']);
        $builder->connect('/bookingpage-02', ['controller' => 'Pages', 'action' => 'bookingpage02']);
        $builder->connect('/bookingpage-03', ['controller' => 'Pages', 'action' => 'bookingpage03']);
        $builder->connect('/bookingpage-success', ['controller' => 'Pages', 'action' => 'bookingpageSuccess']);

        // User Account Dashboard (Modular AccountController)
        $builder->connect('/menu', ['controller' => 'Account', 'action' => 'menu']);
        $builder->connect('/profile-menu', ['controller' => 'Account', 'action' => 'menu']);
        $builder->connect('/my-profile', ['controller' => 'Account', 'action' => 'myProfile']);
        $builder->connect('/security', ['controller' => 'Account', 'action' => 'accountSecurity']);
        $builder->connect('/profile/password-and-security', ['controller' => 'Account', 'action' => 'accountSecurity']);
        $builder->connect('/my-booking', ['controller' => 'Account', 'action' => 'myBooking']);
        $builder->connect('/bookings', ['controller' => 'Account', 'action' => 'myBooking']);
        $builder->connect('/my-booking/find', ['controller' => 'Account', 'action' => 'findBooking'], ['_method' => 'POST']);
        $builder->connect('/my-booking/cancel', ['controller' => 'Account', 'action' => 'cancelBooking'], ['_method' => 'POST']);

        $builder->connect('/payment-detail', ['controller' => 'Account', 'action' => 'paymentDetail']);
        $builder->connect('/my-wishlists', ['controller' => 'Account', 'action' => 'myWishlists']);
        $builder->connect('/favourites', ['controller' => 'Account', 'action' => 'myWishlists']);
        $builder->connect('/favorites', ['controller' => 'Account', 'action' => 'myWishlists']);
        $builder->connect('/recently-viewed', ['controller' => 'Account', 'action' => 'recentlyViewed']);
        $builder->connect('/search-preferences', ['controller' => 'Account', 'action' => 'searchPreferences']);
        $builder->connect('/notifications', ['controller' => 'Account', 'action' => 'notifications']);
        $builder->connect('/language-and-currency', ['controller' => 'Account', 'action' => 'languageAndCurrency']);
        $builder->connect('/language-currency', ['controller' => 'Account', 'action' => 'languageAndCurrency']);
        $builder->connect('/settings', ['controller' => 'Account', 'action' => 'settings']);
        $builder->connect('/delete-account', ['controller' => 'Account', 'action' => 'deleteAccount']);

        // Authentication Flow
        $builder->connect('/login', ['controller' => 'Pages', 'action' => 'login']);
        $builder->connect('/signup', ['controller' => 'Pages', 'action' => 'signup']);
        $builder->connect('/logout', ['controller' => 'Users', 'action' => 'logout']);
        $builder->connect('/users/logout', ['controller' => 'Users', 'action' => 'logout']);
        $builder->connect('/users', ['controller' => 'Users', 'action' => 'index']);
        $builder->connect('/forgot-password', ['controller' => 'Pages', 'action' => 'forgotPassword']);
        $builder->connect('/two-factor-auth', ['controller' => 'Pages', 'action' => 'twoFactorAuth']);
        $builder->connect('/reset-password', ['controller' => 'Pages', 'action' => 'resetPassword']);

        // Support & Info Pages
        $builder->connect('/about-us', ['controller' => 'Pages', 'action' => 'aboutUs']);
        $builder->connect('/how-we-work', ['controller' => 'Pages', 'action' => 'howWeWork']);
        $builder->connect('/help-center', ['controller' => 'Pages', 'action' => 'helpCenter']);
        $builder->connect('/faq', ['controller' => 'Pages', 'action' => 'faq']);
        $builder->connect('/404', ['controller' => 'Pages', 'action' => 'notFound']);
        $builder->connect('/privacy-policy', ['controller' => 'Pages', 'action' => 'privacyPolicy']);
        $builder->connect('/terms-of-service', ['controller' => 'Pages', 'action' => 'termsOfService']);
        $builder->connect('/terms-of-use', ['controller' => 'Pages', 'action' => 'termsOfService']);
        $builder->connect('/terms', ['controller' => 'Pages', 'action' => 'termsOfService']);

        // Contact
        $builder->connect('/contact', ['controller' => 'Pages', 'action' => 'contactV1']);
        $builder->connect('/contact-v1', ['controller' => 'Pages', 'action' => 'contactV1']);
        $builder->connect('/contact/submit', ['controller' => 'Contact', 'action' => 'submit'], ['_method' => 'POST']);

        // Universal API Proxy Route (bridges frontend /api/* to backend microservice)
        $builder->connect('/api/**', ['controller' => 'Pages', 'action' => 'apiProxy']);

        /*
         * Fallback routes
         */
        $builder->connect('/pages/*', 'Pages::display');
        $builder->fallbacks();
    });
};
