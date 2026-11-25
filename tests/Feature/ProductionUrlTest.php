<?php

namespace Tests\Feature;

use App\Providers\AppServiceProvider;
use Tests\TestCase;

class ProductionUrlTest extends TestCase
{
    public function test_production_urls_use_https(): void
    {
        $this->app->detectEnvironment(fn (): string => 'production');
        (new AppServiceProvider($this->app))->boot();

        $this->assertSame('https://localhost/login', route('login'));
        $this->assertSame('https://localhost/build/assets/app.css', asset('build/assets/app.css'));
    }
}
