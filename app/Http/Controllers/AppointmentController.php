<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;

use Carbon\Carbon;


class AppointmentController extends Controller
{
    function formatThaiDate($date)
    {
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

        $time = strtotime($date);
        $day = $dayTH[date('w', $time)];
        $dayNum = date('j', $time);
        $month = $monthTH[date('n', $time)];
        $year = date('Y', $time) + 543;

        return "วัน{$day}ที่ {$dayNum} {$month} {$year}";
    }

    // โชวนัด
    public function showAppointments(Request $request)
    {

        return view('index');
    }

    function generateHospitalAbbreviation($hospitalName)
    {
        // ตัดคำที่ไม่จำเป็นออก
        $words = preg_replace('/^(โรงพยาบาล)/u', '', $hospitalName);
        $words = preg_split('/\s+/u', trim($words)); // ตัดตามช่องว่าง

        $abbr = '';
        foreach ($words as $word) {
            $abbr .= mb_substr($word, 0, 1, "UTF-8"); // เอาอักษรตัวแรกของแต่ละคำ
        }

        return strtoupper($abbr);
    }


    // เพิ่มนัด
    public function addAppointment(Request $request)
    {
        $hn = $request->input('hn');


        if ($request->resource === "in") {
            // ผู้ป่วยใน
            $hn = str_pad(trim(preg_replace('/\s+/', '', $hn)), 7, ' ', STR_PAD_LEFT);
        } else {
            // ผูป่วยนอก
            $fname = $request->input('fname');
            $lname = $request->input('lname');

            // เช็คว่าคนไข้มีอยู่แล้วไหม
            $existing = DB::connection('mysql')
                ->table('patient')
                ->where('fname', $fname)
                ->where('lname', $lname)
                ->first();

            if (!$existing) {

                return redirect()->back()->with('message', [
                    'status' => 0,
                    'title' => 'ไม่พบผู้ป่วย',
                    'message' => "ไม่พบผู้ป่วยชื่อ $fname $lname ในระบบ"
                ]);
            }
            $hn = $existing->hn;
        }

        $appointmentTime = $request->input('appointment_time');

        if ($appointmentTime === 'custom') {
            $start = $request->input('custom_start_time');
            $end = $request->input('custom_end_time');

            if ($start && $end) {
                $appointmentTime = "$start-$end";
            } else {
                return;
            }
        }


        $ward =  str_pad(trim(preg_replace('/\s+/', '', $request->input('ward'))), 6, ' ', STR_PAD_RIGHT);
        $note = $request->filled('note') ? $request->note : '-';


        $insert = DB::table('appointment')->insert([
            'hn' => $hn,
            'tel' => $request->tel,
            'ward' => $ward,
            'doc_id' => $request->docID,
            'a_date' => $request->appointmentDate,
            'a_time' => $appointmentTime,
            'note' => $note
            // 'added_by' => $request->added_by
        ]);

        $message = $insert
            ? ['status' => 1, 'title' => 'เพิ่มสำเร็จ', 'message' => 'เพิ่มสำเร็จ']
            : ['status' => 0, 'title' => 'เพิ่มไม่สำเร็จ', 'message' => 'เพิ่มไม่สำเร็จ'];

        return redirect()->back()->with('message', $message);
    }

    // ลบนัด
    public function deleteAppointment($a_id)
    {

        $delete = DB::table('appointment')->where('a_id', $a_id)->delete();
        $message = [];
        if ($delete) {
            $message = [
                'status' => 1,
                'title' => 'ลบสำเร็จ',
                'message' => 'ลบสำเร็จ'
            ];
        } else {
            $message = [
                'status' => 1,
                'title' => 'ลบสำเร็จ',
                'message' => 'ลบสำเร็จ'
            ];
        }
        return redirect()->back()->with('message', $message);
    }

    // แก้ไขนัด
    public function updateAppointment(Request $request, $id)
    {
        $a_time = $request->input('a_time_start') . '-' . $request->input('a_time_end');
        $updated = DB::connection('mysql')
            ->table('appointment')
            ->where('a_id', $id)
            ->update([
                'a_date' => $request->input('a_date'),
                'a_time' => $a_time,
                'tel' => $request->input('tel'),
                'note' => $request->input('note'),
            ]);

        if ($updated) {
            return response()->json(['success' => true]);
        } else {
            return response()->json(['success' => false, 'message' => 'อัปเดตไม่สำเร็จ']);
        }
    }

    public function searchDoctors(Request $request)
    {
        $q = $request->get('q');

        $results = DB::connection('sqlsrv')
            ->table('DOCC')
            ->select('docCode', 'docName', 'doctitle')
            ->where('docCode', 'like', "%$q%")
            ->orWhere('docName', 'like', "%$q%")
            ->get();

        return response()->json($results);
    }
}
