<?php

namespace Database\Seeders;

use App\Models\WebService;
use App\Models\WebServiceDomain;
use Illuminate\Database\Seeder;

class WebServiceDomainSeeder extends Seeder
{
    /**
     * Seed the default Web Service Domain and attach the seeded services to it.
     * Idempotent: re-running will not duplicate.
     *
     * No user-domain permission rows are created here; a domain only affects a
     * user once an admin explicitly grants it. This keeps existing permissions
     * untouched.
     */
    public function run(): void
    {
        $domain = WebServiceDomain::query()->firstOrCreate(
            ['code' => 'CHERCHEURS'],
            [
                'name' => 'Chercheurs',
                'description' => 'Web Services relating to the researcher (chercheur) domain.',
                'is_active' => true,
                'sort_order' => 0,
            ]
        );

        $codes = [
            'WS_CHECK_CIN',
            'WS_PROFILE',
            'WS_CV',
            'WS_BILAN',
        ];

        $services = WebService::query()
            ->whereIn('code', $codes)
            ->pluck('id')
            ->all();

        foreach ($services as $serviceId) {
            $existing = $domain->webServices()->whereKey($serviceId)->exists();
            if (! $existing) {
                $domain->webServices()->attach($serviceId);
            }
        }
    }
}
