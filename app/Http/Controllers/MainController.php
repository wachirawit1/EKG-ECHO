<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class MainController extends Controller
{
    public function index()
    {
        return view('home');
    }


    // โหลดหน้า
    public function loadFragment(Request $request, $page)
    {
        if ($page === 'appointments') {

            $page = is_numeric($request->query('page')) ? (int)$request->query('page') : 1;
            $perPage = 10;
            $offset = ($page - 1) * $perPage;
            $today = Carbon::now()->toDateString();

            //ดึงจาก mysql
            $mysqlQuery = DB::connection('mysql')
                ->table('appointment')
                ->when($request->filled('hn'), function ($query) use ($request) {
                    $query->where('hn', 'like', '%' . $request->hn . '%');
                })
                ->when($request->filled('start_date') && $request->filled('end_date'), function ($query) use ($request) {
                    $query->whereBetween('a_date', [$request->start_date, $request->end_date]);
                })
                ->when(
                    !$request->filled('hn') &&
                        !$request->filled('start_date') &&
                        !$request->filled('end_date') &&
                        !$request->filled('doc_id'),
                    function ($query) use ($today) {
                        // ถ้าไม่ได้กรอกอะไรเลยเลยทั้ง hn, date, doc_id → ให้ default เป็นวันนี้
                        $query->where('a_date', '=', $today);
                    }
                )
                ->when($request->filled('doc_id'), function ($query) use ($request) {
                    $query->where('doc_id', $request->doc_id);
                })
                ->orderBy('a_date', 'ASC');


            $total = $mysqlQuery->count();

            $appointments = $mysqlQuery
                ->offset($offset)
                ->limit($perPage)
                ->get();

            //ดึง hn ทั้งหมดในหน้านี้
            $hns = $appointments->pluck('hn')
                ->map(fn($hn) => str_pad(trim(preg_replace('/\s+/', '', $hn)), 7, ' ', STR_PAD_LEFT))
                ->unique()
                ->toArray();

            //ดึงรหัสแพทย์
            $docIDs = $appointments
                ->pluck('doc_id')
                ->filter()
                ->unique()
                ->toArray();

            //ดึงรหัสแผนก
            $deptCodes = $appointments
                ->pluck('ward')
                ->filter(function ($ward) {
                    return strpos($ward, 'dept:') !== false;
                })
                ->map(function ($ward) {
                    // แยกค่า dept ออกจาก "dept:REH103" หรือ "dept:212"
                    $code = str_replace('dept:', '', $ward);
                    return trim($code);
                })
                ->filter()
                ->unique()
                ->toArray();

            $wardCodes = $appointments
                ->pluck('ward')
                ->filter(function ($ward) {
                    return strpos($ward, 'ward:') !== false;
                })
                ->map(function ($ward) {
                    // แยกค่า ward ออกจาก "ward:115"
                    $code = str_replace('ward:', '', $ward);
                    return trim($code);
                })
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
                ->whereIn(DB::raw('LTRIM(RTRIM(deptCode))'), $deptCodes) // ใช้ LTRIM และ RTRIM
                ->get()
                ->keyBy(function ($item) {
                    return trim($item->deptCode); // trim ทั้งซ้ายและขวา
                });

            $wards = DB::connection('sqlsrv')
                ->table('Ward')
                ->whereIn(DB::raw('LTRIM(RTRIM(ward_id))'), $wardCodes) // ใช้ LTRIM และ RTRIM
                ->get()
                ->keyBy(function ($item) {
                    return trim($item->ward_id); // trim ทั้งซ้ายและขวา
                });

            // ทำ drop down โรงพยาบาล
            $hospcode = [];

            $buriram = DB::connection('sqlsrv')
                ->table('HOSPCODE')
                ->where('CHANGWAT', '31') // 31 = บุรีรัมย์
                ->where('OFF_NAME2', 'รพช.')
                ->orderBy('OFF_ID')
                ->get()
                ->toArray();

            $korat = DB::connection('sqlsrv')
                ->table('HOSPCODE')
                ->whereIn('OFF_ID', ['11602', '11608'])
                ->orderBy('OFF_ID')
                ->get()
                ->toArray();

            // รวมผลลัพธ์และ map
            $hospcode = array_merge($buriram, $korat);

            // ทำ drop down หมอ
            $targetDoc = [' 21116', ' 22947', ' 26587', ' 33166', ' 34559', ' 37288', ' 36155', ' 34916'];
            $doc = DB::connection('sqlsrv')
                ->table('DOCC')
                ->whereIn('docCode', $targetDoc)
                ->orderBy('docCode')
                ->get();


            // ทำ drop down วอร์ด
            $excludedDeptDesc = [
                'ยกเลิก',
                '(ยกเลิก) พัฒนาการเด็ก',
                '(ยกเลิก) คลินิกโรคเลือดในเด็ก',
                '(ยกเลิก)คลินิกนมแม่',
            ];

            $dept_list = DB::connection('sqlsrv')
                ->table('DEPT')
                ->select(
                    DB::raw("RTRIM(deptCode) + ' - ' + RTRIM(deptDesc) AS NameDept"),
                    '*'
                )
                ->whereNotIn('deptDesc', $excludedDeptDesc)
                ->get();

            $excludedWardIds = [
                'IQF01',
                'IQF02',
                'IQF03',
                'IQF04',
                'IQF05',
                'IQF06',
                'IQF07',
                'IQF08',
                'IQF09',
                'IQF10',
                'IQF11',
                'IQF12',
                'IQF13',
                'IQF14',
                'IQF15',
                'IQF16',
                'IQF17',
                'IQF18',
                'IQM01',
                'IQM02',
                'IQM03',
                'IQM04',
                'IQM05',
                'IQM06',
                'IQM07',
                'IQM08',
                'IQM09',
                'IQM10',
                'IQM11',
                'IQM12',
                'IQM13',
                'IQM14',
                'IQM15',
                'IQM16',
                'IQM17',
                'IQM18',
                'IQM19',
                'IQM20',
                'IQM21',
                'IQM22',
                'IQM23',
                'IQM24',
                'IQM25',
                'IQM26',
                'IQM27',
                'IQW',
                'IQWF8',
                'IQWF7',
                'IQWF6',
                'IQWM8',
                'IQWM7',
                'IQWM6',
                'IQWM5',
                'IQWM4',
                'IQWM3',
                'IQWM2',
                'IQWM1'
            ];

            $ward_list = DB::connection('sqlsrv')
                ->table('Ward')
                ->select(
                    DB::raw("RTRIM(ward_id) + ' - ' + RTRIM(ward_name) AS Nameward"),
                    'ward_id',
                    'ward_name',
                    'UNUSES'
                )
                ->whereNotIn(DB::raw("RTRIM(ward_id)"), $excludedWardIds)
                ->where(function ($query) {
                    $query->whereNull('UNUSES')->orWhere('UNUSES', '<>', 'Y');
                })
                ->orderBy('ward_name', 'asc')
                ->get();

            // map ช้อมูลผู้ป่วยเข้ากับ appointment
            $appointments->transform(function ($item) use ($patients, $doctors, $depts, $wards) {
                $hn = str_pad(trim($item->hn), 7, ' ', STR_PAD_LEFT);
                $patient = $patients[$hn] ?? null;
                $doctor = $doctors[$item->doc_id] ?? null;

                // ชื่อผู้ป่วย
                $item->patient_name = (trim($patient?->titleName) ?? ' ') . ' ' . ($patient?->firstName ?? ' ') . ' ' . ($patient?->lastName ?? ' ');

                // ถ้าไม่พบ patient ใน SQL Server → fallback ไปใช้ MySQL
                if (trim($item->patient_name) === '') {
                    $mysqlPatient = DB::connection('mysql')
                        ->table('patient')
                        ->where('hn', $hn)
                        ->first();

                    if ($mysqlPatient) {
                        $item->hospital_name = ' - รพช. ' . ($mysqlPatient->hospital_name ?? '');
                        $item->patient_name =  ($mysqlPatient->title_name ?? '') . ' ' . ($mysqlPatient->fname ?? '') . ' ' . ($mysqlPatient->lname ?? '');
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
                else {
                    $item->age = '-';
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
                $item->doctor_name = ($doctor?->doctitle ?? '') . ' ' . ($doctor?->docName ?? 'ไม่ระบุ') . ' ' . ($doctor?->docLName ?? '');

                // ชื่อแผนก/วอร์ด
                // ชื่อแผนก/วอร์ด - แก้ไขการดึงค่า ward code
                $wardRaw = $item->ward ?? '';
                $wardCode = '';
                $isDept = false;

                // ตรวจสอบกรณีที่ไม่มีข้อมูลหรือเป็น none
                if (empty($wardRaw) || trim(strtolower($wardRaw)) === 'none' || trim($wardRaw) === '-') {
                    $item->dept_name = '-';
                }
                // ตรวจสอบว่าเป็น dept: หรือ ward:
                elseif (strpos($wardRaw, 'dept:') !== false) {
                    $wardCode = trim(str_replace('dept:', '', $wardRaw));
                    $isDept = true;

                    // ค้นหาชื่อแผนก
                    $item->dept_name = isset($depts[$wardCode]) ? trim($depts[$wardCode]->deptDesc) :
                        'ไม่พบข้อมูลแผนก (Code: ' . $wardCode . ')';
                } elseif (strpos($wardRaw, 'ward:') !== false) {
                    $wardCode = trim(str_replace('ward:', '', $wardRaw));
                    $isDept = false;

                    // ค้นหาชื่อวอร์ด
                    $item->dept_name = isset($wards[$wardCode]) ? trim($wards[$wardCode]->ward_name) :
                        'ไม่พบข้อมูลวอร์ด (Code: ' . $wardCode . ')';
                } else {
                    // กรณีไม่มี prefix dept: หรือ ward:
                    $wardCode = trim($wardRaw);

                    if (empty($wardCode)) {
                        $item->dept_name = '-';
                    } else {
                        // ลองหาใน ward ก่อน แล้วค่อยหาใน dept
                        if (isset($wards[$wardCode])) {
                            $item->dept_name = trim($wards[$wardCode]->ward_name);
                        } elseif (isset($depts[$wardCode])) {
                            $item->dept_name = trim($depts[$wardCode]->deptDesc);
                        } else {
                            $item->dept_name = 'ไม่พบข้อมูล (Code: ' . $wardCode . ')';
                        }
                    }
                }
                return $item;
            });

            $totalPages = ceil($total / $perPage);
            $startNum = ($page - 1) * $perPage + 1;
            $endNum = min($total, $page * $perPage);

            return view('fragments.appointments', compact('appointments', 'totalPages', 'page', 'perPage', 'doc', 'hospcode', 'dept_list', 'ward_list', 'total', 'startNum', 'endNum'));
        }

        // ตัวอย่างหน้า 2
        if ($page === 'treatments') {
            $page = is_numeric($request->query('page')) ? (int)$request->query('page') : 1;
            $perPage = 10;
            $offset = ($page - 1) * $perPage;

            //ดึงจาก mysql
            $mysqlQuery = DB::connection('mysql')
                ->table('treatment')
                ->when($request->filled('hn'), function ($query) use ($request) {
                    $query->where('hn', 'like', '%' . $request->hn . '%');
                })
                ->when($request->filled('start_date') && $request->filled('end_date'), function ($query) use ($request) {
                    $query->whereBetween('t_date', [$request->start_date, $request->end_date]);
                });

            $total = $mysqlQuery->count();

            $treatments = $mysqlQuery
                ->offset($offset)
                ->limit($perPage)
                ->get();

            //ดึง hn ทั้งหมดในหน้านี้
            $hns = $treatments->pluck('hn')
                ->map(fn($hn) => str_pad(trim(preg_replace('/\s+/', '', $hn)), 7, ' ', STR_PAD_LEFT))
                ->filter(fn($hn) => trim($hn) !== '') // ตัดพวก hn ว่างล้วนออก
                ->unique()
                ->toArray();

            //ดึงรหัสแผนก
            $deptCodes = collect()
                ->merge($treatments->pluck('agency'))
                ->merge($treatments->pluck('forward'))
                ->filter(function ($code) {
                    return strpos($code, 'dept:') !== false;
                })
                ->map(function ($code) {
                    // แยกค่า dept ออกจาก "dept:REH103" หรือ "dept:212"
                    $code = str_replace('dept:', '', $code);
                    return trim($code);
                })
                ->filter()
                ->unique()
                ->toArray();

            $wardCodes = collect()
                ->merge($treatments->pluck('agency'))
                ->merge($treatments->pluck('forward'))
                ->filter(function ($code) {
                    return strpos($code, 'ward:') !== false;
                })
                ->map(function ($code) {
                    // แยกค่า ward ออกจาก "ward:115"
                    $code = str_replace('ward:', '', $code);
                    return trim($code);
                })
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

            // ดึงข้อมูลวอร์ด
            $depts = DB::connection('sqlsrv')
                ->table('DEPT')
                ->whereIn(DB::raw('LTRIM(RTRIM(deptCode))'), $deptCodes) // ใช้ LTRIM และ RTRIM
                ->get()
                ->keyBy(function ($item) {
                    return trim($item->deptCode); // trim ทั้งซ้ายและขวา
                });

            $wards = DB::connection('sqlsrv')
                ->table('Ward')
                ->whereIn(DB::raw('LTRIM(RTRIM(ward_id))'), $wardCodes) // ใช้ LTRIM และ RTRIM
                ->get()
                ->keyBy(function ($item) {
                    return trim($item->ward_id); // trim ทั้งซ้ายและขวา
                });

            // ทำ drop down วอร์ด
            $excludedDeptDesc = [
                'ยกเลิก',
                '(ยกเลิก) พัฒนาการเด็ก',
                '(ยกเลิก) คลินิกโรคเลือดในเด็ก',
                '(ยกเลิก)คลินิกนมแม่',
            ];

            $dept_list = DB::connection('sqlsrv')
                ->table('DEPT')
                ->select(
                    DB::raw("RTRIM(deptCode) + ' - ' + RTRIM(deptDesc) AS NameDept"),
                    '*'
                )
                ->whereNotIn('deptDesc', $excludedDeptDesc)
                ->get();

            $excludedWardIds = [
                'IQF01',
                'IQF02',
                'IQF03',
                'IQF04',
                'IQF05',
                'IQF06',
                'IQF07',
                'IQF08',
                'IQF09',
                'IQF10',
                'IQF11',
                'IQF12',
                'IQF13',
                'IQF14',
                'IQF15',
                'IQF16',
                'IQF17',
                'IQF18',
                'IQM01',
                'IQM02',
                'IQM03',
                'IQM04',
                'IQM05',
                'IQM06',
                'IQM07',
                'IQM08',
                'IQM09',
                'IQM10',
                'IQM11',
                'IQM12',
                'IQM13',
                'IQM14',
                'IQM15',
                'IQM16',
                'IQM17',
                'IQM18',
                'IQM19',
                'IQM20',
                'IQM21',
                'IQM22',
                'IQM23',
                'IQM24',
                'IQM25',
                'IQM26',
                'IQM27',
                'IQW',
                'IQWF8',
                'IQWF7',
                'IQWF6',
                'IQWM8',
                'IQWM7',
                'IQWM6',
                'IQWM5',
                'IQWM4',
                'IQWM3',
                'IQWM2',
                'IQWM1'
            ];

            $ward_list = DB::connection('sqlsrv')
                ->table('Ward')
                ->select(
                    DB::raw("RTRIM(ward_id) + ' - ' + RTRIM(ward_name) AS Nameward"),
                    'ward_id',
                    'ward_name',
                    'UNUSES'
                )
                ->whereNotIn(DB::raw("RTRIM(ward_id)"), $excludedWardIds)
                ->where(function ($query) {
                    $query->whereNull('UNUSES')->orWhere('UNUSES', '<>', 'Y');
                })
                ->orderBy('ward_name', 'asc')
                ->get();

            // map ช้อมูลผู้ป่วยเข้ากับ treatment
            $treatments->transform(function ($item) use ($patients, $depts, $wards) {
                $hn = str_pad(trim($item->hn), 7, ' ', STR_PAD_LEFT);
                $patient = $patients[$hn] ?? null;

                // ชื่อผู้ป่วย
                $item->patient_name = (trim($patient?->titleName) ?? ' ') . ' ' . ($patient?->firstName ?? ' ') . ' ' . ($patient?->lastName ?? ' ');

                // ถ้าไม่พบ patient ใน SQL Server → fallback ไปใช้ MySQL
                if (trim($item->patient_name) === '') {
                    $mysqlPatient = DB::connection('mysql')
                        ->table('patient')
                        ->where('hn', $hn)
                        ->first();

                    if ($mysqlPatient) {
                        $item->patient_name = ($mysqlPatient->title_name ?? '') . ' ' . ($mysqlPatient->fname ?? '') . ' ' . ($mysqlPatient->lname ?? '');
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
                else {
                    $item->age = '-';
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

                // ชื่อแผนก/วอร์ด สำหรับ agency
                // ชื่อแผนก/วอร์ด - แก้ไขการดึงค่า agency code
                $agencyRaw = $item->agency ?? '';
                $agencyCode = '';
                $isDept = false;

                // ตรวจสอบกรณีที่ไม่มีข้อมูลหรือเป็น none
                if (empty($agencyRaw) || trim(strtolower($agencyRaw)) === 'none' || trim($agencyRaw) === '-') {
                    $item->agency_name = '-';
                }
                // ตรวจสอบว่าเป็น dept: หรือ ward:
                elseif (strpos($agencyRaw, 'dept:') !== false) {
                    $agencyCode = trim(str_replace('dept:', '', $agencyRaw));
                    $isDept = true;

                    // ค้นหาชื่อแผนก
                    $item->agency_name = isset($depts[$agencyCode]) ? trim($depts[$agencyCode]->deptDesc) :
                        'ไม่พบข้อมูลแผนก (Code: ' . $agencyCode . ')';
                } elseif (strpos($agencyRaw, 'ward:') !== false) {
                    $agencyCode = trim(str_replace('ward:', '', $agencyRaw));
                    $isDept = false;

                    // ค้นหาชื่อวอร์ด
                    $item->agency_name = isset($wards[$agencyCode]) ? trim($wards[$agencyCode]->ward_name) :
                        'ไม่พบข้อมูลวอร์ด (Code: ' . $agencyCode . ')';
                } else {
                    // กรณีไม่มี prefix dept: หรือ ward:
                    $agencyCode = trim($agencyRaw);

                    if (empty($agencyCode)) {
                        $item->agency_name = '-';
                    } else {
                        // ลองหาใน ward ก่อน แล้วค่อยหาใน dept
                        if (isset($wards[$agencyCode])) {
                            $item->agency_name = trim($wards[$agencyCode]->ward_name);
                        } elseif (isset($depts[$agencyCode])) {
                            $item->agency_name = trim($depts[$agencyCode]->deptDesc);
                        } else {
                            $item->agency_name = 'ไม่พบข้อมูล (Code: ' . $agencyCode . ')';
                        }
                    }
                }

                // ชื่อแผนก/วอร์ด สำหรับ forward
                // ชื่อแผนก/วอร์ด - แก้ไขการดึงค่า forward code
                $forwardRaw = $item->forward ?? '';
                $forwardCode = '';
                $isDept = false;

                // ตรวจสอบกรณีที่ไม่มีข้อมูลหรือเป็น none
                if (empty($forwardRaw) || trim(strtolower($forwardRaw)) === 'none' || trim($forwardRaw) === '-') {
                    $item->forward_name = '-';
                }
                // ตรวจสอบว่าเป็น dept: หรือ ward:
                elseif (strpos($forwardRaw, 'dept:') !== false) {
                    $forwardCode = trim(str_replace('dept:', '', $forwardRaw));
                    $isDept = true;

                    // ค้นหาชื่อแผนก
                    $item->forward_name = isset($depts[$forwardCode]) ? trim($depts[$forwardCode]->deptDesc) :
                        'ไม่พบข้อมูลแผนก (Code: ' . $forwardCode . ')';
                } elseif (strpos($forwardRaw, 'ward:') !== false) {
                    $forwardCode = trim(str_replace('ward:', '', $forwardRaw));
                    $isDept = false;

                    // ค้นหาชื่อวอร์ด
                    $item->forward_name = isset($wards[$forwardCode]) ? trim($wards[$forwardCode]->ward_name) :
                        'ไม่พบข้อมูลวอร์ด (Code: ' . $forwardCode . ')';
                } else {
                    // กรณีไม่มี prefix dept: หรือ ward:
                    $forwardCode = trim($forwardRaw);

                    if (empty($forwardCode)) {
                        $item->forward_name = '-';
                    } else {
                        // ลองหาใน ward ก่อน แล้วค่อยหาใน dept
                        if (isset($wards[$forwardCode])) {
                            $item->forward_name = trim($wards[$forwardCode]->ward_name);
                        } elseif (isset($depts[$forwardCode])) {
                            $item->forward_name = trim($depts[$forwardCode]->deptDesc);
                        } else {
                            $item->forward_name = 'ไม่พบข้อมูล (Code: ' . $forwardCode . ')';
                        }
                    }
                }

                return $item;
            });

            $totalPages = ceil($total / $perPage);
            $startNum = ($page - 1) * $perPage + 1;
            $endNum = min($total, $page * $perPage);

            return view('fragments.treatments', compact('treatments', 'totalPages', 'page', 'perPage', 'dept_list', 'ward_list', 'total', 'startNum', 'endNum'));
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
        $titleName = $request->input('titleName');

        // ตรวจสอบผู้ป่วยซ้ำ
        $existing = DB::connection('mysql')->table('patient')
            ->where([
                ['fname', $fname],
                ['lname', $lname],
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

    // แสดงหน้า dashboard
    public function showDashboard(Request $request)
    {
        $targetDoc = [' 21116', ' 22947', ' 26587', ' 33166', ' 34559', ' 37288', ' 36155', ' 34916'];

        //  วันที่ที่ใช้ฟิลเตอร์
        $selected = $request->filled('dateFilter')
            ? \Carbon\Carbon::parse($request->input('dateFilter'))   // ค่าจาก input type=date = YYYY-MM-DD
            : \Carbon\Carbon::now();

        // แปลงเป็นรูปแบบปี พ.ศ. สำหรับช่อง appoint_date (เช่น 25680813)
        $selectedThai = ($selected->year + 543) . $selected->format('md');

        // ใช้เวลา "ตอนนี้" เฉพาะกรณีเลือกเป็น 'วันนี้' เพื่อคำนวณ เสร็จสิ้น/กำลังรอ
        $nowTime = \Carbon\Carbon::now()->format('H:i');
        $cutTime = $selected->isToday() ? $nowTime : '23:59';

        // ── นับจำนวนรายการนัดของ "วันที่เลือก" ───────────────────────────────
        $todayCount = DB::connection('sqlsrv')
            ->table('Appoint')
            ->whereIn('doctor', $targetDoc)
            ->where('appoint_date', $selectedThai)
            ->where('appoint_dept', '=', '111')
            ->count();

        $doneCount = DB::connection('sqlsrv')
            ->table('Appoint')
            ->whereIn('doctor', $targetDoc)
            ->where('appoint_date', $selectedThai)
            ->where('appoint_dept', '=', '111');

        if ($selected->isToday()) {
            $doneCount->where('appoint_time_to', '<', $cutTime);
        } elseif ($selected->isPast()) {
            $doneCount->where('appoint_time_to', '<=', '23:59');
        } else {
            $doneCount->whereRaw('1=0');
        }
        $doneCount = $doneCount->count();

        $waitingCount = DB::connection('sqlsrv')
            ->table('Appoint')
            ->whereIn('doctor', $targetDoc)
            ->where('appoint_date', $selectedThai)
            ->where('appoint_time_to', '>=', $cutTime)
            ->where('appoint_dept', '=', '111')
            ->count();

        // ── ข้อมูลการนัด (MySQL) ของ "วันที่เลือก" ─────────────────────────────
        $mysql_appointment = DB::connection('mysql')
            ->table('appointment')
            ->where('a_date', '=', $selected->toDateString()) // YYYY-MM-DD
            ->orderBy('a_time', 'ASC')
            ->get()
            ->map(function ($item) {
                return (object)[
                    'hn'     => str_pad($item->hn, 7, ' ', STR_PAD_LEFT),
                    'date'   => $item->a_date,   // YYYY-MM-DD
                    'time'   => $item->a_time,
                    'ward'   => $item->ward,
                    'doctor' => str_pad($item->doc_id, 6, ' ', STR_PAD_LEFT),
                    'note'   => $item->note,
                    'source' => 'สมุดบันทึก',
                ];
            });

        // เตรียมข้อมูล mapping ต่างๆ (เหมือนเดิม)
        $hns = $mysql_appointment->pluck('hn')->unique()->toArray();
        $doctorIDs = $mysql_appointment->pluck('doctor')->unique()->toArray();

        $patients = DB::connection('sqlsrv')
            ->table('PATIENT')
            ->leftJoin('PTITLE', 'PATIENT.titleCode', '=', 'PTITLE.titleCode')
            ->whereIn('hn', $hns)
            ->get()
            ->mapWithKeys(function ($item) {
                $fullName = trim($item->titleName) . ' ' . trim($item->firstName) . ' ' . trim($item->lastName);
                return [$item->hn => $fullName];
            });

        $doctors = DB::connection('sqlsrv')
            ->table('DOCC')
            ->whereIn('docCode', $doctorIDs)
            ->get()
            ->mapWithKeys(function ($item) {
                $fullName = trim($item->doctitle) . ' ' . trim($item->docName) . ' ' . trim($item->docLName);
                return [$item->docCode => $fullName];
            });

        $mysqlPatient = DB::connection('mysql')
            ->table('patient')
            ->whereIn('hn', $hns)
            ->get()
            ->mapWithKeys(function ($item) {
                $fullName = $item->title_name . ' ' . $item->fname . ' ' . $item->lname;
                return [$item->hn => $fullName];
            });

        $depts = DB::connection('sqlsrv')
            ->table('DEPT')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->deptCode => $item->deptDesc];
            });

        $wards = DB::connection('sqlsrv')
            ->table('Ward')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->ward_id => $item->ward_name];
            });

        // รวมนัดจาก MySQL + แต่งชื่อ/วอร์ด
        $combinedAppointments = $mysql_appointment->map(function ($item) use ($patients, $mysqlPatient, $doctors, $depts, $wards) {
            $patientName = $patients[$item->hn] ?? ($mysqlPatient[$item->hn] ?? 'ไม่ระบุ');
            $doctorName  = $doctors[$item->doctor] ?? 'ไม่ระบุ';

            // mapping ward/dept
            if (str_starts_with($item->ward ?? '', 'dept:')) {
                $code = substr($item->ward, 5);
                $code = str_pad($code, 6, ' ', STR_PAD_RIGHT);
                $wardDisplay = $depts[$code] ?? $item->ward;
            } elseif (str_starts_with($item->ward ?? '', 'ward:')) {
                $code = substr($item->ward, 5);
                $code = str_pad($code, 6, ' ', STR_PAD_RIGHT);
                $wardDisplay = $wards[$code] ?? $item->ward;
            } else {
                $wardDisplay = $item->ward ?? 'ไม่ระบุ';
            }

            return (object)[
                'hn'           => $item->hn,
                'patient_name' => $patientName,
                'date'         => \Carbon\Carbon::parse($item->date)->locale('th')->translatedFormat('j F') . ' ' . (\Carbon\Carbon::parse($item->date)->year + 543),
                'time'         => $item->time,
                'doctor'       => $doctorName,
                'ward'         => $wardDisplay,
                'note'         => $item->note ?? 'ไม่มีหมายเหตุ',
                'source'       => $item->source,
            ];
        });

        // ── ข้อมูลการนัด (SQL Server) ของ "วันที่เลือก" ────────────────────────
        $sql_appointment = DB::connection('sqlsrv')
            ->table('Appoint')
            ->leftJoin('PATIENT', 'Appoint.hn', '=', 'PATIENT.hn')
            ->leftJoin('PTITLE', 'PATIENT.titleCode', '=', 'PTITLE.titleCode')
            ->leftJoin('DOCC', 'Appoint.doctor', '=', 'DOCC.docCode')
            ->leftJoin('DEPT', 'Appoint.pre_dept_code', '=', 'DEPT.deptCode')
            ->whereIn('doctor', $targetDoc)
            ->where('appoint_date', '=', $selectedThai)
            ->where('appoint_dept', '111')
            ->orderBy('appoint_time_from', 'ASC')
            ->get()
            ->map(function ($item) {
                $thDate = strval($item->appoint_date);           // e.g. '25680723'
                $year   = intval(substr($thDate, 0, 4)) - 543;    // 2025
                $monthDay = substr($thDate, 4);                   // '0723'
                $dateEn  = $year . $monthDay;                     // '20250723'

                return (object)[
                    'hn'           => $item->hn,
                    'patient_name' => trim($item->titleName) . ' ' . trim($item->firstName) . ' ' . trim($item->lastName),
                    'date'         => \Carbon\Carbon::createFromFormat('Ymd', $dateEn)->locale('th')->translatedFormat('j F') . ' ' . (\Carbon\Carbon::createFromFormat('Ymd', $dateEn)->year + 543),
                    'time'         => $item->appoint_time_from . '-' . $item->appoint_time_to,
                    'doctor'       => trim($item->doctitle) . ' ' . trim($item->docName) . ' ' . trim($item->docLName),
                    'ward'         => trim($item->deptDesc) ?? 'ไม่ระบุ',
                    'pt_status'    => ($item->pt_status === 'I') ? 'IPD' : (($item->pt_status === 'O') ? 'OPD' : 'Discharge'),
                    'source'       => 'homc',
                ];
            });

        // รวม & จัดกลุ่ม
        $allAppointments = $combinedAppointments->merge($sql_appointment)->sortBy('time');
        $appointmentsByDoctor = $allAppointments->groupBy('doctor');

        // 🔸 Upcoming: จะอิงจาก "วันนี้จริง" ตามเดิม (ถ้าอยากอิงจากวันที่ที่เลือก ค่อยเปลี่ยนได้)
        $today = \Carbon\Carbon::now();
        $futureStart = $today->copy()->addDay();
        $futureEnd   = $today->copy()->addDays(7);
        $startThai = ($futureStart->year + 543) . $futureStart->format('md');
        $endThai   = ($futureEnd->year + 543) . $futureEnd->format('md');

        $upcoming = DB::connection('sqlsrv')
            ->table('Appoint')
            ->leftJoin('PATIENT', 'Appoint.hn', '=', 'PATIENT.hn')
            ->leftJoin('PTITLE', 'PATIENT.titleCode', '=', 'PTITLE.titleCode')
            ->whereIn('doctor', $targetDoc)
            ->whereBetween('appoint_date', [$startThai, $endThai])
            ->orderBy('appoint_date')
            ->orderBy('appoint_time_from')
            ->limit(5)
            ->get();

        foreach ($upcoming as $u) {
            try {
                $thDate = strval($u->appoint_date);
                $time   = $u->appoint_time_from;
                $year   = intval(substr($thDate, 0, 4)) - 543;
                $monthDay = substr($thDate, 4);
                $dateTimeStr = $year . $monthDay . ' ' . $time;

                $carbon = \Carbon\Carbon::createFromFormat('Ymd H:i', $dateTimeStr);
                $u->date_human  = $carbon->locale('th')->diffForHumans(\Carbon\Carbon::now()->startOfDay(), ['options' => \Carbon\Carbon::ONE_DAY_WORDS]);
                $u->time        = $carbon->format('H:i');
                $u->fullname    = $u->titleName . $u->firstName . ' ' . $u->lastName;
                $u->service_name = $u->service ?? 'ไม่ระบุ';
            } catch (\Exception $e) {
                $u->date_human = '-';
                $u->time = '-';
                $u->fullname = '-';
                $u->service_name = '-';
            }
        }

        return view('dashboard', compact(
            'appointmentsByDoctor',
            'upcoming',
            'todayCount',
            'doneCount',
            'waitingCount',

        ));
    }


    // แสดงหน้า report
    public function showReport()
    {
        $targetDoc = [' 21116', ' 22947', ' 26587', ' 33166', ' 34559', ' 37288', ' 36155', ' 34916'];
        $today = Carbon::now();
        $todayThai = ($today->year + 543) . $today->format('md');

        $mysql_appointment = DB::connection('mysql')
            ->table('appointment')
            ->where('a_date', '=', $today->toDateString())
            ->orderBy('a_time', 'ASC')
            ->get()
            ->map(function ($item) {
                return (object)[
                    'hn' => str_pad($item->hn, 7, ' ', STR_PAD_LEFT),
                    'date' => $item->a_date,
                    'time' => $item->a_time,
                    'doctor' => str_pad($item->doc_id, 6, ' ', STR_PAD_LEFT),
                    'source' => 'สมุดบันทึก'
                ];
            });

        //เตรียมข้อมูล
        $hns = $mysql_appointment->pluck('hn')->unique()->toArray();
        $doctorIDs = $mysql_appointment->pluck('doctor')->unique()->toArray();

        // ดึงข้อมูลผู้ป่วยจาก SQL Server
        $patients = DB::connection('sqlsrv')
            ->table('PATIENT')
            ->leftJoin('PTITLE', 'PATIENT.titleCode', '=', 'PTITLE.titleCode')
            ->whereIn('hn', $hns)
            ->get()
            ->mapWithKeys(function ($item) {
                $fullName = trim($item->titleName) . ' ' . trim($item->firstName) . ' ' . trim($item->lastName);
                return [$item->hn => $fullName];
            });

        // ดึงข้อมูลแพทย์จาก SQL Server
        $doctors = DB::connection('sqlsrv')
            ->table('DOCC')
            ->whereIn('docCode', $doctorIDs)
            ->get()
            ->mapWithKeys(function ($item) {
                $fullName = trim($item->doctitle) . ' ' . trim($item->docName) . ' ' . trim($item->docLName);
                return [$item->docCode => $fullName];
            });

        // รวมข้อมูลนัดหมายจาก MySQL และ SQL Server
        $combinedAppointments = $mysql_appointment->map(function ($item) use ($patients, $doctors) {
            return (object)[
                'hn' => $item->hn,
                'patient_name' => $patients[$item->hn] ?? 'ไม่ระบุ',
                'date' => \Carbon\Carbon::parse($item->date)->locale('th')->translatedFormat('j F Y'),
                'time' => $item->time,
                'doctor' => $doctors[$item->doctor] ?? 'ไม่ระบุ',
                'source' => $item->source
            ];
        });


        $sql_appointment = DB::connection('sqlsrv')
            ->table('Appoint')
            ->leftJoin('PATIENT', 'Appoint.hn', '=', 'PATIENT.hn')
            ->leftJoin('PTITLE', 'PATIENT.titleCode', '=', 'PTITLE.titleCode')
            ->leftJoin('DOCC', 'Appoint.doctor', '=', 'DOCC.docCode') // ถ้าต้องการ doctor name
            ->whereIn('doctor', $targetDoc)
            ->where('appoint_date', '=', $todayThai)
            ->orderBy('appoint_time_from', 'ASC')
            ->get()
            ->map(function ($item) {
                $thDate = strval($item->appoint_date);     // '25680723'
                $year = intval(substr($thDate, 0, 4)) - 543; // 2025
                $monthDay = substr($thDate, 4);              // '0723'
                $dateEn = $year . $monthDay;
                return (object)[
                    'hn' => $item->hn,
                    'patient_name' => trim($item->titleName) . ' ' . trim($item->firstName) . ' ' . trim($item->lastName),
                    'date' => \Carbon\Carbon::createFromFormat('Ymd', $dateEn)
                        ->locale('th')
                        ->translatedFormat('j F Y'),
                    'time' => $item->appoint_time_from . '-' . $item->appoint_time_to,
                    'doctor' => trim($item->doctitle) . ' ' . trim($item->docName) . ' ' . trim($item->docLName),
                    'source' => 'homc'
                ];
            });
        $allAppointments = $combinedAppointments->merge($sql_appointment)
            ->sortBy('time');
        $appointmentsByDoctor = $allAppointments->groupBy('doctor');
        return view('report', compact('appointmentsByDoctor', 'allAppointments'));
    }
}
