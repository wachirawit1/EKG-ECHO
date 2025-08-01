<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class TreatmentController extends Controller
{


    // เพิ่มการมารักษา
    public function addTreatment(Request $request)
    {
        $resource = $request->input('resource');
        if ($resource === 'in') {
            $hn = str_pad(trim(preg_replace('/\s+/', '', $request->input('hn'))), 7, ' ', STR_PAD_LEFT);

            // เช็คว่า appointment มีในวันนี้ไหม
            $exists = DB::connection('mysql')
                ->table('appointment')
                ->where('hn', $hn)
                ->where('a_date', $request->input('t_date'))
                ->exists();

            if (!$exists) {
                return redirect()->back()->with('message', [
                    'status' => 0,
                    'title' => 'ไม่พบการนัดหมาย',
                    'message' => 'ไม่พบข้อมูลนัดหมายของ HN นี้ในวันที่ระบุ'
                ]);
            }
        } else { // out
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
                    'title' => 'ไม่พบผู้ป่วย',
                    'message' => 'ไม่พบข้อมูลผู้ป่วยนอกรพ. ชื่อนี้'
                ]);
            }
            $hn = $existing->hn;
        }

        // Insert ลงตาราง treatment
        $insert = DB::connection('mysql')->table('treatment')->insert([
            'hn' => $hn,
            't_date' => $request->input('t_date'),
            'agency' => str_pad(trim($request->input('agency')), 11, ' ', STR_PAD_RIGHT),
            'forward' => str_pad(trim($request->input('forward')), 11, ' ', STR_PAD_RIGHT),
        ]);

        $message = $insert
            ? ['status' => 1, 'title' => 'เพิ่มสำเร็จ', 'message' => 'เพิ่มข้อมูลการรักษาเรียบร้อย']
            : ['status' => 0, 'title' => 'เพิ่มไม่สำเร็จ', 'message' => 'เกิดข้อผิดพลาดในการบันทึก'];

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
