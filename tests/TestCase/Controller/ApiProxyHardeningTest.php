<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use App\Controller\PagesController;
use Cake\Http\ServerRequest;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

/**
 * ApiProxyHardeningTest — the /api/** proxy must only forward exact or true
 * sub-paths of the allowlisted resources. Lookalike prefixes
 * ("/properties-evil") and sensitive backend paths ("/bookings/create",
 * "/login", "/admin/*") are rejected with 403 before any backend call.
 */
class ApiProxyHardeningTest extends TestCase
{
    use IntegrationTestTrait;

    public function testLookalikePrefixIsRejected(): void
    {
        $this->get('/api/properties-evil');
        $this->assertResponseCode(403);
    }

    public function testSensitiveBackendPathsAreNotProxied(): void
    {
        $this->get('/api/bookings/create');
        $this->assertResponseCode(403);

        $this->get('/api/login');
        $this->assertResponseCode(403);

        $this->get('/api/admin/users');
        $this->assertResponseCode(403);
    }

    public function testProxyPathMatcherIsStrict(): void
    {
        $controller = new PagesController(new ServerRequest());
        $m = new \ReflectionMethod(PagesController::class, 'proxyPathMatches');
        $m->setAccessible(true);

        $this->assertTrue($m->invoke($controller, '/properties', '/properties'));
        $this->assertTrue($m->invoke($controller, '/properties/5', '/properties'));
        $this->assertFalse($m->invoke($controller, '/properties-evil', '/properties'));
        $this->assertFalse($m->invoke($controller, '/propertiesXYZ', '/properties'));
        $this->assertFalse($m->invoke($controller, '/bookings/create', '/bookings/calculate'));
    }
}
