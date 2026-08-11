<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ContestType extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'min_score'            => 'float',
            'coeff_bac_btp'        => 'float',
            'coeff_grad_degree'    => 'float',
            'bonus_per_extra_year' => 'float',
            'max_extra_years'      => 'integer',
            'has_bac'              => 'boolean',
            'has_degree'           => 'boolean',
            'has_school_level'     => 'boolean',
            'has_driving_license'  => 'boolean',
            'has_age_bonus'        => 'boolean',
            // Champs de scoring par niveau scolaire
            'school_levels'        => 'array',   // [{label, extra_years}, ...]
            // Critère d'éligibilité par âge (pour TOUS les types)
            'max_age'              => 'integer',
            'age_reference_date'   => 'date',
        ];
    }

    /**
     * Retourne le nombre d'années supplémentaires pour un niveau scolaire donné.
     * Cherche dans le tableau JSON school_levels la correspondance exacte du label,
     * puis essaie une correspondance partielle (contains).
     * Retourne 0 si aucune correspondance, plafonné à max_extra_years.
     */
    public function getExtraYears(string $schoolLevel): int
    {
        $levels = $this->school_levels ?? [];

        // Correspondance exacte
        foreach ($levels as $level) {
            if (isset($level['label']) && trim($level['label']) === trim($schoolLevel)) {
                return (int) min($level['extra_years'] ?? 0, $this->max_extra_years ?? 4);
            }
        }

        // Correspondance partielle (label contient le niveau)
        foreach ($levels as $level) {
            if (isset($level['label']) && str_contains($schoolLevel, trim($level['label']))) {
                return (int) min($level['extra_years'] ?? 0, $this->max_extra_years ?? 4);
            }
        }

        return 0;
    }

    /**
     * Retourne les options {label => label} pour un Select Filament / frontend.
     */
    public function getSchoolLevelOptions(): array
    {
        $options = [];
        foreach ($this->school_levels ?? [] as $level) {
            if (isset($level['label'])) {
                $options[$level['label']] = $level['label'];
            }
        }
        return $options;
    }

    /**
     * Vérifie si un candidat est éligible selon le critère d'âge.
     *
     * @param  string|\DateTimeInterface $birthDate  Date de naissance du candidat
     * @return bool  true = éligible, false = trop âgé (rejeté)
     */
    public function isAgeEligible(mixed $birthDate): bool
    {
        if (! $this->max_age || ! $birthDate) {
            return true; // Pas de limite d'âge définie → accepté
        }

        $referenceDate = $this->age_reference_date
            ?? \Carbon\Carbon::now()->startOfYear(); // fallback : 1er janvier de l'année courante

        $age = \Carbon\Carbon::parse($birthDate)->diffInYears($referenceDate);

        return $age <= $this->max_age;
    }

    public function contests(): HasMany
    {
        return $this->hasMany(Contest::class);
    }

    public function positions(): HasMany
    {
        return $this->hasMany(Position::class);
    }
}
