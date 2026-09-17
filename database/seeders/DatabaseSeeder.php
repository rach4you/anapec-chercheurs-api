<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Initial Web Services are seeded idempotently. No production users
        // are created here; the first ADMIN is created via the CLI
        // (`php artisan app:create-api-user`).
        $this->call(WebServiceSeeder::class);
    }
}
