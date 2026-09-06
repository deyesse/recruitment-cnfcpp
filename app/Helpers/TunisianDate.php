<?php

namespace App\Helpers;

use Carbon\Carbon;

/**
 * TunisianDate — Helper pour formater les dates en arabe tunisien.
 *
 * Les noms de mois adoptés en Tunisie (d'origine française) diffèrent
 * des noms arabes standard :
 *   Standard  →  Tunisien
 *   يناير     →  جانفي
 *   فبراير    →  فيفري
 *   مارس      →  مارس      (identique)
 *   أبريل     →  أفريل
 *   مايو      →  ماي
 *   يونيو     →  جوان
 *   يوليو     →  جويلية
 *   أغسطس     →  أوت
 *   سبتمبر    →  سبتمبر    (identique)
 *   أكتوبر    →  أكتوبر    (identique)
 *   نوفمبر    →  نوفمبر    (identique)
 *   ديسمبر    →  ديسمبر    (identique)
 */
class TunisianDate
{
    /** Mappage numéro de mois (1-12) → nom tunisien */
    public const MONTHS = [
        1  => 'جانفي',
        2  => 'فيفري',
        3  => 'مارس',
        4  => 'أفريل',
        5  => 'ماي',
        6  => 'جوان',
        7  => 'جويلية',
        8  => 'أوت',
        9  => 'سبتمبر',
        10 => 'أكتوبر',
        11 => 'نوفمبر',
        12 => 'ديسمبر',
    ];

    /** Noms des jours de la semaine en arabe (dimanche = 0) */
    public const WEEKDAYS = [
        0 => 'الأحد',
        1 => 'الاثنين',
        2 => 'الثلاثاء',
        3 => 'الأربعاء',
        4 => 'الخميس',
        5 => 'الجمعة',
        6 => 'السبت',
    ];

    /**
     * Formater une date Carbon au format tunisien.
     *
     * Exemple de sortie : الاثنين 31 أوت 2026 على الساعة 23:59
     *
     * @param  Carbon|string  $date
     * @param  string  $format  Supporte les jetons : {day}, {date}, {month}, {year}, {time}
     * @return string
     */
    public static function format(Carbon|string $date, string $format = '{day} {date} {month} {year} على الساعة {time}'): string
    {
        $carbon = $date instanceof Carbon ? $date : Carbon::parse($date);

        return strtr($format, [
            '{day}'   => self::WEEKDAYS[$carbon->dayOfWeek],
            '{date}'  => $carbon->day,
            '{month}' => self::MONTHS[$carbon->month],
            '{year}'  => $carbon->year,
            '{time}'  => $carbon->format('H:i'),
        ]);
    }

    /**
     * Remplacer uniquement le nom du mois standard arabe dans une chaîne
     * déjà formée par Carbon::translatedFormat('ar').
     */
    public static function fixMonthName(string $formatted): string
    {
        $map = [
            'يناير'  => 'جانفي',
            'فبراير' => 'فيفري',
            'مارس'   => 'مارس',
            'أبريل'  => 'أفريل',
            'مايو'   => 'ماي',
            'يونيو'  => 'جوان',
            'يوليو'  => 'جويلية',
            'أغسطس'  => 'أوت',
            'سبتمبر' => 'سبتمبر',
            'أكتوبر' => 'أكتوبر',
            'نوفمبر' => 'نوفمبر',
            'ديسمبر' => 'ديسمبر',
        ];

        return strtr($formatted, $map);
    }
}
