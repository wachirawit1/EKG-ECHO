@extends('layout')
@section('title', 'ค้นหาผู้ป่วย')
@section('content')
    <div class="row justify-content-center mt-4">
        <div class="col-md-8">

            <!-- Search box -->
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="card-title mb-3 text-center">ค้นหาผู้ป่วย</h5>
                    <input type="search" id="patientSearch" class="form-control" placeholder="กรอก HN, ชื่อ-สกุล">
                    <div id="loading" class="text-center mt-2" style="display: none;">
                        <small class="text-muted">กำลังค้นหา...</small>
                    </div>
                </div>
            </div>

            <!-- Results -->
            <div id="searchResults" class="mt-4"></div>

        </div>
    </div>
@endsection

@push('patinetScript')
    <script>
        $(document).ready(function() {
            let searchTimeout;
            let currentRequest;

            $('#patientSearch').on('input', function() {
                let query = $(this).val().trim();
                let $results = $('#searchResults');
                let $loading = $('#loading');

                // ล้าง timeout เดิม
                clearTimeout(searchTimeout);

                // ยกเลิก request เดิม
                if (currentRequest) {
                    currentRequest.abort();
                }

                $results.html("");
                $loading.hide();

                if (query.length < 2) {
                    $results.html(
                        `<div class="alert alert-secondary text-center">พิมพ์อย่างน้อย 2 ตัวอักษร</div>`
                    );
                    return;
                }

                // รอ 300ms หลังจากหยุดพิมพ์
                searchTimeout = setTimeout(function() {
                    $loading.show();

                    currentRequest = $.ajax({
                        url: '/api/patient/search',
                        method: 'GET',
                        data: {
                            query: query
                        },
                        success: function(data) {
                            $loading.hide();

                            if (data.length === 0) {
                                $results.html(
                                    `<div class="alert alert-warning text-center">ไม่พบผู้ป่วย</div>`
                                );
                            } else {
                                // กรองข้อมูลซ้ำ
                                let uniquePatients = [];
                                let seenHN = new Set();

                                data.forEach(function(patient) {
                                    if (!seenHN.has(patient.hn)) {
                                        seenHN.add(patient.hn);
                                        uniquePatients.push(patient);
                                    }
                                });

                                let html = '';
                                uniquePatients.forEach(function(p) {
                                    html += `
                                <div class="card shadow-sm mb-3">
                                    <div class="card-body">
                                        <h6 class="text-primary">HN: ${p.hn || 'N/A'}</h6>
                                        <h5 class="card-title">${(p.firstName || '') + ' ' + (p.lastName || '')}</h5>
                                        <p class="card-text text-muted">
                                            อายุ: ${p.birthDay || '-'} | ${p.sex || '-'} <br>
                                            Visit ล่าสุด: ${p.last_visit || '-'}
                                        </p>
                                    </div>
                                </div>`;
                                });
                                $results.html(html);
                            }
                        },
                        error: function(xhr, status, error) {
                            $loading.hide();
                            if (status !== 'abort') {
                                $results.html(
                                    `<div class="alert alert-danger text-center">เกิดข้อผิดพลาด</div>`
                                );
                            }
                        }
                    });
                }, 300);
            });
        });
    </script>
@endpush
