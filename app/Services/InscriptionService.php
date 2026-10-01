<?php

namespace App\Services;

use App\Exceptions\DuplicateCinException;
use App\Exceptions\DuplicateEmailException;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Throwable;

class InscriptionService
{
    /**
     * @var Throwable|null
     * @internal For testing transaction rollback only. When non-null, the
     *          next call to {@see checkFailureTrigger()} throws this and the
     *          DB transaction is rolled back.
     */
    private ?Throwable $failureTrigger = null;

    /**
     * @internal Test-only seam to inject a failure after CHERCHEURS insert
     *           and before any relation insert, to verify rollback behavior.
     */
    public function setFailureTrigger(Throwable $throwable): void
    {
        $this->failureTrigger = $throwable;
    }

    /**
     * Register a researcher and all related rows in a single DB transaction.
     *
     * - Rejects duplicate CIN (CIN_ALREADY_EXISTS)
     * - Rejects duplicate email, case-insensitive (EMAIL_ALREADY_EXISTS)
     * - Rolls back every insert if any step fails
     *
     * @return array{chercheur_id: int, cin: string, email: string}
     *
     * @throws DuplicateCinException
     * @throws DuplicateEmailException
     */
    public function register(array $data): array
    {
        return DB::transaction(function () use ($data): array {
            $chercheur = $data['chercheur'];
            $cin = strtoupper(trim((string) $chercheur['cin']));
            $email = trim((string) $chercheur['e_mail']);
            $emailLower = strtolower($email);

            if (DB::table('chercheurs')->where('cin', $cin)->exists()) {
                throw new DuplicateCinException;
            }

            if (DB::table('chercheurs')->whereRaw('LOWER(e_mail) = ?', [$emailLower])->exists()) {
                throw new DuplicateEmailException;
            }

            $langues = $this->dedupeLangues($data['langues'] ?? []);

            $chercheurId = $this->insertChercheur($chercheur, $cin);

            // Test-only failure point: between the CHERCHEURS insert and any
            // relation insert. When set, throws and the whole transaction
            // rolls back, proving no partial rows survive.
            $this->checkFailureTrigger();

            $this->insertMobilites($chercheurId, $data['mobilites'] ?? []);
            $this->insertBureautiques($chercheurId, $data['bureautiques'] ?? []);
            $this->insertPermis($chercheurId, $data['permis'] ?? [], $cin);
            $this->insertDiplomes($chercheurId, $data['diplomes'] ?? [], $cin);
            $this->insertExperiences($chercheurId, $data['experiences'] ?? [], $cin);
            $this->insertLangues($chercheurId, $langues, $cin);
            $this->insertEmploisMetiers($chercheurId, $data['emplois_metiers'] ?? [], $cin);

            return [
                'chercheur_id' => (int) $chercheurId,
                'cin' => $cin,
                'email' => $email,
            ];
        });
    }

    private function checkFailureTrigger(): void
    {
        if ($this->failureTrigger === null) {
            return;
        }
        $throwable = $this->failureTrigger;
        $this->failureTrigger = null;
        throw $throwable;
    }

    private function insertChercheur(array $c, string $cin): int
    {
        $now = Carbon::now();

        $row = [
            'cin' => $cin,
            'password' => trim((string) $c['password']),
            'nom_candidat' => trim((string) $c['nom_candidat']),
            'prenom' => trim((string) $c['prenom']),
            'sexe' => trim((string) $c['sexe']),
            'situation_familiale' => trim((string) $c['situation_familiale']),
            'date_naissance' => $this->toOracleDate($c['date_naissance']),
            'adresse' => trim((string) $c['adresse']),
            'ville_id' => (int) $c['ville_id'],
            'commune_id' => (int) $c['commune_id'],
            'e_mail' => trim((string) $c['e_mail']),
            'vemail' => trim((string) $c['e_mail']),
            'n_gsm' => trim((string) $c['n_gsm']),
            'situation_p_r_emploi' => trim((string) $c['situation_p_r_emploi']),
            'nationalite' => (int) $c['nationalite'],
            'date_inscription' => $now->copy(),
            'date_derniere_actualisation' => $now->copy(),
        ];

        if (isset($c['n_gsm2']) && $c['n_gsm2'] !== null && $c['n_gsm2'] !== '') {
            $row['n_gsm2'] = trim((string) $c['n_gsm2']);
        }
        if (isset($c['tel']) && $c['tel'] !== null && $c['tel'] !== '') {
            $row['tel'] = trim((string) $c['tel']);
        }
        if (isset($c['mois_chomage']) && $c['mois_chomage'] !== null && $c['mois_chomage'] !== '') {
            $row['mois_chomage'] = (string) $c['mois_chomage'];
        }
        if (isset($c['duree_chomage']) && $c['duree_chomage'] !== null && $c['duree_chomage'] !== '') {
            $row['duree_chomage'] = (string) $c['duree_chomage'];
        }
        if (isset($c['handicape']) && $c['handicape'] !== null && $c['handicape'] !== '') {
            $row['handicape'] = trim((string) $c['handicape']);
        }
        if (isset($c['nature_handicape']) && $c['nature_handicape'] !== null && $c['nature_handicape'] !== '') {
            $row['nature_handicape'] = trim((string) $c['nature_handicape']);
        }
        if (isset($c['competences_specifiques']) && $c['competences_specifiques'] !== null && $c['competences_specifiques'] !== '') {
            $row['competences_specifiques'] = trim((string) $c['competences_specifiques']);
        }
        if (isset($c['activites_extra_pro']) && $c['activites_extra_pro'] !== null && $c['activites_extra_pro'] !== '') {
            $row['activites_extra_pro'] = trim((string) $c['activites_extra_pro']);
        }
        if (isset($c['provenance']) && $c['provenance'] !== null && $c['provenance'] !== '') {
            $row['provenance'] = trim((string) $c['provenance']);
        }
        if (isset($c['agence_id']) && $c['agence_id'] !== null) {
            $row['agence_id'] = (int) $c['agence_id'];
        }
        if (isset($c['is_diplome']) && $c['is_diplome'] !== null) {
            $row['is_diplome'] = (int) $c['is_diplome'];
        }

        DB::table('chercheurs')->insert($row);

        // The BEFORE INSERT trigger TRIG_CHERCEUR sets ID via chercheurs_seq.nextval.
        // Retrieve the generated ID immediately using chercheurs_seq.currval on the
        // same Oracle connection. currval is session-level and returns the last
        // nextval generated in this session, so it is guaranteed to be the ID
        // assigned to the row we just inserted.
        $currval = DB::selectOne('SELECT chercheurs_seq.currval AS currval FROM dual');

        if ($currval === null || !property_exists($currval, 'currval') || $currval->currval === null) {
            throw new \RuntimeException('Failed to retrieve chercheurs_seq.currval after insert.');
        }

        return (int) $currval->currval;
    }

    private function insertMobilites(int $chercheurId, array $mobilites): void
    {
        foreach ($mobilites as $mobiliteId) {
            DB::table('chercheurs_mobilites')->insert([
                'mobilite_id' => (int) $mobiliteId,
                'chercheur_id' => $chercheurId,
            ]);
        }
    }

    private function insertBureautiques(int $chercheurId, array $bureautiques): void
    {
        foreach ($bureautiques as $bureautiqueId) {
            DB::table('bureautiques_chercheurs')->insert([
                'chercheur_id' => $chercheurId,
                'bureautique_id' => (int) $bureautiqueId,
            ]);
        }
    }

    private function insertPermis(int $chercheurId, array $permis, string $cin): void
    {
        foreach ($permis as $permiConduireId) {
            DB::table('avoir_permis')->insert([
                'chercheur_id' => $chercheurId,
                'permi_conduire_id' => (int) $permiConduireId,
                'cin' => $cin,
            ]);
        }
    }

    private function insertDiplomes(int $chercheurId, array $diplomes, string $cin): void
    {
        foreach ($diplomes as $d) {
            DB::table('avoir_diplomes')->insert([
                'chercheur_id' => $chercheurId,
                'cin' => $cin,
                'diplome_id' => (int) $d['diplome_id'],
                'specialite_id' => (int) $d['specialite_id'],
                'option_diplome_id' => (int) $d['option_diplome_id'],
                'groupe_etablissement_id' => (int) $d['groupe_etablissement_id'],
                'etablissement_id' => (int) $d['etablissement_id'],
                'date_optention' => trim((string) $d['date_optention']),
                'commentaire' => trim((string) $d['commentaire']),
            ]);
        }
    }

    private function insertExperiences(int $chercheurId, array $experiences, string $cin): void
    {
        foreach ($experiences as $e) {
            $row = [
                'chercheur_id' => $chercheurId,
                'cin' => $cin,
                'date_debut' => $this->toOracleDate($e['date_debut']),
                'ce_jour' => (int) $e['ce_jour'],
                'employeur' => trim((string) $e['employeur']),
                'intitule_poste' => trim((string) $e['intitule_poste']),
                'duree_experience_id' => (int) $e['duree_experience_id'],
            ];
            if (isset($e['date_fin']) && $e['date_fin'] !== null && $e['date_fin'] !== '') {
                $row['date_fin'] = $this->toOracleDate($e['date_fin']);
            }
            if (isset($e['commentaire']) && $e['commentaire'] !== null && $e['commentaire'] !== '') {
                $row['commentaire'] = trim((string) $e['commentaire']);
            }
            DB::table('experience_proffes')->insert($row);
        }
    }

    private function insertLangues(int $chercheurId, array $langues, string $cin): void
    {
        foreach ($langues as $l) {
            DB::table('langues_parlees')->insert([
                'chercheur_id' => $chercheurId,
                'cin' => $cin,
                'competence_linguistique_id' => (int) $l['competence_linguistique_id'],
                'niveau' => trim((string) $l['niveau']),
            ]);
        }
    }

    private function insertEmploisMetiers(int $chercheurId, array $emploisMetiers, string $cin): void
    {
        foreach ($emploisMetiers as $m) {
            DB::table('chercher_emplois')->insert([
                'chercheur_id' => $chercheurId,
                'cin' => $cin,
                'emploi_metier_id' => (int) $m['emploi_metier_id'],
            ]);
        }
    }

    /**
     * Parse a client-provided date string in "dd-mm-yyyy" format.
     * Returns a Carbon instance suitable for Oracle DATE binding.
     */
    private function toOracleDate(string $value): Carbon
    {
        return Carbon::createFromFormat('d-m-Y', trim($value))->startOfDay();
    }

    /**
     * Deduplicate a language list by competence_linguistique_id, keeping the first
     * occurrence. Prevents duplicate rows in langues_parlees for the same language.
     */
    private function dedupeLangues(array $langues): array
    {
        $seen = [];
        $result = [];

        foreach ($langues as $language) {
            $key = (int) $language['competence_linguistique_id'];
            if (isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;
            $result[] = $language;
        }

        return $result;
    }
}
