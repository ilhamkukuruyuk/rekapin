<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AccountManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_admin_can_open_the_account_creation_form(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->withSession(['last_activity_at' => now()->timestamp])
            ->get(route('admin.accounts.create'))
            ->assertOk()
            ->assertSee('Tambah akun')
            ->assertSee('Anggota Biasa')
            ->assertDontSee('value="admin"', false);
    }

    public function test_admin_can_create_an_account_with_a_hashed_password(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->withSession(['last_activity_at' => now()->timestamp])
            ->post(route('admin.accounts.store'), [
                'name' => 'Anggota Baru',
                'email' => 'baru@example.com',
                'role' => 'anggota',
                'nim' => '255150700111099',
                'departemen' => 'Umum',
                'angkatan' => '2025',
                'jabatan' => 'Anggota Biasa',
                'password' => 'AkunBaru123',
                'password_confirmation' => 'AkunBaru123',
            ])
            ->assertRedirect(route('admin.accounts.create'))
            ->assertSessionHas('success', 'Akun berhasil ditambahkan.');

        $user = User::where('email', 'baru@example.com')->firstOrFail();

        $this->assertSame('anggota', $user->role);
        $this->assertSame('255150700111099', $user->nim);
        $this->assertTrue(Hash::check('AkunBaru123', $user->password));
    }

    public function test_admin_cannot_create_another_admin_or_an_invalid_role(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->withSession(['last_activity_at' => now()->timestamp])
            ->from(route('admin.accounts.create'))
            ->post(route('admin.accounts.store'), [
                'name' => 'Admin Tambahan',
                'email' => 'admin-baru@example.com',
                'role' => 'admin',
                'password' => 'AkunBaru123',
                'password_confirmation' => 'AkunBaru123',
            ])
            ->assertRedirect(route('admin.accounts.create'))
            ->assertSessionHasErrors('role');

        $this->assertDatabaseMissing('users', ['email' => 'admin-baru@example.com']);
    }

    public function test_admin_cannot_access_non_account_application_features(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->withSession(['last_activity_at' => now()->timestamp])
            ->get(route('anggota.dashboard'))
            ->assertRedirect(route('admin.accounts.create'))
            ->assertSessionHas('error');
    }

    public function test_non_admin_cannot_access_account_management(): void
    {
        $member = User::factory()->create(['role' => 'anggota']);

        $this->actingAs($member)
            ->withSession(['last_activity_at' => now()->timestamp])
            ->get(route('admin.accounts.create'))
            ->assertRedirect(route('anggota.dashboard'))
            ->assertSessionHas('error');

        $this->assertDatabaseCount('users', 1);
    }

    public function test_guest_is_redirected_to_login_from_account_management(): void
    {
        $this->get(route('admin.accounts.create'))
            ->assertRedirect(route('login'));
    }

    public function test_admin_can_be_bootstrapped_interactively_from_the_console(): void
    {
        $this->artisan('admin:create')
            ->expectsQuestion('Nama admin', 'Admin Console')
            ->expectsQuestion('Email admin', 'admin-console@example.com')
            ->expectsQuestion('Password admin', 'SecureAdmin123')
            ->expectsQuestion('Ulangi password admin', 'SecureAdmin123')
            ->expectsOutput('Akun admin berhasil dibuat.')
            ->assertExitCode(0);

        $admin = User::where('email', 'admin-console@example.com')->firstOrFail();

        $this->assertSame('admin', $admin->role);
        $this->assertTrue(Hash::check('SecureAdmin123', $admin->password));
    }

    public function test_admin_password_must_be_confirmed_and_meet_strength_rules(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->withSession(['last_activity_at' => now()->timestamp])
            ->from(route('admin.accounts.create'))
            ->post(route('admin.accounts.store'), [
                'name' => 'Anggota Baru',
                'email' => 'baru@example.com',
                'role' => 'anggota',
                'password' => 'weak',
                'password_confirmation' => 'different',
            ])
            ->assertRedirect(route('admin.accounts.create'))
            ->assertSessionHasErrors(['password']);

        $this->assertDatabaseMissing('users', ['email' => 'baru@example.com']);
    }
}
