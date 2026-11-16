<?php

namespace Tests\Feature;

use Tests\TestCase;

class SupportAndLegalPagesTest extends TestCase
{
    public function test_public_can_fetch_legal_pages_list(): void
    {
        $response = $this->getJson('/api/v1/legal');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data',
        ]);
    }

    public function test_public_can_view_privacy_policy_page(): void
    {
        $response = $this->get('/privacy-policy');
        $response->assertStatus(200);
        $response->assertSee('Privacy Policy');
    }

    public function test_public_can_view_terms_and_conditions_page(): void
    {
        $response = $this->get('/terms-conditions');
        $response->assertStatus(200);
        $response->assertSee('Terms');
    }

    public function test_public_can_view_delete_account_page(): void
    {
        $response = $this->get('/delete-account');
        $response->assertStatus(200);
        $response->assertSee('Delete Account');
    }
}
