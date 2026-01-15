<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Exception;
use App\Helpers\HmsHelper;

class AppointmentController extends Controller
{
    // แสดงหน้าจัดการนัดหมาย
    public function showAppointments()
    {
        return view('management');
    }

    // เพิ่มนัด
    public function addAppointment(Request $request)
    {
        // 1. Validation - ตรวจสอบข้อมูลขาเข้า (เน้นฟิลด์ที่จำเป็นจริงๆ)
        $validator = Validator::make($request->all(), [
            'appointmentDate' => 'required',
            'docID'           => 'required',
            'ward'            => 'required',
            'resource'        => 'required|in:in,out',
            'appointment_time' => 'required',
            // เงื่อนไขตามประเภทผู้ป่วย
            'hn'              => 'required_if:resource,in',
            'fname'           => 'required_if:resource,out',
            'lname'           => 'required_if:resource,out',
            'hospital_name'   => 'required_if:resource,out',
        ], [
            'required' => 'กรุณากรอกข้อมูลให้ครบถ้วน',
            'required_if' => 'กรุณากรอกข้อมูลผู้ป่วยให้ครบถ้วน',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->with('message', [
                'status'  => 0,
                'title'   => 'ข้อมูลไม่ครบถ้วน',
                'message' => 'กรุณาระบุชื่อแพทย์ วอร์ด วันที่นัด และข้อมูลผู้ป่วยให้ครบถ้วน'
            ]);
        }

        $hn = $request->input('hn');

        DB::beginTransaction();
        try {
            // 2. จัดการข้อมูล HN
            if ($request->resource === "in") {
                $hn = str_pad(trim(preg_replace('/\s+/', '', $hn)), 7, ' ', STR_PAD_LEFT);
            } else {
                $fname = trim($request->input('fname'));
                $lname = trim($request->input('lname'));
                $titleName = $request->input('titleName');
                $hospitalName = $request->input('hospital_name');

                $existing = DB::connection('mysql')->table('patient')
                    ->where('fname', $fname)
                    ->where('lname', $lname)
                    ->first();

                if ($existing) {
                    $hn = $existing->hn;
                } else {
                    $prefix = HmsHelper::generateHospitalAbbreviation($hospitalName);
                    $lastPatient = DB::connection('mysql')->table('patient')
                        ->where('hn', 'like', $prefix . '%')
                        ->orderBy('hn', 'desc')
                        ->first();

                    $newNumber = '0001';
                    if ($lastPatient) {
                        $lastNum = (int)substr($lastPatient->hn, strlen($prefix));
                        $newNumber = str_pad($lastNum + 1, 4, '0', STR_PAD_LEFT);
                    }
                    $hn = $prefix . $newNumber;

                    DB::connection('mysql')->table('patient')->insert([
                        'hn'            => $hn,
                        'fname'         => $fname,
                        'lname'         => $lname,
                        'title_name'    => $titleName,
                        'hospital_name' => $hospitalName,
                    ]);
                }
            }

            // 3. เช็คการนัดซ้ำ
            $existingToday = DB::table('appointment')
                ->where('hn', $hn)
                ->where('a_date', $request->appointmentDate)
                ->first();

            if ($existingToday) {
                DB::rollBack();
                return redirect()->back()->with('message', [
                    'status'  => 0,
                    'title'   => 'มีการนัดหมายแล้ว',
                    'message' => 'ผู้ป่วยรายนี้มีการนัดหมายในวันที่เลือกไว้แล้ว'
                ]);
            }

            // 4. จัดการเวลา
            $appointmentTime = $request->input('appointment_time');
            if ($appointmentTime === 'custom') {
                $start = $request->input('custom_start_time');
                $end = $request->input('custom_end_time');
                $appointmentTime = "$start-$end";
            }

            // 5. บันทึกข้อมูลพร้อม Timestamp และข้อมูลผู้นัด
            DB::table('appointment')->insert([
                'hn'         => $hn,
                'tel'        => $request->tel ?? '-',
                'ward'       => str_pad(trim($request->input('ward')), 11, ' ', STR_PAD_RIGHT),
                'doc_id'     => $request->docID,
                'a_date'     => $request->appointmentDate,
                'a_time'     => $appointmentTime,
                'note'       => $request->filled('note') ? $request->note : '-',
                'created_at' => now(),
                'created_by' => session('user.fullname') ?? session('user.username'),
            ]);

            DB::commit();
            return redirect()->back()->with('message', [
                'status'  => 1,
                'title'   => 'เพิ่มสำเร็จ',
                'message' => 'เพิ่มการนัดหมายเรียบร้อยแล้ว'
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error in addAppointment: ' . $e->getMessage());
            return redirect()->back()->with('message', [
                'status'  => 0,
                'title'   => 'เกิดข้อผิดพลาด',
                'message' => 'เกิดข้อผิดพลาดทางเทคนิค: ' . $e->getMessage()
            ]);
        }
    }

    // ลบ
    public function deleteAppointment($a_id)
    {
        try {
            DB::table('appointment')->where('a_id', $a_id)->delete();
            return redirect()->back()->with('message', [
                'status'  => 1,
                'title' => 'ลบสำเร็จ',
                'message' => 'ลบรายการนัดหมายเรียบร้อย'
            ]);
        } catch (Exception $e) {
            return redirect()->back()->with('message', [
                'status'  => 0,
                'title' => 'ลบไม่สำเร็จ',
                'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()
            ]);
        }
    }

    // อัปเดตนัดหมาย
    public function updateAppointment(Request $request, $id)
    {
        try {
            $a_date = $request->input('a_date');
            $tel = $request->input('tel');
            $note = $request->input('note');

            $start = $request->input('a_time_start');
            $end = $request->input('a_time_end');
            $a_time = "$start-$end";

            DB::table('appointment')
                ->where('a_id', $id)
                ->update([
                    'a_date' => $a_date,
                    'tel'    => $tel,
                    'note'   => $note,
                    'a_time' => $a_time,
                ]);

            return response()->json(['success' => true]);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    // เช็คประวัติการนัดหมาย
    public function checkAppointmentHistory(Request $request)
    {
        try {
            $resource = $request->input('resource');
            $hn = $request->input('hn');
            $fname = $request->input('fname');
            $lname = $request->input('lname');

            if ($resource === 'in') {
                $hn = str_pad(trim(preg_replace('/\s+/', '', $hn)), 7, ' ', STR_PAD_LEFT);
            } else {
                // สำหรับคนไข้นอกรพ. ให้หา HN จากฐานข้อมูล MySQL ก่อน
                $patient = DB::connection('mysql')->table('patient')
                    ->where('fname', $fname)
                    ->where('lname', $lname)
                    ->first();

                if (!$patient) {
                    return response()->json(['status' => 'not_found', 'message' => 'ไม่พบข้อมูลผู้ป่วยนอกรพ.']);
                }
                $hn = $patient->hn;
            }

            // ค้นหาการนัดหมายที่ยังไม่ถึง (หรือล่าสุด)
            $appointment = DB::table('appointment')
                ->where('hn', $hn)
                ->orderBy('a_date', 'desc')
                ->first();

            if ($appointment) {
                // ตรวจสอบว่านัดในอนาคตหรือวันนี้หรือไม่
                $isFuture = $appointment->a_date >= date('Y-m-d');
                return response()->json([
                    'status' => 'found',
                    'message' => 'พบประวัติการนัดหมายล่าสุดวันที่ ' . HmsHelper::formatThaiDate($appointment->a_date),
                    'appointment' => $appointment
                ]);
            }

            return response()->json(['status' => 'no_history']);
        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    // ค้นหาแพทย์ (API)
    public function searchDoctors(Request $request)
    {
        $query = $request->input('q');
        $doctors = DB::connection('sqlsrv')
            ->table('DOCC')
            ->where('docName', 'like', "%$query%")
            ->orWhere('docLName', 'like', "%$query%")
            ->get();

        return response()->json($doctors);
    }
}
