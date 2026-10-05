<?php
declare(strict_types=1);
namespace App\Test\TestCase\Controller;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;
class VerifyFlowTest extends TestCase {
    use IntegrationTestTrait;
    public function testVerifyLodgeProxy(): void {
        $this->session(['User' => ['id' => 1, 'email' => 'a@x.com', 'role' => 'admin', 'name' => 'Admin'], 'auth_token' => 'dummy-token-1234567890']);
        $this->enableCsrfToken();
        $this->post('/admin/verification/lodge/42', ['status' => 'Active']);
        $code = $this->_response->getStatusCode();
        fwrite(STDERR, "\nLODGE-VERIFY HTTP=$code LOC=" . $this->_response->getHeaderLine('Location') . "\n");
        // backend down -> expect 302 with flash OR 502 json; never 500/exception
        $this->assertTrue(in_array($code, [301, 302, 303, 422, 502], true), "unexpected code $code");
    }
    public function testVerifyOwnerProxy(): void {
        $this->session(['User' => ['id' => 1, 'email' => 'a@x.com', 'role' => 'admin', 'name' => 'Admin'], 'auth_token' => 'dummy-token-1234567890']);
        $this->enableCsrfToken();
        $this->post('/admin/verification/owner/7', ['status' => 'approved']);
        $code = $this->_response->getStatusCode();
        fwrite(STDERR, "\nOWNER-VERIFY HTTP=$code LOC=" . $this->_response->getHeaderLine('Location') . "\n");
        $this->assertTrue(in_array($code, [301, 302, 303, 422, 502], true), "unexpected code $code");
    }
    public function testVerifyBadId(): void {
        $this->session(['User' => ['id' => 1, 'email' => 'a@x.com', 'role' => 'admin', 'name' => 'Admin'], 'auth_token' => 'dummy-token-1234567890']);
        $this->enableCsrfToken();
        $this->post('/admin/verification/lodge/!!!', ['status' => 'Active']);
        $code = $this->_response->getStatusCode();
        fwrite(STDERR, "\nBADID-VERIFY HTTP=$code\n");
        $this->assertTrue(in_array($code, [301, 302, 303, 400], true), "unexpected code $code");
    }
}
