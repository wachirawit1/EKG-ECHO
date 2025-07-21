@extends('layout')
@section('title', 'Dashboard | EKG-ECHO')
@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <h1 class="h3 mb-4 text-gray-800">แดชบอร์ด - ระบบนัดหมายผู้ป่วย</h1>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="row">
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-primary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                    นัดหมายวันนี้
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">25</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-calendar-day fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-success shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                    เสร็จสิ้นแล้ว
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">18</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-warning shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                    กำลังรอ
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">7</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-clock fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-danger shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                    ยกเลิก/ไม่มา
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">3</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-times-circle fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">เมนูด่วน</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <a href="#" class="btn btn-primary btn-block">
                                    <i class="fas fa-plus mr-2"></i>เพิ่มนัดหมายใหม่
                                </a>
                            </div>
                            <div class="col-md-3 mb-3">
                                <a href="#" class="btn btn-success btn-block">
                                    <i class="fas fa-calendar-check mr-2"></i>นัดหมายวันนี้
                                </a>
                            </div>
                            <div class="col-md-3 mb-3">
                                <a href="#" class="btn btn-info btn-block">
                                    <i class="fas fa-users mr-2"></i>ค้นหาผู้ป่วย
                                </a>
                            </div>
                            <div class="col-md-3 mb-3">
                                <a href="#" class="btn btn-secondary btn-block">
                                    <i class="fas fa-chart-bar mr-2"></i>รายงาน
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Today's Appointments -->
        <div class="row">
            <div class="col-lg-8">
                <div class="card shadow mb-4">
                    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                        <h6 class="m-0 font-weight-bold text-primary">นัดหมายวันนี้</h6>
                        <a href="#" class="btn btn-sm btn-primary">ดูทั้งหมด</a>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>เวลา</th>
                                        <th>ผู้ป่วย</th>
                                        <th>ประเภท</th>
                                        <th>สถานะ</th>
                                        <th>การดำเนินการ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($appoint as $item)
                                        <tr>
                                            <td>{{ $item->appoint_time_from . ' - ' . $item->appoint_time_to }}</td>
                                            <td>{{ $item->titleCode . $item->titleName . ' ' . $item->firstName . ' ' . $item->lastName }}
                                            </td>
                                            <td>{{ $item->appoint_date }}</td>
                                            <td><span class="badge text-bg-success">เสร็จสิ้น</span></td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="#" class="btn btn-sm btn-outline-primary">ดู</a>
                                                    <a href="#" class="btn btn-sm btn-outline-warning">แก้ไข</a>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-2">
                            {{ $appoint->links('pagination::bootstrap-5') }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Upcoming Appointments -->
            <div class="col-lg-4">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">นัดหมายที่จะถึง</h6>
                    </div>
                    <div class="card-body">
                        <div class="list-group list-group-flush">
                            <!-- Sample Data -->
                            <div class="list-group-item d-flex justify-content-between align-items-start">
                                <div class="ms-2 me-auto">
                                    <div class="fw-bold">คุณสมปอง เก่งดี</div>
                                    <small class="text-muted">พรุ่งนี้ 08:30</small>
                                </div>
                                <span class="badge bg-primary rounded-pill">EKG</span>
                            </div>
                            <div class="list-group-item d-flex justify-content-between align-items-start">
                                <div class="ms-2 me-auto">
                                    <div class="fw-bold">คุณสมศักดิ์ มั่นใจ</div>
                                    <small class="text-muted">พรุ่งนี้ 09:00</small>
                                </div>
                                <span class="badge bg-primary rounded-pill">ECHO</span>
                            </div>
                            <div class="list-group-item d-flex justify-content-between align-items-start">
                                <div class="ms-2 me-auto">
                                    <div class="fw-bold">คุณสมหวัง ดีใจ</div>
                                    <small class="text-muted">มะรืนนี้ 10:00</small>
                                </div>
                                <span class="badge bg-primary rounded-pill">EKG + ECHO</span>
                            </div>
                            <div class="list-group-item d-flex justify-content-between align-items-start">
                                <div class="ms-2 me-auto">
                                    <div class="fw-bold">คุณสมร่วม ช่วยกัน</div>
                                    <small class="text-muted">มะรืนนี้ 14:00</small>
                                </div>
                                <span class="badge bg-primary rounded-pill">EKG</span>
                            </div>
                            <div class="list-group-item d-flex justify-content-between align-items-start">
                                <div class="ms-2 me-auto">
                                    <div class="fw-bold">คุณสมคิด ฉลาด</div>
                                    <small class="text-muted">วันศุกร์ 09:30</small>
                                </div>
                                <span class="badge bg-primary rounded-pill">ECHO</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Stats -->
                <div class="card shadow">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">สถิติรายสัปดาห์</h6>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-6">
                                <div class="border-right">
                                    <div class="h4 mb-0 font-weight-bold text-primary">156</div>
                                    <div class="text-xs">นัดหมายทั้งหมด</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="h4 mb-0 font-weight-bold text-success">142</div>
                                <div class="text-xs">เสร็จสิ้นแล้ว</div>
                            </div>
                        </div>
                        <hr>
                        <div class="text-center">
                            <div class="h5 mb-0 font-weight-bold text-info">91.0%</div>
                            <div class="text-xs">อัตราการเสร็จสิ้น</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- @push('scripts')
        <script>
            // Auto refresh every 5 minutes
            setInterval(function() {
                location.reload();
            }, 300000);

            // Real-time clock
            function updateClock() {
                const now = new Date();
                const timeString = now.toLocaleTimeString('th-TH');
                const dateString = now.toLocaleDateString('th-TH');

                // Update if clock element exists
                if (document.getElementById('current-time')) {
                    document.getElementById('current-time').textContent = timeString;
                }
                if (document.getElementById('current-date')) {
                    document.getElementById('current-date').textContent = dateString;
                }
            }

            // Update clock every second
            setInterval(updateClock, 1000);
            updateClock(); // Initial call
        </script>
    @endpush --}}
@endsection
