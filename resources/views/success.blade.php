@php
    $data = session('data') ?? [];
@endphp
<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تم استلام طلبكم بنجاح</title>

    <!-- Tailwind CDN -->
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        body {
            font-family: 'Tajawal', sans-serif;
        }

        /* 🖨️ إعدادات الطباعة */
        @media print {

            /* إخفاء زر الطباعة والعودة */
            .no-print {
                display: none !important;
            }

            /* تصغير حجم النص قليلاً لتلاؤم المحتوى */
            body {
                font-size: 12px !important;
                background: white !important;
            }

            /* تقليل الهوامش */
            @page {
                margin: 10mm;
            }

            /* تقليل المسافات */
            h2,
            h3 {
                margin-top: 5px !important;
                margin-bottom: 5px !important;
            }

            table th,
            table td {
                padding: 4px 6px !important;
            }

            .goog {
                display: none;
            }
        }
    </style>

</head>

<body class="bg-gray-100">

    <div class="max-w-5xl mx-auto px-4 py-10">

        <!-- زر الطباعة -->
        <div class="text-center mb-6 no-print">
            <button onclick="window.print()"
                class="bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700 transition font-semibold">
                🖨️ طباعة
            </button>
        </div>

        <div class="text-center goog mb-10">
            <h2 class="text-3xl font-bold text-gray-800">تم إرسال طلبكم بنجاح</h2>
            <p class="text-gray-500 mt-2">طباعة استمارة الترشح وامضائها لتضمينها
                بالملف الورقي في صورة قبول المترشح في
                الفرز الأولي.</p>
        </div>

        <!-- Card -->
        <div class="bg-white shadow-md rounded-lg p-6 md:p-8">

            @php
                $posCode = (string)($data['position'] ?? '');
                $posNum = (int)$posCode;
                $posName = $data['position_name'] ?? '';

                if ($posNum >= 1 && $posNum <= 15) {
                    $catTitle = $posName ?: "المهندسين والمحللين\nوالمتصرفين";
                    $catRef = $posCode ?: "من 1 إلى 15";
                } elseif ($posCode === '16') {
                    $catTitle = $posName ?: "ملحق إدارة\nمؤهل التقني السامي";
                    $catRef = "16";
                } elseif (in_array($posCode, ['17', '18'])) {
                    $catTitle = $posName ?: "مستكتب إدارة";
                    $catRef = $posCode ?: "17 و 18";
                } elseif ($posCode === '19') {
                    $catTitle = $posName ?: "سائق";
                    $catRef = "19";
                } elseif (in_array($posCode, ['20', '21'])) {
                    $catTitle = $posName ?: "عون تنظيف";
                    $catRef = $posCode ?: "20 و 21";
                } else {
                    $catTitle = $posName ?: "خطة " . $posCode;
                    $catRef = $posCode;
                }
            @endphp

            <!-- Header matching official model template -->
            <div class="mb-6 pb-4 border-b border-gray-300">
                <!-- Top Row: Ministry info (Right), Title (Center), Category Box (Left) -->
                <div class="flex flex-col md:flex-row items-center justify-between gap-4 mb-4">
                    <!-- Right: Ministry & Center Info -->
                    <div class="text-right text-xs md:text-sm font-semibold leading-snug text-gray-900">
                        <div>الجمهورية التونسية</div>
                        <div>وزارة التشغيل والتكوين المهني</div>
                        <div class="font-bold">المركز الوطني للتكوين المستمر والترقية المهنية</div>
                    </div>

                    <!-- Center: Main Title -->
                    <div class="text-center flex-1 my-2">
                        <h1 class="text-xl md:text-2xl font-extrabold text-gray-900 leading-tight">
                            {{ $data['contest_name'] ?? 'استمارة ترشح للمشاركة في المناظرة الخارجية' }}
                        </h1>
                        @if(empty($data['contest_name']))
                            <h2 class="text-base md:text-lg font-bold text-gray-800 mt-1">
                                بعنوان سنتي 2025 و2026
                            </h2>
                        @endif
                    </div>

                    <!-- Left: Position Category Box -->
                    <div>
                        <div class="border-2 border-gray-900 p-2 md:p-3 text-center text-gray-900 min-w-[130px] bg-white">
                            <div class="text-xs md:text-sm font-semibold text-gray-800 leading-tight whitespace-pre-line">
                                {!! nl2br(e($catTitle)) !!}
                            </div>
                            <div class="text-lg md:text-xl font-black text-gray-900 mt-1 leading-tight tracking-wide">
                                {{ $catRef }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Second Row: Registration Number (Right) and Score (Left, if enabled) -->
                <div class="flex items-center justify-between mt-4">
                    <div class="border-2 border-gray-900 px-4 py-1.5 font-bold text-gray-900 text-sm md:text-base bg-white">
                        رقم التسجيل: <span class="font-mono">{{ $data['id'] ?? '' }}</span>
                    </div>
                    @if(($data['show_score'] ?? true) !== false)
                        <div class="border-2 border-gray-900 px-4 py-1.5 font-bold text-gray-900 text-sm md:text-base bg-white">
                            مجموع النقاط: <span class="font-mono">{{ $data['score'] ?? '' }}</span>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Personal Info -->
            <h3 class="text-xl font-semibold text-gray-700 mb-4">I- الهوية والمعلومات الشخصية</h3>

            <div class="overflow-x-auto">
                <table class="w-full text-right border border-gray-200 rounded-lg bg-white">
                    <tbody class="divide-y divide-gray-200">

                        @php
                            $rows = [
                                'رمز المناظرة المزمع المشاركة فيها' => $data['position'] ?? '',
                                'اسم ولقب' => $data['name'] ?? '',
                                'الجنس' => $data['gender'] ?? '',
                                'تاريخ الولادة' => $data['birth_date'] ?? '',
                                'العنوان الحالي' => $data['address'] ?? '',
                                'الولاية' => $data['governorate'] ?? '',
                                'الرقم البريدي' => $data['postal_code'] ?? '',
                                'رقم بطاقة التعريف' => $data['cin'] ?? '',
                                'تاريخ إصدار بطاقة التعريف' => $data['cin_date'] ?? '',
                                'الهاتف' => $data['tel'] ?? '',
                                'البريد الإلكتروني' => $data['email'] ?? '',
                            ];
                        @endphp

                        @foreach($rows as $label => $value)
                            <tr class="hover:bg-gray-50">
                                <th class="py-3 px-4 font-medium text-gray-700 w-1/3 bg-gray-100">{{ $label }}</th>
                                <td class="py-3 px-4 text-gray-800">
                                    @if($label === 'رمز المناظرة المزمع المشاركة فيها')
                                        <span>
                                            <strong class="font-black text-base md:text-lg text-gray-900">{{ $data['position'] ?? '' }}</strong>
                                            @if(!empty($data['position_name']))
                                                <span class="font-medium text-gray-700"> - {{ $data['position_name'] }}</span>
                                            @endif
                                        </span>
                                    @else
                                        {{ $value }}
                                    @endif
                                </td>
                            </tr>
                        @endforeach


                    </tbody>
                </table>
            </div>

            <!-- Education -->
            <h3 class="text-xl font-semibold text-gray-700 mt-10 mb-4">المستوى التعليمي</h3>

            <div class="overflow-x-auto">
                <table class="w-full text-right border border-gray-200 rounded-lg bg-white">
                    <tbody class="divide-y divide-gray-200">

                        @if(isset($data['degree']))
                            <tr>
                                <th class="py-3 px-4 bg-gray-100">الشهادة</th>
                                <td class="py-3 px-4">{{ $data['degree'] }}</td>
                            </tr>
                        @endif

                        <tr>
                            <th class="py-3 px-4 bg-gray-100">الاختصاص</th>
                            <td class="py-3 px-4">{{ $data['specialty'] }}</td>
                        </tr>
                        <tr>
                            <th class="py-3 px-4 bg-gray-100">سنة التخرج</th>
                            <td class="py-3 px-4">{{ $data['graduation_year'] }}</td>
                        </tr>
                        <tr>
                            <th class="py-3 px-4 bg-gray-100">قرار المعادلة</th>
                            <td class="py-3 px-4">{{ $data['equivalence_decision'] }}</td>
                        </tr>
                        <tr>
                            <th class="py-3 px-4 bg-gray-100">تاريخ قرار المعادلة</th>
                            <td class="py-3 px-4">{{ $data['equivalence_date'] }}</td>
                        </tr>

                    </tbody>
                </table>
            </div>

            <!-- Bac -->
            <h3 class="text-xl font-semibold text-gray-700 mt-10 mb-4">نتائج</h3>

            <div class="overflow-x-auto">
                <table class="w-full text-right border border-gray-200 rounded-lg bg-white">
                    <tbody class="divide-y divide-gray-200">

                        <tr>
                            <th class="py-3 px-4 bg-gray-100">معدل الباكالوريا</th>
                            <td class="py-3 px-4">{{ $data['bac_average'] }}</td>
                        </tr>
                        <tr>
                            <th class="py-3 px-4 bg-gray-100">معدل سنة التخرج</th>
                            <td class="py-3 px-4">{{ $data['grad_average'] }}</td>
                        </tr>

                    </tbody>
                </table>
            </div>
            <div class="mt-12 flex justify-start">
                <div class="w-1/2 border-2 border-dashed border-gray-400 p-6 text-center">
                    <p class="text-gray-700 font-semibold mb-12">الإمضاء</p>
                </div>
            </div>

        </div>

    </div>

</body>

</html>