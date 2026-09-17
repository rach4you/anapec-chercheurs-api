<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class CreateApiUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:create-api-user
                            {--role=admin}
                            {--email=}
                            {--name=}
                            {--password=}
                            {--inactive}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a controlled API user (admin or user) from the CLI';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $email = $this->option('email') ?: $this->ask('Email');
        $name = $this->option('name') ?: $this->ask('Full name');
        $role = strtolower($this->option('role')) ?: User::ROLE_ADMIN;

        if (! in_array($role, User::allowedRoles(), true)) {
            $this->error('Invalid role. Allowed: '.implode(', ', User::allowedRoles()));

            return self::FAILURE;
        }

        if (User::query()->where('email', $email)->exists()) {
            $this->error("User with email {$email} already exists.");

            return self::FAILURE;
        }

        $password = $this->option('password')
            ?: $this->secret('Password');

        if (strlen($password) < 8) {
            $this->error('Password must be at least 8 characters.');

            return self::FAILURE;
        }

        $user = User::query()->create([
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'role' => $role,
            'is_active' => ! (bool) $this->option('inactive'),
        ]);

        $this->info('Created API user:');
        $this->line('  Name: '.$user->name);
        $this->line('  Email: '.$user->email);
        $this->line('  Role: '.$user->role);
        $this->line('  Active: '.($user->is_active ? 'yes' : 'no'));

        if ($role === User::ROLE_ADMIN) {
            $adminCount = User::query()->where('role', User::ROLE_ADMIN)->count();

            if ($adminCount > 1) {
                $this->warn("Heads up: there are now {$adminCount} admin users.");
            }
        }

        return self::SUCCESS;
    }
}
