<?php

namespace App\Http\Requests\Services;

use Illuminate\Foundation\Http\FormRequest;

class ActualisationRequest extends FormRequest
{
    /**
     * Note: chercheur_id is intentionally NOT in the rules. Laravel's
     * validation naturally excludes fields not listed here, so any
     * client-supplied chercheur_id is silently dropped.
     *
     * Also excluded: password, date_inscription, date_derniere_actualisation.
     * These are immutable or server-set and cannot be changed via actualisation.
     */

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'chercheur' => ['required', 'array'],

            'chercheur.nationalite' => ['required', 'integer'],
            'chercheur.cin' => ['required', 'string', 'min:2', 'max:15'],
            'chercheur.nom_candidat' => ['required', 'string', 'max:50'],
            'chercheur.prenom' => ['required', 'string', 'max:50'],
            'chercheur.sexe' => ['required', 'string', 'max:50'],
            'chercheur.situation_familiale' => ['required', 'string', 'max:50'],
            'chercheur.date_naissance' => ['required', 'date_format:d-m-Y'],
            'chercheur.adresse' => ['required', 'string', 'max:500'],
            'chercheur.ville_id' => ['required', 'integer'],
            'chercheur.commune_id' => ['required', 'integer'],
            'chercheur.e_mail' => ['required', 'email', 'max:100'],
            'chercheur.n_gsm' => ['required', 'string', 'max:50'],
            'chercheur.situation_p_r_emploi' => ['required', 'string', 'max:50'],

            'chercheur.n_gsm2' => ['nullable', 'string', 'max:50'],
            'chercheur.tel' => ['nullable', 'string', 'max:50'],
            'chercheur.mois_chomage' => ['nullable', 'string', 'max:2'],
            'chercheur.duree_chomage' => ['nullable', 'string', 'max:10'],
            'chercheur.handicape' => ['nullable', 'string', 'max:10'],
            'chercheur.nature_handicape' => ['nullable', 'string', 'max:50'],
            'chercheur.competences_specifiques' => ['nullable', 'string', 'max:4000'],
            'chercheur.activites_extra_pro' => ['nullable', 'string', 'max:500'],
            'chercheur.provenance' => ['nullable', 'string', 'max:2'],
            'chercheur.agence_id' => ['nullable', 'integer'],
            'chercheur.is_diplome' => ['nullable', 'integer', 'in:0,1'],

            'mobilites' => ['nullable', 'array'],
            'mobilites.*' => ['required', 'integer'],

            'bureautiques' => ['nullable', 'array'],
            'bureautiques.*' => ['required', 'integer'],

            'permis' => ['nullable', 'array'],
            'permis.*' => ['required', 'integer'],

            'diplomes' => ['nullable', 'array'],
            'diplomes.*.diplome_id' => ['required', 'integer'],
            'diplomes.*.specialite_id' => ['required', 'integer'],
            'diplomes.*.option_diplome_id' => ['required', 'integer'],
            'diplomes.*.groupe_etablissement_id' => ['required', 'integer'],
            'diplomes.*.etablissement_id' => ['required', 'integer'],
            'diplomes.*.date_optention' => ['required', 'string', 'max:20'],
            'diplomes.*.commentaire' => ['required', 'string', 'max:500'],

            'experiences' => ['nullable', 'array'],
            'experiences.*.date_debut' => ['required', 'date_format:d-m-Y'],
            'experiences.*.date_fin' => ['nullable', 'date_format:d-m-Y'],
            'experiences.*.ce_jour' => ['required', 'integer', 'in:0,1'],
            'experiences.*.employeur' => ['required', 'string', 'max:200'],
            'experiences.*.intitule_poste' => ['required', 'string', 'max:150'],
            'experiences.*.commentaire' => ['nullable', 'string', 'max:400'],
            'experiences.*.duree_experience_id' => ['required', 'integer'],

            'langues' => ['nullable', 'array'],
            'langues.*.competence_linguistique_id' => ['required', 'integer'],
            'langues.*.niveau' => ['required', 'string', 'max:50'],

            'emplois_metiers' => ['nullable', 'array'],
            'emplois_metiers.*.emploi_metier_id' => ['required', 'integer'],
        ];
    }

    public function messages(): array
    {
        return [
            'chercheur.cin.required' => 'Le champ cin est obligatoire.',
            'chercheur.cin.max' => 'Le champ cin ne doit pas dépasser 15 caractères.',
            'chercheur.e_mail.required' => 'Le champ e_mail est obligatoire.',
            'chercheur.e_mail.email' => 'Le champ e_mail doit être une adresse email valide.',
            'chercheur.date_naissance.date_format' => 'Le champ date_naissance doit être au format jj-mm-aaaa.',
            'chercheur.required' => 'Le bloc chercheur est obligatoire.',
        ];
    }

    public function prepareForValidation(): void
    {
        $chercheur = $this->input('chercheur', []);
        if (is_array($chercheur)) {
            foreach ($chercheur as $key => $value) {
                if (is_string($value)) {
                    $chercheur[$key] = trim($value);
                }
            }
            $this->merge(['chercheur' => $chercheur]);
        }
    }
}
