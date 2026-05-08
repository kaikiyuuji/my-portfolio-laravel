<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Factories use Faker (dev dep). Skip in production where vendor is --no-dev.
        // Create the real admin user manually via `php artisan tinker` or a dedicated
        // command — never seed predictable credentials in prod.
        if (app()->environment('local', 'testing')) {
            User::factory()->create([
                'name' => 'Test User',
                'email' => 'test@example.com',
            ]);
        }

        $this->call([
            StackSeeder::class,
        ]);
    }
}
