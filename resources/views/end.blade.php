{{-- resources/views/application-closed.blade.php --}}
<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <title>أُغلق باب الترشّح</title>
    <link rel="shortcut icon" type="image/png" href="cnfcpp.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

<body>
    <!-- Contest Header -->
    <div class="contest-header">

        <img src="cnfcpp.png" width="100" style="margin: 0 auto" />
        <h1>
            {{ $lastContest ? $lastContest->name : 'استمارة ترشح للمشاركة في المناظرة الخارجية لانتداب إطارات' }}
        </h1>
        <p>
            الرجاء تعمير البيانات المطلوبة بكلّ دقة باللغة العربية ثم المصادقة عليها.
            ولا يمكن تعديلها بعد المصادقة.
            وطباعتها وإرفاقها بملف الترشح.
        </p>
    </div>

    <!-- Deadline Countdown Banner (Expired) -->
    <div class="deadline-banner expired">

        <div class="deadline-left">
            <div class="deadline-icon">
                <!-- AlertTriangle icon -->
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                    xmlns="http://www.w3.org/2000/svg">
                    <path d="M12 9V13" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                    <circle cx="12" cy="17" r="1" fill="currentColor" />
                    <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0Z"
                        stroke="currentColor" stroke-width="2" />
                </svg>
            </div>

            <div class="deadline-text">
                @if(isset($lastContest) && $lastContest)
                    <p class="deadline-label ">انتهت فترة التسجيل</p>
                    <p class="deadline-date">
                        {{ \Illuminate\Support\Carbon::parse($lastContest->ends_at)->locale('ar')->translatedFormat('l d F Y على الساعة H:i') }}
                    </p>
                @else
                    <p class="deadline-label ">لا توجد مناظرة مفتوحة حالياً</p>
                    <p class="deadline-date">
                        سيتم الإعلان عن المناظرات القادمة قريباً.
                    </p>
                @endif
            </div>
        </div>

        <!-- Countdown intentionally NOT rendered (expired state) -->

    </div>

    <style>
        /* =========================
   Contest Header
   ========================= */
        .contest-header {
            background: linear-gradient(to left, #1e3a8a, #2563eb);
            padding: 32px;
            color: #ffffff;
            text-align: center;
            font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }

        .contest-header h1 {
            font-size: 30px;
            font-weight: 800;
            margin: 0 0 8px 0;
        }

        .contest-header p {
            font-size: 16px;
            opacity: 0.9;
            margin: 0;
            line-height: 1.7;
        }

        /* =========================
   Deadline Banner
   ========================= */
        .deadline-banner {
            padding: 16px;
            border-bottom: 1px solid;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }

        /* Expired state */
        .deadline-banner.expired {
            background-color: #fef2f2;
            border-color: #fecaca;
        }

        /* Left side */
        .deadline-left {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        /* Icon */
        .deadline-icon {
            padding: 12px;
            border-radius: 9999px;
            background-color: #fee2e2;
            color: #dc2626;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Text */
        .deadline-text {
            display: flex;
            flex-direction: column;
        }

        .deadline-label {
            font-size: 18px;
            font-weight: 700;
            color: #7f1d1d;
            margin: 0;
        }

        .deadline-date {
            font-size: 18px;
            font-weight: 700;
            color: #450a0a;
            margin: 2px 0 0 0;
        }

        /* =========================
   Responsive (md:flex-row)
   ========================= */
        @media (max-width: 768px) {
            .deadline-banner {
                flex-direction: column;
                align-items: flex-start;
            }

            .contest-header h1 {
                font-size: 24px;
            }

            .deadline-date {
                font-size: 16px;
            }
        }

        /* 1. Use a more-intuitive box-sizing model */
        *,
        *::before,
        *::after {
            box-sizing: border-box;
        }

        /* 2. Remove default margin */
        *:not(dialog) {
            margin: 0;
        }

        /* 3. Enable keyword animations */
        @media (prefers-reduced-motion: no-preference) {
            html {
                interpolate-size: allow-keywords;
            }
        }

        body {
            /* 4. Add accessible line-height */
            line-height: 1.5;
            /* 5. Improve text rendering */
            -webkit-font-smoothing: antialiased;
        }

        /* 6. Improve media defaults */
        img,
        picture,
        video,
        canvas,
        svg {
            display: block;
            max-width: 100%;
        }

        /* 7. Inherit fonts for form controls */
        input,
        button,
        textarea,
        select {
            font: inherit;
        }

        /* 8. Avoid text overflows */
        p,
        h1,
        h2,
        h3,
        h4,
        h5,
        h6 {
            overflow-wrap: break-word;
        }

        /* 9. Improve line wrapping */
        p {
            text-wrap: pretty;
        }

        h1,
        h2,
        h3,
        h4,
        h5,
        h6 {
            text-wrap: balance;
        }

        /*
  10. Create a root stacking context
*/
        #root,
        #__next {
            isolation: isolate;
        }

        .blink {
            animation: blinker 1s step-start infinite;
        }

        @keyframes blinker {
            50% {
                opacity: 0;
            }
        }
    </style>


</body>

</html>
