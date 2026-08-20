import { Head, useForm, usePage } from '@inertiajs/react';
import { RecruitmentForm } from '@/components/recruitment-form';

export default function Welcome({
    deadline,
    currentlyRecruiting,
    degrees,
    positions,
    contestName,
    showScore = true,
    isTestMode = false,
    isTestUnlocked = false,
}: {
    deadline: string;
    currentlyRecruiting: boolean;
    degrees?: any;
    positions?: any;
    contestName?: string;
    showScore?: boolean;
    isTestMode?: boolean;
    isTestUnlocked?: boolean;
}) {
    const { footerText } = usePage().props as { footerText?: string };
    const { data, setData, post, processing, errors } = useForm({
        test_code: '',
    });

    const handleUnlock = (e: React.FormEvent) => {
        e.preventDefault();
        post('/unlock-test-mode');
    };

    return (
        <>
            <Head title={contestName || 'استمارة الترشح'}>
                <link rel="preconnect" href="https://fonts.bunny.net"/>
                <link href="https://fonts.bunny.net/css?family=tajawal:200,300,400,500,700,800,900" rel="stylesheet" />
            </Head>
            <div
                className="flex min-h-screen flex-col items-center bg-[#FDFDFC] p-6 text-[#1b1b18] lg:justify-center lg:p-8" dir="rtl">
                
                {currentlyRecruiting ? (
                    isTestMode && !isTestUnlocked ? (
                        /* Test Mode Lock Card */
                        <div className="w-full max-w-md bg-white rounded-2xl shadow-xl border border-amber-200 p-8 text-center my-auto">
                            <div className="mx-auto w-16 h-16 bg-amber-100 text-amber-700 rounded-full flex items-center justify-center text-3xl mb-4 shadow-sm">
                                🧪
                            </div>
                            <h2 className="text-2xl font-bold text-gray-900 mb-2">
                                وضع الاختبار مفعّل (Mode Test)
                            </h2>
                            <p className="text-sm text-gray-600 mb-6 leading-relaxed">
                                هذه المنظومة حالياً في وضع التجربة والتدقيق قبل الإطلاق الرسمي. لإمكانية الوصول وإيداع مطالب تجريبية، يرجى إدخال رمز الدخول المطلوب.
                            </p>

                            <form onSubmit={handleUnlock} className="space-y-4 text-right">
                                <div>
                                    <label className="block text-sm font-medium text-gray-700 mb-1">
                                        رمز الدخول للاختبار (Code de Test) <span className="text-red-500">*</span>
                                    </label>
                                    <input
                                        type="password"
                                        value={data.test_code}
                                        onChange={(e) => setData('test_code', e.target.value)}
                                        placeholder="أدخل رمز الاختبار هنا..."
                                        className="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition text-center font-mono text-lg tracking-wider"
                                        required
                                    />
                                    {errors.test_code && (
                                        <p className="text-red-600 text-xs mt-1.5 font-semibold text-center">
                                            {errors.test_code}
                                        </p>
                                    )}
                                </div>

                                <button
                                    type="submit"
                                    disabled={processing}
                                    className="w-full bg-amber-600 hover:bg-amber-700 text-white font-bold py-3 rounded-lg shadow-md transition disabled:opacity-50 flex items-center justify-center gap-2"
                                >
                                    {processing ? 'جاري التحقق...' : '🔑 تأكيد الرمز والدخول'}
                                </button>
                            </form>
                        </div>
                    ) : (
                        /* Normal or Unlocked Test Form */
                        <div className="w-full max-w-5xl">
                            {isTestMode && isTestUnlocked && (
                                <div className="bg-amber-50 border-r-4 border-amber-500 p-4 mb-6 rounded-l-lg shadow-sm flex items-center justify-between">
                                    <div className="flex items-center gap-3">
                                        <span className="text-2xl">🧪</span>
                                        <div>
                                            <h4 className="font-bold text-amber-900 text-sm">أنت تفتح الموقع في وضع الاختبار التجريبي</h4>
                                            <p className="text-xs text-amber-700">المطالب المودعة حالياً هي مطالب تجريبية وافتراضية للتدقيق.</p>
                                        </div>
                                    </div>
                                    <span className="bg-amber-200 text-amber-800 text-xs px-3 py-1 rounded-full font-bold">Mode Test</span>
                                </div>
                            )}

                            <RecruitmentForm
                                positions={positions}
                                deadlineDate={deadline}
                                degrees={degrees}
                                contestName={contestName}
                                showScore={showScore}
                            />
                        </div>
                    )
                ) : (
                    <div className={"font-mono text-lg"}>لا توجد اي مناظرة عمل في الوقت الحالي</div>
                )}

                <footer
                    className="mt-8 text-center text-xs text-sky-600 font-medium py-4 border-t border-slate-200/60 w-full max-w-5xl"
                    dir="ltr"
                    dangerouslySetInnerHTML={{
                        __html: footerText || '© Powered by <span class="font-semibold text-sky-700">E..E.E. Bouzekri</span> - <span class="font-semibold text-sky-700">DSI-CNFCPP</span> August 2026'
                    }}
                />
            </div>
        </>
    );
}
