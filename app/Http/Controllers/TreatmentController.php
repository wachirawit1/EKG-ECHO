<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class TreatmentController extends Controller
{


    // เพิ่มการมารักษา
    public function addTreatment(Request $request)
    {

        $hn = str_pad(trim($request->input('hn')), 7, ' ', STR_PAD_LEFT);
        $t_date = $request->input('t_date');
        $agency = str_pad(trim($request->input('agency')), 11, ' ', STR_PAD_RIGHT);
        $forward = str_pad(trim($request->input('forward')), 11, ' ', STR_PAD_RIGHT);
        $resource = $request->input('resource');

        if ($resource === 'in') {

            // คนไข้ใน (SQL Server)
            $insert = DB::connection('mysql')
                ->table('treatment')
                ->insert([
                    'hn' => $hn,
                    't_date' => $t_date,
                    'agency' => $agency,
                    'forward' => $forward
                ]);

            $message = $insert
                ? ['status' => 1, 'title' => 'เพิ่มสำเร็จ', 'message' => 'เพิ่มข้อมูลการรักษาสำหรับผู้ป่วยในสำเร็จ']
                : ['status' => 0, 'title' => 'เพิ่มไม่สำเร็จ', 'message' => 'ไม่สามารถเพิ่มข้อมูลผู้ป่วยในได้'];

            return redirect()->back()->with('message', $message);
        } else {
            // เช็คคนไข้นอกใน MySQL
            $fname = trim($request->input('fname'));
            $lname = trim($request->input('lname'));

            $existing = DB::connection('mysql')
                ->table('patient')
                ->where('fname', $fname)
                ->where('lname', $lname)
                ->first();

            if (!$existing) {
                return redirect()->back()->with('message', [
                    'status' => 0,
                    'title' => 'เพิ่มไม่สำเร็จ',
                    'message' => 'ไม่พบข้อมูลผู้ป่วยนอกชื่อดังกล่าว'
                ]);
            }

            // ถ้าเจอ → ใช้ HN จาก patient table แล้วเพิ่มการรักษา
            $hn = $existing->hn;

            $insert = DB::connection('mysql')
                ->table('treatment')
                ->insert([
                    'hn' => $hn,
                    't_date' => $t_date,
                    'agency' => $agency,
                    'forward' => $forward
                ]);
        }





        $message = $insert
            ? ['status' => 1, 'title' => 'เพิ่มสำเร็จ', 'message' => 'เพิ่มข้อมูลการรักษาสำหรับผู้ป่วยนอกสำเร็จ']
            : ['status' => 0, 'title' => 'เพิ่มไม่สำเร็จ', 'message' => 'ไม่สามารถเพิ่มข้อมูลผู้ป่วยนอกได้'];

        return redirect()->back()->with('message', $message);
    }


    //ลบการักษา
    public function deleteTreatment($t_id)
    {
        $delete = DB::table('treatment')
            ->where('t_id', $t_id)
            ->delete();

        $message = [];

        if ($delete) {
            $message = [
                'status' => 1,
                'title' => 'ลบสำเร็จ',
                'message' => 'ลบสำเร็จ'
            ];
        } else {
            $message = [
                'status' => 0,
                'title' => 'ลบไม่สำเร็จ',
                'message' => 'ลบไม่สำเร็จ'
            ];
        }
        return redirect()->back()->with('message', $message);
    }
}
