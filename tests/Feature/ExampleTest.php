<?php

namespace Tests\Feature;

use Database\Seeders\PortfolioSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_portfolio_seeder_creates_profile_and_publications(): void
    {
        $this->seed(PortfolioSeeder::class);

        $this->assertDatabaseCount('profiles', 1);
        $this->assertDatabaseCount('publications', 3);
        $this->assertDatabaseHas('users', [
            'email' => config('admin_security.login_email', 'admin@portfolio.local'),
        ]);
    }
}
