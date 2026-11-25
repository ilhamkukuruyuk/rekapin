<?php

namespace Tests\Feature;

use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use LogicException;
use Tests\TestCase;

class ProductionSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_demo_user_seeder_is_blocked_in_production(): void
    {
        $this->app->detectEnvironment(fn () => 'production');

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Demo accounts must not be seeded in production.');

        (new UserSeeder)->run();
    }
}
