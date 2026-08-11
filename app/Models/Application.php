<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Application extends Model
{
    use HasFactory;

    protected $guarded = ['id', 'created_at', 'updated_at'];

    public function contest(): BelongsTo
    {
        return $this->belongsTo(Contest::class);
    }

    public function calculateScore(): float
    {
        $code = (string) $this->position;
        $pos = Position::where('code', $code)->first();
        $contestType = $this->contest?->contestType
            ?? $pos?->contestType
            ?? ContestType::where('code', $pos?->type ?? 'cadre')->first();

        $typeCode       = $contestType?->code ?? ($pos?->type ?? ($this->contest?->type ?? 'cadre'));
        $bacFactor      = $contestType?->coeff_bac_btp      ?? ($this->contest?->bac_factor ?? 0.6);
        $gradFactor     = $contestType?->coeff_grad_degree   ?? ($this->contest?->grad_factor ?? 0.4);
        $bonusPerYear   = $contestType?->bonus_per_extra_year ?? 1.0;
        $maxExtraYears  = $contestType?->max_extra_years      ?? 4;

        // Champ de la moyenne de base : configurable par ContestType
        $baseAverageField = $contestType?->base_average_field ?? 'bac_average';

        $score = 0.0;

        // --- Types avec BAC/diplôme : cadre, technicien ---
        if ($typeCode === 'cadre' || $typeCode === 'technicien') {
            $baseAvg = match ($baseAverageField) {
                'btp_average'   => $this->btp_average ?? $this->bac_average ?? 0,
                'grade_9_average' => $this->grade_9_average ?? 0,
                'grade_6_average' => $this->grade_6_average ?? 0,
                default          => $this->bac_average ?? 0,  // 'bac_average'
            };
            $score = ($baseAvg * $bacFactor) + (($this->grad_average ?? 0) * $gradFactor);

        // --- Types avec niveau scolaire + bonus d'années : commis, chauffeur, nettoyage ---
        } elseif (in_array($typeCode, ['commis', 'chauffeur', 'nettoyage'])) {

            // Résolution du nombre d'années supplémentaires
            // 1. Via school_levels JSON du ContestType (paramétrable depuis l'admin)
            // 2. Fallback : 0
            $extraYears = 0;
            if ($this->school_level && $contestType) {
                $extraYears = $contestType->getExtraYears($this->school_level);
            }

            $cappedYears = min($maxExtraYears, $extraYears);

            // Moyenne de base selon le champ configuré
            $baseAvg = match ($baseAverageField) {
                'grade_6_average' => $this->grade_6_average ?? 0,
                'grade_9_average' => $this->grade_9_average ?? $this->grade_6_average ?? 0,
                default           => $this->grade_9_average ?? $this->grade_6_average ?? 0,
            };

            $score = $baseAvg + ($cappedYears * $bonusPerYear);
            // Note : l'âge n'est PAS un bonus de score.
            // Il est vérifié comme critère d'éligibilité dans booted().
        }

        return round($score, 3);
    }

    protected function score(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value, array $attributes) => $attributes['calculated_score'] ?? $this->calculateScore()
        );
    }

    protected static function booted(): void
    {
        static::saving(function (Application $app) {
            // 1. Calcul du score
            $score = $app->calculateScore();
            $app->calculated_score = $score;

            // 2. Récupération du ContestType pour les critères d'éligibilité
            $pos         = Position::where('code', (string) $app->position)->first();
            $contestType = $app->contest?->contestType
                ?? $pos?->contestType
                ?? ContestType::where('code', $pos?->type ?? 'cadre')->first();

            // 3. Vérification du score minimum
            $minScore     = $app->contest?->min_score ?? $contestType?->min_score ?? 12.0;
            $scoreOk      = ($score >= $minScore);

            // 4. Vérification de l'âge maximum (critère d'éligibilité, pas un bonus)
            //    Si le ContestType définit un max_age + age_reference_date,
            //    le candidat est rejeté s'il dépasse l'âge limite à cette date.
            $ageOk = $contestType
                ? $contestType->isAgeEligible($app->birth_date)
                : true;

            // 5. Le candidat est admissible seulement si les deux critères sont remplis
            $app->is_admissible = $scoreOk && $ageOk;
        });
    }

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'cin_date' => 'date',
            'graduation_year' => 'integer',
            'equivalence_date' => 'date',
            'bac_year' => 'integer',
            'driving_license_date' => 'date',
            'is_admissible' => 'boolean',
        ];
    }

    public static function purgeTestApplicationsAndResetCounter(?int $contestId = null): int
    {
        $query = static::query();
        if ($contestId) {
            $query->where('contest_id', $contestId);
        }
        $count = $query->delete();

        if (static::count() === 0) {
            if (\Illuminate\Support\Facades\DB::getDriverName() === 'sqlite') {
                \Illuminate\Support\Facades\DB::statement("DELETE FROM sqlite_sequence WHERE name='applications'");
            } else {
                \Illuminate\Support\Facades\DB::statement("ALTER TABLE applications AUTO_INCREMENT = 1");
            }
        }

        return $count;
    }
}
