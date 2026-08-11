import React from 'react'
import { Head } from '@inertiajs/react'

export default function Success({ data = {} }) {
  const personalRows = {
    'رمز المناظرة المزمع المشاركة فيها': data.position.concat(" - ", data.position_name),
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

  return (
    <div lang="ar" dir="rtl" className="bg-gray-100 min-h-screen">
      <Head title="تم استلام طلبكم بنجاح" />

      {/* Print Button */}
      <div className="text-center mb-6 no-print pt-10">
        <button
          onClick={() => window.print()}
          className="bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700 transition font-semibold"
        >
          🖨️ طباعة
        </button>
      </div>

      <div className="max-w-5xl text-gray-800 mx-auto px-4 pb-10">

        <div className="text-center goog mb-10">
          <h2 className="text-3xl font-bold text-gray-800">
            تم إرسال طلبكم بنجاح
          </h2>
          <p className="text-gray-500 mt-2">
            طباعة استمارة الترشح وامضائها لتضمينها بالملف الورقي
            في صورة قبول المترشح في الفرز الأولي.
          </p>
        </div>

        {/* Card */}
        <div className="bg-white shadow-md rounded-lg p-6 md:p-8">

          {/* Header matching official model template */}
          <div className="mb-6 pb-4 border-b border-gray-300">
            {/* Top Row: Ministry info (Right), Title (Center), Category Box (Left) */}
            <div className="flex flex-col md:flex-row items-center justify-between gap-4 mb-4">
              {/* Right: Ministry & Center Info */}
              <div className="text-right text-xs md:text-sm font-semibold leading-snug text-gray-900">
                {(data.header_text ? data.header_text.split('\n') : [
                  'الجمهورية التونسية',
                  'وزارة التشغيل والتكوين المهني',
                  'المركز الوطني للتكوين المستمر والترقية المهنية'
                ]).map((line: string, idx: number, arr: string[]) => (
                  <div key={idx} className={idx === arr.length - 1 ? 'font-bold' : ''}>
                    {line}
                  </div>
                ))}
              </div>

              {/* Center: Main Title */}
              <div className="text-center flex-1 my-2">
                <h1 className="text-xl md:text-2xl font-extrabold text-gray-900 leading-tight">
                  {data.contest_name || 'استمارة ترشح للمشاركة في المناظرة الخارجية'}
                </h1>
                {!data.contest_name && (
                  <h2 className="text-base md:text-lg font-bold text-gray-800 mt-1">
                    بعنوان سنتي 2025 و2026
                  </h2>
                )}
              </div>

              {/* Left: Position Category Box */}
              <div>
                <div className="border-2 border-gray-900 p-2 md:p-3 text-center text-xs md:text-sm font-bold text-gray-900 min-w-[150px] whitespace-pre-line leading-tight bg-white">
                  {(() => {
                    const pos = String(data.position || '');
                    const num = parseInt(pos, 10);
                    if (num >= 1 && num <= 15) {
                      return 'المهندسين والمحللين\nوالمتصرفين\nمن 1 إلى 15';
                    } else if (pos === '16') {
                      return 'ملحق إدارة\nمؤهل التقني السامي\n16';
                    } else if (['17', '18'].includes(pos)) {
                      return 'مستكتب إدارة\n17 و 18';
                    } else if (pos === '19') {
                      return 'سائق\n19';
                    } else if (['20', '21'].includes(pos)) {
                      return 'عون تنظيف\n20 و 21';
                    }
                    return data.position_name ? `${data.position_name}\n(${pos})` : `خطة ${pos}`;
                  })()}
                </div>
              </div>
            </div>

            {/* Second Row: Registration Number (Right) and Score (Left, if enabled) */}
            <div className="flex items-center justify-between mt-4">
              <div className="border-2 border-gray-900 px-4 py-1.5 font-bold text-gray-900 text-sm md:text-base bg-white">
                رقم التسجيل: <span className="font-mono">{data.id}</span>
              </div>
              {data.show_score !== false && (
                <div className="border-2 border-gray-900 px-4 py-1.5 font-bold text-gray-900 text-sm md:text-base bg-white">
                  مجموع النقاط: <span className="font-mono">{data.score}</span>
                </div>
              )}
            </div>
          </div>

          {/* Personal Info */}
          <h3 className="text-xl font-semibold text-gray-700 mb-4">
            I- الهوية والمعلومات الشخصية
          </h3>

          <table className="w-full text-right border border-gray-200 rounded-lg bg-white">
            <tbody className="divide-y divide-gray-200">
              {Object.entries(personalRows).map(([label, value]) => (
                <tr key={label} className="hover:bg-gray-50">
                  <th className="py-1 px-4 font-medium text-gray-700 w-1/3 bg-gray-100">
                    {label}
                  </th>
                  <td className="py-1 px-4 text-gray-800">
                    {value ?? ''}
                  </td>
                </tr>
              ))}
            </tbody>
          </table>

          {/* Education */}
          <h3 className="text-xl font-semibold text-gray-700 mt-5 mb-4">
            المستوى التعليمي
          </h3>

          <table className="w-full text-right border border-gray-200 rounded-lg bg-white">
            <tbody className="divide-y divide-gray-200">
              {data.degree && (
                <tr>
                  <th className="py-1 px-4 bg-gray-100">الشهادة العلمية</th>
                  <td className="py-1 px-4">{data.degree}</td>
                </tr>
              )}
              <tr>
                <th className="py-1 px-4 bg-gray-100">الاختصاص</th>
                <td className="py-1 px-4">{data.specialty}</td>
              </tr>
              <tr>
                <th className="py-1 px-4 bg-gray-100">سنة التخرج</th>
                <td className="py-1 px-4">{data.graduation_year}</td>
              </tr>
              <tr>
                <th className="py-1 px-4 bg-gray-100">قرار وتاريخ المعادلة</th>
                <td className="py-1 px-4">{data.equivalence_decision} - {data.equivalence_date}</td>
              </tr>
            </tbody>
          </table>

          {/* Results */}
          <h3 className="text-xl font-semibold text-gray-700 mt-5 mb-4">المعدلات المطلوبة</h3>

          <table className="w-full text-right border border-gray-200 rounded-lg bg-white">
            <tbody className="divide-y divide-gray-200">
              <tr>
                <th className="py-1 px-4 font-bold bg-gray-100">معدل البكالوريا</th>
                <td className="py-1 px-4">{data.bac_average}</td>
                <th className="py-1 px-4 font-bold bg-gray-100">معدل سنة التخرج</th>
                <td className="py-1 px-4">{data.grad_average}</td>
              </tr>
            </tbody>
          </table>

          {/* Signature */}
          <div className="mt-12 flex justify-start">
            <div className="w-1/2 border-2 border-dashed border-gray-400 p-6 text-center">
              <p className="text-gray-700 font-semibold mb-12">الإمضاء</p>
            </div>
          </div>

        </div>
      </div>

      {/* Print styles */}
      <style>{`
        @media print {
          .no-print, .goog {
            display: none !important;
          }

          body {
            font-size: 12px !important;
            background: white !important;
          }

          @page {
            margin: 10mm;
          }
        }
        th {
            font-weight:500;
        }
      `}</style>
    </div>
  )
}

