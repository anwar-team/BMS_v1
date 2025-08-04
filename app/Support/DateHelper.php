<?php

namespace App\Support;

class DateHelper
{
    /**
     * Get current Hijri year
     */
    public static function getCurrentHijriYear(): int
    {
        // تقريبي للسنة الهجرية الحالية
        // يمكن تحسينه باستخدام مكتبة متخصصة
        $gregorianYear = date('Y');
        return $gregorianYear - 579; // تقريب للتحويل من الميلادي للهجري
    }

    /**
     * Check if Hijri year is valid
     */
    public static function isValidHijriYear(int $year): bool
    {
        return $year >= 1 && $year <= self::getCurrentHijriYear();
    }

    /**
     * Check if Gregorian year is valid
     */
    public static function isValidGregorianYear(int $year): bool
    {
        return $year >= 1 && $year <= date('Y');
    }

    /**
     * Convert Gregorian year to Hijri (approximate)
     */
    public static function gregorianToHijri(int $gregorianYear): int
    {
        return $gregorianYear - 579;
    }

    /**
     * Convert Hijri year to Gregorian (approximate)
     */
    public static function hijriToGregorian(int $hijriYear): int
    {
        return $hijriYear + 579;
    }
}