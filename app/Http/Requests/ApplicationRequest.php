<?php

namespace App\Http\Requests;

use App\Models\Application;
use App\Models\Position;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ApplicationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        $code = (string) $this->input('position');

        $rules = [
            'position' => 'required|string|exists:positions,code',
            'name' => 'required|string|max:255',
            'gender' => 'required|string|max:10',
            'birth_date' => 'required|date',
            'address' => 'required|string|max:500',
            'governorate' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'postal_code' => 'required|numeric|digits:4',
            'cin' => 'required|numeric|digits:8',
            'cin_date' => 'required|date',
            'tel' => 'required|numeric|digits:8',
            'email' => 'required|email|max:255',
            'agreement' => 'accepted',
        ];

        if (in_array($code, ['17', '18'])) {
            // Commis
            $rules['school_level'] = 'required|string|max:255';
            $rules['end_school_year'] = 'nullable|integer|min:1950|max:2026';
            $rules['school_institution'] = 'nullable|string|max:255';
            $rules['grade_9_average'] = 'required|numeric|min:0|max:20';
        } elseif ($code === '19') {
            // Chauffeur
            $rules['school_level'] = 'required|string|max:255';
            $rules['end_school_year'] = 'nullable|integer|min:1950|max:2026';
            $rules['school_institution'] = 'nullable|string|max:255';
            $rules['grade_9_average'] = 'required|numeric|min:0|max:20';
            $rules['driving_license_category'] = 'required|string|max:50';
            $rules['driving_license_date'] = 'required|date';
        } elseif (in_array($code, ['20', '21'])) {
            // Nettoyage
            $rules['school_level'] = 'required|string|max:255';
            $rules['end_school_year'] = 'nullable|integer|min:1950|max:2026';
            $rules['school_institution'] = 'nullable|string|max:255';
            $rules['grade_6_average'] = 'required|numeric|min:0|max:20';
        } elseif ($code === '16') {
            // Technicien Supérieur
            $rules['degree'] = 'nullable|string|max:255';
            $rules['specialty'] = 'required|string|max:255';
            $rules['graduation_year'] = 'required|integer|min:1900|max:2100';
            $rules['institution'] = 'nullable|string|max:255';
            $rules['equivalence_decision'] = 'nullable|string|max:255';
            $rules['equivalence_date'] = 'required_with:equivalence_decision|nullable|date';
            $rules['bac_average'] = 'nullable|numeric|max:20|min:0';
            $rules['btp_average'] = 'nullable|numeric|max:20|min:0';
            $rules['grad_average'] = 'required|numeric|max:20|min:0';
        } else {
            // Cadres (01 - 15)
            $rules['degree'] = 'nullable|string|max:255';
            $rules['specialty'] = 'required|string|max:255';
            $rules['graduation_year'] = 'required|integer|min:1900|max:2100';
            $rules['institution'] = 'nullable|string|max:255';
            $rules['equivalence_decision'] = 'nullable|string|max:255';
            $rules['equivalence_date'] = 'required_with:equivalence_decision|nullable|date';
            $rules['bac_average'] = 'required|numeric|max:20|min:0';
            $rules['grad_average'] = 'required|numeric|max:20|min:0';
        }

        return $rules;
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ($validator->errors()->any()) {
                return;
            }

            $code = (string) $this->input('position');
            $birthDate = $this->input('birth_date');
            $cin = $this->input('cin');
            $email = $this->input('email');

            // Resolve contest and its uniqueness mode
            $contest = \App\Models\Contest::query()->where('ends_at', '>', now())->first();
            $uniquenessMode = $contest?->uniqueness_mode ?? 'per_contest';
            $contestId = $contest?->id;

            // -----------------------------------------------
            // Duplicate Check based on uniqueness mode
            // -----------------------------------------------
            $duplicateQuery = \App\Models\Application::where('contest_id', $contestId)
                ->where('cin', $cin);

            if ($uniquenessMode === 'per_type') {
                // Find the contest_type_id of the selected position
                $selectedPos = Position::where('code', $code)->first();
                $contestTypeId = $selectedPos?->contest_type_id;
                // Restrict check to applications that share the same contest_type
                if ($contestTypeId) {
                    $positionCodesOfSameType = Position::where('contest_type_id', $contestTypeId)
                        ->pluck('code');
                    $duplicateQuery->whereIn('position', $positionCodesOfSameType);
                }
            } elseif ($uniquenessMode === 'per_position') {
                // Restrict check to the exact same position code
                $duplicateQuery->where('position', $code);
            }
            // 'per_contest' => no extra filter, check CIN across entire contest

            if ($duplicateQuery->exists()) {
                $modeMsg = match($uniquenessMode) {
                    'per_type'     => 'لقد قمت بالتسجيل مسبقاً في هذا الصنف من المناظرة.',
                    'per_position' => 'لقد قمت بالتسجيل مسبقاً في هذه الخطة الوظيفية.',
                    default        => 'لقد قمت بالتسجيل مسبقاً في هذه المناظرة.',
                };
                $validator->errors()->add('cin', $modeMsg);
                return;
            }

            // Email uniqueness scoped to contest
            if (\App\Models\Application::where('contest_id', $contestId)->where('email', $email)->exists()) {
                $validator->errors()->add('email', 'تم استعمال هذا البريد الإلكتروني في هذه المناظرة من قبل.');
                return;
            }

            if ($code) {
                $pos = Position::where('code', $code)->first();
                $contestType = $pos?->contestType;

                // 1. Check Age Limit (Min & Max)
                if ($birthDate && $contestType && ! $contestType->isAgeEligible($birthDate)) {
                    $minAge = $contestType->min_age;
                    $maxAge = $contestType->max_age;
                    $refDate = $contestType->age_reference_date
                        ? $contestType->age_reference_date->format('d/m/Y')
                        : 'غرة جانفي';

                    $refCarbon = $contestType->age_reference_date ?? now()->startOfYear();
                    $candidateAge = \Carbon\Carbon::parse($birthDate)->diffInYears($refCarbon);

                    if ($minAge && $candidateAge < $minAge) {
                        $validator->errors()->add(
                            'birth_date',
                            "عذراً، سن المترشح ({$candidateAge} سنة) أقل من السن الأدنى المسموح به لهذا الصنف ({$minAge} سنة بتاريخ {$refDate})."
                        );
                    } elseif ($maxAge && $candidateAge > $maxAge) {
                        $validator->errors()->add(
                            'birth_date',
                            "عذراً، يتجاوز سن المترشح ({$candidateAge} سنة) السن الأقصى المسموح به لهذا الصنف ({$maxAge} سنة بتاريخ {$refDate})."
                        );
                    }
                }

                // 2. Check Minimum Score Eligibility
                $tempApp = new Application($this->all());
                $score = $tempApp->calculateScore();
                $minScore = $contestType?->min_score ?? 12.0;

                if ($score < $minScore) {
                    $formattedScore = number_format($score, 2);
                    $formattedMin = number_format($minScore, 2);
                    $validator->errors()->add(
                        'bac_average',
                        "عذراً، مجموع النقاط المتحصل عليه ({$formattedScore}) دون الحد الأدنى المطلوب لقبول الترشح ({$formattedMin})."
                    );
                }

                // 3. Check Driving License Seniority (Configurable, default 2 years before reference date)
                if ($code === '19' || ($pos && $pos->type === 'chauffeur') || ($contestType && $contestType->has_driving_license)) {
                    $licenseDateInput = $this->input('driving_license_date');
                    if ($licenseDateInput) {
                        $minYears = $contestType?->driving_license_min_years ?? 2;
                        $refCarbon = $contestType?->age_reference_date ?? ($contest?->ends_at ?? now());
                        $licenseCarbon = \Carbon\Carbon::parse($licenseDateInput);

                        if ($licenseCarbon->copy()->addYears($minYears)->isAfter($refCarbon)) {
                            $refFormatted = \Carbon\Carbon::parse($refCarbon)->format('d/m/Y');
                            $validator->errors()->add(
                                'driving_license_date',
                                "عذراً، يجب أن يكون المترشح متحصل على رخصة السياقة منذ {$minYears} سنوات على الأقل بتاريخ المرجع ({$refFormatted})."
                            );
                        }
                    }
                }
            }
        });
    }

    public function messages()
    {
        return [
            'position.required' => 'حقل الخطة الوظيفية إلزامي.',
            'position.numeric' => 'قيمة الخطة الوظيفية يجب أن تكون رقمًا.',
            'position.exists' => 'الخطة الوظيفية المختارة غير موجودة.',

            'name.required' => 'حقل الاسم إلزامي.',
            'name.string' => 'الاسم يجب أن يكون نصاً.',
            'name.max' => 'الاسم يجب ألا يتجاوز 255 حرفاً.',

            'gender.required' => 'حقل الجنس إلزامي.',
            'gender.string' => 'الجنس يجب أن يكون نصاً.',
            'gender.max' => 'الجنس يجب ألا يتجاوز 10 أحرف.',

            'birth_date.required' => 'حقل تاريخ الولادة إلزامي.',
            'birth_date.date' => 'تاريخ الولادة غير صالح.',

            'birth_place.required' => 'حقل مكان الولادة إلزامي.',
            'birth_place.string' => 'مكان الولادة يجب أن يكون نصاً.',
            'birth_place.max' => 'مكان الولادة يجب ألا يتجاوز 255 حرفاً.',

            'address.required' => 'حقل العنوان إلزامي.',
            'address.string' => 'العنوان يجب أن يكون نصاً.',
            'address.max' => 'العنوان يجب ألا يتجاوز 500 حرفاً.',

            'governorate.required' => 'حقل الولاية إلزامي.',
            'governorate.string' => 'الولاية يجب أن تكون نصاً.',
            'governorate.max' => 'الولاية يجب ألا تتجاوز 255 حرفاً.',

            'postal_code.required' => 'حقل الرمز البريدي إلزامي.',
            'postal_code.integer' => 'الرمز البريدي يجب أن يكون رقماً.',
            'postal_code.digits' => 'الرمز البريدي يجب أن يحتوي على 4 أرقام.',

            'cin.required' => 'حقل رقم بطاقة التعريف الوطنية إلزامي.',
            'cin.unique' => 'تم استعمال رقم بطاقة التعريف الوطنية من قبل.',
            'cin.numeric' => 'رقم بطاقة التعريف يجب أن يكون رقماً.',
            'cin.digits' => 'رقم بطاقة التعريف يجب أن يحتوي على 8 أرقام بالضبط.',

            'agreement.accepted' => 'يجب المصادقة على صحة البيانات.',

            'cin_date.required' => 'حقل تاريخ إصدار بطاقة التعريف إلزامي.',
            'cin_date.date' => 'تاريخ إصدار بطاقة التعريف غير صالح.',

            'tel.required' => 'رقم الهاتف الجوال إلزامي.',
            'tel.numeric' => 'رقم الهاتف الجوال يجب أن يتكون من أرقام فقط.',
            'tel.digits' => 'رقم الهاتف الجوال يجب أن يتكون من 8 أرقام بالضبط.',

            'grad_average.max' => 'معدل التخرج يجب ألا يتجاوز 20.',
            'grad_average.min' => 'معدل التخرج يجب ألا يقل عن 0.',
            'bac_average.min' => 'معدل البكالوريا يجب ألا يقل عن 0.',
            'bac_average.max' => 'معدل البكالوريا يجب ألا يتجاوز 20.',

            'email.required' => 'البريد الإلكتروني إلزامي.',
            'email.email' => 'صيغة البريد الإلكتروني غير صالحة.',
            'email.max' => 'البريد الإلكتروني يجب ألا يتجاوز 255 حرفاً.',
            'email.unique' => 'تم استعمال البريد الإلكتروني من قبل.',

            'degree.string' => 'الشهادة يجب أن تكون نصاً.',
            'degree.max' => 'الشهادة يجب ألا تتجاوز 255 حرفاً.',

            'specialty.required' => 'الاختصاص إلزامي.',
            'specialty.string' => 'الاختصاص يجب أن يكون نصاً.',
            'specialty.max' => 'الاختصاص يجب ألا يتجاوز 255 حرفاً.',

            'graduation_year.required' => 'سنة التخرج إلزامية.',
            'graduation_year.integer' => 'سنة التخرج يجب أن تكون رقماً.',
            'graduation_year.min' => 'سنة التخرج لا يمكن أن تكون أقل من 1900.',
            'graduation_year.max' => 'سنة التخرج لا يمكن أن تتجاوز 2100.',

            'equivalence_decision.string' => 'قرار المعادلة يجب أن يكون نصاً.',
            'equivalence_decision.max' => 'قرار المعادلة يجب ألا يتجاوز 255 حرفاً.',

            'equivalence_date.required_with' => 'تاريخ المعادلة إلزامي.',
            'equivalence_date.date' => 'تاريخ المعادلة غير صالح.',

            'bac_average.required' => 'معدل البكالوريا إلزامي.',
            'bac_average.numeric' => 'معدل البكالوريا يجب أن يكون رقماً.',

            'grad_average.required' => 'معدل التخرج إلزامي.',
            'grad_average.numeric' => 'معدل التخرج يجب أن يكون رقماً.',
        ];
    }
}
