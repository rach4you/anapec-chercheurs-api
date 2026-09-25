<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

/**
 * Convenience alias: `php artisan app:create-admin`.
 */
class CreateAdminUser extends CreateApiUser
{
    /**
     * @var string
     */
    protected $signature = 'app:create-admin {--email=} {--name=} {--password=}';

    /**
     * @var string
     */
    protected $description = 'Create a controlled ADMIN API user from the CLI';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        return parent::handle();
    }
}
