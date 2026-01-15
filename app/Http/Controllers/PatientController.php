<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PatientController extends Controller
{
    public function index()
    {
        return view('patient.search');
    }

    public function patientSearch(Request $request)
    {
        $query = trim($request->input('query'));

        if (!$query || mb_strlen($query) < 2) {
            return response()->json([
                'error' => 'กรุณาป้อนคำค้นหาที่มีความยาวอย่างน้อย 2 ตัวอักษร'
            ], 400);
        }

        // 1. ค้นหาจากฐานข้อมูลหลักโรงพยาบาล (SQL Server)
        $hisPatients = DB::connection('sqlsrv')
            ->table('PATIENT as p')
            ->leftJoin('OPD_H as o', function ($join) {
                $join->on('p.hn', '=', 'o.hn')
                    ->whereRaw('o.regNo = (SELECT MAX(regNo) FROM OPD_H WHERE hn = p.hn)');
            })
            ->leftJoin('PatSS as s', 'p.hn', '=', 's.hn')
            ->leftJoin('PTITLE as t', 'p.titleCode', '=', 't.titleCode')
            ->where(function ($q) use ($query) {
                $q->where('p.hn', 'LIKE', "%{$query}%")
                    ->orWhere(DB::raw("p.firstName + ' ' + p.lastName"), 'LIKE', "%{$query}%");
            })
            ->select(
                'p.hn',
                't.titleName as title_name',
                'p.firstName as firstName',
                'p.lastName as lastName',
                'p.birthDay',
                'p.sex',
                'o.regNo',
                's.CardID'
            )
            ->limit(10)
            ->get()
            ->map(function ($item) {
                $item->source = 'HIS';
                $item->hospital_name = 'โรงพยาบาลหลัก';
                $item->birthDayFormatted = \App\Helpers\DateHelper::formatThaiDate($item->birthDay);
                return $item;
            });

        // 2. ค้นหาจากฐานข้อมูลท้องถิ่น (MySQL) - สำหรับคนไข้ส่งตัวจากภายนอก
        $localPatients = DB::connection('mysql')
            ->table('patient')
            ->where('hn', 'LIKE', "%{$query}%")
            ->orWhere(DB::raw("CONCAT(fname, ' ', lname)"), 'LIKE', "%{$query}%")
            ->limit(10)
            ->get()
            ->map(function ($item) {
                return (object)[
                    'hn' => $item->hn,
                    'title_name' => $item->title_name,
                    'firstName' => $item->fname,
                    'lastName' => $item->lname,
                    'birthDay' => null,
                    'birthDayFormatted' => 'คนไข้ภายนอก/รพช.',
                    'sex' => null,
                    'regNo' => null,
                    'CardID' => null,
                    'source' => 'LOCAL',
                    'hospital_name' => $item->hospital_name ?? 'ไม่ระบุ'
                ];
            });

        // 3. รวมผลลัพธ์ (ให้คนไข้ในระบบหลักขึ้นก่อน)
        $combinedResults = $hisPatients->merge($localPatients);

        return response()->json($combinedResults);
    }
}
