<?php

namespace Database\Seeders;

use App\Models\WebService;
use Illuminate\Database\Seeder;

class WebServiceSeeder extends Seeder
{
    /**
     * Seed the four initial Web Services. Idempotent: re-running will not duplicate.
     */
    public function run(): void
    {
        $services = [
            [
                'code' => 'WS_CHECK_CIN',
                'name' => 'Check CIN Document',
                'description' => 'Verify the authenticity of a national identity card (CIN).',
            ],
            [
                'code' => 'WS_PROFILE',
                'name' => 'Profile Search',
                'description' => 'Look up a researcher profile by identifier.',
            ],
            [
                'code' => 'WS_CV',
                'name' => 'Curriculum Vitae',
                'description' => 'Fetch the full CV of a researcher.',
            ],
            [
                'code' => 'WS_BILAN',
                'name' => 'Career Balance',
                'description' => 'Retrieve a summary / career balance for a researcher.',
            ],
        ];

        foreach ($services as $service) {
            WebService::query()->firstOrCreate(
                ['code' => $service['code']],
                [
                    'name' => $service['name'],
                    'description' => $service['description'],
                    'is_active' => true,
                ]
            );
        }
    }
}
