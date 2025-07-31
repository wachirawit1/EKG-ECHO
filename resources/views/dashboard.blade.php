@extends('layout')
@section('title', 'Dashboard')
@section('content')
    <div class="row ">
        <div class="col-12">
            <h1 class="h3 mb-4">แดชบอร์ด - ระบบนัดหมายผู้ป่วย</h1>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                นัดหมายวันนี้
                            </div>
                            <div class="h5 mb-0 font-weight-bold">{{ $todayCount }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar-day fa-2x text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                เสร็จสิ้นแล้ว
                            </div>
                            <div class="h5 mb-0 font-weight-bold">{{ $doneCount }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                กำลังรอ
                            </div>
                            <div class="h5 mb-0 font-weight-bold">7</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-warning"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                ยกเลิก/ไม่มา
                            </div>
                            <div class="h5 mb-0 font-weight-bold">3</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-times-circle fa-2x text-danger"></i>
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
                                <i class="fas fa-plus mr-2"></i> เพิ่มนัดหมายใหม่
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="#" class="btn btn-success btn-block">
                                <i class="fas fa-calendar-check mr-2"></i> นัดหมายวันนี้
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="#" class="btn btn-info btn-block">
                                <i class="fas fa-users mr-2"></i> ค้นหาผู้ป่วย
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('report.show') }}" class="btn btn-secondary btn-block">
                                <i class="fas fa-chart-bar mr-2"></i> รายงาน
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
                                    <th>วันที่นัด</th>
                                    <th>HN</th>
                                    <th>สถานะ</th>
                                    <th>แหล่งที่มา</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($appointmentsByDoctor as $doctor => $appointments)
                                    <tr>
                                        <td colspan="6" class="bg-light font-weight-bold">
                                            {{ $doctor }}
                                            <span class="text-muted"> ({{ $appointments->count() }} นัด)</span>
                                        </td>
                                    </tr>
                                    @foreach ($appointments as $item)
                                        <tr>
                                            <td>{{ $item->time }}</td>
                                            <td>{{ $item->patient_name }}</td>
                                            <td>{{ $item->date }}</td>
                                            <td><span class="badge text-bg-primary">{{ '#' . $item->hn }}</span></td>
                                            <td>{{ $item->pt_status ?? '-' }}</td>
                                            <td>{{ $item->source }}</td>
                                        </tr>
                                    @endforeach
                                @endforeach
                            </tbody>
                        </table>
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
                    <ul class="list-group list-group-flush">
                        @foreach ($upcoming as $item)
                            <li class="list-group-item d-flex justify-content-between align-items-start">
                                <div class="ms-2 me-auto">
                                    <div class="font-weight-bold">{{ $item->fullname }}</div>
                                    <small class="text-muted">{{ $item->date_human }} {{ $item->time }}</small>
                                </div>
                                <span class="badge badge-primary badge-pill">{{ $item->service_name }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">สถิติรายสัปดาห์</h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6 border-right">
                            <div class="h4 mb-0 font-weight-bold text-primary">156</div>
                            <div class="text-xs">นัดหมายทั้งหมด</div>
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
@endsection
@push('refresh')
    <script>
        setInterval(function() {
            location.reload();
        }, 300000);
    </script>
@endpush
