@php
    $data = session('data') ?? [];
    $posCode = (string)($data['position'] ?? '');
    $posNum = (int)$posCode;
    $posName = $data['position_name'] ?? '';
    $posType = $data['position_type'] ?? (
        $posNum >= 1 && $posNum <= 15 ? 'cadre' :
        ($posCode === '16' ? 'technicien' :
        (in_array($posCode, ['17', '18']) ? 'commis' :
        ($posCode === '19' ? 'chauffeur' :
        (in_array($posCode, ['20', '21']) ? 'nettoyage' : 'cadre'))))
    );

    $isCadre = $posType === 'cadre';
    $isTechnicien = $posType === 'technicien';
    $isCommis = $posType === 'commis';
    $isChauffeur = $posType === 'chauffeur';
    $isNettoyage = $posType === 'nettoyage';

    if ($posName) {
        $catTitle = $posName;
        $catRef = "الخطة " . $posCode;
    } elseif ($isCadre) {
        $catTitle = "المهندسين والمحللين\nوالمتصرفين";
        $catRef = $posCode ?: "من 01 إلى 15";
    } elseif ($isTechnicien) {
        $catTitle = "ملحق إدارة\nمؤهل التقني السامي";
        $catRef = "الخطة " . ($posCode ?: "16");
    } elseif ($isCommis) {
        $catTitle = "مستكتب إدارة";
        $catRef = "الخطة " . ($posCode ?: "17 و 18");
    } elseif ($isChauffeur) {
        $catTitle = "سائق";
        $catRef = "الخطة " . ($posCode ?: "19");
    } elseif ($isNettoyage) {
        $catTitle = "عون تنظيف";
        $catRef = "الخطة " . ($posCode ?: "20 و 21");
    } else {
        $catTitle = "خطة " . $posCode;
        $catRef = "الخطة " . $posCode;
    }
@endphp
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>استمارة ترشح - {{ $data['name'] ?? 'مطلب ترشح' }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=tajawal:400,500,700,800,900" rel="stylesheet" />
    <style>
        body { font-family: 'Tajawal', sans-serif; }
        @media print {
            * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
            .no-print { display: none !important; }
            html, body { background: white !important; color: #0f172a !important; font-size: 11px !important; margin: 0 !important; }
            @page { size: A4 portrait; margin: 8mm 9mm; }
            .sheet-card { border: none !important; box-shadow: none !important; padding: 0 !important; }
            .doc-table { border-collapse: collapse !important; border: 1.5px solid #0f172a !important; }
            .doc-table th, .doc-table td { border: 1px solid #64748b !important; padding: 2.5px 5px !important; font-size: 10.5px !important; }
            .doc-table th { background-color: #f1f5f9 !important; font-weight: 700 !important; }
        }
    </style>
</head>
<body class="bg-slate-100 min-h-screen text-slate-900">

    <div class="no-print max-w-4xl mx-auto pt-8 px-4">
        <div class="bg-white rounded-2xl shadow-lg border border-slate-200 p-6 mb-6 flex flex-col md:flex-row items-center justify-between gap-4">
            <div>
                <h1 class="text-xl font-bold text-slate-900">تم تسجيل مطلب ترشحكم بنجاح</h1>
                <p class="text-sm text-slate-600 mt-1">يرجى طباعة استمارة الترشح وإمضاؤها لإرفاقها بالملف الورقي.</p>
            </div>
            <button onclick="window.print()" class="bg-emerald-600 hover:bg-emerald-700 text-white px-6 py-3 rounded-xl font-bold text-sm shadow-md transition">
                🖨️ طباعة الاستمارة / حفظ PDF
            </button>
        </div>
    </div>

    <div class="max-w-4xl mx-auto px-4 pb-12 print:p-0 print:m-0 print:max-w-none">
        <div class="sheet-card bg-white shadow-xl rounded-2xl p-6 md:p-8 border border-slate-200 print:shadow-none print:border-none print:p-0">

            <!-- Header -->
            <header class="border-b-2 border-slate-900 pb-3 mb-4">
                <div class="flex flex-row items-stretch justify-between gap-3">
                    <div class="text-right text-xs leading-relaxed font-semibold text-slate-800 flex flex-col justify-center min-w-[190px]">
                        @php
                            $headerLines = !empty(trim($data['header_text'] ?? ''))
                                ? array_filter(array_map('trim', explode("\n", $data['header_text'])))
                                : [
                                    'الجمهورية التونسية',
                                    'وزارة التشغيل والتكوين المهني',
                                    'المركز الوطني لتكوين المكونين وهندسة التكوين'
                                ];
                        @endphp
                        @foreach($headerLines as $idx => $line)
                            <div class="{{ $idx === 0 ? 'text-slate-900 font-bold' : ($loop->last ? 'text-slate-950 font-extrabold text-[12px] mt-0.5' : 'text-slate-800 font-semibold') }}">
                                {{ $line }}
                            </div>
                        @endforeach
                    </div>

                    <div class="flex-1 text-center flex flex-col justify-center px-2">
                        @if(!empty($data['contest_name']))
                            @if(str_starts_with($data['contest_name'], 'استمارة'))
                                <h1 class="text-lg md:text-xl font-black text-slate-950 leading-snug">
                                    {{ $data['contest_name'] }}
                                </h1>
                            @else
                                <h1 class="text-base md:text-lg font-extrabold text-slate-950 leading-snug">
                                    استمارة ترشح
                                </h1>
                                <div class="text-xs md:text-sm font-bold text-slate-800 mt-0.5">
                                    {{ $data['contest_name'] }}
                                </div>
                            @endif
                        @else
                            <h1 class="text-lg md:text-xl font-black text-slate-950 leading-snug">
                                استمارة ترشح للمشاركة في المناظرة الخارجية لانتداب إطارات وأعوان بعنوان سنتي 2025 و2026
                            </h1>
                        @endif
                    </div>

                    <div class="flex items-center justify-end min-w-[130px]">
                        <div class="border-2 border-slate-900 rounded-lg p-2 text-center bg-slate-50 w-full">
                            <div class="text-[11px] font-bold text-slate-800 leading-tight whitespace-pre-line">
                                {!! nl2br                <div class="flex items-center justify-between mt-3 pt-2 border-t border-slate-300">
                    <div class="flex items-center gap-2 md:gap-3">
                        <div class="border border-slate-900 rounded px-3 py-1 text-xs md:text-sm font-bold bg-slate-50 text-slate-950">
                            رقم التسجيل: <span class="font-mono font-black text-sm md:text-base mr-1">{{ $data['id'] ?? '-' }}</span>
                        </div>
                        @if(!empty($data['created_at']))
                            <div class="border border-slate-900 rounded px-3 py-1 text-xs md:text-sm font-bold bg-slate-50 text-slate-950">
                                تاريخ الترشح الإلكتروني: <span class="font-mono font-semibold text-xs md:text-sm mr-1">{{ $data['created_at'] }}</span>
                            </div>
                        @endif
                    </div>
                    @if(($data['show_score'] ?? true) !== false)
                        <div class="border border-slate-900 rounded px-3 py-1 text-xs md:text-sm font-bold bg-slate-50 text-slate-950">
                            مجموع النقاط: <span class="font-mono font-black text-sm md:text-base mr-1">{{ $data['score'] ?? '-' }}</span>
                        </div>
                    @endif
                </div>
            </header>

            <!-- I- Personal info -->
            <section className="mb-3">
                <h2 class="text-sm md:text-base font-extrabold text-slate-900 bg-slate-200/80 px-2.5 py-1 rounded border-r-4 border-slate-900 mb-1.5">
                    I- الهوية والمعلومات الشخصية
                </h2>
                <table class="doc-table w-full text-right text-xs border border-slate-900">
                    <tbody>
                        <tr>
                            <th class="w-[22%] bg-slate-100 font-bold border border-slate-400 p-1.5 text-slate-900">رمز وخطة المناظرة</th>
                            <td colspan="3" class="border border-slate-400 p-1.5 font-bold text-slate-950">
                                <span class="bg-slate-900 text-white px-2 py-0.5 rounded text-xs font-mono ml-2">{{ $data['position'] ?? '-' }}</span>
                                <span>{{ $data['position_name'] ?? $catTitle }}</span>
                            </td>
                        </tr>
                        <tr>
                            <th class="w-[22%] bg-slate-100 font-bold border border-slate-400 p-1.5 text-slate-900">الاسم واللقب</th>
                            <td class="w-[28%] border border-slate-400 p-1.5 font-extrabold text-slate-950">{{ $data['name'] ?? '-' }}</td>
                            <th class="w-[22%] bg-slate-100 font-bold border border-slate-400 p-1.5 text-slate-900">الجنس</th>
                            <td class="w-[28%] border border-slate-400 p-1.5 text-slate-950">{{ $data['gender'] ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th class="bg-slate-100 font-bold border border-slate-400 p-1.5 text-slate-900">رقم بطاقة التعريف</th>
                            <td class="border border-slate-400 p-1.5 font-mono font-bold text-slate-950">{{ $data['cin'] ?? '-' }}</td>
                            <th class="bg-slate-100 font-bold border border-slate-400 p-1.5 text-slate-900">تاريخ إصدارها</th>
                            <td class="border border-slate-400 p-1.5 font-mono text-slate-950">{{ $data['cin_date'] ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th class="bg-slate-100 font-bold border border-slate-400 p-1.5 text-slate-900">تاريخ الولادة</th>
                            <td class="border border-slate-400 p-1.5 font-mono text-slate-950">{{ $data['birth_date'] ?? '-' }}</td>
                            <th class="bg-slate-100 font-bold border border-slate-400 p-1.5 text-slate-900">رقم الهاتف</th>
                            <td class="border border-slate-400 p-1.5 font-mono font-bold text-slate-950">{{ $data['tel'] ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th class="bg-slate-100 font-bold border border-slate-400 p-1.5 text-slate-900">العنوان الحالي</th>
                            <td class="border border-slate-400 p-1.5 text-slate-900">{{ $data['address'] ?? '-' }}</td>
                            <th class="bg-slate-100 font-bold border border-slate-400 p-1.5 text-slate-900">الترقيم البريدي</th>
                            <td class="border border-slate-400 p-1.5 font-mono text-slate-950">{{ $data['postal_code'] ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th class="bg-slate-100 font-bold border border-slate-400 p-1.5 text-slate-900">الولاية / المعتمدية</th>
                            <td class="border border-slate-400 p-1.5 text-slate-900">{{ trim(($data['governorate'] ?? '') . ' - ' . ($data['city'] ?? ''), ' -') ?: '-' }}</td>
                            <th class="bg-slate-100 font-bold border border-slate-400 p-1.5 text-slate-900">البريد الإلكتروني</th>
                            <td class="border border-slate-400 p-1.5 font-mono text-[11px] text-slate-950">{{ $data['email'] ?? '-' }}</td>
                        </tr>
                    </tbody>
                </table>
            </section>

            <!-- II- Profile specifics -->
            @if($isCadre)
                <section class="mb-3">
                    <h2 class="text-sm md:text-base font-extrabold text-slate-900 bg-slate-200/80 px-2.5 py-1 rounded border-r-4 border-slate-900 mb-1.5">
                        II- المستوى التعليمي
                    </h2>
                    <table class="doc-table w-full text-right text-xs border border-slate-900">
                        <tbody>
                            <tr>
                                <th class="w-[22%] bg-slate-100 font-bold border border-slate-400 p-1.5 text-slate-900">الشهادة العلمية</th>
                                <td class="w-[28%] border border-slate-400 p-1.5 font-bold text-slate-950">{{ $data['degree'] ?? '-' }}</td>
                                <th class="w-[22%] bg-slate-100 font-bold border border-slate-400 p-1.5 text-slate-900">الاختصاص</th>
                                <td class="w-[28%] border border-slate-400 p-1.5 font-bold text-slate-950">{{ $data['specialty'] ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th class="bg-slate-100 font-bold border border-slate-400 p-1.5 text-slate-900">سنة التخرج</th>
                                <td class="border border-slate-400 p-1.5 font-mono font-bold text-slate-950">{{ $data['graduation_year'] ?? '-' }}</td>
                                <th class="bg-slate-100 font-bold border border-slate-400 p-1.5 text-slate-900">المؤسسة الجامعية</th>
                                <td class="border border-slate-400 p-1.5 text-slate-950">{{ $data['institution'] ?? 'مؤسسة تعليم عال عمومية' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </section>
                <section class="mb-3">
                    <h2 class="text-sm md:text-base font-extrabold text-slate-900 bg-slate-200/80 px-2.5 py-1 rounded border-r-4 border-slate-900 mb-1.5">
                        III- المعدلات المطلوبة ومقياس الفرز الأولي
                    </h2>
                    <table class="doc-table w-full text-right text-xs border border-slate-900">
                        <tbody>
                            <tr>
                                <th class="w-[22%] bg-slate-100 font-bold border border-slate-400 p-1.5 text-slate-900">معدل البكالوريا</th>
                                <td class="w-[28%] border border-slate-400 p-1.5 font-mono font-bold text-slate-950">{{ $data['bac_average'] ?? '-' }}</td>
                                <th class="w-[22%] bg-slate-100 font-bold border border-slate-400 p-1.5 text-slate-900">معدل سنة التخرج</th>
                                <td class="w-[28%] border border-slate-400 p-1.5 font-mono font-bold text-slate-950">{{ $data['grad_average'] ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th class="bg-slate-100 font-bold border border-slate-400 p-1.5 text-slate-900">صيغة الفرز الأولي (مجموع النقاط)</th>
                                <td colspan="3" class="border border-slate-400 p-1.5 text-slate-950">
                                    <span class="font-semibold">(معدل البكالوريا × 60%) + (معدل سنة التخرج × 40%)</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </section>
            @elseif($isTechnicien)
                <section class="mb-3">
                    <h2 class="text-sm md:text-base font-extrabold text-slate-900 bg-slate-200/80 px-2.5 py-1 rounded border-r-4 border-slate-900 mb-1.5">
                        II- المستوى التعليمي والتكويني
                    </h2>
                    <table class="doc-table w-full text-right text-xs border border-slate-900">
                        <tbody>
                            <tr>
                                <th class="w-[22%] bg-slate-100 font-bold border border-slate-400 p-1.5 text-slate-900">الشهادة المطلوبة</th>
                                <td class="w-[28%] border border-slate-400 p-1.5 font-bold text-slate-950">مؤهل التقني السامي (BTP)</td>
                                <th class="w-[22%] bg-slate-100 font-bold border border-slate-400 p-1.5 text-slate-900">الاختصاص</th>
                                <td class="w-[28%] border border-slate-400 p-1.5 font-bold text-slate-950">{{ $data['specialty'] ?? 'مساعد مديرية' }}</td>
                            </tr>
                            <tr>
                                <th class="bg-slate-100 font-bold border border-slate-400 p-1.5 text-slate-900">سنة التخرج</th>
                                <td class="border border-slate-400 p-1.5 font-mono font-bold text-slate-950">{{ $data['graduation_year'] ?? '-' }}</td>
                                <th class="bg-slate-100 font-bold border border-slate-400 p-1.5 text-slate-900">مركز التكوين المهني</th>
                                <td class="border border-slate-400 p-1.5 text-slate-950">{{ $data['institution'] ?? '-' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </section>
                <section class="mb-3">
                    <h2 class="text-sm md:text-base font-extrabold text-slate-900 bg-slate-200/80 px-2.5 py-1 rounded border-r-4 border-slate-900 mb-1.5">
                        III- المعدلات المطلوبة ومقياس الفرز الأولي
                    </h2>
                    <table class="doc-table w-full text-right text-xs border border-slate-900">
                        <tbody>
                            <tr>
                                <th class="w-[22%] bg-slate-100 font-bold border border-slate-400 p-1.5 text-slate-900">معدل مؤهل التقني أو البكالوريا</th>
                                <td class="w-[28%] border border-slate-400 p-1.5 font-mono font-bold text-slate-950">{{ $data['btp_average'] ?? ($data['bac_average'] ?? '-') }}</td>
                                <th class="w-[22%] bg-slate-100 font-bold border border-slate-400 p-1.5 text-slate-900">معدل سنة التخرج</th>
                                <td class="w-[28%] border border-slate-400 p-1.5 font-mono font-bold text-slate-950">{{ $data['grad_average'] ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th class="bg-slate-100 font-bold border border-slate-400 p-1.5 text-slate-900">صيغة الفرز الأولي (مجموع النقاط)</th>
                                <td colspan="3" class="border border-slate-400 p-1.5 text-slate-950">
                                    <span class="font-semibold">(معدل البكالوريا أو مؤهل التقني المهني × 40%) + (معدل سنة التخرج × 60%)</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </section>
            @elseif($isCommis)
                <section class="mb-3">
                    <h2 class="text-sm md:text-base font-extrabold text-slate-900 bg-slate-200/80 px-2.5 py-1 rounded border-r-4 border-slate-900 mb-1.5">
                        II- المستوى الدراسي
                    </h2>
                    <table class="doc-table w-full text-right text-xs border border-slate-900">
                        <tbody>
                            <tr>
                                <th class="w-[22%] bg-slate-100 font-bold border border-slate-400 p-1.5 text-slate-900">المستوى الدراسي</th>
                                <td colspan="3" class="border border-slate-400 p-1.5 font-bold text-slate-950">{{ $data['school_level'] ?? '-' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </section>
                <section class="mb-3">
                    <h2 class="text-sm md:text-base font-extrabold text-slate-900 bg-slate-200/80 px-2.5 py-1 rounded border-r-4 border-slate-900 mb-1.5">
                        III- النتائج ومقياس الفرز الأولي
                    </h2>
                    <table class="doc-table w-full text-right text-xs border border-slate-900">
                        <tbody>
                            <tr>
                                <th class="w-[22%] bg-slate-100 font-bold border border-slate-400 p-1.5 text-slate-900">معدل السنة التاسعة أساسي</th>
                                <td colspan="3" class="border border-slate-400 p-1.5 font-mono font-bold text-slate-950">{{ $data['grade_9_average'] ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th class="bg-slate-100 font-bold border border-slate-400 p-1.5 text-slate-900">صيغة الفرز الأولي (مجموع النقاط)</th>
                                <td colspan="3" class="border border-slate-400 p-1.5 text-slate-900">
                                    معدل السنة التاسعة من التعليم الأساسي + 1 نقطة عن كل سنة دراسية تفوق المستوى الأدنى المطلوب (في حدود 4 سنوات كحد أقصى).
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </section>
            @elseif($isChauffeur)
                <section class="mb-3">
                    <h2 class="text-sm md:text-base font-extrabold text-slate-900 bg-slate-200/80 px-2.5 py-1 rounded border-r-4 border-slate-900 mb-1.5">
                        II- المستوى الدراسي وبيانات رخصة السياقة
                    </h2>
                    <table class="doc-table w-full text-right text-xs border border-slate-900">
                        <tbody>
                            <tr>
                                <th class="w-[22%] bg-slate-100 font-bold border border-slate-400 p-1.5 text-slate-900">المستوى الدراسي</th>
                                <td colspan="3" class="border border-slate-400 p-1.5 font-bold text-slate-950">{{ $data['school_level'] ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th class="bg-slate-100 font-bold border border-slate-400 p-1.5 text-slate-900">صنف رخصة السياقة</th>
                                <td class="border border-slate-400 p-1.5 font-bold text-slate-950">{{ $data['driving_license_category'] ?? 'صنف ب (B)' }}</td>
                                <th class="bg-slate-100 font-bold border border-slate-400 p-1.5 text-slate-900">تاريخ الحصول عليها</th>
                                <td class="border border-slate-400 p-1.5 font-mono text-slate-950">{{ $data['driving_license_date'] ?? '-' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </section>
                <section class="mb-3">
                    <h2 class="text-sm md:text-base font-extrabold text-slate-900 bg-slate-200/80 px-2.5 py-1 rounded border-r-4 border-slate-900 mb-1.5">
                        III- النتائج ومقياس الفرز الأولي
                    </h2>
                    <table class="doc-table w-full text-right text-xs border border-slate-900">
                        <tbody>
                            <tr>
                                <th class="w-[22%] bg-slate-100 font-bold border border-slate-400 p-1.5 text-slate-900">معدل السنة التاسعة أساسي</th>
                                <td colspan="3" class="border border-slate-400 p-1.5 font-mono font-bold text-slate-950">{{ $data['grade_9_average'] ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th class="bg-slate-100 font-bold border border-slate-400 p-1.5 text-slate-900">صيغة الفرز الأولي (مجموع النقاط)</th>
                                <td colspan="3" class="border border-slate-400 p-1.5 text-slate-900">
                                    معدل السنة التاسعة من التعليم الأساسي + 1 نقطة عن كل سنة دراسية تفوق المستوى الأدنى المطلوب.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </section>
            @elseif($isNettoyage)
                <section class="mb-3">
                    <h2 class="text-sm md:text-base font-extrabold text-slate-900 bg-slate-200/80 px-2.5 py-1 rounded border-r-4 border-slate-900 mb-1.5">
                        II- المستوى الدراسي
                    </h2>
                    <table class="doc-table w-full text-right text-xs border border-slate-900">
                        <tbody>
                            <tr>
                                <th class="w-[22%] bg-slate-100 font-bold border border-slate-400 p-1.5 text-slate-900">المستوى الدراسي</th>
                                <td colspan="3" class="border border-slate-400 p-1.5 font-bold text-slate-950">{{ $data['school_level'] ?? '-' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </section>
                <section class="mb-3">
                    <h2 class="text-sm md:text-base font-extrabold text-slate-900 bg-slate-200/80 px-2.5 py-1 rounded border-r-4 border-slate-900 mb-1.5">
                        III- النتائج ومقياس الفرز الأولي
                    </h2>
                    <table class="doc-table w-full text-right text-xs border border-slate-900">
                        <tbody>
                            <tr>
                                <th class="w-[22%] bg-slate-100 font-bold border border-slate-400 p-1.5 text-slate-900">معدل السنة السادسة أساسي</th>
                                <td colspan="3" class="border border-slate-400 p-1.5 font-mono font-bold text-slate-950">{{ $data['grade_6_average'] ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th class="bg-slate-100 font-bold border border-slate-400 p-1.5 text-slate-900">صيغة الفرز الأولي (مجموع النقاط)</th>
                                <td colspan="3" class="border border-slate-400 p-1.5 text-slate-900">
                                    معدل السنة السادسة أساسي + 1 نقطة عن كل سنة دراسية تفوق المستوى الأدنى المطلوب (في حدود 3 سنوات كحد أقصى).
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </section>
            @endif

            <!-- Footer -->
            <footer class="mt-4 pt-2 border-t-2 border-slate-900">
                <div class="flex flex-row items-stretch justify-between gap-4">
                    <div class="flex-1 border border-slate-400 rounded-lg p-2.5 bg-slate-50/50 text-[11px] text-slate-800">
                        <div class="font-bold text-slate-950 mb-1">⚖️ تصريح بالشرف:</div>
                        <p class="text-justify text-[10.5px]">
                            أصرح بشرفي بصحة ودقة كافة البيانات والمعلومات المصرح بها أعلاه في هذه الاستمارة، وأتحمل كامل المسؤولية في صورة الإدلاء ببيانات غير مطابقة.
                        </p>
                    </div>
                    <div class="w-[42%] min-w-[210px] border-2 border-slate-900 rounded-lg p-2.5 bg-white text-center flex flex-col justify-between">
                        <div>
                            <div class="font-bold text-slate-950 text-xs mt-1">إمضاء المترشح(ة)</div>
                        </div>
                    </div>
                </div>
            </footer>

        </div>
    </div>

    <footer class="no-print max-w-4xl mx-auto text-center text-xs text-sky-600 font-medium py-4 pb-8" dir="ltr">
        {!! $data['footer_text'] ?? \App\Models\Setting::getFooterText() !!}
    </footer>

</body>
</html>