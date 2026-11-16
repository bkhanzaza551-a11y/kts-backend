<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * Test root route intentionally redirects to admin login.
     */
    public function test_root_redirects_to_admin_login(): void
    {
        $response = $this->get('/');

        $response->assertStatus(302);
        $response->assertRedirect(route('admin.login'));
    }

    /**
     * Test admin login page renders successfully.
     */
    public function test_admin_login_page_renders(): void
    {
        $response = $this->get(route('admin.login'));

        $response->assertStatus(200);
        $response->assertSee('KTS Markets');
    }
}
