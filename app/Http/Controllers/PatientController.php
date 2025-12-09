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
        $query = $request->input('query');

        if (!$query || strlen($query) < 2) {
            return response()->json(['error' => 'กรุณาป้อนคำค้นหาที่มีความยาวอย่างน้อย 2 ตัวอักษร'], 400);
        }

        $patients = DB::connection('sqlsrv')
            ->table('PATIENT')
            ->where('hn', 'LIKE', '%' . $query . '%')
            ->orwhere(DB::raw("firstName + ' ' + lastName"), 'LIKE', '%' . $query . '%')
            ->limit(5)
            ->get();
        return response()->json($patients);
    }
}
