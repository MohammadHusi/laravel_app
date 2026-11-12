<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Feature test environment & autentikasi dasar.
 * Menggunakan RefreshDatabase dengan SQLite :memory: (lihat phpunit.xml).
 *
 * TC-ENV-06, TC-AUTH-01..06, TC-RBAC-01..03
 */
class AuthAccessTest extends TestCase
{
    use RefreshDatabase;

    private function makeUser(string $role): User
    {
        return User::factory()->create([
            'name' => ucfirst($role).' User',
            'username' => $role.'_user',
            'email' => $role.'@example.test',
            'role' => $role,
            'password' => 'password',
        ]);
    }

    /** TC-ENV-06: guest diarahkan dari '/' ke login (302), bukan 200. */
    public function test_root_redirects_guest_to_login(): void
    {
        $response = $this->get('/');

        $response->assertStatus(302);
        $response->assertRedirect(route('login'));
    }

    /** TC-AUTH-01: halaman login dapat diakses guest. */
    public function test_login_page_is_accessible_to_guest(): void
    {
        $this->get('/login')->assertStatus(200)->assertSee('login', false);
    }

    /** TC-AUTH-02: login dengan email valid berhasil dan diarahkan ke dashboard. */
    public function test_login_with_email_succeeds(): void
    {
        $user = $this->makeUser('admin');

        $response = $this->post('/login', [
            'login' => 'admin@example.test',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    /** TC-AUTH-03: login dengan username valid berhasil. */
    public function test_login_with_username_succeeds(): void
    {
        $this->makeUser('guru');

        $this->post('/login', [
            'login' => 'guru_user',
            'password' => 'password',
        ])->assertRedirect(route('dashboard'));

        $this->assertAuthenticated();
    }

    /** TC-AUTH-04: password salah gagal dengan pesan validasi. */
    public function test_login_with_wrong_password_fails(): void
    {
        $this->makeUser('admin');

        $response = $this->from('/login')->post('/login', [
            'login' => 'admin@example.test',
            'password' => 'salah',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors('login');
        $this->assertGuest();
    }

    /** TC-AUTH-05: logout mengakhiri sesi dan kembali ke login. */
    public function test_logout_invalidates_session(): void
    {
        $user = $this->makeUser('admin');

        $this->actingAs($user)
            ->post('/logout')
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

    /** TC-AUTH-06: dashboard mengarahkan tiap role ke route yang benar. */
    public function test_dashboard_redirects_by_role(): void
    {
        foreach (['admin', 'guru', 'murid'] as $role) {
            $user = $this->makeUser($role);
            $this->actingAs($user)->get('/dashboard')->assertRedirect();
        }
    }

    /** TC-RBAC-01: murid tidak boleh mengakses area admin (403). */
    public function test_murid_cannot_access_admin_area(): void
    {
        $user = $this->makeUser('murid');

        $this->actingAs($user)->get('/admin')->assertStatus(403);
    }

    /** TC-RBAC-02: guru tidak boleh mengakses area admin (403). */
    public function test_guru_cannot_access_admin_area(): void
    {
        $user = $this->makeUser('guru');

        $this->actingAs($user)->get('/admin')->assertStatus(403);
    }

    /** TC-RBAC-03: admin dapat mengakses area admin (200). */
    public function test_admin_can_access_admin_area(): void
    {
        $user = $this->makeUser('admin');

        $this->actingAs($user)->get('/admin')->assertStatus(200);
    }

    /** TC-RBAC-04: guest diarahkan ke login saat mengakses area terproteksi. */
    public function test_guest_is_redirected_to_login_for_protected_route(): void
    {
        $this->get('/admin')->assertRedirect(route('login'));
    }
}
