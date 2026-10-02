<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\WebService;
use App\Services\ActualisationService;
use App\Services\InscriptionService;
use Database\Factories\UserFactory;
use Database\Seeders\WebServiceSeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class ActualisationTest extends TestCase
{
    use DatabaseTransactions;

    protected $connectionsToTransact = [null];

    protected function setUp(): void
    {
        parent::setUp();
        $this->beginDatabaseTransaction();
        $this->seed(WebServiceSeeder::class);
    }

    private function userWithActualisationPermission(?string $email = null, bool $enabled = true, bool $active = true): User
    {
        $factory = UserFactory::new();

        if (! $active) {
            $factory->asInactive();
        }

        $user = $factory->create([
            'email' => $email ?? fake()->unique()->safeEmail(),
            'password' => 'secret123',
            'is_active' => $active,
        ]);

        $ws = WebService::query()->where('code', 'WS_ACTUALISATION')->firstOrFail();
        $user->webServices()->attach($ws->id, ['is_enabled' => $enabled]);

        return $user;
    }

    private function validInscriptionData(?string $cin = null, ?string $email = null): array
    {
        $data = $this->validActualisationData($cin, $email);
        $data['chercheur']['password'] = 'secret123';
        $data['chercheur']['date_inscription'] = '23-09-2026';
        $data['chercheur']['date_derniere_actualisation'] = '23-09-2026';
        return $data;
    }

    private function validActualisationData(?string $cin = null, ?string $email = null): array
    {
        return [
            'chercheur' => [
                'nationalite' => 0,
                'cin' => $cin ?? ('WSA_' . strtoupper(Str::random(8))),
                'nom_candidat' => 'Test',
                'prenom' => 'Rollback',
                'sexe' => 'M',
                'situation_familiale' => 'Celibataire',
                'date_naissance' => '18-09-1996',
                'adresse' => 'Test address Anapec 4 lotissement la colline',
                'ville_id' => 11,
                'commune_id' => 7728,
                'e_mail' => $email ?? (strtolower(Str::random(10)) . '@example.com'),
                'n_gsm' => '0656565656',
                'n_gsm2' => '0454545454',
                'tel' => '0455445454',
                'situation_p_r_emploi' => 'Sans emploi',
                'mois_chomage' => '4',
                'duree_chomage' => '2015',
                'handicape' => 'oui',
                'nature_handicape' => 'Aveugle',
                'competences_specifiques' => 'PhotoShop, DreamWeaver, Flash',
                'activites_extra_pro' => 'DEVELOPPEUR SENIOR DATA BASE ORACLE',
                'provenance' => 'F',
                'agence_id' => 123,
                'is_diplome' => 1,
            ],
            'mobilites' => [1, 2, 3, 4],
            'diplomes' => [
                [
                    'diplome_id' => 31,
                    'specialite_id' => 31295,
                    'option_diplome_id' => 312951587,
                    'groupe_etablissement_id' => 10,
                    'etablissement_id' => 723,
                    'date_optention' => '2018',
                    'commentaire' => 'BAC+2 TEST COMENTAIRE',
                ],
                [
                    'diplome_id' => 33,
                    'specialite_id' => 33452,
                    'option_diplome_id' => 334521520,
                    'groupe_etablissement_id' => 17,
                    'etablissement_id' => 731,
                    'date_optention' => '2017',
                    'commentaire' => 'BAC+4 TEST COMENTAIRE',
                ],
            ],
            'experiences' => [
                [
                    'date_debut' => '01-09-2023',
                    'date_fin' => '19-09-2024',
                    'ce_jour' => 0,
                    'employeur' => 'ORACLE',
                    'intitule_poste' => 'DATA BASE ADMIN',
                    'commentaire' => 'TEST DATA BASE ADMIN EXP',
                    'duree_experience_id' => 4,
                ],
                [
                    'date_debut' => '11-09-2025',
                    'ce_jour' => 1,
                    'employeur' => 'ANAPEC',
                    'intitule_poste' => 'DEVELOPPEUR',
                    'commentaire' => 'TEST DEVELOPPEUR',
                    'duree_experience_id' => 4,
                ],
            ],
            'bureautiques' => [1, 2, 3, 4],
            'permis' => [3, 1, 2],
            'langues' => [
                ['competence_linguistique_id' => 1, 'niveau' => 'Courant'],
                ['competence_linguistique_id' => 4, 'niveau' => 'Courant'],
                ['competence_linguistique_id' => 8, 'niveau' => 'Moyen'],
            ],
            'emplois_metiers' => [
                ['emploi_metier_id' => 47141],
            ],
        ];
    }

    private function createResearcher(?string $cin = null, ?string $email = null): array
    {
        $service = new InscriptionService;
        $data = $this->validInscriptionData($cin, $email);
        return $service->register($data);
    }

    // AUTHORIZATION TESTS

    // 1. Unauthenticated request → 401
    public function test_unauthenticated_request_returns_401(): void
    {
        $this->putJson('/api/v1/services/actualisation', [])
            ->assertStatus(401);
    }

    // 2. Inactive user → 401
    public function test_inactive_user_returns_401(): void
    {
        $user = $this->userWithActualisationPermission('inact_actualisation@example.com', true, false);

        $this->assertFalse($user->fresh()->is_active);

        $response = $this->actingAs($user, 'api')
            ->putJson('/api/v1/services/actualisation', ['chercheur' => ['cin' => 'TEST123']]);

        $response->assertStatus(401);
    }

    // 3. User without WS_ACTUALISATION permission → 403
    public function test_user_without_actualisation_permission_returns_403(): void
    {
        $user = UserFactory::new()->create([
            'email' => 'noactualisationperm@example.com',
            'password' => 'secret123',
        ]);

        $this->actingAs($user, 'api')
            ->putJson('/api/v1/services/actualisation', ['chercheur' => ['cin' => 'TEST123']])
            ->assertStatus(403);
    }

    // 4. User with disabled WS_ACTUALISATION permission → 403
    public function test_user_with_disabled_actualisation_permission_returns_403(): void
    {
        $user = $this->userWithActualisationPermission('disabledactualisation@example.com', false);

        $this->actingAs($user, 'api')
            ->putJson('/api/v1/services/actualisation', ['chercheur' => ['cin' => 'TEST123']])
            ->assertStatus(403);
    }

    // 5. WS_ACTUALISATION globally OFF → 403
    public function test_globally_off_actualisation_returns_403(): void
    {
        $user = $this->userWithActualisationPermission('globoffactualisation@example.com');

        $ws = WebService::query()->where('code', 'WS_ACTUALISATION')->firstOrFail();
        $ws->update(['is_active' => false]);

        $this->actingAs($user, 'api')
            ->putJson('/api/v1/services/actualisation', ['chercheur' => ['cin' => 'TEST123']])
            ->assertStatus(403);
    }

    // 6. Valid user + permission + global ON → allowed
    public function test_valid_user_with_permission_and_global_on_is_allowed(): void
    {
        $user = $this->userWithActualisationPermission('validactualisation@example.com');
        $cin = 'WSA_A' . strtoupper(Str::random(6));
        $this->createResearcher($cin);
        $data = $this->validActualisationData($cin);

        $response = $this->actingAs($user, 'api')
            ->putJson('/api/v1/services/actualisation', $data);

        $this->assertNotContains($response->status(), [401, 403]);
    }

    // BUSINESS LOGIC TESTS

    // TEST 1 — Complete successful actualisation.
    public function test_1_complete_actualisation_succeeds(): void
    {
        $user = $this->userWithActualisationPermission();
        $cin = 'WSA_OK_' . strtoupper(Str::random(6));
        $researcher = $this->createResearcher($cin);
        $chercheurId = $researcher['chercheur_id'];

        $row = DB::table('chercheurs')->where('id', $chercheurId)->first();
        $this->assertSame('Test', $row->nom_candidat);

        $newEmail = 'updated_' . strtolower(Str::random(8)) . '@example.com';
        $data = $this->validActualisationData($cin, $newEmail);
        $data['chercheur']['nom_candidat'] = 'UpdatedName';
        $data['chercheur']['prenom'] = 'UpdatedPrenom';
        $data['chercheur']['n_gsm'] = '0700000000';
        $data['chercheur']['adresse'] = 'New Address';

        $response = $this->actingAs($user, 'api')
            ->putJson('/api/v1/services/actualisation', $data);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Chercheur actualisé avec succès.')
            ->assertJsonPath('data.chercheur_id', $chercheurId)
            ->assertJsonPath('data.cin', $cin)
            ->assertJsonPath('data.email', $newEmail);

        $row = DB::table('chercheurs')->where('id', $chercheurId)->first();
        $this->assertSame('UpdatedName', $row->nom_candidat);
        $this->assertSame('UpdatedPrenom', $row->prenom);
        $this->assertSame($newEmail, $row->e_mail);
        $this->assertSame($newEmail, $row->vemail, 'vemail must mirror e_mail.');
        $this->assertSame('0700000000', $row->n_gsm);
        $this->assertSame('New Address', $row->adresse);
        $this->assertNotNull($row->date_derniere_actualisation);
    }

    // TEST 2 — CIN not found → 404.
    public function test_2_cin_not_found_returns_404(): void
    {
        $user = $this->userWithActualisationPermission();
        $cin = 'WSA_NF_' . strtoupper(Str::random(8));

        $data = $this->validActualisationData($cin);

        $response = $this->actingAs($user, 'api')
            ->putJson('/api/v1/services/actualisation', $data);

        $response->assertStatus(404)
            ->assertJsonPath('success', false)
            ->assertJsonPath('code', 'CIN_NOT_FOUND')
            ->assertJsonPath('message', 'Aucun chercheur trouvé avec ce CIN.');
    }

    // TEST 3 — Duplicate email (different CIN) → 409.
    public function test_3_duplicate_email_different_cin_returns_409(): void
    {
        $user = $this->userWithActualisationPermission();

        $sharedEmail = 'shared_' . strtolower(Str::random(8)) . '@example.com';
        $this->createResearcher('WSA_DUPL_A', $sharedEmail);

        $cinB = 'WSA_DUPL_B';
        $this->createResearcher($cinB, 'unique_' . strtolower(Str::random(8)) . '@example.com');

        $data = $this->validActualisationData($cinB, $sharedEmail);

        $response = $this->actingAs($user, 'api')
            ->putJson('/api/v1/services/actualisation', $data);

        $response->assertStatus(409)
            ->assertJsonPath('success', false)
            ->assertJsonPath('code', 'EMAIL_ALREADY_EXISTS')
            ->assertJsonPath('message', 'Un chercheur existe déjà avec cet email.');
    }

    // TEST 4 — Same email (no change) → no conflict.
    public function test_4_same_email_no_conflict(): void
    {
        $user = $this->userWithActualisationPermission();
        $cin = 'WSA_SAME_' . strtoupper(Str::random(6));
        $email = 'same_' . strtolower(Str::random(8)) . '@example.com';

        $this->createResearcher($cin, $email);

        $data = $this->validActualisationData($cin, $email);

        $response = $this->actingAs($user, 'api')
            ->putJson('/api/v1/services/actualisation', $data);

        $response->assertStatus(200)
            ->assertJsonPath('success', true);
    }

    // TEST 5 — Immutable fields are not changed.
    public function test_5_immutable_fields_not_changed(): void
    {
        $user = $this->userWithActualisationPermission();
        $cin = 'WSA_I' . strtoupper(Str::random(6));
        $researcher = $this->createResearcher($cin);
        $chercheurId = $researcher['chercheur_id'];

        $row = DB::table('chercheurs')->where('id', $chercheurId)->first();
        $originalPassword = $row->password;
        $originalDateInscription = $row->date_inscription;

        $data = $this->validActualisationData($cin);
        $data['chercheur']['password'] = 'new_password';
        $data['chercheur']['date_inscription'] = '99-99-9999';
        $data['chercheur']['nom_candidat'] = 'UpdatedName';

        $response = $this->actingAs($user, 'api')
            ->putJson('/api/v1/services/actualisation', $data);
        $response->assertStatus(200);

        $row = DB::table('chercheurs')->where('id', $chercheurId)->first();
        $this->assertSame($originalPassword, $row->password, 'Password must not be updated.');
        $this->assertEquals($originalDateInscription, $row->date_inscription, 'Date inscription must be preserved.');
        $this->assertEquals($chercheurId, (int) $row->id, 'Chercheur ID must not change.');
        $this->assertSame('UpdatedName', $row->nom_candidat, 'Nom candidat must be updated.');
    }

    // TEST 6 — Relations replaced when provided.
    public function test_6_relations_replaced_when_provided(): void
    {
        $user = $this->userWithActualisationPermission();
        $cin = 'WSA_REPL_' . strtoupper(Str::random(6));
        $researcher = $this->createResearcher($cin);
        $chercheurId = $researcher['chercheur_id'];

        $this->assertSame(4, DB::table('chercheurs_mobilites')->where('chercheur_id', $chercheurId)->count());
        $this->assertSame(2, DB::table('avoir_diplomes')->where('chercheur_id', $chercheurId)->count());

        $data = $this->validActualisationData($cin);
        $data['mobilites'] = [5, 6];
        $data['diplomes'] = [
            [
                'diplome_id' => 35,
                'specialite_id' => 35000,
                'option_diplome_id' => 350000001,
                'groupe_etablissement_id' => 20,
                'etablissement_id' => 800,
                'date_optention' => '2020',
                'commentaire' => 'MASTER TEST',
            ],
        ];

        $response = $this->actingAs($user, 'api')
            ->putJson('/api/v1/services/actualisation', $data);
        $response->assertStatus(200);

        $mobilites = DB::table('chercheurs_mobilites')
            ->where('chercheur_id', $chercheurId)
            ->pluck('mobilite_id')
            ->toArray();
        sort($mobilites);
        $this->assertEquals([5, 6], $mobilites);

        $this->assertSame(1, DB::table('avoir_diplomes')->where('chercheur_id', $chercheurId)->count());
        $diplome = DB::table('avoir_diplomes')
            ->where('chercheur_id', $chercheurId)
            ->first();
        $this->assertEquals(35, (int) $diplome->diplome_id);

        // cin is re-written on all relation rows
        $this->assertSame($cin, $diplome->cin);
    }

    // TEST 7 — Relations preserved when omitted.
    public function test_7_relations_preserved_when_omitted(): void
    {
        $user = $this->userWithActualisationPermission();
        $cin = 'WSA_PRES_' . strtoupper(Str::random(6));
        $researcher = $this->createResearcher($cin);
        $chercheurId = $researcher['chercheur_id'];

        $originalMobilitesCount = DB::table('chercheurs_mobilites')->where('chercheur_id', $chercheurId)->count();
        $originalDiplomesCount = DB::table('avoir_diplomes')->where('chercheur_id', $chercheurId)->count();
        $originalLanguesCount = DB::table('langues_parlees')->where('chercheur_id', $chercheurId)->count();

        $this->assertSame(4, $originalMobilitesCount);
        $this->assertSame(2, $originalDiplomesCount);
        $this->assertSame(3, $originalLanguesCount);

        $data = $this->validActualisationData($cin);
        unset($data['mobilites']);
        unset($data['diplomes']);
        unset($data['langues']);

        $response = $this->actingAs($user, 'api')
            ->putJson('/api/v1/services/actualisation', $data);
        $response->assertStatus(200);

        $this->assertSame($originalMobilitesCount, DB::table('chercheurs_mobilites')->where('chercheur_id', $chercheurId)->count());
        $this->assertSame($originalDiplomesCount, DB::table('avoir_diplomes')->where('chercheur_id', $chercheurId)->count());
        $this->assertSame($originalLanguesCount, DB::table('langues_parlees')->where('chercheur_id', $chercheurId)->count());
    }

    // TEST 8 — Relations cleared when empty array.
    public function test_8_relations_cleared_when_empty_array(): void
    {
        $user = $this->userWithActualisationPermission();
        $cin = 'WSA_CLR_' . strtoupper(Str::random(6));
        $researcher = $this->createResearcher($cin);
        $chercheurId = $researcher['chercheur_id'];

        $this->assertSame(4, DB::table('chercheurs_mobilites')->where('chercheur_id', $chercheurId)->count());
        $this->assertSame(2, DB::table('avoir_diplomes')->where('chercheur_id', $chercheurId)->count());
        $this->assertSame(3, DB::table('langues_parlees')->where('chercheur_id', $chercheurId)->count());

        $data = $this->validActualisationData($cin);
        $data['mobilites'] = [];
        $data['diplomes'] = [];
        $data['langues'] = [];

        $response = $this->actingAs($user, 'api')
            ->putJson('/api/v1/services/actualisation', $data);
        $response->assertStatus(200);

        $this->assertSame(0, DB::table('chercheurs_mobilites')->where('chercheur_id', $chercheurId)->count());
        $this->assertSame(0, DB::table('avoir_diplomes')->where('chercheur_id', $chercheurId)->count());
        $this->assertSame(0, DB::table('langues_parlees')->where('chercheur_id', $chercheurId)->count());
    }

    // TEST 9 — Duplicate language in same request is deduplicated.
    public function test_9_duplicate_language_in_same_request_deduplicated(): void
    {
        $user = $this->userWithActualisationPermission();
        $cin = 'WSA_LANG_' . strtoupper(Str::random(6));
        $researcher = $this->createResearcher($cin);
        $chercheurId = $researcher['chercheur_id'];

        $this->assertSame(3, DB::table('langues_parlees')->where('chercheur_id', $chercheurId)->count());

        $data = $this->validActualisationData($cin);
        $data['langues'] = [
            ['competence_linguistique_id' => 1, 'niveau' => 'Courant'],
            ['competence_linguistique_id' => 1, 'niveau' => 'Moyen'],
            ['competence_linguistique_id' => 4, 'niveau' => 'Courant'],
        ];

        $response = $this->actingAs($user, 'api')
            ->putJson('/api/v1/services/actualisation', $data);
        $response->assertStatus(200);

        $this->assertSame(2, DB::table('langues_parlees')->where('chercheur_id', $chercheurId)->count());

        $row = DB::table('langues_parlees')
            ->where('chercheur_id', $chercheurId)
            ->where('competence_linguistique_id', 1)
            ->first();
        $this->assertNotNull($row);
        $this->assertSame('Courant', $row->niveau);
    }

    // TEST 10 — Missing required field → 422.
    public function test_10_missing_required_field_returns_422(): void
    {
        $user = $this->userWithActualisationPermission();
        $cin = 'WSA_VAL_' . strtoupper(Str::random(6));
        $researcher = $this->createResearcher($cin);
        $chercheurId = $researcher['chercheur_id'];

        $row = DB::table('chercheurs')->where('id', $chercheurId)->first();
        $originalNomCandidat = $row->nom_candidat;

        $data = $this->validActualisationData($cin);
        $data['chercheur']['nom_candidat'] = 'ShouldNotChange';
        unset($data['chercheur']['cin']);

        $response = $this->actingAs($user, 'api')
            ->putJson('/api/v1/services/actualisation', $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['chercheur.cin']);

        $row = DB::table('chercheurs')->where('id', $chercheurId)->first();
        $this->assertSame($originalNomCandidat, $row->nom_candidat);
    }

    // TEST 11 — Transaction rolls back on mid-transaction failure.
    public function test_11_transaction_rolls_back_on_mid_update_failure(): void
    {
        $user = $this->userWithActualisationPermission();
        $cin = 'WSA_RB_' . strtoupper(Str::random(6));
        $researcher = $this->createResearcher($cin);
        $chercheurId = $researcher['chercheur_id'];

        $row = DB::table('chercheurs')->where('id', $chercheurId)->first();
        $originalNomCandidat = $row->nom_candidat;

        $originalMobilitesCount = DB::table('chercheurs_mobilites')->where('chercheur_id', $chercheurId)->count();

        $service = new ActualisationService;
        $service->setFailureTrigger(new \RuntimeException('Simulated mid-transaction failure'));
        $this->app->instance(ActualisationService::class, $service);

        $data = $this->validActualisationData($cin);
        $data['chercheur']['nom_candidat'] = 'ShouldNotChange';

        $response = $this->actingAs($user, 'api')
            ->putJson('/api/v1/services/actualisation', $data);

        $response->assertStatus(500);

        $row = DB::table('chercheurs')->where('id', $chercheurId)->first();
        $this->assertSame($originalNomCandidat, $row->nom_candidat, 'Rollback must restore the original nom_candidat.');
        $this->assertSame($originalMobilitesCount, DB::table('chercheurs_mobilites')->where('chercheur_id', $chercheurId)->count());
    }
}
