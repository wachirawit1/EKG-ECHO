@extends('layout')

@section('title', 'Executive Dashboard | EKG-ECHO')

@section('content')
    <div class="container py-4">
        {{-- Breadcrumb --}}
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('index') }}" class="text-decoration-none">แดชบอร์ด</a></li>
                <li class="breadcrumb-item active">รายงานสถิติ (Executive Overview)</li>
            </ol>
        </nav>

        {{-- Page Header --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold text-dark mb-1">
                    <i class="fas fa-chart-pie me-2 text-primary"></i>Executive Dashboard
                </h2>
                <p class="text-muted mb-0">สรุปข้อมูลการนัดหมายและภาระงานประจำวันที่
                    {{ \App\Helpers\DateHelper::formatThaiDate(now()) }}</p>
            </div>
            <div class="btn-group">
                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="window.print()">
                    <i class="fas fa-print me-1"></i> พิมพ์รายงาน
                </button>
                <button type="button" class="btn btn-primary btn-sm" onclick="location.reload()">
                    <i class="fas fa-sync-alt me-1"></i> รีเฟรชข้อมูล
                </button>
            </div>
        </div>

        {{-- Summary Cards --}}
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm bg-primary text-white h-100">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-uppercase mb-2 opacity-75 small fw-bold">เคสทั้งหมดวันนี้</h6>
                                <h2 class="mb-0 fw-bold">{{ $stats['total'] }}</h2>
                            </div>
                            <div class="icon-square bg-white bg-opacity-25 rounded-circle p-3">
                                <i class="fas fa-users fa-2x"></i>
                            </div>
                        </div>
                        <div class="mt-3 small opacity-75">
                            <i class="fas fa-info-circle me-1"></i> รวมจากระบบ HOMC และสมุดบันทึก
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm bg-success text-white h-100">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-uppercase mb-2 opacity-75 small fw-bold">ดึงข้อมูลจาก HIS</h6>
                                <h2 class="mb-0 fw-bold">{{ $stats['his'] }}</h2>
                            </div>
                            <div class="icon-square bg-white bg-opacity-25 rounded-circle p-3">
                                <i class="fas fa-database fa-2x"></i>
                            </div>
                        </div>
                        <div class="mt-3 small opacity-75">
                            <i class="fas fa-check-circle me-1"></i> ข้อมูลที่บันทึกผ่านระบบ HOMC
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm bg-info text-white h-100">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-uppercase mb-2 opacity-75 small fw-bold">บันทึกผ่านระบบเอง</h6>
                                <h2 class="mb-0 fw-bold">{{ $stats['manual'] }}</h2>
                            </div>
                            <div class="icon-square bg-white bg-opacity-25 rounded-circle p-3">
                                <i class="fas fa-keyboard fa-2x"></i>
                            </div>
                        </div>
                        <div class="mt-3 small opacity-75">
                            <i class="fas fa-user-edit me-1"></i> ข้อมูลที่เจ้าหน้าที่คีย์เพิ่มเอง
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-4">
            {{-- Charts Section --}}
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white py-3 border-0">
                        <h5 class="fw-bold mb-0">ภาระงานตามรายชื่อแพทย์</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="doctorWorkloadChart" height="300"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white py-3 border-0">
                        <h5 class="fw-bold mb-0">สัดส่วนที่มาข้อมูล</h5>
                    </div>
                    <div class="card-body d-flex align-items-center justify-content-center">
                        <div style="width: 100%; max-width: 250px;">
                            <canvas id="sourceDistributionChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Detailed Data Table --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0">ตารางรายชื่อผู้ป่วย (แยกตามแพทย์)</h5>
                <span class="badge bg-light text-dark border">{{ count($appointmentsByDoctor) }} รายชื่อแพทย์</span>
            </div>
            <div class="card-body p-0">
                <div class="accordion accordion-flush" id="doctorAccordion">
                    @foreach ($appointmentsByDoctor as $doctorName => $appointments)
                        <div class="accordion-item border-bottom">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed py-3" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#doctor-{{ Str::slug($doctorName) }}">
                                    <div class="d-flex justify-content-between w-100 pe-3 align-items-center">
                                        <span class="fw-bold text-primary">
                                            <i class="fas fa-user-md me-2"></i>{{ $doctorName }}
                                        </span>
                                        <span class="badge rounded-pill bg-primary bg-opacity-10 text-primary">
                                            {{ count($appointments) }} เคส
                                        </span>
                                    </div>
                                </button>
                            </h2>
                            <div id="doctor-{{ Str::slug($doctorName) }}" class="accordion-collapse collapse"
                                data-bs-parent="#doctorAccordion">
                                <div class="accordion-body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-hover align-middle mb-0 mb-0-important">
                                            <thead class="bg-light small text-uppercase">
                                                <tr>
                                                    <th class="ps-4">เวลา</th>
                                                    <th>HN</th>
                                                    <th>ชื่อ-นามสกุล</th>
                                                    <th>แผนก/วอร์ด</th>
                                                    <th class="text-center">แหล่งข้อมูล</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($appointments as $app)
                                                    <tr>
                                                        <td class="ps-4 fw-bold text-secondary">{{ $app->time }}</td>
                                                        <td><code class="text-primary fw-bold">{{ $app->hn }}</code>
                                                        </td>
                                                        <td class="fw-bold">{{ $app->patient_name }}</td>
                                                        <td><span class="small text-muted">{{ $app->appoint_dept ?? '-' }}</span>
                                                        </td>
                                                        <td class="text-center">
                                                            @if ($app->source == 'homc')
                                                                <span
                                                                    class="badge bg-success-soft text-success rounded-pill px-3">HOMC</span>
                                                            @else
                                                                <span
                                                                    class="badge bg-info-soft text-info rounded-pill px-3">สมุดบันทึก</span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <style>
        .icon-square {
            width: 56px;
            height: 56px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .bg-success-soft {
            background-color: rgba(25, 135, 84, 0.1);
        }

        .bg-info-soft {
            background-color: rgba(13, 202, 240, 0.1);
        }

        .table-hover tbody tr:hover {
            background-color: rgba(13, 110, 253, 0.02);
        }

        .accordion-button:not(.collapsed) {
            background-color: rgba(13, 110, 253, 0.05);
            color: #0d6efd;
            box-shadow: none;
        }

        .mb-0-important {
            margin-bottom: 0 !important;
        }

        @media print {

            .btn-group,
            nav,
            .accordion-button::after {
                display: none !important;
            }

            .accordion-collapse {
                display: block !important;
            }

            .card {
                border: 1px solid #dee2e6 !important;
                box-shadow: none !important;
            }
        }
    </style>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Data from PHP
            const doctorData = @json($stats['by_doctor']);
            const sourceData = @json($stats['by_source']);

            // 1. Doctor Workload Chart (Bar Chart)
            const doctorLabels = Object.keys(doctorData);
            const doctorCounts = Object.values(doctorData);

            new Chart(document.getElementById('doctorWorkloadChart'), {
                type: 'bar',
                data: {
                    labels: doctorLabels,
                    datasets: [{
                        label: 'จำนวนคนไข้ (เคส)',
                        data: doctorCounts,
                        backgroundColor: 'rgba(13, 110, 253, 0.7)',
                        borderColor: '#0d6efd',
                        borderWidth: 1,
                        borderRadius: 5
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        }
                    }
                }
            });

            // 2. Source Distribution Chart (Doughnut Chart)
            const sourceLabels = Object.keys(sourceData).map(s => s === 'homc' ? 'ระบบ HIS' : 'บันทึกเอง');
            const sourceCounts = Object.values(sourceData);

            new Chart(document.getElementById('sourceDistributionChart'), {
                type: 'doughnut',
                data: {
                    labels: sourceLabels,
                    datasets: [{
                        data: sourceCounts,
                        backgroundColor: [
                            '#198754', // HIS
                            '#0dcaf0' // Manual
                        ],
                        hoverOffset: 4
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    },
                    cutout: '70%'
                }
            });
        });
    </script>
@endsection
