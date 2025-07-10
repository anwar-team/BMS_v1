<?php

namespace App\Support;

class DateHelper
{
    /**
     * تحويل السنة من الميلادي إلى الهجري (تقريبي)
     * 
     * @param int $gregorianYear
     * @return int
     */
    public static function gregorianToHijri(int $gregorianYear): int
    {
        // معادلة تقريبية للتحويل: السنة الهجرية = (السنة الميلادية - 622) * 1.030684
        return (int) round(($gregorianYear - 622) * 1.030684);
    }

    /**
     * تحويل السنة من الهجري إلى الميلادي (تقريبي)
     * 
     * @param int $hijriYear
     * @return int
     */
    public static function hijriToGregorian(int $hijriYear): int
    {
        // معادلة تقريبية للتحويل: السنة الميلادية = (السنة الهجرية / 1.030684) + 622
        return (int) round(($hijriYear / 1.030684) + 622);
    }

    /**
     * التحقق من صحة السنة الهجرية
     * 
     * @param int $year
     * @return bool
     */
    public static function isValidHijriYear(int $year): bool
    {
        // السنة الهجرية الأولى هي 1، والحالية تقريباً 1445
        return $year >= 1 && $year <= self::getCurrentHijriYear();
    }

    /**
     * التحقق من صحة السنة الميلادية
     * 
     * @param int $year
     * @return bool
     */
    public static function isValidGregorianYear(int $year): bool
    {
        return $year >= 1 && $year <= date('Y');
    }

    /**
     * الحصول على السنة الهجرية الحالية
     * 
     * @return int
     */
    public static function getCurrentHijriYear(): int
    {
        return self::gregorianToHijri((int) date('Y'));
    }

    /**
     * تنسيق عرض التاريخ حسب النوع
     * 
     * @param int $year
     * @param string $type
     * @return string
     */
    public static function formatYear(int $year, string $type): string
    {
        if ($type === 'hijri') {
            return $year . ' هـ';
        }
        return $year . ' م';
    }

    /**
     * التحقق من منطقية التاريخ (سنة الوفاة بعد سنة الميلاد)
     * 
     * @param int $birthYear
     * @param string $birthType
     * @param int|null $deathYear
     * @param string|null $deathType
     * @return bool
     */
    public static function isLogicalDateRange(int $birthYear, string $birthType, ?int $deathYear = null, ?string $deathType = null): bool
    {
        if (!$deathYear) {
            return true;
        }

        // تحويل كلا التاريخين إلى نفس النوع للمقارنة
        $birthYearForComparison = $birthType === 'hijri' ? self::hijriToGregorian($birthYear) : $birthYear;
        $deathYearForComparison = $deathType === 'hijri' ? self::hijriToGregorian($deathYear) : $deathYear;

        return $deathYearForComparison > $birthYearForComparison;
    }
}