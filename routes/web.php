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
            'max_age' => $pos->contestType?->max_age,
            'age_reference_date' => $pos->contestType?->age_reference_date?->format('Y-m-d'),
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
    return Inertia::render('success', [
        'data' => session('data') ?? [],
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
    $validated['position_name'] = Position::where('code', $validated['position'])->first()?->name;

    $app = \App\Models\Application::create(Arr::except($validated, ['agreement', 'contest_name', 'show_score', 'header_text']));
    $validated['id'] = $app->id;
    $validated['score'] = $app->score;

    // Format dates in French format (d/m/Y) for presentation
    if (! empty($validated['birth_date'])) {
        try { $validated['birth_date'] = \Carbon\Carbon::parse($validated['birth_date'])->format('d/m/Y'); } catch (\Exception $e) {}
    }
    if (! empty($validated['cin_date'])) {
        try { $validated['cin_date'] = \Carbon\Carbon::parse($validated['cin_date'])->format('d/m/Y'); } catch (\Exception $e) {}
    }
    if (! empty($validated['equivalence_date'])) {
        try { $validated['equivalence_date'] = \Carbon\Carbon::parse($validated['equivalence_date'])->format('d/m/Y'); } catch (\Exception $e) {}
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

    $data = [
        'id' => $app->id,
        'contest_name' => $contestName,
        'show_score' => $showScore,
        'header_text' => $contest?->header_text,
        'position' => (string) $app->position,
        'position_name' => $posName,
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
        'degree' => $app->degree,
        'specialty' => $app->specialty,
        'graduation_year' => $app->graduation_year,
        'equivalence_decision' => $app->equivalence_decision,
        'equivalence_date' => $app->equivalence_date?->format('d/m/Y'),
        'bac_average' => $app->bac_average,
        'grad_average' => $app->grad_average,
        'score' => $app->calculated_score ?? $app->calculateScore(),
    ];

    return Redirect::to('/success')->with(['data' => $data]);
});
// require __DIR__.'/settings.php';
