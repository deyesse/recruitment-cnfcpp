import React from 'react'
import { Head } from '@inertiajs/react'

interface SuccessProps {
  data?: {
    id?: number | string
    position?: string
    position_name?: string
    position_type?: string
    contest_name?: string
    header_text?: string
    logo_url?: string
    show_score?: boolean
    min_score?: number
    score?: number | string
    created_at?: string

    name?: string
    gender?: string
    birth_date?: string
    address?: string
    city?: string
    governorate?: string
    postal_code?: string
    cin?: string
    cin_date?: string
    tel?: string
    email?: string

    // Cadre / Technicien
    degree?: string
    specialty?: string
    graduation_year?: number | string
    institution?: string
    equivalence_decision?: string
    equivalence_date?: string
    bac_average?: number | string
    btp_average?: number | string
    grad_average?: number | string

    // Commis / Chauffeur / Nettoyage
    school_level?: string
    end_school_year?: number | string
    school_institution?: string
    grade_9_average?: number | string
    grade_6_average?: number | string

    // Chauffeur
    driving_license_category?: string
    driving_license_date?: string
  }
}

export default function Success({ data = {} }: SuccessProps) {
  const posCode = String(data.position || '').trim()
  const posNum = parseInt(posCode, 10)
  const posType = data.position_type || (
    posNum >= 1 && posNum <= 15 ? 'cadre' :
    posCode === '16' ? 'technicien' :
    ['17', '18'].includes(posCode) ? 'commis' :
    posCode === '19' ? 'chauffeur' :
    ['20', '21'].includes(posCode) ? 'nettoyage' : 'cadre'
  )

  const isCadre = posType === 'cadre'
  const isTechnicien = posType === 'technicien'
  const isCommis = posType === 'commis'
  const isChauffeur = posType === 'chauffeur'
  const isNettoyage = posType === 'nettoyage'

  /* ── Category Title / Label ── */
  const posTitle = (() => {
    if (data.position_name) return data.position_name
    if (posNum >= 1 && posNum <= 15) return 'إطار (مهندس / متصرف / تقني)'
    if (posCode === '16') return 'ملحق إدارة (مؤهل التقني السامي)'
    if (['17', '18'].includes(posCode)) return 'مستكتب إدارة'
    if (posCode === '19') return 'سائق'
    if (['20', '21'].includes(posCode)) return 'عون تنظيف'
    return `خطة رقم ${posCode}`
  })()

  const headerLines = (() => {
    if (data.header_text && data.header_text.trim()) {
      return data.header_text
        .split('\n')
        .map((l: string) => l.trim())
        .filter(Boolean)
    }
    return [
      'الجمهورية التونسية',
      'وزارة التشغيل والتكوين المهني',
      'المركز الوطني لتكوين المكونين وهندسة التكوين'
    ]
  })()

  const equivalenceText = [data.equivalence_decision, data.equivalence_date ? `بتاريخ ${data.equivalence_date}` : null]
    .filter(Boolean)
    .join(' - ')

  const scoreFormatted = data.score !== undefined && data.score !== null && data.score !== ''
    ? (typeof data.score === 'number' ? Number(data.score).toFixed(2) : data.score)
    : '-'

  const logoSrc = data.logo_url || '/cnfcpp.png'

  return (
    <div lang="ar" dir="rtl" className="bg-slate-100 min-h-screen text-slate-900 font-sans antialiased selection:bg-teal-100">
      <Head title={`استمارة ترشح - ${data.name || 'مطلب ترشح'} (رقم ${data.id || ''})`}>
        <link rel="preconnect" href="https://fonts.bunny.net" />
        <link href="https://fonts.bunny.net/css?family=tajawal:400,500,700,800,900" rel="stylesheet" />
      </Head>

      {/* ── Screen Actions Bar (Hidden on print) ── */}
      <div className="no-print max-w-4xl mx-auto pt-8 px-4">
        <div className="bg-white rounded-2xl shadow-lg border border-slate-200/80 p-6 mb-6">
          <div className="flex flex-col md:flex-row items-center justify-between gap-4">
            <div className="flex items-center gap-4 text-right">
              <div className="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-2xl font-bold shadow-inner">
                ✓
              </div>
              <div>
                <h1 className="text-xl font-bold text-slate-900">تم تسجيل مطلب ترشحكم بنجاح</h1>
                <p className="text-sm text-slate-600 mt-0.5">
                  يرجى طباعة استمارة الترشح وإمضاؤها لإرفاقها بالملف الورقي في صورة القبول الأولي.
                </p>
              </div>
            </div>

            <div className="flex items-center gap-3 w-full md:w-auto">
              <button
                type="button"
                onClick={() => window.print()}
                className="flex-1 md:flex-none inline-flex items-center justify-center gap-2 bg-teal-700 hover:bg-teal-800 active:bg-teal-900 text-white px-6 py-3 rounded-xl font-bold text-sm shadow-md shadow-teal-700/20 hover:shadow-lg transition-all duration-200 cursor-pointer"
              >
                <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                <span>طباعة الاستمارة / حفظ PDF</span>
              </button>

              <a
                href="/"
                className="inline-flex items-center justify-center bg-slate-100 hover:bg-slate-200 text-slate-700 px-4 py-3 rounded-xl font-medium text-sm transition"
              >
                الرئيسية
              </a>
            </div>
          </div>
        </div>
      </div>

      {/* ── The Printable Sheet Container ── */}
      <div className="max-w-4xl mx-auto px-4 pb-12 print:p-0 print:m-0 print:max-w-none">
        <div className="sheet-card bg-white shadow-xl rounded-2xl p-6 md:p-10 border border-slate-200 print:shadow-none print:border-none print:p-0 print:rounded-none flex flex-col justify-between min-h-[850px] print:min-h-[272mm]">

          {/* ══════════════════════════════════════════════════════════════
              HEADER SECTION
             ══════════════════════════════════════════════════════════════ */}
          <header className="header-box pb-4 mb-4">
            <div className="flex flex-row items-center justify-between gap-4">

              {/* Right: Official State Hierarchy */}
              <div className="ministry-col text-right text-xs md:text-sm leading-relaxed font-bold text-slate-900 min-w-[210px]">
                {headerLines.map((line: string, idx: number) => (
                  <div key={idx} className={idx === headerLines.length - 1 ? 'font-black mt-0.5' : ''}>
                    {line}
                  </div>
                ))}
              </div>

              {/* Center: Contest Title */}
              <div className="title-col flex-1 text-center px-2">
                <h1 className="text-base md:text-lg font-extrabold text-teal-800 leading-snug">
                  استمارة ترشح للمناظرة الخارجية لانتداب إطارات وأعوان
                </h1>
                <div className="text-xs md:text-sm font-bold text-slate-800 mt-0.5">
                  بعنوان سنتي 2025 و 2026
                </div>
              </div>

              {/* Left: Logo (aligned to the far left) */}
              <div className="logo-col flex items-center justify-start text-left w-[170px] max-w-[170px] shrink-0">
                <img
                  src={logoSrc}
                  alt="شعار المؤسسة"
                  className="max-h-16 md:max-h-20 max-w-full w-auto h-auto object-contain mr-auto ml-0"
                  onError={(e) => {
                    const target = e.currentTarget as HTMLImageElement;
                    if (target.src !== window.location.origin + '/cnfcpp.png') {
                      target.src = '/cnfcpp.png';
                    }
                  }}
                />
              </div>
            </div>

            {/* Top 3 Horizontal Metric / Info Cards */}
            <div className="top-cards-bar grid grid-cols-3 gap-3 my-4 text-center">
              {/* Right: Registration Number */}
              <div className="card-reg border border-slate-300 rounded-lg py-2.5 px-3 bg-slate-50/90 flex items-center justify-center gap-1.5 text-xs md:text-sm font-bold text-slate-900">
                <span>رقم التسجيل</span>
                <span className="font-mono font-black text-sm md:text-base mr-1">#{data.id ?? '-'}</span>
              </div>

              {/* Center: Position Code & Title */}
              <div className="card-pos border border-slate-300 rounded-lg py-2.5 px-3 bg-slate-50/90 flex items-center justify-center gap-2 text-xs md:text-sm font-bold text-slate-950">
                <span className="text-slate-700 font-semibold text-xs">الخطة ورمز المناظرة:</span>
                <span className="font-extrabold text-slate-900 text-xs md:text-sm">{posTitle}</span>
                <span className="text-slate-400 font-bold">—</span>
                <span className="font-mono font-black text-base md:text-lg text-slate-950 px-1.5 py-0.5 rounded bg-slate-200/60 border border-slate-300">
                  {data.position || '—'}
                </span>
              </div>

              {/* Left: Total Score or Date */}
              {data.show_score !== false ? (
                <div className="card-score border border-emerald-500 rounded-lg py-2.5 px-3 bg-emerald-50/70 flex items-center justify-center gap-1.5 text-xs md:text-sm font-bold text-emerald-800">
                  <span>مجموع النقاط</span>
                  <span className="font-mono font-black text-sm md:text-base text-emerald-900 mr-1">
                    {scoreFormatted}
                  </span>
                  <span className="text-xs font-semibold">نقطة</span>
                </div>
              ) : (
                <div className="card-date border border-slate-300 rounded-lg py-2.5 px-3 bg-slate-50/90 flex items-center justify-center gap-1.5 text-xs font-bold text-slate-700">
                  <span>تاريخ الترشح</span>
                  <span className="font-mono mr-1">{data.created_at || '-'}</span>
                </div>
              )}
            </div>
          </header>

          {/* ══════════════════════════════════════════════════════════════
              SECTION I: IDENTITY & PERSONAL INFO
             ══════════════════════════════════════════════════════════════ */}
          <section className="section-block mb-5">
            <h2 className="section-heading text-xs md:text-sm font-extrabold text-white bg-teal-800 py-1.5 px-4 rounded-t flex items-center justify-between">
              <span>I. الهوية والمعلومات الشخصية</span>
            </h2>

            <table className="doc-table w-full text-right text-xs md:text-sm border border-slate-300 border-t-0">
              <tbody>
                <tr>
                  <th className="cell-label w-[20%] bg-slate-50 font-bold border border-slate-300 py-2.5 px-3 text-slate-800">
                    الاسم واللقب:
                  </th>
                  <td className="cell-value w-[30%] border border-slate-300 py-2.5 px-3 font-bold text-slate-950">
                    {data.name || '-'}
                  </td>
                  <th className="cell-label w-[20%] bg-slate-50 font-bold border border-slate-300 py-2.5 px-3 text-slate-800">
                    الجنس:
                  </th>
                  <td className="cell-value w-[30%] border border-slate-300 py-2.5 px-3 font-bold text-slate-950">
                    {data.gender || '-'}
                  </td>
                </tr>
                <tr>
                  <th className="cell-label bg-slate-50 font-bold border border-slate-300 py-2.5 px-3 text-slate-800">
                    رقم ب.ت.و:
                  </th>
                  <td className="cell-value border border-slate-300 py-2.5 px-3 font-mono font-bold text-slate-950">
                    {data.cin || '-'}
                  </td>
                  <th className="cell-label bg-slate-50 font-bold border border-slate-300 py-2.5 px-3 text-slate-800">
                    تاريخ الإصدار:
                  </th>
                  <td className="cell-value border border-slate-300 py-2.5 px-3 font-mono font-bold text-slate-950">
                    {data.cin_date || '-'}
                  </td>
                </tr>
                <tr>
                  <th className="cell-label bg-slate-50 font-bold border border-slate-300 py-2.5 px-3 text-slate-800">
                    تاريخ الولادة:
                  </th>
                  <td className="cell-value border border-slate-300 py-2.5 px-3 font-mono font-bold text-slate-950">
                    {data.birth_date || '-'}
                  </td>
                  <th className="cell-label bg-slate-50 font-bold border border-slate-300 py-2.5 px-3 text-slate-800">
                    رقم الهاتف:
                  </th>
                  <td className="cell-value border border-slate-300 py-2.5 px-3 font-mono font-bold text-slate-950">
                    {data.tel || '-'}
                  </td>
                </tr>
                <tr>
                  <th className="cell-label bg-slate-50 font-bold border border-slate-300 py-2.5 px-3 text-slate-800">
                    العنوان:
                  </th>
                  <td className="cell-value border border-slate-300 py-2.5 px-3 font-bold text-slate-950">
                    {data.address || '-'}
                  </td>
                  <th className="cell-label bg-slate-50 font-bold border border-slate-300 py-2.5 px-3 text-slate-800">
                    الترقيم البريدي:
                  </th>
                  <td className="cell-value border border-slate-300 py-2.5 px-3 font-mono font-bold text-slate-950">
                    {data.postal_code || '-'}
                  </td>
                </tr>
                <tr>
                  <th className="cell-label bg-slate-50 font-bold border border-slate-300 py-2.5 px-3 text-slate-800">
                    الولاية / المعتمدية:
                  </th>
                  <td className="cell-value border border-slate-300 py-2.5 px-3 font-bold text-slate-950">
                    {trimDash(`${data.governorate || ''} ـــ ${data.city || ''}`) || '-'}
                  </td>
                  <th className="cell-label bg-slate-50 font-bold border border-slate-300 py-2.5 px-3 text-slate-800">
                    البريد الإلكتروني:
                  </th>
                  <td className="cell-value border border-slate-300 py-2.5 px-3 font-mono text-xs font-bold text-slate-950">
                    {data.email || '-'}
                  </td>
                </tr>
              </tbody>
            </table>
          </section>

          {/* ══════════════════════════════════════════════════════════════
              SECTION II & III: PROFILE SPECIFIC
             ══════════════════════════════════════════════════════════════ */}

          {/* ─── PROFILE 1: CADRES (01 à 15 : إطارات) ─── */}
          {isCadre && (
            <>
              <section className="section-block mb-5">
                <h2 className="section-heading text-xs md:text-sm font-extrabold text-white bg-teal-800 py-1.5 px-4 rounded-t flex items-center justify-between">
                  <span>II. المستوى التعليمي</span>
                </h2>
                <table className="doc-table w-full text-right text-xs md:text-sm border border-slate-300 border-t-0">
                  <tbody>
                    <tr>
                      <th className="cell-label w-[20%] bg-slate-50 font-bold border border-slate-300 py-2.5 px-3 text-slate-800">
                        الشهادة العلمية:
                      </th>
                      <td className="cell-value w-[30%] border border-slate-300 py-2.5 px-3 font-bold text-slate-950">
                        {data.degree || '-'}
                      </td>
                      <th className="cell-label w-[20%] bg-slate-50 font-bold border border-slate-300 py-2.5 px-3 text-slate-800">
                        الاختصاص:
                      </th>
                      <td className="cell-value w-[30%] border border-slate-300 py-2.5 px-3 font-bold text-slate-950">
                        {data.specialty || '-'}
                      </td>
                    </tr>
                    <tr>
                      <th className="cell-label bg-slate-50 font-bold border border-slate-300 py-2.5 px-3 text-slate-800">
                        سنة التخرج:
                      </th>
                      <td className="cell-value border border-slate-300 py-2.5 px-3 font-mono font-bold text-slate-950">
                        {data.graduation_year || '-'}
                      </td>
                      <th className="cell-label bg-slate-50 font-bold border border-slate-300 py-2.5 px-3 text-slate-800">
                        المؤسسة الجامعية:
                      </th>
                      <td className="cell-value border border-slate-300 py-2.5 px-3 font-bold text-slate-950">
                        {data.institution || 'مؤسسة تعليم عال عمومية'}
                      </td>
                    </tr>
                    {equivalenceText && (
                      <tr>
                        <th className="cell-label bg-slate-50 font-bold border border-slate-300 py-2.5 px-3 text-slate-800">
                          قرار وتاريخ المعادلة:
                        </th>
                        <td colSpan={3} className="cell-value border border-slate-300 py-2.5 px-3 text-slate-950 font-medium">
                          {equivalenceText}
                        </td>
                      </tr>
                    )}
                  </tbody>
                </table>
              </section>

              <section className="section-block mb-5">
                <h2 className="section-heading text-xs md:text-sm font-extrabold text-white bg-teal-800 py-1.5 px-4 rounded-t flex items-center justify-between">
                  <span>III. النتائج ومقياس الفرز الأولي</span>
                </h2>
                <table className="doc-table w-full text-right text-xs md:text-sm border border-slate-300 border-t-0">
                  <tbody>
                    <tr>
                      <th className="cell-label w-[20%] bg-slate-50 font-bold border border-slate-300 py-2.5 px-3 text-slate-800">
                        معدل البكالوريا:
                      </th>
                      <td className="cell-value w-[30%] border border-slate-300 py-2.5 px-3 font-mono font-bold text-slate-950">
                        {data.bac_average || '-'}
                      </td>
                      <th className="cell-label w-[20%] bg-slate-50 font-bold border border-slate-300 py-2.5 px-3 text-slate-800">
                        معدل سنة التخرج:
                      </th>
                      <td className="cell-value w-[30%] border border-slate-300 py-2.5 px-3 font-mono font-bold text-slate-950">
                        {data.grad_average || '-'}
                      </td>
                    </tr>
                    <tr>
                      <th className="cell-label bg-slate-50 font-bold border border-slate-300 py-2.5 px-3 text-slate-800">
                        مجموع النقاط المحتسب:
                      </th>
                      <td className="cell-value border border-slate-300 py-2.5 px-3 font-mono font-black text-teal-800 text-sm md:text-base">
                        {scoreFormatted} نقطة
                      </td>
                      <th className="cell-label bg-slate-50 font-bold border border-slate-300 py-2.5 px-3 text-slate-800">
                        صيغة الفرز الأولي (مجموع النقاط):
                      </th>
                      <td className="cell-value border border-slate-300 py-2.5 px-3 text-slate-950 font-medium text-xs">
                        (معدل البكالوريا × 60%) + (معدل سنة التخرج × 40%)
                      </td>
                    </tr>
                  </tbody>
                </table>
              </section>
            </>
          )}

          {/* ─── PROFILE 2: TECHNICIEN (16 : ملحق إدارة - BTP) ─── */}
          {isTechnicien && (
            <>
              <section className="section-block mb-5">
                <h2 className="section-heading text-xs md:text-sm font-extrabold text-white bg-teal-800 py-1.5 px-4 rounded-t flex items-center justify-between">
                  <span>II. المستوى التعليمي والتكويني</span>
                </h2>
                <table className="doc-table w-full text-right text-xs md:text-sm border border-slate-300 border-t-0">
                  <tbody>
                    <tr>
                      <th className="cell-label w-[20%] bg-slate-50 font-bold border border-slate-300 py-2.5 px-3 text-slate-800">
                        الشهادة المطلوبة:
                      </th>
                      <td className="cell-value w-[30%] border border-slate-300 py-2.5 px-3 font-bold text-slate-950">
                        مؤهل التقني السامي (BTP)
                      </td>
                      <th className="cell-label w-[20%] bg-slate-50 font-bold border border-slate-300 py-2.5 px-3 text-slate-800">
                        الاختصاص:
                      </th>
                      <td className="cell-value w-[30%] border border-slate-300 py-2.5 px-3 font-bold text-slate-950">
                        {data.specialty || 'مساعد مديرية'}
                      </td>
                    </tr>
                    <tr>
                      <th className="cell-label bg-slate-50 font-bold border border-slate-300 py-2.5 px-3 text-slate-800">
                        سنة التخرج:
                      </th>
                      <td className="cell-value border border-slate-300 py-2.5 px-3 font-mono font-bold text-slate-950">
                        {data.graduation_year || '-'}
                      </td>
                      <th className="cell-label bg-slate-50 font-bold border border-slate-300 py-2.5 px-3 text-slate-800">
                        مركز التكوين المهني:
                      </th>
                      <td className="cell-value border border-slate-300 py-2.5 px-3 font-bold text-slate-950">
                        {data.institution || 'مركز تكوين مهني عمومي'}
                      </td>
                    </tr>
                    {equivalenceText && (
                      <tr>
                        <th className="cell-label bg-slate-50 font-bold border border-slate-300 py-2.5 px-3 text-slate-800">
                          قرار وتاريخ المعادلة:
                        </th>
                        <td colSpan={3} className="cell-value border border-slate-300 py-2.5 px-3 text-slate-950 font-medium">
                          {equivalenceText}
                        </td>
                      </tr>
                    )}
                  </tbody>
                </table>
              </section>

              <section className="section-block mb-5">
                <h2 className="section-heading text-xs md:text-sm font-extrabold text-white bg-teal-800 py-1.5 px-4 rounded-t flex items-center justify-between">
                  <span>III. النتائج ومقياس الفرز الأولي</span>
                </h2>
                <table className="doc-table w-full text-right text-xs md:text-sm border border-slate-300 border-t-0">
                  <tbody>
                    <tr>
                      <th className="cell-label w-[20%] bg-slate-50 font-bold border border-slate-300 py-2.5 px-3 text-slate-800">
                        معدل مؤهل التقني / البكالوريا:
                      </th>
                      <td className="cell-value w-[30%] border border-slate-300 py-2.5 px-3 font-mono font-bold text-slate-950">
                        {data.btp_average || data.bac_average || '-'}
                      </td>
                      <th className="cell-label w-[20%] bg-slate-50 font-bold border border-slate-300 py-2.5 px-3 text-slate-800">
                        معدل سنة التخرج:
                      </th>
                      <td className="cell-value w-[30%] border border-slate-300 py-2.5 px-3 font-mono font-bold text-slate-950">
                        {data.grad_average || '-'}
                      </td>
                    </tr>
                    <tr>
                      <th className="cell-label bg-slate-50 font-bold border border-slate-300 py-2.5 px-3 text-slate-800">
                        مجموع النقاط المحتسب:
                      </th>
                      <td className="cell-value border border-slate-300 py-2.5 px-3 font-mono font-black text-teal-800 text-sm md:text-base">
                        {scoreFormatted} نقطة
                      </td>
                      <th className="cell-label bg-slate-50 font-bold border border-slate-300 py-2.5 px-3 text-slate-800">
                        صيغة الفرز الأولي (مجموع النقاط):
                      </th>
                      <td className="cell-value border border-slate-300 py-2.5 px-3 text-slate-950 font-medium text-xs">
                        (معدل البكالوريا أو مؤهل التقني المهني × 40%) + (معدل سنة التخرج × 60%)
                      </td>
                    </tr>
                  </tbody>
                </table>
              </section>
            </>
          )}

          {/* ─── PROFILE 3: COMMIS (17 & 18 : مستكتب إدارة) ─── */}
          {isCommis && (
            <>
              <section className="section-block mb-5">
                <h2 className="section-heading text-xs md:text-sm font-extrabold text-white bg-teal-800 py-1.5 px-4 rounded-t flex items-center justify-between">
                  <span>II. المستوى الدراسي والتكوين</span>
                </h2>
                <table className="doc-table w-full text-right text-xs md:text-sm border border-slate-300 border-t-0">
                  <tbody>
                    <tr>
                      <th className="cell-label w-[20%] bg-slate-50 font-bold border border-slate-300 py-2.5 px-3 text-slate-800">
                        المستوى المصرح به:
                      </th>
                      <td colSpan={3} className="cell-value border border-slate-300 py-2.5 px-3 font-bold text-slate-950">
                        <span>{data.school_level || '-'}</span>
                        <span className="text-slate-500 font-normal mr-2 text-xs">
                          (الشروط: أدناه التاسعة أساسي بنجاح وأقصاه الرابعة ثانوي منهاة نظام جديد)
                        </span>
                      </td>
                    </tr>
                    {(data.school_institution || data.end_school_year) && (
                      <tr>
                        <th className="cell-label bg-slate-50 font-bold border border-slate-300 py-2.5 px-3 text-slate-800">
                          المؤسسة التعليمية / المعهد:
                        </th>
                        <td className="cell-value border border-slate-300 py-2.5 px-3 font-bold text-slate-950">
                          {data.school_institution || '-'}
                        </td>
                        <th className="cell-label bg-slate-50 font-bold border border-slate-300 py-2.5 px-3 text-slate-800">
                          سنة الانقطاع:
                        </th>
                        <td className="cell-value border border-slate-300 py-2.5 px-3 font-mono font-bold text-slate-950">
                          {data.end_school_year || '-'}
                        </td>
                      </tr>
                    )}
                  </tbody>
                </table>
              </section>

              <section className="section-block mb-5">
                <h2 className="section-heading text-xs md:text-sm font-extrabold text-white bg-teal-800 py-1.5 px-4 rounded-t flex items-center justify-between">
                  <span>III. النتائج ومقياس الفرز الأولي</span>
                </h2>
                <table className="doc-table w-full text-right text-xs md:text-sm border border-slate-300 border-t-0">
                  <tbody>
                    <tr>
                      <th className="cell-label w-[20%] bg-slate-50 font-bold border border-slate-300 py-2.5 px-3 text-slate-800">
                        معدل السنة التاسعة أساسي:
                      </th>
                      <td className="cell-value w-[30%] border border-slate-300 py-2.5 px-3 font-mono font-bold text-slate-950">
                        {data.grade_9_average ? `${data.grade_9_average} / 20` : '-'}
                      </td>
                      <th className="cell-label w-[20%] bg-slate-50 font-bold border border-slate-300 py-2.5 px-3 text-slate-800">
                        مجموع النقاط المحتسب:
                      </th>
                      <td className="cell-value w-[30%] border border-slate-300 py-2.5 px-3 font-mono font-black text-teal-800 text-sm md:text-base">
                        {scoreFormatted} نقطة
                      </td>
                    </tr>
                    <tr>
                      <th className="cell-label bg-slate-50 font-bold border border-slate-300 py-2.5 px-3 text-slate-800">
                        صيغة الفرز الأولي المعتمدة:
                      </th>
                      <td colSpan={3} className="cell-value border border-slate-300 py-2.5 px-3 text-slate-900 text-xs leading-relaxed">
                        معدل السنة التاسعة من التعليم الأساسي + 1 نقطة عن كل سنة دراسية تفوق المستوى الأدنى المطلوب (في حدود 4 سنوات كحد أقصى).
                      </td>
                    </tr>
                  </tbody>
                </table>
              </section>
            </>
          )}

          {/* ─── PROFILE 4: CHAUFFEUR (19 : سائق) ─── */}
          {isChauffeur && (
            <>
              <section className="section-block mb-5">
                <h2 className="section-heading text-xs md:text-sm font-extrabold text-white bg-teal-800 py-1.5 px-4 rounded-t flex items-center justify-between">
                  <span>II. المستوى الدراسي وبيانات رخصة السياقة</span>
                </h2>
                <table className="doc-table w-full text-right text-xs md:text-sm border border-slate-300 border-t-0">
                  <tbody>
                    <tr>
                      <th className="cell-label w-[20%] bg-slate-50 font-bold border border-slate-300 py-2.5 px-3 text-slate-800">
                        المستوى المصرح به:
                      </th>
                      <td colSpan={3} className="cell-value border border-slate-300 py-2.5 px-3 font-bold text-slate-950">
                        <span>{data.school_level || '-'}</span>
                        <span className="text-slate-500 font-normal mr-2 text-xs">
                          (الشروط: أدناه التاسعة أساسي بنجاح وأقصاه الرابعة ثانوي منهاة ودون نجاح)
                        </span>
                      </td>
                    </tr>
                    <tr>
                      <th className="cell-label bg-slate-50 font-bold border border-slate-300 py-2.5 px-3 text-slate-800">
                        صنف رخصة السياقة:
                      </th>
                      <td className="cell-value border border-slate-300 py-2.5 px-3 font-bold text-slate-950">
                        {data.driving_license_category || 'صنف ب (B)'}
                      </td>
                      <th className="cell-label bg-slate-50 font-bold border border-slate-300 py-2.5 px-3 text-slate-800">
                        تاريخ الحصول على الرخصة:
                      </th>
                      <td className="cell-value border border-slate-300 py-2.5 px-3 font-mono font-bold text-slate-950">
                        {data.driving_license_date || '-'}
                      </td>
                    </tr>
                    <tr>
                      <th className="cell-label bg-slate-50 font-bold border border-slate-300 py-2.5 px-3 text-slate-800">
                        شرط الأقدمية في السياقة:
                      </th>
                      <td colSpan={3} className="cell-value border border-slate-300 py-2.5 px-3 text-slate-900 font-semibold text-xs">
                        متحصل على رخصة السياقة منذ سنتين على الأقل بتاريخ آخر أجل لقبول الترشحات (شرط وجوبي).
                      </td>
                    </tr>
                  </tbody>
                </table>
              </section>

              <section className="section-block mb-5">
                <h2 className="section-heading text-xs md:text-sm font-extrabold text-white bg-teal-800 py-1.5 px-4 rounded-t flex items-center justify-between">
                  <span>III. النتائج ومقياس الفرز الأولي</span>
                </h2>
                <table className="doc-table w-full text-right text-xs md:text-sm border border-slate-300 border-t-0">
                  <tbody>
                    <tr>
                      <th className="cell-label w-[20%] bg-slate-50 font-bold border border-slate-300 py-2.5 px-3 text-slate-800">
                        معدل السنة التاسعة أساسي:
                      </th>
                      <td className="cell-value w-[30%] border border-slate-300 py-2.5 px-3 font-mono font-bold text-slate-950">
                        {data.grade_9_average ? `${data.grade_9_average} / 20` : '-'}
                      </td>
                      <th className="cell-label w-[20%] bg-slate-50 font-bold border border-slate-300 py-2.5 px-3 text-slate-800">
                        مجموع النقاط المحتسب:
                      </th>
                      <td className="cell-value w-[30%] border border-slate-300 py-2.5 px-3 font-mono font-black text-teal-800 text-sm md:text-base">
                        {scoreFormatted} نقطة
                      </td>
                    </tr>
                    <tr>
                      <th className="cell-label bg-slate-50 font-bold border border-slate-300 py-2.5 px-3 text-slate-800">
                        صيغة الفرز الأولي المعتمدة:
                      </th>
                      <td colSpan={3} className="cell-value border border-slate-300 py-2.5 px-3 text-slate-900 text-xs leading-relaxed">
                        معدل السنة التاسعة من التعليم الأساسي + 1 نقطة عن كل سنة دراسية تفوق المستوى الأدنى المطلوب.
                      </td>
                    </tr>
                  </tbody>
                </table>
              </section>
            </>
          )}

          {/* ─── PROFILE 5: NETTOYAGE (20 & 21 : عون تنظيف) ─── */}
          {isNettoyage && (
            <>
              <section className="section-block mb-5">
                <h2 className="section-heading text-xs md:text-sm font-extrabold text-white bg-teal-800 py-1.5 px-4 rounded-t flex items-center justify-between">
                  <span>II. المستوى الدراسي والتكوين</span>
                </h2>
                <table className="doc-table w-full text-right text-xs md:text-sm border border-slate-300 border-t-0">
                  <tbody>
                    <tr>
                      <th className="cell-label w-[20%] bg-slate-50 font-bold border border-slate-300 py-2.5 px-3 text-slate-800">
                        المستوى المصرح به:
                      </th>
                      <td colSpan={3} className="cell-value border border-slate-300 py-2.5 px-3 font-bold text-slate-950">
                        <span>{data.school_level || '-'}</span>
                        <span className="text-slate-500 font-normal mr-2 text-xs">
                          (الشروط: أدناه السادسة أساسي بنجاح وأقصاه التاسعة أساسي منهاة ودون نجاح)
                        </span>
                      </td>
                    </tr>
                    {(data.school_institution || data.end_school_year) && (
                      <tr>
                        <th className="cell-label bg-slate-50 font-bold border border-slate-300 py-2.5 px-3 text-slate-800">
                          المؤسسة التعليمية:
                        </th>
                        <td className="cell-value border border-slate-300 py-2.5 px-3 font-bold text-slate-950">
                          {data.school_institution || '-'}
                        </td>
                        <th className="cell-label bg-slate-50 font-bold border border-slate-300 py-2.5 px-3 text-slate-800">
                          سنة الانقطاع:
                        </th>
                        <td className="cell-value border border-slate-300 py-2.5 px-3 font-mono font-bold text-slate-950">
                          {data.end_school_year || '-'}
                        </td>
                      </tr>
                    )}
                  </tbody>
                </table>
              </section>

              <section className="section-block mb-5">
                <h2 className="section-heading text-xs md:text-sm font-extrabold text-white bg-teal-800 py-1.5 px-4 rounded-t flex items-center justify-between">
                  <span>III. النتائج ومقياس الفرز الأولي</span>
                </h2>
                <table className="doc-table w-full text-right text-xs md:text-sm border border-slate-300 border-t-0">
                  <tbody>
                    <tr>
                      <th className="cell-label w-[20%] bg-slate-50 font-bold border border-slate-300 py-2.5 px-3 text-slate-800">
                        معدل السنة السادسة أساسي:
                      </th>
                      <td className="cell-value w-[30%] border border-slate-300 py-2.5 px-3 font-mono font-bold text-slate-950">
                        {data.grade_6_average ? `${data.grade_6_average} / 20` : '-'}
                      </td>
                      <th className="cell-label w-[20%] bg-slate-50 font-bold border border-slate-300 py-2.5 px-3 text-slate-800">
                        مجموع النقاط المحتسب:
                      </th>
                      <td className="cell-value w-[30%] border border-slate-300 py-2.5 px-3 font-mono font-black text-teal-800 text-sm md:text-base">
                        {scoreFormatted} نقطة
                      </td>
                    </tr>
                    <tr>
                      <th className="cell-label bg-slate-50 font-bold border border-slate-300 py-2.5 px-3 text-slate-800">
                        صيغة الفرز الأولي المعتمدة:
                      </th>
                      <td colSpan={3} className="cell-value border border-slate-300 py-2.5 px-3 text-slate-900 text-xs leading-relaxed">
                        معدل السنة السادسة أساسي + 1 نقطة عن كل سنة دراسية تفوق المستوى الأدنى المطلوب (في حدود 3 سنوات كحد أقصى).
                      </td>
                    </tr>
                  </tbody>
                </table>
              </section>
            </>
          )}

          {/* ══════════════════════════════════════════════════════════════
              FOOTER: DECLARATION & SIGNATURE
             ══════════════════════════════════════════════════════════════ */}
          <footer className="footer-section mt-5 pt-2 flex-1 flex flex-col justify-between">
            <div>
              {/* Legal Declaration */}
              <div className="text-xs leading-relaxed text-slate-800 mb-2">
                <span className="font-extrabold text-slate-950">⚖️ تصريح بالشرف: </span>
                <span>أصرح بشرفي بصحة ودقة كافة البيانات والمعلومات المصرح بها أعلاه في هذه الاستمارة، وأتحمل كامل المسؤولية الإدارية والجزائية في صورة الإدلاء ببيانات خاطئة أو غير مطابقة للوثائق الرسمية.</span>
              </div>

              <div className="text-xs font-bold text-amber-800 mb-4">
                * ملاحظة: تُسحب هذه الاستمارة وتُوقَّع وتُرفق وجوباً بملف الترشح الورقي عند القبول في مرحلة الفرز الأولي.
              </div>

              {/* Signature Area (Left aligned with generous space below) */}
              <div className="w-full text-left pl-6 pt-2 pb-24 md:pb-36">
                <span className="font-bold text-slate-950 text-xs md:text-sm">
                  إمضاء المترشح(ة):
                </span>
              </div>
            </div>

            {/* Document extraction timestamp at bottom */}
            <div className="mt-auto pt-2 border-t border-slate-200 flex justify-between text-[10px] text-slate-500 font-mono">
              <span>منظومة الترشح للمناظرات الخارجية</span>
              <span>تاريخ وتوقيت التسجيل على الموقع: {data.created_at || new Date().toLocaleString('fr-FR')}</span>
            </div>
          </footer>

        </div>
      </div>

      {/* ── Screen Footer Signature ── */}
      <footer className="no-print max-w-4xl mx-auto text-center text-xs text-sky-600 font-medium py-4 pb-8" dir="ltr">
        © Powered by <span className="font-semibold text-sky-700">E..E.E. Bouzekri</span> - <span className="font-semibold text-sky-700">DSI-CNFCPP</span> August 2026
      </footer>

      {/* ══════════════════════════════════════════════════════════════
          PRINT & DISPLAY STYLES
         ══════════════════════════════════════════════════════════════ */}
      <style>{`
        @media print {
          * {
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
          }

          .no-print {
            display: none !important;
          }

          html, body {
            background: white !important;
            color: #0f172a !important;
            font-size: 13px !important;
            line-height: 1.5 !important;
            margin: 0 !important;
            padding: 0 !important;
          }

          @page {
            size: A4 portrait;
            margin: 10mm 12mm 10mm 12mm;
          }

          .sheet-card {
            border: none !important;
            box-shadow: none !important;
            padding: 0 !important;
            margin: 0 !important;
            width: 100% !important;
            min-height: 272mm !important;
            display: flex !important;
            flex-direction: column !important;
            justify-content: space-between !important;
          }

          .header-box {
            margin-bottom: 14px !important;
            padding-bottom: 8px !important;
          }

          .title-col h1 {
            font-size: 15px !important;
            line-height: 1.35 !important;
          }

          .title-col div {
            font-size: 12px !important;
          }

          .ministry-col {
            width: 210px !important;
            max-width: 220px !important;
            min-width: 190px !important;
            flex-shrink: 0 !important;
          }

          .title-col {
            flex: 1 1 auto !important;
            padding: 0 12px !important;
          }

          .logo-col {
            width: 160px !important;
            max-width: 160px !important;
            min-width: 130px !important;
            flex-shrink: 0 !important;
            display: flex !important;
            align-items: center !important;
            justify-content: flex-start !important;
            text-align: left !important;
          }

          .logo-col img {
            max-height: 52px !important;
            max-width: 155px !important;
            width: auto !important;
            height: auto !important;
            object-fit: contain !important;
            margin-right: auto !important;
            margin-left: 0 !important;
          }

          .top-cards-bar {
            margin-top: 12px !important;
            margin-bottom: 14px !important;
            gap: 10px !important;
          }

          .top-cards-bar > div {
            padding: 8px 12px !important;
            font-size: 13px !important;
          }

          .section-block {
            margin-bottom: 15px !important;
            page-break-inside: avoid !important;
            break-inside: avoid !important;
          }

          .section-heading {
            font-size: 13.5px !important;
            padding: 5px 12px !important;
            background-color: #115e59 !important;
            color: #ffffff !important;
            font-weight: 800 !important;
          }

          .doc-table {
            width: 100% !important;
            border-collapse: collapse !important;
            border: 1px solid #94a3b8 !important;
          }

          .doc-table th,
          .doc-table td {
            border: 1px solid #cbd5e1 !important;
            padding: 7px 12px !important;
            font-size: 12.5px !important;
            line-height: 1.45 !important;
          }

          .doc-table th {
            background-color: #f8fafc !important;
            font-weight: 700 !important;
            color: #1e293b !important;
          }

          .footer-section {
            margin-top: 16px !important;
            padding-top: 8px !important;
            flex: 1 1 auto !important;
            display: flex !important;
            flex-direction: column !important;
            justify-content: space-between !important;
            page-break-inside: avoid !important;
            break-inside: avoid !important;
          }
        }
      `}</style>
    </div>
  )
}

function trimDash(str: string): string {
  return str.replace(/^[\sـ-]+|[\sـ-]+$/g, '')
}
