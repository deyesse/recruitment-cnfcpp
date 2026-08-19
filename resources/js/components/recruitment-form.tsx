import React, { useState, useCallback, useEffect } from 'react';
import { CandidateData, INITIAL_DATA, TUNISIAN_GOVERNORATES, TUNISIAN_DELEGATIONS, BAC_SPECIALTIES, SCHOOL_LEVEL_OPTIONS } from './types';
import { InputGroup, SelectGroup } from './InputGroup';
import { SectionHeader } from './SectionHeader';
import { Send, Clock, AlertTriangle } from 'lucide-react';
import { Form, usePage } from '@inertiajs/react';

export const RecruitmentForm: React.FC = (deadlineDate, positions) => {
    const [data, setData] = useState<CandidateData>(INITIAL_DATA);
    const { errors } = usePage().props;

    // Countdown State
    positions = deadlineDate.positions;
    let degrees = deadlineDate.degrees;
    const [deadline] = useState<Date>(() => {
        return new Date(deadlineDate.deadlineDate);
    });

    const [agreed, setAgreed] = useState(false);
    const [timeLeft, setTimeLeft] = useState({
        days: 0,
        hours: 0,
        minutes: 0,
        seconds: 0,
        isExpired: false
    });


    useEffect(() => {
        const timer = setInterval(() => {
            const now = new Date().getTime();
            const distance = deadline.getTime() - now;

            if (distance < 0) {
                clearInterval(timer);
                setTimeLeft(prev => ({ ...prev, isExpired: true }));
            } else {
                setTimeLeft({
                    days: Math.floor(distance / (1000 * 60 * 60 * 24)),
                    hours: Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60)),
                    minutes: Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60)),
                    seconds: Math.floor((distance % (1000 * 60)) / 1000),
                    isExpired: false
                });
            }
        }, 1000);

        return () => clearInterval(timer);
    }, [deadline]);


    const handleChange = useCallback((e: React.ChangeEvent<HTMLInputElement | HTMLSelectElement>) => {
        const { name, value } = e.target;
        setData((prev) => ({ ...prev, [name]: value }));
    }, []);

    const handleGovernorateChange = useCallback((e: React.ChangeEvent<HTMLSelectElement>) => {
        const gov = e.target.value;
        setData((prev) => {
            const available = TUNISIAN_DELEGATIONS[gov] || [];
            const newCity = available.includes(prev.city) ? prev.city : '';
            return { ...prev, governorate: gov, city: newCity };
        });
    }, []);

    const selectedPos = Array.isArray(positions)
        ? positions.find((p: any) => (p.code || p) == data.position)
        : null;

    const profileType = selectedPos?.type || (
        ['17', '18'].includes(String(data.position)) ? 'commis' :
        String(data.position) === '19' ? 'chauffeur' :
        ['20', '21'].includes(String(data.position)) ? 'nettoyage' :
        String(data.position) === '16' ? 'technicien' : 'cadre'
    );

    let schoolLevelOptions: { value: string; label: string }[] = [];
    if (selectedPos) {
        if (Array.isArray(selectedPos.school_levels) && selectedPos.school_levels.length > 0) {
            schoolLevelOptions = selectedPos.school_levels.map((item: any) => ({
                value: item.label || item,
                label: item.label || item,
            }));
        } else {
            if (profileType === 'commis') {
                schoolLevelOptions = SCHOOL_LEVEL_OPTIONS.commis || [];
            } else if (profileType === 'chauffeur') {
                schoolLevelOptions = SCHOOL_LEVEL_OPTIONS.chauffeur || [];
            } else if (profileType === 'nettoyage') {
                schoolLevelOptions = SCHOOL_LEVEL_OPTIONS.nettoyage || [];
            }
        }
    }

    let ageError: string | null = null;
    if (selectedPos && data.birth_date && (selectedPos.min_age || selectedPos.max_age)) {
        const parts = data.birth_date.split('-');
        if (parts.length === 3 && parts[0].length === 4) {
            const birthYear = parseInt(parts[0], 10);
            const refYear = selectedPos.age_reference_date
                ? new Date(selectedPos.age_reference_date).getFullYear()
                : 2026;
            const candidateAge = refYear - birthYear;

            const refFormatted = selectedPos.age_reference_date
                ? new Date(selectedPos.age_reference_date).toLocaleDateString('fr-FR')
                : '01/01/2026';

            if (selectedPos.min_age && candidateAge < Number(selectedPos.min_age)) {
                ageError = `تنبيه هام: سن المترشح (${candidateAge} سنة) أقل من السن الأدنى المسموح به لهذا الصنف (${selectedPos.min_age} سنة بتاريخ ${refFormatted}). يُرفض الملف تلقائياً.`;
            } else if (selectedPos.max_age && candidateAge > Number(selectedPos.max_age)) {
                ageError = `تنبيه هام: سن المترشح (${candidateAge} سنة) يتجاوز السن الأقصى المسموح به لهذا الصنف (${selectedPos.max_age} سنة بتاريخ ${refFormatted}). يُرفض الملف تلقائياً.`;
            }
        }
    }

    let licenseError: string | null = null;
    if (selectedPos && (profileType === 'chauffeur' || selectedPos.has_driving_license) && data.driving_license_date) {
        const parts = data.driving_license_date.split('-');
        if (parts.length === 3 && parts[0].length === 4) {
            const minYears = selectedPos.driving_license_min_years !== undefined ? Number(selectedPos.driving_license_min_years) : 2;
            const licenseDate = new Date(data.driving_license_date);
            const refDate = selectedPos.age_reference_date
                ? new Date(selectedPos.age_reference_date)
                : new Date('2026-01-01');

            const yearsAfter = new Date(licenseDate);
            yearsAfter.setFullYear(yearsAfter.getFullYear() + minYears);

            if (yearsAfter > refDate) {
                const refFormatted = selectedPos.age_reference_date
                    ? new Date(selectedPos.age_reference_date).toLocaleDateString('fr-FR')
                    : '01/01/2026';
                licenseError = `تنبيه هام: يجب أن يكون المترشح متحصل على رخصة السياقة منذ ${minYears} سنوات على الأقل بتاريخ المرجع (${refFormatted}). يُرفض الملف تلقائياً.`;
            }
        }
    }

    // Real-time score calculation
    let calculatedScore: number | null = null;
    let scoreError: string | null = null;
    const minScore = 12.0;

    if (data.position && selectedPos) {
        if (profileType === 'cadre' || profileType === 'technicien') {
            const bac = parseFloat(data.bac_average);
            const grad = parseFloat(data.grad_average);
            if (!isNaN(bac) && !isNaN(grad)) {
                const coeffBac = profileType === 'technicien' ? 0.4 : 0.6;
                const coeffGrad = profileType === 'technicien' ? 0.6 : 0.4;
                calculatedScore = Math.round((bac * coeffBac + grad * coeffGrad) * 1000) / 1000;
                if (calculatedScore < minScore) {
                    scoreError = `مجموع النقاط المحسوب (${calculatedScore.toFixed(2)}) أقل من الحد الأدنى المطلوب لقبول الترشح (12.00).`;
                }
            }
        }
    }

    return (

        <Form action="/apply" method="POST"
            className="bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-100 max-w-7xl mx-auto">
            {/* Header */}
            <div className="bg-gradient-to-l from-primary-800 to-primary-600 p-8 text-white text-center">
                <h1 className="text-3xl font-extrabold mb-2">{deadlineDate.contestName || 'استمارة ترشح للمشاركة في المناظرة الخارجية لانتداب إطارات بعنوان سنة 2025'}</h1>
                <p className="opacity-90 max-w-3xl mx-auto mb-4">الرجاء تعمير البيانات المطلوبة بكلّ دقة باللغة

                    العربية ثم المصادقة عليها.
                    ولا يمكن تعديلها بعد المصادقة.
                    وطباعتها وإرفاقها بملف الترشح.</p>

                <a
                    href="/reprint"
                    className="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-white/10 hover:bg-white/20 text-white border border-white/30 backdrop-blur-sm text-sm font-bold transition-all shadow-sm"
                >
                    <span>🖨️ هل قمت بالتسجيل سابقاً وتريد طباعة استمارة الترشح؟ اضغط هنا</span>
                </a>
            </div>

            {/* Deadline Countdown Banner */}
            <div className={`p-4 border-b flex flex-col md:flex-row items-center justify-between gap-4 transition-colors ${timeLeft.isExpired ? 'bg-red-50 border-red-200' : 'bg-orange-50 border-orange-200'}`}>
                <div className="flex items-center gap-3">
                    <div className={`p-3 rounded-full ${timeLeft.isExpired ? 'bg-red-100 text-red-600' : 'bg-orange-100 text-orange-600'}`}>
                        {timeLeft.isExpired ? <AlertTriangle size={24} /> : <Clock size={24} />}
                    </div>
                    <div>
                        <p className={`text-sm font-bold ${timeLeft.isExpired ? 'text-red-800' : 'text-orange-800'}`}>
                            {timeLeft.isExpired ? 'انتهت فترة التسجيل' : 'تاريخ غلق باب الترشحات'}
                        </p>
                        <p className={`text-lg font-bold ${timeLeft.isExpired ? 'text-red-900' : 'text-orange-900'}`}>
                            {deadline.toLocaleDateString('ar-TN', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit', hour12: false }).replace(/\s*في\s*/, ' - ')}
                        </p>
                    </div>
                </div>

                {!timeLeft.isExpired && (
                    <div className="flex items-center gap-2" dir="ltr">
                        <div className="bg-white p-2 rounded-lg border border-orange-200 shadow-sm text-center min-w-[60px]">
                            <span className="block text-xl font-bold text-orange-600 leading-none">{timeLeft.days}</span>
                            <span className="text-[10px] text-gray-500 uppercase font-bold">يوم</span>
                        </div>
                        <span className="font-bold text-orange-300">:</span>
                        <div className="bg-white p-2 rounded-lg border border-orange-200 shadow-sm text-center min-w-[60px]">
                            <span className="block text-xl font-bold text-orange-600 leading-none">{timeLeft.hours}</span>
                            <span className="text-[10px] text-gray-500 uppercase font-bold">ساعة</span>
                        </div>
                        <span className="font-bold text-orange-300">:</span>
                        <div className="bg-white p-2 rounded-lg border border-orange-200 shadow-sm text-center min-w-[60px]">
                            <span className="block text-xl font-bold text-orange-600 leading-none">{timeLeft.minutes}</span>
                            <span className="text-[10px] text-gray-500 uppercase font-bold">دقيقة</span>
                        </div>
                        <span className="font-bold text-orange-300">:</span>
                        <div className="bg-white p-2 rounded-lg border border-orange-200 shadow-sm text-center min-w-[60px]">
                            <span className="block text-xl font-bold text-orange-600 leading-none">{timeLeft.seconds}</span>
                            <span className="text-[10px] text-gray-500 uppercase font-bold">ثانية</span>
                        </div>
                    </div>
                )}
            </div>


            <div className="p-6 md:p-10 space-y-12">

                {(Object.keys(errors).length > 0 || ageError || scoreError) && (
                    <div className="mb-4 rounded-lg border border-red-300 bg-red-50 p-4 text-red-800 font-bold">
                        <ul className="list-disc list-inside space-y-1">
                            {ageError && <li>{ageError}</li>}
                            {scoreError && <li>{scoreError}</li>}
                            {Object.entries(errors).map(([key, error]) => (
                                <li key={key}>{error as string}</li>
                            ))}
                        </ul>
                    </div>
                )}

                {/* Section I: Selection of Position */}
                <section>
                    <SectionHeader number="I" title="اختيار رمز المناظرة والخطة المطلوبة" />
                    <div className="bg-amber-50 p-6 rounded-xl border border-amber-200">
                        <label className="block text-base font-bold text-gray-800 mb-2">
                            رمز المناظرة المزمع المشاركة فيها (الخطة المطلوبة) <span className="text-red-500">*</span>
                        </label>
                        <select
                            name="position"
                            value={data.position}
                            onChange={handleChange}
                            disabled={timeLeft.isExpired}
                            className="w-full p-3 border rounded-lg text-right text-lg font-bold bg-white outline-none focus:ring-2 focus:ring-amber-500 border-amber-300"
                            required
                        >
                            <option value="">-- اختر رمز المناظرة --</option>
                            {Array.isArray(positions) && positions.map((pos: any) => {
                                const code = typeof pos === 'object' ? pos.code : pos;
                                const name = typeof pos === 'object' ? `${pos.code} - ${pos.name} (${pos.specialty || pos.degree})` : pos;
                                return (
                                    <option key={code} value={code}>
                                        {name}
                                    </option>
                                );
                            })}
                        </select>

                        {data.position && (
                            <div className="mt-3 p-3 bg-white rounded border border-amber-200 text-sm text-gray-700">
                                <strong>الخطة المختارة :</strong> {
                                    Array.isArray(positions) && positions.find((p: any) => (p.code || p) == data.position)?.name
                                } | <strong>رمز المناظرة :</strong> {data.position}
                            </div>
                        )}
                    </div>
                </section>

                {/* Section II: Personal Info */}
                <section>
                    <SectionHeader number="II" title="الهوية والمعطيات الشخصية" />

                    <div className="grid grid-cols-1 md:grid-cols-12 gap-6">
                        {/* Row 1: Name & Gender */}
                        <div className="md:col-span-8">
                            <InputGroup
                                label="اسم ولقب المترشح"
                                name="name"
                                value={data.name}
                                onChange={handleChange}
                                required
                                placeholder="الاسم واللقب كما هو في بطاقة التعريف"
                                disabled={timeLeft.isExpired}
                            />
                        </div>
                        <div className="md:col-span-4">
                            <label className="text-sm font-bold text-gray-700 flex items-center gap-2 mb-3">
                                الجنس <span className="text-red-500">*</span>
                            </label>
                            <div className="flex gap-4">
                                <label className={`flex-1 flex items-center justify-center gap-2 p-2.5 rounded-lg border cursor-pointer transition-all ${data.gender === 'ذكر' ? 'bg-blue-50 border-blue-500 text-blue-700' : 'border-gray-300 hover:bg-gray-50'}`}>
                                    <input
                                        type="radio"
                                        name="gender"
                                        value="ذكر"
                                        checked={data.gender === 'ذكر'}
                                        onChange={handleChange}
                                        className="w-4 h-4 accent-primary-600"
                                        disabled={timeLeft.isExpired}
                                    />
                                    <span>ذكر</span>
                                </label>
                                <label className={`flex-1 flex items-center justify-center gap-2 p-2.5 rounded-lg border cursor-pointer transition-all ${data.gender === 'انثى' ? 'bg-pink-50 border-pink-500 text-pink-700' : 'border-gray-300 hover:bg-gray-50'}`}>
                                    <input
                                        type="radio"
                                        name="gender"
                                        value="انثى"
                                        checked={data.gender === 'انثى'}
                                        onChange={handleChange}
                                        className="w-4 h-4 accent-pink-500"
                                        disabled={timeLeft.isExpired}
                                    />
                                    <span>أنثى</span>
                                </label>
                            </div>
                        </div>

                        {/* Row 2: Birth Info */}
                        <div className="md:col-span-6">
                            <InputGroup
                                label="تاريخ الولادة"
                                name="birth_date"
                                type="date"
                                value={data.birth_date}
                                onChange={handleChange}
                                required
                                disabled={timeLeft.isExpired}
                            />
                            {(ageError || errors.birth_date) && (
                                <div className="mt-2 p-3 bg-red-50 border border-red-300 rounded-lg text-red-700 text-sm font-bold flex items-center gap-2">
                                    <AlertTriangle size={18} className="shrink-0 text-red-600" />
                                    <span>{ageError || (errors.birth_date as string)}</span>
                                </div>
                            )}
                        </div>

                        {/* Row 3: Address */}
                        <div className="md:col-span-12">
                            <div className="bg-gray-50 p-4 rounded-lg border border-gray-200 grid grid-cols-1 md:grid-cols-12 gap-4">
                                <div className="md:col-span-12">
                                    <h3 className="text-sm font-bold text-gray-700 mb-2">العنوان الشخصي الحالي</h3>
                                </div>
                                <div className="md:col-span-3">
                                    <SelectGroup
                                        label="الولاية"
                                        name="governorate"
                                        value={data.governorate}
                                        onChange={handleGovernorateChange}
                                        required
                                        options={TUNISIAN_GOVERNORATES.map(g => ({ value: g, label: g }))}
                                        disabled={timeLeft.isExpired}
                                    />
                                </div>
                                <div className="md:col-span-3">
                                    <SelectGroup
                                        label="المعتمدية"
                                        name="city"
                                        value={data.city}
                                        onChange={handleChange}
                                        required
                                        disabled={!data.governorate || timeLeft.isExpired}
                                        options={
                                            data.governorate && TUNISIAN_DELEGATIONS[data.governorate]
                                                ? TUNISIAN_DELEGATIONS[data.governorate].map(d => ({ value: d, label: d }))
                                                : []
                                        }
                                        placeholder={data.governorate ? '-- اختر المعتمدية --' : '-- اختر الولاية أولاً --'}
                                    />
                                </div>
                                <div className="md:col-span-4">
                                    <InputGroup
                                        label="العنوان (النهج / الحي)"
                                        name="address"
                                        value={data.address}
                                        onChange={handleChange}
                                        required
                                        placeholder="رقم المنزل، اسم النهج، الحي..."
                                        disabled={timeLeft.isExpired}
                                    />
                                </div>
                                <div className="md:col-span-2">
                                    <InputGroup
                                        label="الترقيم البريدي"
                                        name="postal_code"
                                        type="tel"
                                        digitsOnly={true}
                                        maxLength={4}
                                        value={data.postal_code}
                                        onChange={handleChange}
                                        required
                                        placeholder="XXXX"
                                        className="tracking-widest font-mono text-center"
                                        dir="ltr"
                                        disabled={timeLeft.isExpired}
                                    />
                                </div>
                            </div>
                        </div>

                        {/* Row 4: ID Card */}
                        <div className="md:col-span-6">
                            <InputGroup
                                label="رقم بطاقة التعريف الوطنية"
                                name="cin"
                                type="tel"
                                digitsOnly={true}
                                maxLength={8}
                                value={data.cin}
                                onChange={handleChange}
                                required
                                placeholder="XXXXXXXX"
                                className="tracking-widest font-mono text-left"
                                dir="ltr"
                                disabled={timeLeft.isExpired}
                            />
                        </div>
                        <div className="md:col-span-6">
                            <InputGroup
                                label="تاريخ الإصدار"
                                name="cin_date"
                                type="date"
                                value={data.cin_date}
                                onChange={handleChange}
                                required
                                disabled={timeLeft.isExpired}
                            />
                        </div>

                        {/* Row 5: Contact */}
                        <div className="md:col-span-6">
                            <InputGroup
                                label="رقم الهاتف الجوال"
                                name="tel"
                                type="tel"
                                digitsOnly={true}
                                maxLength={8}
                                value={data.tel}
                                onChange={handleChange}
                                required
                                placeholder="XXXXXXXX"
                                className="tracking-widest text-left"
                                dir="ltr"
                                disabled={timeLeft.isExpired}
                            />
                        </div>
                        <div className="md:col-span-6">
                            <InputGroup
                                label="البريد الإلكتروني"
                                name="email"
                                type="email"
                                value={data.email}
                                onChange={handleChange}
                                required
                                placeholder="example@email.com"
                                className="text-left"
                                dir="ltr"
                                disabled={timeLeft.isExpired}
                            />
                        </div>
                    </div>
                </section>

                {/* Section III: Education & Form Type Specifics */}
                <section>
                    <SectionHeader number="III" title="المستوى التعليمي والمعطيات الخاصة بالخطة" />

                    {!data.position ? (
                        <div className="p-6 bg-amber-50 border border-amber-200 rounded-xl text-center text-amber-800 font-bold">
                            الرجاء اختيار رمز المناظرة أعلاه أولاً لفتـح الحقول الخاصة بالخطة المطلوبـة.
                        </div>
                    ) : (
                        <div className="grid grid-cols-1 md:grid-cols-12 gap-6">

                            {/* TYPE 1: CADRES */}
                            {profileType === 'cadre' && (
                                <>
                                    <div className="md:col-span-12">
                                        <InputGroup
                                            label="معدل البكالوريا"
                                            name="bac_average"
                                            type="text"
                                            decimalOnly={true}
                                            value={data.bac_average}
                                            onChange={handleChange}
                                            placeholder="--.--"
                                            required
                                            disabled={timeLeft.isExpired}
                                        />
                                    </div>
                                    <div className="md:col-span-12">
                                        <InputGroup
                                            label="الشهادة العلمية"
                                            name="degree"
                                            value={data.degree}
                                            onChange={handleChange}
                                            required
                                            placeholder="الشهادة الوطنية لمهندس / الإجازة الوطنية / الأستاذية..."
                                            disabled={timeLeft.isExpired}
                                        />
                                    </div>
                                    <div className="md:col-span-8">
                                        <InputGroup
                                            label="الاختصاص"
                                            name="specialty"
                                            value={data.specialty}
                                            onChange={handleChange}
                                            required
                                            placeholder="مثال: هندسة البرمجيات، مالية، إدارة الأعمال..."
                                            disabled={timeLeft.isExpired}
                                        />
                                    </div>
                                    <div className="md:col-span-4">
                                        <InputGroup
                                            label="سنة التخرج"
                                            name="graduation_year"
                                            type="tel"
                                            digitsOnly={true}
                                            maxLength={4}
                                            value={data.graduation_year}
                                            onChange={handleChange}
                                            required
                                            disabled={timeLeft.isExpired}
                                        />
                                    </div>
                                    <div className="md:col-span-12">
                                        <InputGroup
                                            label="مؤسسة التعليم العالي"
                                            name="institution"
                                            value={data.institution}
                                            onChange={handleChange}
                                            placeholder="اسم الكلية أو المعهد العالي..."
                                            disabled={timeLeft.isExpired}
                                        />
                                    </div>
                                    <div className="md:col-span-8">
                                        <InputGroup
                                            label="قرار المعادلة رقم (بالنسبة للشهادات المسلمة من قبل الجامعات الخاصة أو الأجنبية)"
                                            name="equivalence_decision"
                                            value={data.equivalence_decision}
                                            onChange={handleChange}
                                            disabled={timeLeft.isExpired}
                                        />
                                    </div>
                                    <div className="md:col-span-4">
                                        <InputGroup
                                            label="بتاريخ"
                                            name="equivalence_date"
                                            type="date"
                                            value={data.equivalence_date}
                                            onChange={handleChange}
                                            disabled={timeLeft.isExpired}
                                        />
                                    </div>
                                    <div className="md:col-span-12">
                                        <InputGroup
                                            label="معدل سنة التخرج"
                                            name="grad_average"
                                            type="text"
                                            decimalOnly={true}
                                            value={data.grad_average}
                                            onChange={handleChange}
                                            placeholder="--.--"
                                            disabled={timeLeft.isExpired}
                                            required
                                        />
                                    </div>
                                </>
                            )}

                            {/* TYPE 2: TECHNICIEN SUPERIEUR */}
                            {profileType === 'technicien' && (
                                <>
                                    <div className="md:col-span-12">
                                        <InputGroup
                                            label="معدل مؤهل التقني المهني أو البكالوريا"
                                            name="bac_average"
                                            type="number"
                                            step="0.01"
                                            min={0}
                                            max={20}
                                            value={data.bac_average}
                                            onChange={handleChange}
                                            placeholder="--.--"
                                            required
                                            disabled={timeLeft.isExpired}
                                        />
                                    </div>
                                    <div className="md:col-span-12">
                                        <InputGroup
                                            label="الشهادة العلمية"
                                            name="degree"
                                            value={data.degree || 'مؤهل التقني السامي'}
                                            onChange={handleChange}
                                            required
                                            disabled={timeLeft.isExpired}
                                        />
                                    </div>
                                    <div className="md:col-span-8">
                                        <InputGroup
                                            label="الاختصاص"
                                            name="specialty"
                                            value={data.specialty || 'مساعد مديرية'}
                                            onChange={handleChange}
                                            required
                                            disabled={timeLeft.isExpired}
                                        />
                                    </div>
                                    <div className="md:col-span-4">
                                        <InputGroup
                                            label="سنة التخرج"
                                            name="graduation_year"
                                            type="number"
                                            min={1980}
                                            max={new Date().getFullYear()}
                                            value={data.graduation_year}
                                            onChange={handleChange}
                                            required
                                            disabled={timeLeft.isExpired}
                                        />
                                    </div>
                                    <div className="md:col-span-12">
                                        <InputGroup
                                            label="مركز التكوين المهني"
                                            name="institution"
                                            value={data.institution}
                                            onChange={handleChange}
                                            placeholder="اسم مركز التكوين المهني..."
                                            disabled={timeLeft.isExpired}
                                        />
                                    </div>
                                    <div className="md:col-span-8">
                                        <InputGroup
                                            label="قرار المعادلة رقم (بالنسبة للشهادات المسلمة من قبل المؤسسات الأجنبية)"
                                            name="equivalence_decision"
                                            value={data.equivalence_decision}
                                            onChange={handleChange}
                                            disabled={timeLeft.isExpired}
                                        />
                                    </div>
                                    <div className="md:col-span-4">
                                        <InputGroup
                                            label="بتاريخ"
                                            name="equivalence_date"
                                            type="date"
                                            value={data.equivalence_date}
                                            onChange={handleChange}
                                            disabled={timeLeft.isExpired}
                                        />
                                    </div>
                                    <div className="md:col-span-12">
                                        <InputGroup
                                            label="معدل سنة التخرج"
                                            name="grad_average"
                                            type="number"
                                            step="0.01"
                                            min={0}
                                            max={20}
                                            value={data.grad_average}
                                            onChange={handleChange}
                                            placeholder="--.--"
                                            disabled={timeLeft.isExpired}
                                            required
                                        />
                                    </div>
                                </>
                            )}

                            {/* TYPE 3: COMMIS D'ADMINISTRATION */}
                            {profileType === 'commis' && (
                                <>
                                    <div className="md:col-span-8">
                                        <SelectGroup
                                            label="المستوى الدراسي"
                                            name="school_level"
                                            value={data.school_level}
                                            onChange={handleChange}
                                            required
                                            options={schoolLevelOptions}
                                            placeholder="-- اختر المستوى الدراسي المقبول --"
                                            disabled={timeLeft.isExpired}
                                        />
                                    </div>
                                    <div className="md:col-span-4">
                                        <InputGroup
                                            label="سنة الانقطاع عن الدراسة"
                                            name="end_school_year"
                                            type="tel"
                                            digitsOnly={true}
                                            maxLength={4}
                                            value={data.end_school_year}
                                            onChange={handleChange}
                                            disabled={timeLeft.isExpired}
                                        />
                                    </div>
                                    <div className="md:col-span-12">
                                        <InputGroup
                                            label="المؤسسة التعليمية"
                                            name="school_institution"
                                            value={data.school_institution}
                                            onChange={handleChange}
                                            placeholder="اسم المدرسة أو المعهد الثانوي..."
                                            disabled={timeLeft.isExpired}
                                        />
                                    </div>
                                    <div className="md:col-span-12">
                                        <InputGroup
                                            label="معدل السنة التاسعة من التعليم الأساسي"
                                            name="grade_9_average"
                                            type="text"
                                            decimalOnly={true}
                                            value={data.grade_9_average}
                                            onChange={handleChange}
                                            required
                                            placeholder="--.--"
                                            disabled={timeLeft.isExpired}
                                        />
                                    </div>
                                </>
                            )}

                            {/* TYPE 4: CHAUFFEUR */}
                            {profileType === 'chauffeur' && (
                                <>
                                    <div className="md:col-span-8">
                                        <SelectGroup
                                            label="المستوى الدراسي"
                                            name="school_level"
                                            value={data.school_level}
                                            onChange={handleChange}
                                            required
                                            options={schoolLevelOptions}
                                            placeholder="-- اختر المستوى الدراسي المقبول --"
                                            disabled={timeLeft.isExpired}
                                        />
                                    </div>
                                    <div className="md:col-span-4">
                                        <InputGroup
                                            label="سنة الانقطاع عن الدراسة"
                                            name="end_school_year"
                                            type="tel"
                                            digitsOnly={true}
                                            maxLength={4}
                                            value={data.end_school_year}
                                            onChange={handleChange}
                                            disabled={timeLeft.isExpired}
                                        />
                                    </div>
                                    <div className="md:col-span-12">
                                        <InputGroup
                                            label="المؤسسة التعليمية"
                                            name="school_institution"
                                            value={data.school_institution}
                                            onChange={handleChange}
                                            placeholder="اسم المدرسة أو المعهد الثانوي..."
                                            disabled={timeLeft.isExpired}
                                        />
                                    </div>
                                    <div className="md:col-span-12">
                                        <InputGroup
                                            label="معدل السنة التاسعة من التعليم الأساسي"
                                            name="grade_9_average"
                                            type="text"
                                            decimalOnly={true}
                                            value={data.grade_9_average}
                                            onChange={handleChange}
                                            required
                                            placeholder="--.--"
                                            disabled={timeLeft.isExpired}
                                        />
                                    </div>
                                    <div className="md:col-span-6 bg-blue-50 p-4 rounded-lg border border-blue-200">
                                        <InputGroup
                                            label="رخصة السياقة"
                                            name="driving_license_category"
                                            value={data.driving_license_category || 'صنف ب (B)'}
                                            onChange={handleChange}
                                            required
                                            disabled={timeLeft.isExpired}
                                        />
                                    </div>
                                    <div className="md:col-span-6 bg-blue-50 p-4 rounded-lg border border-blue-200">
                                        <InputGroup
                                            label="تاريخ الإصدار / الحصول على الرخصة"
                                            name="driving_license_date"
                                            type="date"
                                            value={data.driving_license_date}
                                            onChange={handleChange}
                                            required
                                            disabled={timeLeft.isExpired}
                                        />
                                        {licenseError && (
                                            <p className="text-red-600 text-xs font-bold mt-2">
                                                {licenseError}
                                            </p>
                                        )}
                                    </div>
                                </>
                            )}

                            {/* TYPE 5: NETTOYAGE */}
                            {profileType === 'nettoyage' && (
                                <>
                                    <div className="md:col-span-8">
                                        <SelectGroup
                                            label="المستوى الدراسي"
                                            name="school_level"
                                            value={data.school_level}
                                            onChange={handleChange}
                                            required
                                            options={schoolLevelOptions}
                                            placeholder="-- اختر المستوى الدراسي المقبول --"
                                            disabled={timeLeft.isExpired}
                                        />
                                    </div>
                                    <div className="md:col-span-4">
                                        <InputGroup
                                            label="سنة الانقطاع عن الدراسة"
                                            name="end_school_year"
                                            type="tel"
                                            digitsOnly={true}
                                            maxLength={4}
                                            value={data.end_school_year}
                                            onChange={handleChange}
                                            disabled={timeLeft.isExpired}
                                        />
                                    </div>
                                    <div className="md:col-span-12">
                                        <InputGroup
                                            label="المؤسسة التعليمية"
                                            name="school_institution"
                                            value={data.school_institution}
                                            onChange={handleChange}
                                            placeholder="اسم المدرسة الإبتدائية أو الإعدادية..."
                                            disabled={timeLeft.isExpired}
                                        />
                                    </div>
                                    <div className="md:col-span-12">
                                        <InputGroup
                                            label="معدل السنة السادسة من التعليم الأساسي"
                                            name="grade_6_average"
                                            type="text"
                                            decimalOnly={true}
                                            value={data.grade_6_average}
                                            onChange={handleChange}
                                            required
                                            placeholder="--.--"
                                            disabled={timeLeft.isExpired}
                                        />
                                    </div>
                                </>
                            )}

                        </div>
                    )}
                </section>
                <div className="md:col-span-12">
                    <div className="p-4 rounded-lg border border-gray-200 bg-gray-50 flex items-center gap-3">
                        <input
                            type="checkbox"
                            name="agreement"
                            id="agreement"
                            checked={agreed}
                            onChange={(e) => setAgreed(e.target.checked)}
                            disabled={timeLeft.isExpired}
                            className="w-5 h-5 accent-primary-600"
                        />
                        <label htmlFor="agreement" className="text-sm font-bold text-gray-700 cursor-pointer">
                            أشهد بصحة البيانات المذكورة أعلاه وأتحمل مسؤوليتي في حالة ثبوت عكس ذلك
                            <span className="text-red-500">*</span>
                        </label>
                    </div>
                </div>
                {/* Footer Actions */}
                <div className="pt-6 border-t border-gray-200 flex flex-col md:flex-row justify-between items-center gap-4">
                    <p className=" text-red-500 font-bold">
                        هام جدا: لا يمكن تحيين البيانات بعد تأكيد الترشح

                    </p>
                    <button
                        type="submit"
                        disabled={timeLeft.isExpired}
                        className={`
              flex items-center gap-2 px-8 py-3 rounded-lg cursor-pointer font-bold text-white shadow-lg
              transition-all duration-200 transform hover:-translate-y-1
              ${(timeLeft.isExpired) ? 'bg-gray-400 cursor-not-allowed hover:transform-none' : 'bg-primary-600 hover:bg-primary-700'}
            `}
                    >
                        {timeLeft.isExpired ? ('التسجيل مغلق') : (
                            <>
                                <span>تأكيد الترشح</span>
                                <Send size={18} className="rtl:rotate-180" />
                            </>
                        )}
                    </button>
                </div>
            </div>
        </Form>

    );
};
