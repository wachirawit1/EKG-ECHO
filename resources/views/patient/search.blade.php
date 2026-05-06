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

@push('patientScript')
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
                                let html = '';
                                data.forEach(function(p) {
                                    let hn = p.hn || 'N/A';
                                    let fullName = (p.firstName || '') + ' ' + (
                                        p.lastName || '');
                                    let birthDay = p.birthDayFormatted || '-';
                                    let sex = p.sex || '-';
                                    let lastVisit = p.regNo || '-';
                                    let cardId = p.CardID || '-';
                                    let tel = p.tel || 'ไม่ระบุ';
                                    let sourceColor = p.source === 'HIS' ?
                                        'primary' : 'success';
                                    let hospital = p.hospital_name || 'ไม่ระบุ';

                                    html += `
                                        <div class="card shadow-sm mb-3 border-start border-4 border-${sourceColor}">
                                            <div class="card-body">
                                                <div class="d-flex justify-content-between align-items-start mb-2">
                                                    <div>
                                                        <span class="badge bg-${sourceColor} mb-2">${hospital}</span>
                                                        <h6 class="text-secondary mb-1">HN: ${hn}</h6>
                                                        <h4 class="card-title fw-bold text-dark mb-1">${fullName}</h4>
                                                    </div>
                                                    <div class="text-end">
                                                        <a href="tel:${tel}" class="btn btn-sm btn-outline-primary rounded-pill">
                                                            <i class="fas fa-phone-alt me-1"></i> ${tel}
                                                        </a>
                                                    </div>
                                                </div>
                                                <div class="row g-2 mt-2 pt-2 border-top">
                                                    <div class="col-6 col-md-4">
                                                        <small class="text-muted d-block">เลขบัตรประชาชน</small>
                                                        <span class="small fw-bold">${cardId}</span>
                                                    </div>
                                                    <div class="col-6 col-md-4">
                                                        <small class="text-muted d-block">วันเกิด / เพศ</small>
                                                        <span class="small fw-bold">${birthDay} (${sex})</span>
                                                    </div>
                                                    <div class="col-12 col-md-4">
                                                        <small class="text-muted d-block">Visit ล่าสุด</small>
                                                        <span class="small fw-bold text-info">${lastVisit}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    `;
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
