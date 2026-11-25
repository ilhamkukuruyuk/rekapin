<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_guests_are_redirected_from_the_homepage_to_login(): void
    {
        $this->get('/')
            ->assertRedirect(route('login'));
    }
}
