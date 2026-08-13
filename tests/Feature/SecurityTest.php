<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecurityTest extends TestCase
{
    // `/` の参照や status エンドポイントの insert のためスキーマを保証（:memory: 順序依存の既存不具合の修正）
    use RefreshDatabase;

    public function test_security_headers_present_on_all_responses(): void
    {
        $response = $this->get('/');
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
    }

    public function test_xss_protection_header_present(): void
    {
        $response = $this->get('/');
        $response->assertHeader('X-XSS-Protection', '1; mode=block');
    }

    public function test_permissions_policy_header_present(): void
    {
        $response = $this->get('/');
        $response->assertHeader('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');
    }

    public function test_status_endpoint_returns_json_not_html(): void
    {
        $review = \App\Models\Review::create([
            'github_url' => 'https://github.com/test/repo',
            'owner'      => 'test',
            'repo'       => 'repo',
            'status'     => 'pending',
        ]);
        $response = $this->getJson("/reviews/{$review->id}/status");
        $response->assertHeader('Content-Type', 'application/json');
    }
}
