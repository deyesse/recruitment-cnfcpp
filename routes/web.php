<?php

use App\Http\Requests\ApplicationRequest;
use App\Models\Position;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {

    $contest = \App\Models\Contest::query()->where('ends_at', '>', now())->first();
    if ($contest == null) {
        $lastContest = \App\Models\Contest::query()->orderBy('ends_at', 'desc')->first();
        return view('end', ['lastContest' => $lastContest]);
    }
    $deadline = $contest->ends_at->toIso8601String();

    $positionsQuery = $contest->positions()->exists()
        ? $contest->positions()
        : \App\Models\Position::query();

    $positions = $positionsQuery->with('contestType')->get()->map(function ($pos) {
        return [
            'id' => $pos->id,
            'code' => $pos->code,
            'name' => $pos->name,
            'type' => $pos->type,
            'degree' => $pos->degree,
            'specialty' => $pos->specialty,
            'min_age' => $pos->contestType?->min_age,
            'max_age' => $pos->contestType?->max_age,
            'age_reference_date' => $pos->contestType?->age_reference_date?->format('Y-m-d'),
            'driving_license_min_years' => $pos->contestType?->driving_license_min_years ?? 2,
            'school_levels' => $pos->contestType?->school_levels ?? [],
        ];
    })->toArray();
    $degrees = $contest->degrees ?? [];

    $isTestMode = (bool) ($contest->is_test_mode ?? false);
    $isTestUnlocked = (bool) session('test_mode_unlocked_' . $contest->id, false);

    return Inertia::render('welcome', [
        'positions' => $positions,
        'deadline' => $deadline,
        'degrees' => $degrees,
        'currentlyRecruiting' => true,
        'contestName' => $contest->name,
        'showScore' => (bool) ($contest->show_score ?? true),
        'isTestMode' => $isTestMode,
        'isTestUnlocked' => $isTestUnlocked,
    ]);
})->name('home');

Route::post('/unlock-test-mode', function (\Illuminate\Http\Request $request) {
    $contest = \App\Models\Contest::query()->where('ends_at', '>', now())->first();
    if (! $contest || ! $contest->is_test_mode) {
        return back();
    }

    $request->validate([
        'test_code' => 'required|string',
    ], [
        'test_code.required' => 'رمز الاختبار إلزامي.',
    ]);

    if (trim($request->input('test_code')) === trim($contest->test_code)) {
        session(['test_mode_unlocked_' . $contest->id => true]);
        return back();
    }

    return back()->withErrors([
        'test_code' => 'رمز الاختبار غير صحيح. الرجاء التثبت من الرمز.',
    ]);
});

Route::get('/success', function () {
    $data = session('data') ?? [];
    if (! isset($data['logo_url'])) {
        $contest = \App\Models\Contest::query()->where('ends_at', '>', now())->first()
            ?? (isset($data['contest_id']) ? \App\Models\Contest::find($data['contest_id']) : null);
        $data['logo_url'] = $contest?->logo_path ? asset('storage/' . $contest->logo_path) : asset('cnfcpp.png');
    }
    return Inertia::render('success', [
        'data' => $data,
    ]);
});

Route::post('/apply', function (ApplicationRequest $request) {

    $contest = \App\Models\Contest::query()->where('ends_at', '>', now())->first();

    if ($contest?->is_test_mode && ! session('test_mode_unlocked_' . $contest->id)) {
        return back()->withErrors([
            'test_code' => 'المناظرة في وضع الاختبار. يرجى إدخال رمز الاختبار أولاً.',
        ]);
    }

    $validated = $request->validated();

    $validated['contest_id'] = $contest?->id;
    $validated['contest_name'] = $contest?->name;
    $validated['show_score'] = (bool) ($contest?->show_score ?? true);
    $validated['header_text'] = $contest?->header_text;
    $validated['logo_url'] = $contest?->logo_path ? asset('storage/' . $contest->logo_path) : asset('cnfcpp.png');
    $positionModel = Position::where('code', $validated['position'])->with('contestType')->first();
    $validated['position_name'] = $positionModel?->name;
    $validated['position_type'] = $positionModel?->type ?? ($positionModel?->contestType?->code ?? 'cadre');
    $validated['base_average_field'] = $positionModel?->contestType?->base_average_field ?? 'bac_average';
    $validated['min_score'] = $positionModel?->contestType?->min_score ?? 12.0;

    $app = \App\Models\Application::create(Arr::except($validated, ['agreement', 'contest_name', 'show_score', 'header_text', 'logo_url']));
    $validated['id'] = $app->id;
    $validated['score'] = $app->score;
    $validated['created_at'] = $app->created_at?->format('d/m/Y H:i') ?? now()->format('d/m/Y H:i');

    // Format dates in French format (d/m/Y) for presentation
    foreach (['birth_date', 'cin_date', 'equivalence_date', 'driving_license_date'] as $dateField) {
        if (! empty($validated[$dateField])) {
            try { $validated[$dateField] = \Carbon\Carbon::parse($validated[$dateField])->format('d/m/Y'); } catch (\Exception $e) {}
        }
    }

    return Redirect::to('/success')->with(['data' => $validated]);

});

Route::get('/reprint', function () {
    return Inertia::render('reprint');
});

Route::post('/reprint', function (\Illuminate\Http\Request $request) {
    $credentials = $request->validate([
        'cin' => 'required|numeric|digits:8',
        'tel' => 'required|numeric|digits:8',
        'registration_date' => 'required',
    ], [
        'cin.required' => 'رقم بطاقة التعريف الوطنية إلزامي.',
        'cin.digits' => 'رقم بطاقة التعريف الوطنية يجب أن يتكون من 8 أرقام.',
        'tel.required' => 'رقم الهاتف الجوال إلزامي.',
        'tel.digits' => 'رقم الهاتف يجب أن يتكون من 8 أرقام.',
        'registration_date.required' => 'تاريخ التسجيل إلزامي.',
    ]);

    $rawDate = $request->input('registration_date');
    $searchDate = null;
    try {
        if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $rawDate)) {
            $searchDate = \Carbon\Carbon::createFromFormat('d/m/Y', $rawDate)->format('Y-m-d');
        } else {
            $searchDate = \Carbon\Carbon::parse($rawDate)->format('Y-m-d');
        }
    } catch (\Exception $e) {
        $searchDate = $rawDate;
    }

    $app = \App\Models\Application::with('contest')->where('cin', $credentials['cin'])
        ->where('tel', $credentials['tel'])
        ->whereDate('created_at', $searchDate)
        ->first();

    if (! $app) {
        return back()->withErrors([
            'search' => 'عذراً، لم يتم العثور على أي مطلب ترشح بهذه البيانات (رقم بطاقة التعريف، رقم الهاتف، وتاريخ التسجيل). الرجاء التثبت من صحة المعطيات.',
        ]);
    }

    $posName = $app->position_name
        ?? Position::where('code', (string) $app->position)->first()?->name
        ?? '';

    $contest = $app->contest ?? \App\Models\Contest::find($app->contest_id);
    $contestName = $contest?->name ?? '';
    $showScore = (bool) ($contest?->show_score ?? true);

    $positionForReprint = Position::where('code', (string) $app->position)->with('contestType')->first();

    $data = [
        'id' => $app->id,
        'contest_name' => $contestName,
        'show_score' => $showScore,
        'header_text' => $contest?->header_text,
        'logo_url' => $contest?->logo_path ? asset('storage/' . $contest->logo_path) : asset('cnfcpp.png'),
        'position' => (string) $app->position,
        'position_name' => $posName,
        'position_type' => $positionForReprint?->type ?? ($positionForReprint?->contestType?->code ?? 'cadre'),
        'base_average_field' => $positionForReprint?->contestType?->base_average_field ?? 'bac_average',
        'min_score' => $positionForReprint?->contestType?->min_score ?? 12.0,
        'name' => $app->name,
        'gender' => $app->gender,
        'birth_date' => $app->birth_date?->format('d/m/Y'),
        'address' => $app->address,
        'city' => $app->city,
        'governorate' => $app->governorate,
        'postal_code' => $app->postal_code,
        'cin' => $app->cin,
        'cin_date' => $app->cin_date?->format('d/m/Y'),
        'tel' => $app->tel,
        'email' => $app->email,
        // Cadre / Technicien fields
        'degree' => $app->degree,
        'specialty' => $app->specialty,
        'graduation_year' => $app->graduation_year,
        'institution' => $app->institution,
        'equivalence_decision' => $app->equivalence_decision,
        'equivalence_date' => $app->equivalence_date?->format('d/m/Y'),
        'bac_average' => $app->bac_average,
        'btp_average' => $app->btp_average,
        'grad_average' => $app->grad_average,
        // Commis / Chauffeur / Nettoyage fields
        'school_level' => $app->school_level,
        'end_school_year' => $app->end_school_year,
        'school_institution' => $app->school_institution,
        'grade_9_average' => $app->grade_9_average,
        'grade_6_average' => $app->grade_6_average,
        // Chauffeur fields
        'driving_license_category' => $app->driving_license_category,
        'driving_license_date' => $app->driving_license_date?->format('d/m/Y'),
        'score' => $app->calculated_score ?? $app->calculateScore(),
        'created_at' => $app->created_at?->format('d/m/Y H:i') ?? now()->format('d/m/Y H:i'),
    ];

    return Redirect::to('/success')->with(['data' => $data]);
});
// require __DIR__.'/settings.php';
