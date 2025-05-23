<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MainController extends Controller
{
   public function index()
   {
      return redirect(route('app.show'));
   }


   // โหลดหน้า
   public function loadFragment(Request $request, $page)
   {
      if ($page === 'appointments') {

         $page = is_numeric($request->query('page')) ? (int)$request->query('page') : 1;
         $perPage = 8;
         $offset = ($page - 1) * $perPage;

         //ดึงจาก mysql
         $mysqlQuery = DB::connection('mysql')
            ->table('appointment')
            ->when($request->filled('hn'), function ($query) use ($request) {
               $query->where('hn', 'like', '%' . $request->hn . '%');
            })
            ->when($request->filled('start_date') && $request->filled('end_date'), function ($query) use ($request) {
               $query->whereBetween('a_date', [$request->start_date, $request->end_date]);
            })
            ->when($request->filled('doc_id'), function ($query) use ($request) {
               $query->where('doc_id', $request->doc_id);
            });


         $total = $mysqlQuery->count();

         $appointments = $mysqlQuery
            ->orderBy('a_date', 'ASC')
            ->offset($offset)
            ->limit($perPage)
            ->get();

         //ดึง hn ทั้งหมดในหน้านี้
         $hns = $appointments->pluck('hn')
            ->map(fn($hn) => str_pad(trim(preg_replace('/\s+/', '', $hn)), 7, ' ', STR_PAD_LEFT))
            ->filter(fn($hn) => trim($hn) !== '') // ตัดพวก hn ว่างล้วนออก
            ->unique()
            ->toArray();

         //ดึงรหัสแพทย์
         $docIDs = $appointments
            ->pluck('doc_id')
            ->filter()
            ->unique()
            ->toArray();

         //ดึงรหัสวอร์ด
         $deptCodes = $appointments
            ->pluck('ward')
            ->filter()
            ->unique()
            ->toArray();

         //ดึงข้อมูล ผู้ป่วย จาก sql server
         $patients = DB::connection('sqlsrv')
            ->table('PATIENT')
            ->join('REGION', 'PATIENT.regionCode', '=', 'REGION.regionCode')
            ->join('AREA', 'PATIENT.areaCode', '=', 'AREA.areaCode')
            ->join('PTITLE', 'PATIENT.titleCode', '=', 'PTITLE.titleCode')
            ->whereIn('PATIENT.hn', $hns)
            ->get()
            ->keyBy('hn');


         // ดึงข้อมูลแพทย์ 
         $doctors = DB::connection('sqlsrv')
            ->table('DOCC')
            ->whereIn(DB::raw('LTRIM(docCode)'), $docIDs)
            ->get()
            ->keyBy(function ($item) {
               return trim($item->docCode);
            });

         // ดึงข้อมูลวอร์ด
         $depts = DB::connection('sqlsrv')
            ->table('DEPT')
            ->whereIn(DB::raw('RTRIM(deptCode)'), $deptCodes)
            ->get()
            ->keyBy(function ($item) {
               return trim($item->deptCode);
            });

         // ทำ drop down หมอ
         $targetDoc = [' 21116', ' 22947', ' 26587', ' 33166', ' 34559', ' 37288', ' 36155', ' 34916'];
         $doc = DB::connection('sqlsrv')
            ->table('DOCC')
            ->whereIn('docCode', $targetDoc)
            ->orderBy('docCode')
            ->get();

         // dd($doc);

         // ทำ drop down วอร์ด
         $dept_list = DB::connection('sqlsrv')
            ->table('DEPT')
            ->get();


         // map ช้อมูลผู้ป่วยเข้ากับ appointment
         $appointments->transform(function ($item) use ($patients, $doctors, $depts) {
            $hn = str_pad(trim($item->hn), 7, ' ', STR_PAD_LEFT);
            $patient = $patients[$hn] ?? null;
            $doctor = $doctors[$item->doc_id] ?? null;


            // ชื่อผู้ป่วย
            $item->patient_name = (trim($patient?->titleName) ?? '') . ' ' . ($patient?->firstName ?? '') . ' ' . ($patient?->lastName ?? '');

            // ถ้าไม่พบ patient ใน SQL Server → fallback ไปใช้ MySQL
            if (trim($item->patient_name) === '') {
               $mysqlPatient = DB::connection('mysql')
                  ->table('patient')
                  ->where('hn', $hn)
                  ->first();

               if ($mysqlPatient) {
                  $item->patient_name =  ($mysqlPatient->title_name ?? '') . ' ' . ($mysqlPatient->fname ?? '') . ' ' . ($mysqlPatient->lname ?? '');
                  $item->hospital_name = $mysqlPatient->hospital_name ?? '';
               }
            }

            // คำนวณอายุจาก birthDay
            // เริ่มจากค่าว่างไว้ก่อน
            $item->age = null;

            // กรณีผู้ป่วยใน (SQL Server) → birthDay เป็น พ.ศ. และรูปแบบ yyyymmdd
            if ($patient?->birthDay && strlen($patient->birthDay) === 8) {
               $year = (int)substr($patient->birthDay, 0, 4) - 543;
               $month = (int)substr($patient->birthDay, 4, 2);
               $day = (int)substr($patient->birthDay, 6, 2);

               try {
                  $birthDate = Carbon::createFromDate($year, $month, $day);
                  $item->age = $birthDate->age . ' ปี';
               } catch (\Exception $e) {
                  $item->age = null;
               }
            }

            // กรณีผู้ป่วยนอก (MySQL) → birth_date เป็น ค.ศ. และรูปแบบ YYYY-MM-DD
            elseif (!empty($mysqlPatient?->dob)) {
               try {
                  $birthDate = Carbon::parse($mysqlPatient->dob);
                  $item->age = $birthDate->age . ' ปี';
               } catch (\Exception $e) {
                  $item->age = null;
               }
            }



            // ที่อยู่
            // ถ้า $patient เป็น null ให้ใช้ default ค่าเป็นค่าว่าง
            if ($patient) {
               $tambonCode = $patient->regionCode . $patient->tambonCode;
               $tambon = DB::connection('sqlsrv')->table('Tambon')->where('tambonCode', $tambonCode)->first();
               $tambonName = trim($tambon->tambonName ?? '');  // ใช้ trim() เพื่อลบช่องว่างเกิน

               // กำหนดที่อยู่โดยใช้ trim() กับแต่ละฟิลด์
               $addrParts = [
                  trim($patient->addr1),
                  $patient->moo ? 'หมู่ ' . trim($patient->moo) : '',   // ถ้ามีค่า moo ให้ใช้ trim()
                  $patient->addr2 ? 'ถ.' . trim($patient->addr2) : '',    // ถ้ามีค่า addr2 ให้ใช้ trim()
                  $tambonName ? 'ต.' . $tambonName : '',                 // ถ้ามี tambonName ให้ใช้ trim()
                  $patient->regionName ? 'อ.' . trim($patient->regionName) : '', // ถ้ามีค่า regionName ให้ใช้ trim()
                  $patient->areaName ? 'จ.' . trim($patient->areaName) : '',   // ถ้ามีค่า areaName ให้ใช้ trim()
                  trim($patient->postalCode),  // ตัดช่องว่างรอบ postalCode
               ];

               // กรองข้อมูลที่ว่างออกจาก $addrParts
               $item->address = implode(' ', array_filter($addrParts));
            } else {
               $item->address = 'ไม่พบข้อมูลที่อยู่';
            }



            // ชื่อแพทย์
            $item->doctor_name = ($doctor?->doctitle ?? '') . ' ' . ($doctor?->docName ?? '-') . ' ' . ($doctor?->docLName ?? '');

            // ชื่อแผนก (วอร์ด)
            $wardCode = trim($item->ward ?? '');
            $item->dept_name = $depts[$wardCode]->deptDesc ?? '';

            return $item;
         });

         $totalPages = ceil($total / $perPage);

         return view('fragments.appointments', compact('appointments', 'totalPages', 'page', 'doc', 'dept_list'));
      }

      // ตัวอย่างหน้า 2
      if ($page === 'treatments') {
         $page = is_numeric($request->query('page')) ? (int)$request->query('page') : 1;
         $perPage = 8;
         $offset = ($page - 1) * $perPage;

         // ===== STEP 1: Query หลัก (ใช้ได้ทั้ง count และ get) =====
         $mysqlQuery = DB::connection('mysql')
            ->table('treatment')
            ->when(
               $request->filled('hn'),
               fn($query) =>
               $query->where('hn', 'like', '%' . $request->hn . '%')
            )
            ->when(
               $request->filled('start_date') && $request->filled('end_date'),
               fn($query) =>
               $query->whereBetween('t_date', [$request->start_date, $request->end_date])
            );

         $total = $mysqlQuery->count();

         // ===== STEP 2: ดึงข้อมูลรายการจริง (clone เพื่อไม่กระทบ $mysqlQuery) =====
         $treatments = (clone $mysqlQuery)
            ->orderBy('t_date', 'DESC')
            ->offset($offset)
            ->limit($perPage)
            ->get();

         // ===== STEP 3: เตรียม HN และข้อมูลที่ต้องใช้ร่วม =====
         $hns = $treatments->pluck('hn')
            ->map(fn($hn) => str_pad(trim(preg_replace('/\s+/', '', $hn)), 7, ' ', STR_PAD_LEFT))
            ->filter(fn($hn) => trim($hn) !== '')
            ->unique()
            ->toArray();

         $patients = DB::connection('sqlsrv')
            ->table('PATIENT')
            ->join('REGION', 'PATIENT.regionCode', '=', 'REGION.regionCode')
            ->join('AREA', 'PATIENT.areaCode', '=', 'AREA.areaCode')
            ->join('PTITLE', 'PATIENT.titleCode', '=', 'PTITLE.titleCode')
            ->whereIn('PATIENT.hn', $hns)
            ->get()
            ->keyBy('hn');

         $depts = DB::connection('sqlsrv')
            ->table('DEPT')
            ->get()
            ->keyBy(fn($item) => trim($item->deptCode));

         $dept = DB::connection('sqlsrv')
            ->table('DEPT')
            ->get();


         $mysqlPatientLookup = fn($hn) =>
         DB::connection('mysql')->table('patient')->where('hn', $hn)->first();

         // ===== STEP 4: แปลงข้อมูลแต่ละรายการให้สมบูรณ์ =====
         $treatments->transform(function ($item) use ($patients, $depts, $mysqlPatientLookup) {
            $hn = str_pad(trim($item->hn), 7, ' ', STR_PAD_LEFT);
            $patient = $patients[$hn] ?? null;

            // ชื่อผู้ป่วย
            $item->patient_name = trim($patient?->titleName ?? '') . ' ' . trim(($patient?->firstName ?? '') . ' ' . ($patient?->lastName ?? ''));

            if (trim($item->patient_name) === '') {
               $mysqlPatient = $mysqlPatientLookup($hn);
               if ($mysqlPatient) {
                  $item->patient_name = ($mysqlPatient->title_name ?? '') . ' ' . ($mysqlPatient->fname ?? '') . ' ' . ($mysqlPatient->lname ?? '');
               }
            }

            // คำนวณอายุ
            $item->age_text = '-';
            if (!empty($patient?->birthDay)) {
               // คนไข้ใน (SQL Server) → birthDay เป็น 8 หลัก เช่น 25400516
               $raw = str_pad((string) $patient->birthDay, 8, '0', STR_PAD_LEFT);
               [$b_year, $b_month, $b_day] = [substr($raw, 0, 4), substr($raw, 4, 2), substr($raw, 6, 2)];

               if (checkdate((int)$b_month, (int)$b_day, (int)$b_year - 543)) {
                  $birthDate = Carbon::create($b_year - 543, $b_month, $b_day);
                  $item->age_text = $birthDate->age . ' ปี';
               }
            } else {
               // คนไข้นอก (MySQL) → birth_date เป็นรูปแบบ Y-m-d
               $mysqlPatient = $mysqlPatientLookup($hn);

               if (!empty($mysqlPatient?->dob)) {
                  try {
                     $birthDate = Carbon::createFromFormat('Y-m-d', $mysqlPatient->dob);
                     $item->age_text = $birthDate->age . ' ปี';
                  } catch (\Exception $e) {
                     // ถ้าเกิด error ในการแปลงวัน
                     $item->age_text = '-';
                  }
               }
            }

            // หน่วยงาน
            $item->dept_name = $depts[trim($item->agency ?? '')]->deptDesc ?? '';
            $item->dept_forward = $depts[trim($item->forward ?? '')]->deptDesc ?? '';

            return $item;
         });

         $date = now()->toDateString();
         $totalPages = ceil($total / $perPage);

         return view('fragments.treatments', compact('treatments', 'date', 'page','totalPages', 'dept'));
      }

      return view("fragments.$page");
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

   // add new patient
   public function addPatient(Request $request)
   {

      $hospital_name = $request->input('hospital_name');
      $fname = $request->input('fname');
      $lname = $request->input('lname');
      $id_card = $request->input('id_card');
      $titleName = $request->input('titleName');
      $gender = $request->input('gender');
      $dob = $request->input('dob');

      // ตรวจสอบผู้ป่วยซ้ำ
      $existing = DB::connection('mysql')->table('patient')
         ->where([
            ['fname', $fname],
            ['lname', $lname],
            ['dob', $dob],
            ['id_card', $id_card]
         ])
         ->first();

      if ($existing) {
         return redirect()->back()->with('message', [
            'status' => 0,
            'title' => 'เพิ่มไม่สำเร็จ',
            'message' => 'ผู้ป่วยนี้ข้อมูลในระบบอยู่แล้ว'
         ]);
      }

      // เริ่ม transaction
      DB::beginTransaction();
      try {
         // gen hn
         $prefix = $this->generateHospitalAbbreviation($hospital_name);
         $lastHN = DB::connection('mysql')
            ->table('patient')
            ->where('hn', 'like', $prefix . '%')
            ->orderBy('hn', 'desc')
            ->first();

         $newNumber = '0001';
         if ($lastHN) {
            $lastNumber = (int)substr($lastHN->hn, strlen($prefix));
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
         }

         $hn = $prefix . $newNumber;

         // insert
         DB::connection('mysql')->table('patient')->insert([
            'hn' => $hn,
            'title_name' => $titleName,
            'fname' => $fname,
            'lname' => $lname,
            'dob' => $dob,
            'id_card' => $id_card,
            'gender' => $gender,
            'hospital_name' => $hospital_name,
         ]);

         DB::commit();

         return redirect()->back()->with('message', [
            'status' => 1,
            'title' => 'เพิ่มสำเร็จ',
            'message' => 'เพิ่มเข้าระบบแล้ว'
         ]);
      } catch (\Exception $e) {
         DB::rollBack();
         return redirect()->back()->with('message', [
            'status' => 0,
            'title' => 'เกิดข้อผิดพลาด',
            'message' => 'ไม่สามารถเพิ่มผู้ป่วยได้: ' . $e->getMessage()
         ]);
      }
   }


   // กรอก hn หาชื่อ
   public function getPatientName(Request $request)
   {
      $hn = str_pad(trim(preg_replace('/\s+/', '', $request->hn)), 7, ' ', STR_PAD_LEFT);
      $name = null;
      $found = false;

      // ลองหาใน SQL Server ก่อน
      $patient = DB::connection('sqlsrv')->table('PATIENT')
         ->join('PTITLE', 'PATIENT.titleCode', '=', 'PTITLE.titleCode')
         ->where('hn', $hn)->first();

      if ($patient) {
         $first = rtrim($patient->firstName ?? '');
         $last = rtrim($patient->lastName ?? '');
         $title = rtrim($patient->titleName ?? '');
         $name = $title . ' ' . $first . ' ' . $last;
         $found = true;
      } else {
         // หาใน MySQL เผื่อเคยมีคนไข้นอก
         $local = DB::connection('mysql')->table('patient')->where('hn', $hn)->first();
         if ($local) {
            $first = $local->fname ?? '';
            $last = $local->lname ?? '';
            $name = $first . ' ' . $last;
            $found = true;
         }
      }

      return response()->json([
         'name' => $name,
         'found' => $found,
      ]);
   }
}
