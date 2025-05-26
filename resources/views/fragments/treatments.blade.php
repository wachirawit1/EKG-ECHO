@php
    $data = $treatments ?? [];

    // วันที่ไทย
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
@endphp


<div class="d-flex justify-content-md-center align-items-center d-grid gap-2 ">
    <form id="searchTreatForm" onsubmit="searchTreatments(event)" class="d-flex gap-2">
        <input type="date" class="form-control" name="start_date" id="start_date" onkeydown="return false"> -
        <input type="date" class="form-control" name="end_date" id="end_date" onkeydown="return false">
        <div class="input-group">
            <input class="form-control" type="search" name="hn" placeholder="ค้นหา HN..."
                value="{{ request('hn') }}">
            <button class="btn btn-primary" type="submit">ค้นหา</button>
        </div>
    </form>

    <div class="btn-group">
        {{-- modal trigger --}}
        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addTreatment">
            <i class="bi bi-plus-circle"></i> เพิ่มการตรวจ EKG
        </button>


        {{-- Add new patient --}}
        <button type="button" class="btn btn-outline-success" data-bs-toggle="modal" data-bs-target="#addPatient">
            เพิ่มคนไข้นอก
        </button>
    </div>

</div>

@include('modal.addpatient')
@include('modal.treatadd')

@if (request('hn') || (request('start_date') && request('end_date')))
    <span class="my-2">
        ผลการค้นหา
        @if (request('hn'))
            HN: "{{ request('hn') }}"
        @endif
        @if (request('start_date') && request('end_date'))
            ช่วงวันที่: {{ request('start_date') }} ถึง {{ request('end_date') }}
        @endif
        ทั้งหมด {{ count($data) }} รายการ
    </span>
    <span class="ms-auto"><a href="javascript:void(0)" id="resetSearch">คืนค่า</a></span>
@endif

<table class="table table-hover table-striped  border ">
    <div class="table-responsive">

        @if ($treatments->count() === 0)
            <div class="alert alert-warning text-center">ไม่พบข้อมูลการรักษา</div>
        @else
            <table class="table table-hover table-bordered align-middle">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>วันที่รักษา</th>
                        <th>HN</th>
                        <th>ชื่อ-นามสกุล</th>
                        <th>อายุ</th>
                        <th>หน่วยงาน</th>
                        <th>ส่งต่อ</th>
                        <th colspan="2" class="text-center">การจัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($treatments as $index => $item)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ formatThaiDate($item->t_date) }}</td>
                            <td>{{ $item->hn }}</td>
                            <td>{{ $item->patient_name }}</td>
                            <td>{{ $item->age_text }}</td>
                            <td>{{ $item->dept_name }}</td>
                            <td>{{ $item->dept_forward }}</td>
                            <td class="text-center">
                                <!-- MOdal button !-->
                                <button class="btn btn-sm btn-outline-info" data-bs-toggle="modal"
                                    data-bs-target="#treatmentInfo{{ $item->t_id }}">ดู</button>

                                <!-- Modal info -->
                                <div class="modal fade text-start" id="treatmentInfo{{ $item->t_id }}"
                                    tabindex="-1">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content">

                                            <!-- Modal Header -->
                                            <div class="modal-header">
                                                <h5 class="modal-title fw-bold"
                                                    id="treatmentInfoLabel{{ $item->t_id }}">
                                                    ข้อมูลผู้ป่วย</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                    aria-label="ปิด"></button>
                                            </div>

                                            <!-- Modal Body -->
                                            <div class="modal-body">
                                                <button type="button"
                                                    class="d-flex my-2 ms-auto btn btn-warning edit-btn"
                                                    data-id="{{ $item->t_id }}">แก้ไข</button>
                                                <form id="treatmentInfoForm{{ $item->t_id }}">
                                                    <div class="row">
                                                        <div class="col mb-3">
                                                            <label for="hn{{ $item->t_id }}"
                                                                class="form-label">HN</label>
                                                            <input type="text" class="form-control"
                                                                id="hn{{ $item->t_id }}" name="hn"
                                                                value="{{ trim($item->hn) . ' - ' . $item->patient_name }}"
                                                                readonly>
                                                        </div>

                                                        <div class="col mb-3">
                                                            <label for="age{{ $item->t_id }}"
                                                                class="form-label">อายุ</label>
                                                            <input class="form-control" id="age{{ $item->t_id }}"
                                                                name="age" value="{{ $item->age_text }}" readonly>
                                                        </div>
                                                    </div>
                                                    {{-- <div class="mb-3">
                                                        <label for="address{{ $item->t_id }}"
                                                            class="form-label">ที่อยู่</label>
                                                        <textarea class="form-control" id="address{{ $item->t_id }}" name="address" rows="2" readonly>{{ $item->address }}</textarea>
                                                    </div> --}}

                                                    <div class="mb-3">
                                                        <label class="form-label"
                                                            for="a_date_text{{ $item->t_id }}">วันที่รักษา</label>
                                                        <div class="input-group">

                                                            <input class="form-control"
                                                                id="a_date_text{{ $item->t_id }}"
                                                                value="{{ formatThaiDate($item->t_date) }}" readonly>

                                                            <input type="date" class="form-control"
                                                                id="a_date{{ $item->t_id }}" name="a_date"
                                                                value="{{ $item->t_date }}" readonly hidden>


                                                        </div>
                                                    </div>


                                                </form>
                                            </div>

                                            <!-- Modal Footer -->
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary"
                                                    data-bs-dismiss="modal">ปิด</button>
                                                <button type="button" class="btn btn-primary save-btn"
                                                    data-id="{{ $item->t_id }}" disabled>
                                                    บันทึกข้อมูล
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </td>
                            <td class="text-center">
                                <!-- ปุ่มลบ -->
                                <button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal"
                                    data-bs-target="#deleteAppointment{{ $item->t_id }}">
                                    ลบ
                                </button>

                                <!-- Modal Delete -->
                                <div class="modal fade" id="deleteAppointment{{ $item->t_id }}" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <form action="{{ route('treatment.delete', $item->t_id) }}"
                                                method="POST">
                                                @csrf
                                                @method('DELETE')
                                                <div class="modal-header">
                                                    <h5 class="modal-title">ยืนยันการลบ</h5>
                                                    <button type="button" class="btn-close"
                                                        data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    ต้องการลบประวัติการรักษา <strong>HN:
                                                        {{ $item->hn . ' วันที่: ' . $item->t_date }}</strong>
                                                    หรือไม่?
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary"
                                                        data-bs-dismiss="modal">ยกเลิก</button>
                                                    <button type="submit" class="btn btn-danger">ลบ</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                <!-- End Modal -->
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif


    </div>
    {{-- pagination --}}
    @if ($totalPages > 1)
        <nav aria-label="Page navigation example">
            <ul class="pagination justify-content-start">
                {{-- ปุ่ม Previous --}}
                <li class="page-item {{ $page == 1 ? 'disabled' : '' }}">
                    <button class="page-link page-btn" data-page="{{ $page - 1 }}"
                        {{ $page == 1 ? 'disabled' : '' }}>Previous</button>
                </li>

                {{-- ลูปเลขหน้าแบบฉลาด --}}
                @php
                    $start = max(1, $page - 2);
                    $end = min($totalPages, $page + 2);
                @endphp

                @if ($start > 1)
                    <li class="page-item"><button class="page-link page-btn" data-page="1">1</button></li>
                    @if ($start > 2)
                        <li class="page-item disabled"><button class="page-link" disabled>...</button></li>
                    @endif
                @endif

                @for ($i = $start; $i <= $end; $i++)
                    <li class="page-item {{ $page == $i ? 'active' : '' }}">
                        <button class="page-link page-btn"
                            data-page="{{ $i }}">{{ $i }}</button>
                    </li>
                @endfor

                @if ($end < $totalPages)
                    @if ($end < $totalPages - 1)
                        <li class="page-item disabled"><button class="page-link" disabled>...</button></li>
                    @endif
                    <li class="page-item"><button class="page-link page-btn"
                            data-page="{{ $totalPages }}">{{ $totalPages }}</button></li>
                @endif

                {{-- ปุ่ม Next --}}
                <li class="page-item {{ $page == $totalPages ? 'disabled' : '' }}">
                    <button class="page-link page-btn" data-page="{{ $page + 1 }}"
                        {{ $page == $totalPages ? 'disabled' : '' }}>Next</button>
                </li>
            </ul>
        </nav>
    @endif
</table>
