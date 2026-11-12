<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * TC-ENV-06: root route sengaja mengembalikan 302 (redirect), bukan 200.
     * Lihat BUG-01. Assertion diperkuat untuk memverifikasi target redirect.
     */
    public function test_the_application_root_redirects_guest(): void
    {
        $response = $this->get('/');

        $response->assertStatus(302);
        $response->assertRedirect(route('login'));
    }
}
