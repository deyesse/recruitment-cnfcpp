import React from 'react'
import { Head } from '@inertiajs/react'

export default function Success({ data = {} as any }) {
  const posType: string = data.position_type ?? 'cadre'
  const baseAvgField: string = data.base_average_field ?? 'bac_average'

  const isCadreOrTechnicien = ['cadre', 'technicien'].includes(posType)
  const isSchoolLevel = ['commis', 'chauffeur', 'nettoyage'].includes(posType)
  const isChauffeur = posType === 'chauffeur'

  /* ── label de l'average de base selon le champ configuré ── */
  const baseAvgLabel: Record<string, string> = {
    bac_average:    'معدل البكالوريا',
    btp_average:    'معدل شهادة التقني السامي (BTP)',
    grade_9_average: 'معدل السنة التاسعة أساسي',
    grade_6_average: 'معدل السنة السادسة أساسي',
  }
  const baseAvgValue = {
    bac_average:    data.bac_average,
    btp_average:    data.btp_average,
    grade_9_average: data.grade_9_average,
    grade_6_average: data.grade_6_average,
  }[baseAvgField] ?? '-'

  const personalRows: Record<string, string> = {
    'رمز المناظرة المزمع المشاركة فيها': `${data.position} - ${data.position_name ?? ''}`,
    'الاسم واللقب': data.name,
    'الجنس': data.gender,
    'تاريخ الولادة': data.birth_date,
    'العنوان الحالي': data.address,
    'المعتمدية': data.city,
    'الولاية': data.governorate,
    'الترقيم البريدي': data.postal_code,
    'رقم بطاقة التعريف الوطنية': data.cin,
    'تاريخ إصدار بطاقة التعريف الوطنية': data.cin_date,
    'رقم الهاتف الجوال': data.tel,
    'البريد الإلكتروني': data.email,
  }

  /* ── Catégory box text ── */
  const categoryBox = (() => {
    const pos = String(data.position || '')
    const num = parseInt(pos, 10)
    const name = data.position_name || ''

    if (num >= 1 && num <= 15) {
      return {
        title: name || 'المهندسين والمحللين والمتصرفين',
        ref: pos || 'من 1 إلى 15',
      }
    }
    if (pos === '16') {
      return {
        title: name || 'ملحق إدارة\nمؤهل التقني السامي',
        ref: '16',
      }
    }
    if (['17', '18'].includes(pos)) {
      return {
        title: name || 'مستكتب إدارة',
        ref: pos || '17 و 18',
      }
    }
    if (pos === '19') {
      return {
        title: name || 'سائق',
        ref: '19',
      }
    }
    if (['20', '21'].includes(pos)) {
      return {
        title: name || 'عون تنظيف',
        ref: pos || '20 و 21',
      }
    }
    return {
      title: name || `خطة ${pos}`,
      ref: pos,
    }
  })()

  return (
    <div lang="ar" dir="rtl" className="bg-gray-100 min-h-screen">
      <Head title="تم استلام طلبكم بنجاح" />

      {/* ── زر الطباعة ── */}
      <div className="text-center mb-6 no-print pt-10">
        <button
          onClick={() => window.print()}
          className="bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700 transition font-semibold"
        >
          🖨️ طباعة / حفظ PDF
        </button>
      </div>

      <div className="max-w-5xl text-gray-800 mx-auto px-4 pb-10">

        <div className="text-center goog mb-10">
          <h2 className="text-3xl font-bold text-gray-800">تم إرسال طلبكم بنجاح</h2>
          <p className="text-gray-500 mt-2">
            طباعة استمارة الترشح وامضائها لتضمينها بالملف الورقي في صورة قبول المترشح في الفرز الأولي.
          </p>
        </div>

        {/* ══ Card ══ */}
        <div className="bg-white shadow-md rounded-lg p-6 md:p-8 print-card">

          {/* ── Header ── */}
          <div className="header-section mb-6 pb-4 border-b border-gray-300">

            {/* Row 1 : Ministry | Title | Box */}
            <div className="header-top">
              {/* Ministry */}
              <div className="ministry-info text-right text-xs font-semibold leading-snug text-gray-900">
                {(data.header_text ? data.header_text.split('\n') : [
                  'الجمهورية التونسية',
                  'وزارة التشغيل والتكوين المهني',
                  'المركز الوطني للتكوين المستمر والترقية المهنية',
                ]).map((line: string, idx: number, arr: string[]) => (
                  <div key={idx} className={idx === arr.length - 1 ? 'font-bold' : ''}>{line}</div>
                ))}
              </div>

              {/* Title */}
              <div className="contest-title text-center">
                <h1 className="text-xl font-extrabold text-gray-900 leading-tight">
                  {data.contest_name || 'استمارة ترشح للمشاركة في المناظرة الخارجية'}
                </h1>
                {!data.contest_name && (
                  <h2 className="text-base font-bold text-gray-800 mt-1">بعنوان سنتي 2025 و2026</h2>
                )}
              </div>

              {/* Category Box */}
              <div className="category-box-wrapper">
                <div className="category-box border-2 border-gray-900 p-2 md:p-3 text-center text-gray-900 min-w-[130px] bg-white">
                  <div className="category-box-title text-xs md:text-sm font-semibold text-gray-800 leading-tight whitespace-pre-line">
                    {categoryBox.title}
                  </div>
                  <div className="category-box-ref text-lg md:text-xl font-black text-gray-900 mt-1 leading-tight tracking-wide">
                    {categoryBox.ref}
                  </div>
                </div>
              </div>
            </div>

            {/* Row 2 : Registration | Score */}
            <div className="header-bottom mt-4">
              <div className="reg-box border-2 border-gray-900 px-4 py-1.5 font-bold text-gray-900 text-sm bg-white inline-block">
                رقم التسجيل: <span className="font-mono">{data.id}</span>
              </div>
              {data.show_score !== false && (
                <div className="score-box border-2 border-gray-900 px-4 py-1.5 font-bold text-gray-900 text-sm bg-white inline-block">
                  مجموع النقاط: <span className="font-mono">{data.score}</span>
                </div>
              )}
            </div>
          </div>

          {/* ══ I - Informations personnelles ══ */}
          <h3 className="section-title text-xl font-semibold text-gray-700 mb-3">
            I- الهوية والمعلومات الشخصية
          </h3>
          <table className="info-table w-full text-right border border-gray-200 bg-white mb-4">
            <tbody>
              {Object.entries(personalRows).map(([label, value]) => (
                <tr key={label}>
                  <th className="py-1 px-3 bg-gray-100 w-2/5">{label}</th>
                  <td className="py-1 px-3">
                    {label === 'رمز المناظرة المزمع المشاركة فيها' ? (
                      <span>
                        <strong className="font-black text-base md:text-lg text-gray-900">{data.position}</strong>
                        {data.position_name && (
                          <span className="font-medium text-gray-700"> - {data.position_name}</span>
                        )}
                      </span>
                    ) : (
                      value ?? ''
                    )}
                  </td>
                </tr>
              ))}
            </tbody>
          </table>

          {/* ══ II - Cadre / Technicien ══ */}
          {isCadreOrTechnicien && (
            <>
              <h3 className="section-title text-xl font-semibold text-gray-700 mt-5 mb-3">
                II- المستوى التعليمي
              </h3>
              <table className="info-table w-full text-right border border-gray-200 bg-white mb-4">
                <tbody>
                  {data.degree && (
                    <tr>
                      <th className="py-1 px-3 bg-gray-100 w-2/5">الشهادة العلمية</th>
                      <td className="py-1 px-3">{data.degree}</td>
                    </tr>
                  )}
                  <tr>
                    <th className="py-1 px-3 bg-gray-100 w-2/5">الاختصاص</th>
                    <td className="py-1 px-3">{data.specialty ?? '-'}</td>
                  </tr>
                  <tr>
                    <th className="py-1 px-3 bg-gray-100 w-2/5">سنة التخرج</th>
                    <td className="py-1 px-3">{data.graduation_year ?? '-'}</td>
                  </tr>
                  <tr>
                    <th className="py-1 px-3 bg-gray-100 w-2/5">قرار وتاريخ المعادلة</th>
                    <td className="py-1 px-3">
                      {[data.equivalence_decision, data.equivalence_date].filter(Boolean).join(' - ') || '-'}
                    </td>
                  </tr>
                </tbody>
              </table>

              <h3 className="section-title text-xl font-semibold text-gray-700 mt-5 mb-3">
                III- المعدلات المطلوبة
              </h3>
              <table className="info-table w-full text-right border border-gray-200 bg-white mb-4">
                <tbody>
                  <tr>
                    <th className="py-1 px-3 bg-gray-100 w-2/5">{baseAvgLabel[baseAvgField] ?? 'المعدل الأساسي'}</th>
                    <td className="py-1 px-3">{baseAvgValue}</td>
                    <th className="py-1 px-3 bg-gray-100 w-1/5">معدل سنة التخرج</th>
                    <td className="py-1 px-3">{data.grad_average ?? '-'}</td>
                  </tr>
                </tbody>
              </table>
            </>
          )}

          {/* ══ II - Commis / Chauffeur / Nettoyage ══ */}
          {isSchoolLevel && (
            <>
              <h3 className="section-title text-xl font-semibold text-gray-700 mt-5 mb-3">
                II- المستوى الدراسي
              </h3>
              <table className="info-table w-full text-right border border-gray-200 bg-white mb-4">
                <tbody>
                  <tr>
                    <th className="py-1 px-3 bg-gray-100 w-2/5">المستوى الدراسي</th>
                    <td className="py-1 px-3">{data.school_level ?? '-'}</td>
                  </tr>
                  <tr>
                    <th className="py-1 px-3 bg-gray-100 w-2/5">{baseAvgLabel[baseAvgField] ?? 'المعدل الأساسي'}</th>
                    <td className="py-1 px-3">{baseAvgValue}</td>
                  </tr>
                  {isChauffeur && (
                    <>
                      <tr>
                        <th className="py-1 px-3 bg-gray-100 w-2/5">صنف رخصة القيادة</th>
                        <td className="py-1 px-3">{data.driving_license_category ?? '-'}</td>
                      </tr>
                      <tr>
                        <th className="py-1 px-3 bg-gray-100 w-2/5">تاريخ رخصة القيادة</th>
                        <td className="py-1 px-3">{data.driving_license_date ?? '-'}</td>
                      </tr>
                    </>
                  )}
                </tbody>
              </table>
            </>
          )}

          {/* ── Signature ── */}
          <div className="signature-area mt-10">
            <div className="border-2 border-dashed border-gray-400 p-6 text-center" style={{ width: '45%' }}>
              <p className="text-gray-700 font-semibold mb-12">الإمضاء</p>
            </div>
          </div>

        </div>{/* end card */}
      </div>

      {/* ══ Styles ══ */}
      <style>{`
        /* ── Screen layout ── */
        .header-top {
          display: flex;
          flex-direction: row;
          align-items: flex-start;
          justify-content: space-between;
          gap: 16px;
          margin-bottom: 12px;
        }
        .contest-title { flex: 1; text-align: center; }
        .ministry-info { text-align: right; min-width: 160px; }
        .category-box-wrapper { min-width: 140px; }
        .header-bottom {
          display: flex;
          justify-content: space-between;
          align-items: center;
        }
        .info-table tbody tr { border-bottom: 1px solid #e5e7eb; }
        .info-table th { font-weight: 500; background: #f3f4f6; }
        .section-title { margin-top: 20px; }

        /* ── Print / PDF ── */
        @media print {
          * {
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
          }

          .no-print, .goog {
            display: none !important;
          }

          body {
            font-size: 11px !important;
            background: white !important;
            margin: 0;
          }

          @page {
            size: A4 portrait;
            margin: 12mm 10mm;
          }

          .print-card {
            box-shadow: none !important;
            border-radius: 0 !important;
            padding: 0 !important;
          }

          .header-top {
            display: flex !important;
            flex-direction: row !important;
            justify-content: space-between !important;
            align-items: flex-start !important;
            gap: 8px !important;
          }

          .header-bottom {
            display: flex !important;
            justify-content: space-between !important;
          }

          .info-table {
            width: 100% !important;
            border-collapse: collapse !important;
            page-break-inside: avoid;
          }

          .info-table th,
          .info-table td {
            padding: 3px 6px !important;
            border: 1px solid #d1d5db !important;
            background: transparent;
          }

          .info-table th {
            background-color: #f3f4f6 !important;
          }

          .section-title {
            font-size: 13px !important;
            margin-top: 10px !important;
            margin-bottom: 4px !important;
          }

          .category-box {
            min-width: 110px !important;
            border: 2px solid black !important;
            padding: 3px !important;
          }

          .category-box-title {
            font-size: 10px !important;
            font-weight: 600 !important;
          }

          .category-box-ref {
            font-size: 16px !important;
            font-weight: 900 !important;
          }

          .reg-box, .score-box {
            border: 2px solid black !important;
            font-size: 11px !important;
          }

          .signature-area {
            margin-top: 20px !important;
          }
        }
      `}</style>
    </div>
  )
}
