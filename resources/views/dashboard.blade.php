@extends('layout')
@section('title', 'Dashboard | EKG-ECHO')

@section('dashboardContent')
    <div class="container-fluid py-4 px-4">
        {{-- Stats Overview --}}
        <div class="row g-3 mb-4">
            <div class="col-xl-3 col-md-6">
                <div class="card border-0 shadow-sm h-100 stat-card border-start border-4 border-primary">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="stat-icon bg-primary bg-opacity-10 rounded-3 p-2">
                                <i class="fas fa-calendar-alt text-primary fa-lg"></i>
                            </div>
                            <span class="text-muted small fw-bold">นัดทั้งหมด</span>
                        </div>
                        <h2 class="fw-bold mb-1 text-dark">{{ $todayCount }}</h2>
                        <p class="text-muted small mb-0">ประจำวันที่
                            {{ request('dateFilter', now()->format('d/m/Y')) }}</p>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card border-0 shadow-sm h-100 stat-card border-start border-4 border-success">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="stat-icon bg-success bg-opacity-10 rounded-3 p-2">
                                <i class="fas fa-check-circle text-success fa-lg"></i>
                            </div>
                            <span class="text-muted small fw-bold">เสร็จสิ้นแล้ว</span>
                        </div>
                        <h2 class="fw-bold mb-1 text-dark">{{ $doneCount }}</h2>
                        <div class="progress mt-2" style="height: 6px; border-radius: 3px;">
                            <div class="progress-bar bg-success" role="progressbar"
                                style="width: {{ $todayCount > 0 ? ($doneCount / $todayCount) * 100 : 0 }}%"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card border-0 shadow-sm h-100 stat-card border-start border-4 border-warning">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="stat-icon bg-warning bg-opacity-10 rounded-3 p-2">
                                <i class="fas fa-clock text-warning fa-lg"></i>
                            </div>
                            <span class="text-muted small fw-bold">กำลังรอ</span>
                        </div>
                        <h2 class="fw-bold mb-1 text-dark">{{ $waitingCount }}</h2>
                        <p class="text-muted small mb-0"><i class="fas fa-sync-alt me-1"></i>อัปเดตแบบ Realtime</p>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card border-0 shadow-sm h-100 stat-card border-start border-4 border-info">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="stat-icon bg-info bg-opacity-10 rounded-3 p-2">
                                <i class="fas fa-user-plus text-info fa-lg"></i>
                            </div>
                            <span class="text-muted small fw-bold">คนไข้ใหม่วันนี้</span>
                        </div>
                        <h2 class="fw-bold mb-1 text-dark">{{ $newPatientsCount }}</h2>
                        <p class="text-muted small mb-0">ข้อมูลคำนวณจากระบบ</p>
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
                            <div class="input-group input-group-sm rounded-pill border overflow-hidden">
                                <span class="input-group-text bg-white border-0"><i
                                        class="fas fa-calendar text-primary"></i></span>
                                <input type="date" class="form-control border-0" id="appointmentDate"
                                    value="{{ request('dateFilter', now()->toDateString()) }}"
                                    onchange="filterAppointments()">
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
                                <tbody>
                                    @php $counter = 1; @endphp
                                    @foreach ($appointmentsByDoctor as $doctor => $appointments)
                                        <tr class="bg-gray-100">
                                            <td colspan="6"
                                                class="ps-4 py-2 border-bottom fw-bold text-primary small bg-light bg-opacity-50">
                                                <i class="fas fa-user-md me-2"></i>{{ $doctor }}
                                                <span
                                                    class="badge bg-primary bg-opacity-10 text-primary ms-2">{{ $appointments->count() }}
                                                    เคส</span>
                                            </td>
                                        </tr>
                                        @foreach ($appointments as $item)
                                            <tr>
                                                <td class="ps-4 text-muted small text-center">{{ $counter++ }}</td>
                                                <td><span
                                                        class="badge bg-secondary bg-opacity-10 text-secondary fw-bold">{{ trim($item->hn) }}</span>
                                                </td>
                                                <td class="fw-bold">{{ $item->patient_name }}</td>
                                                <td class="text-primary fw-bold">{{ $item->time }}</td>
                                                <td><span class="small text-muted">{{ $item->ward ?? '-' }}</span></td>
                                                <td class="text-center">
                                                    @if (str_contains($item->note ?? '', 'เสร็จ') || $item->source == 'homc')
                                                        <span class="status-dot status-done"></span>
                                                    @else
                                                        <span class="status-dot status-waiting"></span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endforeach
                                    @if ($appointmentsByDoctor->isEmpty())
                                        <tr>
                                            <td colspan="6" class="text-center py-5">
                                                <img src="https://img.icons8.com/clouds/100/000000/todo-list.png"
                                                    class="mb-3">
                                                <p class="text-muted">ไม่พบนัดหมายในวันที่เลือก</p>
                                            </td>
                                        </tr>
                                    @endif
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
                                            <h6 class="mb-0 fw-bold small">{{ $item->fullname }}</h6>
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
                document.getElementById('current-time').textContent = now.toLocaleString('th-TH', options);
            }
            setInterval(updateClock, 1000);
            updateClock();
        });

        function filterAppointments() {
            const date = document.getElementById('appointmentDate').value;
            const url = new URL(window.location.href);
            url.searchParams.set('dateFilter', date);
            window.location.href = url.toString();
        }
    </script>
@endsection
