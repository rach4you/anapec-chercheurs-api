<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class CheckCinService
{
    /**
     * Check whether a researcher with the given CIN exists in SIGEC.
     *
     * READ-ONLY: uses a parameterized EXISTS subquery on the CHERCHEURS table.
     * The CIN column is VARCHAR2(15) and has a bitmap index in Oracle.
     */
    public function exists(string $cin): bool
    {
        $cin = trim($cin);

        if ($cin === '') {
            return false;
        }

        return DB::table('chercheurs')
            ->where('cin', $cin)
            ->exists();
    }
}
