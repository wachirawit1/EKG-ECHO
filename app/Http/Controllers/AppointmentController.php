<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;

use Carbon\Carbon;

use Illuminate\Support\Facades\Log;
use Exception;


class AppointmentController extends Controller
{
    function formatThaiDate($date)
    {
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
            return $date; // fallback ถ้าแปลงไม่ได้
        }
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
    // อัพเดท addAppointment method เพื่อเพิ่มการเช็คซ้ำก่อนบันทึก
    public function addAppointment(Request $request)
    {
        $hn = $request->input('hn');

        if ($request->resource === "in") {
            $hn = str_pad(trim(preg_replace('/\s+/', '', $hn)), 7, ' ', STR_PAD_LEFT);
        } else {
            $fname = $request->input('fname');
            $lname = $request->input('lname');
            $titleName = $request->input('titleName');
            $hospitalName = $request->input('hospital_name');

            // ตรวจสอบว่ามีผู้ป่วยอยู่แล้วหรือไม่
            $existing = DB::connection('mysql')
                ->table('patient')
                ->where('fname', $fname)
                ->where('lname', $lname)
                ->first();

            if ($existing) {
                $hn = $existing->hn;
            } else {
                // สร้าง HN ใหม่สำหรับผู้ป่วยนอก
                $lastPatient = DB::connection('mysql')
                    ->table('patient')
                    ->orderBy('hn', 'desc')
                    ->first();

                $newHnNumber = $lastPatient ? (int)trim($lastPatient->hn) + 1 : 1;
                $hn = str_pad($newHnNumber, 7, '0', STR_PAD_LEFT);

                // เพิ่มผู้ป่วยใหม่
                DB::connection('mysql')->table('patient')->insert([
                    'hn' => $hn,
                    'fname' => $fname,
                    'lname' => $lname,
                    'title_name' => $titleName,
                    'hospital_name' => $hospitalName,
                ]);
            }
        }

        // เช็คการนัดซ้ำ
        $existingToday = DB::table('appointment')
            ->where('hn', $hn)
            ->where('a_date', $request->appointmentDate)
            ->first();

        if ($existingToday) {
            return redirect()->back()->with('message', [
                'status' => 0,
                'title' => 'มีการนัดหมายแล้ว',
                'message' => 'ผู้ป่วยรายนี้มีการนัดหมายในวันที่เลือกแล้ว'
            ]);
        }

        $appointmentTime = $request->input('appointment_time');
        if ($appointmentTime === 'custom') {
            $start = $request->input('custom_start_time');
            $end = $request->input('custom_end_time');
            $appointmentTime = "$start-$end";
        }

        $ward = str_pad(trim($request->input('ward')), 11, ' ', STR_PAD_RIGHT);
        $note = $request->filled('note') ? $request->note : '-';

        $insert = DB::table('appointment')->insert([
            'hn' => $hn,
            'tel' => $request->tel,
            'ward' => $ward,
            'doc_id' => $request->docID,
            'a_date' => $request->appointmentDate,
            'a_time' => $appointmentTime,
            'note' => $note
        ]);

        $message = $insert
            ? ['status' => 1, 'title' => 'เพิ่มสำเร็จ', 'message' => 'เพิ่มการนัดหมายสำเร็จ']
            : ['status' => 0, 'title' => 'เพิ่มไม่สำเร็จ', 'message' => 'เกิดข้อผิดพลาดในการเพิ่มข้อมูล'];

        return redirect()->back()->with('message', $message);
    }

    // เช็คประวัติการนัดล่าสุด
    public function checkAppointmentHistory(Request $request)
    {
        try {
            // เพิ่ม debug log
            Log::info('checkAppointmentHistory called', [
                'request_data' => $request->all(),
                'method' => $request->method(),
                'headers' => $request->headers->all()
            ]);

            // ตรวจสอบข้อมูลที่ส่งมา
            $resource = $request->input('resource');

            if (!$resource || !in_array($resource, ['in', 'out'])) {
                Log::warning('Invalid resource type', ['resource' => $resource]);
                return response()->json([
                    'status' => 'error',
                    'message' => 'ประเภทผู้ป่วยไม่ถูกต้อง'
                ], 400);
            }

            $hn = null;

            if ($resource === "in") {
                // ผู้ป่วยใน - ตรวจสอบและ format HN
                $inputHn = $request->input('hn');

                if (!$inputHn) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'กรุณาระบุ HN'
                    ], 400);
                }

                // ตรวจสอบรูปแบบ HN
                $cleanHn = trim(preg_replace('/\s+/', '', $inputHn));
                if (!preg_match('/^\d{1,7}$/', $cleanHn)) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'HN ต้องเป็นตัวเลขไม่เกิน 7 หลัก'
                    ], 400);
                }

                $hn = str_pad($cleanHn, 7, ' ', STR_PAD_LEFT);
            } else {
                // ผู้ป่วยนอก - หา HN จากชื่อ
                $fname = trim($request->input('fname', ''));
                $lname = trim($request->input('lname', ''));

                if (!$fname || !$lname) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'กรุณาระบุชื่อและนามสกุล'
                    ], 400);
                }

                $existing = DB::connection('mysql')
                    ->table('patient')
                    ->where('fname', $fname)
                    ->where('lname', $lname)
                    ->first();

                if (!$existing) {
                    return response()->json([
                        'status' => 'not_found',
                        'message' => "ไม่ไม่เคยมีประวัติผู้ป่วยชื่อ {$fname} {$lname} ในระบบ"
                    ]);
                }
                $hn = $existing->hn;
            }

            Log::info('Processing HN', ['hn' => $hn]);

            // เช็คประวัติการนัดล่าสุด - ดึงจาก MySQL เฉพาะข้อมูล appointment
            $lastAppointment = DB::connection('mysql')
                ->table('appointment')
                ->select('a_date', 'a_time', 'doc_id', 'ward', 'note')
                ->where('hn', $hn)
                ->orderBy('a_date', 'desc')
                ->orderBy('a_id', 'desc')
                ->first();

            Log::info('Database query result', ['appointment' => $lastAppointment]);

            if ($lastAppointment) {
                // ดึงข้อมูลแพทย์จาก SQL Server
                $doctor = null;
                if ($lastAppointment->doc_id) {
                    $doctor = DB::connection('sqlsrv')
                        ->table('DOCC')
                        ->where(DB::raw('LTRIM(docCode)'), trim($lastAppointment->doc_id))
                        ->first();
                }

                // ดึงข้อมูลแผนก/วอร์ดจาก SQL Server
                $wardName = '';
                $wardRaw = $lastAppointment->ward ?? '';

                if (!empty($wardRaw) && trim(strtolower($wardRaw)) !== 'none' && trim($wardRaw) !== '-') {
                    if (strpos($wardRaw, 'dept:') !== false) {
                        // เป็นแผนก
                        $wardCode = trim(str_replace('dept:', '', $wardRaw));
                        $dept = DB::connection('sqlsrv')
                            ->table('DEPT')
                            ->where(DB::raw('LTRIM(RTRIM(deptCode))'), $wardCode)
                            ->first();
                        $wardName = $dept ? trim($dept->deptDesc) : '';
                    } elseif (strpos($wardRaw, 'ward:') !== false) {
                        // เป็นวอร์ด
                        $wardCode = trim(str_replace('ward:', '', $wardRaw));
                        $ward = DB::connection('sqlsrv')
                            ->table('Ward')
                            ->where(DB::raw('LTRIM(RTRIM(ward_id))'), $wardCode)
                            ->first();
                        $wardName = $ward ? trim($ward->ward_name) : '';
                    } else {
                        // ไม่มี prefix ลองหาทั้งวอร์ดและแผนก
                        $wardCode = trim($wardRaw);

                        // ลองหาใน Ward ก่อน
                        $ward = DB::connection('sqlsrv')
                            ->table('Ward')
                            ->where(DB::raw('LTRIM(RTRIM(ward_id))'), $wardCode)
                            ->first();

                        if ($ward) {
                            $wardName = trim($ward->ward_name);
                        } else {
                            // ถ้าไม่เจอ ลองหาใน DEPT
                            $dept = DB::connection('sqlsrv')
                                ->table('DEPT')
                                ->where(DB::raw('LTRIM(RTRIM(deptCode))'), $wardCode)
                                ->first();
                            $wardName = $dept ? trim($dept->deptDesc) : '';
                        }
                    }
                }

                // จัดรูปแบบชื่อแพทย์
                $doctorName = '';
                if ($doctor) {
                    $doctorName = trim(
                        ($doctor->doctitle ?? '') . ' ' .
                            ($doctor->docName ?? '') . ' ' .
                            ($doctor->docLName ?? '')
                    );
                }

                // แปลงวันที่เป็นรูปแบบไทย
                $thaiDate = $this->formatThaiDate($lastAppointment->a_date);

                // เช็คว่าเป็นการนัดในอดีตหรืออนาคต
                $appointmentDate = Carbon::parse($lastAppointment->a_date);
                $today = Carbon::today();

                $timePrefix = '';
                if ($appointmentDate->isFuture()) {
                    $timePrefix = 'มีการนัดหมายล่วงหน้า';
                } elseif ($appointmentDate->isToday()) {
                    $timePrefix = 'มีการนัดหมายวันนี้';
                } else {
                    $timePrefix = 'เคยมานัดล่าสุด';
                }

                $message = "{$timePrefix} {$thaiDate} เวลา {$lastAppointment->a_time}";

                if ($doctorName && trim($doctorName) !== '') {
                    $message .= " หมอ{$doctorName}";
                }

                if ($wardName && trim($wardName) !== '') {
                    $message .= " วอร์ด/แผนก {$wardName}";
                }

                if ($lastAppointment->note && $lastAppointment->note !== '-') {
                    $message .= " หมายเหตุ: {$lastAppointment->note}";
                }

                return response()->json([
                    'status' => 'found',
                    'message' => $message,
                    'data' => [
                        'date' => $lastAppointment->a_date,
                        'thai_date' => $thaiDate,
                        'time' => $lastAppointment->a_time,
                        'doctor' => $doctorName,
                        'ward' => $wardName,
                        'note' => $lastAppointment->note,
                        'is_future' => $appointmentDate->isFuture(),
                        'is_today' => $appointmentDate->isToday()
                    ]
                ]);
            }

            return response()->json([
                'status' => 'no_history',
                'message' => 'ไม่พบประวัติการนัดหมายก่อนหน้า'
            ]);
        } catch (Exception $e) {
            // Log error สำหรับ debug
            Log::error('Error in checkAppointmentHistory: ' . $e->getMessage(), [
                'request_data' => $request->all(),
                'stack_trace' => $e->getTraceAsString(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'เกิดข้อผิดพลาดในการตรวจสอบข้อมูล กรุณาลองใหม่อีกครั้ง',
                'debug' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
}
