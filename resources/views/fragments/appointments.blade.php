@php
    $data = $appointments ?? [];

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



<div class="d-flex justify-content-md-center align-items-center d-grid gap-2">

    {{-- Section: Search & Add --}}
    <form id="searchForm" class="d-flex gap-2" onsubmit="searchAppointments(event)">

        <input  class="form-control" id="start_date" name="start_date" value="{{ request('start_date') }}"
            placeholder="วันที่เริ่ม"> -

        <input class="form-control" id="end_date" name="end_date" value="{{ request('end_date') }}"
            placeholder="วันที่สิ้นสุด">

        <select class="form-select" name="doc_id" id="doc_id">
            <option value="">ค้นหาด้วยหมอ</option>
            <option value="none" {{ request('doc_id') == 'none' ? 'selected' : '' }}>ไม่มี</option>
            @foreach ($doc as $d)
                <option value="{{ trim($d->docCode) }}" {{ request('doc_id') == trim($d->docCode) ? 'selected' : '' }}>
                    {{ $d->doctitle }}{{ $d->docName }} {{ $d->docLName }}
                </option>
            @endforeach
        </select>

        <div class="input-group">
            <input class="form-control" type="search" name="hn" placeholder="ค้นหา HN..."
                value="{{ request('hn') }}">
            <button class="btn btn-teal" type="submit">
                <i class="bi bi-search"></i> ค้นหา
            </button>
        </div>

    </form>

    <!-- Add Appointment Modal -->
    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addAppointment">
        <i class="bi bi-plus-circle"></i> เพิ่มนัดหมาย
    </button>



    @include('modal.appadd')




</div>

@if (request('hn') || ((request('start_date') && request('end_date')) || request('doc_id')))
    <div class="bg-light border rounded p-3 my-3 d-flex justify-content-between align-items-center flex-wrap">
        <div class="d-flex flex-wrap align-items-center gap-2">
            <strong class="text-primary me-2">
                <i class="fas fa-search me-1"></i>
                ผลการค้นหา:
            </strong>

            @if (request('hn'))
                <span class="badge bg-primary">HN: {{ request('hn') }}</span>
            @endif

            @if (request('start_date') && request('end_date'))
                <span class="badge bg-success">
                    {{ date('d/m/Y', strtotime(request('start_date'))) }} -
                    {{ date('d/m/Y', strtotime(request('end_date'))) }}
                </span>
            @endif

            @if (request('doc_id') && request('doc_id') != '')
                @php
                    $selectedDoc = $doc->firstWhere('docCode', request('doc_id'));
                @endphp
                <span class="badge bg-warning text-dark">
                    {{ $selectedDoc ? $selectedDoc->doctitle . ' ' . $selectedDoc->docName . ' ' . $selectedDoc->docLName : 'ไม่มี' }}
                </span>
            @endif

            <span class="badge bg-secondary">{{ $total }} รายการ</span>
        </div>

        <a href="javascript:void(0)" class="btn btn-outline-danger btn-sm mt-2 mt-md-0" id="resetSearch">
            <i class="fas fa-times me-1"></i>
            คืนค่า
        </a>
    </div>
@endif

<table class="table table-hover table-striped border ">
    <div class="table-responsive">

        {{-- Section: Table --}}
        @if ($appointments->count() === 0)
            <div class="alert alert-warning text-center mt-3">ไม่พบข้อมูลการนัดวันนี้</div>
        @else
            <table class="table table-hover table-bordered align-middle">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>วันที่นัด</th>
                        <th>HN</th>
                        <th>ชื่อ-นามสกุล</th>
                        <th>วอร์ด</th>
                        <th>แพทย์</th>
                        <th>หมายเหตุ</th>
                        <th colspan="2" class="text-center">การจัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($data as $index => $item)
                        <tr>
                            <td>{{ ($page - 1) * $perPage + $index + 1 }}</td>
                            <td>{{ formatThaiDate($item->a_date) }}</td>
                            <td>{{ $item->hn }}</td>
                            <td>{{ $item->patient_name ?? '-' }}</td>
                            <td>{{ $item->dept_name }}</td>
                            <td>{{ $item->doctor_name }}</td>
                            <td>{{ $item->note ?? '-' }}</td>
                            <td class="text-center">
                                <!-- MOdal button !-->
                                <button class="btn btn-sm btn-outline-info" data-bs-toggle="modal"
                                    data-bs-target="#patientInfo{{ $item->a_id }}">ดู</button>


                                <!-- Modal info -->
                                <div class="modal fade text-start" id="patientInfo{{ $item->a_id }}" tabindex="-1">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content">
                                            <!-- Modal Header -->
                                            <div class="modal-header">
                                                <h5 class="modal-title fw-bold"
                                                    id="patientInfoLabel{{ $item->a_id }}">
                                                    ข้อมูลผู้ป่วย
                                                </h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                    aria-label="ปิด"></button>
                                            </div>

                                            <!-- Modal Body -->
                                            <div class="modal-body">
                                                <div class="d-flex justify-content-end align-items-center gap-2 my-2">
                                                    <button type="button" class="btn btn-info print-btn"
                                                        data-id="{{ $item->a_id }}">
                                                        <i class="fas fa-print me-1"></i>พิมพ์ PDF
                                                    </button>
                                                    <button type="button" class="btn btn-warning edit-btn"
                                                        data-id="{{ $item->a_id }}">
                                                        <i class="fas fa-edit me-1"></i>แก้ไข
                                                    </button>
                                                </div>

                                                <!-- Patient Info Form -->
                                                <form id="patientInfoForm{{ $item->a_id }}">
                                                    <div class="row">
                                                        <div class="col-8 mb-3">
                                                            <label for="hn{{ $item->a_id }}"
                                                                class="form-label fw-bold">HN</label>
                                                            <input type="text" class="form-control"
                                                                id="hn{{ $item->a_id }}" name="hn"
                                                                value="{{ trim($item->hn) . ' - ' . $item->patient_name . ($item->hospital_name ?? '') }}"
                                                                readonly>
                                                        </div>

                                                        <div class="col mb-3">
                                                            <label for="age{{ $item->a_id }}"
                                                                class="form-label fw-bold">อายุ</label>
                                                            <input class="form-control" id="age{{ $item->a_id }}"
                                                                name="age" value="{{ $item->age }}" readonly>
                                                        </div>
                                                    </div>

                                                    <div class="mb-3">
                                                        <label for="address{{ $item->a_id }}"
                                                            class="form-label fw-bold">ที่อยู่</label>
                                                        <textarea class="form-control" id="address{{ $item->a_id }}" name="address" rows="2" readonly>{{ $item->address }}</textarea>
                                                    </div>

                                                    <div class="mb-3">
                                                        <label class="form-label fw-bold"
                                                            for="a_date_text{{ $item->a_id }}">วันที่นัด</label>
                                                        <div class="input-group">
                                                            <input class="form-control"
                                                                id="a_date_text{{ $item->a_id }}"
                                                                value="{{ formatThaiDate($item->a_date) }}" readonly>

                                                            <input type="date" class="form-control"
                                                                id="a_date{{ $item->a_id }}" name="a_date"
                                                                value="{{ $item->a_date }}" readonly hidden>

                                                            <input type="text" id="a_time_text{{ $item->a_id }}"
                                                                class="form-control" value="{{ $item->a_time }}"
                                                                readonly>

                                                            @php
                                                                $parts = explode('-', $item->a_time);
                                                                $start = $parts[0] ?? '';
                                                                $end = $parts[1] ?? '';
                                                            @endphp
                                                            <input type="text" class="form-control"
                                                                id="a_time_start{{ $item->a_id }}"
                                                                name="a_time_start" value="{{ $start }}"
                                                                hidden>
                                                            <input type="text" class="form-control"
                                                                id="a_time_end{{ $item->a_id }}" name="a_time_end"
                                                                value="{{ $end }}" hidden>
                                                        </div>
                                                    </div>

                                                    <div class="mb-3">
                                                        <label for="tel{{ $item->a_id }}"
                                                            class="form-label fw-bold">เบอร์ติดต่อ</label>
                                                        <textarea class="form-control" id="tel{{ $item->a_id }}" name="tel" rows="2" readonly>{{ $item->tel }}</textarea>
                                                    </div>

                                                    <div class="mb-3">
                                                        <label for="note{{ $item->a_id }}"
                                                            class="form-label fw-bold">หมายเหตุ</label>
                                                        <textarea class="form-control" name="note" id="note{{ $item->a_id }}" rows="3" readonly>{{ $item->note }}</textarea>
                                                    </div>
                                                </form>
                                            </div>

                                            <!-- Modal Footer -->
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary"
                                                    data-bs-dismiss="modal">ปิด</button>
                                                <button type="button" class="btn btn-primary save-btn"
                                                    data-id="{{ $item->a_id }}" disabled>
                                                    <i class="fas fa-save me-1"></i>บันทึกข้อมูล
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <td class="text-center">
                                <!-- ปุ่มลบ -->
                                <button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal"
                                    data-bs-target="#deleteAppointment{{ $item->a_id }}">
                                    ลบ
                                </button>

                                <!-- Modal Delete -->
                                <div class="modal fade" id="deleteAppointment{{ $item->a_id }}" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <form action="{{ route('app.delete', $item->a_id) }}" method="POST">
                                                @csrf
                                                @method('DELETE')
                                                <div class="modal-header">
                                                    <h5 class="modal-title">ยืนยันการลบ</h5>
                                                    <button type="button" class="btn-close"
                                                        data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    ต้องการลบนัดหมายของ <strong>HN:
                                                        {{ $item->hn . ' - ' . $item->patient_name }}</strong>
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
    <x-pagination :page="$page" :totalPages="$totalPages" :startNum="$startNum" :endNum="$endNum" :total="$total" />

</table>
