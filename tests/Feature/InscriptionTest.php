<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\WebService;
use App\Services\InscriptionService;
use Database\Factories\UserFactory;
use Database\Seeders\WebServiceSeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class InscriptionTest extends TestCase
{
    use DatabaseTransactions;

    protected $connectionsToTransact = [null];

    protected function setUp(): void
    {
        parent::setUp();

        $this->beginDatabaseTransaction();
        $this->seed(WebServiceSeeder::class);
    }

    private function userWithInscriptionPermission(): User
    {
        $user = UserFactory::new()->create([
            'email' => fake()->unique()->safeEmail(),
            'password' => 'secret123',
            'is_active' => true,
        ]);

        $ws = WebService::query()->where('code', 'WS_INSCRIPTION')->firstOrFail();
        $user->webServices()->attach($ws->id, ['is_enabled' => true]);

        return $user;
    }

    private function validInscriptionData(?string $cin = null, ?string $email = null): array
    {
        return [
            'chercheur' => [
                'nationalite' => 0,
                'cin' => $cin ?? ('WSI_' . strtoupper(Str::random(8))),
                'password' => 'secret123',
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
                'date_inscription' => '23-09-2026',
                'date_derniere_actualisation' => '23-09-2026',
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

    // TEST 1 — Complete successful inscription.
    public function test_1_inscription_complete_succeeds(): void
    {
        $user = $this->userWithInscriptionPermission();
        $data = $this->validInscriptionData();
        $cin = $data['chercheur']['cin'];
        $email = $data['chercheur']['e_mail'];

        $response = $this->actingAs($user, 'api')
            ->postJson('/api/v1/services/inscription', $data);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Chercheur enregistré avec succès.')
            ->assertJsonPath('data.cin', $cin)
            ->assertJsonPath('data.email', $email);

        $chercheurId = $response->json('data.chercheur_id');
        $this->assertIsInt($chercheurId);
        $this->assertGreaterThan(0, $chercheurId);

        // CHERCHEURS
        $this->assertSame(1, DB::table('chercheurs')->where('cin', $cin)->count());

        // Relations — every row must reference the same chercheur_id
        $this->assertSame(4, DB::table('chercheurs_mobilites')->where('chercheur_id', $chercheurId)->count());
        $this->assertSame(4, DB::table('bureautiques_chercheurs')->where('chercheur_id', $chercheurId)->count());
        $this->assertSame(3, DB::table('avoir_permis')->where('chercheur_id', $chercheurId)->count());
        $this->assertSame(2, DB::table('avoir_diplomes')->where('chercheur_id', $chercheurId)->count());
        $this->assertSame(2, DB::table('experience_proffes')->where('chercheur_id', $chercheurId)->count());
        $this->assertSame(3, DB::table('langues_parlees')->where('chercheur_id', $chercheurId)->count());
        $this->assertSame(1, DB::table('chercher_emplois')->where('chercheur_id', $chercheurId)->count());

        // Server-set dates are populated
        $row = DB::table('chercheurs')->where('cin', $cin)->first();
        $this->assertNotNull($row->date_inscription);
        $this->assertNotNull($row->date_derniere_actualisation);

        // The chercheur_id in the response must equal chercheurs_seq.currval
        // (proves the ID was retrieved from the sequence, not from a re-query).
        $currvalRow = DB::selectOne('SELECT chercheurs_seq.currval AS currval FROM dual');
        $this->assertNotNull($currvalRow, 'chercheurs_seq.currval must be readable after insert.');
        $this->assertSame($chercheurId, (int) $currvalRow->currval, 'Response chercheur_id must match chercheurs_seq.currval.');

        // The CHERCHEURS row itself must carry this same ID.
        $this->assertSame($chercheurId, (int) $row->id, 'CHERCHEURS.id must match the response chercheur_id.');
    }

    // TEST 2 — Duplicate CIN.
    public function test_2_duplicate_cin_returns_409(): void
    {
        $user = $this->userWithInscriptionPermission();

        $cin = 'WSI_DUP_' . strtoupper(Str::random(6));

        // Seed the duplicate before the request
        DB::table('chercheurs')->insert(['cin' => $cin]);
        $this->assertSame(1, DB::table('chercheurs')->where('cin', $cin)->count());

        $data = $this->validInscriptionData(cin: $cin);

        $response = $this->actingAs($user, 'api')
            ->postJson('/api/v1/services/inscription', $data);

        $response->assertStatus(409)
            ->assertJsonPath('success', false)
            ->assertJsonPath('code', 'CIN_ALREADY_EXISTS')
            ->assertJsonPath('message', 'Un chercheur existe déjà avec ce CIN.');

        // No new chercheur, no partial writes
        $this->assertSame(1, DB::table('chercheurs')->where('cin', $cin)->count());
    }

    // TEST 3 — Duplicate email, case-insensitive.
    public function test_3_duplicate_email_returns_409(): void
    {
        $user = $this->userWithInscriptionPermission();

        // Pre-insert a chercheurs row with a DIFFERENT CIN but the same email
        // (uppercased) so the CIN check passes and only the email check fires.
        $existingCin = 'WSI_XE_' . strtoupper(Str::random(6));
        $requestCin = 'WSI_XR_' . strtoupper(Str::random(6));
        $lowerEmail = 'wsi_' . strtolower(Str::random(8)) . '@example.com';
        $upperEmail = strtoupper($lowerEmail);

        DB::table('chercheurs')->insert([
            'cin' => $existingCin,
            'e_mail' => $upperEmail,
        ]);

        // Request with lowercase version — should still collide (case-insensitive)
        $data = $this->validInscriptionData(cin: $requestCin, email: $lowerEmail);

        $response = $this->actingAs($user, 'api')
            ->postJson('/api/v1/services/inscription', $data);

        $response->assertStatus(409)
            ->assertJsonPath('success', false)
            ->assertJsonPath('code', 'EMAIL_ALREADY_EXISTS')
            ->assertJsonPath('message', 'Un chercheur existe déjà avec cet email.');

        // No new chercheur was created for the requested CIN
        $this->assertSame(0, DB::table('chercheurs')->where('cin', $requestCin)->count());
    }

    // TEST 4 — Missing required field → 422.
    public function test_4_missing_required_field_returns_422(): void
    {
        $user = $this->userWithInscriptionPermission();

        $data = $this->validInscriptionData();
        $cin = $data['chercheur']['cin'];
        unset($data['chercheur']['cin']);

        $response = $this->actingAs($user, 'api')
            ->postJson('/api/v1/services/inscription', $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['chercheur.cin']);

        // Validation failure means no insert happened
        $this->assertSame(0, DB::table('chercheurs')->where('cin', $cin)->count());
    }

    // TEST 5 — Mid-transaction failure → full rollback.
    public function test_5_transaction_rolls_back_on_mid_insert_failure(): void
    {
        $user = $this->userWithInscriptionPermission();

        $cin = 'WSI_' . strtoupper(Str::random(8));

        // Inject a service instance whose failure trigger fires after the
        // CHERCHEURS insert and before any relation insert. This proves the
        // DB transaction rolls back every insert cleanly.
        $service = new InscriptionService;
        $service->setFailureTrigger(new \RuntimeException('Simulated mid-transaction failure'));
        $this->app->instance(InscriptionService::class, $service);

        $data = $this->validInscriptionData(cin: $cin);

        $response = $this->actingAs($user, 'api')
            ->postJson('/api/v1/services/inscription', $data);

        $response->assertStatus(500);

        // Rollback proof: no CHERCHEURS row survived
        $this->assertSame(0, DB::table('chercheurs')->where('cin', $cin)->count());
    }

    // TEST 6 — Duplicate language within the same request.
    public function test_6_duplicate_language_in_same_request_creates_one_row(): void
    {
        $user = $this->userWithInscriptionPermission();

        $cin = 'WSI_' . strtoupper(Str::random(8));
        $data = $this->validInscriptionData(cin: $cin);

        // Inject a duplicate of competence_linguistique_id=1 (already present)
        $data['langues'][] = [
            'competence_linguistique_id' => 1,
            'niveau' => 'Moyen',
        ];

        $response = $this->actingAs($user, 'api')
            ->postJson('/api/v1/services/inscription', $data);

        $response->assertStatus(201);

        $chercheurId = $response->json('data.chercheur_id');
        $this->assertIsInt($chercheurId);

        // 3 unique languages (ids 1, 4, 8), not 4
        $this->assertSame(3, DB::table('langues_parlees')->where('chercheur_id', $chercheurId)->count());

        // The kept row is the first occurrence (niveau = "Courant")
        $row = DB::table('langues_parlees')
            ->where('chercheur_id', $chercheurId)
            ->where('competence_linguistique_id', 1)
            ->first();
        $this->assertNotNull($row);
        $this->assertSame('Courant', $row->niveau);
    }
}
