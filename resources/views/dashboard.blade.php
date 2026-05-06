@extends('layout')
@section('title', 'Dashboard | EKG-ECHO')

@section('dashboardContent')
    <div class="container-fluid py-4 px-4">
        {{-- Stats Overview --}}
        <div class="row g-3 mb-4 row-cols-2 row-cols-md-3 row-cols-lg-5">
            <div class="col">
                <div class="card border-0 shadow-sm h-100 stat-card border-start border-4 border-primary">
                    <div class="card-body p-3 text-center">
                        <div class="d-flex justify-content-center align-items-center mb-2">
                            <div class="stat-icon bg-primary bg-opacity-10 rounded-3 p-2" style="width: 35px; height: 35px;">
                                <i class="fas fa-calendar-alt text-primary"></i>
                            </div>
                        </div>
                        <span class="text-muted smaller fw-bold d-block mb-1">นัดทั้งหมด</span>
                        <h3 class="fw-bold mb-0 text-dark" id="stat-total">{{ $todayCount }}</h3>
                        <p class="text-muted" style="font-size: 10px; margin-top: 4px;">
                            <i class="fas fa-calendar-day me-1"></i>
                            {{ \Carbon\Carbon::parse(request('dateFilter', now()->toDateString()))->locale('th')->translatedFormat('j F') }}
                            {{ \Carbon\Carbon::parse(request('dateFilter', now()->toDateString()))->year + 543 }}
                        </p>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card border-0 shadow-sm h-100 stat-card border-start border-4 border-success">
                    <div class="card-body p-3 text-center">
                        <div class="d-flex justify-content-center align-items-center mb-2">
                            <div class="stat-icon bg-success bg-opacity-10 rounded-3 p-2"
                                style="width: 35px; height: 35px;">
                                <i class="fas fa-check-circle text-success"></i>
                            </div>
                        </div>
                        <span class="text-muted smaller fw-bold d-block mb-1">ตรวจเสร็จแล้ว</span>
                        <h3 class="fw-bold mb-0 text-dark" id="stat-done">{{ $doneCount }}</h3>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card border-0 shadow-sm h-100 stat-card border-start border-4 border-info">
                    <div class="card-body p-3 text-center">
                        <div class="d-flex justify-content-center align-items-center mb-2">
                            <div class="stat-icon bg-info bg-opacity-10 rounded-3 p-2" style="width: 35px; height: 35px;">
                                <i class="fas fa-user-check text-info"></i>
                            </div>
                        </div>
                        <span class="text-muted smaller fw-bold d-block mb-1">มาแล้ว/รอตรวจ</span>
                        <h3 class="fw-bold mb-0 text-dark" id="stat-came">{{ $cameCount }}</h3>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card border-0 shadow-sm h-100 stat-card border-start border-4"
                    style="border-color: #6f42c1 !important;">
                    <div class="card-body p-3 text-center">
                        <div class="d-flex justify-content-center align-items-center mb-2">
                            <div class="stat-icon bg-purple bg-opacity-10 rounded-3 p-2"
                                style="width: 35px; height: 35px; background-color: rgba(111, 66, 193, 0.1);">
                                <i class="fas fa-user-times" style="color: #6f42c1;"></i>
                            </div>
                        </div>
                        <span class="text-muted smaller fw-bold d-block mb-1">ไม่มาตามนัด</span>
                        <h3 class="fw-bold mb-0 text-dark" style="color: #6f42c1;" id="stat-missed">{{ $missedCount }}</h3>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card border-0 shadow-sm h-100 stat-card border-start border-4 border-warning">
                    <div class="card-body p-3 text-center">
                        <div class="d-flex justify-content-center align-items-center mb-2">
                            <div class="stat-icon bg-warning bg-opacity-10 rounded-3 p-2"
                                style="width: 35px; height: 35px;">
                                <i class="fas fa-clock text-warning"></i>
                            </div>
                        </div>
                        <span class="text-muted smaller fw-bold d-block mb-1">รอรับบริการ</span>
                        <h3 class="fw-bold mb-0 text-dark" id="stat-waiting">{{ $waitingCount }}</h3>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-4">
            {{-- Main Content Section --}}
            <div class="col-lg-8">
                {{-- Quick Actions Banner --}}
                <div class="card border-0 shadow-sm mb-4 bg-primary bg-opacity-10 action-banner">
                    <div class="card-body p-4">
                        <div class="row align-items-center">
                            <div class="col-md-7">
                                <h4 class="fw-bold text-primary mb-2">เข้าถึงเมนูต่างๆ ได้รวดเร็ว</h4>
                                <p class="text-secondary mb-4 small">จัดการการนัดหมาย บันทึกผลการตรวจ
                                    และดูรายงานสรุปสถิติประจำวันได้ในที่เดียว</p>
                                <div class="d-flex flex-wrap gap-2">
                                    <a href="{{ route('app.show') }}" class="btn btn-primary rounded-pill px-4 shadow-sm">
                                        <i class="fas fa-plus me-2"></i>เริ่มบันทึกงาน
                                    </a>
                                    <a href="{{ route('patient.search') }}"
                                        class="btn btn-outline-primary rounded-pill px-4">
                                        <i class="fas fa-search me-2"></i>ค้นหาชื่อHN
                                    </a>
                                </div>
                            </div>
                            <div class="col-md-5 d-none d-md-block text-center">
                                <i class="fas fa-heartbeat fa-5x text-primary opacity-25"></i>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Today's List --}}
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                        <h5 class="fw-bold mb-0">รายการนัดคัดแยกตามกลุ่มภาระงาน</h5>
                        <div class="d-flex align-items-center">
                            <div class="input-group input-group-sm rounded-pill border overflow-hidden shadow-sm"
                                style="width: 180px;">
                                <span class="input-group-text bg-white border-0"><i
                                        class="fas fa-calendar-alt text-primary"></i></span>
                                <input type="text" class="form-control border-0 bg-white" id="appointmentDate"
                                    placeholder="เลือกวันที่..." readonly>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light small">
                                    <tr>
                                        <th class="ps-4">ลำดับ</th>
                                        <th>HN</th>
                                        <th>ชื่อ-นามสกุล</th>
                                        <th>เวลา</th>
                                        <th>แผนก/การตรวจ</th>
                                        <th class="text-center">สถานะ</th>
                                    </tr>
                                </thead>
                                <tbody id="dashboard-table-body">
                                    @include('fragments.dashboard_table')
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Sidebar Section --}}
            <div class="col-lg-4">
                {{-- Upcoming Section --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0 py-3">
                        <h6 class="fw-bold mb-0">นัดหมายที่จะถึง (เร็วๆ นี้)</h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush scroll-section"
                            style="max-height: 400px; overflow-y: auto;">
                            @forelse ($upcoming as $item)
                                <div class="list-group-item border-0 p-3 upcoming-item mx-2 rounded-3 mb-2">
                                    <div class="d-flex align-items-center">
                                        <div class="time-box me-3 text-center">
                                            <div class="small fw-bold text-primary">
                                                {{ \App\Helpers\DateHelper::toCarbon($item->appoint_date)->format('d') }}
                                            </div>
                                            <div class="x-small text-muted">
                                                {{ \App\Helpers\DateHelper::toCarbon($item->appoint_date)->format('M') }}
                                            </div>
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="d-flex align-items-center">
                                                <h6 class="mb-0 fw-bold small">{{ $item->fullname }}</h6>
                                                <span class="ms-2 badge bg-light text-muted fw-normal"
                                                    style="font-size: 9px;">HN: {{ $item->hn }}</span>
                                            </div>
                                            <span class="x-small text-muted">{{ $item->time }} |
                                                {{ $item->service_name }}</span>
                                        </div>
                                        <div class="arrow">
                                            <i class="fas fa-chevron-right text-light opacity-50"></i>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-4 opacity-50 small">ไม่มีนัดหมายใหม่</div>
                            @endforelse
                        </div>
                    </div>
                </div>

                {{-- Online Users Section --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                        <h6 class="fw-bold mb-0">ออนไลน์อยู่ในขณะนี้</h6>
                        <span
                            class="badge bg-success bg-opacity-10 text-success rounded-pill">{{ count($onlineUsers) }}</span>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush scroll-section"
                            style="max-height: 200px; overflow-y: auto;">
                            @forelse ($onlineUsers as $user)
                                <div class="list-group-item border-0 px-4 py-2">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-circle bg-primary bg-opacity-10 text-primary me-3 small fw-bold d-flex align-items-center justify-content-center"
                                            style="width: 32px; height: 32px; border-radius: 50%;">
                                            {{ mb_substr($user->fullname, 0, 1) }}
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="small fw-bold">{{ $user->fullname }}</div>
                                            <div class="x-small text-muted">
                                                <i class="fas fa-circle text-success x-small me-1"
                                                    style="font-size: 6px;"></i>
                                                {{ \Carbon\Carbon::parse($user->last_activity)->diffForHumans() }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-4 opacity-50 small">ไม่มีผู้ใช้งานออนไลน์</div>
                            @endforelse
                        </div>
                    </div>
                </div>

                {{-- Performance Card --}}
                <div class="card border-0 shadow-sm overflow-hidden dash-card secondary-card">
                    <div class="card-body p-4">
                        <h6 class="fw-bold mb-3">เป้าหมายงานสัปดาห์นี้</h6>
                        <div class="mb-4">
                            <div class="d-flex justify-content-between mb-1 small">
                                <span>ความคืบหน้า</span>
                                <span class="fw-bold text-primary">91%</span>
                            </div>
                            <div class="progress" style="height: 8px; border-radius: 4px;">
                                <div class="progress-bar bg-primary" role="progressbar" style="width: 91%"></div>
                            </div>
                        </div>
                        <div class="row g-2 text-center">
                            <div class="col-6">
                                <div class="p-2 border rounded-3 bg-light">
                                    <div class="h5 mb-0 fw-bold">156</div>
                                    <div class="x-small text-muted text-uppercase">ทั้งหมด</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="p-2 border rounded-3 bg-light">
                                    <div class="h5 mb-0 fw-bold text-success">142</div>
                                    <div class="x-small text-muted text-uppercase">เสร็จสิ้น</div>
                                </div>
                            </div>
                        </div>
                        <a href="{{ route('report.show') }}"
                            class="btn btn-outline-secondary btn-sm w-100 mt-3 rounded-pill">
                            ดูรายละเอียดสถิติ <i class="fas fa-arrow-right ms-2"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        :root {
            --bs-primary: #4361ee;
            --bs-success: #2ec4b6;
            --bs-warning: #ff9f1c;
            --bs-info: #3a86ff;
            --bs-danger: #e71d36;
        }

        .stat-card {
            transition: all 0.3s ease;
            border-radius: 1rem;
            background: #ffffff;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1) !important;
        }

        .stat-icon {
            width: 45px;
            height: 45px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .action-banner {
            border-radius: 1.25rem;
            border: 1px dashed rgba(13, 110, 253, 0.2) !important;
        }

        .status-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            display: inline-block;
        }

        .status-done {
            background: #10b981;
            box-shadow: 0 0 8px rgba(16, 185, 129, 0.5);
        }

        .status-waiting {
            background: #f59e0b;
            box-shadow: 0 0 8px rgba(245, 158, 11, 0.5);
        }

        .upcoming-item {
            background: #fafafa;
            border: 1px solid #f0f0f0;
            transition: background 0.2s;
            cursor: pointer;
        }

        .upcoming-item:hover {
            background: #f0f7ff;
            border-color: #d0e7ff;
        }

        .time-box {
            background: white;
            border-radius: 8px;
            padding: 5px;
            min-width: 45px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .x-small {
            font-size: 0.7rem;
        }

        .bg-gray-100 {
            background-color: #f8f9fa;
        }

        .scroll-section::-webkit-scrollbar {
            width: 4px;
        }

        .scroll-section::-webkit-scrollbar-thumb {
            background: #e0e0e0;
            border_radius: 10px;
        }

        #appointmentDate {
            font-weight: bold;
            padding-left: 0;
        }

        .btn-toggle.active {
            background-color: #0d6efd;
            color: white;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Clock
            function updateClock() {
                const now = new Date();
                const options = {
                    hour: '2-digit',
                    minute: '2-digit',
                    second: '2-digit',
                    hour12: false,
                    weekday: 'long',
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric'
                };
                const clockEl = document.getElementById('current-time');
                if (clockEl) clockEl.textContent = now.toLocaleString('th-TH', options);
            }
            setInterval(updateClock, 1000);
            updateClock();

            // Initialize Flatpickr
            flatpickr("#appointmentDate", {
                locale: "th",
                dateFormat: "Y-m-d",
                altInput: true,
                altFormat: "j F Y",
                defaultDate: "{{ request('dateFilter', now()->toDateString()) }}",
                onChange: function(selectedDates, dateStr, instance) {
                    filterAppointments(dateStr);
                },
                onReady: function(selectedDates, dateStr, instance) {
                    // ปรับปีใน alt input ให้เป็น พ.ศ. (Optional - flatpickr default as AD)
                    const altInput = instance.altInput;
                    if (altInput && dateStr) {
                        const date = new Date(dateStr);
                        const yearBE = date.getFullYear() + 543;
                        altInput.value = altInput.value.replace(date.getFullYear(), yearBE);
                    }
                }
            });
        });

        function filterAppointments(date) {
            const url = new URL(window.location.href);
            url.searchParams.set('dateFilter', date);
            window.location.href = url.toString();
        }

        // Auto Update Dashboard (AJAX Polling) every 60 seconds
        setInterval(function() {
            const dateFilter = "{{ request('dateFilter', now()->toDateString()) }}";
            // Check if there's any active modal (prevent updating while user is interacting)
            if ($('.modal.show').length > 0) return;

            $.ajax({
                url: "{{ route('index') }}",
                type: 'GET',
                data: {
                    dateFilter: dateFilter
                },
                success: function(response) {
                    if (response) {
                        $('#stat-total').text(response.todayCount);
                        $('#stat-done').text(response.doneCount);
                        $('#stat-came').text(response.cameCount);
                        $('#stat-missed').text(response.missedCount);
                        $('#stat-waiting').text(response.waitingCount);

                        // Update table body
                        $('#dashboard-table-body').html(response.table_html);
                    }
                },
                error: function(err) {
                    console.log("Failed to auto-update dashboard.");
                }
            });
        }, 300000);
    </script>
@endsection
