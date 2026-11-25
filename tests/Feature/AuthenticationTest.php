<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_login_page_submits_to_the_authentication_endpoint(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('method="POST"', false)
            ->assertSee('action="'.route('login.post').'"', false)
            ->assertSee('name="_token"', false);
    }

    public function test_legacy_role_login_urls_redirect_to_the_single_login_page(): void
    {
        foreach (['anggota', 'bendahara', 'psdi', 'presbem'] as $role) {
            $this->get(route("{$role}.login"))
                ->assertRedirect(route('login'));
        }
    }

    public function test_quick_login_shows_every_seeded_demo_account(): void
    {
        $response = $this->get(route('login'))->assertOk();

        foreach ([
            'adkeu@bem-filkom.ub.ac.id',
            'psdi@bem-filkom.ub.ac.id',
            'daffa@bem-filkom.ub.ac.id',
            'ahmad@bem-filkom.ub.ac.id',
            'claudio@bem-filkom.ub.ac.id',
            'budi@student.ub.ac.id',
            'citra@student.ub.ac.id',
            'dimas@student.ub.ac.id',
            'fajar@student.ub.ac.id',
            'gita@student.ub.ac.id',
        ] as $email) {
            $response->assertSee($email);
        }

        $response->assertDontSee('budi@bem-filkom.ub.ac.id');
    }

    public function test_login_page_hides_demo_credentials_in_production(): void
    {
        $this->app->detectEnvironment(fn () => 'production');

        $this->get(route('login'))
            ->assertOk()
            ->assertDontSee('Akun Demo Cepat')
            ->assertDontSee('anggota123')
            ->assertDontSee('claudio@bem-filkom.ub.ac.id');
    }

    public function test_member_dashboard_shows_the_authenticated_users_profile(): void
    {
        $member = User::factory()->create([
            'name' => 'Ananda',
            'nim' => '255150700111003',
            'role' => 'anggota',
        ]);

        $this->actingAs($member)
            ->withSession(['last_activity_at' => now()->timestamp])
            ->get(route('anggota.dashboard'))
            ->assertOk()
            ->assertSee('Ananda')
            ->assertSee('255150700111003')
            ->assertDontSee('Budi Santoso');
    }

    public function test_users_are_authenticated_and_redirected_to_their_role_dashboard(): void
    {
        $roleDashboards = [
            'anggota' => 'anggota.dashboard',
            'bendahara' => 'bendahara.dashboard',
            'adkeu' => 'bendahara.dashboard',
            'psdi' => 'psdi.dashboard',
            'presbem' => 'presbem.dashboard',
        ];

        foreach ($roleDashboards as $role => $dashboardRoute) {
            $password = 'correct-password';
            $user = User::factory()->create([
                'role' => $role,
                'password' => $password,
            ]);

            $this->post(route('login.post'), [
                'email' => $user->email,
                'password' => $password,
            ])->assertRedirect(route($dashboardRoute));

            $this->assertAuthenticatedAs($user);
            $this->post(route('logout'));
        }
    }

    public function test_admin_is_authenticated_and_redirected_to_account_creation(): void
    {
        $password = 'correct-password';
        $admin = User::factory()->create([
            'role' => 'admin',
            'password' => $password,
        ]);

        $this->post(route('login.post'), [
            'email' => $admin->email,
            'password' => $password,
        ])->assertRedirect(route('admin.accounts.create'));

        $this->assertAuthenticatedAs($admin);
    }

    public function test_a_demo_password_for_another_role_does_not_authenticate_the_user(): void
    {
        $user = User::factory()->create([
            'role' => 'bendahara',
            'password' => 'correct-password',
        ]);

        $this->from(route('login'))
            ->post(route('login.post'), [
                'email' => $user->email,
                'password' => 'psdi123',
            ])
            ->assertRedirect(route('login'))
            ->assertSessionHas('error');

        $this->assertGuest();
    }

    public function test_users_cannot_access_another_roles_dashboard(): void
    {
        $user = User::factory()->create(['role' => 'anggota']);

        $this->actingAs($user)
            ->withSession(['last_activity_at' => now()->timestamp])
            ->get(route('bendahara.dashboard'))
            ->assertRedirect(route('anggota.dashboard'))
            ->assertSessionHas('error');
    }

    public function test_authenticated_user_can_log_out_using_post(): void
    {
        $user = User::factory()->create(['role' => 'anggota']);

        $this->actingAs($user)
            ->withSession(['last_activity_at' => now()->timestamp])
            ->post(route('logout'))
            ->assertRedirect(route('login'))
            ->assertSessionHas('success');

        $this->assertGuest();
    }

    public function test_logout_does_not_accept_get_requests(): void
    {
        $this->get(route('logout'))->assertMethodNotAllowed();
    }

    public function test_inactive_user_is_logged_out_after_idle_timeout(): void
    {
        $user = User::factory()->create(['role' => 'anggota']);

        $this->actingAs($user)
            ->withSession(['last_activity_at' => now()->subMinutes(31)->timestamp])
            ->get(route('anggota.dashboard'))
            ->assertRedirect(route('login'))
            ->assertSessionHas('error', 'Sesi Anda berakhir karena tidak aktif. Silakan masuk kembali.');

        $this->assertGuest();
    }

    public function test_authenticated_activity_refreshes_idle_timer(): void
    {
        $user = User::factory()->create(['role' => 'anggota']);

        $this->actingAs($user)
            ->withSession(['last_activity_at' => now()->subMinutes(29)->timestamp])
            ->get(route('anggota.dashboard'))
            ->assertOk()
            ->assertSessionHas('last_activity_at', now()->timestamp);
    }
}
