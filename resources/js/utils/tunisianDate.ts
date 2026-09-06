/**
 * tunisianDate.ts
 *
 * Utilitaire pour formater les dates avec les noms de mois en arabe tunisien
 * (d'origine française), conformes au calendrier officiel tunisien.
 *
 * Noms adoptés :
 *   1 → جانفي    7  → جويلية
 *   2 → فيفري    8  → أوت
 *   3 → مارس     9  → سبتمبر
 *   4 → أفريل    10 → أكتوبر
 *   5 → ماي      11 → نوفمبر
 *   6 → جوان     12 → ديسمبر
 */

const MONTHS_TN: readonly string[] = [
    '', // index 0 vide pour que index 1 = جانفي
    'جانفي',
    'فيفري',
    'مارس',
    'أفريل',
    'ماي',
    'جوان',
    'جويلية',
    'أوت',
    'سبتمبر',
    'أكتوبر',
    'نوفمبر',
    'ديسمبر',
];

const WEEKDAYS_AR: readonly string[] = [
    'الأحد',
    'الاثنين',
    'الثلاثاء',
    'الأربعاء',
    'الخميس',
    'الجمعة',
    'السبت',
];

/**
 * Formater une date en arabe tunisien.
 *
 * Exemple : الاثنين 31 أوت 2026 على الساعة 23:59
 *
 * @param date - Date JS, chaîne ISO ou timestamp
 * @param includeTime - Inclure l'heure (défaut : true)
 */
export function formatTunisianDate(date: Date | string | number, includeTime = true): string {
    const d = date instanceof Date ? date : new Date(date);

    const day = WEEKDAYS_AR[d.getDay()];
    const dateNum = d.getDate();
    const month = MONTHS_TN[d.getMonth() + 1];
    const year = d.getFullYear();

    if (!includeTime) {
        return `${day} ${dateNum} ${month} ${year}`;
    }

    const hours = String(d.getHours()).padStart(2, '0');
    const minutes = String(d.getMinutes()).padStart(2, '0');

    return `${day} ${dateNum} ${month} ${year} على الساعة ${hours}:${minutes}`;
}

/**
 * Retourner uniquement le nom du mois en arabe tunisien.
 * @param monthIndex - Mois (1-12)
 */
export function getTunisianMonthName(monthIndex: number): string {
    return MONTHS_TN[monthIndex] ?? '';
}
