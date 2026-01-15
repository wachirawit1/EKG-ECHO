<?php

namespace App\Helpers;

use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Log;

class HmsHelper
{
    /**
     * แปลงวันที่เป็นรูปแบบไทย
     */
    public static function formatThaiDate($date)
    {
        if (!$date) return '-';
        try {
            $dayTH = ['อาทิตย์', 'จันทร์', 'อังคาร', 'พุธ', 'พฤหัสบดี', 'ศุกร์', 'เสาร์'];
            $monthTH = [
                1 => 'มกราคม',
                'กุมภาพันธ์',
                'มีนาคม',
                'เมษายน',
                'พฤษภาคม',
                'มิถุนายน',
                'กรกฎาคม',
                'สิงหาคม',
                'กันยายน',
                'ตุลาคม',
                'พฤศจิกายน',
                'ธันวาคม',
            ];

            $carbon = Carbon::parse($date);
            $day = $dayTH[$carbon->dayOfWeek];
            $dayNum = $carbon->day;
            $month = $monthTH[$carbon->month];
            $year = $carbon->year + 543;

            return "วัน{$day}ที่ {$dayNum} {$month} {$year}";
        } catch (Exception $e) {
            Log::error('Error formatting Thai date: ' . $e->getMessage());
            return $date;
        }
    }

    /**
     * คำนวณอายุจากวันเกิด (รองรับรูปแบบ yyyymmdd พ.ศ.)
     */
    public static function calculateAge($birthDay)
    {
        if (!$birthDay || strlen($birthDay) !== 8) return '-';

        try {
            $year = (int)substr($birthDay, 0, 4) - 543;
            $month = (int)substr($birthDay, 4, 2);
            $day = (int)substr($birthDay, 6, 2);

            $birthDate = Carbon::createFromDate($year, $month, $day);
            return $birthDate->age . ' ปี';
        } catch (Exception $e) {
            return '-';
        }
    }

    /**
     * สร้างชื่อย่อโรงพยาบาล
     */
    public static function generateHospitalAbbreviation($hospitalName)
    {
        if (!$hospitalName) return 'UNK';
        $words = preg_replace('/^(โรงพยาบาล)/u', '', $hospitalName);
        $words = preg_split('/\s+/u', trim($words));

        $abbr = '';
        foreach ($words as $word) {
            $abbr .= mb_substr($word, 0, 1, "UTF-8");
        }

        return strtoupper($abbr);
    }
}
