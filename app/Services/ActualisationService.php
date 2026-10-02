<?php

namespace App\Services;

use App\Exceptions\CinNotFoundException;
use App\Exceptions\DuplicateEmailException;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Throwable;

class ActualisationService
{
    /**
     * @var Throwable|null
     * @internal For testing transaction rollback only. When non-null, the
     *          next call to {@see checkFailureTrigger()} throws this and the
     *          DB transaction is rolled back.
     */
    private ?Throwable $failureTrigger = null;

    /**
     * @internal Test-only seam to inject a failure after the CHERCHEURS update
     *           and before any relation update, to verify rollback behavior.
     */
    public function setFailureTrigger(Throwable $throwable): void
    {
        $this->failureTrigger = $throwable;
    }

    /**
     * Update an existing researcher and all provided relation rows in a single DB transaction.
     *
     * - Returns 404 if the CIN does not exist (CIN_NOT_FOUND)
     * - Returns 409 if the email is already used by a different researcher (EMAIL_ALREADY_EXISTS)
     * - Rolls back every change if any step fails
     * - Only relation arrays explicitly present in $data are replaced; omitted arrays are preserved
     * - An explicit empty array clears all relations for that table
     *
     * @return array{chercheur_id: int, cin: string, email: string}
     *
     * @throws CinNotFoundException
     * @throws DuplicateEmailException
     */
    public function update(array $data): array
    {
        return DB::transaction(function () use ($data): array {
            $chercheur = $data['chercheur'];
            $cin = strtoupper(trim((string) $chercheur['cin']));
            $email = trim((string) $chercheur['e_mail']);
            $emailLower = strtolower($email);

            $row = DB::table('chercheurs')->where('cin', $cin)->first();

            if ($row === null) {
                throw new CinNotFoundException;
            }

            $chercheurId = (int) $row->id;

            $existingEmailLower = strtolower((string) ($row->e_mail ?? ''));
            if ($emailLower !== $existingEmailLower) {
                $conflict = DB::table('chercheurs')
                    ->whereRaw('LOWER(e_mail) = ?', [$emailLower])
                    ->where('cin', '!=', $cin)
                    ->exists();

                if ($conflict) {
                    throw new DuplicateEmailException;
                }
            }

            $this->updateChercheur($chercheur, $cin, $chercheurId);

            $this->checkFailureTrigger();

            if (array_key_exists('mobilites', $data)) {
                $this->replaceMobilites($chercheurId, $data['mobilites']);
            }

            if (array_key_exists('bureautiques', $data)) {
                $this->replaceBureautiques($chercheurId, $data['bureautiques']);
            }

            if (array_key_exists('permis', $data)) {
                $this->replacePermis($chercheurId, $data['permis'], $cin);
            }

            if (array_key_exists('diplomes', $data)) {
                $this->replaceDiplomes($chercheurId, $data['diplomes'], $cin);
            }

            if (array_key_exists('experiences', $data)) {
                $this->replaceExperiences($chercheurId, $data['experiences'], $cin);
            }

            if (array_key_exists('langues', $data)) {
                $langues = $this->dedupeLangues($data['langues']);
                $this->replaceLangues($chercheurId, $langues, $cin);
            }

            if (array_key_exists('emplois_metiers', $data)) {
                $this->replaceEmploisMetiers($chercheurId, $data['emplois_metiers'], $cin);
            }

            return [
                'chercheur_id' => $chercheurId,
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

    private function updateChercheur(array $c, string $cin, int $chercheurId): void
    {
        $now = Carbon::now();

        $row = [
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
            'date_derniere_actualisation' => $now,
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

        DB::table('chercheurs')
            ->where('id', $chercheurId)
            ->update($row);
    }

    private function replaceMobilites(int $chercheurId, array $mobilites): void
    {
        DB::table('chercheurs_mobilites')
            ->where('chercheur_id', $chercheurId)
            ->delete();

        foreach ($mobilites as $mobiliteId) {
            DB::table('chercheurs_mobilites')->insert([
                'mobilite_id' => (int) $mobiliteId,
                'chercheur_id' => $chercheurId,
            ]);
        }
    }

    private function replaceBureautiques(int $chercheurId, array $bureautiques): void
    {
        DB::table('bureautiques_chercheurs')
            ->where('chercheur_id', $chercheurId)
            ->delete();

        foreach ($bureautiques as $bureautiqueId) {
            DB::table('bureautiques_chercheurs')->insert([
                'chercheur_id' => $chercheurId,
                'bureautique_id' => (int) $bureautiqueId,
            ]);
        }
    }

    private function replacePermis(int $chercheurId, array $permis, string $cin): void
    {
        DB::table('avoir_permis')
            ->where('chercheur_id', $chercheurId)
            ->delete();

        foreach ($permis as $permiConduireId) {
            DB::table('avoir_permis')->insert([
                'chercheur_id' => $chercheurId,
                'permi_conduire_id' => (int) $permiConduireId,
                'cin' => $cin,
            ]);
        }
    }

    private function replaceDiplomes(int $chercheurId, array $diplomes, string $cin): void
    {
        DB::table('avoir_diplomes')
            ->where('chercheur_id', $chercheurId)
            ->delete();

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

    private function replaceExperiences(int $chercheurId, array $experiences, string $cin): void
    {
        DB::table('experience_proffes')
            ->where('chercheur_id', $chercheurId)
            ->delete();

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

    private function replaceLangues(int $chercheurId, array $langues, string $cin): void
    {
        DB::table('langues_parlees')
            ->where('chercheur_id', $chercheurId)
            ->delete();

        foreach ($langues as $l) {
            DB::table('langues_parlees')->insert([
                'chercheur_id' => $chercheurId,
                'cin' => $cin,
                'competence_linguistique_id' => (int) $l['competence_linguistique_id'],
                'niveau' => trim((string) $l['niveau']),
            ]);
        }
    }

    private function replaceEmploisMetiers(int $chercheurId, array $emploisMetiers, string $cin): void
    {
        DB::table('chercher_emplois')
            ->where('chercheur_id', $chercheurId)
            ->delete();

        foreach ($emploisMetiers as $m) {
            DB::table('chercher_emplois')->insert([
                'chercheur_id' => $chercheurId,
                'cin' => $cin,
                'emploi_metier_id' => (int) $m['emploi_metier_id'],
            ]);
        }
    }

    private function toOracleDate(string $value): Carbon
    {
        return Carbon::createFromFormat('d-m-Y', trim($value))->startOfDay();
    }

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
