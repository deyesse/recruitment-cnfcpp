import React, { useState } from 'react';
import { Head, Form, usePage } from '@inertiajs/react';
import { Printer, ArrowRight, AlertCircle, Calendar, Phone, CreditCard } from 'lucide-react';

export default function Reprint() {
    const { errors, footerText } = usePage().props as any;

    const [data, setData] = useState({
        cin: '',
        tel: '',
        registration_date: '',
    });

    const handleChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        const { name, value } = e.target;

        // Digits-only check for CIN and Phone
        if (['cin', 'tel'].includes(name)) {
            const clean = value.replace(/\D/g, '').slice(0, 8);
            setData(prev => ({ ...prev, [name]: clean }));
            return;
        }

        setData(prev => ({ ...prev, [name]: value }));
    };

    return (
        <div lang="ar" dir="rtl" className="min-h-screen bg-slate-50 flex flex-col justify-center py-12 px-4 sm:px-6 lg:px-8 font-['Tajawal',sans-serif]">
            <Head title="إعادة طباعة استمارة الترشح">
                <link rel="preconnect" href="https://fonts.bunny.net" />
                <link href="https://fonts.bunny.net/css?family=tajawal:400,500,700,800,900" rel="stylesheet" />
            </Head>

            <div className="sm:mx-auto sm:w-full sm:max-w-xl">
                {/* Header Logo/Icon */}
                <div className="flex justify-center mb-4">
                    <div className="h-16 w-16 bg-teal-700 text-white rounded-2xl flex items-center justify-center shadow-lg shadow-teal-700/30">
                        <Printer size={32} />
                    </div>
                </div>

                <h2 className="text-center text-3xl font-black text-slate-900 leading-tight">
                    إعادة طباعة استمارة الترشح
                </h2>
                <p className="mt-2 text-center text-sm font-medium text-slate-600">
                    خاص بالمترشحين المسجلين سابقاً والراغبين في استخراج استمارة الترشح الخاصة بهم
                </p>
            </div>

            <div className="mt-8 sm:mx-auto sm:w-full sm:max-w-xl">
                <div className="bg-white py-8 px-6 shadow-xl shadow-slate-200/50 rounded-2xl border border-slate-200/80 sm:px-10">

                    {/* Error Flash Message */}
                    {Object.keys(errors).length > 0 && (
                        <div className="mb-6 rounded-xl border border-red-200 bg-red-50 p-4 text-red-800 text-sm font-bold flex items-start gap-3">
                            <AlertCircle className="w-5 h-5 text-red-600 shrink-0 mt-0.5" />
                            <div className="space-y-1">
                                {Object.entries(errors).map(([key, error]) => (
                                    <p key={key}>{error as string}</p>
                                ))}
                            </div>
                        </div>
                    )}

                    <Form action="/reprint" method="POST" className="space-y-6">
                        {/* Field 1: CIN */}
                        <div>
                            <label htmlFor="cin" className="block text-sm font-bold text-slate-700 mb-1.5">
                                رقم بطاقة التعريف الوطنية <span className="text-red-500">*</span>
                            </label>
                            <div className="relative rounded-xl shadow-sm">
                                <div className="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-slate-400">
                                    <CreditCard size={18} />
                                </div>
                                <input
                                    id="cin"
                                    name="cin"
                                    type="tel"
                                    required
                                    maxLength={8}
                                    value={data.cin}
                                    onChange={handleChange}
                                    placeholder="XXXXXXXX"
                                    className="block w-full pr-10 pl-3 py-3 border border-slate-300 rounded-xl text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-teal-600 focus:border-teal-600 font-mono tracking-widest text-lg"
                                    dir="ltr"
                                />
                            </div>
                            <span className="text-[11px] text-slate-500 mt-1 block">يتكون من 8 أرقام</span>
                        </div>

                        {/* Field 2: Phone */}
                        <div>
                            <label htmlFor="tel" className="block text-sm font-bold text-slate-700 mb-1.5">
                                رقم الهاتف الجوال <span className="text-red-500">*</span>
                            </label>
                            <div className="relative rounded-xl shadow-sm">
                                <div className="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-slate-400">
                                    <Phone size={18} />
                                </div>
                                <input
                                    id="tel"
                                    name="tel"
                                    type="tel"
                                    required
                                    maxLength={8}
                                    value={data.tel}
                                    onChange={handleChange}
                                    placeholder="XXXXXXXX"
                                    className="block w-full pr-10 pl-3 py-3 border border-slate-300 rounded-xl text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-teal-600 focus:border-teal-600 font-mono tracking-widest text-lg"
                                    dir="ltr"
                                />
                            </div>
                            <span className="text-[11px] text-slate-500 mt-1 block">رقم الهاتف المسجل عند الترشح</span>
                        </div>

                        {/* Field 3: Registration Date (French format JJ/MM/AAAA) */}
                        <div>
                            <label htmlFor="registration_date" className="block text-sm font-bold text-slate-700 mb-1.5">
                                تاريخ التسجيل <span className="text-red-500">*</span>
                            </label>
                            <div className="relative rounded-xl shadow-sm flex items-center">
                                <div className="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-slate-400">
                                    <Calendar size={18} />
                                </div>
                                <input
                                    id="registration_date"
                                    name="registration_date"
                                    type="text"
                                    required
                                    value={data.registration_date}
                                    onChange={(e) => {
                                        let val = e.target.value.replace(/[^\d/]/g, '');
                                        // Auto slash formatting for DD/MM/YYYY
                                        if (val.length === 2 && !val.includes('/')) {
                                            val = val + '/';
                                        } else if (val.length === 5 && val.split('/').length === 2) {
                                            val = val + '/';
                                        }
                                        if (val.length <= 10) {
                                            setData(prev => ({ ...prev, registration_date: val }));
                                        }
                                    }}
                                    placeholder="JJ/MM/AAAA"
                                    className="block w-full pr-10 pl-10 py-3 border border-slate-300 rounded-xl text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-teal-600 focus:border-teal-600 font-mono text-center tracking-wider text-base"
                                    dir="ltr"
                                />
                                <input
                                    type="date"
                                    tabIndex={-1}
                                    onChange={(e) => {
                                        if (e.target.value) {
                                            const [y, m, d] = e.target.value.split('-');
                                            setData(prev => ({ ...prev, registration_date: `${d}/${m}/${y}` }));
                                        }
                                    }}
                                    className="absolute left-3 opacity-0 w-6 h-6 cursor-pointer"
                                    title="اختر التاريخ من التقويم"
                                />
                            </div>
                            <span className="text-[11px] text-slate-500 mt-1 block">التاريخ بالشكل الفرنسي: اليوم/الشهر/السنة (مثال: 09/08/2026)</span>
                        </div>

                        {/* Submit Button */}
                        <div>
                            <button
                                type="submit"
                                className="w-full flex justify-center items-center gap-2 py-3.5 px-4 rounded-xl shadow-md text-base font-bold text-white bg-teal-700 hover:bg-teal-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-600 transition-all cursor-pointer"
                            >
                                <Printer size={20} />
                                <span>البحث وعرض الاستمارة للطباعة</span>
                            </button>
                        </div>
                    </Form>

                    {/* Back link */}
                    <div className="mt-6 text-center pt-4 border-t border-slate-100">
                        <a
                            href="/"
                            className="inline-flex items-center gap-2 text-sm font-bold text-teal-700 hover:text-teal-900 transition-colors"
                        >
                            <span>العودة لصفحة التسجيل الرئيسية</span>
                            <ArrowRight size={16} />
                        </a>
                    </div>
                </div>
            </div>

            <footer
                className="mt-8 text-center text-xs text-sky-600 font-medium py-4 border-t border-slate-200/60 sm:mx-auto sm:w-full sm:max-w-xl"
                dir="ltr"
                dangerouslySetInnerHTML={{
                    __html: footerText || '© Powered by <span class="font-semibold text-sky-700">E..E.E. Bouzekri</span> - <span class="font-semibold text-sky-700">DSI-CNFCPP</span> August 2026'
                }}
            />
        </div>
    );
}
