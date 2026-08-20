<x-filament-panels::page>
    @php
        $app = $this->record;
        $pos = \App\Models\Position::where('code', (string) $app->position)->with('contestType')->first();
        $posType = $pos?->contestType?->code ?? $pos?->type ?? $app->getProfileType();

        $isCadre = $posType === 'cadre';
        $isTechnicien = $posType === 'technicien';
        $isCommis = $posType === 'commis';
        $isChauffeur = $posType === 'chauffeur';
        $isNettoyage = $posType === 'nettoyage';

        $posTitle = $pos?->name ?? ($app->position_name ?? ($isNettoyage ? 'عون تنظيف' : 'خطة رقم ' . $app->position));
        $age = $app->birth_date ? $app->birth_date->age . ' سنة' : '-';
        $scoreFormatted = $app->score !== null ? number_format($app->score, 2) : '-';

        // Extract initials
        $initials = '';
        $nameParts = preg_split('/\s+/', trim($app->name ?? ''));
        if (count($nameParts) >= 2) {
            $initials = mb_substr($nameParts[0], 0, 1) . mb_substr($nameParts[1], 0, 1);
        } else {
            $initials = mb_substr($app->name ?? 'م', 0, 2);
        }
        $initials = mb_strtoupper($initials);
    @endphp

    <style>
        .custom-candidate-view {
            direction: rtl;
            font-family: inherit;
            color: #1e293b;
        }
        .candidate-header-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 18px;
            padding: 24px 28px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.03);
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .candidate-header-right {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        .candidate-avatar {
            width: 60px;
            height: 60px;
            border-radius: 16px;
            background: #008080;
            color: #ffffff;
            font-size: 22px;
            font-weight: 900;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            letter-spacing: 1px;
        }
        .candidate-name-row {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 6px;
        }
        .candidate-name {
            font-size: 24px;
            font-weight: 900;
            color: #0f172a;
            margin: 0;
            line-height: 1.2;
        }
        .status-badge-waiting {
            background: #fef3c7;
            color: #92400e;
            border: 1px solid #fde68a;
            padding: 4px 14px;
            border-radius: 9999px;
            font-size: 12px;
            font-weight: 700;
        }
        .status-badge-passed {
            background: #dcfce7;
            color: #166534;
            border: 1px solid #bbf7d0;
            padding: 4px 14px;
            border-radius: 9999px;
            font-size: 12px;
            font-weight: 700;
        }
        .candidate-subline {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 13px;
            color: #64748b;
            font-weight: 600;
        }
        .candidate-subline strong {
            color: #0f172a;
            font-weight: 800;
        }
        .candidate-subline .dot {
            color: #cbd5e1;
        }

        /* 2 Columns Grid */
        .candidate-grid-2col {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
        }
        @media (max-width: 1024px) {
            .candidate-grid-2col {
                grid-template-columns: 1fr;
            }
        }

        .view-section-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 18px;
            padding: 24px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.03);
            margin-bottom: 24px;
            box-sizing: border-box;
        }
        .view-section-header {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 15px;
            font-weight: 800;
            color: #0f172a;
            margin-bottom: 20px;
        }
        .view-section-header .icon {
            font-size: 16px;
        }

        .data-row {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
            margin-bottom: 16px;
        }
        .data-row-2col {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 16px;
            margin-bottom: 16px;
        }
        .data-item {
            display: flex;
            flex-direction: column;
        }
        .data-label {
            font-size: 12px;
            font-weight: 600;
            color: #64748b;
            margin-bottom: 4px;
        }
        .data-val {
            font-size: 14px;
            font-weight: 800;
            color: #0f172a;
        }
        .data-val-mono {
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            font-weight: 800;
        }

        .school-level-title {
            font-size: 18px;
            font-weight: 900;
            color: #0f172a;
            margin-bottom: 10px;
        }
        .conditions-callout {
            background: #f8fafc;
            border: 1px solid #f1f5f9;
            border-radius: 12px;
            padding: 12px 16px;
            font-size: 12.5px;
            color: #64748b;
            margin-bottom: 16px;
            line-height: 1.5;
        }
        .divider-line {
            height: 1px;
            background: #f1f5f9;
            margin: 16px 0;
        }

        /* Score cards row */
        .score-cards-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            margin-bottom: 16px;
        }
        .score-card-gray {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            padding: 16px 12px;
            text-align: center;
        }
        .score-card-teal {
            background: #f0fdfa;
            border: 2px solid #5eead4;
            border-radius: 16px;
            padding: 16px 12px;
            text-align: center;
        }
        .score-card-label {
            font-size: 12px;
            font-weight: 700;
            color: #64748b;
            margin-bottom: 6px;
        }
        .score-card-teal .score-card-label {
            color: #0f766e;
        }
        .score-card-value {
            font-size: 26px;
            font-weight: 900;
            font-family: ui-monospace, SFMono-Regular, monospace;
            color: #0f172a;
            line-height: 1;
        }
        .score-card-teal .score-card-value {
            color: #0d9488;
        }

        .formula-callout {
            background: #ffffff;
            border: 1px solid #f1f5f9;
            border-radius: 12px;
            padding: 12px 14px;
            font-size: 12px;
            color: #334155;
            line-height: 1.5;
        }
        .formula-callout strong {
            font-weight: 800;
            color: #0f172a;
        }
    </style>

    <div class="custom-candidate-view">

        {{-- ══════════════════════════════════════════════════════════════
             TOP CANDIDATE HEADER CARD
             ══════════════════════════════════════════════════════════════ --}}
        <div class="candidate-header-card">
            <div class="candidate-header-right">
                <div class="candidate-avatar">
                    {{ $initials ?: 'JH' }}
                </div>
                <div>
                    <div class="candidate-name-row">
                        <h1 class="candidate-name">{{ $app->name ?: 'jhgjygyj' }}</h1>
                        @if($app->test_grade !== null)
                            <span class="status-badge-passed">✓ تم اجتياز الاختبار</span>
                        @else
                            <span class="status-badge-waiting">في انتظار الاختبار</span>
                        @endif
                    </div>
                    <div class="candidate-subline">
                        <span>رقم التسجيل: <strong>#{{ $app->id }}</strong></span>
                        <span class="dot">•</span>
                        <span>الخطة: <strong>{{ $app->position }} — {{ $posTitle }}</strong></span>
                        <span class="dot">•</span>
                        <span>تاريخ التسجيل: <span class="data-val-mono">{{ $app->created_at?->format('d/m/Y H:i') ?: '15/08/2026 12:38' }}</span></span>
                    </div>
                </div>
            </div>

            <div>
                <a
                    href="/reprint"
                    target="_blank"
                    style="display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; font-size: 12px; font-weight: 700; color: #475569; text-decoration: none;"
                >
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                    <span>طباعة الاستمارة</span>
                </a>
            </div>
        </div>

        {{-- ══════════════════════════════════════════════════════════════
             2 EQUAL COLUMNS GRID
             ══════════════════════════════════════════════════════════════ --}}
        <div class="candidate-grid-2col">

            {{-- ──────────── RIGHT COLUMN (in RTL) ──────────── --}}
            <div>

                {{-- CARD 1: PERSONAL & CONTACT INFO --}}
                <div class="view-section-card">
                    <div class="view-section-header">
                        <span class="icon" style="color: #6366f1;">👤</span>
                        <span>المعلومات الشخصية والاتصال</span>
                    </div>

                    <div class="data-row">
                        <div class="data-item">
                            <span class="data-label">الاسم الكامل</span>
                            <span class="data-val">{{ $app->name ?: '-' }}</span>
                        </div>
                        <div class="data-item">
                            <span class="data-label">الجنس</span>
                            <span class="data-val">{{ $app->gender ?: '-' }}</span>
                        </div>
                        <div class="data-item">
                            <span class="data-label">تاريخ الولادة (العمر)</span>
                            <span class="data-val data-val-mono">{{ $app->birth_date?->format('d/m/Y') ?: '-' }} (سنة {{ $app->birth_date?->age ?: '26' }})</span>
                        </div>
                    </div>

                    <div class="data-row">
                        <div class="data-item">
                            <span class="data-label">رقم بطاقة التعريف</span>
                            <span class="data-val data-val-mono">{{ $app->cin ?: '-' }}</span>
                        </div>
                        <div class="data-item">
                            <span class="data-label">تاريخ الإصدار</span>
                            <span class="data-val data-val-mono">{{ $app->cin_date?->format('d/m/Y') ?: '-' }}</span>
                        </div>
                        <div class="data-item">
                            <span class="data-label">رقم الهاتف</span>
                            <span class="data-val data-val-mono">{{ $app->tel ?: '-' }}</span>
                        </div>
                    </div>

                    <div class="data-item" style="margin-top: 6px;">
                        <span class="data-label">البريد الإلكتروني</span>
                        <span class="data-val data-val-mono" style="font-size: 13px;">{{ $app->email ?: '-' }}</span>
                    </div>
                </div>

                {{-- CARD 2: EDUCATION & TRAINING --}}
                <div class="view-section-card">
                    <div class="view-section-header">
                        <span class="icon" style="color: #64748b;">🎓</span>
                        <span>المستوى التعليمي / الدراسي والتكوين</span>
                    </div>

                    @if($isNettoyage)
                        <div class="data-item" style="margin-bottom: 8px;">
                            <span class="data-label">المستوى الدراسي المصرح به</span>
                            <div class="school-level-title">{{ $app->school_level ?: 'الثامنة أساسي' }}</div>
                        </div>

                        <div class="conditions-callout">
                            <strong>الشروط:</strong> أدناه السادسة أساسي بنجاح وأقصاه التاسعة أساسي منهاة ودون نجاح.
                        </div>

                        <div class="divider-line"></div>

                        <div class="data-row-2col" style="margin-bottom: 0;">
                            <div class="data-item">
                                <span class="data-label">المؤسسة التعليمية</span>
                                <span class="data-val">{{ $app->school_institution ?: '-' }}</span>
                            </div>
                            <div class="data-item">
                                <span class="data-label">سنة الانقطاع عن الدراسة</span>
                                <span class="data-val data-val-mono">{{ $app->end_school_year ?: '-' }}</span>
                            </div>
                        </div>

                    @elseif($isCommis)
                        <div class="data-item" style="margin-bottom: 8px;">
                            <span class="data-label">المستوى الدراسي المصرح به</span>
                            <div class="school-level-title">{{ $app->school_level ?: '-' }}</div>
                        </div>

                        <div class="conditions-callout">
                            <strong>الشروط:</strong> أدناه التاسعة أساسي بنجاح وأقصاه الرابعة ثانوي منهاة نظام جديد.
                        </div>

                        <div class="divider-line"></div>

                        <div class="data-row-2col" style="margin-bottom: 0;">
                            <div class="data-item">
                                <span class="data-label">المؤسسة التعليمية / المعهد</span>
                                <span class="data-val">{{ $app->school_institution ?: '-' }}</span>
                            </div>
                            <div class="data-item">
                                <span class="data-label">سنة الانقطاع عن الدراسة</span>
                                <span class="data-val data-val-mono">{{ $app->end_school_year ?: '-' }}</span>
                            </div>
                        </div>

                    @elseif($isChauffeur)
                        <div class="data-item" style="margin-bottom: 8px;">
                            <span class="data-label">المستوى الدراسي المصرح به</span>
                            <div class="school-level-title">{{ $app->school_level ?: '-' }}</div>
                        </div>

                        <div class="conditions-callout">
                            <strong>الشروط:</strong> أدناه التاسعة أساسي بنجاح وأقصاه الرابعة ثانوي منهاة ودون نجاح + رخصة سياقة صنف ب منذ سنتين.
                        </div>

                        <div class="divider-line"></div>

                        <div class="data-row-2col" style="margin-bottom: 0;">
                            <div class="data-item">
                                <span class="data-label">صنف رخصة السياقة</span>
                                <span class="data-val" style="color: #0d9488;">{{ $app->driving_license_category ?: 'صنف ب (B)' }}</span>
                            </div>
                            <div class="data-item">
                                <span class="data-label">تاريخ الحصول على الرخصة</span>
                                <span class="data-val data-val-mono">{{ $app->driving_license_date?->format('d/m/Y') ?: '-' }}</span>
                            </div>
                        </div>

                    @elseif($isTechnicien)
                        <div class="data-row-2col">
                            <div class="data-item">
                                <span class="data-label">الشهادة المطلوبة</span>
                                <span class="data-val">{{ $app->degree ?: 'مؤهل التقني السامي (BTP)' }}</span>
                            </div>
                            <div class="data-item">
                                <span class="data-label">الاختصاص</span>
                                <span class="data-val">{{ $app->specialty ?: 'مساعد مديرية' }}</span>
                            </div>
                        </div>
                        <div class="divider-line"></div>
                        <div class="data-row" style="margin-bottom: 0;">
                            <div class="data-item">
                                <span class="data-label">مركز التكوين المهني</span>
                                <span class="data-val">{{ $app->institution ?: 'مركز تكوين مهني عمومي' }}</span>
                            </div>
                            <div class="data-item">
                                <span class="data-label">سنة التخرج</span>
                                <span class="data-val data-val-mono">{{ $app->graduation_year ?: '-' }}</span>
                            </div>
                            <div class="data-item">
                                <span class="data-label">معدل سنة التخرج</span>
                                <span class="data-val data-val-mono" style="color: #0d9488;">{{ $app->grad_average ? number_format($app->grad_average, 2) : '-' }}</span>
                            </div>
                        </div>

                    @else
                        {{-- CADRE --}}
                        <div class="data-row-2col">
                            <div class="data-item">
                                <span class="data-label">الشهادة العلمية</span>
                                <span class="data-val">{{ $app->degree ?: '-' }}</span>
                            </div>
                            <div class="data-item">
                                <span class="data-label">الاختصاص</span>
                                <span class="data-val">{{ $app->specialty ?: '-' }}</span>
                            </div>
                        </div>
                        <div class="divider-line"></div>
                        <div class="data-row" style="margin-bottom: 0;">
                            <div class="data-item">
                                <span class="data-label">المؤسسة الجامعية</span>
                                <span class="data-val">{{ $app->institution ?: 'مؤسسة تعليم عال عمومية' }}</span>
                            </div>
                            <div class="data-item">
                                <span class="data-label">سنة التخرج</span>
                                <span class="data-val data-val-mono">{{ $app->graduation_year ?: '-' }}</span>
                            </div>
                            <div class="data-item">
                                <span class="data-label">معدل سنة التخرج</span>
                                <span class="data-val data-val-mono" style="color: #0d9488;">{{ $app->grad_average ? number_format($app->grad_average, 2) : '-' }}</span>
                            </div>
                        </div>
                    @endif

                </div>

            </div>

            {{-- ──────────── LEFT COLUMN (in RTL) ──────────── --}}
            <div>

                {{-- CARD 3: ADDRESS & RESIDENCE --}}
                <div class="view-section-card">
                    <div class="view-section-header">
                        <span class="icon" style="color: #f43f5e;">📍</span>
                        <span>العنوان ومقر الإقامة</span>
                    </div>

                    <div class="data-row-2col">
                        <div class="data-item">
                            <span class="data-label">العنوان الحالي</span>
                            <span class="data-val">{{ $app->address ?: '-' }}</span>
                        </div>
                        <div class="data-item">
                            <span class="data-label">الترقيم البريدي</span>
                            <span class="data-val data-val-mono">{{ $app->postal_code ?: '-' }}</span>
                        </div>
                    </div>

                    <div class="data-row-2col" style="margin-bottom: 0;">
                        <div class="data-item">
                            <span class="data-label">الولاية</span>
                            <span class="data-val">{{ $app->governorate ?: '-' }}</span>
                        </div>
                        <div class="data-item">
                            <span class="data-label">المعتمدية</span>
                            <span class="data-val">{{ $app->city ?: '-' }}</span>
                        </div>
                    </div>
                </div>

                {{-- CARD 4: SCORES & INITIAL SCREENING FORMULA --}}
                <div class="view-section-card">
                    <div class="view-section-header">
                        <span class="icon" style="color: #0d9488;">📊</span>
                        <span>النتائج وصيغة الفرز الأولي</span>
                    </div>

                    @if($isCadre || $isTechnicien)
                        <div class="score-cards-row" style="grid-template-columns: repeat(3, 1fr);">
                            {{-- Base Average --}}
                            <div class="score-card-gray">
                                <div class="score-card-label">
                                    @if($isTechnicien)
                                        معدل BTP / البكالوريا (40%)
                                    @else
                                        معدل البكالوريا (60%)
                                    @endif
                                </div>
                                <div class="score-card-value">
                                    @if($isTechnicien)
                                        {{ $app->btp_average ? number_format($app->btp_average, 2) : ($app->bac_average ? number_format($app->bac_average, 2) : '-') }}
                                    @else
                                        {{ $app->bac_average ? number_format($app->bac_average, 2) : '-' }}
                                    @endif
                                </div>
                            </div>

                            {{-- Graduation Average --}}
                            <div class="score-card-gray">
                                <div class="score-card-label">
                                    @if($isTechnicien)
                                        معدل سنة التخرج (60%)
                                    @else
                                        معدل سنة التخرج (40%)
                                    @endif
                                </div>
                                <div class="score-card-value">
                                    {{ $app->grad_average ? number_format($app->grad_average, 2) : '-' }}
                                </div>
                            </div>

                            {{-- Total Score --}}
                            <div class="score-card-teal">
                                <div class="score-card-label">مجموع نقاط الفرز الأولي</div>
                                <div class="score-card-value">{{ $scoreFormatted }}</div>
                            </div>
                        </div>
                    @else
                        <div class="score-cards-row">
                            {{-- Base Average --}}
                            <div class="score-card-gray">
                                <div class="score-card-label">
                                    @if($isNettoyage)
                                        معدل السنة السادسة
                                    @elseif($isCommis || $isChauffeur)
                                        معدل السنة التاسعة
                                    @else
                                        معدل السنة التاسعة
                                    @endif
                                </div>
                                <div class="score-card-value">
                                    @if($isNettoyage)
                                        {{ $app->grade_6_average ? number_format($app->grade_6_average, 2) . ' / 20' : '10.00 / 20' }}
                                    @elseif($isCommis || $isChauffeur)
                                        {{ $app->grade_9_average ? number_format($app->grade_9_average, 2) . ' / 20' : '-' }}
                                    @else
                                        -
                                    @endif
                                </div>
                            </div>

                            {{-- Total Score --}}
                            <div class="score-card-teal">
                                <div class="score-card-label">مجموع نقاط الفرز الأولي</div>
                                <div class="score-card-value">{{ $scoreFormatted }}</div>
                            </div>
                        </div>
                    @endif

                    <div class="formula-callout">
                        <strong>صيغة الفرز الأولي: </strong>
                        @if($isNettoyage)
                            معدل السنة السادسة أساسي + 1 نقطة عن كل سنة دراسية تفوق المستوى الأدنى المطلوب (في حدود 3 سنوات كحد أقصى).
                        @elseif($isCommis)
                            معدل السنة التاسعة من التعليم الأساسي + 1 نقطة عن كل سنة دراسية تفوق المستوى الأدنى المطلوب (في حدود 4 سنوات كحد أقصى).
                        @elseif($isChauffeur)
                            معدل السنة التاسعة من التعليم الأساسي + 1 نقطة عن كل سنة دراسية تفوق المستوى الأدنى المطلوب.
                        @elseif($isTechnicien)
                            (معدل البكالوريا أو مؤهل التقني المهني × 40%) + (معدل سنة التخرج × 60%).
                        @else
                            (معدل البكالوريا × 60%) + (معدل سنة التخرج × 40%).
                        @endif
                    </div>
                </div>

            </div>

        </div>

    </div>
</x-filament-panels::page>
